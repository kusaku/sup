<?php
/**
 * @property Service $service
 * @property ServiceTree $parent
 * @property ServiceTree[] $childs
 * @property ServiceTree $prevSibling
 *
 * @property integer $order
 * @property integer $id
 * @property integer $parent_id
 * @property integer $service_id
 * @property integer $hide_on_site
 */
class ServiceTree extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'service_tree';
	}
	
	public function relations() {
		return array(
			'parent'=>array(
				self::HAS_ONE,
				'ServiceTree',
				'parent_id',
				'order'=>'`order` ASC'
			),
				
			'childs'=>array(
				self::HAS_MANY,
				'ServiceTree',
				'parent_id',
				'order'=>'`order` ASC'
			),

			'service'=>array(
				self::BELONGS_TO,
				'Service',
				'service_id',
			),
		);
	}

	public function prevSibling() {
		$obCondition=new CDbCriteria();
		$obCondition->addCondition('`parent_id`='.$this->parent_id);
		$obCondition->addCondition('`order`<'.$this->order);
		$obCondition->order='`order` DESC';
		return ServiceTree::model()->find($obCondition);
	}

	public function nextSibling() {
		$obCondition=new CDbCriteria();
		$obCondition->addCondition('`parent_id`='.$this->parent_id);
		$obCondition->addCondition('`order`>'.$this->order);
		$obCondition->order='`order` ASC';
		return ServiceTree::model()->find($obCondition);
	}
}
