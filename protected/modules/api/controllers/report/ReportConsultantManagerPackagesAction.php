<?php
/**
 * Класс выполняет запрос на получение счёта приложения
 */
class ReportConsultantManagerPackagesAction extends ReportPartnerManagerPackagesAction {

	function run() {
		$this->_forAllPartners = true;
		$this->_type = Partner::TP_CONSULTANT;
		return parent::run();
	}
}
