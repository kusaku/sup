<?php
/**
 * Базовый класс обеспечивающий модель данных для взаимодействия с ISP Bill Manager
 */
abstract class BillManager extends CModel {
	/**
	 * @var $obBMConnection ISPConnection
	 */
	protected $obBMConnection;
	
	/**
	 * Метод устанавливает объект обеспечивающий соединение с BillManager
	 * @param $obConnection ISPConnection
	 */
	public function setConnection($obConnection) {
		$this->obBMConnection=$obConnection;
	}
}
