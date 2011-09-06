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
			'parent'=>array(self::HAS_ONE, 'Service', 'parent_id', 'order'=>'t.sort_order ASC'),
			'childs'=>array(self::HAS_MANY, 'Service', 'parent_id', 'order'=>'t.sort_order ASC'),
			'serv2pack'=>array(self::HAS_MANY, 'Serv2pack', 'serv_id'),			
			'Packages'=>array(self::MANY_MANY, 'Package','serv2pack(serv_id, pack_id)'),
			);
	}
}
?>