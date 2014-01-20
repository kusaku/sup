<?php
class SetDefaultPartnerSettingsAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		$obPartnerDefaultSettings = new PartnerDefaultSettings();
		if(!array_intersect_key($_POST, array_flip($obPartnerDefaultSettings->getSafeAttributeNames())) ){
			throw new CHttpException(400,'Bad request',400);
		}

		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0){
			throw new CHttpException(403,'Auth required',403);
		}

		// прописываем настройки в модель
		$obPartnerDefaultSettings->attributes = $_POST;

		if($obPartnerDefaultSettings->save()){
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
			);
		} else {
			$arResult=array(
				'result'=>501,
				'resultText'=>'Function error',
				'error'=>1,
				'errorText'=>'fields error',
				'data'=>$obPartnerDefaultSettings->getErrors(),
			);
		}
		$this->getController()->renderPartial('/layouts/json',array('data'=>$arResult));
	}
}