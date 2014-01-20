<?php
/**
 * Модель обеспечивает хранение данных о пользователе BillManager
 * @property $name string - логин для авторизации в BillManager
 */
class BMUsers extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'bm_users';
	}
	
	public function rules() {
		return array(array(
			'id,name,realname,email,disabled,superuser',
			'safe'
		));
	}

	public function relations() {
		return array(
			'user_data'=>array(
				self::HAS_ONE,'BMUserData','user_id'
			)
		);
	}
}
