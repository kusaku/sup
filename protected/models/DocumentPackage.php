<?php
/**
 * Модель обеспечивающая связь между пользователями и документами
 */

class DocumentPackage extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'document_package';
	}

	public function relations() {
		return array(
			'documents'=>array(
				self::HAS_MANY,'Documents','document_id'
			), 'package'=>array(
				self::HAS_MANY,'Package','package_id'
			)
		);
	}
}