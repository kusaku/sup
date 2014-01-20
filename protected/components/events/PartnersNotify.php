<?php

class PartnersNotify implements IModelEvent {
	private $obCaller;
	/**
	 * Отправлять ли партнеру оповещение об изменении статуса заказа.
	 * @var boolean
	 */
	private $_bSendStatusNotification = false;

	public function init(CModel $caller) {
		$this->obCaller=$caller;
	}

	public function afterSave() {
		// отправляем оповещение партнерам об изменении статуса заказа
		if($this->_bSendStatusNotification && $this->obCaller->client->owner_partner){
			$this->_sendStatusNotification();
		}
	}

	public function beforeSave() {
		if (!$this->obCaller->isNewRecord) {
			if ($obOldValue = Package::model()->findByPk($this->obCaller->id)) {
				if ($this->obCaller->status_id != $obOldValue->status_id) {
					$this->_bSendStatusNotification = true;
				}
			}
		} else {
			$this->_bSendStatusNotification = true;
		}
		return true;
	}

	private function _sendStatusNotification() {
		//TODO: change after new partner workflow initiation
//		$post->to = $this->client->owner_partner->partner->mail;
		try {
			$obUser=$this->obCaller->client->owner_partner->partner;
			//Yii::app()->getComponent('documents')->createEmailPartnerPackageNotification($obPackage)->send($obUser->mail, $obUser->fio);
			/**
			 * @var FSDocumentsAPI $obDocuments
			 */
			$obDocuments=Yii::app()->getComponent('documents');
			if(!is_null($obDocuments)) {
				$obDocuments->createEmailPartnerPackageNotification($this->obCaller)->send('evgenia.l@fabricasaitov.ru', $obUser->fio);
			}
		} catch(exception $e) {}
	}
}