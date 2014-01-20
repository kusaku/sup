<?php 
class ServiceDescription extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'service_description';
	}
	
	public function relations() {
		return array(
			'parent'=>array(
				self::HAS_ONE,
				'Service',
				'service_id',
			),
		);
	}
}
