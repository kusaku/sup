<?php
/**
 * Класс выполняет обработку функции AddService
 */
class AddServiceAction extends ApiUserAction implements IApiPostAction {
	protected $obToken;
	protected $obPackage;
	/**
	 * Метод обеспечивает проверку прав доступа для текущего и обрабатываемого пользователя
	 */
	protected function _prepareAndCheckPackage() {
		$this->obToken=$this->getController()->getModule()->getApplicationTokens();
		if($this->obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$this->obPackage=Package::model()->findByPk(intval($_REQUEST['packageID']));
		if(!$this->obPackage)
			throw new ApiException(1,'no package');
		//Если хотим не свой пакет, надо убедиться, что есть права
		if($this->obPackage->client_id!=$this->obToken->getUserId()) {
			if(!$this->obToken->hasRole('SuperAdmin')) { //Не суперадмин
				if(!$this->obToken->hasRole('Manager')) {  //Не менеджер
					if($this->obToken->hasRole('BasePartner')) { // Партнёр?
						if($this->obPackage->client->owner_partner) { //А у пользователья есть партнёр владелец?
							if($this->obPackage->client->owner_partner->id_partner!=$this->obToken->getUserId()) { //Владелец не я
								throw new ApiException(3,'no access'); // Значит лесом
							}
						} else { //Нет владельца
							throw new ApiException(3,'no access'); //Тоже лесом
						}
					} else { //Не партнёр и не манагер, значит лесом
						throw new ApiException(3,'no access');
					}
				} else { //Манагер
				}
			}
		}
	}

	public function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_REQUEST['packageID'])) {
			throw new CHttpException(400,'Bad request',400);
		}

		$this->checkAccess();
		$this->_prepareAndCheckPackage();
		$obTransaction=Package::model()->dbConnection->beginTransaction();
		try {
			// создание сайта при $_REQUEST['siteUrl']
			if (isset($_REQUEST['siteUrl']) && $_REQUEST['siteUrl']!='') {
				$obSite = Site::getByUrl($_REQUEST['siteUrl']);
				if (!$obSite) {
					$obSite = new Site();
					$obSite->url = $_REQUEST['siteUrl'];
					$obSite->host = '';
					$obSite->ftp = '';
					$obSite->db = '';
					$obSite->client_id = $this->obPackage->client_id;
					$obSite->save();
					$this->obPackage->site_id = $obSite->primaryKey;
				} else {
					if($obSite->client_id==$this->obPackage->client_id) {
						$this->obPackage->site_id = $obSite->id;
					} else {
						//Пока будем привязывать
						$this->obPackage->site_id=$obSite->id;
						//TODO Решить что делать если сайт уже занят другим клиентом
						//throw new ApiException(2, 'site already busy');
					}
				}
			}
			$this->obPackage->save();

			$arData=array(
				'id'=>$this->obPackage->id,
				'summ'=>$this->obPackage->summ
			);

			if($arProduct=$this->obPackage->getProduct()) {
				$arData['product']=array(
					'id'=>$arProduct['id'],
					'name'=>$arProduct['name'],
					'price'=>$arProduct['price']
				);
			}
			if($this->obPackage->site_id>0) {
				$arData['site']=array(
					'id'=>$this->obPackage->site->id,
					'url'=>$this->obPackage->site->url
				);
			}
			if($obTransaction->active) $obTransaction->commit();
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$arData
			);
			$this->getController()->render('json',array('data'=>$arResult));
		} catch(exception $e) {
			if($obTransaction->active) $obTransaction->rollBack();
			throw new ApiException(4,$e->getMessage());//'Package update error');
		}
	}
}
