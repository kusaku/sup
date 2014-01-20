<?php
/**
 * Базовый класс обеспечивающий работу с документами
 */

class FSDocumentsAPI extends CComponent {
	const STORAGE_PATH='/data/storage';
	/**
	 * @var $obModel Documents
	 */
	protected $obModel;
	protected $obLinkPeople;
	protected $obLinkDocument;
	protected $obLinkPackage;
	protected $obLinkInvoice;
	protected $obLinkPayment;
	protected $obLinkWaveAttachment;

	function init() {
		$this->obModel=Documents::model();
		$this->obLinkPeople=DocumentPeople::model();
		$this->obLinkDocument=DocumentDocument::model();
		$this->obLinkPackage=DocumentPackage::model();
		$this->obLinkInvoice=DocumentInvoice::model();
		$this->obLinkWaveAttachment=DocumentWaveAttachment::model();
		//$this->obLinkPayment=DocumentPayment::model();
	}

	/**
	 * Метод выполняет создание договора на создание сайта
	 */
	function createContract($package) {
		return new FSDocumentContract($this,$package);
	}

	/**
	 * Метод выполняет создание оригинала договора на создание сайта
	 */
	function createContractOriginal($package) {
		return new FSDocumentContractOriginal($this,$package);
	}

	/**
	 * Метод выполняет создание прложения к договору к пакету
	 */
	function createContractDetailsApplication($package) {
		return new FSDocumentContractDetailsApplication($this,$package);
	}

	/**
	 * Метод выполняет создание прложения к договору к пакету
	 */
	function createOffertApplication($package) {
		return new FSDocumentOffertApplication($this,$package);
	}

	/**
	 * Метод выполняет создание прложения к договору к пакету
	 */
	function createContractDetailsApplicationOriginal($package) {
		return new FSDocumentContractDetailsApplicationOriginal($this,$package);
	}

	/**
	 * Метод выполняет создание полной версии договора со всеми приложениями
	 */
	function createFullContract($package,array $arApplications=array()) {
		return new FSDocumentFullContract($this,$package,$arApplications);
	}

	/**
	 * Метод выполняет создание полной версии договора со всеми приложениями без печатей и подписей
	 */
	function createFullContractOriginal($package,array $arApplications=array()) {
		return new FSDocumentFullContractOriginal($this,$package,$arApplications);
	}

	/**
	 * Метод выполняет генерацию счёта на оплату
	 */
	function createInvoice($package) {
		return new FSDocumentInvoice($this,$package);
	}

	/**
	 * Метод выполняет создание квитанции на оплату заказа в банке
	 */
	function createReceipt($package) {
		return new FSDocumentReceipt($this,$package);
	}

	/**
	 * Метод выполняет создание квитанции на оплату заказа в банке
	 */
	function createAct($package) {
		return new FSDocumentAct($this,$package);
	}

	/**
	 * Метод выполняет генерацию письма о регистрации пользователя
	 */
	function createEmailRegister($obUser,$sPwd) {
		return new FSEmailRegister($this,$obUser,$sPwd);
	}

	/**
	 * Метод выполняет генерацию письма о регистрации партнера
	 */
	function createEmailPartnerRegister($obUser,$sPwd) {
		return new FSEmailPartnerRegister($this,$obUser,$sPwd);
	}

	/**
	 * Метод выполняет генерацию письма о регистрации партнера для менеджеров партнеров
	 */
	function createEmailPartnerManagerRegister($obUser,$obManager) {
		return new FSEmailPartnerManagerRegister($this,$obUser,$obManager);
	}

	/**
	 * Метод выполняет генерацию письма о создании заказа
	 */
	function createEmailNewPackage($obPackage) {
		return new FSEmailNewPackage($this,$obPackage);
	}

	/**
	 * Метод выполняет генерацию письма о создании заказа
	 */
	function createEmailPackagePayed($obPackage) {
		return new FSEmailPackagePayed($this,$obPackage);
	}

	/**
	 * Метод выполняет генерацию письма о создании заказа менеджером
	 */
	function createEmailNewPackageManager($obPackage) {
		return new FSEmailNewPackageManager($this,$obPackage);
	}

	/**
	 * Письмо о восстановлении пароля
	 */
	function createEmailNewPassword($obRequest, $obApplication, $obUser) {
		return new FSEmailNewPassword($this,$obRequest,$obApplication,$obUser);
	}

	/**
	 * Письмо уведомление о изменении статуса заказа партнёра
	 */
	function createEmailPartnerPackageNotification($obPackage) {
		return new FSEmailPartnerPackageNotification($this,$obPackage);
	}

	/**
	 * Письмо уведомление о заказе на вывод средств.
	 */
	function createEmailPartnerManagerWithdrawNotification($obWithdraw) {
		return new FSEmailEmailPartnerManagerWithdrawNotification($this,$obWithdraw);
	}

	/**
	 * Письмо уведомление партнера об обработке бухгалтером заказа на вывод средств.
	 */
	function createEmailPartnerWithdrawProcessed($obWithdraw) {
		return new FSEmailEmailPartnerWithdrawProcessed($this,$obWithdraw);
	}

	/**
	 * Письмо уведомление менеджера об обработке бухгалтером заказа на вывод средств.
	 */
	function createEmailPartnerManagerWithdrawProcessed($obWithdraw) {
		return new FSEmailEmailPartnerManagerWithdrawProcessed($this,$obWithdraw);
	}

	/**
	 * Метод выполняет генерацию письма о создании заказа
	 */
	function createEmailWaveNewPost($obPackage,$arPost) {
		return new FSEmailWaveNewPost($this,$obPackage,$arPost);
	}

	/**
	 * Метод выполняет генерацию письма о создании заказа
	 */
	function createEmailWaveNewFile($obPackage) {
		return new FSEmailWaveNewFile($this,$obPackage);
	}

	/**
	 * @param $obPeople
	 * @param $sPassword
	 */
	function createEmailSendPassword($obPeople,$sPassword) {
		return new FSEmailSendPassword($this,$obPeople,$sPassword);
	}

	/**
	 * Функция хранения файла в хранилище
	 */
	function createDocumentFile($sFilename,$sFilepath) {
		return new FSDocumentFile($this,$sFilename,$sFilepath);
	}

	function getModel() {
		return $this->obModel;
	}

	function createMetaRecord() {
		return new Documents;
	}

	function saveDocumentOnDisk($content,$obDocument) {
		$sFilehash=md5(join('|',$obDocument->attributes));
		$sDirpart=self::STORAGE_PATH.'/'.substr($sFilehash,0,3);
		$sDirname=Yii::app()->getBasePath().$sDirpart;
		if(!file_exists($sDirname)) {
			mkdir($sDirname,0755,true);
		}
		if(file_exists($sDirname) && is_dir($sDirname)) {
			file_put_contents($sDirname.'/'.$sFilehash, $content);
			return $sDirpart.'/'.$sFilehash;
		}
		throw new Exception(30,'SYSTEM_FILE_SAVE_ERROR');
	}

	function loadDocumentFromDisk($obDocument) {
		$sPath=Yii::app()->getBasePath().$obDocument->storage_name;
		if(file_exists($sPath)) {
			return file_get_contents($sPath);
		}
		throw new Exception(40,'SYSTEM_FILE_LOAD_ERROR');
	}

	function getDocumentFileInfo($obDocument) {
		$sPath=Yii::app()->getBasePath().$obDocument->storage_name;
		if(file_exists($sPath)) {
			$arResult=array(
				'size'=>filesize($sPath),
				'mime'=>CFileHelper::getMimeType($sPath)
			);
			return $arResult;
		}
		throw new Exception(40,'SYSTEM_FILE_LOAD_ERROR');
	}

	function linkToDocument($obDocument,$id) {
		if($this->obLinkDocument->findByAttributes(array('document_id'=>$obDocument->id,'linked_id'=>$id)))
			return true;
		$obLinkDocument=new DocumentDocument();
		$obLinkDocument->document_id=$obDocument->id;
		$obLinkDocument->linked_id=$id;
		return $obLinkDocument->save();
	}

	function linkToPeople($obDocument,$id) {
		if($this->obLinkPeople->findByAttributes(array('document_id'=>$obDocument->id,'people_id'=>$id)))
			return true;
		$obLinkPeople=new DocumentPeople();
		$obLinkPeople->document_id=$obDocument->id;
		$obLinkPeople->people_id=$id;
		return $obLinkPeople->save();
	}

	function linkToPackage($obDocument,$id) {
		if($this->obLinkPackage->findByAttributes(array('document_id'=>$obDocument->id,'package_id'=>$id)))
			return true;
		$obLinkPackage=new DocumentPackage();
		$obLinkPackage->document_id=$obDocument->id;
		$obLinkPackage->package_id=$id;
		return $obLinkPackage->save();
	}

	function linkToInvoice($obDocument,$id) {
		if($this->obLinkInvoice->findByAttributes(array('document_id'=>$obDocument->id,'invoice_id'=>$id)))
			return true;
		$obLinkInvoice=new DocumentInvoice();
		$obLinkInvoice->document_id=$obDocument->id;
		$obLinkInvoice->invoice_id=$id;
		return $obLinkInvoice->save();
	}

	function linkToWaveAttachment($obDocument,$id) {
		if($this->obLinkWaveAttachment->findByAttributes(array('document_id'=>$obDocument->id,'wave_attachment_id'=>$id)))
			return true;
		$obLinkWaveAttachment=new DocumentWaveAttachment();
		$obLinkWaveAttachment->document_id=$obDocument->id;
		$obLinkWaveAttachment->wave_attachment_id=$id;
		return $obLinkWaveAttachment->save();
	}
}
