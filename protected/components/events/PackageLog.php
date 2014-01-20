<?php

class PackageLog implements IModelEvent {
	/**
	 * @var Package $obCaller
	 */
	private $obCaller;
	private $obPackage;
	private $bFound;
	private $bNew;

	public function init(CModel $caller) {
		$this->obCaller=$caller;
		$this->bFound=false;
	}

	public function afterSave() {
		//Обработка инфокода
		if($this->bFound) {
			$arNotification=array(
				'client_id'=>$this->obCaller->client_id,
				'manager_id'=>Yii::app()->user->id,
				'dt'=> date('Y-m-d H:i:s')
			);
			$arMessage=array();
			if($this->obCaller->descr!=$this->obPackage->descr) {
				if($this->obCaller->descr=='') {
					$arMessage[]='Удалено описание';
				} else {
					$arMessage[]='Отредактировано описание:<br/><span class="quote">'.nl2br(strip_tags($this->obCaller->descr)).'</span>';
				}
			}
			if($this->obCaller->status_id!=$this->obPackage->status_id) {
				$arMessage[]='Изменён статус c <b>'.$this->obPackage->wf_status->name.'</b> на <b>'.$this->obCaller->wf_status->name.'</b>';
			}
			if($this->obCaller->payment_id!=$this->obPackage->payment_id) {
				$arMessage[]='Изменён статус оплаты c <b>'.$this->obPackage->pay_status->name.'</b> на <b>'.$this->obCaller->pay_status->name.'</b>';
			}
			if($this->obCaller->manager_id!=$this->obPackage->manager_id) {
				if($this->obPackage->manager_id>0) {
					$arMessage[]='Заказа передан от менеджера <a href="#people_'.$this->obPackage->manager_id.'">'.$this->obPackage->manager->fio.'</a> к менеджеру <a href="#people_'.$this->obCaller->manager_id.'">'.$this->obCaller->manager->fio.'</a>';
				} else {
					$arMessage[]='Менеджер <a href="#people_'.$this->obCaller->manager_id.'">'.$this->obCaller->manager->fio.'</a> принял заказ';
				}
			}
			if($this->obCaller->summ!=$this->obPackage->summ) {
				$arMessage[]='Изменилась сумма заказа с '.LangUtils::money($this->obPackage->summ).' на '.LangUtils::money($this->obCaller->summ);
			}
			if($this->obCaller->paid!=$this->obPackage->paid) {
				$arMessage[]='Изменилась сумма оплаты заказа с '.LangUtils::money($this->obPackage->paid).' на '.LangUtils::money($this->obCaller->paid);
			}
			if($this->obCaller->jur_person_id!=$this->obPackage->jur_person_id) {
				if($this->obPackage->jur_person_id>0) {
					$arMessage[]='Изменилось юридическое лицо связанное с проектом с <b>'.$this->obPackage->jur_person->title.'</b> на <b>'.$this->obCaller->jur_person->title.'</b>';
				} else {
					$arMessage[]='Указано юридическое лицо связанное с проектом: <b>'.$this->obCaller->jur_person->title.'</b>';
				}
			}
			if($this->obCaller->rm_issue_id!=$this->obPackage->rm_issue_id) {
				if($this->obPackage->rm_issue_id>0) {
					$arMessage[]='Изменился номер задачи Redmine связанной с заказом с <b>'.$this->obPackage->rm_issue_id.'</b> на <b>'.$this->obPackage->rm_issue_id.'</b>';
				} else {
					$arMessage[]='Указан номер задачи Redmine связанный с заказом: <b>'.$this->obCaller->rm_issue_id.'</b>';
				}
			}
			if(count($arMessage)>0) {
				$arNotification['info']='[auto] В заказе #'.$this->obCaller->id.' произошли изменения:<ul><li>'.join('</li><li>',$arMessage).'</li></ul>';
				Logger::put($arNotification);
			}
		} elseif($this->bNew) {
			$arNotification=array(
				'client_id'=>$this->obCaller->client_id,
				'manager_id'=>Yii::app()->user->id,
				'info'=>'[auto] Создан заказ #'.$this->obCaller->id,
				'dt'=> date('Y-m-d H:i:s')
			);
			Logger::put($arNotification);
		}
		return true;
	}

	public function beforeSave() {

		if($this->obCaller->id>0) {
			if($this->obPackage=Package::model()->findByPk($this->obCaller->id)) {
				$this->bFound=true;
			}
		} else {
			$this->bNew=true;
		}
		return true;
	}
}