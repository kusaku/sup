<?php
/**
 * Класс выполняет обрботку функции CreatePackage
 */
class CreatePackageAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_REQUEST['productID'])) {
			throw new CHttpException(400,'Bad request',400);
		}

		$this->checkAccess();

		//Определяем пользователя
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		$iUserId=$obToken->getUserId();
		if($iUserId==0)
			throw new CHttpException(403,'Auth required',403);
		if(isset($_REQUEST['userId']) && isset($_REQUEST['version']) && $_REQUEST['version']>='0.3')
			$iUserId=intval($_REQUEST['userId']);
		$obUser=People::model()->with('partner_data','infocode')->findByPk($iUserId);
		if(!$obUser)
			throw new ApiException(3,'User not found');
		if($iUserId!=$obToken->getUserId()) {
			//Если хотим не себя, надо убедиться, что есть права
			if(!$obToken->checkUserAccess('SuperAdmin'))
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

		$iProductId=intval($_REQUEST['productID']);
		$obServices=new CFSServicesModel();
		if($iProductId>0) {
			//Если указан продукт, ищем его сервисы
			$arList=$obServices->getServices($iProductId);
			if(count($arList)==0)
				throw new ApiException(1,'wrong product id');
			$arProduct=array_pop($arList);
			$arMore=array();
			if(isset($_REQUEST['services']) && is_array($_REQUEST['services']) && isset($arProduct['services'])) {
				foreach($_REQUEST['services'] as $arService)
					foreach($arProduct['services'] as $arItem)
						if(is_numeric($arService)) {
							if($arItem['id']==$arService){
								$arItem['count']=1;
								$arItem['descr']='';
								$arMore[]=$arItem;
							}
						} else {
							if($arItem['id']==$arService['id']) {
								$arItem['count']=1;
								$arItem['descr']='';
								if(isset($arService['count']))
									$arItem['count']=$arService['count'];
								if(isset($arService['price']))
									$arItem['price']=$arService['price'];
								if(isset($arService['descr']))
									$arItem['descr']=$arService['descr'];
								$arMore[]=$arItem;
							}
						}
			} elseif(isset($_REQUEST['serviceIDs']) && is_array($_REQUEST['serviceIDs']) && isset($arProduct['services']))
				for($i=0;$i<count($_REQUEST['serviceIDs']);$i++)
					if(isset($_REQUEST['serviceIDs'][$i]))
						foreach($arProduct['services'] as $arItem)
							if($arItem['id']==$_REQUEST['serviceIDs'][$i]){
								$arItem['count']=1;
								$arItem['descr']='';
								$arMore[]=$arItem;
							}
		} else {
			//Если это быстрый заказ
		}

		$obPackage = new Package();
		$obTransaction=Package::model()->dbConnection->beginTransaction();
		try {
			$obPackage->client_id = $obUser->id;
			// создание сайта при $_REQUEST['siteUrl']
			if (isset($_REQUEST['siteUrl']) && $_REQUEST['siteUrl']!='') {
				$site = Site::getByUrl($_REQUEST['siteUrl']);
				if (!$site) {
					$obSite = new Site();
					$obSite->url = $_REQUEST['siteUrl'];
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
			if(isset($_REQUEST['promocode']) && $_REQUEST['promocode']!='') {
				// ищем промокод
				$obPromocode = Promocode::model()->findByAttributes(array('code'=>$_REQUEST['promocode']));
				if(!$obPromocode) {
					// если не нашли - создаем новый
					$obPromocode = new Promocode();
					$obPromocode->code = $_REQUEST['promocode'];
					$obPromocode->save();
				}
				$obPackage->promocode_id = $obPromocode->primaryKey;
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
			if(!isset($_REQUEST['title']) || $_REQUEST['title']=='') {
				if(isset($arProduct)) {
					$obPackage->name=$arProduct['name'];
				} else {
					//TODO Доделать и убрать хардкод
					$obPackage->name='Быстрый заказ';
				}
			} else {
				$obPackage->name=htmlspecialchars($_REQUEST['title'],ENT_QUOTES,'utf-8',false);
			}
			if(!isset($_REQUEST['description']) || $_REQUEST['description']=='') {
				$obPackage->descr=Yii::t('api','Order from client: {client}',array('{client}'=>Yii::app()->getModule('api')->getApplicationUser()->getName()));
			} else {
				$obPackage->descr=htmlspecialchars($_REQUEST['description'],ENT_QUOTES,'utf-8',false);
			}
			$obPackage->dt_beg=date('Y-m-d H:i:s');
			$obPackage->dt_change = date('Y-m-d H:i:s');
			$obPackage->source_id=Yii::app()->getModule('api')->getApplicationUser()->getId();
			//Ставим заказы от сайта как заказы с сайта :) Грянзый хак
			if($obPackage->source_id==2)
				$obPackage->external=1;
			$obPackage->save();
			$obPackage->summ = 0;

			if(!$obPackage->infocode_id<=0){
				//TODO: сделать отправку письма о ненайденном инфокоде
				// после перехода на инфокоды вместо промокодов
				Yii::log('There is no such Infocode: "'. $_REQUEST['promocode'] .'". Package ID: '. $obPackage->primaryKey, CLogger::LEVEL_INFO, 'api.createPackage');
			}

			if(isset($arProduct)) {
				$obProduct2Package=new Serv2pack();
				$obProduct2Package->serv_id=$iProductId;
				$obProduct2Package->pack_id=$obPackage->id;
				$obProduct2Package->quant=1;
				$obProduct2Package->price=$arProduct['price']; //TODO Выяснить стоит ли здесь использовать скидку
				$obProduct2Package->descr='';
				$obProduct2Package->master_id=0;
				$obProduct2Package->duration=$arProduct['duration'];
				$obProduct2Package->save();
				$obPackage->summ=$arProduct['price'];

				foreach ($arMore as $arService) {
					$s2p = new Serv2pack();
					$s2p->serv_id = $arService['id'];
					$s2p->pack_id = $obPackage->id;
					$s2p->quant = $arService['count'];
					$s2p->price = $arService['price']; //TODO Выяснить стоит ли использовать скидку и считать сумму
					$s2p->descr = $arService['descr'];
					$s2p->duration = $arService['duration'];
					$s2p->master_id = 0;
					$s2p->save();
					$obPackage->summ += $s2p->quant * $s2p->price;
				}
				$obPackage->update(array('summ'));
			}
			//Формируем и отправляем письмо
			try {
				Yii::app()->getComponent('documents')->createEmailNewPackage($obPackage)->send($obUser->mail, $obUser->fio);
			} catch(exception $e) {}
			$arData=array(
				'id'=>$obPackage->id,
				'summ'=>$obPackage->summ
			);
			if(isset($arProduct)) {
				$arData['product']=array(
					'id'=>$arProduct['id'],
					'name'=>$arProduct['name'],
					'price'=>$arProduct['price']
				);
			}
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
			Yii::log('Message: '. $e->getMessage().', Line:' . $e->getLine(), CLogger::LEVEL_ERROR, 'api.createPackage');
			$obTransaction->rollBack();
			throw new ApiException(2,$e->getMessage());//'Package create error');
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}