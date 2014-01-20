<?php 
# (c) 2011 aks - FabricaSaitov.ru

/**
 * постоянное хранилище объектов,
 * ХХХ БУДЬ ВНИМАТЕЛЕН! ХХХ
 * СОХРАНЕНИЕ ОБЪЕКТОВ НЕМЕДЛЕННОЕ, ПОЛУЧЕНИЕ ОТЛОЖЕННОЕ
 * ХХХ БУДЬ ВНИМАТЕЛЕН! ХХХ
 *
 * пример использования:
 *
 * сохранение:
 * Persistent::model()->somename = 'somevalue';
 * Persistent::model()->somearray = array(1,2,3,4);
 *
 * или можно так:
 * $my_reg = new Persistent();
 * $my_reg->somename = 'anothervalue';
 *
 * получение:
 * $someobject = Persistent::model()->somename;
 * $someobject->somemethod();
 *
 * или можно так:
 * $my_reg = new Persistent();
 * echo $my_reg->somename;
 * $my_reg->somename = 'anothervalue';
 
 * для конфликтных имен:
 * Persistent::setValue('very->conflict.name', 'myvalue');
 * Persistent::getValue('very->conflict.name');
 *
 */
 
class Persistent extends CActiveRecord {
	private static $_loaded = array(
	);
	
	public $name = '';
	public $data = '87MGAA==';
	
	public static function model($className = __CLASS__) {
	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'persistent';
	}
	
	public function init() {
		self::model()->deleteAll('expires < NOW()');
	}
	
	// переопределение 'магических' методов
	public function __get($name) {
		try {
			return parent::__get($name);
		}
		catch(Exception $e) {
			return self::getData($name);
		}
	}
	public function __set($name, $data) {
		try {
			parent::__set($name, $data);
		}
		catch(Exception $e) {
			self::setData($name, $data);
		}
	}
	
	// переопределение методов интерфейса ArrayAccess
	public function offsetExists($name) {
		try {
			return property_exists($this, $name);
		}
		catch(Exception $e) {
			return !is_null(self::getData($name));
		}
	}
	public function offsetGet($name) {
		try {
			return parent::offsetGet($name);
		}
		catch(Exception $e) {
			return self::getData($name);
		}
	}
	public function offsetSet($name, $data) {
		try {
			parent::offsetSet($name, $data);
		}
		catch(Exception $e) {
			self::setData($name, $data);
		}
	}
	public function offsetUnset($name) {
		parent::offsetUnset($name);
		self::setData($name, null);
	}
	
	// получение значения
	public static function getData($name) {
		isset(self::$_loaded[$name]) or self::$_loaded[$name] = self::model()->findByPk($name) or self::$_loaded[$name] = new Persistent();
		return @unserialize(gzinflate(base64_decode(self::$_loaded[$name]->data)));
	}
	
	// установка значения
	public static function setData($name, $data = null, $expires = '+1 minutes') {
		isset(self::$_loaded[$name]) or self::$_loaded[$name] = self::model()->findByPk($name) or self::$_loaded[$name] = new Persistent();
		self::$_loaded[$name]->name = $name;
		self::$_loaded[$name]->data = base64_encode(gzdeflate(serialize($data), 9));
		self::$_loaded[$name]->expires = date('Y-m-d H:i:s', strtotime($expires));
		self::$_loaded[$name]->data == '87MGAA==' ? self::$_loaded[$name]->getIsNewRecord() or self::$_loaded[$name]->delete() : self::$_loaded[$name]->save();
	}
}


/**
 * Пока у нас нет PHP 5.3, исползуем этот вариант get_called_class(),
 * хотя он и создаёт много накладных расходов.
 * @link http://www.php.net/manual/en/function.get-called-class.php#93799
 */

/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if (!function_exists('get_called_class')) {
	function get_called_class($bt = false, $l = 1) {
		if (!$bt)
			$bt = debug_backtrace();
		if (!isset($bt[$l]))
			throw new Exception("Cannot find called class -> stack level too deep.");
		if (!isset($bt[$l]['type'])) {
			throw new Exception('type not set');
		} else {
			switch ($bt[$l]['type']) {
				case '::':
					$lines = file($bt[$l]['file']);
					$i = 0;
					$callerLine = '';
					do {
						$i++;
						$callerLine = $lines[$bt[$l]['line'] - $i].$callerLine;
					} while (stripos($callerLine, $bt[$l]['function']) === false);
					preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/', $callerLine, $matches);
					if (!isset($matches[1])) {
						// must be an edge case.
						throw new Exception("Could not find caller class: originating method call is obscured.");
					}
					switch ($matches[1]) {
						case 'self':
						case 'parent':
							return get_called_class($bt, $l + 1);
						default:
							return $matches[1];
					}
					// won't get here.
				case '->':
					switch ($bt[$l]['function']) {
						case '__get':
							// edge case -> get class of calling object
							if (!is_object($bt[$l]['object']))
								throw new Exception("Edge case fail. __get called on non object.");
							return get_class($bt[$l]['object']);
						default:
							return $bt[$l]['class'];
					}
					
				default:
					throw new Exception("Unknown backtrace method type");
			}
		}
	}
}


/**
 * реализация очереди объектов
 *
 * XXX надо бы добавить итераторы...
 *
 * использование:
 *
 * ObjectQueue:enQueue($object);
 * ...
 * $object = ObjectQueue:enQueue()
 *
 * чтобы создать новый тип очереди, надо
 * расширить класс ObjectQueue и использовать его:
 *
 * class MailQueue extends ObjectQueue {}
 *
 * MailQueue:enQueue($mail);
 *
 */
class ObjectQueue extends Persistent {

	/**
	 * поставить элемент в очередь
	 * @param object $object элемент
	 * @param object $filo [optional] поставить в начало очереди
	 * @return
	 */
	public static function enQueue($object, $filo = false) {
		$type = get_called_class();
		
		$name = $type.'.'.uniqid();
		$names = self::getData('Queue.'.$type) or $names = array(
		);
		
		if ($filo)
			array_unshift($names, $name);
		else
			array_push($names, $name);
			
		self::setData('Queue.'.$type, $names, '+10 years');
		self::setData('Queue.'.$type.'.length', count($names), '+10 years');
		self::setData($name, $object, '+10 years');
	}
	
	/**
	 * получить первый элемент
	 * @param object $lifo [optional] получить последний элемент
	 * @return
	 */
	public static function deQueue($lifo = false) {
		$type = get_called_class();
		
		if ($lifo)
			$names = self::getData('Queue.'.$type) and $name = array_pop($names) and $object = self::getData($name);
		else
			$names = self::getData('Queue.'.$type) and $name = array_shift($names) and $object = self::getData($name);
			
		if (isset($object)) {
			self::setData('Queue.'.$type, $names, '+10 years');
			self::setData('Queue.'.$type.'.length', count($names), '+10 years');
			self::setData($name, null);
			return $object;
		}
	}
	
	/**
	 * получить длину очереди
	 * @return
	 */
	public static function length() {
		$type = get_called_class();
		
		return self::getData('Queue.'.$type.'.length');
	}
	
	/**
	 * очистить очередь
	 * @return
	 */
	public static function clear() {
		$type = get_called_class();
		
		self::model()->deleteByPk(self::getData('Queue.'.$type));
		self::setData('Queue.'.$type, array(
		), '+10 years');
		self::setData('Queue.'.$type.'.length', 0, '+10 years');
	}
	
	/**
	 * полностью удалить очередь
	 * @return
	 */
	public static function remove() {
		$type = get_called_class();
		
		self::model()->deleteByPk(self::getData('Queue.'.$type));
		self::model()->deleteByPk('Queue.'.$type);
		self::model()->deleteByPk('Queue.'.$type.'.length');
	}
}

/**
 * реализация очереди отправки почты
 */
class MailQueue extends ObjectQueue {
}

/**
 * реализация очереди недоставленной почты
 */
class FailedMailQueue extends ObjectQueue {
}
