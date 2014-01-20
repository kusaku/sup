<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class LastNoticeListWidget extends CWidget {
	public $list;
	public function run() {
		$this->render('LastNoticeList',array('notices'=>$this->list));
	}
}