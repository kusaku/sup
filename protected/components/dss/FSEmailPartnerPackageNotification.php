<?php
/**
 * Класс обеспечивает подготовку и отправку письма о регистрации пользователя
 */

class FSEmailPartnerPackageNotification extends FSEmail {
	protected $sType='emailPartnerPackage';
	protected $obUser;
	protected $obPackage;
	
	function __construct($obStorage,$obPackage) {
		parent::__construct($obStorage);
		$this->obPackage=$obPackage;
		$this->obUser=$this->obPackage->client->owner_partner->partner;
	}
	
	function getTitle() {
		return 'Обновление статуса заказа.';
	}
	
	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/email/partnerpackage');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}
	
	/**
	 * Вообще странно, хэши будут часто совпадать, но если этого не сделать - они никогда не совпадут
	 */
	public function getHash() {
		if($this->sHash=='')
			$this->setHash(md5($this->obUser->id.'|'.$this->obUser->mail.'|'.$this->getTitle().'|'.$this->obPackage->id.'|'.$this->obPackage->status_id.'|'.$this->obPackage->payment_id));
		return $this->sHash;
	}
	
	/**
	 * Метод выполняет генерацию текста письма и его сохранение как нового документа.
	 */
	public function _getAsHtml() {
		if($obController=$this->getController()) {
			$obController->createAction('partnerpackage')->runWithParams(array('package'=>$this->obPackage,'user'=>$this->obUser));
			$sContent=$obController->getOutput();
			$this->store($sContent,'html');
			return $sContent;
		}
		throw new Exception(20,'SYSTEM_DOCUMENT_TEMPLATE_NOT_FOUND');
	}
	
	/**
	 * Метод возвращает письмо в виде HTML
	 */
	public function getAsHtml() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		}
		return $this->_getAsHtml(); 
	}
	
	/**
	 * Метод выполняет сохранение документа в хранилище. При этом также создаются связи между документом и пакетом, а также
	 * клиентом и менеджером.
	 * @param $sContent - содержимое документа
	 * @param $sFormat - формат документа
	 * @return Documents - объект описывающий метаданные документа
	 */
	protected function store($sContent,$sFormat='html') {
		if($obDocMeta=parent::store($sContent,$sFormat)) {
			$this->obStorage->linkToPeople($obDocMeta,$this->obUser->id); //Привязываем к партнёру
			if($this->obPackage->manager) $this->obStorage->linkToPeople($obDocMeta,$this->obPackage->manager->id); //Привязываем к менеджеру
			$this->obStorage->linkToPackage($obDocMeta,$this->obPackage->id); //Привязываем к пакету
		}
		return $obDocMeta;
	}
}
