<?php
/*
	Класс таблицы PeopleGroup
*/

class PeopleGroup extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'people_group';
	}

	/*
		Описание отношений с другими таблицами
	*/
	public function relations() 
	{
		return array('peoples'=>array(self::HAS_MANY, 'People', 'pgroup_id'));
	}

	/*
		Возвращиаем группу по ID
	*/
	public static function getById($id)
	{
		return self::model()->find(array('condition'=>"id=$id", 'limit'=>1));
	}
}
?>