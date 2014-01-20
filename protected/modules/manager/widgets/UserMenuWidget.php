<?php
/**
 * Указанный виджет подготавливает контекстное меню пользователя
 */

class UserMenuWidget extends CWidget {
	public $user;

	public function init() {
	}

	public function run() {
		$this->render('UserMenu',array('user'=>$this->user));
	}
}
