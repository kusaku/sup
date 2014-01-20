<?php

class PackageDescriptionUpdate implements IModelEvent {
	/**
	 * @var Package $obCaller
	 */
	private $obCaller;
	private $sDescription;
	private $bFound;
	private $bNew;

	public function init(CModel $caller) {
		$this->obCaller=$caller;
		$this->bFound=false;
	}

	public function afterSave() {
		//Обработка инфокода
		if($this->bFound) {
			if($this->obCaller->descr!=$this->sDescription) {
				$arNotification=array(
					'client_id'=>$this->obCaller->client_id,
					'manager_id'=>Yii::app()->user->id,
					'dt'=> date('Y-m-d H:i:s')
				);
				if($this->obCaller->descr=='') {
					$arNotification['info']='[auto] Удалено описание заказа #'.$this->obCaller->id.'.';
				} else {
					$arNotification['info']='[auto] Отредактировано описание заказа #'.$this->obCaller->id.':<br/>'.nl2br(strip_tags($this->obCaller->descr));
				}
				Logger::put($arNotification);
			}
		} elseif($this->bNew && $this->obCaller->descr!='') {
			$arNotification=array(
				'client_id'=>$this->obCaller->client_id,
				'manager_id'=>Yii::app()->user->id,
				'info'=>'[auto] Создано описание заказа #'.$this->obCaller->id.':<br/>'.nl2br(strip_tags($this->obCaller->descr)),
				'dt'=> date('Y-m-d H:i:s')
			);
			Logger::put($arNotification);
		}
		return true;
	}

	public function beforeSave() {
		if($this->obCaller->id>0) {
			if($obPackage=Package::model()->findByPk($this->obCaller->id)) {
				$this->sDescription=$obPackage->descr;
				$this->bFound=true;
			}
		} else {
			$this->bNew=true;
		}
		return true;
	}
}