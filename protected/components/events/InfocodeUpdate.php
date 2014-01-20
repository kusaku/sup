<?php

class InfocodeUpdate implements IModelEvent {
	private $obCaller;

	public function init(CModel $caller) {
		$this->obCaller=$caller;
	}

	public function afterSave() {
		//Обработка инфокода
		if($this->obCaller->promocode_id>0) {
			$obInfocode = Infocode::model()->with('partner')->findByAttributes(array('value'=>$this->obCaller->promocode->code));
			if ($obInfocode) {
				//приписываем инфокод к заказу
				$this->obCaller->infocode_id = $obInfocode->primaryKey;
				//Приписываем инфокод к клиенту, если у него еще нет инфокода и если он не партнер
				if($this->obCaller->client_id>0) {
					if(!$this->obCaller->client->partner_data){
						if(!$this->obCaller->client->infocode){
							$this->obCaller->client->infocode_id = $obInfocode->primaryKey;
							$this->obCaller->client->save();
						}
					}
					//приписываем клиента партнеру, если он еще ни к кому не приписан
					if(!$this->obCaller->client->owner_partner && $obInfocode->partner){
						$obPartnerPeople = new PartnerPeople();
						$obPartnerPeople->id_client = $this->obCaller->client_id;
						$obPartnerPeople->id_partner = $obInfocode->partner['id'];
						$obPartnerPeople->save();
					}
				}
			}
		}
		return true;
	}

	public function beforeSave() {
		return true;
	}
}