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
			<col width="50">
			<col width="300">
			<col width="100">
			<col width="100">
			<tr>
				<th>##</th>
				<th>Партнер</th>
				<th>Новых клиентов</th>
				<th>Заказов</th>
			</tr>
			<?php foreach($partners as $arPartner):?>
				<tr>
					<td><?php echo $arPartner['id']?></td>
					<td><?php echo $arPartner['fio']?></td>
					<td><?php echo $arPartner['new_clients']?></td>
					<td><?php echo $arPartner['orders']?></td>
				</tr>
			<?php endforeach;?>
			<tr>
				<td colspan="2" align="right">Итого:</td>
				<th><?php echo $total['clients']?></th>
				<th><?php echo $total['orders']?></th>
			</tr>
		</table>
		<h1>Итог:</h1>
		<table class="reportItem">
			<tr>
				<th>Количество активных партнеров в период</th>
				<th><?php echo $total['active_partners']?></th>
			</tr>
			<tr>
				<th>Количество новых партнеров в период</th>
				<th><?php echo $total['new_partners']?></th>
			</tr>
			<tr>
				<th>Количество партнеров с которыми ведутся переговоры</th>
				<th><?php echo $total['negotiations_partners']?></th>
			</tr>
		</table>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>
</div>
