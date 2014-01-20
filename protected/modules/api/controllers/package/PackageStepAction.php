<?php
/**
 * Класс выполняет обработку функции packageStep
 */
class PackageStepAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['packageID']) || !isset($_REQUEST['step']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
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
		if($obWorkflow->step_id!=$_REQUEST['step']) {
			$arSteps=$obWorkflow->step->steps;
			$bDone=false;
			foreach($arSteps as $obStep) {
				if($obStep->to_step_id==$_REQUEST['step']) {
					$obWorkflow->step_id=$obStep->to_step_id;
					$obWorkflow->update();
					$bDone=true;
					break;
				}
			}
			if(!$bDone)
				throw new ApiException(3,'cant switch step');
		}
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}