<?php
/**
 * @property integer $id
 * @property integer $client_id
 * @property string $code
 *
 * @property Package[] $package
 * @property People $people
 */
class Promocode extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'promocode';
	}
	
	public function relations() {
		return array(
			'people'=>array(
				self::BELONGS_TO,
				'People',
				'client_id'
			),
				
			'package'=>array(
				self::HAS_MANY,
				'Package',
				'promocode_id'
			),
		);
	}
}
