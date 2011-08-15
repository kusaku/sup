<ul class="columnsBody">
	<?php 
	if ($client_id) {
		// генерируем блок по выбранному клиенту (ответ на AJAX запрос)
		Package::genClientBlock($client_id);
	} else {
		$packs = Yii::app()->user->checkAccess('admin') ? Package::getLast(30) : Package::getMy(30);
		foreach ($packs as $pack) {
			if ($pack->status_id != 15 and $pack->status_id != 999) {
				Package::genClientBlock($pack->client_id);
			}
		}
	}
	?>
</ul>
