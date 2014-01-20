<?php
/**
 * Класс выполняет обработку функции packageStepSaveData
 */
class PackageStepSaveDataAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['packageID']) || !isset($_POST['data']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$arResult=array();
		$arFilter=array(
			'client_id'=>$obToken->getUserId(),
			'id'=>intval($_REQUEST['packageID'])
		);
		//Получаем модель
		$obPackageModel=Package::model();
		$obPackage=$obPackageModel->findByAttributes($arFilter);
		if(!$obPackage)
			throw new ApiException(1,'no package');
		$obWorkflow=$obPackage->initWorkflow();
		$obWorkflow->saveData($_POST['data']);
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
