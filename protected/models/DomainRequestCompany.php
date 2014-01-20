<?php
/**
 * Модель обеспечивающая сохранение заявок на регистрацию доменов
 */

class DomainRequestCompany extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function rules() {
		return array(
			array(
				'company,company_ru,inn,kpp',
				'safe'
			)
		);
	}

	public function tableName() {
		return 'domain_request_company_data';
	}
}