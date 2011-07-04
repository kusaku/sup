<?php
/*
	Класс таблицы
*/

class Service extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'service';
    }
    
    public function relations() 
    {
		return array(
			'serv2pack'=>array(self::HAS_MANY, 'Serv2pack', 'serv_id'),
			'Packages'=>array(self::MANY_MANY, 'Package','serv2pack(serv_id, pack_id)'),
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
    
    public static function getAllByParent($id)
    {
    	return self::model()->findAll(array('condition'=>"parent_id=$id", 'order'=>'sort_order ASC'));
    }
}
?>