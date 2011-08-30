<?php
/*
 * Форма оплаченного заказа.
 * Тут мы читаем сообщения из редмайна.
 */

$zserv = array(); // Заказанные сервисы/услуги
$usersArray = Redmine::getUsersArray();


function GetMasters($sel = 0, $usersArray = null) {
	if ($usersArray === null)
		$usersArray = Redmine::getUsersArray();
	
	$res = "<select class='RedmineUserSelect'>";
	$res .= "<option value=\"0\">--нет--</option>";
	$peoples = PeopleGroup::getById(5)->peoples;
	foreach ($peoples as $people) {
		$selected = '';
		if ($people->id == $sel)
			$selected = ' selected';
		if ( !array_key_exists($people->login ,$usersArray) )
			$disabled = 'disabled';
		else
			$disabled = '';

		$res = $res.'<option value="'.@$usersArray[$people->login].'" '.$disabled.' '.$selected.'>'.$people->fio.'</option>';
	}

	$res = $res."</select>\n";
	return $res;
}

//  Возвращаем SELECT с выбором сайта. Ипользуются сайты, закреплённые за проектам клиентами
function sites($client_id, $sel) {
	$sites = Site::getAllByClient($client_id);
	$res = "<select name=\"pack_site_id\"><option value=\"0\">--нет--</option>";
	if (isset($sites))
		foreach ($sites as $site) {
			$res = $res."<option value='".$site->id."'";

			if ($sel == $site->id)
				$res = $res." selected";
			$res .= ">";

			$res = $res."$site->url</option>";
		}
	$res = $res."</select>";
	return $res;
}

/*
 * Возвращаем
 */
function GetManagers(){
	$res = "<select id='newManager' name='newManager'>\n<option value=\"0\">Не передавать</option>";
	$peoples = PeopleGroup::getById(4)->peoples;
	foreach ($peoples as $people) {
		//if ( $people->id != Yii::app()->user->id )
			$res = $res."<option value=\"$people->id\">$people->fio</option>\n";
	}
	$res = $res."</select>\n";
	return $res;
}

$RedmineUserSelect = '<select class="RedmineUserSelect">';
foreach ($usersArray as $key => $user) {
	$RedmineUserSelect .= '<option value="'.$user.'">'.$key.'</option>';
}
$RedmineUserSelect .= '</select>';


	//$pack = Package::model()->findByPk($package_id);
	$client = $pack->client;
	$zakaz_size =  sizeof( $pack->servPack );
	$client_id = $pack->client_id;

	foreach ($pack->servPack as $key => $zakaz)
	{
		$zserv[$zakaz->serv_id] = $zakaz;
	}

?>
<form name="megaform" action="/package/edit" method="POST">
<div class="wrapper">
<div class="editClientWindow" id="sm_content">

	

		<input type="hidden" name="pack_id" value="<?=$pack->id?>">
		<input type="hidden" name="pack_client_id" value="<?=$client->id?>">
		<input type="hidden" name="pack_summa" id="pack_summa" value="<?=$pack->summa?>">


	<div class="clientHead">Просмотр заказа в работе #<?=$pack->id?></div>

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
			<strong>Название:</strong> <?=$pack->name?>
			<div id="site_selector">
				<strong>Сайт:</strong> <?=$pack->site_id ? $pack->site->url : sites($client->id, $pack->site_id).'<a href="javascript:loadNewSite();" class="plus">+</a>';?><br>
			</div>
			<strong>Описание:</strong> <?=$pack->descr?>
<div class="tabs">
	<span id="tab<?=$pack->redmine_proj?>" class="tab selected" onClick="selectTab(<?=$pack->redmine_proj?>)">Заказ</span>
<?php

$tabs = array();
$tabs[] = array('to_redmine'=>$pack->redmine_proj, 'name'=>'Заказ', 'serv_id'=>$pack->redmine_proj);

foreach ($zserv as $value) {
	print '<span id="tab'.$value->serv_id.'" class="tab" onClick="selectTab('.$value->serv_id.')">'.$value->service->name.'</span>';
	$tabs[] = array('to_redmine'=>$value->to_redmine, 'name'=>$value->service->name, 'serv_id'=>$value->serv_id, 'master_id'=>$value->master_id);
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
						print '<div id="tabContent'.$tab['serv_id'].'" class="tabContent '.$hidden.'">';
						
						// если дата окончания услуги прошла
						if (! empty($client->attr['bm_id']->values[0]->value) and strtotime($usluga->dt_end) < strtotime('now')) {
							// XXX если заказан хостинг (исправить номер!):
							if ($zserv->service->parent_id == 67 and strtotime($zserv->dt_end) < strtotime('now')) {
								print '<a class="plus" title="Добавить заказ в BILLManager" id="linkid-'.$pack->primaryKey.'-'.$tab['serv_id'].'" onClick="bmVHost('.$pack->primaryKey.','.$tab['serv_id'].')" class="edit"></a>';
							}
							// XXX если заказана регистрация доменного имени (исправить номер!):
							if ($zserv->service->parent_id == 68 and strtotime($zserv->dt_end) < strtotime('now')) {
								print '<a class="plus" title="Добавить заказ в BILLManager" id="linkid-'.$pack->primaryKey.'-'.$tab['serv_id'].'" onClick="bmDomainName('.$pack->primaryKey.','.$tab['serv_id'].')" class="edit"></a>';
							}
						}
						
						if ( $tab['to_redmine'] ){
							$this->renderPartial('issue', array('issue_id'=>$tab['to_redmine'], 'pack_id'=>$pack->id, 'serv_id'=>$tab['serv_id']));
						} else {
							print '<br><br>Требуется распределить задачу на мастера и отдать её в работу.<br>';
							print 'Выберите мастера: ';
							print GetMasters($tab['master_id'], $usersArray);
							print '<a onClick="newRedmineIssue('.$pack->id.', '.$tab['serv_id'].'); $(this).attr(\'onClick\', \'\');" class="orangeButton">Отдать в работу</a><br>';

							/*
							 *	Старый механизм создания задач (без указания мастера - мастер берётся из настроек поступившего заказа).
							 *	Механизм привязки существующей задачи.
							 *
							 *
							print '<br><br>Данные не получены! Вероятно задача #'.$tab['to_redmine'].' - "'.$tab['name'].'" не создана. <a onClick="newRedmineIssue('.$pack->id.', '.$tab['serv_id'].')" class="orangeButton">Создать задачу</a><br><br><br>';
							print '<br><br><br><br>Для привязки существующих задач к заказу воспользуйтесь следующей формой:<br>';
							print 'Введите номер задачи с которой производим связку #<input type="number" id="input'.$tab['serv_id'].'" max="999999" min="1" size="9"> <a onClick="bindRedmineIssue('.$pack->id.', '.$tab['serv_id'].')" class="grayButton">Связать</a><br>';
							/**/
						}
						print '</div>';

						$hidden = ' hidden';
					}
				?>

			</div>
	</div>




<div class="buttons">
	<a onClick="packUpdate();" class="buttonSave">Сохранить</a>
	<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
	<span style="float:left;">Передать заказ менеджеру:</span>&nbsp;<?= getManagers() ?>
	<span id="summa"></span>
</div>
</div>
</div>
</form>
