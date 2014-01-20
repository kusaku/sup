<?php

class RecsReport extends PaymentsReport {
	public function __construct($date_from,$date_to,$manager_id=0) {
		parent::__construct($date_from,$date_to,$manager_id);
		$this->mode='rec';
	}
}