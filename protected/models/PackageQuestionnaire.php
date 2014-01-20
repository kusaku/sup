<?php
/**
 * Класс обеспечивает хранение результатов заполнения анекет на создание сайта
 */
class PackageQuestionnaire extends CActiveRecord {
	
	public function rules() {
		return array(
			array('description,colors,favorite_sites',
				'safe'
			),
		);
	}
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_questionnaire';
	}
	
	public function relations() {
		return array(
			'package'=>array(
				self::BELONGS_TO,
				'Package',
				'package_id'
			),
		);
	}
}
