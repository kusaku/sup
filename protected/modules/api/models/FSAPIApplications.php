<?php 
/**
 * Класс обеспечивает работу с таблицей api_applications с использованием модели active_record
 */
class FSAPIApplications extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Метод возвращает имя таблицы
	 */
	public function tableName() {
		return 'api_applications';
	}
	
	/**
	 * Метод проверяет ключ приложения
	 */
	public function checkKey($sKey) {
		return $this->code_hash==md5($sKey);
	}
}