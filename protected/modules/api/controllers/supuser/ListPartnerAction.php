<?php

/**
 * Класс выполняет обработку функции AddService
 */
class ListPartnerAction extends ListPartnersAction {

	function run() {
		$this->_myself = $this->getController()->getModule()->getApplicationTokens()->getUserId();
		parent::run();
	}
}
