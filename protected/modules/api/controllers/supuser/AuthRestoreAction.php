<?php
/**
 * Класс выполняет обрботку функции AuthRestore
 */
class AuthRestoreAction extends ApiApplicationAction implements IApiPostAction {
	/**
	 * Метод выполняет операции по восстановлению пароля
	 * @todo Вынести название письма и текст письма в настройки
	 * @todo Усилить безопасность поля codeUrl
	 */
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['login']) && !isset($_REQUEST['email']))
			throw new CHttpException(400,'Bad request',400);
		
		$this->checkAccess();
		
		$obApplication=$this->getController()->getModule()->getApplicationTokens()->getApplication();
		$arFilter=array();
		if(isset($_REQUEST['login']))
			$arFilter['login']=$_REQUEST['login'];
		if(isset($_REQUEST['email']))
			$arFilter['mail']=$_REQUEST['email'];
		$sIp='';
		if(isset($_REQUEST['ip']) && preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#',$_REQUEST['ip']))
			$sIp=$_REQUEST['ip'];
		$sCodeUrl='';
		if(isset($_REQUEST['codeUrl']))
			$sCodeUrl=urldecode($_REQUEST['codeUrl']);
		$sCodePage='';
		if(isset($_REQUEST['codePage']))
			$sCodePage=urldecode($_REQUEST['codePage']);
		if($obUser=People::model()->findByAttributes($arFilter)) {
			$obValidator=new CEmailValidator();
			if(!$obValidator->validateValue($obUser->mail))
				throw new ApiException(3,'user got no email');
			$obRequest=new FSPeoplePasswordRestoreRequest();
			$obRequest->people_id=$obUser->id;
			$obRequest->active=1;
			$obRequest->code=$obRequest->genCode($obUser->id,$obUser->mail);
			$obRequest->date_add=date('Y-m-d H:i:s');
			$obRequest->date_used=NULL;
			$obRequest->ip=$sIp;
			$obRequest->codeUrl=$sCodeUrl;
			$obRequest->codePage=$sCodePage;
			$obDBCriteria=new CDbCriteria();
			$obDBCriteria->addColumnCondition(array('people_id'=>$obUser->id,'active'=>1));
			FSPeoplePasswordRestoreRequest::model()->updateAll(array('active'=>0),$obDBCriteria);
			$obRequest->save();
			//Отправляем письмо
			try {
				Yii::app()->getComponent('documents')->createEmailNewPassword($obRequest,$obApplication,$obUser)->send($obUser->mail,$obUser->fio);
			} catch(exception $e) {}
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>array(
					'info'=>'Вам отправлен код восстановления пароля'
				)
			);
		} else throw new ApiException(1,'no user');
		$this->getController()->render('json',array('data'=>$arResult));
	}
}