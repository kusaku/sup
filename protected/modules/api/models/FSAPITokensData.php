<?php 
/**
 * Класс обеспечивает работу с таблицей api_tokens_data с использованием модели active_record
 */
class FSAPITokensData extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Метод возвращает имя таблицы
	 */
	public function tableName() {
		return 'api_tokens_data';
	}
}