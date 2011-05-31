<?php
/*
 * Форма оплаченного заказа.
 * Тут мы читаем сообщения из редмайна.
 */


/*
 * Возвращаем SELECT с выбором сайта. Ипользуются сайты, закреплённые за проектам клиентами
 *
 * НУЖНО ПЕРЕДЕЛАТЬ - AJAX
 */
function sites($client_id, $sel = 0)
{
	$sites = Site::getAllByClient($client_id);
	$res = '<select name="pack_site_id"><option value="0">..</option>';
	if ( isset( $sites ) )
	foreach ($sites as $site)
	{
		$res = $res.'<option value="'.$site->id.'"';

		if ( $sel == $site->id)
			$res = $res." selected";

		$res = $res.'>'.$site->url.'</option>';
	}
	$res = $res.'</select>';
	return $res;
}

$zserv = array(); // Заказанные сервисы/услуги


	//$pack = Package::getById($package_id);
	$client = $pack->client;
	$zakaz_size =  sizeof( $pack->servPack );
	$client_id = $pack->client_id;
	$pack_id = $pack->id;

	foreach ($pack->servPack as $key => $zakaz)
	{
		$zserv[$zakaz->serv_id] = $zakaz;
	}

?>

<div class="wrapper">
<div class="editClientWindow" id="sm_content">

	<form name="megaform" action="/package/save" method="POST">

		<input type="hidden" name="pack_id" value="<?=$pack_id?>">
		<input type="hidden" name="pack_client_id" value="<?=$client->id?>">
		<input type="hidden" name="pack_summa" id="pack_summa" value="<?=$pack->summa?>">


	<div class="clientHead">Просмотр заказа в работе</div>

	<div class="clientInfo">
		<div class="column">
			<p class="label">Имя:</p>
			<p><?=$client->fio?>&nbsp;</p>
			<p class="label">E-mail:</p>
			<p><?=$client->mail?>&nbsp;</p>
		</div>
		<div class="column">
			<p class="label">Телефон:</p>
			<p><?=$client->phone?>&nbsp;</p>
			<p class="label">Город:</p>
			<p><?=$client->state?>&nbsp;</p>
		</div>
		<div class="column wide">
			<p class="label">Примечание:</p>
			<p><?=$client->descr?>&nbsp;</p>
		</div>
	</div>
	<div class="domainInfo">
			<strong>Название:</strong> <?=$pack->name?><br>
			<strong>Описание:</strong> <?=$pack->descr?>
<div class="tabs">
	<span id="tab<?=$pack->redmine_proj?>" class="tab selected" onClick="selectTab(<?=$pack->redmine_proj?>)">Заказ</span>
<?php

$tabs = array();
$tabs[] = $pack->redmine_proj;

foreach ($zserv as $value) {
	print '<span id="tab'.$value->to_redmine.'" class="tab" onClick="selectTab('.$value->to_redmine.')">'.$value->service->name.'</span>';
	$tabs[] = $value->to_redmine;
}

?>
</div>
	</div>
	<div class="scroll-wrap">
			<div class="scroll-pane">


<?php

$hidden = '';
foreach ($tabs as $tab)
{
	print '<div id="tabContent'.$tab.'" class="tabContent '.$hidden.'">';

	$issue = Redmine::getIssue($tab);
	if ( $issue ){
		print $issue->subject.' ('.$issue->done_ratio.'%)<br>';
		print 'Иполнитель: '.$issue->assigned_to['name'].'<br>';
		print 'Описание: '.str_replace("\n", '<br>', $issue->description).'<br>';
		print '<hr>';
		foreach ($issue->journals->journal as $journal)
		{
			print $journal->user['name'].' ('.date('d-m-Y H:i', strtotime($journal->created_on)).')<br>';
			print $journal->notes;
			print '<hr>';
		}
	}else print 'Данные не получены! Вероятно задача не создана.';
	
	print '</div>';
	$hidden = ' hidden';
}


?>

			</div>
	</div>


</form>

<div class="buttons">
<!--		<a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
	<a href="javascript:alert('Пока не работает.');" class="buttonSaveExit">Сохранить и выйти</a> -->
	<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>

	<span id="summa"></span>
</div>
</div>
</div>