<div style="margin-bottom: 5px;" class="newClientWindow">
	<div class="editClientWindow" id="sm_content">
		<form id="megaform" action="/manager/mail/savetemplate" method="POST">
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
							<label>Общий</label>
							<input type="radio"<?= !$template->people_id ? 'checked="checked"' : ''?> name="people_id[<?=$template->id?>]" value="0" />
							<label>Персональный</label>
							<input type="radio"<?= $template->people_id ? 'checked="checked"' : ''?> name="people_id[<?=$template->id?>]" value="<?=Yii::app()->user->id?>" />
							<br/>
							<input type="text" name="subject[<?=$template->id?>]" value="<?=$template->subject?>" /><textarea rows="7" cols="70" name="body[<?=$template->id?>]"><?= $template->body?></textarea>
						</div>
					</div>
					<?php endforeach; ?>
					<div id="orderBlock0" class="orderBlock">
						<div class="header">
							<a class="arrow" onclick="CardShowHide(0)"></a>
							<a class="arrow" onclick="$('#newtplflag').val($('#newtplflag').val() ? '' : '1');CardShowHide(0);">Создать новый шаблон</a>
						</div>
						<div class="orderPart hidden">
							<label>Название шаблона</label>
							<input type="text" name="name[0]" value="Новый шаблон" />
							<br/>
							<label>Общий</label>
							<input type="radio" name="people_id[0]" value="0" />
							<label>Персональный</label>
							<input type="radio" name="people_id[0]" value="<?=Yii::app()->user->id?>" />
							<br/>
							<input type="text" name="subject[0]" value="Тема письма" /><textarea rows="7" cols="70" name="body[0]">Тело письма</textarea>
							<input id="newtplflag" type="hidden" name="new[0]" value="0" />
						</div>
					</div>
				</div>
			</div>
			<div class="buttons">
				<a class="grayButton" onclick="hidePopUp();">Отмена</a>
				<a style="float:right;" id="linkid-<?= Yii::app()->user->id?>" class="orangeButton" onclick="saveAndProceed('#megaform', function(data){ if(data.success) hidePopUp();else $('#linkid-<?= Yii::app()->user->id?>').tipBox('Ошибка сохранения!').tipBox('show');});">Сохранить</a>
			</div>
		</form>
	</div>
</div>
