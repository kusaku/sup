<div class="wrapper">
	<div class="logo">
		<h1><a href="/" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
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
		<h1>Отчет за период c <?= $total['dt_beg']?> по <?= $total['dt_end']?></h1>
		<?php foreach ($data as $managerItem): ?>
		<?php if (!$managerItem['count']) continue; ?>
		<h1 class="expandnext" style="cursor:pointer;"><?= $managerItem['name']?></h1>
		<table class="reportItem">
			<tr style="display:none;" class="collapsible">
				<th style="width: 13%">Дата</th>
				<th style="width: 37%;">Клиент</th>
				<th style="width: 37%;">Проект</th>
				<th style="width: 13%;text-align:right;">Сумма</th>
			</tr>
			<?php foreach ($managerItem['pays'] as $payItem): ?>
			<tr style="display:none;" class="collapsible">
				<td>
					<?= $payItem['dt']?>
				</td>
				<td>
					<?= $payItem['client']?><?= empty($payItem['mail']) ? '' : " ({$payItem['mail']})"?>
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
				<th style="width:13%">Количество:</th>
				<th style="width:37%;">
					<?= $managerItem['count']?>
				</th>
				<th style="width:37%">Сумма:</th>
				<th style="width:13%;text-align:right;">
					<?= number_format($managerItem['summ'], 0, ',', ' ')?>  руб.
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
