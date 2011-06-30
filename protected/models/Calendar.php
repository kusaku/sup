<?php
/*
	Класс таблицы
*/

class Calendar extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'calendar';
	}

	public function relations()
	{
		return array(
			'people'=>array(self::BELONGS_TO,'People',	'people_id'),
			);
	}

	/**
	 *	Возвращает событие по ID. Может вернуть любое событие, даже не моё.
	 * @param int $id
	 * @return object
	 */
	public static function getById($id)
	{
		return self::model()->find(array('condition'=>"id=$id", 'limit'=>1));
	}

	/**
	 *	Возвращает все актуальные события.
	 * @param bool $onlyMy
	 * @return object
	 */
	public static function getAllActual( $onlyMy = true)
	{
		if ( $onlyMy )
			return self::model()->findAll(array('condition'=>"(status = 1)and(people_id = ".Yii::app()->user->id.")and(date <= '".date("Y-m-d")."')"));
		else
			return self::model()->findAll(array('condition'=>"(status = 1)and(date <= '".date("Y-m-d")."')"));
	}

	/**
	 *	Возвращает список всех моих событий по статусу.
	 * @param int $status
	 * @param bool $onlyMy
	 * @return object
	 */
	public static function getAllBySatus($status, $onlyMy)
	{
		if ( $onlyMy )
			return self::model()->findAll(array('condition'=>"(status = ".$status.")and(people_id = ".Yii::app()->user->id.")"));
		else
			return self::model()->findAll(array('condition'=>"status = ".$status ));
	}
}