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
	<script type="text/javascript">
		$(function(){
			$('.expandall').bind('click', function(){
				if ($(this).is('.expanded')) {
					$('.expandall').removeClass('expanded').text('Развернуть всё');
					$('.expandnext').removeClass('expanded');
					$('.collapsible').hide();
				}
				else {
					$('.expandall').addClass('expanded').text('Свернуть всё');
					$('.expandnext').addClass('expanded');
					$('.collapsible').show();
				}
			});
			$('.expandnext').bind('click', function(){
				if ($(this).is('.expanded')) {
					$(this).removeClass('expanded');
					$(this).next().find('.collapsible').hide();
				}
				else {
					$(this).addClass('expanded');
					$(this).next().find('.collapsible').show();
				}
			});
		});
	</script>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
		<a style="float:right;" class="orangeButton hiddenprint expandall">Развернуть всё</a>
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
		<?php foreach ($data as $partnerItem): ?>
		<?php if (!$partnerItem['count']) continue; ?>
		<h1 class="expandnext" style="float:left;clear:both;cursor:pointer;border-bottom:1px dashed #FF7B0B;"><?= $partnerItem['name']?> (<?= $partnerItem['mail']?>)</h1>
		<table class="reportItem">
			<tr style="display:none;" class="collapsible">
				<th style="width: 13%">Дата</th>
				<th style="width: 27%;">Клиент</th>
				<th style="width: 30%;">Плательщик</th>
				<th style="width: 17%;">Проект</th>
				<th style="width: 13%;text-align:right;">Сумма</th>
			</tr>
			<?php foreach ($partnerItem['pays'] as $payItem): ?>
			<tr style="display:none;" class="collapsible">
				<td>
					<?= $payItem['dt']?>
				</td>
				<td>
					<?= $payItem['client']?><?= empty($payItem['mail']) ? '' : " ({$payItem['mail']})"?>
				</td>
				<td>
					<?= $payItem['description']?>
				</td>
				<td>
					<?= $payItem['name']?><?= empty($payItem['site']) ? '' : " ({$payItem['site']})"?>
				</td>
				<td style="text-align:right;">
					<?= number_format($payItem['amount'], 0, ',', ' ')?>   руб.
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<th>Количество:</th>
				<th>
					<?= $partnerItem['count']?>
				</th>
				<th></th>
				<th>Сумма:</th>
				<th style="text-align:right;">
					<?= number_format($partnerItem['summ'], 0, ',', ' ')?>  руб.
				</th>
			</tr>
		</table>
		<?php endforeach; ?>
		<h1>Итог:</h1>
		<table class="reportItem">
			<tr>
				<th>Количество</th>
				<th>Сумма</th>
			</tr>
			<tr>
				<th>
					<?= $total['count']?>
				</th>
				<th style="width: 13%;text-align:right;">
					<?= number_format($total['summ'], 0, ',', ' ')?>  руб.
				</th>
			</tr>
		</table>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
		<a style="float:right;" class="orangeButton hiddenprint expandall">Развернуть всё</a>
	</div>
</div>
