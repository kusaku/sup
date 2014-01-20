<?php
/**
 * Модель обеспечивающая взаимодействие с документами
 */

class Documents extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'documents';
	}

	public function relations() {
		return array(
			'documents'=>array(
				self::MANY_MANY,'Documents','document_document(document_id,linked_id)'
			), 'people'=>array(
				self::MANY_MANY,'People','document_people(document_id,people_id)'
			), 'invoices'=>array(
				self::MANY_MANY,'Service','document_invoice(document_id, invoice_id)'
			), 'payments'=>array(
				self::MANY_MANY,'Service','document_payment(document_id, payment_id)'
			), 'packages'=>array(
				self::MANY_MANY,'Package','document_package(document_id, package_id)'
			), 'attachments'=>array(
				self::MANY_MANY,'WaveAttachments','document_wave_attachment(document_id, wave_attachment_id)'
			)
		);
	}
}