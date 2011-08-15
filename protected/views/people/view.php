<!--		Возвращаем форму для создания/редактирования человека		-->
<div class="newClientWindow" style="margin-bottom: 5px;">
	<div class="clientHead">Информация о клиенте</div>
	<form action="/people/save" method="POST" name="megaform">
		<div style="float:left;width:275px;">
			<input type="hidden" name="id" value="<?=$people->id?>"><input type="hidden" name="parent_id" value="<?=$people->parent_id?>">
			<?php if (Yii::app()->user->checkAccess('admin') or Yii::app()->user->checkAccess('moder')): ?>
			<label>Роль: </label>
			<?php 
			$this->renderPartial('/snippets/roles', array(
				'select'=>true, 'user'=>$people,
			));
			?>
			<?php endif; ?>
			<label>
				Имя<span class="orange">*</span>: 
			</label>
			<input type="text" value="<?=$people->fio?>" size="26" name="fio"/>
			<label>
				E-mail<span class="orange">*</span>: 
			</label>
			<input type="text" value="<?=$people->mail?>" size="26" name="mail"/>
			<label>Телефон: </label>
			<input type="text" value="<?=$people->phone?>" size="26" name="phone"/>
			<label>Организация: </label>
			<input type="text" value="<?=$people->firm?>" size="26" name="firm"/>
			<label>Город: </label>
			<input type="text" value="<?=$people->state?>" size="26" name="state"/>
			<label>Примечание: </label>
			<textarea name="descr" rows="3" cols="12"><?= $people->descr?></textarea>
		</div>
		<div style="float:right;width:295px;margin-top:10px;max-height:400px;overflow-y:auto;">
			<?php if (! empty($people->attr['bm_id']->values[0]->value)): ?>
			<div>
				<span><a class="add_open" id="linkid-<?= $people->primaryKey; ?>" onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) bmOpen(<?= $people->primaryKey; ?>); else $('#linkid-<?= $people->primaryKey; ?>').tipBox('Ошибка сохранения!').tipBox('show');});" href="#"></a>Открыть BILLManager</span>
			</div>
			<div>
				<span><a class="add_bm" id="linkid-<?= $people->primaryKey; ?>" onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) bmUpdateAttributes(<?= $people->primaryKey; ?>); else $('#linkid-<?= $people->primaryKey; ?>').tipBox('Ошибка сохранения!').tipBox('show');});" href="#"></a>Подгрузить данные из BILLManager</span>
			</div>
			<?php else : ?>
			<?php if ($people->primaryKey): ?>
			<div>
				<span><a class="add_bm" id="linkid-<?= $people->primaryKey; ?>" onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) bmRegister(<?= $people->primaryKey; ?>); else $('#linkid-<?= $people->primaryKey; ?>').tipBox('Ошибка сохранения!').tipBox('show');});" href="#"></a>Регистрация в BILLManager</span>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<div class="supAccordion">
				<?php foreach (Attributes::model()->with('children')->getGroups() as $group): ?>
				<h3 style="cursor: pointer;"><?= $group->name?></h3>
				<div style="display:none;">
					<?php foreach ($group->children as $attr): ?>
					<label for="<?= $attr->type ?>">
						<?= $attr->name; ?>
					</label>
					<?php if ($set = unserialize($attr->set)): ?>
					<select style="width:135px" id="<?= $attr->type ?>" name="attr[<?= $attr->primaryKey ?>]">
						<?php foreach ($set as $option): ?>
						<option value="<?=$option['value'] ?>"<?= (isset($people->attr[$attr->type]) and $option['value'] == $people->attr[$attr->type]->values[0]->value) ? ' selected="selected"' : ''?>><?= $option['name']?></option>
						<?php endforeach; ?>
					</select>
					<?php else : ?>
					<input type="text" size="70" id="<?= $attr->type ?>" value="<?= isset($people->attr[$attr->type]) ? $people->attr[$attr->type]->values[0]->value : '' ?>" name="attr[<?= $attr->primaryKey ?>]">
					<?php endif; ?>
					<? endforeach; ?>
				</div>
				<? endforeach; ?>
			</div>
		</div>
	</form>
	<div class="buttons">
		<p><span class="orange">* - обязательные поля</span></p>
		<a onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) Package(0, data.people_id); else alert('Ошибка сохранения!')});" class="plus" title="Сохранить клиента и добавить ему заказ"></a>
		<a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
		<a class="buttonCancel" onClick="hidePopUp()">Отмена</a>
	</div>
</div>
