<?php
/**
 * Класс обеспечивает подготовку и отправку письма о создании заказа пользователем
 */
class FSEmailPackagePayed extends FSEmail {
	protected $sType='emailPackagePayed';
	protected $obUser;
	protected $obPackage;

	function __construct($obStorage,$obPackage) {
		parent::__construct($obStorage);
		$this->obPackage=$obPackage;
		$this->obUser=$this->obPackage->client;
	}

	function getTitle() {
		return 'Смена статуса Вашего заказа.';
	}

	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 * @return CController
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/email/packagepayed');
		if(is_array($arResult)) {
			return $arResult;
		}
		return null;
	}

	/**
	 * Вообще странно, хэши будут часто совпадать, но если этого не сделать - они никогда не совпадут
	 */
	public function getHash() {
		if($this->sHash=='')
			$this->setHash(md5($this->obUser->id.'|'.$this->obUser->mail.'|'.$this->getTitle().'|'.$this->obPackage->id.'|'.time()));
		return $this->sHash;
	}

	/**
	 * Метод выполняет генерацию текста письма и его сохранение как нового документа.
	 */
	public function _getAsHtml() {
		if($arController=$this->getController()) {
			$arController[0]->createAction($arController[1])->runWithParams(array('user'=>$this->obUser,'package'=>$this->obPackage));
			$sContent=$arController[0]->getOutput();
			$this->store($sContent,'html');
			return $sContent;
		}
		throw new Exception(20,'SYSTEM_DOCUMENT_TEMPLATE_NOT_FOUND');
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
			$this->obStorage->linkToPackage($obDocMeta,$this->obPackage->id);
			if($this->obPackage->manager_id>0) {
				$this->obStorage->linkToPeople($obDocMeta,$this->obPackage->manager_id);
			}
		}
		return $obDocMeta;
	}
}
