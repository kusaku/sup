<?php
/**
 * Класс выполняет обрботку функции GetMyRekvizAction
 */
class GetMyRekvizAction extends GetRekvizAction {
	function run() {
		unset($_GET['userId']);
		return parent::run();
	}
}
