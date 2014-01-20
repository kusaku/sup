<?php
/**
 * Класс выполняет запрос на получение счёта приложения
 */
class ReportConsultantPackagesAction extends ReportPartnerManagerPackagesAction {

	function run() {
		$this->_forAllPartners = false;
		$this->_type = Partner::TP_CONSULTANT;
		return parent::run();
	}
}
