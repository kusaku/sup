<?php
/**
 * Класс обеспечивает генерацию ошибок уровня Api
 */
class ApiException extends CHttpException {
	protected $iErrorCode=0;
	protected $sErrorText='';
	
	function __construct($iErrorCode,$sErrorText) {
		parent::__construct(501,'Function error',501);
		$this->iErrorCode=$iErrorCode;
		$this->sErrorText=$sErrorText;
	}
	
	function getResultArray() {
		$arResult=array(
			'result'=>$this->getCode(),
			'resultText'=>$this->getMessage(),
			'error'=>$this->iErrorCode,
			'errorText'=>$this->sErrorText,
		);
		return $arResult;
	}
}
