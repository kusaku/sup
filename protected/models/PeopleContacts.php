<?php
/**
 * Класс обеспечивает взаимодействие с моделью контактных данных пользователей
 * хранимых в таблице people_contacts
 * @property integer $id
 * @property integer $people_id
 * @property string  $fio
 * @property string  $phone
 * @property string  $mobile
 * @property string  $email
 * @property string  $comment
 *
 * @property People $people
 */
class PeopleContacts extends CActiveRecord {
	/**
	 * @param string $className
	 *
	 * @return PeopleContacts
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'people_contacts';
	}

	public function rules() {
		return array(
			array(
				'email','email'
			),
			array(
				'fio,phone,mobile,email,comment','safe','on'=>'form'
			),
			array(
				'fio,phone,mobile,email,comment','safe','on'=>'update'
			)
		);
	}

	public function attributeNames() {
		return array('id','people_id','fio','phone','mobile','email','comment');
	}

	public function attributeLabels() {
		return array(
			'id'=>'ID',
			'people_id'=>'ID клиента',
			'fio'=>'ФИО',
			'phone'=>'Телефон',
			'mobile'=>'Мобильный',
			'email'=>'Email',
			'comment'=>'Комментарий'
		);
	}

	public function relations() {
		return array(
			'people'=>array(
				self::BELONGS_TO,
				'People',
				'people_id'
			)
		);
	}

	public function getTitle() {
		switch(false) {
			case empty($this->fio):
				return $this->fio;
			case empty($this->email):
				return $this->email;
			case empty($this->phone):
				return $this->phone;
			case empty($this->mobile):
				return $this->mobile;
			case empty($this->comment):
				return $this->comment;
			default:
				return $this->id;
		}
	}
}