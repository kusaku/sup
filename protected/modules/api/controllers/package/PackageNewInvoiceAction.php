<?php
/**
 * Класс выполняет создание/изменение счёта привязанного к заказу
 */
class PackageNewInvoiceAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['packageID']) || !isset($_REQUEST['paymethodID']) || $_SERVER['REQUEST_METHOD']!='POST' || !isset($_POST['data']))
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
			throw new ApiException(7,'no package');
		$obMethodModel=PayMethod::model();
		$arFilter=array(
			'active'=>1,
			'id'=>intval($_REQUEST['paymethodID'])
		);
		$obMethod=$obMethodModel->findByAttributes($arFilter);
		if(!$obMethod)
			throw new ApiException(4,'wrong pay method');
		if($obPackage->manager_id==0)
			throw new ApiException(5,'no manager');
		if($obPackage->jur_person_id==0)
			throw new ApiException(6,'jur_person_id not set');
		$obInvoice=$obPackage->invoice;
		if(!$obInvoice) {
			$obInvoice=new PackageInvoice();
			$obInvoice->package_id=$obPackage->id;
			$obInvoice->invoice_status='new';
			$obInvoice->date_add=date('Y-m-d H:i:s');
			$obInvoice->date_issue=NULL;
			$obInvoice->date_edit=date('Y-m-d H:i:s');
			$obInvoice->date_closed=NULL;
			$obInvoice->summ=$obPackage->summ;
			$obInvoice->description='';
			$obInvoice->pay_method_id=$obMethod->id;
			$obInvoice->payment_id=0;
		} else 
			$obInvoice->pay_method_id=$obMethod->id;
		/*if($obInvoice->invoice_status!='new')
			throw new ApiException(1,'already issued');*/
		if($obMethod->payer_type=='man') {
			//Физ лицо
			$obRekviz=$obInvoice->rekviz_phis;
			if(!$obRekviz) 
				$obRekviz=new PackageInvoiceRekvizPhis();
			$obRekviz->attributes=$_POST['data'];
		} else {
			//Юр лицо
			$obRekviz=$obInvoice->rekviz_jur;
			if(!$obRekviz) 
				$obRekviz=new PackageInvoiceRekvizJur();
			$obRekviz->jur_person_reference_id=$obPackage->jur_person_id;
			$obRekviz->setScenario('ltd');
			if(isset($_POST['data']['type']) && $_POST['data']['type']=='ip')
				$obRekviz->setScenario('ip');
			$obRekviz->attributes=$_POST['data'];
		}
		if($obRekviz->validate()) {
			if($obInvoice->isNewRecord) {
				if(!$obInvoice->save())
					throw new ApiException(2,'invoice create error');
			}
			$obRekviz->package_invoice_id=$obInvoice->id;
			if(!$obRekviz->save())
				throw new ApiException(2,'invoice create error');
			$obInvoice->date_edit=date('Y-m-d H:i:s');
			$obInvoice->update(array('date_edit','pay_method_id'));
			$arResult=array('result'=>'200','resultText'=>'ok');
		} else 
			$arResult=array('result'=>'501','resultText'=>'Function error','error'=>3,'errorText'=>'field error','data'=>$obRekviz->errors);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
