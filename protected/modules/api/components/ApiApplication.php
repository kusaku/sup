<?php
/**
 * Класс реализует интерфейс IWebUser и обеспечивает управление авторизацие приложений в системе
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 14.05.2012
 */

class ApiApplication implements IWebUser {
	static private $arParams=array('login_field','new_people_pgroup_id','new_people_auth_item');
	private $iId=0;
	private $sName='';
	private $obApplicationRecord=false;
	
	function __construct($iId=0,$sName='') {
		$this->iId=intval($iId);
		$this->sName=$sName;
		$this->_init();
	}
	
	/**
	 * Метод выполняет инициализацию внутренних записей
	 */
	private function _init() {
		$this->obApplicationRecord=FSAPIApplications::model()->findByPk($this->iId);
	}
	
	public function checkAccess($operation, $params=array()) {
		if($this->getIsGuest())
			return Yii::app()->getModule('api')->getApplicationAuth()->getAuthItem('guest_application')->checkAccess($operation,$params);
		return Yii::app()->getModule('api')->getApplicationAuth()->checkAccess($operation,$this->iId,$params);
	}
	
	public function getId() {
		return $this->iId;
	}
	
	public function getIsGuest() {
		return $this->iId==0;
	}
	
	public function getName() {
		if($this->getIsGuest())
			return $this->sName;
		return $this->obApplicationRecord->title;
	}
	
	/**
	 * Метод возвращает настройку приложения
	 */
	public function getParameter($name,$default='') {
		if($this->getIsGuest())
			return $default;
		if(in_array($name,self::$arParams))
			return $this->obApplicationRecord->$name;		
	}
	
	/**
	 * Метод устанавливает настройку приложения
	 */
	public function setParameter($name,$value) {
		if($this->getIsGuest()) return;
	}
}
