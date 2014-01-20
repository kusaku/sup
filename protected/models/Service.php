<?php
/**
 *
 */
class Service extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'service';
	}
	
	public function relations() {
		return array(
			'parent'=>array(
				self::HAS_ONE,
				'Service',
				'parent_id',
				'order'=>'t.sort_order ASC'
			),
				
			'childs'=>array(
				self::HAS_MANY,
				'Service',
				'parent_id',
				'order'=>'childs.sort_order ASC'
			),
			
			'descriptions'=>array(
				self::HAS_MANY,
				'ServiceDescription',
				'service_id',
			),
			
            'description'=>array(
                self::HAS_ONE,
                'ServiceDescription',
                'service_id',
            ),
				
			'serv2pack'=>array(
				self::HAS_MANY,
				'Serv2pack',
				'serv_id'
			),
				
			'Packages'=>array(
				self::MANY_MANY,
				'Package',
				'serv2pack(serv_id, pack_id)'
			),
		);
	}

	/**
	 * ограничение области запроса
	 * @return array
	 */
	public function scopes() {
		return array(
			'actual'=>array(
				'condition'=>'t.parent_id >= 0',
				'order'=>'t.sort_order ASC',
			),
		);
	}

	public function getTitle() {
		if($this->description) {
			return $this->description->title;
		}
		return $this->name;
	}
}
