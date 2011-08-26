<?php 
/**
 *
 */
class Package extends CActiveRecord {
	/**
	 *
	 * @param object $className [optional]
	 * @return
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 *
	 * @return
	 */
	public function tableName() {
		return 'package';
	}
	
	/**
	 *
	 * @return
	 */
	public function relations() {
		return array(
			// связка с менеджером
			'manager'=>array(
				self::BELONGS_TO, 'People', 'manager_id'
			),
			// связка с клиентом
			'client'=>array(
				self::BELONGS_TO, 'People', 'client_id'
			),
			// связка с сервисами. Возврящает все сервися по этму пакету (заказу)
			'services'=>array(
				self::MANY_MANY, 'Service', 'serv2pack(pack_id, serv_id)'
			),
			// связка с сервисами. Возвращает все сервисы вместе с данными из serv2pack (blablabla->quant, blablabla->service->name)
			'servPack'=>array(
				self::HAS_MANY, 'Serv2pack', 'pack_id', 'with'=>'service'
			),
			// оплыты по заказу
			'payments'=>array(
				self::HAS_MANY, 'Payment', 'package_id'
			),
			// связка с сайтом
			'site'=>array(
				self::BELONGS_TO, 'Site', 'site_id'
			),
			// связка со статусами
			'status'=>array(
				self::BELONGS_TO, 'Status', 'status_id'
			),
		);
	}
	
	/**
	 * ограничение области запроса и порядка
	 * @return array
	 */
	public function scopes() {
		return array(
			'byclient'=>array(
				'order'=>'client_id ASC'
			), 'bychanged'=>array(
				'order'=>'dt_change ASC'
			), 'lastmonth'=>array(
				'condition'=>'dt_change > SUBDATE(NOW(), INTERVAL 1 MONTH)'
			), 'lastyear'=>array(
				'condition'=>'dt_change > SUBDATE(NOW(), INTERVAL 1 YEAR)'
			), 'active'=>array(
				'condition'=>'status_id NOT IN(15, 999)'
			),
		);
	}
}
