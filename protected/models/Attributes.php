<?php 
/*
 Класс таблицы
 */

class Attributes extends CActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return 'attributes';
    }
    
    public function relations() {
		return array(
			'value'=>array(self::HAS_MANY, 'PeopleAttr', 'attribute_id'),
			'groups'=>array(self::HAS_MANY, 'Attributes', 'parent_id', 'condition'=>'attributes.parent_id=0'),			
			'parent'=>array(self::BELONGS_TO, 'Attributes', 'parent_id'),
			'children'=>array(self::HAS_MANY, 'Attributes', 'parent_id')
		);    
    }
	
	public function getGroups() {
         return self::model()->findAllByAttributes(array('parent_id'=>0));  
	}
}
?>
