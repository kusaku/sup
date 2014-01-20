<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PayMethod
 */
class PayMethod extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'pay_method';
	}
	
	public function relations() {
		return array(
			'invoices'=>array(
				self::HAS_MANY,
				'PackageInvoice',
				'pay_method_id'
			),
			'category'=>array(
				self::BELONGS_TO,
				'PayMethodCategory',
				'category_id'
			)
		);
	}
}
