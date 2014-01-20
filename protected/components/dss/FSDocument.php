<?php 
/**
 * Класс описывает универсальный документ, который ничего сам не отражает
 */
 
class FSDocument {
	protected $obStorage;
	protected $sType='user';
	protected $sHash;

	/**
	 * @param $obStorage FSDocumentsAPI хранилище обеспечивающее хранение документа
	 */
	function __construct($obStorage) {
		$this->obStorage=$obStorage;
		$this->sHash='';
	}
	
	public function getHash() {
		if($this->sHash=='')
			$this->setHash(md5(''));
		return $this->sHash;
	}
	
	public function setHash($hash) {
		$this->sHash=$hash;
	}
	
	public function getFilename() {
		return $this->getHash();
	}
	
	public function getType() {
		return $this->sType;
	}
	
	public function getTitle() {
		return 'Пользовательский документ';
	}
	
	/**
	 * Метод выполняет сохранение документа в хранилище
	 * @param $sContent - данные для сохранения
	 * @param $sFormat - формат сохранения
	 * @return string - путь к файлу
	 */
	protected function store($sContent,$sFormat='html') {
		$obDoc=$this->obStorage->createMetaRecord();
		$obDoc->type=$this->sType;
		$obDoc->title=$this->getTitle();
		$obDoc->date_create=date('Y-m-d H:i:s');
		$obDoc->date_edit=NULL;
		$obDoc->date_delete=NULL;
		$obDoc->md5summ=$this->getHash();
		$obDoc->created_by=0; //TODO Продумать как задавать эту величину
		$obDoc->storage_format=$sFormat;
		$obDoc->storage_name=$this->obStorage->saveDocumentOnDisk($sContent,$obDoc);
		if($obDoc->save())
			return $obDoc;
		return false;
	}
}
