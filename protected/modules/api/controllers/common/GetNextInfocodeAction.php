<?php
/**
 * Класс выполняет обрботку функции getServices
 */
class GetNextInfocodeAction extends ApiApplicationAction implements IApiGetAction {
	function run() {
		//Обработаем родительский вызов
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'data'=> Infocode::getNextInfocodeValue(),
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
