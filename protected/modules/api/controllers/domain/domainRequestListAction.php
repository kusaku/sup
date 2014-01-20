<?php
/**
 * Функция выполняет получение списка заявок на домен определённого пользователя или привязанные к определённому проекту. 
 */

class DomainRequestListAction extends ApiUserAction implements IApiGetAction {
	public function run() {
		$this->_checkProtocolRequirements();	
 		$this->checkAccess();
		throw new CHttpException(503,'Not implemented',503);
	}
}