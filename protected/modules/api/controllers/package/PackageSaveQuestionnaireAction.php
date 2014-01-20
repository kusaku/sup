<?php
/**
 * Класс выполняет обработку функции PackageSaveQuestionnnaire
 */
class PackageSaveQuestionnaireAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['packageID']) || $_SERVER['REQUEST_METHOD']!='POST')
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
		//Сохраняем анкету
		$obForm=new PackageQuestionnaire();
		$obForm->attributes=$_POST['data'];
		$obForm->package_id=$obPackage->id;
		$obForm->date_filled=date('Y-m-d H:i:s');
		if(!$obForm->save())
			throw new ApiException(2,'save error');
		$obNotify=new ManagerNotifier();
		$obNotify->log='[auto] Заполнена анкета на создание сайта, заказ №'.$obPackage->getNumber();
		$obNotify->calendar='[auto] Пользователь '.$obPackage->client->mail.' ['.$obPackage->client->id.'] заполнил анкету в личном кабинете к заказу №'.$obPackage->getNumber().'.';
		$obNotify->mail=$this->getController()->renderPartial('mail/questionnaire',array('package'=>$obPackage,'questionnaire'=>$obForm),true);
		$obNotify->manager_id=$obPackage->manager_id;
		$obNotify->client_id=$obPackage->client_id;
		$obNotify->Send();
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}