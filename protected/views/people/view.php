<!--		Возвращаем форму для создания/редактирования человека		-->

<div class="newClientWindow" style="margin-bottom: 5px;">
	<div class="clientHead">Информация о клиенте</div>
	<form action="/people/save" method="POST" name="megaform">
		<input type="hidden" name="id"	value="<?=$people->id?>">
		<input type="hidden" name="parent_id"	value="<?=$people->parent_id?>">
		<label>Имя<span class="orange">*</span>: </label>
		<input type="text" value="<?=$people->fio?>" size="26" name="fio"/>
		<label>E-mail<span class="orange">*</span>: </label>
		<input type="text" value="<?=$people->mail?>" size="26" name="mail"/>
		<label>Телефон: </label>
		<input type="text" value="<?=$people->phone?>" size="26" name="phone"/>
		<label>Организация: </label>
		<input type="text" value="<?=$people->firm?>" size="26" name="firm"/>
		<label>Город: </label>
		<input type="text" value="<?=$people->state?>" size="26" name="state"/>
		<label>Примечание: </label>
		<textarea name="descr" rows="3" cols="12"><?=$people->descr?></textarea>
	</form>
	<div class="buttons">
		<p><span class="orange">* - обязательные поля</span></p>
		<!-- <a href="javascript:alert('Пока не работает.');" class="plus" title="Сохранить клиента и добавить ему заказ"></a> -->
		<a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
		<a class="buttonCancel" onClick="hidePopUp()">Отмена</a>
	</div>
</div>