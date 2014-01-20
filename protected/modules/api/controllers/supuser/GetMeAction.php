<?php
/**
 * Класс выполняет обрботку функции GetMe
 */
class GetMeAction extends GetUserAction {
	function run() {
		unset($_GET['id']);
		parent::run();
	}
}
