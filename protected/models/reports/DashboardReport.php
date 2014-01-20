<?php

class DashboardReport extends Report{
	private $arData;
	private $iPeopleId;
	private $sPeriod;
	private $sDtBeg;
	private $sDtEnd;

	public function __construct($date_from='',$period='',$people_id=0) {
		$request = Yii::app()->getRequest();

		if($people_id==0) {
			$this->iPeopleId = $request->getParam('people_id') ? (int) $request->getParam('people_id') : 0;
		} else {
			$this->iPeopleId=$people_id;
		}
		if($date_from=='') {
			$date = $request->getParam('date') ? (string) $request->getParam('date') : date('Y-m-d');
		} else {
			$date=$date_from;
		}
		if($period=='') {
			$this->sPeriod=$request->getParam('period') ? (string) $request->getParam('period') : 'day';
		} else {
			$this->sPeriod=$period;
		}

		$timestamp = strtotime($date);

		switch ($this->sPeriod) {
			case 'year':
				$this->sDtBeg = date('Y-01-01 00:00:00', $timestamp);
				break;
			case 'month':
				$this->sDtBeg = date('Y-m-01 00:00:00', $timestamp);
				break;
			case 'day':
				$this->sDtBeg = date('Y-m-d 00:00:00', $timestamp);
				break;
			case 'week':
				$this->sDtBeg = date('Y-m-d 00:00:00', strtotime('last mon', $timestamp));
				break;
			default:
				$this->sDtBeg = date('Y-m-d 00:00:00', strtotime($this->sPeriod));
				break;
		}
		// гранулярность - сутки, верхний порог - строго меньше, поэтому прибавляем 86399 секунд
		$this->sDtEnd = date('Y-m-d 23:59:59', $timestamp);
		parent::__construct($this->sDtBeg,$this->sDtEnd);
		$this->_initData();
	}

	private function _initData() {
		$arPropsArray=array(
			0=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			4=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			5=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			6=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			33=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			126=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			143=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			144=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			148=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			149=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			150=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			'shop'=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			'total'=>0,
			'summ'=>0,
			'summ_excluded'=>0,
			'summ_total'=>0
		);
		$this->arData=array(
			'managers'=>array(),
			'summary'=>array(
				'new'=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
				'paying'=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
				'paid'=>array('total'=>0,'summ'=>0,'summ_excluded'=>0,'summ_total'=>0),
			),
			'products'=>array(
				'new'=>$arPropsArray,
				'paying'=>$arPropsArray,
				'paid'=>$arPropsArray
			),
		);
	}

	/**
	 * Метод добавляет расчёт новых заказов к результату
	 */
	public function withNewOrders() {
		$obCondition=new CDbCriteria();
		$obCondition->addCondition("dt_beg>='{$this->sDtBeg}'");
		$obCondition->addCondition("dt_beg<='{$this->sDtEnd}'");
		if($this->iPeopleId==0) {
			$obCondition->addCondition("manager_id>0");
		} else {
			$obCondition->addCondition("manager_id={$this->iPeopleId}");
		}
		$obCondition->addCondition('status_id!=15');
		$this->_prepareData('new',$obCondition);
		return $this;
	}

	/**
	 * Метод выполняет добавление к результату заказов к которым есть платёжка
	 */
	public function withPaying() {
		$obCondition=new CDbCriteria();
		if($this->iPeopleId==0) {
			$obCondition->addCondition("manager_id>0");
		} else {
			$obCondition->addCondition("manager_id={$this->iPeopleId}");
		}
		$obCondition->addCondition('status_id!=15');
		$obCondition->join='INNER JOIN `payment` ON `t`.`id`=`payment`.`package_id`';
		$obCondition->addCondition("`payment`.`dt`>='{$this->sDtBeg}'");
		$obCondition->addCondition("`payment`.`dt`<='{$this->sDtEnd}'");
		$obCondition->addCondition("`payment`.`ptype_id`='0'");
		$this->_prepareData('paying',$obCondition);
		return $this;
	}

	/**
	 * Метод выполняет добавление к результату оплаченных заказов
	 */
	public function withPaid() {
		$obCondition=new CDbCriteria();
		if($this->iPeopleId==0) {
			$obCondition->addCondition("manager_id>0");
		} else {
			$obCondition->addCondition("manager_id={$this->iPeopleId}");
		}
		$obCondition->addCondition('status_id!=15');
		$obCondition->join='INNER JOIN `payment` ON `t`.`id`=`payment`.`package_id`';
		$obCondition->addCondition("`payment`.`dt`>='{$this->sDtBeg}'");
		$obCondition->addCondition("`payment`.`dt`<='{$this->sDtEnd}'");
		$obCondition->addCondition("`payment`.`ptype_id`='1'");
		$this->_prepareData('paid',$obCondition);
		return $this;
	}

	public function getResult() {
		$arResult=array(
			'people'=>People::getById($this->iPeopleId),
			'period'=>$this->sPeriod,
			'date'=>$this->sDtEnd,
			'date_from'=>$this->sDtBeg,
			'data'=>$this->arData
		);
		return $arResult;
	}

	private function _prepareData($type,$obCondition) {
		/**
		 * @var Package[] $arPackages
		 */
		$arPackages=Package::model()->findAll($obCondition);
		foreach($arPackages as $obPackage) {
			$obProduct=$obPackage->getProduct();
			$obSum=$obPackage->getSum();
			//Общая статистика
			if(!is_null($obProduct) && isset($this->arData['products'][$type][$obProduct->id])) {
				$this->arData['products'][$type][$obProduct->id]['total']++;
				$this->arData['products'][$type][$obProduct->id]['summ']+=$obSum->getClear();
				$this->arData['products'][$type][$obProduct->id]['summ_excluded']+=$obSum->getExcluded();
				$this->arData['products'][$type][$obProduct->id]['summ_total']+=$obSum->getFull();
				if(in_array($obProduct->id,array(148,149,150))) {
					$this->arData['products'][$type]['shop']['total']++;
					$this->arData['products'][$type]['shop']['summ']+=$obSum->getClear();
					$this->arData['products'][$type]['shop']['summ_excluded']+=$obSum->getExcluded();
					$this->arData['products'][$type]['shop']['summ_total']+=$obSum->getFull();
				}
			} else {
				$this->arData['products'][$type][0]['total']++;
				$this->arData['products'][$type][0]['summ']+=$obSum->getClear();
				$this->arData['products'][$type][0]['summ_excluded']+=$obSum->getExcluded();
				$this->arData['products'][$type][0]['summ_total']+=$obSum->getFull();
			}
			$this->arData['products'][$type]['total']++;
			$this->arData['products'][$type]['summ']+=$obSum->getClear();
			$this->arData['products'][$type]['summ_excluded']+=$obSum->getExcluded();
			$this->arData['products'][$type]['summ_total']+=$obSum->getFull();
			//По менеджерам
			if(!isset($this->arData['managers'][$obPackage->manager_id])) {
				$this->arData['managers'][$obPackage->manager_id]=array(
					'people'=>People::getById($obPackage->manager_id),
					'new'=>array('total'=>0,'summ'=>0),
					'paying'=>array('total'=>0,'summ'=>0),
					'paid'=>array('total'=>0,'summ'=>0),
				);
			}
			$this->arData['managers'][$obPackage->manager_id][$type]['total']++;
			$this->arData['managers'][$obPackage->manager_id][$type]['summ']+=$obSum->getClear();
			$this->arData['summary'][$type]['total']++;
			$this->arData['summary'][$type]['summ']+=$obSum->getClear();
			$this->arData['summary'][$type]['summ_excluded']+=$obSum->getExcluded();
			$this->arData['summary'][$type]['summ_total']+=$obSum->getFull();
		}
	}
}