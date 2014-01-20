<?php
/**
 * Класс обеспечивает подготовку и отправку письма о регистрации пользователя
 */

class FSEmailPartnerManagerRegister extends FSEmail {
	protected $sType='emailPartnerManagerRegister';
	protected $obUser;
	protected $obManager;

	function __construct($obStorage,$obUser,$obManager) {
		parent::__construct($obStorage);
		$this->obUser=$obUser;
		$this->obManager=$obManager;
	}

	function getTitle() {
		return 'Появился новый партнер';
	}

	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/email/partnermanagerregister');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}

	public function getHash() {
		if($this->sHash=='')
			$this->setHash(md5($this->obUser->mail.'|'.$this->obManager->mail.'|'.time()));
		return $this->sHash;
	}

	/**
	 * Метод выполняет генерацию текста письма и его сохранение как нового документа.
	 */
	public function _getAsHtml() {
		if($obController=$this->getController()) {
			$obController->createAction('partnermanagerregister')->runWithParams(array('user'=>$this->obUser,'manager'=>$this->obManager));
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
			$this->obStorage->linkToPeople($obDocMeta,$this->obManager->id);
		}
		return $obDocMeta;
	}
}
