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
				'condition'=>'status_id != 15 AND status_id != 999'
			),
		);
	}
	
	/**
	 * ???
	 * @param object $id
	 * @return
	 */
	public static function updateById($id) {
		if ($id) {
			$pack = Package::model()->findByPk($id);
			$pack->dt_change = date('Y-m-d H:i:s');
			$pack->save();
			return true;
		} else {
			return false;
		}
	}
	
	public static function getById($id) {
		return self::model()->find(array(
			'condition'=>"id=$id", 'limit'=>1
		));
	}
	
	/**
	 * последние $count заказов текущего пользователя
	 * @param object $count [optional]
	 * @return array
	 */
	public static function getMy($count = 100) {
		return self::model()->findAll(array(
			'condition'=>'manager_id=0 OR manager_id='.Yii::app()->user->id,
			//
			//'group'=>'client_id', // С группировкой не работает.
			//
			'order'=>'dt_change DESC, dt_beg DESC', 'limit'=>$count
		));
	}
	
	/**
	 * последние $count заказов
	 * @param object $count [optional]
	 * @return array
	 */
	public static function getLast($count = 100) {
		return self::model()->findAll(array(
			//'group'=>'client_id', // С группировкой не работает.
			//
			'order'=>'dt_change DESC, dt_beg DESC', 'limit'=>$count
		));
	}
	
	/**
	 * Возвращает проекты менеджера
	 * @param int $manager_id [optional]
	 * @return Package
	 */
	public static function getProjects($manager_id = null) {
		isset($manager_id) or $manager_id = Yii::app()->user->id;
		return self::model()->findAll(array(
			'condition'=>"manager_id=$manager_id", 'order'=>'dt_change DESC, dt_beg DESC'
		));
	}
}
