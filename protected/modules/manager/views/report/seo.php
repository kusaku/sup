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
		<h1>Отчет за период c <strong><?= date('d.m.Y',$total['dt_beg']); ?> </strong> по <strong><?= date('d.m.Y',$total['dt_end']); ?></strong>
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
		<h1 class="expandnext" style="float:left;clear:both;cursor:pointer;border-bottom:1px dashed #FF7B0B;">Оплаты заказов с промокодами</h1>
		<table class="reportItem">
			<tr>
				<th style="width: 12%">Дата заказа</th>
				<th style="width: 12%">Дата оплаты</th>
				<th style="width: 10%;">Менеджер</th>
				<th style="width: 12%">Тип сайта</th>
				<th style="width: 12%;">Статус</th>
				<th style="width: 18%;">Клиент</th>
				<th style="width: 10%;">Номер</th>
				<th style="width: 10%;">Сумма оплаты</th>
				<th style="width: 10%;text-align:right;">Кроме</th>
				<th style="width: 10%;text-align:right;">Итого</th>
				<th style="width: 10%;text-align:right;">Промо-код</th>
			</tr>
			<?php foreach ($data as $managerItem): ?>
			<?php foreach ($managerItem['packs'] as $item): ?>
			<tr class="collapsible">
				<td>
					<?= $item['dt_beg']?>
				</td>
				<td>
					<?= $item['dt']?>
				</td>
				<td>
					<?= $item['manager']?>
				</td>
				<td>
					<?= $item['type']?>
				</td>
				<td>
					<?= $item['status']?>
				</td>
				<td>
					<?= $item['client']?><?= empty($item['mail']) ? '' : " ({$item['mail']})"?>
				</td>
				<td>
					<?= $item['id']?>
				</td>
				<td style="text-align:right;">
					<?= LangUtils::money($item['summ'],false)?>
				</td>
				<th style="text-align:right;">
					<?= LangUtils::money($item['excluded'],false)?>
				</th>
				<th style="text-align:right;">
					<?= LangUtils::money($item['total'],false)?>
				</th>
				<td style="text-align:right;">
					<?= $item['promocode']?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php if ($managerItem['count']): ?>
			<tr>
				<th>Всего:</th>
				<th>
					<?= number_format($managerItem['count'], 0, ',', ' ')?>
				</th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>Подытог:</th>
				<th style="text-align:right;">
					<?= LangUtils::money($managerItem['summ'],false)?>
				</th>
				<th style="text-align:right;">
					<?= LangUtils::money($managerItem['excluded'],false)?>
				</th>
				<th style="text-align:right;">
					<?= LangUtils::money($managerItem['total'],false)?>
				</th>
				<th></th>
			</tr>
			<?php endif; ?>
			<?php endforeach; ?>
			<tr>
				<th>Всего:</th>
				<th>
					<?= number_format($total['count'], 0, ',', ' ')?>
				</th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>Итого:</th>
				<th style="text-align:right;">
					<?= LangUtils::money($total['summ'],false)?>
				</th>
				<th style="text-align:right;">
					<?= LangUtils::money($total['excluded'],false)?>
				</th>
				<th style="text-align:right;">
					<?= LangUtils::money($total['total'],false)?>
				</th>
				<th></th>
			</tr>
		</table>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
		<a style="float:right;" class="orangeButton hiddenprint expandall">Развернуть всё</a>
	</div>
</div>
