<?php
/**
 * @property integer $serv_id
 * @property integer $pack_id
 * @property integer $quant
 * @property integer $price
 * @property string $descr
 * @property integer $duration
 * @property integer $master_id
 * @property string $dt_beg
 * @property string $dt_end
 * @property Package $package
 * @property Service $service
 * @property People $master
 */
class Serv2pack extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'serv2pack';
	}
	
	public function relations() {
		return array(
			'package'=>array(
				self::BELONGS_TO,
				'Package',
				'pack_id'
			),
				
			'service'=>array(
				self::BELONGS_TO,
				'Service',
				'serv_id'
			),
				
			'master'=>array(
				self::BELONGS_TO,
				'People',
				'master_id'
			),
		);
	}

	public function getSumm() {
		return $this->quant*$this->price;
	}
	
	public static function getByIds($serv_id, $pack_id) {
		return self::model()->findByPk(array(
			'serv_id'=>$serv_id,'pack_id'=>$pack_id
		));
	}
	
	public static function delByPack($pack_id) {
		self::model()->deleteAllByAttributes(array(
			'pack_id'=>$pack_id
		));
	}
}
