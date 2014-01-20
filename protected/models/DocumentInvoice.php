<?php
/**
 * Модель обеспечивающая связь между пользователями и документами
 */

class DocumentInvoice extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'document_invoice';
	}

	public function relations() {
		return array(
			'documents'=>array(
				self::HAS_MANY,'Documents','document_id'
			), 'invoice'=>array(
				self::HAS_MANY,'PackageInvoice','invoice_id'
			)
		);
	}
}