<?php
/**
 * Класс выполняет обработку функции IssueInvoice
 */
class PackageGenInvoiceAction extends ApiUserAction implements IApiGetAction {
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
		if(!$obPackage->invoice)
			throw new ApiException(3,'no invoice');
		try {
			$sContent=Yii::app()->getComponent('documents')->createInvoice($obPackage)->getAsPdf();
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