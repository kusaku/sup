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
	<div class="today">
		<span class="name"><?= $day?></span>
		- <?= date('d')?><?= $month?><?= date('Y')?>г.<a onClick="calendarToggle()" style="text-decoration: none;">
			<div class="datePicker"></div>
			<div id="eventsCount"></div>
		</a>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
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
		<table class="reportItem">
			<thead>
				<tr>
					<th>ID-заказа</th>
					<th>Клиент</th>
					<th>Менеджер</th>
					<th>Дата</th>
					<th>Сумма оплаты</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($payments as $pay): ?>
					<tr>
						<td>#<?php echo $pay['order_id']?></td>
						<td><?php echo $pay['client']?></td>
						<td><?php echo $pay['manager']?></td>
						<td><?php echo $pay['date']?></td>
						<td><?php echo $pay['summ']?> р.</td>
					</tr>			
				<?php endforeach;?>		
			</tbody>
		</table>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>
</div>
