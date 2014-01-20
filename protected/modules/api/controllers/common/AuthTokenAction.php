<?php
/**
 * Класс выполняет обработку функции getServices
 */
class AuthTokenAction extends ApiApplicationAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['appId']) || !isset($_REQUEST['appKey']) || !is_numeric($_REQUEST['appId']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		if($obApplication=FSAPIApplications::model()->findByPk(intval($_REQUEST['appId']))) {
			if($obApplication->checkKey($_REQUEST['appKey'])) {
				if($obApplication->active==1) {
					if(isset($_REQUEST['oldtoken'])) {
						//Если пользователь указал старый токен, ищем его и пробуем обновить сессию
						$arToken=Yii::app()->getModule('api')->getApplicationTokens()->UpdateToken($_REQUEST['oldtoken'],$obApplication->id,join('',$_REQUEST));
						$arResult=array(
							'result'=>200,
							'resultText'=>'ok',
							'data'=>array(
								'token'=>$arToken[0],
								'expires'=>$arToken[1]
							)
						);
					} else {
						//Пользователь не указывал старый токен, только генерация нового
						$arToken=Yii::app()->getModule('api')->getApplicationTokens()->NewToken($obApplication->id,join('',$_REQUEST));
						$arResult=array(
							'result'=>200,
							'resultText'=>'ok',
							'data'=>array(
								'token'=>$arToken[0],
								'expires'=>$arToken[1]
							)
						);
					}
				} else
					throw new ApiException(5,'application offline'); 				
			} else
				throw new ApiException(4,'auth error');
		} else
			throw new ApiException(1,'no application');
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
