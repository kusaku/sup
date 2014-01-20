<?php
/**
 * @var ReportController $this
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:600px;'));
?>
<div class="formHead">Выбор вида отчета</div>
<div class="formBody">
	<form id="megaform" action="/manager/report/generate" method="get">
		<div class="formRows">
			<div class="formRow">
				<label>Вид отчета:</label>
				<select name="reportType">
					<?php if ($roles['admin'] || $roles['marketolog'] || $roles['topmanager']): ?>
					<option<?= UserRegistry::model()->report_reportType == 'recspays' ? ' selected="selected"' : ''?> value="recspays">Отчет по финансам</option>
					<option<?= UserRegistry::model()->report_reportType == 'recs' ? ' selected="selected"' : ''?> value="recs">Отчет по платежкам</option>
					<option<?= UserRegistry::model()->report_reportType == 'recs2' ? ' selected="selected"' : ''?> value="recs2">Отчет по неподтверждённым платежам</option>
					<option<?= UserRegistry::model()->report_reportType == 'pays' ? ' selected="selected"' : ''?> value="pays">Отчет по оплатам</option>
					<option<?= UserRegistry::model()->report_reportType == 'projects' ? ' selected="selected"' : ''?> value="projects">Отчет по проектам</option>
					<option<?= UserRegistry::model()->report_reportType == 'seo' ? ' selected="selected"' : ''?> value="seo">Отчет по промо</option>
					<option<?= UserRegistry::model()->report_reportType == 'seo2' ? ' selected="selected"' : ''?> value="seo2">Отчет по заказам с сайта</option>
					<option<?= UserRegistry::model()->report_reportType == 'partnerRecsPays' ? ' selected="selected"' : ''?> value="partnerRecsPays">Отчет по оплатам партнёров</option>
					<option<?= UserRegistry::model()->report_reportType == 'partner' ? ' selected="selected"' : ''?> value="partner">Отчет по партнёрам</option>
					<option<?= UserRegistry::model()->report_reportType == 'partner2' ? ' selected="selected"' : ''?> value="partner2">Отчет по заказам партнеров</option>
					<?php else : ?>
					<option<?= UserRegistry::model()->report_reportType == 'myrecspays' ? ' selected="selected"' : ''?> value="myrecspays">Мой отчет по финансам</option>
					<option<?= UserRegistry::model()->report_reportType == 'myrecs' ? ' selected="selected"' : ''?> value="myrecs">Мой отчет по платежкам</option>
					<option<?= UserRegistry::model()->report_reportType == 'mypays' ? ' selected="selected"' : ''?> value="mypays">Мой отчет по оплатам</option>
					<option<?= UserRegistry::model()->report_reportType == 'myprojects' ? ' selected="selected"' : ''?> value="myprojects">Мой отчет по проектам</option>
					<option<?= UserRegistry::model()->report_reportType == 'partner' ? ' selected="selected"' : ''?> value="partner">Мой отчет по партнёрам</option>
					<?php endif; ?>
				</select>
			</div>
			<?php if ($roles['admin'] || $roles['marketolog']): ?>
			<div class="formRow">
				<label>Менеджер:</label>
				<select id="manager_id" name="manager_id">
					<?php if ($roles['admin'] || $roles['moder'] || $roles['marketolog']): ?>
						<option<?= UserRegistry::model()->report_manager_id == 0 ? ' selected="selected"' : ''?> value="0">Все менеджеры</option>
					<?php endif; ?>
					<?php foreach ($managers as $manager): ?>
						<option<?= UserRegistry::model()->report_manager_id == $manager->primaryKey ? ' selected="selected"' : ''?> value="<?=$manager->primaryKey ?>"><?= $manager->fio?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<div class="formRow">
				<label>От:</label>
				<input type="text" name="dt_beg" class="datepicker" value="<?= UserRegistry::model()->report_dt_beg ? UserRegistry::model()->report_dt_beg : date('01.m.Y'); ?>">
			</div>
			<div class="formRow">
				<label>До:</label>
				<input type="text" name="dt_end" class="datepicker" value="<?= UserRegistry::model()->report_dt_end ? UserRegistry::model()->report_dt_end : date('01.m.Y', strtotime('+1 month')); ?>">
			</div>
			<div class="formRow">
				<label>Статус заказа:</label>
				<select name="status_id">
					<option<?= UserRegistry::model()->report_status_id == -1 ? ' selected="selected"' : ''?> value="-1">-любой-  </option>
					<?php foreach (PackageStatus::model()->findAll(array( 'order'=>'id ASC' )) as $status): ?>
					<option<?= UserRegistry::model()->report_status_id == $status->primaryKey ? ' selected="selected"' : ''?> value="<?= $status->primaryKey;?>"><?= $status->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="formRow">
				<label>Статус оплаты:</label>
				<select name="payment_id">
					<option<?= UserRegistry::model()->report_status_id == -1 ? ' selected="selected"' : ''?> value="-1">-любой-  </option>
					<?php foreach (PackagePayment::model()->findAll(array( 'order'=>'id ASC' )) as $status): ?>
					<option<?= UserRegistry::model()->report_payment_id == $status->primaryKey ? ' selected="selected"' : ''?> value="<?= $status->primaryKey;?>"><?= $status->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="formRow fullRow">
				<label>Выводить пустые заказы:</label>
				<input<?= UserRegistry::model()->report_show_empty ? ' checked="checked"' : ''?> type="checkbox" name="show_empty">
			</div>
		</div>
		<div class="buttons">
			<a class="buttonGray" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" class="buttonOrange" onclick="$('#megaform').submit();">Генерировать</a>
		</div>
	</form>
</div>
</div>