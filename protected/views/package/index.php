<ul class="columnsBody">
	<?php 
	if ($client_id) {
		// генерируем блок по выбранному клиенту (ответ на AJAX запрос)
		Package::genClientBlock($client_id);
	} else {
		$a = array();
		foreach (Package::getTop(30) as $pack) {
			// выбираем свои заказы, которые ещё не вбрали, статус которых "не в архиве" и "не сдан клиенту"
			if ( !isset($a[$pack->client_id]) and $pack->status_id != 15 and $pack->status_id != 999 ) {
				$a[$pack->client_id] = 1;
				Package::genClientBlock($pack->client_id);
			}
		}
	}
	?>
</ul>
