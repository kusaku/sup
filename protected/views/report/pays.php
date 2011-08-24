<div class="wrapper">
	<div class="logo">
		<h1><a href="/" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="reportButtons">
		<a style="float:right;" class="orangeButton hiddenprint" onclick="window.print()">Печать</a>
	</div>
	<div class="report">
		<h1>Отчет за период c <?= $total['dt_beg']?> по <?= $total['dt_end']?></h1>
		<?php foreach ($data as $managerItem): ?>
		<?php if (!$managerItem['count']) continue; ?>
		<h1><?= $managerItem['name']?></h1>
		<table class="reportItem">
			<tr>
				<th style="width: 13%">Дата</th>
				<th style="width: 27%;">Клиент</th>
				<th style="width: 30%;">Плательщик</th>
				<th style="width: 17%;">Проект</th>
				<th style="width: 13%;text-align:right;">Сумма</th>
			</tr>
			<?php foreach ($managerItem['pays'] as $payItem): ?>
			<tr>
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
				<th colspan="4">Итог:</th>
				<th style="text-align:right;">
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
	</div>
</div>
