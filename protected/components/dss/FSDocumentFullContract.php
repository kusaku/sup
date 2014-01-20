<?php
/**
 * Класс отражает документ - контракт между клиентом и компанией
 */

class FSDocumentFullContract extends FSPDFDocument implements IDocumentHtmlResult {
	const VERSION='2';
	protected $sType = 'contractFull';
	protected $obContract;
	protected $arApplications;
	protected $obPackage;

	/**
	 * Конструктор инициализует внутренние поля документа и подготавливает их для дальнейшей
	 * обработки. Также если не заданы данные, он заполняет их значениями по умолчанию.
	 */
	function __construct($obStorage, $obPackage,array $arApplications=array()) {
		parent::__construct($obStorage);
		$this->obPackage=$obPackage;
		$this->obContract=$this->obStorage->createContract($this->obPackage);
		if(count($arApplications)==0)
			$this->arApplications=$this->obContract->getApplications();
		else
			$this->arApplications=$arApplications;
	}

	public function getTitle() {
		return 'Полный договор №'.$this->obPackage->getNumber();
	}

	public function getHash() {
		if($this->sHash=='') {
			$sHash=$this->obContract->getHash().'|';
			foreach($this->arApplications as $obApplication)
				if($obApplication->storage_format=='html')
					$sHash.=$obApplication->md5summ.'|';
			$sHash.='|'.self::VERSION;
			$this->setHash(md5($sHash));
		}
		return $this->sHash;
	}
	
	public function getAsHtml() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		}
		$sContent=$this->obContract->getAsHtml();
		foreach($this->arApplications as $obApplication) 
			if($obApplication->storage_format=='html')
				$sContent.='<pagebreak resetpagenum="1" />'.$this->obStorage->loadDocumentFromDisk($obApplication);
		$this->store($sContent,'html');
		return $sContent;
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
			if($this->obPackage->client_id>0) $this->obStorage->linkToPeople($obDocMeta,$this->obPackage->client_id);
			if($this->obPackage->manager_id>0) $this->obStorage->linkToPeople($obDocMeta,$this->obPackage->manager_id);
			$this->obStorage->linkToPackage($obDocMeta,$this->obPackage->id);
		}
		return $obDocMeta;
	}
}
