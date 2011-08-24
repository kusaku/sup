<?php 
// Форма заказа
function GetMasters($sel = 0) {
	$res = '';
	$peoples = PeopleGroup::getById(5)->peoples;
	foreach ($peoples as $people) {
		$selected = '';
		if ($people->id == $sel)
			$selected = ' selected';
		$res = $res."<option value=\"$people->id\" $selected>$people->fio</option>";
	}
	return $res;
}

$masters = GetMasters();

function ShowMasters($id, $masters, $sel = false) {
	$res = "<select name=\"master[$id]\"><option value=\"0\">--нет--</option>";
	
	if ($sel)
		$res = $res.GetMasters($sel);
	else
		$res = $res.$masters;
		
	$res = $res."</select>\n";
	
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

// Заказанные сервисы/услуги
$ordered = array();

// Нам передали ИД заказа для редактирования или это будет новый заказ?
if ($package_id) {
	$package = Package::getById($package_id);
	
	$client = $package->client;
	$order_size = sizeof($package->servPack);
	$client_id = $package->client_id;
	$package_id = $package->id;
	
	foreach ($package->servPack as $key=>$order) {
		$ordered[$order->serv_id] = $order;
	}
} else {
	$package = new Package();
	$client = People::getById($client_id);
	$package_id = 0;
}

?>
<div class="wrapper">
	<div class="editClientWindow" id="sm_content">
		<form name="megaform" action="/package/save" method="POST">
			<input type="hidden" name="pack_id" value="<?=$package_id?>"><input type="hidden" name="pack_client_id" value="<?=$client->id?>"><input type="hidden" name="pack_summa" id="pack_summa" value="<?=$package->summa?>">
			<div class="clientHead">Добавление/редактирование заказа</div>
			<?php if ($client->parent_id): ?>
			<div class="clientInfo">
				<strong>Основной клиент:</strong>
				<br>
				<div class="column">
					<p class="label">Имя:</p>
					<p><?= $client->parent->fio?>&nbsp;</p>
					<p class="label">E-mail:</p>
					<p><?= $client->parent->mail?>&nbsp;</p>
				</div>
				<div class="column">
					<p class="label">Телефон:</p>
					<p><?= $client->parent->phone?>&nbsp;</p>
					<p class="label">Город:</p>
					<p><?= $client->parent->state?>&nbsp;</p>
				</div>
				<div class="column wide">
					<p class="label">Примечание:</p>
					<p><?= $client->parent->descr?>&nbsp;</p>
				</div>
			</div>
			<?php else : ?>
			<div class="clientInfo">
				<div class="column">
					<p class="label">Имя:</p>
					<p><?= $client->fio?>&nbsp;</p>
					<p class="label">E-mail:</p>
					<p><?= $client->mail?>&nbsp;</p>
				</div>
				<div class="column">
					<p class="label">Телефон:</p>
					<p><?= $client->phone?>&nbsp;</p>
					<p class="label">Город:</p>
					<p><?= $client->state?>&nbsp;</p>
				</div>
				<div class="column wide">
					<p class="label">Примечание:</p>
					<p><?= $client->descr?>&nbsp;</p>
				</div>
			</div>
			<?php endif; ?>
			<div class="domainInfo">
				<table>
					<tr>
						<td style="vertical-align: top;">Название: <input type="text" name="pack_name" value="<?=$package->name?>">
							<br>
							Описание:
							<br>
							<textarea name="pack_descr" cols="30" rows="5"><?= $package->descr?></textarea>
						</td>
						<td style="vertical-align: top;">
							<div id="site_selector">
								<?=sites($client->id, $package->site_id)?>
								<a href="javascript:loadNewSite();" class="plus">+</a>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="scroll-wrap">
				<div class="scroll-pane">
					<?php foreach (Service::getAllByParent(0) as $group): ?>
					<div class="projectBlock">
						<div class="header">
							<a onClick="$('#projectPart<?= $group->id?>').removeClass('hidden').children().removeClass('hidden'); $(this).attr('onClick', '$(\'#projectPart<?= $group->id?>\').toggleClass(\'hidden\');')"><?= $group->name?>:</a>
						</div>
						<?php
							// Предварительная проверка, есть-ли в блоке заказанные услуги. Если есть, что блок будем выводить раскрытым.
							$active = false;
							foreach (Service::getAllByParent($group->id) as $service){
								$active = (bool)isset($ordered[$service->id]);
								if ($active) break;
							}
						?>

						<div class="projectPart <?=$active ? '' : 'hidden'?>" id="projectPart<?= $group->id?>">
							<?php foreach (Service::getAllByParent($group->id) as $service): ?>
							<div class="subPart  <?=isset($ordered[$service->id]) ? '' : 'hidden';?>">
								<?php 
								$active = isset($ordered[$service->id]) ? ' checked="checked"' : false; // Это заказанная услуга!! УРА, товарищи!
								$dataTime = date('Y-m-d H:i:s');
								?>
								<label class="column1"><?= $service->name?>:</label>
								<?php if ($group->exclusive): ?>
								<input class="cbox" type="radio"<?= $active?> name="service[<?= $group->id?>]" value="<?= $service->id?>"><?php else : ?>
								<input class="cbox" type="checkbox"<?= $active?> name="service[<?= $service->id?>]" value="<?= $service->id?>"><?php endif; ?>
								<?php if ($active): ?>
								<input class="column2" type="text" name="descr[<?= $service->id?>]" value="<?= $ordered[$service->id]->descr?>" size="30">
								<?php else : ?>
								<input class="column2" type="text" name="descr[<?= $service->id?>]" value="" size="30">
								<?php endif; ?>
								<?php if ($active): ?>
								<input class="column3" type="text" id="count<?= $service->id?>" style="width: 20px;" name="count[<?= $service->id?>]" onChange="javascript:sumka()" value="<?= $ordered[$service->id]->quant?>" size="3">
								<?php else : ?>
								<input class="column3" type="text" id="count<?= $service->id?>" style="width: 20px;" name="count[<?= $service->id?>]" onChange="javascript:sumka()" value="1" size="3">
								<?php endif; ?>
								<img src="/images/cross_gray.png"/><?php if ($active): ?>
								<input class="column3" type="text" id="price<?= $service->id?>" style="width: 50px;" name="price[<?= $service->id?>]" onChange="javascript:sumka()" title="<?= $service->price?>" value="<?= $ordered[$service->id]->price?>" size="10">
								<?php else : ?>
								<input class="column3" type="text" id="price<?= $service->id?>" style="width: 50px;" name="price[<?= $service->id?>]" onChange="javascript:sumka()" title="<?= $service->price?>" value="<?= $service->price?>" size="10">
								<?php endif; ?>
								руб.<label class="column4" for="mastername2">Мастер:</label>
								<?php if ($active): ?>
								<?= ShowMasters($service->id, $masters, $ordered[$service->id]->master_id)?>
								<?php else : ?>
								<?= ShowMasters($service->id, $masters)?>
								<?php endif; ?>
								<?php if ($active): ?>
								<input type="hidden" name="dt_beg[<?= $service->id?>]" value="<?= $ordered[$service->id]->dt_beg?>">
								<?php else : ?>
								<input type="hidden" name="dt_beg[<?= $service->id?>]" value="<?= $dataTime?>">
								<?php endif; ?>
								<?php if ($active): ?>
								<input type="hidden" name="dt_end[<?= $service->id?>]" value="<?= $ordered[$service->id]->dt_end?>">
								<?php else : ?>
								<input type="hidden" name="dt_end[<?= $service->id?>]" value="<?= $dataTime?>">
								<?php endif; ?>
								
							</div>
							<?php endforeach; ?>
							<div class="projectPartBottom"></div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
<?////////////////////////php endif; ?>
			<div class="buttons">
				<a onClick="packSave()" class="buttonSave">Сохранить</a>
				<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
				<span style="float:left;">Передать заказ менеджеру:</span>&nbsp;<?= getManagers() ?>
				<span id="summa"></span>
			</div>
		</form>
	</div>
</div>
<?php 
// Если это лишь контактное лицо, то чуть ниже подгрузим сайты клиента
$client->parent_id and $client_id = $client->parent_id;
?>
<script type="text/javascript">
	// Подсчёт суммы для открытого заказа
	sumka();
	// Загржаем список сайтов (доменов) этого клиента

	var client_id = '<?= $client_id ?>';
	var site_id = '<?= $package->site_id ?>';
	//loadSites(client_id, site_id);
</script>
