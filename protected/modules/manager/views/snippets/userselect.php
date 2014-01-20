<select name="people_id<?= isset($index) ? "[{$index}]"  : ''; ?>">
	<option value="0">--выберите--</option>
	<?php foreach (PeopleGroup::model()->findAllByPk(isset($group_id) ? $group_id : array( 1,2,3,4,5,8,9,11 )) as $group): ?>
	<optgroup label="<?= $group->name; ?>">
		<?php foreach ($group->peoples as $people): ?>
		<option<?= (isset($selected) and $selected == $people->primaryKey) ? ' selected="selected"' : ''; ?> value="<?=$people->primaryKey; ?>"><?= $people->fio; ?></option>
		<?php endforeach; ?>
	</optgroup>
	<?php endforeach; ?>
</select>
