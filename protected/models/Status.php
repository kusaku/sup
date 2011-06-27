<?php
/*
	Класс таблицы
*/

class Status extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'package_status';
    }
    
    public function relations() 
    {
		return array(
			'package'=>array(self::BELONGS_TO, 'Package', 'status_id')
			);
	}
    
    public static function getById($id)
    {
    	return self::model()->find(array('condition'=>"id=$id", 'limit'=>1));
    }

	public static function getAll()
    {
    	return self::model()->findAll();
    }
}
?>