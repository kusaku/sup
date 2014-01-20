<div class="userlist">
	<?php foreach (PeopleGroup::model()->findAllByPk(array( 1, 2, 3, 4, 5, 8, 9, 11, 12)) as $group): ?>
	<h1><?= $group->name; ?></h1>
	<ul>
		<?php foreach ($group->peoples as $user): ?>
		<li><a href="#" onclick="addEditClient(<?=$user->primaryKey; ?>)"><?= $user->fio; ?> (<?= $user->login; ?>)</a></li>
		<?php endforeach; ?>
	</ul>
	<?php endforeach; ?>
</div>
