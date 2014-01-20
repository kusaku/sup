<?php
/**
 * Класс выполняет обрботку функции GetMyRekvizAction
 */
class SaveMyRekvizAction extends SaveRekvizAction {
	function run() {
		unset($_REQUEST['userId']);
		return parent::run();
	}
}
