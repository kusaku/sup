<?php
/**
 * Класс реализует ActiveRecord для записей таблицы PackageInvoice
 */
class PackageInvoice extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_invoice';
	}
	
	public function relations() {
		return array(
			'method'=>array(
				self::BELONGS_TO,
				'PayMethod',
				'pay_method_id'
			),
			'payment'=>array(
				self::BELONGS_TO,
				'Payment',
				'payment_id'
			),
			'rekviz_phis'=>array(
				self::HAS_ONE,
				'PackageInvoiceRekvizPhis',
				'package_invoice_id'
			),
			'rekviz_jur'=>array(
				self::HAS_ONE,
				'PackageInvoiceRekvizJur',
				'package_invoice_id'
			)
		);
	}
}
