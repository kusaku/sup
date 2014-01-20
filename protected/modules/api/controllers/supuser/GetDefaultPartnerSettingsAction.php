<?php
class GetDefaultPartnerSettingsAction extends ApiUserAction implements IApiGetAction {
	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0){
			throw new CHttpException(403,'Auth required',403);
		}

		$obPartnerDeafultSettings = new PartnerDefaultSettings();
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'data'=>$obPartnerDeafultSettings->getAttributes(),
		);
		$this->getController()->renderPartial('/layouts/json',array('data'=>$arResult));
	}
}