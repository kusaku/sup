<?php 
//Карточка клиента
$client = People::getById($client_id);
$summa = 0;
?>
<div class="wrapper">
	<div class="addClientWindow" id="sm_content">
		<div class="clientHead">
			Клиент: <span><?= $client->fio?></span>
		</div>
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
		<div class="orders">
			<div class="column">
				<a onClick="Package(0, <?=$client->id?>)" class="add_zakaz"></a>
				Добавить заказ
			</div>
			<div class="column">
				<a onClick="addEditClient(0, <?=$client->id?>)" class="add_contact"></a>
				Контактные лица
				<?php 
				/* Контактные лица клиента. Выводим в спывающем окошке.
				 */
				$contacts = $client->contacts;
				if (sizeof($contacts) > 0) {
					print '<div class="tips"><div class="tipsTop"></div><div class="tipsBody">';
					foreach ($contacts as $contact) {
						print '<a onClick="addEditClient('.$contact->id.')">'.$contact->fio.'</a><br>';
					}
					print '</div><div class="tipsBottom"></div></div>';
				}
				?>
			</div>
			<div class="column">
				<a onClick="editDomain(0, <?=$client->id?>)" class="add_site"></a>
				Добавить сайт
			</div>
			<?php if (! empty($client->attr['bm_id']->values[0]->value)): ?>
			<div class="column">				
				<span><a class="add_open" id="linkid-<?= $client->primaryKey; ?>" onclick="bmOpen(<?= $client->primaryKey; ?>)" href="#"></a> Открыть BILLManager</span>
			</div>
			<?php else : ?>
			<div class="column">				
				<span><a class="add_bm" id="linkid-<?= $client->primaryKey; ?>" onclick="bmRegister(<?= $client->primaryKey; ?>)" href="#"></a> Рег. в BILLManager</span>								
			</div>
			<?php endif; ?>
		</div>
		<div class="scroll-wrap">
			<div class="scroll-pane">
				<?php 
				$sites = $client->my_sites;
				foreach ($sites as $site) {
					print '<div class="orderBlock" id="orderBlock'.$site->id.'">';
					print '<div class="header">';
					print '<a onClick="CardShowHide('.$site->id.')" class="arrow"></a>';
					print '<a onClick="editDomain('.$site->id.')" class="edit">редактировать</a>';
					print '<a href="http://'.$site->url.'" target="_blank">'.$site->url.'</a> <span class="descript">"'.Site::getTypeById($site->id).'"</span>';
					print '</div>';
				
					
					$packages = $site->package;
					foreach ($packages as $package) {
						print '<div class="orderPart hidden">';
						print '<a onClick="Package('.$package->id.', 0)" class="active" style="text-decoration: underline;">Заказ #'.$package->id.'&nbsp;&nbsp;'.$package->name;
						if ($package->summa)
							print '&nbsp;&nbsp;&nbsp;&nbsp;<strong>'.$package->summa.'руб.</strong>';
						print '</a>';
						$uslugi = $package->servPack;
						foreach ($uslugi as $usluga) {
							print '<div class="subPart">';
							
							print '<div class="column1">';
							print '<p class="label">Описание:</p>';
							print '<p>'.$usluga->service->name;
							
							// если дата окончания услуги прошла
							if (! empty($client->attr['bm_id']->values[0]->value) and strtotime($usluga->dt_end) < strtotime('now')) {
								// XXX если заказан хостинг (исправить номер!):
								if ($usluga->service->parent_id == 67 and strtotime($usluga->dt_end) < strtotime('now')) {
									print '<a class="plus" title="Добавить заказ в BILLManager" id="linkid-'.$package->primaryKey.'-'.$usluga->service->primaryKey.'" onClick="bmVHost('.$site->id.','.$package->primaryKey.','.$usluga->service->primaryKey.')" class="edit"></a>';
								}
								// XXX если заказана регистрация доменного имени (исправить номер!):
								if ($usluga->service->parent_id == 68 and strtotime($usluga->dt_end) < strtotime('now')) {
									print '<a class="plus" title="Добавить заказ в BILLManager" id="linkid-'.$package->primaryKey.'-'.$usluga->service->primaryKey.'" onClick="bmDomainName('.$site->id.','.$package->primaryKey.','.$usluga->service->primaryKey.')" class="edit"></a>';
								}
							}
							
							print '</p>';
							
							print '</div>';
							
							print '<div class="column2">';
							print '<p class="label">Заказан:</p>';
							print '<p>'.$usluga->dt_beg.'</p>';
							print '</div>';
							
							print '<div class="column3">';
							print '<p class="label">Мастер:</p>';
							print '<p>'.People::getNameById($usluga->master_id).'</p>';
							print '</div>';
							
							print '</div>';
							$summa = $summa + $usluga->price * $usluga->quant;
							
						}
						print '</div>';
					}
					print '</div>';
				}
				
				/* Выводим заказы, не привязанные к сайтам
				 */
				print '<div class="orderBlock" id="orderBlockNoSite">';
				print '<div class="header">';
				print '<a onClick="CardShowHide(\'NoSite\')" class="arrow"></a>';
				print '<span class="descript">Заказы без привязки к сайту</span>';
				print '</div>';
				foreach ($client->packages as $package)
					if (!$package->site_id) {
						print '<div class="orderPart hidden">';
						print '<a onClick="Package('.$package->id.', 0)" class="active">Заказ #'.$package->id.'&nbsp;&nbsp;'.$package->name;
						if ($package->summa)
							print '&nbsp;&nbsp;&nbsp;&nbsp;<strong>'.$package->summa.'руб.</strong>';
						print '</a>';
						$uslugi = $package->servPack;
						foreach ($uslugi as $usluga) {
							print '<div class="subPart">';
							
							print '<div class="column1">';
							print '<p class="label">Описание:</p>';
							print '<p>'.$usluga->service->name.'</p>';
							print '</div>';
							
							print '<div class="column2">';
							print '<p class="label">Заказан:</p>';
							print '<p>'.$usluga->dt_beg.'</p>';
							print '</div>';
							
							print '<div class="column3">';
							print '<p class="label">Мастер:</p>';
							print '<p>'.People::getNameById($usluga->master_id).'</p>';
							print '</div>';
							
							print '</div>';
							$summa = $summa + $usluga->price * $usluga->quant;
							
						}
						print '</div>';
					}
				print '</div>';
				
				?>
			</div>
		</div>
		<div class="buttons">
			<!--	<a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
			<a href="javascript:alert('Пока не работает.');" class="buttonSaveExit">Сохранить и выйти</a> --><a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
			<span id="summa"><?= $summa?>руб.</span>
		</div>
	</div>
</div>
