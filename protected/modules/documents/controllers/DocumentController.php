<?php
class DocumentController extends DocController {
	function actionContract($package,$jur_person,$client_jur_person) {
		$this->sOutput=$this->renderPartial('contract',array('package'=>$package,'jur_person'=>$jur_person,'client_jur_person'=>$client_jur_person),true);
	}
	
	function actionPdf($content) {
		$this->sOutput=$this->renderPartial('pdf',array('content'=>$content),true);
	}
	
	function actionInvoice($package,$jur_person,$client_jur_person,array $services) {
		$this->sOutput=$this->renderPartial('invoice',array('package'=>$package,'jur_person'=>$jur_person,'client_jur_person'=>$client_jur_person,'services'=>$services),true);
	}
	
	function actionReceipt($package,$jur_person,$title) {
		$this->sOutput=$this->renderPartial('receipt',array('package'=>$package,'jur_person'=>$jur_person,'title'=>$title),true);
	}
	
	function actionAct($package,$jur_person,$client_jur_person,array $services) {
		$this->sOutput=$this->renderPartial('act',array('package'=>$package,'jur_person'=>$jur_person,'client_jur_person'=>$client_jur_person,'services'=>$services),true);
	}
	
	function actionContractDetailsApplication($package,$jur_person,$client_jur_person,$product,array $services) {
		$sTemplate='contractDetailsApplication/product'.$product->serv_id;
		if($this->getViewFile($sTemplate)) {
			$this->sOutput=$this->renderPartial($sTemplate,array('package'=>$package,'jur_person'=>$jur_person,'client_jur_person'=>$client_jur_person,'product'=>$product,'services'=>$services),true);
		} else {
			$this->sOutput=intval($product->serv_id);
		}
	}
	
	function actionOffertApplication($package,$product,array $services) {
		$sTemplate='offertApplication/product'.$product->serv_id;
		if($this->getViewFile($sTemplate)) {
			$this->sOutput=$this->renderPartial($sTemplate,array('package'=>$package,'product'=>$product,'services'=>$services),true);
		} else {
			//$this->sOutput=intval($product->serv_id);
			$this->sOutput='';
		}
	}
}