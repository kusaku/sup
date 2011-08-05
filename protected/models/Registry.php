<?php 
# (c) 2011 aks - FabricaSaitov.ru

/**
 * реестр, загружается при старте приложения
 * и сохраняет измененные значения при нормальном завершении
 *
 * использование:
 * Registry::model()->somename = 'somevalue';
 * Registry::model()->somearray = array(1,2,3,4);
 *
 * или можно так:
 * $my_reg = new Registry();
 * echo $my_reg->somename;
 * $my_reg->somename = 'anothervalue';
 *
 * для конфликтных имен:
 * Registry::setValue('very->conflict.name', 'myvalue');
 * Registry::getValue('very->conflict.name');
 *
 */
 
class Registry extends CActiveRecord {
	private static $_registry = array(
	);
	private static $_changed = array(
	);
	
	public $name = '';
	public $value = 'N;';
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'registry';
	}
	
	// переопределение 'магических' методов
	public function __get($name) {
		try {
			return parent::__get($name);
		}
		catch(Exception $e) {
			return self::getValue($name);
		}
	}
	public function __set($name, $value) {
		try {
			parent::__set($name, $value);
		}
		catch(Exception $e) {
			self::setValue($name, $value);
		}
	}
	
	// переопределение методов интерфейса ArrayAccess
	public function offsetExists($name) {
		try {
			return property_exists($this, $name);
		}
		catch(Exception $e) {
			return isset(self::$_registry[$name]);
		}
	}
	public function offsetGet($name) {
		try {
			return parent::offsetGet($name);
		}
		catch(Exception $e) {
			return self::getValue($name);
		}
	}
	public function offsetSet($name, $value) {
		try {
			parent::offsetSet($name, $value);
		}
		catch(Exception $e) {
			self::setValue($name, $value);
		}
	}
	public function offsetUnset($name) {
		parent::offsetUnset($name);
		self::setValue($name, null);
	}
	
	// загрузка - вызывается при запуске
	public static function registryLoad() {
		foreach (self::model()->findAll() as $record) {
			self::$_registry[$record->name] = $record;
		}
	}
	
	// выгрузка - вызывается при завершении
	public static function registrySave() {
		foreach (self::$_changed as $record) {
			$record->value == 'N;' ? $record->delete() : $record->save();
		}
	}
	
	// получение значения
	public static function getValue($name) {
		isset(self::$_registry[$name]) or self::$_registry[$name] = new Registry();
		return @unserialize(self::$_registry[$name]->value);
	}
	
	// установка значения
	public static function setValue($name, $value = null) {
		$value = serialize($value);
		isset(self::$_registry[$name]) or self::$_registry[$name] = new Registry();
		self::$_registry[$name]->name = $name;
		if (self::$_registry[$name]->value != $value) {
			self::$_registry[$name]->value = $value;
			array_push(self::$_changed, self::$_registry[$name]);
		}
	}
}

/**
 * реестр текущего пользователя
 */
class UserRegistry extends Registry {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	// переопределение 'магических' методов
	public function __get($name) {
		$name = 'users.'.Yii::app()->user->id.'.'.$name;
		return parent::__get($name);
	}
	public function __set($name, $value) {
		$name = 'users.'.Yii::app()->user->id.'.'.$name;
		parent::__set($name, $value);
	}
	
	// переопределение методов интерфейса ArrayAccess
	public function offsetExists($name) {
		$name = 'users.'.Yii::app()->user->id.'.'.$name;
		return parent::offsetExists($name);
	}
	public function offsetGet($name) {
		$name = 'users.'.Yii::app()->user->id.'.'.$name;
		parent::offsetGet($name);
	}
	public function offsetSet($name, $value) {
		$name = 'users.'.Yii::app()->user->id.'.'.$name;
		parent::offsetSet($name, $value);
	}
	public function offsetUnset($name) {
		$name = 'users.'.Yii::app()->user->id.'.'.$name;
		parent::offsetUnset($name);
	}
}
