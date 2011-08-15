<?php 
/*
 Класс таблицы
 */

class Payment extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'payment';
	}
	
	public function relations() {
		return array(
			'package'=>array(
				self::BELONGS_TO, 'Package', 'package_id'
			), 'rekviz'=>array(
				self::BELONGS_TO, 'Rekviz', 'rekvizit_id'
			),
		);
	}
	
	/**
	 * ограничение области запроса
	 * @return array
	 */
	public function scopes() {
		return array(
			// оплата клиентом
			'pay'=>array(
				'condition'=>'ptype_id = 1',
			),
			// процент менеджеру
			'percent'=>array(
				'condition'=>'ptype_id = 2',
			),
			// возврат
			'return'=>array(
				'condition'=>'debit = -1',
			),			
		);
	}
	
	/*
	 * Поиск
	 */
	public function getById($id) {
		return self::model()->findByPk(array(
			'condition'=>"id=$id", 'limit'=>1
		));
	}
	
}
?>
