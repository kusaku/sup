<?php

class EventableModel extends CActiveRecord {
	private $_arEventHandlers;

	public function init() {
		$this->_arEventHandlers=array();
	}

	public function addEventHandler($handler) {
		if(!array_key_exists($handler,$this->_arEventHandlers)) {
			$this->_arEventHandlers[$handler]=$handler;
		}
	}

	public function deleteEventHandler($handler) {
		if(array_key_exists($handler,$this->_arEventHandlers)) {
			unset($this->_arEventHandlers[$handler]);
		}
	}

	private function initHandler(&$handler) {
		$handler=new $handler;
		$handler->init($this);
	}

	/**
	 * Метод вызывается перед сохранением записи и выполняет вызов привязанных обработчиков
	 */
	protected function beforeSave() {
		if(parent::beforeSave()) {
			$bResult=true;
			foreach($this->_arEventHandlers as &$obHandler) {
				if(is_string($obHandler)) {
					$this->initHandler($obHandler);
				}
				$bResult&=$obHandler->beforeSave();
			}
			return $bResult;
		}
		return false;
	}

	/**
	 * Метод выполняется после сохранения записи о пакете и обновляет состояние мастера управления пакетом
	 */
	protected function afterSave() {
		parent::afterSave();
		foreach($this->_arEventHandlers as &$obHandler) {
			if(is_string($obHandler)) {
				$this->initHandler($obHandler);
			}
			$obHandler->afterSave();
		}
	}
}