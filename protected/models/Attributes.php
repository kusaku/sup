<?php 
class Attributes extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'attributes';
	}
	
	public function relations() {
		return array(
			'values'=>array(
				self::HAS_MANY,
				'PeopleAttr',
				'attribute_id',
				'joinType'=>'INNER JOIN'
			),
				
			'parent'=>array(
				self::BELONGS_TO,
				'Attributes',
				'parent_id'
			),
				
			'children'=>array(
				self::HAS_MANY,
				'Attributes',
				'parent_id'
			)
		);
	}
	
	public function getGroups() {
		return self::model()->findAllByAttributes(array(
			'parent_id'=>0
		));
	}
	
	public function getByType($name) {
		return self::model()->findByAttributes(array(
			'type'=>$name
		));
	}
}
