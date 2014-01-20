<?php
/**
 * Класс выполняет запрос на получение счёта приложения
 */
class PackageGetInvoiceAction extends ApiUserAction implements IApiGetAction {
	/**
	 * Метод выполняет действия для версии протокола 0.3.1
	 */
	private function run_0_3_1() {
		if(!isset($_GET['packageID']) && !isset($_GET['invoiceID']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		if(isset($_GET['packageID'])) {
			$arFilter=array(
				'client_id'=>$obToken->getUserId(),
				'id'=>intval($_GET['packageID'])
			);
			//Получаем модель
			$obPackageModel=Package::model();
			$obPackage=$obPackageModel->findByAttributes($arFilter);
			if(!$obPackage)
				throw new ApiException(1,'no package');
			$obInvoice=$obPackage->invoice;
			if(!$obInvoice) 
				throw new ApiException(2,'no invoice');
		} else {
			$obInvoiceModel=PackageInvoice::model();
			$obInvoice=$obInvoiceModel->findByPk(intval($_GET['invoiceID']));
			if(!$obInvoice)
				throw new ApiException(2,'no invoice');
			$obPackageModel=Package::model();
			$obPackage=$obPackageModel->findByPk($obInvoice->package_id);
			if(!$obPackage)
				throw new ApiException(1,'no package');
			if($obPackage->client_id!=$obToken->getUserId())
				throw new ApiException(1,'no package');
		}
		$arData=$obInvoice->attributes;
		if($obInvoice->method->payer_type=='man') {
			$arData['rekviz']=$obInvoice->rekviz_phis->attributes;
		} else {
			$arData['rekviz']=$obInvoice->rekviz_jur->attributes;
		}
		$arData['fs_rekviz']=$obPackage->jur_person->attributes;
		$arResult=array('result'=>'200','resultText'=>'ok','data'=>$arData);
		$this->getController()->render('json',array('data'=>$arResult));
	}
	
	function run() {
		$this->_checkProtocolRequirements();
		if($_REQUEST['version']>'0.3') {
			return $this->run_0_3_1();
		}		
		
		if(!isset($_GET['packageID']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$arFilter=array(
			'client_id'=>$obToken->getUserId(),
			'id'=>intval($_GET['packageID'])
		);
		//Получаем модель
		$obPackageModel=Package::model();
		$obPackage=$obPackageModel->findByAttributes($arFilter);
		if(!$obPackage)
			throw new ApiException(1,'no package');
		$obInvoice=$obPackage->invoice;
		if(!$obInvoice) 
			throw new ApiException(2,'no invoice');
		$arData=$obInvoice->attributes;
		if($obInvoice->method->payer_type=='man') {
			$arData['rekviz']=$obInvoice->rekviz_phis->attributes;
		} else {
			$arData['rekviz']=$obInvoice->rekviz_jur->attributes;
		}
		$arData['fs_rekviz']=$obPackage->jur_person->attributes;
		$arResult=array('result'=>'200','resultText'=>'ok','data'=>$arData);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
