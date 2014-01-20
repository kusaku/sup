<select name="status_id" style="width:120px;">
	<option value="-1">--не изменять--</option>
	<?php foreach (PackageStatus::model()->findAll(array( 'order'=>'id ASC' )) as $status): ?>
	<option<?= (isset($selected) and $selected == $status->primaryKey) ? ' selected="selected"' : ''; ?> value="<?= $status->primaryKey;?>"><?= $status->name; ?></option>
	<?php endforeach; ?>
</select>
