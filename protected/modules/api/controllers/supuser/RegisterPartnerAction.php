<?php
/**
 * Класс выполняет обрботку функции RegisterUser
 */
class RegisterPartnerAction extends RegisterUserAction{
	function run() {
		$this->_toRegisterPartner = true;
		if (isset($_POST['data']['type'])){
			$this->_partnerType = htmlspecialchars($_POST['data']['type'],ENT_QUOTES,'utf-8',false);
		}
		parent::run();
	}
}