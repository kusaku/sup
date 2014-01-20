<?php
/**
 * Класс отражает документ - контракт между клиентом и компанией
 */
 
class FSDocumentContract extends FSPDFDocument implements IDocumentHtmlResult {
	const VERSION='2';
	protected $sType='contract';
	protected $obPackage;
	protected $obJurPerson;
	protected $obClientJurPerson;
	private $arHashableFields=array('title','type','director_position','director_fio','director_source','director_source_info','inn','kpp','egrip','address','settlement_account','bank_title','correspondent_account','bank_bik','stamp_url','sign_url');
	
	/**
	 * Конструктор инициализует внутренние поля документа и подготавливает их для дальнейшей
	 * обработки. Также если не заданы данные, он заполняет их значениями по умолчанию.
	 */
	function __construct($obStorage,$obPackage) {
		parent::__construct($obStorage);
		$this->obPackage=$obPackage;
		//Инициализируем юридическое лицо компании
		if($this->obPackage->jur_person) {
			$this->obJurPerson=$this->obPackage->jur_person;
		} else {
			$this->obJurPerson=new JurPersonReference();
			$this->obJurPerson->attributes=JurPersonReference::fsgroupData();
		}
		//Инициализируем юридическое лицо клиента
		if($this->obPackage->client) {
			$obClient=$this->obPackage->client;
			if($obClient->jur_person) {
				$this->obClientJurPerson=$obClient->jur_person;
			} else {
				$this->obClientJurPerson=new JurPersonReference();
				$this->obClientJurPerson->attributes=JurPersonReference::convertPeopleAttributes($obClient);
			}
		} else {
			throw new CHttpException(500,'Package client not found');
		}
	}
	
	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/document/contract');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}

	public function getTitle() {
		return 'Договор №'.$this->obPackage->getNumber();
	}

	/**
	 * Функция генерирует хэш на основании данных шаблона
	 */
	public function getHash() {
		if($this->sHash=='') {
			$arData=array();
			$arData['type']=$this->sType;
			$arData['package_id']=$this->obPackage->id;
			foreach($this->obClientJurPerson->attributes as $key=>$value) 
				if(in_array($key,$this->arHashableFields))
					$arData['client_'.$key]=$value;
			foreach($this->obJurPerson->attributes as $key=>$value) 
				if(in_array($key,$this->arHashableFields))
					$arData['fs_'.$key]=$value;
			array_push($arData,self::VERSION);
			ksort($arData);
			$this->setHash(md5(join('|',$arData)));
		}
		return $this->sHash;
	}
		
	public function getAsHtml() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		} elseif($obController=$this->getController()) {
			$obController->createAction('contract')->runWithParams(array('package'=>$this->obPackage,'jur_person'=>$this->obJurPerson,'client_jur_person'=>$this->obClientJurPerson));
			$sContent=$obController->getOutput();
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
			if($this->obPackage->client_id>0) $this->obStorage->linkToPeople($obDocMeta,$this->obPackage->client_id);
			if($this->obPackage->manager_id>0) $this->obStorage->linkToPeople($obDocMeta,$this->obPackage->manager_id);
			$this->obStorage->linkToPackage($obDocMeta,$this->obPackage->id);
		}
		return $obDocMeta;
	}
	
	/**
	 * Метод выполняет поиск всех приложений к договору и возвращает их в виде массива
	 */
	public function getApplications() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
			return $obOldDocument->documents;
		}
		return array();
	}
}
