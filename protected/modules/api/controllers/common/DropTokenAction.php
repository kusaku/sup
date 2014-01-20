<?php
/**
 * Класс выполняет обрботку функции dropToken
 */
class DropTokenAction extends ApiApplicationAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['token']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		if($obToken=FSAPITokens::model()->findByAttributes(array('token'=>$_REQUEST['token']))) {
			$obToken->expired=1;
			if(!$obToken->save())
				throw new ApiException(2,'token delete error');
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
			);
		} else
			throw new ApiException(1,'no token');
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
