<?php

/**
 * Класс выполняет обработку функции AddService
 */
class ListAllPartnersAction extends ListPartnersAction {

	function run() {
		$this->_bAll = true;
		parent::run();
	}
}
