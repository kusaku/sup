<?php
/**
 * Класс отражает документ - контракт между клиентом и компанией
 */
 
class FSDocumentFile extends FSDocument {
	protected $sType='userfile';
	protected $sFilename='';
	protected $sFilepath='';
	/**
	 * Конструктор инициализует внутренние поля документа и подготавливает их для дальнейшей
	 * обработки. Также если не заданы данные, он заполняет их значениями по умолчанию.
	 */
	function __construct($obStorage,$sFilename,$sFilepath) {
		parent::__construct($obStorage);
		$this->sFilename=$sFilename;
		$this->sFilepath=$sFilepath;
	}

	public function getTitle() {
		return 'Пользовательский файл: '.$this->sFilename;
	}

	/**
	 * Функция генерирует хэш на основании данных шаблона
	 */
	public function getHash() {
		if($this->sHash=='') {
			$this->setHash(md5_file($this->sFilepath));
		}
		return $this->sHash;
	}
		
	public function getAsFile() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'raw'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		} elseif($this->sFilepath!='' && file_exists($this->sFilepath)) {
			$sContent=file_get_contents($this->sFilepath);
			$this->store($sContent,'raw');
			return $sContent;
		}
		throw new Exception(20,'SYSTEM_DOCUMENT_FILE_NOT_FOUND');
	}
	
	public function storeAndGet() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'raw'))) {
			return $obOldDocument;
		} elseif($this->sFilepath!='' && file_exists($this->sFilepath)) {
			$sContent=file_get_contents($this->sFilepath);
			return $this->store($sContent,'raw');
		}
		throw new Exception(20,'SYSTEM_DOCUMENT_FILE_NOT_FOUND');
	}
	
	/**
	 * Метод выполняет сохранение документа в хранилище. При этом также создаются связи между документом и пакетом, а также
	 * клиентом и менеджером.
	 * @param $sContent - содержимое документа
	 * @param $sFormat - формат документа
	 * @return Documents - объект описывающий метаданные документа
	 */
	protected function store($sContent,$sFormat='raw') {
		return parent::store($sContent,$sFormat);
	}
}
