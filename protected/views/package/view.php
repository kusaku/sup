<?php
/*
 * Форма заказа
 */

function GetMasters( $sel = 0 )
{
	$res = '';
	$peoples = PeopleGroup::getById( 5 )->peoples;
	foreach ( $peoples as $people )
	{
		$selected = '';
		if ( $people->id == $sel ) $selected = ' selected';
		$res = $res.'<option value="'.$people->id."\"$selected>".$people->fio.'</option>';
	}
	return $res;
}

$masters = GetMasters();

function ShowMasters($id, $masters, $sel = 0)
{
	$res = '<select name="master['.$id.']"><option value="0">..</option>';
//	$res = '<select name="master['.$id.']" id="longSelect'.$id.'"><option value="0">..</option>';

	if ( $sel ) $res = $res.GetMasters ($sel);
	else $res = $res.$masters;

	$res = $res."</select>\n";
//	$res = $res.'<script>cuSel({changedEl: "#longSelect'.$id.'"});</script>';

	return $res;
}

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

/*
 * Нам передали ИД заказа для редактирования или это будет новый заказ?
 */
if ( $package_id )
{
	$pack = Package::getById($package_id);

	$client = $pack->client;
	$zakaz_size =  sizeof( $pack->servPack );
	$client_id = $pack->client_id;
	$pack_id = $pack->id;

	foreach ($pack->servPack as $key => $zakaz)
	{
		$zserv[$zakaz->serv_id] = $zakaz;
	}
}
else
{
	$pack = new Package();
	$client = People::getById($client_id);
	$pack_id = 0;
}
?>

<div class="wrapper">
<div class="editClientWindow" id="sm_content">

	<form name="megaform" action="/package/save" method="POST">

		<input type="hidden" name="pack_id" value="<?=$pack_id?>">
		<input type="hidden" name="pack_client_id" value="<?=$client->id?>">
		<input type="hidden" name="pack_summa" id="pack_summa" value="<?=$pack->summa?>">


	<div class="clientHead">Добавление/редактирование заказа</div>
<?php
	if( $client->parent_id )
	{
//		$pack->descr = 'Контактное лицо: '.$client->fio."\n".'Телефон: '.$client->phone."\n".'EMail: '.$client->mail."\n".$_POST['pack_descr'];
?>
	<div class="clientInfo">
		<strong>Основной клиент:</strong><br>
		<div class="column">
			<p class="label">Имя:</p>
			<p><?=$client->parent->fio?>&nbsp;</p>
			<p class="label">E-mail:</p>
			<p><?=$client->parent->mail?>&nbsp;</p>
		</div>
		<div class="column">
			<p class="label">Телефон:</p>
			<p><?=$client->parent->phone?>&nbsp;</p>
			<p class="label">Город:</p>
			<p><?=$client->parent->state?>&nbsp;</p>
		</div>
		<div class="column wide">
			<p class="label">Примечание:</p>
			<p><?=$client->parent->descr?>&nbsp;</p>
		</div>
	</div>
<?php
	}
?>

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
		<table>
			<tr>
				<td style="vertical-align: top;">Название:	<input type="text" name="pack_name" value="<?=$pack->name?>"><br>
			Описание:<br><textarea name="pack_descr" cols="30" rows="5"><?=$pack->descr?></textarea></td>
				<td style="vertical-align: top;"><div id="site_selector"></div></td>
			</tr>
		</table>
	</div>
	<div class="scroll-wrap">
			<div class="scroll-pane">


<?php
$Pservices = Service::getAllByParent(0);
	foreach ($Pservices as $Pservice)
	{
		$services = Service::getAllByParent($Pservice->id);
		print '<div class="projectBlock">
						<div class="header">
							<a onClick="$(\'#projectPart'.$Pservice->id.'\').toggleClass(\'hidden\')">'.$Pservice->name.':</a><!-- <a href="#" class="showHidden">показать неактивные</a> -->
						</div>
						<div class="projectPart" id="projectPart'.$Pservice->id.'">';
			foreach ($services as $service)
			{
				print '<div class="subPart">';
				$b = isset ( $zserv[$service->id] ); // Это заказанная услуга!! УРА, товарищи!
				$dataTime = date('Y-m-d H:i:s');

				print '<label class="column1" for="check1">'.$service->name.':</label>';
				
				if ($Pservice->exclusive == 1) {
					if ($b)	print "<input class='cbox' type='radio' name='service[".$Pservice->id."]' value='".$service->id."' checked>";
					else	print "<input class='cbox' type='radio' name='service[".$Pservice->id."]' value='".$service->id."' >";
				} else {
					if ($b)	print "<input class='cbox' type='checkbox' name='service[".$service->id."]' value='".$service->id."' checked>";
					else	print "<input class='cbox' type='checkbox' name='service[".$service->id."]' value='".$service->id."' >";
				}
				

				if ($b)	print '<input class="column2" type="text" name="descr['.$service->id.']." value="'.$zserv[$service->id]->descr.'" size="30">';
				else	print '<input class="column2" type="text" name="descr['.$service->id.']." value="" size="30">';

				if ($b)	print '&nbsp;&nbsp;<input class="column3" type="text" id="count'.$service->id.'" style="width: 20px;" name="count['.$service->id.']." onChange="javascript:sumka()" value="'.$zserv[$service->id]->quant.'" size="3">';
				else	print '&nbsp;&nbsp;<input class="column3" type="text" id="count'.$service->id.'" style="width: 20px;" name="count['.$service->id.']." onChange="javascript:sumka()" value="1" size="3">';

				print '&nbsp;<img src="/images/cross_gray.png"/>&nbsp;';

				if ($b)	print '<input class="column3" type="text" id="price'.$service->id.'" style="width: 50px;" name="price['.$service->id.']." onChange="javascript:sumka()" title="'.$service->price.'" value="'.$zserv[$service->id]->price.'" size="10"> руб.';
				else	print '<input class="column3" type="text" id="price'.$service->id.'" style="width: 50px;" name="price['.$service->id.']." onChange="javascript:sumka()" title="'.$service->price.'" value="'.$service->price.'" size="10"> руб.';

				print '<label class="column4" for="mastername2">Мастер:&nbsp;</label>';

				if ($b)	print ShowMasters ($service->id, $masters, $zserv[$service->id]->master_id);
				else	print ShowMasters ($service->id, $masters); // Когда будем выводить заказ, нужно будет вызвать GetMasters и передать ему ID мастера

				if ($b)	print '<input type="hidden" name="dt_beg['.$service->id.']." value="'.$zserv[$service->id]->dt_beg.'">';
				else	print '<input type="hidden" name="dt_beg['.$service->id.']." value="'.$dataTime.'">';

				print '</div>';
			}
		print '<div class="projectPartBottom"></div>
						</div>
					</div>';

	}

?>


			</div>
	</div>


</form>

<div class="buttons">
	<a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
	<a href="javascript:alert('Пока не работает.');" class="buttonSaveExit">Сохранить и выйти</a>
	<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>

	<span id="summa"></span>
</div>
</div>
</div>
<?php
	if( $client->parent_id ) $client_id = $client->parent_id; // Если это лишь контактное лицо, то чуть ниже подгрузим сайты клиента
?>
<script>
	sumka(); // Подсчёт суммы для открытого заказа
	loadSites(<?=$client_id?>, <?=$pack->site_id?>); // Загржаем список сайтов (доменов) этого клиента
</script>