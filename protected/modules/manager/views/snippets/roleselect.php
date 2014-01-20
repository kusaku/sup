<select name="group_id<?= isset($index) ? "[{$index}]"  : ''; ?>">
	<option value="0">--выберите--</option>
	<?php foreach (PeopleGroup::model()->findAll() as $group): ?>
	<option<?= (isset($selected) and $selected == $group->primaryKey) ? ' selected="selected"' : ''; ?> value="<?=$group->primaryKey; ?>"><?= $group->name; ?></option>
	<?php endforeach; ?>
</select>
