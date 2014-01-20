<?php
/**
 * Модель обеспечивающая связь между пользователями и документами
 */

class DocumentDocument extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'document_document';
	}

	public function relations() {
		return array(
			'documents'=>array(
				self::HAS_MANY,'Documents','document_id'
			), 'linked'=>array(
				self::HAS_MANY,'Documents','linked_id'
			)
		);
	}
}