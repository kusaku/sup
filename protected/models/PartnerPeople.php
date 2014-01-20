<?php 
/**
 * Модель работы с клиентами партнёра
 * @property integer $id_client
 * @property integer $id_partner
 *
 * @property People $client
 * @property People $partner
 */
class PartnerPeople extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'partner_people';
	}

	public function relations() {
		return array(
			'client'=>array(
				self::HAS_ONE,'People',array('id'=>'id_client')
			),
			'partner'=>array(
				self::HAS_ONE,'People',array('id'=>'id_partner')
			),
		);
	}
}
