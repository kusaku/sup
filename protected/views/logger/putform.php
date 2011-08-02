<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform" action="/logger/put" method="POST">
		<div class="clientHead">Журнал</div>
		<div style="padding:10px 5px;">
			<textarea style="width:550px;height:100px;" name="info"></textarea>
			<input type="hidden" id="client_id" name="client_id" value="<?=$client_id?>" />
		</div>
		<a style="float:right;" class="orangeButton" onclick="saveAndProceed('#megaform', function(success){ if (success) {hidePopUp(); loggerForm(<?=$client_id?>); }});">Добавить запись</a>
		<?php if (count($records = Logger::get($client_id))): ?>
		<div style="clear:both;" class="orderBlock" id="orderBlock0">
			<div class="header">
				<a onClick="CardShowHide(0)" class="arrow"></a>
				<a onClick="CardShowHide(0)">Записи в журнале</a>
			</div>
			<div style="max-height:300px;overflow:auto;" class="orderPart">
				<?php foreach ($records as $record): ?>
				<div class="subPart">
					<div class="column1">
						<p class="label">Менеджер:</p>
						<p><?= People::getNameById($record->manager_id)?></p>
					</div>
					<div class="column2">
						<p class="label">Дата:</p>
						<p><?= $record->dt; ?></p>
					</div>
					<div style="clear:both;padding:5px 0px;">
						<?= $record->info; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>			
		</div>
	</form>
</div>
