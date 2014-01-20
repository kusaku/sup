<?php
/**
 * Модель обеспечивающая сохранение заявок на регистрацию доменов
 */

class DomainRequestRaw extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'domain_request_raw_data';
	}
}