<?php
/**
 * Класс выполняет обрботку функции AuthUser
 */
class AuthUserAction extends ApiApplicationAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['login']) || !isset($_REQUEST['password']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$sLoginField=$this->getController()->getModule()->getApplicationUser()->getParameter('login_field','login');
		$obIdentity = new ApiUserIdentity($_REQUEST['login'], $_REQUEST['password']);
		$obIdentity->authenticate($sLoginField);
		if($obIdentity->errorCode>0) {
			switch($obIdentity->errorCode) {
				case ApiUserIdentity::ERROR_USERNAME_INVALID:
					throw new ApiException(1,'no user');
				case ApiUserIdentity::ERROR_PASSWORD_INVALID:
					throw new ApiException(2,'wrong password');
				default:
					throw new ApiException(1,'no user');
			}
		} else {
			if($obUser=People::model()->findByPk($obIdentity->getState('id'))) {
				//Временно повышаем права до уровня Client
				$obUserAuth=$this->getController()->getModule()->getUserAuth();
				if(!$obUserAuth->isAssigned(Yii::app()->params['apiConfig']['new_people_auth_item'],$obUser->id))
					$obUserAuth->assign(Yii::app()->params['apiConfig']['new_people_auth_item'],$obUser->id);
				Yii::app()->getModule('api')->getApplicationTokens()->setUserId($obUser->id);
				$arResult=array(
					'result'=>200,
					'resultText'=>'ok',
					'data'=>array(
						'mail'=>$obUser->mail,
						'login'=>$obUser->login,
						'id'=>$obUser->id,
						'fio'=>$obUser->fio,
						'state'=>$obUser->state,
						'phone'=>$obUser->phone,
						'firm'=>$obUser->firm,
						'descr'=>$obUser->descr,
						'regdate'=>$obUser->regdate,
					)
				);
			} else
				throw new ApiException(1,'no user');
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}