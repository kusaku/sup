<?php
/**
 * Класс выполняет запрос на получение счёта приложения
 */
class ReportPartnerPackagesAction extends ReportPartnerManagerPackagesAction {

	function run() {
		$this->_forAllPartners = false;
		return parent::run();
	}
}
