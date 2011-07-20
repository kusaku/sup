<?php 
/*
 Класс таблицы
 */

class Logger extends CActiveRecord {
	/**
	 *
	 * @param object $className [optional]
	 * @return CActiveRecord
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'logger';
	}
	
	public function relations() {
		return array('client'=>array(self::BELONGS_TO, 'People', 'client_id'), 'manager'=>array(self::BELONGS_TO, 'People', 'manager_id'));
	}
	
	/**
	 *
	 * @param object $client_id
	 * @param object $manager_id
	 * @param object $info
	 * @return bool
	 */
	public function put($attributes) {
		$record = self::model();
		$record->setIsNewRecord(true);
		$record->client_id = (int) $attributes['client_id'];
		$record->manager_id = (int) $attributes['manager_id'];
		$record->info = (string) $attributes['info'];
		;
		$record->dt = date('Y-m-d h:i:s');
		if ($record->insert())
			return $record;
		else
			return null;
	}
	
	/**
	 * 
	 * @return 
	 */
	public function scopes() {
		return array('lastfirst'=>array('order'=>'dt DESC'));
	}

	
	/**
	 *
	 * @param object $var
	 * @return Logger
	 */
	public function get($var) {
		if (is_numeric($var)) {
			return self::model()->lastfirst()->findAllByAttributes(array('client_id'=>$var));
		}
		if (is_string($var)) {
			return self::model()->lastfirst()->findAllByAttributes(array('dt'=>date('Y-m-d h:i:s', strtotime($var))));
		}
	}
}
?>
