<?php
/**
 * Модель обеспечивающая связь между пользователями и документами
 */

class DocumentPeople extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'document_people';
	}

	public function relations() {
		return array(
			'documents'=>array(
				self::HAS_MANY,'Documents','document_id'
			), 'people'=>array(
				self::HAS_MANY,'People','people_id'
			)
		);
	}
}