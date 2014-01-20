<?php
/**
 * Класс выполняет обработку функции packageGenContract
 */
class PackageGenContractAction extends ApiUserAction implements IApiGetAction {
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
		
		try {
			$obDocuments=Yii::app()->getComponent('documents');
			if(isset($_GET['signed']) && $_GET['signed']==1) {
				$obContract=$obDocuments->createContract($obPackage);
				$obContract->getAsPdf();
				$obDetails=$obDocuments->createContractDetailsApplication($obPackage);
				$obDetails->setContract($obContract);
				$obDetails->getAsPdf();
				$sContent=$obDocuments->createFullContract($obPackage)->getAsPdf();
			} else {
				$obContract=$obDocuments->createContractOriginal($obPackage);
				$obContract->getAsPdf();
				$obDetails=$obDocuments->createContractDetailsApplicationOriginal($obPackage);
				$obDetails->setContract($obContract);
				$obDetails->getAsPdf();
				$sContent=$obDocuments->createFullContractOriginal($obPackage)->getAsPdf();
			}
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>base64_encode($sContent)
			);
		} catch (exception $e) {
			throw new ApiException($e->getCode(),$e->getMessage());
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}