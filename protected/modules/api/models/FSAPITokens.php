<?php 
/**
 * Класс обеспечивает работу с таблицей api_tokens с использованием модели active_record
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 14.05.2012
 */
class FSAPITokens extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Метод возвращает имя таблицы
	 */
	public function tableName() {
		return 'api_tokens';
	}
}