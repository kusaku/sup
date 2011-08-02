<?php
/*
 *	Класс таблицы
 */

class Rekviz extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'rekviz';
    }
    
    public function relations() 
    {
		return array(
			'people'=>array(self::BELONGS_TO,	'People',	'people_id'),
			'rekviz'=>array(self::HAS_MANY,		'Rekviz',	'rekvizit_id'),
			);
	}
	
	/*
	 * Поиск
	 */
	public function getById($id) {
		 return self::model()->findByPk(array('condition'=>"id=$id", 'limit'=>1));
	}
	
}
?>