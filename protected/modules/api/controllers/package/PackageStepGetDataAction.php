<?php
/**
 * Класс выполняет обработку функции packageStepGetData
 */
class PackageStepGetDataAction extends ApiUserAction implements IApiGetAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_GET['packageID']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$arResult=array();
		$arFilter=array(
			'client_id'=>$obToken->getUserId(),
			'id'=>intval($_GET['packageID'])
		);
		//Получаем модель
		$obPackageModel=Package::model();
		$obPackage=$obPackageModel->findByAttributes($arFilter);
		if(!$obPackage)
			throw new ApiException(1,'no package');
		$obWorkflow=$obPackage->initWorkflow();
		if(isset($_GET['stepID'])) {
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$obWorkflow->getData($_GET['stepID'])
			);
		} else {
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$obWorkflow->getData()
			);
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
