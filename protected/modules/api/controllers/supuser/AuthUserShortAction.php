<?php
/**
 * Класс выполняет обрботку функции AuthUserShort
 */
class AuthUserShortAction extends ApiApplicationAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['login']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		$obToken=Yii::app()->getModule('api')->getApplicationTokens();
		if($obToken->getExpireDate()-300<time())
			throw new ApiException(2,'session too old');
		else {
			$sLoginField=$this->getController()->getModule()->getApplicationUser()->getParameter('login_field','login');
			$obIdentity = new ApiUserIdentity($_REQUEST['login'], '');
			$obIdentity->authenticateSimple($sLoginField);
			if($obIdentity->errorCode>0)
				throw new ApiException(1,'no user');
			else {
				if($obUser=People::model()->findByPk($obIdentity->getState('id'))) {
					$obToken->setExpireDate($obToken->getDateAdd()+300);
					$obToken->setUserId($obUser->id);
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
				}
				else
					throw new ApiException(1,'no user');
			}
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}