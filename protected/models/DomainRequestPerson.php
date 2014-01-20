<?php
/**
 * Модель обеспечивающая сохранение заявок на регистрацию доменов
 */

class DomainRequestPerson extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function rules() {
		return array(
			array(
				'firstname,middlename,lastname,firstname_ru,middlename_ru,lastname_ru,birthdate,passport,passport_series,passport_org,passport_date',
				'safe'
			)
		);
	}

	public function tableName() {
		return 'domain_request_person_data';
	}
}