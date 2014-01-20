<?php
/**
 * Класс отражает документ - контракт между клиентом и компанией
 */
 
class FSDocumentContractOriginal extends FSDocumentContract {
	protected $sType='contractOriginal';
	private $arHashableFields=array('title','director_position','director_fio','director_source','director_source_info','inn','kpp','address','settlement_account','bank_title','correspondent_account','bank_bik');
	
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
		return 'Оригинал договора №'.$this->obPackage->getNumber();
	}
}
