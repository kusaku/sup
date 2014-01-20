<select name="people_id<?= $index; ?>">
	<option value="0">--выберите--</option>
	<?php foreach ($groups as $group): ?>
	<optgroup label="<?= $group->name; ?>">
		<?php foreach ($group->peoples as $people): ?>
		<option<?= (isset($selected) and $selected == $people->primaryKey) ? ' selected="selected"' : ''; ?> value="<?=$people->primaryKey; ?>"><?= $people->fio; ?></option>
		<?php endforeach; ?>
	</optgroup>
	<?php endforeach; ?>
</select>
