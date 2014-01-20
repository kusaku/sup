<?php
/**
 * Класс обеспечивает управление приложениями к договорам
 */
 
abstract class FSDocumentContractApplication extends FSPDFDocument implements IDocumentHtmlResult {
	protected $obContract;
	
	/**
	 * Метод привязывает текущее приложение к указанному контракту
	 */
	public function setContract($obContract) {
		if($obContractDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$obContract->getHash(),'type'=>$obContract->getType(),'storage_format'=>'html'))) {
			$this->obContract=$obContractDocument;
		} else {
			$this->obContract=false;
		}
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
			if($this->obContract) {
				$this->obStorage->linkToDocument($this->obContract,$obDocMeta->id);
			}
		}
		return $obDocMeta;
	}
}
