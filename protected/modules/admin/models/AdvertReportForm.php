<?php

class AdvertReportForm extends CFormModel {
	public $dt_from;
	public $dt_to;

	public function attributeNames() {
		return array('dt_from','dt_to');
	}

	public function attributeLabels() {
		return array(
			'dt_from'=>'С',
			'dt_to'=>'По'
		);
	}

	public function rules() {
		return array(
			array(
				'dt_from,dt_to',
				'safe'
			)
		);
	}
}