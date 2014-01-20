<?php 
/**
 * package status log model
 */

class PackageStatusLog extends CActiveRecord {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'package_status_log';
	}

	public function relations() {
		return array(
			'package'=>array(
				self::BELONGS_TO,'Package','package_id'
			),'manager'=>array(
				self::BELONGS_TO,'People','manager_id'
			),'wf_status'=>array(
				self::BELONGS_TO,'PackageStatus','status_id'
			),'pay_status'=>array(
				self::BELONGS_TO,'PackagePayment','payment_id'
			),'site'=>array(
				self::BELONGS_TO,'Site','site_id'
			)
		);
	}
}
