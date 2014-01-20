<?php
/**
 * Класс отражает документ - оригинал приложения деталей к договору
 */
 
class FSDocumentContractDetailsApplicationOriginal extends FSDocumentContractDetailsApplication {
	protected $sType='contractDetailsOriginal';
	
	/**
	 * Конструктор инициализует внутренние поля документа и подготавливает их для дальнейшей
	 * обработки. Также если не заданы данные, он заполняет их значениями по умолчанию.
	 */
	function __construct($obStorage,$obPackage) {
		parent::__construct($obStorage,$obPackage);
		$this->obJurPerson->stamp_url='';
		$this->obJurPerson->sign_url='';
	}

	public function getTitle() {
		return 'Оригинал приложения к договору №'.$this->obPackage->getNumber();
	}
}