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
$tabs[]['to_redmine'] = $pack->redmine_proj;

foreach ($zserv as $value) {
	print '<span id="tab'.$value->to_redmine.'" class="tab" onClick="selectTab('.$value->to_redmine.')">'.$value->service->name.'</span>';
	$tabs[] = array('to_redmine'=>$value->to_redmine, 'name'=>$value->service->name);
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
						$issue = Redmine::getIssue($tab['to_redmine']);
						print '<div id="tabContent'.$tab['to_redmine'].'" class="tabContent '.$hidden.'">';

						if ( $issue ){
							print $issue->subject.' ('.$issue->done_ratio.'%)';
							print '<div class="progressBar"><div class="progressStat" style="width:'.$issue->done_ratio.'%">'.$issue->done_ratio.'%</div></div>';
							print 'Иполнитель: '.$issue->assigned_to['name'].'<br>';
							print 'Описание: '.str_replace("\n", '<br>', $issue->description).'<br>';
							print '<hr>';

							foreach ($issue->journals->journal as $journal)
							{
								print $journal->user['name'].' ('.date('d-m-Y H:i', strtotime($journal->created_on)).')<br>';
								print nl2br(htmlspecialchars($journal->notes));
								print '<hr>';
							}

							print '<textarea class="redmineMessage" id ="redmineMessageInput'.$tab['to_redmine'].'"></textarea> <br><a onClick="redmineSendMessage('.$tab['to_redmine'].');" class="orangeButton" style="clear: both; float: right;">Опубликовать</a>';
						} else print 'Данные не получены! Вероятно задача #'.$tab['to_redmine'].' не создана.<br><a onClick="createRedmineIssue('.$tabs[0]['to_redmine'].', \'#'.$pack_id.' '.$tab['name'].'\');" class="grayButton" style="clear: both; float: right;">Создать задачу</a>';

						print '</div>';

						$hidden = ' hidden';
					}
				?>

			</div>
	</div>


</form>

<div class="buttons">
	<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
	<span id="summa"></span>
</div>
</div>
</div>