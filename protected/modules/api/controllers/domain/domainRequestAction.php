<?php
/**
 * Класс выполняет обработку функции AddService
 */
class DomainRequestAction extends ApiUserAction implements IApiPostAction {
	public function run() {
		$this->_checkProtocolRequirements();
		if(!isset($_REQUEST['mode']) || !isset($_REQUEST['fields'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();
		if($_REQUEST['mode']!='company' && $_REQUEST['mode']!='person') {
			throw new ApiException(1,'wrong mode');
		}

		/**
		 * @var ApiTokens $obToken
		 */
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);

		if(isset($_REQUEST['requestID'])) {
			//Если пытаемся изменить существующий запрос
			if($obRequest=DomainRequest::model()->findByPk($_REQUEST['requestID'])) {
				if($obRequest->client_id!=$obToken->getUserId()) {
					if(!$obToken->hasRole('SuperAdmin')) { //Не суперадмин
						if(!$obToken->hasRole('Manager')) {  //Не менеджер
							if($obToken->hasRole('BasePartner')) { // Партнёр?
								if($obRequest->client->owner_partner) { //А у пользователья есть партнёр владелец?
									if($obRequest->client->owner_partner->id_partner!=$obToken->getUserId()) { //Владелец не я
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
				$sMode=$obRequest->mode;
				$obRequest->setScenario('edit');
				$obData=$obRequest->$sMode;
				$obRaw=$obRequest->raw;
			} else {
				throw new ApiException(2,'request not found');
			}
		} else {
			$obRequest=new DomainRequest('new');
			$obRequest->date_add=date('Y-m-d H:i:s');
			$obRequest->status='new';
			$obRequest->client_id=$obToken->getUserId();
			if($_REQUEST['mode']=='company') {
				$obData=new DomainRequestCompany();
			} else {
				$obData=new DomainRequestPerson();
			}
			$obRaw=new DomainRequestRaw();
		}

		if(isset($_REQUEST['siteID'])) {
			if($obSite=Site::model()->findByPk(intval($_REQUEST['siteID']))) {
				if(!$obRequest->isNewRecord) {
					if($obSite->client_id!=$obRequest->client_id) {
						throw new ApiException(4,'site already busy');
					}
				}
				$obRequest->site_id=$obSite->id;
				$obRequest->domain=$obSite->url;
				$obRequest->zone=substr($obSite->url,strrpos($obSite->url,'.'));
			} else {
				throw new ApiException(5,'site by id not found');
			}
		} elseif(isset($_REQUEST['siteUrl'])) {
			$_REQUEST['siteUrl']=trim($_REQUEST['siteUrl']);
			$obRequest->domain=$_REQUEST['siteUrl'];
			$obRequest->zone=substr($_REQUEST['siteUrl'],strrpos($_REQUEST['siteUrl'],'.'));
			$obRequest->site_id=NULL;
		}
		if(isset($_REQUEST['packageID'])) {
			$obRequest->package_id=intval($_REQUEST['packageID']);
		}
		if(isset($_REQUEST['raw'])) {
			$obRaw->data=$_REQUEST['raw'];
		}
		$obRequest->date_change=date('Y-m-d H:i:s');
		$obRequest->attributes=$_REQUEST['fields'];
		$obData->attributes=$_REQUEST['fields'];
		$obRequest->mode=$_REQUEST['mode'];
		$obRequest->status='new';
		$obTransaction=DomainRequest::model()->dbConnection->beginTransaction();
		try {
			if($obRequest->isNewRecord) {
				if($obRequest->package_id>0) {
					if($arRequests=DomainRequest::model()->findAllByAttributes(array('package_id'=>$obRequest->package_id,'status'=>'new'))) {
						foreach($arRequests as $obReq) {
							$obReq->status='denied';
							$obReq->update(array('status'));
						}
					}
				}
				$obRequest->save();
				if($obRequest->package_id>0) {
					//Отправляем уведомление для менеджера свзяанного с данным заказом
					if($obRequest->package && $obRequest->package->manager) {
						$post = new PHPMail();
						$post->from = 'sup@fabricasaitov.ru'; ///TODO Вынести в конфиг или ещё куда
						$post->fromname = 'СУП';
						$post->to = $obRequest->package->manager->mail;
						$post->toname = $obRequest->package->manager->fio;
						$post->subject = '[СУП][#'.$obRequest->package->id.'] Уведомление о заявке на домен';
						$post->body = 'Здравствуйте, '.$obRequest->package->manager->fio.".<br/>\n Клиент ".
							$obRequest->package->client->mail.' заполнил анкету на '.
							'регистрацию домена по заказу #'.$obRequest->package_id.".<br/>\n Анкета доступна в SUP'e по ссылке:<br>\n<br>\n".
							'<a href="http://sup.fabricasaitov.ru/manager#domainRequest_'.$obRequest->id.'">http://sup.fabricasaitov.ru/manager#domainRequest_'.$obRequest->id."</a>\n<br/>".
							"Список всех анкет на домены к данному заказу:\n<br/>".
							'<a href="http://sup.fabricasaitov.ru/manager#domainRequests_package_'.$obRequest->package_id.'">http://sup.fabricasaitov.ru/manager#domainRequests_package_'.$obRequest->package_id."</a>\n<br/>".
							"<br/>\nЕсли оплата по заказу уже поступила, то прошу как можно скорее подать заявку на регистрацию домена!<br>\n<br>\n".
							"С наилучшими пожеланиями,<br>\n".
							"Система управления проектами.";
						// пробуем отправить
						try {
							$post->SendMe();
						}
						catch(exception $e) {}
					}
				}
				$obData->request_id=$obRequest->id;
				$obRaw->request_id=$obRequest->id;
			} else {
				$obRequest->save();
			}
			$obData->save();
			$obRaw->save();
		 	if($obTransaction->active) $obTransaction->commit();
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>array('id'=>$obRequest->id)
			);
			$this->getController()->render('json',array('data'=>$arResult));
		} catch(exception $e) {
			if($obTransaction->active) $obTransaction->rollBack();
			throw new ApiException(6,$e->getMessage());//'Package update error');
		}
	}
}
