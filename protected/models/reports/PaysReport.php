<?php

class PaysReport extends PaymentsReport {
	public function __construct($date_from,$date_to,$manager_id=0) {
		parent::__construct($date_from,$date_to,$manager_id);
		$this->mode='pay';
	}

	protected function getCriteria() {
		$criteria = new CDbCriteria();
		$criteria->order = 'dt ASC';
		if($this->date_from<PR_BUG_TIME && $this->date_to>PR_BUG_TIME) {
			$criteria->condition="(ptype_id=1 ".
				"AND dt>='".date('Y-m-d 00:00:00', $this->date_from)."' ".
				"AND dt<='".date('Y-m-d 23:59:59', PR_BUG_TIME)."') OR (ptype_id='1' ".
				"AND dt_pay>='".date('Y-m-d 00:00:00', PR_BUG_TIME)."' ".
				"AND dt_pay<='".date('Y-m-d 23:59:59', $this->date_to)."')";
		} elseif($this->date_from<PR_BUG_TIME && $this->date_to<PR_BUG_TIME) {
			$criteria->scopes=array('pay');
			$criteria->compare('dt', '>='.date('Y-m-d 00:00:00', $this->date_from));
			$criteria->compare('dt', '<='.date('Y-m-d 23:59:59', $this->date_to));
		} else {
			$criteria->condition="(ptype_id='1' ".
				"AND dt_pay>='".date('Y-m-d 00:00:00', $this->date_from)."' ".
				"AND dt_pay<='".date('Y-m-d 23:59:59', $this->date_to)."')";
		}
		return $criteria;
	}
}