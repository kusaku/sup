<select name="status_id" style="width:120px;">
	<option value="0">--любой статус--</option>
	<option value="-1">Любые оплаченные</option>
	<?php foreach (PackageStatus::model()->findAll(array('order'=>'id ASC')) as $status): ?>
	<option value="<?= $status->primaryKey;?>"><?= $status->name; ?></option>
	<?php endforeach; ?>
</select>