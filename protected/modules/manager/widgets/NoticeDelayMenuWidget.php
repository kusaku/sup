<?php
/**
 * Виджет обеспечивает генерацию меню обеспечивающего откладывание сообщения
 */

class NoticeDelayMenuWidget extends CWidget {
	public $notice;

	public function run() {
		$this->render('NoticeDelayMenu',array('obNotice'=>$this->notice));
	}
}