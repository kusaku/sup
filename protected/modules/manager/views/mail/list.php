<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform" action="/manager/mail/send" method="POST">
		<div class="clientHead">Выбор шаблона письма</div>
		<div style="padding:10px 5px;">
			<select id="template_id" name="template_id">
				<?php foreach ($templates as $template): ?>
				<option value="<?=$template->id?>"><?= $template->name.' '.($template->people_id ? '(персональный)' : '(общий)')?></option>
				<?php endforeach; ?>
			</select>
			<a class="orangeButton" onclick="EditMailTemplates()">Редактировать</a>
			<input type="hidden" id="package_id" name="package_id" value="<?=$package_id?>" />
		</div>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" class="orangeButton" onclick="SendMail()">Отправить!</a>			
		</div>
	</form>
</div>
