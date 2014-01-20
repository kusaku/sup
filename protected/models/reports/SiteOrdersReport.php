<?php
class SiteOrdersReport extends Report{
	/**
	 * Метод выполняет генерацию отчёта по общему количеству заказов с сайта с учётом разных алгоритмов в зависимости
	 * от даты запроса.
	 * Строка 1: Всего с сайта ААА заказов на сумму БББ руб.,
	 * где ААА - количество заказов у которых статус не равен "отклонено" и дата создания заказа попадает в период выборки;
	 *     БББ - сумма стоимости указанных заказов;
	 * @return array
	 */
	public function getTotalOrdersCount() {
		$sDateWhere= '`dt_beg`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `dt_beg`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT COUNT(`id`) as number, SUM(`summ`) as money FROM `package` WHERE `external`=1 AND `status_id`<>15 AND '.$sDateWhere;
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT COUNT(`id`) as number, SUM(`summ`) as money, `source_id` FROM `package` WHERE `status_id`<>15 AND `source_id` IN (2,5) AND '.$sDateWhere.' GROUP BY `source_id`';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arList=$obQuery->queryAll();
			$arResult=array(
				'number'=>0,
				'money'=>0,
				'children'=>array()
			);
			foreach($arList as $arRow) {
				$arResult['number']+=$arRow['number'];
				$arResult['money']+=$arRow['money'];
				$arRow['title']=FSAPIApplications::model()->findByPk($arRow['source_id'])->title;
				$arResult['children'][]=$arRow;
			}
			$arResult['query']=$sQuery;
		} else {
			//Переходный период
			$sQuery1='SELECT COUNT(`id`) as number, SUM(`summ`) as money FROM `package` WHERE `status_id`<>15 AND `external`=1 AND `dt_beg`>=\''.date('Y-m-d 00:00:00',$this->date_from).
				'\' AND `dt_beg`<=\''.date('Y-m-d H:i:s',1352700000).'\'';
			$sQuery2='SELECT COUNT(`id`) as number, SUM(`summ`) as money FROM `package` WHERE `status_id`<>15 AND `source_id` IN (2,5) AND `dt_beg`>\''.date('Y-m-d H:i:s',1352700000).
				'\' AND `dt_beg`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery='SELECT SUM(`number`) as number, SUM(`money`) as money FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		}
		return $arResult;
	}

	/**
	 * Метод выполняет генерацию отчёта по общему количеству заказов с сайта с учётом разных алгоритмов в зависимости
	 * от даты запроса.
	 * Строка 1: Всего с сайта ААА заказов на сумму БББ руб.,
	 * где ААА - количество заказов у которых статус не равен "отклонено" и дата создания заказа попадает в период выборки;
	 *     БББ - сумма стоимости указанных заказов;
	 * @return array
	 */
	public function getTotalOrders() {
		$sDateWhere= '`dt_beg`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `dt_beg`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT id, name, summ, external as source_id, status_id, dt_beg FROM `package` WHERE `external`=1 AND `status_id`<>15 AND '.$sDateWhere;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT id, name, summ, source_id, status_id, dt_beg FROM `package` WHERE `status_id`<>15 AND `source_id` IN (2,5) AND '.$sDateWhere;
		} else {
			//Переходный период
			$sQuery1='SELECT id, name, summ, external as source_id, status_id, dt_beg FROM `package` WHERE `status_id`<>15 AND `external`=1 AND `dt_beg`>=\''.date('Y-m-d 00:00:00',$this->date_from).
				'\' AND `dt_beg`<=\''.date('Y-m-d H:i:s',1352700000).'\'';
			$sQuery2='SELECT id, name, summ, source_id, status_id, dt_beg FROM `package` WHERE `status_id`<>15 AND `source_id` IN (2,5) AND `dt_beg`>\''.date('Y-m-d H:i:s',1352700000).
				'\' AND `dt_beg`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery='SELECT id, name, summ, source_id, status_id, dt_beg FROM ('.$sQuery1.' UNION '.$sQuery2.') as A GROUP BY id';
		}
		$obQuery=Yii::app()->db->createCommand($sQuery);
		$arResult=$obQuery->queryAll();
		return $arResult;
	}

	/**
	 * Метод выполняет подсчёт суммы и количества "виртуально" оплаченных заказов.
	 * Строка 2: Всего с сайта условно оплачено ВВВ заказов на сумму ГГГ руб.,
	 * где ВВВ - количество заказов у которых статус равен "условно оплачено", есть записи в таблице оплат, дата payment.dt
	 *           попадает в указанный период и платежи не были подтверждены бухгалтером;
	 *     ГГГ - сумма пришедших оплат (payment.amount) по заказам у которых статус равен "условно оплачено", есть записи в
	 *           таблице оплат, дата payment.dt попадает в указанный период и платежи не были подтверждены бухгалтером;
	 * @return array
	 */
	public function getVirtuallyPaidOrdersCount() {
		$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT `package`.`source_id`, COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere.' GROUP BY `package`.`source_id`';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arList=$obQuery->queryAll();
			$arResult=array(
				'number'=>0,
				'money'=>0,
				'children'=>array()
			);
			foreach($arList as $arRow) {
				$arResult['number']+=$arRow['number'];
				$arResult['money']+=$arRow['money'];
				$arRow['title']=FSAPIApplications::model()->findByPk($arRow['source_id'])->title;
				$arResult['children'][]=$arRow;
			}
			$arResult['query']=$sQuery;
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\'';
			//Переходный период
			$sQuery1='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
			$sDateWhere='`payment`.`dt`>\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery2='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
			$sQuery='SELECT SUM(`number`) as number, SUM(`money`) as money FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		}
		return $arResult;
	}

	/**
	 * Метод выполняет выборку виртуально оплаченных заказов
	 * Строка 2: Всего с сайта условно оплачено ВВВ заказов на сумму ГГГ руб.,
	 * где ВВВ - количество заказов у которых статус равен "условно оплачено", есть записи в таблице оплат, дата payment.dt
	 *           попадает в указанный период и платежи не были подтверждены бухгалтером;
	 *     ГГГ - сумма пришедших оплат (payment.amount) по заказам у которых статус равен "условно оплачено", есть записи в
	 *           таблице оплат, дата payment.dt попадает в указанный период и платежи не были подтверждены бухгалтером;
	 * @return array
	 */
	public function getVirtuallyPaidOrders() {
		$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
		$sCommonSelect='`package`.`id`,`payment`.`id` as `id_payment`, `package`.`name`,`package`.`summ`, `payment`.`amount`,`package`.`payment_id`, `payment`.`ptype_id`,`payment`.`dt`,`package`.`dt_beg`';
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT '.$sCommonSelect.',`package`.`external` as `source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT '.$sCommonSelect.',`package`.`source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\'';
			//Переходный период
			$sQuery1='SELECT '.$sCommonSelect.',`package`.`external` as `source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
			$sDateWhere='`payment`.`dt`>\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery2='SELECT '.$sCommonSelect.',`package`.`source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=20 AND `payment`.`ptype_id`=0 AND '.$sDateWhere;
			$sQuery='SELECT id,id_payment,name,summ,amount,source_id,payment_id,ptype_id,dt,dt_beg FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
		}
		$obQuery=Yii::app()->db->createCommand($sQuery);
		$arResult=$obQuery->queryAll();
		return $arResult;
	}

	/**
	 * Метод выполняет подсчёт суммы и количества "реально" оплаченных заказов.
	 * Строка 3: Всего с сайта действительно оплачено ДДД заказов на сумму ЕЕЕ руб.,
	 * где ДДД - количество заказов у которых статус равен "пришла оплата", есть записи в таблице оплат, дата payment.dt
	 *           попадает в указанный период и платежи подтверждены бухгалтером;
	 *     ЕЕЕ - сумма пришедших оплат (payment.amount) по заказам у которых статус равен "пришла оплата", есть записи в
	 *           таблице оплат, дата payment.dt попадает в указанный период и платежи подтверждены бухгалтером;
	 * @return array
	 */
	public function getReallyPaidOrdersCount() {
		$sDateWhere=$this->getDateWhere();
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT `package`.`source_id`, COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere.' GROUP BY `package`.`source_id`';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arList=$obQuery->queryAll();
			$arResult=array(
				'number'=>0,
				'money'=>0,
				'children'=>array()
			);
			foreach($arList as $arRow) {
				$arResult['number']+=$arRow['number'];
				$arResult['money']+=$arRow['money'];
				$arRow['title']=FSAPIApplications::model()->findByPk($arRow['source_id'])->title;
				$arResult['children'][]=$arRow;
			}
			$arResult['query']=$sQuery;
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\'';
			//Переходный период
			$sQuery1='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
			$sDateWhere='`payment`.`dt`>\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery2='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
			$sQuery='SELECT SUM(`number`) as number, SUM(`money`) as money FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		}
		return $arResult;
	}

	/**
	 * Метод выполняет выборку виртуально оплаченных заказов
	 * Строка 3: Всего с сайта действительно оплачено ДДД заказов на сумму ЕЕЕ руб.,
	 * где ДДД - количество заказов у которых статус равен "пришла оплата", есть записи в таблице оплат, дата payment.dt
	 *           попадает в указанный период и платежи подтверждены бухгалтером;
	 *     ЕЕЕ - сумма пришедших оплат (payment.amount) по заказам у которых статус равен "пришла оплата", есть записи в
	 *           таблице оплат, дата payment.dt попадает в указанный период и платежи подтверждены бухгалтером;
	 * @return array
	 */
	public function getReallyPaidOrders() {
		$sDateWhere=$this->getDateWhere();
		$sCommonSelect='`package`.`id`,`payment`.`id` as `id_payment`, `package`.`name`,`package`.`summ`, `payment`.`amount`,`package`.`payment_id`, `payment`.`ptype_id`,`payment`.`dt`,`package`.`dt_beg`';
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT '.$sCommonSelect.',`package`.`external` as `source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT '.$sCommonSelect.',`package`.`source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\'';
			//Переходный период
			$sQuery1='SELECT '.$sCommonSelect.',`package`.`external` as `source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
			$sDateWhere='`payment`.`dt`>\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery2='SELECT '.$sCommonSelect.',`package`.`source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id`=30 AND `payment`.`ptype_id`=1 AND '.$sDateWhere;
			$sQuery='SELECT id,id_payment,name,summ,amount,source_id,payment_id,ptype_id,dt,dt_beg FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
		}
		$obQuery=Yii::app()->db->createCommand($sQuery);
		$arResult=$obQuery->queryAll();
		return $arResult;
	}

	/**
	 * Метод выполняет подсчёт суммы и количества оплаченных заказов.
	 * Строка 4: Всего с сайта оплачено ЁЁЁ заказов на сумму ЖЖЖ руб.,
	 * где ЁЁЁ - количество заказов у которых статус равен "пришла оплата" или "условно оплачено", есть записи в таблице
	 *           оплат, дата payment.dt попадает в указанный период;
	 *     ЖЖЖ - сумма пришедших оплат (payment.amount) по заказам у которых статус равен "пришла оплата" или "условно
	 *           оплачено", есть записи в таблице оплат, дата payment.dt попадает в указанный период;
	 * @return array
	 */
	public function getPaidOrdersCount() {
		$sDateWhere=$this->getDateWhere();
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT `package`.`source_id`, COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere.' GROUP BY `package`.`source_id`';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arList=$obQuery->queryAll();
			$arResult=array(
				'number'=>0,
				'money'=>0,
				'children'=>array()
			);
			foreach($arList as $arRow) {
				$arResult['number']+=$arRow['number'];
				$arResult['money']+=$arRow['money'];
				$arRow['title']=FSAPIApplications::model()->findByPk($arRow['source_id'])->title;
				$arResult['children'][]=$arRow;
			}
			$arResult['query']=$sQuery;
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\'';
			//Переходный период
			$sQuery1='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
			$sDateWhere='`payment`.`dt`>\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery2='SELECT COUNT(`package`.`id`) as number, SUM(`payment`.`amount`) as money
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
			$sQuery='SELECT SUM(`number`) as number, SUM(`money`) as money FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
			$obQuery=Yii::app()->db->createCommand($sQuery);
			$arResult=$obQuery->queryRow();
			$arResult['query']=$sQuery;
		}
		return $arResult;
	}

	protected function getDateWhere() {
		if($this->date_from<PR_BUG_TIME && $this->date_to>PR_BUG_TIME) {
			$sDateWhere='((`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',PR_BUG_TIME).'\') OR '.
				'(`payment`.`dt_pay`>=\''.date('Y-m-d 00:00:00',PR_BUG_TIME).'\' AND `payment`.`dt_pay`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'))';
		} elseif($this->date_from>PR_BUG_TIME) {
			$sDateWhere='`payment`.`dt_pay`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt_pay`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
		}
		return $sDateWhere;
	}

	/**
	 * Метод выполняет выборку виртуально оплаченных заказов
	 * Строка 4: Всего с сайта оплачено ЁЁЁ заказов на сумму ЖЖЖ руб.,
	 * где ЁЁЁ - количество заказов у которых статус равен "пришла оплата" или "условно оплачено", есть записи в таблице
	 *           оплат, дата payment.dt попадает в указанный период;
	 *     ЖЖЖ - сумма пришедших оплат (payment.amount) по заказам у которых статус равен "пришла оплата" или "условно
	 *           оплачено", есть записи в таблице оплат, дата payment.dt попадает в указанный период;
	 * @return array
	 */
	public function getPaidOrders() {
		$sDateWhere=$this->getDateWhere();
		$sCommonSelect='`package`.`id`,`payment`.`id` as `id_payment`, `package`.`name`,`package`.`summ`, `payment`.`amount`,`package`.`payment_id`, `payment`.`ptype_id`,`payment`.`dt`,`package`.`dt_beg`';
		if($this->date_from<SOR_NEW_LOGIC_DATETIME && $this->date_to<SOR_NEW_LOGIC_DATETIME) {
			//Старый алгоритм
			$sQuery='SELECT '.$sCommonSelect.',`package`.`external` as `source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
		} elseif($this->date_from>SOR_NEW_LOGIC_DATETIME) {
			//Новый алгоритм
			$sQuery='SELECT '.$sCommonSelect.',`package`.`source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
		} else {
			$sDateWhere='`payment`.`dt`>=\''.date('Y-m-d 00:00:00',$this->date_from).'\' AND `payment`.`dt`<=\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\'';
			//Переходный период
			$sQuery1='SELECT '.$sCommonSelect.',`package`.`external` as `source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`external`=1 AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
			$sDateWhere='`payment`.`dt`>\''.date('Y-m-d H:i:s',SOR_NEW_LOGIC_DATETIME).'\' AND `payment`.`dt`<=\''.date('Y-m-d 23:59:59',$this->date_to).'\'';
			$sQuery2='SELECT '.$sCommonSelect.',`package`.`source_id`
				FROM `package` JOIN `payment` ON `package`.`id`=`payment`.`package_id`
				WHERE `package`.`source_id` IN (2,5) AND `package`.`payment_id` IN (20,30) AND `payment`.`ptype_id` IN (0,1) AND '.$sDateWhere;
			$sQuery='SELECT id,id_payment,name,summ,amount,source_id,payment_id,ptype_id,dt,dt_beg FROM ('.$sQuery1.' UNION '.$sQuery2.') as A';
		}
		$obQuery=Yii::app()->db->createCommand($sQuery);
		$arResult=$obQuery->queryAll();
		return $arResult;
	}
}