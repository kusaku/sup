<?php
$weekdays = array(
	'Пн.','Вт.','Ср.','Чт.','Пт.','Сб.','Вс.'
	);
	$day = $weekdays[date('N') - 1];
	$months = array(
		'января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября',
		'ноября','декабря'
	);
	$month = $months[date('n') - 1];
	?>
	<div class="wrapper">
	<div class="logo">
	<h1><a href="/manager" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="reportButtons">
	<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>
	<div class="today">
	<span class="name"><?= $day?></span>
	- <?= date('d')?><?= $month?><?= date('Y')?>г.<a onClick="calendarToggle()" style="text-decoration: none;">
	<div class="datePicker"></div>
	<div id="eventsCount"></div>
	</a>
	</div>
	<div class="report">
	<h1>Отчет за период c <strong><?= $total['dt_beg']; ?> </strong> по <strong><?= $total['dt_end']; ?></strong>
	<?php
	$params = $this->getActionParams();
	$dt_beg = @$params['dt_beg'];
	$dt_end = @$params['dt_end'];
	$params['dt_beg'] = date('d.m.Y', strtotime($dt_beg.' -1 month'));
	$params['dt_end'] = date('d.m.Y', strtotime($dt_end.' -1 month'));
	$back_url = Yii::app()->getUrlManager()->createUrl($this->getRoute(), $params);
	$params['dt_beg'] = date('d.m.Y', strtotime($dt_beg.' +1 month'));
	$params['dt_end'] = date('d.m.Y', strtotime($dt_end.' +1 month'));
	$fwd_url = Yii::app()->getUrlManager()->createUrl($this->getRoute(), $params);
	?>
	<a class="hiddenprint" href="<?=$back_url;?>">&lt;&lt;&lt;</a><a class="hiddenprint" href="<?=$fwd_url;?>">&gt;&gt;&gt;</a></h1>
	<?php
	$criteria = new CDbCriteria();
	// выборка по дате
	$criteria->compare('dt_beg', '>='.date('Y-m-d', strtotime($total['dt_beg'])));
	$criteria->compare('dt_beg', '<'.date('Y-m-d', strtotime($total['dt_end']) + 86399));
	
	$criteria00 = clone $criteria;
	$criteria00->scopes = array(
	);
	
	$criteria01 = clone $criteria;
	$criteria01->scopes = array(
		'virtuallypaid'
	);
	
	$criteria02 = clone $criteria;
	$criteria02->scopes = array(
		'reallypaid'
	);
	
	$criteria03 = clone $criteria;
	$criteria03->scopes = array(
		'paid'
	);
	
	$criteria10 = clone $criteria;
	$criteria10->scopes = array(
		'external',
		'not_denied',
	);
	
	$criteria20 = clone $criteria;
	$criteria20->scopes = array(
		'internal'
	);
	
	$criteria21 = clone $criteria;
	$criteria21->scopes = array(
		'internal','virtuallypaid'
	);
	
	$criteria22 = clone $criteria;
	$criteria22->scopes = array(
		'internal','reallypaid'
	);
	
	$criteria23 = clone $criteria;
	$criteria23->scopes = array(
		'internal','paid'
	);
	
	$summ_criteria_package = new CDbCriteria;
	$summ_criteria_package->select = 'SUM(summ) as amount';
	$summ_criteria_package->group = 'NULL';
	
	$summ_criteria_payment = new CDbCriteria;
	$summ_criteria_payment->select = 'SUM(amount) as summ';
	$summ_criteria_payment->group = 'NULL';
	
	$summ_criteria_payment_rec = clone $summ_criteria_payment;
	$summ_criteria_payment_rec->scopes = array(
		'rec'
	);
	
	$summ_criteria_payment_pay = clone $summ_criteria_payment;
	$summ_criteria_payment_pay->scopes = array(
		'pay'
	);
	
	$summ_criteria_payment_recpay = clone $summ_criteria_payment;
	$summ_criteria_payment_recpay->scopes = array(
		'recpay'
	);
	
	?>
	<h1>Заказы</h1>
	<p>
	Всего <strong><?= Package::model()->count($criteria00); ?></strong>
	заказов на сумму <strong>
	<?php
	$package_summ = Package::model();
	$criteria00->mergeWith($summ_criteria_package);
	$package_summ = $package_summ->find($criteria00);
	echo number_format($package_summ ? $package_summ->amount : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<p>
	Всего условно оплачено <strong><?= Package::model()->count($criteria01); ?></strong>
	заказов на сумму <strong>
	<?php
	$payment_summ = Payment::model()->with(array(
		'package'=>array(
			'joinType'=>'INNER JOIN','select'=>FALSE,'scopes'=>$criteria00->scopes,'condition'=>$criteria01->condition,'params'=>$criteria01->params
		)
	))->find($summ_criteria_payment_rec);
	echo number_format($payment_summ ? $payment_summ->summ : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<p>
	Всего действительно оплачено <strong><?= Package::model()->count($criteria02); ?></strong>
	заказов на сумму <strong>
	<?php
	$payment_summ = Payment::model()->with(array(
		'package'=>array(
			'joinType'=>'INNER JOIN','select'=>FALSE,'scopes'=>$criteria00->scopes,'condition'=>$criteria02->condition,'params'=>$criteria02->params
		)
	))->find($summ_criteria_payment_pay);
	echo number_format($payment_summ ? $payment_summ->summ : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<p>
	Всего оплачено <strong><?= Package::model()->count($criteria03); ?></strong>
	заказов на сумму<strong>
	<?php
	$payment_summ = Payment::model()->with(array(
		'package'=>array(
			'joinType'=>'INNER JOIN','select'=>FALSE,'scopes'=>$criteria00->scopes,'condition'=>$criteria03->condition,'params'=>$criteria03->params
		)
	))->find($summ_criteria_payment_recpay);
	echo number_format($payment_summ ? $payment_summ->summ : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<h1>Заказы, полученные с сайта</h1>
	<p>
	Всего с сайта <strong><a href="#showTotalList"><?= $data['from_site']['total']['number'] ?></a></strong>
	заказов на сумму <strong><?= number_format($data['from_site']['total']['money'], 0, ',', ' ') ?> руб.</strong>
	</p>
	<div id="totalList" style="display: none;">
	<?php
	$dataProvider=new CArrayDataProvider($data['from_site']['total_list'],array('id'=>'total','pagination'=>array('pageSize'=>1000)));
	$arData=$dataProvider->getData();
	$summ=0;
	foreach($arData as $arRow) $summ+=$arRow['summ'];
											   $this->widget('zii.widgets.grid.CGridView',array('dataProvider'=>$dataProvider,'columns'=>array(
												   'id'=>array('header'=>'#','name'=>'id'),
																																			   'name'=>array('header'=>'Название','name'=>'name','footer'=>'<b>Итого:</b>'),
											   'summ'=>array('header'=>'Сумма','name'=>'summ','footer'=>'<b>'.$summ.'</b>'),
											   'source_id'=>array('header'=>'Источник','name'=>'source_id'),
											   'status_id'=>array('header'=>'Статус','name'=>'status_id'),
											   'dt_beg'=>array('header'=>'Дата создания','value'=>'date("d.m.Y H:i:s", strtotime($data["dt_beg"]))')
	),'template'=>'{items}'));
	?>
	</div>
	<p>
	Всего с сайта условно оплачено <strong><a href="#showСonditionallyList"><?= $data['from_site']['conditionally_paid']['number'] ?></a></strong>
	заказов на сумму <strong><?= number_format($data['from_site']['conditionally_paid']['money'], 0, ',', ' ');?> руб.</strong>
	</p>
	<div id="conditionallyList" style="display: none;">
	<?php
	$dataProvider=new CArrayDataProvider($data['from_site']['conditionally_paid_list'],array('id'=>'conditionally','pagination'=>array('pageSize'=>1000)));
	$arData=$dataProvider->getData();
	$summ=0;$amount=0;
	foreach($arData as $arRow) {$summ+=$arRow['summ'];$amount+=$arRow['amount'];}
	$this->widget('zii.widgets.grid.CGridView',array('dataProvider'=>$dataProvider,'columns'=>array(
		'id'=>array('header'=>'#','name'=>'id'),
					'name'=>array('header'=>'Название','name'=>'name','footer'=>'<b>Итого:</b>'),
					'summ'=>array('header'=>'Сумма','name'=>'summ','footer'=>'<b>'.$summ.'</b>'),
					'source_id'=>array('header'=>'Источник','name'=>'source_id'),
					'payment_id'=>array('header'=>'Статус оплаты','name'=>'payment_id'),
					'dt_beg'=>array('header'=>'Дата создания','value'=>'date("d.m.Y H:i:s", strtotime($data["dt_beg"]))'),
					'id_payment'=>array('header'=>'Номер платежа','name'=>'id_payment'),
					'dt'=>array('header'=>'Дата платежа','value'=>'date("d.m.Y H:i:s", strtotime($data["dt"]))',),
					'amount'=>array('header'=>'Сумма платёжки','name'=>'amount','footer'=>'<b>'.$amount.'</b>'),
					'ptype_id'=>array('header'=>'Статус оплаты','name'=>'ptype_id'),
		),'template'=>'{items}'));
	?>
	</div>
	<p>
	Всего с сайта действительно оплачено <strong><a href="#showReallyList"><?= $data['from_site']['really_paid']['number'] ?></a></strong>
	заказов на сумму <strong><?= number_format($data['from_site']['really_paid']['money'], 0, ',', ' ')	?> руб.</strong>
	</p>
	<div id="reallyList" style="display: none;">
	<?php
	$dataProvider=new CArrayDataProvider($data['from_site']['really_paid_list'],array('id'=>'really','pagination'=>array('pageSize'=>1000)));
	$arData=$dataProvider->getData();
	$summ=0;$amount=0;
	foreach($arData as $arRow) {$summ+=$arRow['summ'];$amount+=$arRow['amount'];}
	$this->widget('zii.widgets.grid.CGridView',array('dataProvider'=>$dataProvider,'columns'=>array(
		'id'=>array('header'=>'#','name'=>'id'),
					'name'=>array('header'=>'Название','name'=>'name','footer'=>'<b>Итого:</b>'),
					'summ'=>array('header'=>'Сумма','name'=>'summ','footer'=>'<b>'.$summ.'</b>'),
					'source_id'=>array('header'=>'Источник','name'=>'source_id'),
					'payment_id'=>array('header'=>'Статус оплаты','name'=>'payment_id'),
					'dt_beg'=>array('header'=>'Дата создания','value'=>'date("d.m.Y H:i:s", strtotime($data["dt_beg"]))'),
					'id_payment'=>array('header'=>'Номер платежа','name'=>'id_payment'),
					'dt'=>array('header'=>'Дата платежа','value'=>'date("d.m.Y H:i:s", strtotime($data["dt"]))',),
					'amount'=>array('header'=>'Сумма платёжки','name'=>'amount','footer'=>'<b>'.$amount.'</b>'),
					'ptype_id'=>array('header'=>'Статус оплаты','name'=>'ptype_id'),
		),'template'=>'{items}'));
	?>
	</div>
	<p>
	Всего с сайта оплачено <strong><a href="#showPaidList"><?= $data['from_site']['total_paid']['number'] ?></a></strong>
	заказов на сумму <strong><?= number_format($data['from_site']['total_paid']['money'], 0, ',', ' ') ?> руб.</strong>
	</p>
	<div id="paidList" style="display: none;">
	<?php
	$dataProvider=new CArrayDataProvider($data['from_site']['total_paid_list'],array('id'=>'totally','pagination'=>array('pageSize'=>1000)));
	$arData=$dataProvider->getData();
	$summ=0;$amount=0;
	foreach($arData as $arRow) {$summ+=$arRow['summ'];$amount+=$arRow['amount'];}
	$this->widget('zii.widgets.grid.CGridView',array('dataProvider'=>$dataProvider,'columns'=>array(
		'id'=>array('header'=>'#','name'=>'id'),
					'name'=>array('header'=>'Название','name'=>'name','footer'=>'<b>Итого:</b>'),
					'summ'=>array('header'=>'Сумма','name'=>'summ','footer'=>'<b>'.$summ.'</b>'),
					'source_id'=>array('header'=>'Источник','name'=>'source_id'),
					'payment_id'=>array('header'=>'Статус оплаты','name'=>'payment_id'),
					'dt_beg'=>array('header'=>'Дата создания','value'=>'date("d.m.Y H:i:s", strtotime($data["dt_beg"]))'),
					'id_payment'=>array('header'=>'Номер платежа','name'=>'id_payment'),
					'dt'=>array('header'=>'Дата платежа','value'=>'date("d.m.Y H:i:s", strtotime($data["dt"]))',),
					'amount'=>array('header'=>'Сумма платёжки','name'=>'amount','footer'=>'<b>'.$amount.'</b>'),
					'ptype_id'=>array('header'=>'Статус оплаты','name'=>'ptype_id'),
		),'template'=>'{items}'));
	?>
	</div>
	<h1>Заказы, созданные менеджерами</h1>
	<p>
	Всего из SUP <strong><?= Package::model()->count($criteria20); ?></strong>
	заказов на сумму<strong>
	<?php
	$package_summ = Package::model();
	$criteria20->mergeWith($summ_criteria_package);
	$package_summ = $package_summ->find($criteria20);
	echo number_format($package_summ ? $package_summ->amount : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<p>
	Всего из SUP условно оплачено <strong><?= Package::model()->count($criteria21); ?></strong>
	заказов на сумму <strong>
	<?php
	$payment_summ = Payment::model()->with(array(
		'package'=>array(
			'joinType'=>'INNER JOIN','select'=>FALSE,'scopes'=>$criteria20->scopes,'condition'=>$criteria21->condition,'params'=>$criteria21->params
		)
	))->find($summ_criteria_payment_rec);
	echo number_format($payment_summ ? $payment_summ->summ : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<p>
	Всего из SUP действительно оплачено <strong><?= Package::model()->count($criteria22); ?></strong>
	заказов на сумму<strong>
	<?php
	$payment_summ = Payment::model()->with(array(
		'package'=>array(
			'joinType'=>'INNER JOIN','select'=>FALSE,'scopes'=>$criteria20->scopes,'condition'=>$criteria22->condition,'params'=>$criteria22->params
		)
	))->find($summ_criteria_payment_pay);
	echo number_format($payment_summ ? $payment_summ->summ : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	<p>
	Всего из SUP оплачено <strong><?= Package::model()->count($criteria23); ?></strong>
	заказов на сумму<strong>
	<?php
	$payment_summ = Payment::model()->with(array(
		'package'=>array(
			'joinType'=>'INNER JOIN','select'=>FALSE,'scopes'=>$criteria20->scopes,'condition'=>$criteria23->condition,'params'=>$criteria23->params
		)
	))->find($summ_criteria_payment_recpay);
	echo number_format($payment_summ ? $payment_summ->summ : 0, 0, ',', ' ');
	?>
	руб.</strong>
	</p>
	</div>
	<div class="reportButtons">
	<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>
	</div>
	