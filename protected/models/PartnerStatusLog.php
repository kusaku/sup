<?php 
/**
 * Модель работы с клиентами партнёра 
 */

class PartnerStatusLog extends CActiveRecord {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'partner_status_log';
	}

	public function relations() {
		return array(
			'partner'=>array(
				self::BELONGS_TO,'People','partner_id'
			),
			'manager'=>array(
				self::BELONGS_TO,'People','manager_id'
			),
		);
	}
	
	public function scopes() {
		return array(
			'bydate'=>array(
				'order'=>'date DESC'
			)
		);
	}
}
