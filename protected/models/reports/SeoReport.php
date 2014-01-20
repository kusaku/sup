<?php

class SeoReport extends Report {
	private $status_id;
	private $payment_id;
	private $manager_id;
	private $show_empty;

	public function __construct($date_from,$date_to,$manager_id,$status_id,$payment_id,$show_empty) {
		parent::__construct($date_from,$date_to);
		$this->manager_id=$manager_id;
		$this->status_id=$status_id;
		$this->payment_id=$payment_id;
		$this->show_empty=$show_empty;
	}

	public function getData() {
		if ($this->manager_id>0) {
			// выберем переданного менеджера
			$managers = array(
				People::getById((int) $this->manager_id),
			);
		} else {
			// выберем менеджеров и старших менеджеров
			$managers = People::model()->findAllByAttributes(array('pgroup_id'=>array(3,4,5,8,11,12)));
		}
		$data = array();
		// установка критериев отбора
		$criteria = new CDbCriteria();
		// сортировка - берем из модели
		$criteria->scopes = array(
			//'byclient','bychanged'
		);
		// выборка по дате
		$criteria->compare('dt_beg', '>='.date('Y-m-d 00:00:00', $this->date_from));
		$criteria->compare('dt_beg', '<'.date('Y-m-d 23:59:59', $this->date_to));

		// если выбран статус заказа делаем выборку по нему
		if($this->status_id>0) {
			$criteria->addColumnCondition(array('status_id'=>$this->status_id));
		}
		// если выбран статус оплаты делаем выборку по нему
		if($this->payment_id>0) {
			$criteria->addColumnCondition(array('payment_id'=>$this->payment_id));
		}

		// выборка по периоду
		// Седрак просил переделать
		$criteriaPay = new CDbCriteria();
		// выберем все платежи
		$criteriaPay->scopes = array('recpay');

		$totalSumm = 0;
		$totalCount = 0;
		$totalExcluded = 0;
		$totalTotal = 0;

		foreach ($managers as $manager) {
			$packs = array();
			$managerSumm = 0;
			$managerCount = 0;
			$managerExcluded=0;$managerTotal=0;

			// клонируем критерий и добавляем к нему менеджера
			$criteriaPack = clone $criteria;
			$criteriaPack->addColumnCondition(array('manager_id'=>$manager->primaryKey));

			$payments = Payment::model()->with(array(
				'package'=>array(
					'joinType'=>'INNER JOIN',
					'scopes'=>$criteriaPack->scopes,
					'condition'=>$criteriaPack->condition,
					'params'=>$criteriaPack->params
				)
			))->findAll($criteriaPay);

			foreach ($payments as $payment) {
				$package = $payment->package;
				if (!($this->show_empty or count($package->servPack)))
					continue;
				$obSumm=$package->getSum();
				$managerSumm += $payment->amount*$payment->debit;
				$managerExcluded+=$obSumm->getExcluded();
				$managerTotal+=$payment->amount * $payment->debit-$obSumm->getExcluded();
				$managerCount++;

				$arRow = array(
					'client'=> empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio,
					'manager'=> empty($package->manager->fio) ? "#{$package->manager_id}" : $package->manager->fio,
					'mail'=> empty($package->client) ? '' : $package->client->mail,'name'=> empty($package->name) ? "#{$package->primaryKey}" : $package->name,
					'descr'=>$package->descr,'site'=> empty($package->site->url) ? '' : $package->site->url,
					'summ'=>$payment->amount*$payment->debit,'promocode'=> empty($package->promocode->code) ? '(не указан)' : $package->promocode->code,
					'type'=>isset($package->services) ? $package->services[0]->name : '(нет)',
					'id'=>$package->primaryKey,'status'=>$payment->ptype_id == 0 ? 'обещан' : 'подтвержден',
					'dt'=>$payment->dt != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($payment->dt)) : '(дата не указана)',
					'dt_beg'=>$package->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_beg)) : '(дата не указана)',
					'dt_end'=>$package->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_end)) : '(дата не указана)',
					'dt_change'=>$package->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_change)) : '(дата не указана)',
					'excluded'=>$obSumm->getExcluded(),'total'=>$payment->amount * $payment->debit-$obSumm->getExcluded()
				);
				if($payment->ptype_id==1 && strtotime($payment->dt_pay)>PR_BUG_TIME)
					$arRow['dt']=date('d.m.Y',strtotime($payment->dt_pay));
				else
					$arRow['dt']=date('d.m.Y', strtotime($payment->dt));
				$packs[] =$arRow;
			}

			$totalSumm += $managerSumm;
			$totalCount += $managerCount;
			$totalExcluded += $managerExcluded;
			$totalTotal += $managerTotal;

			$data[$manager->primaryKey] = array(
				'name'=>$manager->fio,'packs'=>$packs,'summ'=>$managerSumm,'count'=>$managerCount,'excluded'=>$managerExcluded,'total'=>$managerTotal
			);
		}

		$total = array('dt_beg'=>$this->date_from,'dt_end'=>$this->date_to,'summ'=>$totalSumm,'count'=>$totalCount,'excluded'=>$totalExcluded,'total'=>$totalTotal);
		return array('data'=>$data,'total'=>$total);
	}
}