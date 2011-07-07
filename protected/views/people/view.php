<!--		Возвращаем форму для создания/редактирования человека		-->
<div class="newClientWindow" style="margin-bottom: 5px;">
	<div class="clientHead">Информация о клиенте</div>
	<form action="/people/save" method="POST" name="megaform">
		<div style="float:left;width:275px;">
			<input type="hidden" name="id" value="<?=$people->id?>"><input type="hidden" name="parent_id" value="<?=$people->parent_id?>"><label>Имя<span class="orange">*</span>: </label>
			<input type="text" value="<?=$people->fio?>" size="26" name="fio"/><label>E-mail<span class="orange">*</span>: </label>
			<input type="text" value="<?=$people->mail?>" size="26" name="mail"/><label>Телефон: </label>
			<input type="text" value="<?=$people->phone?>" size="26" name="phone"/><label>Организация: </label>
			<input type="text" value="<?=$people->firm?>" size="26" name="firm"/><label>Город: </label>
			<input type="text" value="<?=$people->state?>" size="26" name="state"/><label>Примечание: </label>
			<textarea name="descr" rows="3" cols="12"><?= $people->descr?></textarea>
			<?php if (!empty($people->attr['bm_id']->values[0]->value)): ?>
			<a style="padding:5px 20px;display:block;" id="linkid-<?= $people->primaryKey; ?>" onclick="bmOpen(<?= $people->primaryKey; ?>)" href="#">Открыть в BM (id <?= $people->attr['bm_id']->values[0]->value; ?>)</a>
			<?php else : ?>
			<?php if ($people->primaryKey): ?>
			<a style="padding:5px 20px;display:block;" id="linkid-<?= $people->primaryKey; ?>" onclick="bmRegister(<?= $people->primaryKey; ?>)" href="#">Зарегистрировать в BM</a>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<div style="float:right;width:295px;margin-top:10px;max-height: 575px;overflow-y:scroll;">
			<div class="supAccordion">
				<?php foreach (Attributes::model()->with('children')->getGroups() as $group): ?>
				<h3 style="cursor: pointer;"><?= $group->name?></h3>
				<div style="display:none;">
					<?php foreach ($group->children as $attr): ?>
					<label for="<?= $attr->type ?>">
						<?= $attr->name; ?>
					</label>
					<input type="text" size="70" id="<?= $attr->type ?>" value="<?= isset($people->attr[$attr->type]) ? $people->attr[$attr->type]->values[0]->value : '' ?>" name="attr[<?= $attr->primaryKey ?>]">
					<? endforeach; ?>
				</div>
				<? endforeach; ?>
			</div>
		</div>
	</form>
	<div class="buttons">
		<p><span class="orange">* - обязательные поля</span></p>
		<a onclick="saveAndProceed('#sup_popup form', Package)" class="plus" title="Сохранить клиента и добавить ему заказ"></a>
		<a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
		<a class="buttonCancel" onClick="hidePopUp()">Отмена</a>
	</div>
</div>
