<select name="payment_id" style="width:120px;">
	<option value="-1">--не изменять--</option>
	<?php foreach ($arStatuses as $status): ?>
		<option<?php if($status['selected']):?> selected="selected"<?php endif?><?php if($status['disabled']):?> disabled="disabled"<?php endif?> value="<?php echo $status['id'];?>"><?php echo $status['name'];?></option>
	<?php endforeach; ?>
</select>