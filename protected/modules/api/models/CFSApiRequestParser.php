<?php
/**
 * Класс выполняет анализ переданного запроса на правильность и подготавливает контроллер и 
 * выполняет действите контроллера
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 12.05.12
 */
class CFSApiRequestParser extends CModel {
	private $obCurrentController;
	private $arActions;
	private $obModule;
	private $sActionID;
	
	public function __construct(ApiModule $obModuleInstance) {
		if(!($obModuleInstance instanceof CWebModule))
			throw new CException('System error');
		$this->obModule=$obModuleInstance;
		$this->arActions=$obModuleInstance->APIActions;
		$this->_analizeRequest();	
	}
	
	public function attributeNames() {
		return array();
	}
	
	/**
	 * Метод выполняет поиск выбранного действия и вызывает соответствующий контроллер в зависимости от результата 
	 * анализа запроса
	 */
	public function Run() {
		$this->obCurrentController->run($this->sActionID);
	}
	
	/**
	 * Метод выполняет анализ запроса и выбирает соответствующий контроллер 
	 */
	private function _analizeRequest() {
		if(!isset($_GET['action']))
			throw new CHttpException(400,'Bad request');
		$sAction=$_GET['action'];
		if(isset($this->arActions[$sAction])) {
			$arResult=Yii::app()->createController($this->arActions[$sAction],$this->obModule);
			if(is_array($arResult)) {
				$this->obCurrentController=$arResult[0];
				$this->sActionID=$arResult[1];
			} else
				throw new CHttpException(404,'Not found');
		} else
			throw new CHttpException(404,'Not found');
	}
}
