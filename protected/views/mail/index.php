<div style="margin-bottom: 5px;" class="newClientWindow">
	<div class="editClientWindow" id="sm_content">
		<form id="megaform" action="/mail/savetemplate" method="POST">
			<div class="clientHead">Список шаблонов писем</div>
			<div class="scroll-wrap" style="padding:10px 5px;">
				<div class="scroll-pane">
					<?php foreach ($templates as $template): ?>
					<div id="orderBlock<?=$template->id?>" class="orderBlock">
						<div class="header">
							<a class="arrow" onclick="CardShowHide(<?=$template->id?>)"></a>
							<a class="arrow" onclick="CardShowHide(<?=$template->id?>)"><?= $template->name?></a>
							<?php if ($template->people_id == 0): ?>
							(общий)
							<?php else : ?>
							(персональный)
							<?php endif; ?>
						</div>
						<div class="orderPart hidden">
							<label>Название шаблона</label>
							<input type="text" name="name[<?=$template->id?>]" value="<?=$template->name?>" />
							<br/>
							<input type="radio"<?= !$template->people_id ? 'checked="checked"' : ''?> name="people_id[<?=$template->id?>]" value="0" /><label>Общий</label>
							<input type="radio"<?= $template->people_id ? 'checked="checked"' : ''?> name="people_id[<?=$template->id?>]" value="<?=Yii::app()->user->id?>" /><label>Персональный</label>
							<br/>
							<input type="text" name="subject[<?=$template->id?>]" value="<?=$template->subject?>" />
							<textarea rows="7" cols="70" name="body[<?=$template->id?>]"><?= $template->body?></textarea>
						</div>
					</div>
					<?php endforeach; ?>
					<div id="orderBlock0" class="orderBlock">
						<div class="header">
							<a class="arrow" onclick="CardShowHide(<?=$template->id?>)"></a>
							<a class="arrow" onclick="$('#newtplflag').val($('#newtplflag').val() ? '' : '1');CardShowHide(0);">Создать новый шаблон</a>
						</div>
						<div class="orderPart hidden">
							<label>Название шаблона</label>
							<input type="text" name="name[0]" value="Новый шаблон" />
							<br/>
							<input type="radio" name="people_id[0]" value="0" /><label>Общий</label>
							<input type="radio" name="people_id[0]" value="<?=Yii::app()->user->id?>" /><label>Персональный</label>
							<br/>
							<input type="text" name="subject[0]" value="Тема письма" />
							<textarea rows="7" cols="70" name="body[0]">Тело письма</textarea>
							<input id="newtplflag" type="hidden" name="new[0]" value="0" />
						</div>
					</div>
				</div>
			</div><a class="orangeButton" onclick="saveAndProceed('#megaform', function(){alert('Сохранено.')});">Сохранить</a>
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
		</form>
	</div>
</div>