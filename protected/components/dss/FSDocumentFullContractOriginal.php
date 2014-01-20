<?php
/**
 * Класс отражает документ - оригинал приложения деталей к договору
 */
 
class FSDocumentFullContractOriginal extends FSDocumentFullContract {
	protected $sType='contractFullOriginal';
	
	/**
	 * Конструктор инициализует внутренние поля документа и подготавливает их для дальнейшей
	 * обработки. Также если не заданы данные, он заполняет их значениями по умолчанию.
	 */
	function __construct($obStorage,$obPackage,array $arApplications=array()) {
		parent::__construct($obStorage,$obPackage);
		$this->obPackage=$obPackage;
		$this->obContract=$this->obStorage->createContractOriginal($this->obPackage);
		if(count($arApplications)==0)
			$this->arApplications=$this->obContract->getApplications();
		else
			$this->arApplications=$arApplications;
	}

	public function getTitle() {
		return 'Оригинал полного договора №'.$this->obPackage->getNumber();
	}
}