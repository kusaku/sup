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
		<?php foreach ($data as $managerItem): ?>
		<?php if (!$managerItem['count']) continue; ?>
		<h1><?= $managerItem['name']?></h1>
		<?php foreach ($managerItem['packs'] as $packItem): ?>
		<h2 style="float:right;">Клиент:
			<?= $packItem['client']?>  &lt;<a href="mailto:<?= $packItem['mail']?>"><?= $packItem['mail']?></a>&gt;</h2>
		<h2 class="expandnext" style="cursor:pointer;float:left;border-bottom:1px dashed #000000;">Заказ
			<?= $packItem['name']?>
			<?php /* if(!empty($packItem['descr'])) : ?> (<?= $packItem['descr']?>) <?php endif; */ ?>			
			<?php if ( empty($packItem['site'])): ?>
			(без привязки к сайту)
			<?php else : ?>
			<a href="<?= $packItem['site']?>"><?= $packItem['site']?></a>
			<?php endif; ?>
		</h2>
		<table class="reportItem">
			<tr style="display:none;" class="collapsible">
				<th>Статус</th>
				<th colspan="6">Услуги</th>
			</tr>
			<tr style="display:none;" class="collapsible">
				<th style="width: 25%" rowspan="<?= $packItem['count']+1 ?>">
					<p>
						Состояние: <?= $packItem['status']?>
					</p>
					<p>
						Создан: <?= $packItem['dt_beg']?>
					</p>
					<p>
						Изменен: <?= $packItem['dt_change']?>
					</p>
				</th>
				<?php if ($packItem['count']): ?>
				<th style="width: 15%">Услуга</th>
				<th style="width: 13%">Заказана</th>
				<th style="width: 13%">Выполнена</th>
				<th style="width: 13%">Стоимость</th>
				<th style="width: 8%">Количество</th>
				<th style="width: 13%;text-align:right;">Сумма</th>
				<?php else : ?>
				<td colspan="6">В этом заказе никаких услуг не заказано</td>
				<?php endif; ?>
			</tr>
			<?php foreach ($packItem['servs'] as $servItem): ?>
			<tr style="display:none;" class="collapsible">
				<td>
					<p>
						<?= $servItem['name']?>
					</p>
					<?php if (! empty($servItem['descr'])): ?>
					<p>
						<?= $servItem['descr']?>
					</p>
					<?php endif; ?>
				</td>
				<td>
					<?= $servItem['dt_beg']?>
				</td>
				<td>
					<?= $servItem['dt_end']?>
				</td>
				<th>
					<?= number_format($servItem['price'], 0, ',', ' ')?> руб.
				</th>
				<td>
					<?= $servItem['count']?>
				</td>
				<th style="text-align:right;">
					<?= number_format($servItem['summ'], 0, ',', ' ')?> руб.
				</th>
			</tr>
			<?php endforeach; ?>
			<tr>
				<th>Сумма</th>
				<th style="text-align:right;" colspan="6">
					<?= number_format($packItem['summ'], 0, ',', ' ')?> руб.
				</th>
			</tr>
			<?php if ($packItem['paid']): ?>
			<tr style="display:none;" class="collapsible">
				<?php if (! empty($packItem['pays'])): ?>
				<th rowspan="<?= count($packItem['pays'])+1 ?>">Оплаты</th>
				<th>Дата </th>
				<th colspan="4">Плательщик </th>
				<th>Сумма</th>
			</tr>
			<?php foreach ($packItem['pays'] as $payItem): ?>
			<tr style="display:none;" class="collapsible">
				<td>
					<?= $payItem['dt']?>
				</td>
				<td colspan="4">
					<?= $payItem['description']?>
				</td>
				<th>
					<?= number_format($payItem['summ'], 0, ',', ' ')?> руб.
				</th>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
			<tr>
				<th>Оплачено</th>
				<th style="text-align:right;" colspan="6">
					<?php if ($packItem['summ'] > 0): ?>
					<?= number_format(100 * $packItem['paid'] / $packItem['summ'], 0, ',', ' ')?>% -   
					<?php endif; ?>
					<?= number_format($packItem['paid'], 0, ',', ' ')?> руб.
				</th>
			</tr>
			<?php endif; ?>
			<tr>
				<th>Промокод</th>
				<th style="text-align:right;" colspan="6">
					<?= $packItem['promocode']; ?>
				</th>
			</tr>
		</table>
		<?php endforeach; ?>
		<h1>Подитог:</h1>
		<table class="reportItem">
			<tr>
				<th style="width: 25%">Заказов</th>
				<th style="width: 15%">Сумма</th>
				<th style="text-align:right;">Оплачено</th>
			</tr>
			<tr>
				<th>
					<?= number_format($managerItem['count'], 0, ',', ' ')?>
				</th>
				<th>
					<?= number_format($managerItem['summ'], 0, ',', ' ')?>  руб.
				</th>
				<th style="text-align:right;">
					<?php if ($managerItem['summ'] > 0): ?>
					(<?= number_format(100 * $managerItem['paid'] / $managerItem['summ'], 0, ',', ' ')?>%)
					<?php endif; ?>
					<?= number_format($managerItem['paid'], 0, ',', ' ')?> руб.
				</th>
			</tr>
		</table>
		<?php endforeach; ?>
		<h1>Итог:</h1>
		<table class="reportItem">
			<tr>
				<th style="width: 25%">Заказов</th>
				<th style="width: 15%">Сумма</th>
				<th style="text-align:right;">Оплачено</th>
			</tr>
			<tr>
				<th>
					<?= number_format($total['count'], 0, ',', ' ')?>
				</th>
				<th>
					<?= number_format($total['summ'], 0, ',', ' ')?>  руб.
				</th>
				<th style="text-align:right;">
					<?php if ($total['summ'] > 0): ?>
					(<?= number_format(100 * $total['paid'] / $total['summ'], 0, ',', ' ')?>%)
					<?php endif; ?>
					<?= number_format($total['paid'], 0, ',', ' ')?> руб.
				</th>
			</tr>
		</table>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
		<a style="float:right;" class="orangeButton hiddenprint expandall">Развернуть всё</a>
	</div>
</div>
