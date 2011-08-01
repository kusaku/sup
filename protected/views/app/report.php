<div id="modal"></div>
<div id="sup_popup" class="popup"></div>
<div id="sup_preloader" class="popup"><img src="/images/preloader.gif" boreder="0"></div>

<div class="wrapper">
	<div class="logo">
		<h1><a href="/" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="userBar">
		<ul>
			<li><a onClick="editCalendarEvent(0)" class="addOrder">Напоминание</a></li>
			<li><a onClick="addEditClient(0)" class="addClient">Добавить клиента</a></li>
			<li><a onClick="selectReportType()" class="lastDone">Создать отчет</a></li>
			<li><a href="http://doc.fabricasaitov.ru/" target="_blanck" class="notWorked">Wiki-справка</a></li>
		</ul>
		<form method="post" action="#">
			<input class="searchClient" id="searchClient" name="clientName" placeholder="Поиск клиента..." size="67"/><a onClick="searchClear()" class="buttonClear hidden" id="buttonClear"></a>
		</form>
		<a href="#" class="userName"><?= Yii::app()->user->fio?></a>
		<a href="/app/logout" class="logout">выход</a>
	</div>
	<div class="report">
		<div class="reportButtons">
			<a style="float:right;" class="orangeButton" onclick="alert('Пока не работает =(')">Печать</a>
		</div>		
		<?php foreach ($data as $managerItem): ?>
		<?php if (!$managerItem['count']) continue; ?>
		<h1><?= $managerItem['name']?></h1>
		<?php foreach ($managerItem['packs'] as $packItem): ?>
		<h2><?= $packItem['client']?>  &lt;<a href="mailto:<?= $packItem['clientmail']?>"><?= $packItem['clientmail']?></a>&gt;</h2>
		<h2>					
			<?php if(!empty($packItem['descr'])) : ?>
			<?= $packItem['descr']?>
			<?php else : ?>
			<?= $packItem['name']?>
			<?php endif; ?> : <a href="<?= $packItem['site']?>"><?= $packItem['site']?></a>
		</h2>
		<table class="reportItem">
			<tr>
				<th>Статус</th>
				<th colspan="6">Услуги</th>
			</tr>
			<tr>
				<th style="width: 25%" rowspan="<?= $packItem['count']+1 ?>">
					<p><?= $packItem['status']?></p>
					<p>Изменен: <?= $packItem['dt_change']?></p>
					<p>Период: <?= $packItem['dt_beg']?> - <?= $packItem['dt_end']?></p>						
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
		</table>
		<?php endforeach; ?>
		<h1>Подитог:</h1>
		<table class="reportItem">
			<tr>
				<th>Заказов</th>
				<th>Сумма</th>
			</tr>
			<tr>
				<td><?= $managerItem['count']?></td>
				<td><?= number_format($managerItem['summ'], 0, ',', ' ')?> руб.</td>
			</tr>
		</table>
		<?php endforeach; ?>
		<h1>Итог:</h1>
		<table class="reportItem">
			<tr>
				<th>Заказов</th>
				<th>Сумма</th>
			</tr>
			<tr>
				<td><?= $total['count']?></td>
				<td><?= number_format($total['summ'], 0, ',', ' ')?> руб.</td>
			</tr>
		</table>			
	</div>
</div>
