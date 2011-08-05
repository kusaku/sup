<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform">
		<div class="clientHead">Массовая рассылка</div>
		<div style="padding:10px 5px;">
			<label for="filter">Вид рассылки:</label>
			<select id="filter" name="filter">
				<option value="employers">Всем сотрудникам</option>
				<option value="managers">Всем менеджерам</option>
				<option value="clients">Всем клиентам</option>
				<option value="partners">Всем партнерам</option>
				<option value="bigclients">Крупным клиентам</option>
				<option value="newclients">Новым клиентам</option>
				<option value="seoclients">Клиентам с SEO</option>
			</select>
		</div>
		<div style="padding:10px 5px;">
			<select id="template_id" name="template_id">
				<?php foreach ($templates as $template): ?>
				<option value="<?=$template->id?>"><?= $template->name.' '.($template->people_id ? '(персональный)' : '(общий)')?></option>
				<?php endforeach; ?>
			</select>
			<a class="orangeButton" onclick="EditMailTemplates()">Редактировать</a>
		</div>
		<div>
			<div style="margin:5px 10px;padding:2px;border:1px solid #ff8000;">
				<div id="progress" style="width:0;height:20px;background:#ff8000;" class="orangeButton"></div>
			</div>
		</div>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" id="stagebutton" class="orangeButton" onclick="resetQueue()">Сгенерировать письма</a>
		</div>
	</form>
</div>
