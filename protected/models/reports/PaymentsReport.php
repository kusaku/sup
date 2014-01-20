<?php

class PaymentsReport extends Report {
	protected $manager_id;
	protected $mode;

	public function __construct($date_from,$date_to,$manager_id=0) {
		$this->manager_id=$manager_id;
		parent::__construct($date_from,$date_to);
		$mode='';
	}

	protected function getCriteria() {
		$criteria = new CDbCriteria();
		$criteria->order = 'dt ASC';

		$criteria->scopes = array($this->mode);
		$criteria->compare('dt', '>='.date('Y-m-d 00:00:00', $this->date_from));
		$criteria->compare('dt', '<='.date('Y-m-d 23:59:59', $this->date_to));
		return $criteria;
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

		$criteria = $this->getCriteria();
		$payments = Payment::model()->findAll($criteria);

		$totalSumm = 0;
		$totalCount = 0;
		$totalExcluded = 0;
		$totalTotal = 0;

		foreach ($managers as $manager) {
			$pays = array();
			$managerSumm = 0;$managerExcluded=0;$managerTotal=0;

			// XXX как бы выбрать только оплаты текущего менеждера???
			foreach ($payments as $payment) {
				$package = $payment->package;
				if(isset($package->manager) and $manager->equals($package->manager)) {
					$obSumm=$package->getSum();
					$totalCount++;
					$managerSumm += $payment->amount * $payment->debit;
					$managerExcluded+=$obSumm->getExcluded();
					$managerTotal+=$payment->amount * $payment->debit-$obSumm->getExcluded();
					$arRow = array(
						'id'=>$package->id,
						'name'=> empty($package->name) ? "#{$package->primaryKey}" : $package->name,
						'site'=> empty($package->site->url) ? '' : $package->site->url,
						'client'=> empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio,
						'description'=>htmlspecialchars($payment->description),
						'mail'=> empty($package->client->mail) ? '' : $package->client->mail,
						'amount'=>$payment->amount * $payment->debit,
						'exclude'=>$obSumm->getExcluded(),
						'summ'=>$payment->amount * $payment->debit-$obSumm->getExcluded()
					);
					if($payment->ptype_id==1 && strtotime($payment->dt_pay)>PR_BUG_TIME)
						$arRow['dt']=date('d.m.Y',strtotime($payment->dt_pay));
					else
						$arRow['dt']=date('d.m.Y', strtotime($payment->dt));
					$pays[]=$arRow;
				}
			}

			$totalSumm += $managerSumm;
			$totalExcluded += $managerExcluded;
			$totalTotal += $managerTotal;
			$data[$manager->primaryKey] = array('name'=>$manager->fio,'pays'=>$pays,'count'=>count($pays),'summ'=>$managerSumm,'excluded'=>$managerExcluded,'total'=>$managerTotal);
		}

		$total = array('dt_beg'=>$this->date_from,'dt_end'=>$this->date_to,'count'=>$totalCount,'summ'=>$totalSumm,'excluded'=>$totalExcluded,'total'=>$totalTotal);
		return array('data'=>$data,'total'=>$total);
	}
}