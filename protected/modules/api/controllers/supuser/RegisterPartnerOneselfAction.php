<?php
/**
 * Класс выполняет обработку функции PackageSetProduct
 */
class RegisterPartnerOneselfAction extends RegisterUserAction {
	function run() {
		if(!(
			isset($_POST['mail'])
			&& isset($_POST['password'])
			&& isset($_POST['autoAuth'])
		) ){
			throw new CHttpException(400,'Bad request',400);
		}
		$this->_toRegisterPartner = true;
		$this->_registerOneself = true;

		parent::run();
	}
}