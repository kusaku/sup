<?php
/**
 * Класс выполняет обрботку функции GetMe
 */
class AuthNewPasswordAction extends ApiApplicationAction implements IApiPostAction{
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['key']) || !isset($_REQUEST['password']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obApplication=$this->getController()->getModule()->getApplicationTokens()->getApplication();
		$arFilter=array(
			'code'=>$_REQUEST['key'],
			'active'=>1
		);
		if($obRequest=FSPeoplePasswordRestoreRequest::model()->findByAttributes($arFilter)) {
			if($obUser=People::model()->findByPk($obRequest->people_id)) {
				if($_REQUEST['password']!='') {
					if(strlen($_REQUEST['password'])>=6) {
						$obUser->psw=People::genPasswordHash($_REQUEST['password']);
						$obUser->update(array('psw'));
						$obRequest->active=0;
						$obRequest->date_used=date('Y-m-d H:i:s');
						$obRequest->update(array('active','date_used'));
						if($_REQUEST['autoAuth']==1)
							$this->getController()->getModule()->getApplicationTokens()->setUserId($obUser->id);
						$arResult=array(
							'result'=>200,
							'resultText'=>'ok',
							'data'=>array('info'=>'Ваш пароль успешно обновлён')
						);
					} else {
						throw new ApiException(3,'password wrong');
					}
				} else {
					throw new ApiException(2,'password required');
				}
			} else {
				throw new ApiException(4,'no user');
			}
		} else {
			throw new ApiException(1,'wrong key');
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
