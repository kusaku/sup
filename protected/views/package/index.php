<ul class="columnsBody">
<?php

$packs = New Package();
$packages = $packs->getTop(30);
$a = array();

if ($client_id)
{
	/* Генерируем блок по выбранному клиенту (ответ на AJAX запрос)
	 */
	Package::genClientBlock($client_id);
}
else
if (isset ( $packages ))
foreach ($packages as $pack)
{
	/*	Выбираем свои заказы, которые ещё не вбрали, статус которых "не в архиве" и "не сдан клиенту"
	 */
	if ( !isset($a[$pack->client_id]) and $pack->status_id != 15 and $pack->status_id != 999 )
	{
		$a[$pack->client_id] = 1;
		Package::genClientBlock($pack->client_id);
	}
}

?>
</ul>