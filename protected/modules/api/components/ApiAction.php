<?php
/**
 * Класс родитель для всех действий в рамках контроллеров модуля API
 */
abstract class ApiAction extends CAction {
	
	/**
	 * Метод выполняет проверку запроса на соответствие протоколу, а также поддерживаемую версию
	 * @return void
	 * @throws CHttpException (500, 400) если протокол не поддерживается или неверный формат запроса
	 */
	protected function _checkProtocolRequirements() {
		if(isset($_REQUEST['version']) && $_REQUEST['version']>'0.1') {
			if(ApiModule::PROTOCOL_VERSION<$_REQUEST['version'])
				throw new CHttpException(500,'Protocol version not supported',505);
			///@TODO после перехода на версию 5.3, это можно заменить строкой ниже
			$obRF=new ReflectionClass($this);
			if($obRF->getConstant('MODE')!=$_SERVER['REQUEST_METHOD'])
				throw new CHttpException(400,'Bad request',400);
			/*if($this::MODE!=$_SERVER['REQUEST_METHOD'])
				throw new CHttpException(400,'Bad request',400);*/
		}
	}
	
	public function checkAccess($params=array()) {
		throw new CHttpException(401,'Access offline',401);
	}
}
