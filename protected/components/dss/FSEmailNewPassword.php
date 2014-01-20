<?php
/**
 * Класс обеспечивает подготовку и отправку письма о регистрации пользователя
 */

class FSEmailNewPassword extends FSEmail {
	protected $sType='emailNewPassword';
	protected $obUser;
	protected $obRequest;
	protected $obApplication;
	
	function __construct($obStorage,$obRequest,$obApplication,$obUser) {
		parent::__construct($obStorage);
		$this->obUser=$obUser;
		$this->obRequest=$obRequest;
		$this->obApplication=$obApplication;
	}
	
	function getTitle() {
		return 'Восстановление пароля.';
	}
	
	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/email/newpassword');
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
			$this->setHash(md5($this->obUser->id.'|'.$this->obUser->mail.'|'.$this->getTitle().'|'.$this->obRequest->code.'|'.$this->obApplication->id));
		return $this->sHash;
	}
	
	/**
	 * Метод выполняет генерацию текста письма и его сохранение как нового документа.
	 */
	public function _getAsHtml() {
		if($obController=$this->getController()) {
			$obController->createAction('newpassword')->runWithParams(array('model'=>$this->obRequest,'application'=>$this->obApplication,'user'=>$this->obUser));
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
			$this->obStorage->linkToPeople($obDocMeta,$this->obUser->id);
		}
		return $obDocMeta;
	}
}
