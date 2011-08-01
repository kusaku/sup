<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform" action="/report/generate" method="GET">
		<div class="clientHead">Выбор вида отчета</div>
		<div style="padding:10px 0px;">
			<label>Вид отчета: </label>
			<select id="reportType" name="reportType">
				<?php if ($roles['admin']): ?>
				<option value="allmanagers">Отчет по всем менеджерам</option>
				<option value="onemanager">Отчет по выбранному менеджеру</option>
				<?php endif; ?>
				<option value="monthly">Отчет за месяц</option>
			</select>
			<?php if ($roles['admin']): ?>
			<label>Менеджер: </label>
			<select id="manager_id" name="manager_id">
				<?php foreach ($managers as $manager): ?>
				<option value="<?=$manager->primaryKey ?>"><?= $manager->fio?></option>
				<?php endforeach; ?>
			</select>
			<?php endif; ?>
		</div>
		<div>
		<label>От: </label>
		<input type="text" name="from" class="datepicker">
		<label>До: </label>
		<input type="text" name="to" class="datepicker">
		</div>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" class="orangeButton" onclick="$('#megaform').submit();">Генерировать</a>
		</div>
	</form>
</div>
