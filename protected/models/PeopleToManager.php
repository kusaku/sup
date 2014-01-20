<?php 
/*
 Класс таблицы PeopleToManager
 */

class PeopleToManager extends CActiveRecord {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'people_to_manager';
	}
	
	public function relations() {
		return array(
			'user'=>array(
				self::BELONGS_TO,
				'People',
				'user_id'
			),
			'manager'=>array(
				self::BELONGS_TO,
				'People',
				'manager_id'
			)
		);
	}
}