<?php
/**
 * Модель обеспечивает управление записями календаря (уведомлений) для
 * менеджеров компании.
 *
 * @property integer $id
 * @property integer $people_id
 * @property string $date
 * @property string $message
 * @property integer $status
 * @property integer $interval
 */
class Calendar extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'calendar';
	}
	
	public function relations() {
		return array(
			'people'=>array(
				self::BELONGS_TO,
				'People',
				'people_id'
			),
		);
	}

	public function rules() {
		return array(
			array(
				'message','required'
			)
		);
	}

	public function attributeLabels() {
		return array(
			'people_id'=>'ID пользователя',
			'date'=>'Дата',
			'message'=>'Сообщение',
			'status'=>'Состояние',
			'interval'=>'Периодичность'
		);
	}
	
	/**
	 * Возвращает событие по ID. Может вернуть любое событие, даже не моё.
	 * @param int $id
	 * @return object
	 */
	public static function getById($id) {
		return self::model()->find(array(
			'condition'=>"id=$id",'limit'=>1
		));
	}
	
	/**
	 * Возвращает все актуальные события.
	 * @param bool $onlyMy
	 * @return object
	 */
	public static function getAllActual($onlyMy = true) {
		if ($onlyMy)
			return self::model()->findAll(array(
				'condition'=>"(status = 1)and(people_id = ".Yii::app()->user->id.")and(date <= '".date("Y-m-d H:i:s")."')"
			));
		else
			return self::model()->findAll(array(
				'condition'=>"(status = 1)and(date <= '".date("Y-m-d H:i:s")."')"
			));
	}
	
	/**
	 * Возвращает список всех моих событий по статусу.
	 * @param int $status
	 * @param bool $onlyMy
	 * @return object
	 */
	public static function getAllBySatus($status, $onlyMy) {
		if ($onlyMy)
			return self::model()->findAll(array(
				'condition'=>"(status = ".$status.")and(people_id = ".Yii::app()->user->id.")"
			));
		else
			return self::model()->findAll(array(
				'condition'=>"status = ".$status
			));
	}
}
