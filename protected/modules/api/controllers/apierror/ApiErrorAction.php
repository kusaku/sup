<?php
/**
 * Класс выполняет обработку действий связанных с ошибками с разными ответами
 */
class ApiErrorAction extends CAction {
	public $ErrorCode=501;
	public $HttpErrorCode=501;
	
	function run($Message,$error=0,$errorText='') {	
		$arResult=array(
			'result'=>501,
			'resultText'=>$Message,
			'error'=>$error,
			'errorText'=>$errorText
		);
		$this->getController()->_setAnswer(500,'Internal Server Error');
		$this->getController()->render('json',array('data'=>$arResult));
	}
}