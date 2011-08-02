<?php if($select): ?>
<select name="pgroup_id" style="width:120px;">
	<?php foreach (PeopleGroup::model()->findAll() as $group): ?>
	<option <?=$group->primaryKey==$user->pgroup_id? 'selected="selected"' : '';?> value="<?= $group->primaryKey;?>"><?= $group->name; ?></option>
	<?php endforeach; ?>
</select>
<?php else: ?>
	<input type="text" disabled="disabled" name="pgroup_id" value="<?=$user->people_group->name;?>">
<?php endif; ?>