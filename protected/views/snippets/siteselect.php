<?php if ($sites = People::model()->findByPk($client_id)->sites): ?>
<select name="site_id">
	<option value="0">[без сайта]</option>
	<optgroup label="Сайты">
		<?php foreach ($sites as $site): ?>
		<option <?= (isset($selected) and $selected == $site->primaryKey) ? 'selected="selected"' : ''; ?> value="<?=$site->primaryKey; ?>"><?= $site->url; ?></option>
		<?php endforeach; ?>
	</optgroup>
</select>
<?php else : ?>
<select name="site_id">
	<optgroup label="Сайты">
		<option selected="selected" value="0">[сайтов нет]</option>
	</optgroup>
</select>
<?php endif; ?>