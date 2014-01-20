<?php
/**
 * Модель для управления данными о пользователе связанными с BillManager
 * @property BMUsers $user
 * @property People $people
 */
class BMUserData extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'bm_user_data';
	}
	
	public function rules() {
		return array(
			array(
				'account_id,profile_id,user_id',
				'safe'
			)
		);
	}

	public function relations() {
		return array(
			'people'=>array(
				self::BELONGS_TO,'People','people_id'
			), 'user'=>array(
				self::BELONGS_TO,'BMUsers','user_id'
			)
		);
	}
}
