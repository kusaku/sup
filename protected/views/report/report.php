<div id="modal"></div>
<div id="sup_popup" class="popup"></div>
<div id="sup_preloader" class="popup"><img src="/images/preloader.gif" boreder="0"></div>

<div class="wrapper">
 	<div class="logo">
			<h1><a href="/" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
 	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>		
	<div class="report">
		<h1>Отчет за период c <?=$total['dt_beg']?> по <?=$total['dt_end']?></h1>
		<?php foreach ($data as $managerItem): ?>
		<?php if (!$managerItem['count']) continue; ?>
		<h1><?= $managerItem['name']?></h1>
		<?php foreach ($managerItem['packs'] as $packItem): ?>		
		<h2 style="float:left;">
			Заказ
			<?= $packItem['name']?>
			<?php if(!empty($packItem['descr'])) : ?>
			(<?= $packItem['descr']?>)
			<?php endif; ?>			
			<?php if(empty($packItem['site'])) : ?>
			(без привязки к сайту)
			<?php else : ?>
			<a href="<?= $packItem['site']?>"><?= $packItem['site']?></a>
			<?php endif; ?>
		</h2>
		<h2 style="float:right;">
			Клиент:
			<?= $packItem['client']?>  &lt;<a href="mailto:<?= $packItem['clientmail']?>"><?= $packItem['clientmail']?></a>&gt;
		</h2>		
		<table class="reportItem">
			<tr>
				<th>Статус</th>
				<th colspan="6">Услуги</th>
			</tr>
			<tr>
				<th style="width: 25%" rowspan="<?= $packItem['count']+1 ?>">
					<p><?= $packItem['status']?></p>
					<p>Создан: <?= $packItem['dt_beg']?></p>
					<p>Изменен: <?= $packItem['dt_change']?></p>											
				</th>
				<?php if ($packItem['count']): ?>
				<th style="width: 15%">Услуга</th>
				<th style="width: 18%">Заказана</th>
				<th style="width: 18%">Выполнена</th>
				<th style="width: 8%">Стоимость</th>
				<th style="width: 8%">Количество</th>
				<th style="width: 8%">Сумма</th>
				<?php else: ?>
				<td colspan="6">В этом заказе никаких услуг не заказано</td>
				<?php endif; ?>
			</tr>
			<?php foreach ($packItem['servs'] as $servItem): ?>
			<tr>
				<td>
					<p><?= $servItem['name']?></p>
					<?php if(!empty($servItem['descr'])): ?>
					<p><?= $servItem['descr']?></p>
					<?php endif; ?>
				</td>
				<td>
					<?= $servItem['dt_beg']?>
				</td>
				<td>
					<?= $servItem['dt_end']?>
				</td>
				<td>
					<?= number_format($servItem['price'], 0, ',', ' ')?> руб.
				</td>
				<td>
					<?= $servItem['count']?>
				</td>
				<td>
					<?= number_format($servItem['summ'], 0, ',', ' ')?> руб.
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<th>Сумма</th>
				<th colspan="6">
					<?= number_format($packItem['summ'], 0, ',', ' ')?> руб.
				</th>
			</tr>
			<?php if ($packItem['paid']): ?>
			<tr>
				<th>Оплачено</th>
				<th colspan="6">
					<?=number_format($packItem['paid'], 0, ',', ' ')?> руб. 
					<?php if($packItem['summ']>0): ?>
					(<?=number_format(100 * $packItem['paid']/$packItem['summ'], 0, ',', ' ')?>%)
					<?php endif; ?>
				</th>
			</tr>
			<?php endif; ?>
		</table>
		<?php endforeach; ?>
		<h1>Подитог:</h1>
		<table class="reportItem">
			<tr>
				<th>Заказов</th>
				<th>Сумма</th>
				<th>Оплачено</th>
			</tr>
			<tr>
				<td><?= number_format($managerItem['count'], 0, ',', ' ')?></td>
				<td><?= number_format($managerItem['summ'], 0, ',', ' ')?> руб.</td>
				<td>
					<?= number_format($managerItem['paid'], 0, ',', ' ')?> руб.
					<?php if($managerItem['summ']>0): ?>
					(<?= number_format(100 * $managerItem['paid']/$managerItem['summ'], 0, ',', ' ')?>%)
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php endforeach; ?>
		<h1>Итог:</h1>
		<table class="reportItem">
			<tr>
				<th>Заказов</th>
				<th>Сумма</th>
				<th>Оплачено</th>
			</tr>
			<tr>
				<td><?= number_format($total['count'], 0, ',', ' ')?></td>
				<td><?= number_format($total['summ'], 0, ',', ' ')?> руб.</td>
				<td>
					<?= number_format($total['paid'], 0, ',', ' ')?> руб.
					<?php if($total['summ']>0): ?>
					(<?= number_format(100 * $total['paid']/$total['summ'], 0, ',', ' ')?>%)
					<?php endif; ?>
				</td>
			</tr>
		</table>			
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>		
</div>
