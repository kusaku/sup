<?php
class ClientPaymentStatusNotify implements IModelEvent {
	/**
	 * @var Package
	 */
	private $obCaller;
	/**
	 * Отправлять ли клиенту оповещение об изменении статуса заказа.
	 * @var boolean
	 */
	private $_bSendStatusNotification;

	public function init(CModel $caller) {
		$this->obCaller=$caller;
		$this->_bSendStatusNotification=false;
	}

	public function afterSave() {
		// отправляем оповещение партнерам об изменении статуса заказа
		if($this->_bSendStatusNotification && $this->obCaller->client->owner_partner){
			$this->_sendStatusNotification();
		}
	}

	public function beforeSave() {
		if (!$this->obCaller->isNewRecord) {
			$obProduct=$this->obCaller->GetProduct();
			if(!is_null($obProduct) && in_array($obProduct->id,array(4,5,6,33,126,144,148,149,150))) {
				if ($obOldValue = Package::model()->findByPk($this->obCaller->id)) {
					if ($this->obCaller->payment_id != $obOldValue->payment_id && $this->obCaller->payment_id>=20) {
						$this->_bSendStatusNotification = true;
					}
				}
			}
		}
		return true;
	}

	private function _sendStatusNotification() {
		try {
			$obUser=$this->obCaller->client;
			/**
			 * @var FSDocumentsAPI $obDocuments
			 */
			$obDocuments=Yii::app()->getComponent('documents');
			if(!is_null($obDocuments)) {
				$obDocuments->createEmailPackagePayed($this->obCaller)->send($obUser->mail, $obUser->fio);
			}
		} catch(exception $e) {}
	}
}