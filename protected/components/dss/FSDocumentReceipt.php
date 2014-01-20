<?php
/**
 * Класс отражает документ - контракт между клиентом и компанией
 */
 
class FSDocumentReceipt extends FSPDFDocument implements IDocumentHtmlResult {
	const VERSION='2';
	protected $sType='receipt';
	protected $obPackage;
	protected $obJurPerson;
	protected $sTitle;
	private $arHashableFields=array('title','director_position','director_fio','director_source','director_source_info','inn','kpp','address','settlement_account','bank_title','correspondent_account','bank_bik','stamp_url','sign_url');
	
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
		$this->sTitle=$this->getReceiptTitle();
	}
	
	/**
	 * Метод задаёт назначение платежа
	 */
	protected function getReceiptTitle() {
		$sResult='Оплата заказа '.$this->obPackage->getNumber();
		if($arServices=$this->obPackage->services) {
			$arParams=array();
			foreach($arServices as $obService) {
				if($obService->parent_id==1)
					$arParams['site']=1;
				if(in_array($obService->parent_id,array(67,68,68,108,113)))
					$arParams['support']=1;
				if($obService->parent_id==118)
					$arParams['design']=1;
				if($obService->parent_id==138)
					$arParams['advert']=1;
			}
			if(isset($arParams['site']) && isset($arParams['support']))
				$sResult.=' - Услуги по разработке и поддержке сайта';
			elseif(isset($arParams['site']) && isset($arParams['advert']))
				$sResult.=' - Услуги по разработке и продвижению сайтов';
			elseif(!isset($arParams['site']) && isset($arParams['design']))
				$sResult.=' - Разработка дизайна';
			elseif(isset($arParams['site']))
				$sResult.=' - Услуги по разработке сайта';
			elseif(isset($arParams['advert']))
				$sResult.=' - Услуги по продвижению сайта';
			elseif(isset($arParams['support']))
				$sResult.=' - Услуги по поддержке сайта';
		}
		return $sResult;
	}

	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/document/receipt');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}

	public function getTitle() {
		return 'Квитанция на оплату №'.$this->obPackage->getNumber();
	}

	/**
	 * Функция генерирует хэш на основании данных шаблона
	 */
	public function getHash() {
		if($this->sHash=='') {
			$arData=array();
			foreach($this->obJurPerson->attributes as $key=>$value) 
				if(in_array($key,$this->arHashableFields))
					$arData['fs_'.$key]=$value;
			$arData['summ']=$this->obPackage->summ;
			$arData['package_id']=$this->obPackage->id;
			$arData['title']=$this->sTitle;
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
			$obController->createAction('receipt')->runWithParams(array('package'=>$this->obPackage,'jur_person'=>$this->obJurPerson,'title'=>$this->sTitle));
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
			if($this->obPackage->invoice) $this->obStorage->linkToInvoice($obDocMeta,$this->obPackage->invoice->id);
		}
		return $obDocMeta;
	}
}