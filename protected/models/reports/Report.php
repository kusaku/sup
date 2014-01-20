<?php
define('PR_BUG_TIME',1354737599);
define('SOR_NEW_LOGIC_DATETIME',1352700000);

class Report {
	protected $date_from;
	protected $date_to;

	public function __construct($date_from,$date_to) {
		if(is_integer($date_from)) {
			$this->date_from=$date_from;
		} elseif(is_string($date_from)) {
			$this->date_from=strtotime($date_from);
		} else {
			$this->date_from=strtotime('now -1 month');
		}
		if(is_integer($date_to)) {
			$this->date_to=$date_to;
		} elseif(is_string($date_to)) {
			$this->date_to=strtotime($date_to);
		} else {
			$this->date_to=strtotime('now');
		}
	}
}