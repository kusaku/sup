<?php
/**
 * Класс выполняет обработку действий связанных с ошибками с разными ответами
 */
class BigErrorAction extends CAction {
	public $ErrorCode=500;
	public $HttpErrorCode=500;
	
	function run($Message,$Other='') {	
		$arResult=array(
			'result'=>$this->ErrorCode,
			'resultText'=>$Other
		);
		$this->getController()->_setAnswer($this->HttpErrorCode,$Message);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}