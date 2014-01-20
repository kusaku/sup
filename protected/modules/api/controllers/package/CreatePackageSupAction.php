<?php
/**
 * Класс выполняет обрботку функции CreatePackage
 */
class CreatePackageSupAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		//Определяем пользователя
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		$iUserId=$obToken->getUserId();
		if($iUserId==0)
			throw new CHttpException(403,'Auth required',403);
		if(isset($_POST['userId']))
			$iUserId=intval($_POST['userId']);
		$obUser=People::model()->with('partner_data','infocode')->findByPk($iUserId);
		if(!$obUser)
			throw new ApiException(3,'User not found');
		if($iUserId!=$obToken->getUserId()) {
			//Если хотим не себя, надо убедиться, что есть права
			if(!$obToken->checkUserAccess('SuperAdmin')) {
				if(!$obToken->checkUserAccess('Manager')) {
					if($obToken->checkUserAccess('BasePartner') && $obUser->owner_partner->id_partner!=$obToken->getUserId()) {
						throw new ApiException(2,'Not alowed');
					} elseif($obToken->checkUserAccess('Client') && $obUser->id!=$obToken->getUserId()) {
						throw new ApiException(2,'Not alowed');
					}
				} else {
					if($obUser->pgroup_id!=7) {
						throw new ApiException(2,'Not alowed');
					}
				}
			}
		}

		$obServices=new CFSServicesModel();
		$arSystemServiceList=$obServices->getServicesSup();
		$arServiceToSave=array();
		$arExclusive=array();
		if(isset($_POST['services']) && is_array($_POST['services']) && is_array($arSystemServiceList)) {
			foreach($_POST['services'] as $arRequestedService) {
				foreach($arSystemServiceList as $arSystemService) {
					if($arSystemService['id']==$arRequestedService['id']) {
						//Check for exclusive services
						if($arSystemService['exclusive']){
							if(!in_array($arSystemService['parent_id'], $arExclusive)){
								$arExclusive[]=$arSystemService['parent_id'];
							} else {
								throw new ApiException(1,'mutually exclusive services found');
							}
						}
						// add service parameters from request
						$arSystemService['count']=1;
						$arSystemService['descr']='';
						if(isset($arRequestedService['count'])) {
							$arSystemService['count']=(int)$arRequestedService['count'];
						}
						if(isset($arRequestedService['price']) && $arSystemService['price']==0) {
							$arSystemService['price']=(float)$arRequestedService['price'];
						}
						if(isset($arRequestedService['descr'])) {
							$arSystemService['descr']=htmlspecialchars($arRequestedService['descr'],ENT_QUOTES,'utf-8',false);
						}
						$arServiceToSave[]=$arSystemService;
					}
				}
			}
		}

		$obPackage = new Package();
		$obTransaction=Package::model()->dbConnection->beginTransaction();
		try {
			$obPackage->client_id = $obUser->id;
			// создание сайта при $_POST['siteUrl']
			if (isset($_POST['siteUrl']) && $_POST['siteUrl']!='') {
				$site = Site::getByUrl($_POST['siteUrl']);
				if (!$site) {
					$obSite = new Site();
					$obSite->url = $_POST['siteUrl'];
					$obSite->host = '';
					$obSite->ftp = '';
					$obSite->db = '';
					$obSite->client_id = $obPackage->client_id;
					$obSite->save();
					$obPackage->site_id = $obSite->primaryKey;
				} else {
					//TODO Решить что делать если сайт уже занят
				}
			}

			// присвоение заказу промокода
			if(isset($_POST['promocode']) && $_POST['promocode']!='') {
				// ищем промокод
				$promocode = Promocode::model()->findByAttributes(array('code'=>$_POST['promocode']));
				if(!$promocode) {
					// если не нашли - создаем новый
					$promocode = new Promocode();
					$promocode->code = htmlspecialchars($_POST['promocode'],ENT_QUOTES,'utf-8',false);
					$promocode->save();
				}
				$obPackage->promocode_id = $promocode->primaryKey;

				//Обработка инфокода
				$obInfocode = Infocode::model()->with('partner')->findByAttributes(array('value'=>$_POST['promocode']));

				if ($obInfocode) {
					//приписываем инфокод к заказу
					$obPackage->infocode_id = $obInfocode->primaryKey;

					//Приписываем инфокод к клиенту, если у него еще нет инфокода и если он не партнер
					if(!$obUser->partner_data){
						if(!$obUser->infocode){
							$obUser->infocode_id = $obInfocode->primaryKey;
							$obUser->save();
						}
					}

					//приписываем клиента партнеру, если он еще ни к кому не приписан
					if(!$obUser->owner_partner && $obInfocode->partner){
						$obParnterPeople = new PartnerPeople();
						$obParnterPeople->id_client = $obUser->primaryKey;
						$obParnterPeople->id_partner = $obInfocode->partner['id'];
						$obParnterPeople->save();
					}
				}
			} else {
				$obPackage->promocode_id = null;
			}

			//Проверяем, есть ли у клиента партнер и приписываем заказу процент партнера
			//Так как $obUser->owner_partner после записи $obParnterPeople
			//не обновляется, следовательно делаем отдельный запрос.
			$obPartnerPeople2 = PartnerPeople::model()->with('partner','partner.partner_data')->findByAttributes(array('id_client'=>$obUser->primaryKey));
			if($obPartnerPeople2){
				$obPackage->partner_percent = $obPartnerPeople2->partner->partner_data['percent'];
			}

			//Прописываем статусы
			$obPackage->status_id=1;
			$obPackage->payment_id=17;
			//Поля и описания
			if(isset($arServiceToSave[0])) {
				$obPackage->name=$arServiceToSave[0]['name'];
			} else {
				//TODO Доделать и убрать хардкод
				$obPackage->name='Быстрый заказ';
			}
			if(isset($_POST['description']) && $_POST['description'] != ''){
				$obPackage->descr = htmlspecialchars($_POST['description'],ENT_QUOTES,'utf-8',false);
			} else {
				$obPackage->descr=Yii::t('api','Order from client: {client}',array('{client}'=>Yii::app()->getModule('api')->getApplicationUser()->getName()));
			}
			$obPackage->dt_beg=date('Y-m-d H:i:s');
			$obPackage->dt_change = date('Y-m-d H:i:s');
			$obPackage->source_id=Yii::app()->getModule('api')->getApplicationUser()->getId();
			$obPackage->save();
			$obPackage->summ = 0;

			if(isset($_POST['promocode']) && $_POST['promocode']!='' && !$obInfocode){
				//TODO: сделать отправку письма о ненайденном инфокоде
				// после перехода на инфокоды вместо промокодов
				Yii::log('There is no such Infocode: "'. $_REQUEST['promocode'] .'". Package ID: '. $obPackage->primaryKey, CLogger::LEVEL_INFO, 'api.createPackageSup');
			}

			foreach ($arServiceToSave as $arRequestedService) {
				$s2p = new Serv2pack();
				$s2p->serv_id = $arRequestedService['id'];
				$s2p->pack_id = $obPackage->id;
				$s2p->quant = $arRequestedService['count'];
				$s2p->price = $arRequestedService['price']; //TODO Выяснить стоит ли использовать скидку и считать сумму
				$s2p->descr = $arRequestedService['descr'];
				$s2p->duration = $arRequestedService['duration'];
				$s2p->master_id = 0;
				$s2p->save();
				$obPackage->summ += $s2p->quant * $s2p->price;
			}
			$obPackage->update(array('summ'));

			//Формируем и отправляем письмо
			try {
				Yii::app()->getComponent('documents')->createEmailNewPackage($obPackage)->send($obUser->mail, $obUser->fio);
			} catch(exception $e) {}
			$arData=array(
				'id'=>$obPackage->id,
				'summ'=>$obPackage->summ
			);
			if($obPackage->site_id>0) {
				$arData['site']=array(
					'id'=>$obSite->id,
					'url'=>$obSite->url
				);
			}
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$arData
			);
			$obTransaction->commit();
		} catch(exception $e) {
			$obTransaction->rollBack();
			throw new ApiException(4,$e->getMessage());
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}