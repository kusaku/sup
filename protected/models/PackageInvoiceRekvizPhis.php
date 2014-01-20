<?php
/**
 * Класс реализует ActiveRecord для записей таблицы PackageInvoice
 */
class PackageInvoiceRekvizPhis extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_invoice_rekviz_phis';
	}
	
	public function rules() {
		return array(
			array(
				'fio','safe'
			),
		);
	}
	
	
	public function relations() {
		return array(
			'invoice'=>array(
				self::BELONGS_TO,
				'PackageInvoice',
				'package_invoice_id'
			),
		);
	}
}
