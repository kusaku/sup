<?php
/**
 * Класс выполняет обработку функции packageGenReceipt
 */
class PackageDocsReceivedAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['packageID']))
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
		$obInvoice=$obPackage->invoice;
		if(!$obInvoice)
			throw new ApiException(2,'no invoice');
		$obInvoice->invoice_status='issued';
		$obInvoice->update(array('invoice_status'));
		$obPackage->payment_id=19;
		if($obPackage->status_id<30) {
			$obPackage->status_id=30;
			$sPaytype=$obPackage->invoice->method->title;
			$obNotify=new ManagerNotifier();
			$obNotify->log='[auto] Пользователь выставил себе счёт в личном кабинете к заказу №'.$obPackage->getNumber();
			$obNotify->calendar='[auto] Пользователь '.$obPackage->client->mail.' ['.$obPackage->client->id.'] выставил себе счёт в личном кабинете к заказу №'.$obPackage->getNumber().'. Выбранный способ оплаты: '.$sPaytype.'.';
			$obNotify->manager_id=$obPackage->manager_id;
			$obNotify->client_id=$obPackage->client_id;
			$obNotify->Send();
		}
		$obPackage->update(array('payment_id','status_id'));
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok'	
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
