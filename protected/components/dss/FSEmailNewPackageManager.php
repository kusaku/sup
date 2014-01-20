<?php
/**
 * Класс обеспечивает подготовку и отправку письма о создании заказа администратором
 */
class FSEmailNewPackageManager extends FSEmailNewPackage {
	protected $sType='emailNewPackageManager';

	function __construct($obStorage,$obPackage) {
		parent::__construct($obStorage,$obPackage);
	}

	function getTitle() {
		return 'Ваш заказ создан.';
	}

	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 * @return CController
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/email/newpackagemanager');
		if(is_array($arResult)) {
			return $arResult;
		}
		return null;
	}
}
