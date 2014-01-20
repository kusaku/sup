<?php
/**
 * Класс обеспечивает подготовку и отправку письма о регистрации пользователя
 */

class FSEmailEmailPartnerManagerWithdrawNotification extends FSEmail {
	protected $sType='emailPartnerManagerWithdraw';
	protected $obUser;
	protected $obWithdraw;

	function __construct($obStorage,$obWithdraw) {
		parent::__construct($obStorage);
		$this->obWithdraw=$obWithdraw;
		$this->obUser=$this->obWithdraw->partner_data->manager;
	}

	function getTitle() {
		return 'Запрос на вывод средств.';
	}

	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/email/partnermanagerwithdraw');
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
			$this->setHash(md5($this->obUser->id.'|'.$this->obUser->mail.'|'.$this->getTitle().'|'.$this->obWithdraw->id.'|'.$this->obWithdraw->status.'|'.$this->obWithdraw->summ));
		return $this->sHash;
	}

	/**
	 * Метод выполняет генерацию текста письма и его сохранение как нового документа.
	 */
	public function _getAsHtml() {
		if($obController=$this->getController()) {
			$obController->createAction('partnermanagerwithdraw')->runWithParams(array('withdraw'=>$this->obWithdraw,'user'=>$this->obUser));
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
			$this->obStorage->linkToPeople($obDocMeta,$this->obWithdraw->partner_data->manager->id); //Привязываем к менеджеру
		}
		return $obDocMeta;
	}
}
