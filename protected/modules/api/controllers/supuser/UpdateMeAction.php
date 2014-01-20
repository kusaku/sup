<?php
/**
 * Класс выполняет обрботку функции UpdateMe
 */
class UpdateMeAction extends UpdateUserAction {
	function run() {
		unset($_REQUEST['userId']);
		return parent::run();
	}
}