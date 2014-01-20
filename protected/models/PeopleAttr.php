<?php 
class PeopleAttr extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'people_attr';
	}
	
	public function relations() {
		return array(
			'client'=>array(
				self::BELONGS_TO,
				'People',
				'people_id'
			),
				
			'attr'=>array(
				self::BELONGS_TO,
				'Attributes',
				'attribute_id',
				'joinType'=>'INNER JOIN'
			)
		);
	}
}
