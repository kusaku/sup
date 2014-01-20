<?php

class FSDocumentOffertApplication extends FSPDFDocument implements IDocumentHtmlResult {
	const VERSION='3';
	protected $sType='offertApplication';
	protected $obPackage;
	protected $obProduct;
	protected $arServices;
	protected $arHashableFields=array('title','director_position','director_fio','director_source','director_source_info','inn','kpp','address','settlement_account','bank_title','correspondent_account','bank_bik','stamp_url','sign_url');
	
	/**
	 * Конструктор инициализует внутренние поля документа и подготавливает их для дальнейшей
	 * обработки. Также если не заданы данные, он заполняет их значениями по умолчанию.
	 */
	function __construct($obStorage,$obPackage) {
		parent::__construct($obStorage);
		$this->obPackage=$obPackage;
		if($this->obProduct=$this->obPackage->getProductEx(true)) {
			//Иницилизируем состав услуги
			if(is_array($this->obPackage->servPack)) {
				$this->arServices=array();
				foreach ($this->obPackage->servPack as $obServiceData) {
					if($obServiceData->serv_id==$this->obProduct->serv_id) continue;
					$arRow=array(
						'id'=>$obServiceData->serv_id,
						'title'=>$this->getServiceName($obServiceData)
					);
					if($arDescriptions=$obServiceData->service->descriptions) {
						$arRow['days']=$arDescriptions[0]->days;
					} else {
						$arRow['days']='';
					}
					$arRow['price']=$obServiceData->price;
					$arRow['quant']=$obServiceData->quant;
					$this->arServices[]=$arRow;
				}
			}
		} else {
			$this->obProduct=false;
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
		$arResult=Yii::app()->createController('documents/document/offertApplication');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}

	public function getTitle() {
		return 'Приложение к оферте №'.$this->obPackage->getNumber();
	}

	/**
	 * Функция генерирует хэш на основании данных шаблона
	 */
	public function getHash() {
		if($this->sHash=='') {
			$arData=array();
			if($arDescriptions=$this->obProduct->service->descriptions) {
				$arData['product_title']=$arDescriptions[0]->title;
				$arData['product_days']=$arDescriptions[0]->days;
			} else {
				$arData['product_title']=$this->obProduct->service->name;
				$arData['product_days']='';
			}
			$arData['package_id']=$this->obPackage->id;
			$arData['product_price']=$this->obProduct->price;
			$arData['product_quant']=$this->obProduct->quant;
			foreach($this->arServices as $id=>$arService)
				foreach($arService as $key=>$value)
					$arData['serv_'.$id.'_'.$key]=$value;
			ksort($arData);
			$this->setHash(md5(join('|',$arData)));
		}
		return $this->sHash;
	}
		
	public function getAsHtml() {
		if(!$this->obProduct) return 'No product';
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		} elseif($obController=$this->getController()) {
			$obController->createAction('offertApplication')->runWithParams(array('package'=>$this->obPackage,'product'=>$this->obProduct,'services'=>$this->arServices));
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
		if(!$this->obProduct) return null;
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
		return array();
	}
}
