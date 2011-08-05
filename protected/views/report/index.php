<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform" action="/report/generate" method="GET">
		<div class="clientHead">Выбор вида отчета</div>
		<div style="padding:10px 0px;">
			<label>Вид отчета: </label>
			<select id="reportType" name="reportType">
				<?php if ($roles['admin']): ?>
				<option <?= UserRegistry::model()->report_reportType == 'allmanagers' ? 'selected="selected"' : ''?> value="allmanagers">Отчет по всем менеджерам</option>
				<option <?= UserRegistry::model()->report_reportType == 'onemanager' ? 'selected="selected"' : ''?> value="onemanager">Отчет по выбранному менеджеру</option>
				<?php endif; ?>
				<option <?= UserRegistry::model()->report_reportType == 'myself' ? 'selected="selected"' : ''?> value="myself">Мой отчет</option>
			</select>
			<?php if ($roles['admin']): ?>
			<label>Менеджер: </label>
			<select id="manager_id" name="manager_id">
				<?php foreach ($managers as $manager): ?>
				<option <?= UserRegistry::model()->report_manager_id == $manager->primaryKey ? 'selected="selected"' : ''?> value="<?=$manager->primaryKey ?>"><?= $manager->fio?></option>
				<?php endforeach; ?>
			</select>
			<?php endif; ?>
		</div>
		<div>
			<label>От: </label>
			<input type="text" name="dt_beg" class="datepicker" value="<?= UserRegistry::model()->report_dt_beg ? UserRegistry::model()->report_dt_beg : date('Y-m-01'); ?>">
			<label>До: </label>
			<input type="text" name="dt_end" class="datepicker" value="<?= UserRegistry::model()->report_dt_end ? UserRegistry::model()->report_dt_end : date('Y-m-01', strtotime('+1 month')); ?>"></div>
		<div>
			<label>Статус: </label>
			<?= $this->renderPartial('/snippets/statuses'); ?>
			<label>Выводить пустые заказы: </label>
			<input <?= UserRegistry::model()->report_show_empty ? 'checked="checked"' : ''?> type="checkbox" name="show_empty"></div>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" class="orangeButton" onclick="$('#megaform').submit();">Генерировать</a>
		</div>
	</form>
</div>
