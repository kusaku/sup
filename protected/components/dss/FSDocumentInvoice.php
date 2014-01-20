<?php
/**
 * Класс отражает документ - контракт между клиентом и компанией
 */
 
class FSDocumentInvoice extends FSPDFDocument implements IDocumentHtmlResult {
	const VERSION='3';
	protected $sType='bill';
	protected $obPackage;
	protected $obJurPerson;
	protected $obClientJurPerson;
	protected $arServices;
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
			$this->obJurPerson=new JurPersonReference();
		}
		//Иницилизируем состав услуги
		if(is_array($this->obPackage->servPack)) {
			$this->arServices=array();
			foreach ($this->obPackage->servPack as $servPack) {
				$arRow=array(
					'id'=>$servPack->serv_id,
					'name'=>$this->getServiceName($servPack),
					'price'=>$servPack->price,
					'quant'=>$servPack->quant
				);
				$this->arServices[]=$arRow;
			}
		}
	}

	/**
	 * Метод подбирает подходящее имя для услуги
	 * @param Serv2Pack $obServPack
	 */
	protected function getServiceName($obServPack) {
		if(in_array($obServPack->serv_id,array(31,38,139))) {
			return $obServPack->descr; //Описание из поля описание для нестандартных услуг
		}
		if($obServPack->descr!='' && in_array($obServPack->serv_id,array(96,97,98,99,100,101,102,103,104,105,106,107))) {
			return $obServPack->descr; //Описание из поля описание для нестандартных услуг
		}
		if($obDescription=$obServPack->service->description) {
			if($obDescription->document_title!='')
				return $obDescription->document_title;
			elseif($obDescription->title!='')
				return $obDescription->title;
		}
		return $obServPack->service->name;
	}

	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/document/invoice');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}

	public function getTitle() {
		return 'Счёт №'.$this->obPackage->getNumber();
	}

	/**
	 * Функция генерирует хэш на основании данных шаблона
	 */
	public function getHash() {
		if($this->sHash=='') {
			$arData=array();
			$arData['package_id']=$this->obPackage->getNumber();
			foreach($this->obClientJurPerson->attributes as $key=>$value) 
				if(in_array($key,$this->arHashableFields))
					$arData['client_'.$key]=$value;
			foreach($this->obJurPerson->attributes as $key=>$value) 
				if(in_array($key,$this->arHashableFields))
					$arData['fs_'.$key]=$value;
			foreach($this->arServices as $arService)
				foreach($arService as $key=>$value)
					$arData['serv_'.$arService['id'].'_'.$key]=$value;
			ksort($arData);
			array_push($arData,self::VERSION);
			$this->setHash(md5(join('|',$arData)));
		}
		return $this->sHash;
	}
	
	public function getAsHtml() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		} elseif($obController=$this->getController()) {
			$obController->createAction('invoice')->runWithParams(array('package'=>$this->obPackage,'jur_person'=>$this->obJurPerson,'client_jur_person'=>$this->obClientJurPerson,'services'=>$this->arServices));
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
			if($this->obPackage->invoice) 
				$this->obStorage->linkToInvoice($obDocMeta,$this->obPackage->invoice->id);
		}
		return $obDocMeta;
	}
}
