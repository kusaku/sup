<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform" action="/report/generate" method="POST">
		<div class="clientHead">Выбор вида отчета</div>
		<div style="padding:10px 0px;">
			<label>Вид отчета: </label>
			<select id="reportType" name="reportType">
				<?php if ($roles['admin']): ?>
				<option<?= UserRegistry::model()->report_reportType == 'pays' ? ' selected="selected"' : ''?> value="pays">Отчет по выплатам</option>
				<option<?= UserRegistry::model()->report_reportType == 'projects' ? ' selected="selected"' : ''?> value="projects">Отчет по проектам</option>
				<?php else : ?>
				<option<?= UserRegistry::model()->report_reportType == 'mypays' ? ' selected="selected"' : ''?> value="mypays">Мой отчет по выплатам</option>
				<option<?= UserRegistry::model()->report_reportType == 'myprojects' ? ' selected="selected"' : ''?> value="myprojects">Мой отчет по проектам</option>				
				<?php endif; ?>
			</select>
			<?php if ($roles['admin']): ?>
			<label>Менеджер: </label>
			<select id="manager_id" name="manager_id">
				<?php if ($roles['admin']): ?>
				<option<?= UserRegistry::model()->report_manager_id == 0 ? ' selected="selected"' : ''?> value="0">Все менеджеры</option>
				<?php endif; ?>
				<?php foreach ($managers as $manager): ?>
				<option<?= UserRegistry::model()->report_manager_id == $manager->primaryKey ? ' selected="selected"' : ''?> value="<?=$manager->primaryKey ?>"><?= $manager->fio?></option>
				<?php endforeach; ?>
			</select>
			<?php endif; ?>
		</div>
		<div>
			<label>От: </label>
			<input type="text" name="dt_beg" class="datepicker" value="<?= UserRegistry::model()->report_dt_beg ? UserRegistry::model()->report_dt_beg : date('01.m.Y'); ?>">
			<label>До: </label>
			<input type="text" name="dt_end" class="datepicker" value="<?= UserRegistry::model()->report_dt_end ? UserRegistry::model()->report_dt_end : date('01.m.Y', strtotime('+1 month')); ?>"></div>
		<div>
			<label>Статус: </label>
			<select name="status_id">
				<option<?= UserRegistry::model()->report_status_id == -1 ? ' selected="selected"' : ''?> value="-1">Любой статус</option>
				<option<?= UserRegistry::model()->report_status_id == -2 ? ' selected="selected"' : ''?> value="-2">Любые оплаченные</option>
				<?php foreach (Status::model()->findAll(array( 'order'=>'id ASC' )) as $status): ?>
				<option<?= UserRegistry::model()->report_status_id == $status->primaryKey ? ' selected="selected"' : ''?> value="<?= $status->primaryKey;?>"><?= $status->name; ?></option>
				<?php endforeach; ?>
			</select>
			<label style="width:180px;">Выводить пустые заказы</label>
			<input<?= UserRegistry::model()->report_show_empty ? ' checked="checked"' : ''?> type="checkbox" name="show_empty">
		</div>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" class="orangeButton" onclick="$('#megaform').attr('action',$('#megaform').attr('action') + '/' + $('#megaform #reportType').val()).submit();">Генерировать</a>
		</div>
	</form>
</div>
