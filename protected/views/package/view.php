<?php $client = $pack->client; ?>
<div class="wrapper">
	<form name="megaform" action="/package/edit" method="POST">
		<div class="editClientWindow" id="sm_content">
			<input type="hidden" name="pack_id" value="<?=$pack->primaryKey;?>;">
			<input type="hidden" name="client_id" value="<?=$client->primaryKey;?>">
			<div class="clientHead">
				Просмотр заказа в работе #<?= $pack->primaryKey; ?>
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
			<div class="domainInfo">
				<div>
					<strong>Название:</strong>
					<?= $pack->name?>
					<br/>
					<strong>Описание:</strong>
					<?= $pack->descr?>
				</div>
				<div id="site_selector">
					<strong>Сайт:</strong>
					<?php if ($pack->site_id): ?>
					<b><?= $pack->site->url; ?></b>
					<?php else : ?>
					<?php $this->renderPartial('/snippets/siteselect', array( 'client_id'=>$client->primaryKey )); ?>
					<a href="javascript:loadNewSite();" class="plus">+</a>
					<?php endif; ?>
				</div>
			</div>
			<div class="tabs">
				<span id="tab0" class="tab selected" onClick="selectTab(0)">Главная задача</span>
				<?php foreach ($pack->servPack as $serv): ?>
				<span id="tab<?=$serv->serv_id;?>" class="tab" onClick="selectTab(<?=$serv->serv_id;?>)"><?= $serv->service->name; ?></span>
				<?php endforeach; ?>
			</div>
			<div class="scroll-wrap">
				<div class="scroll-pane">
					<div id="tabContent0" class="tabContent">
						<?php 
						$this->renderPartial('issue', array(
							'issue_id'=>$pack->redmine_proj,'pack_id'=>$pack->primaryKey,'serv_id'=>0
						));
						?>
					</div>
					<?php foreach ($pack->servPack as $serv): ?>
					<div id="tabContent<?=$serv->serv_id;?>" class="tabContent hidden">
						<?php if ($serv->service->parent_id == 67 and ! empty($client->attr['bm_id']->values[0]->value) and strtotime($serv->dt_end) < strtotime('now')): ?>
						<h3><a class="plus" title="заказать хостинг" id="linkid-<?= $serv->pack_id; ?>-<?= $serv->serv_id; ?>" onClick="bmVHost(<?= $serv->pack_id; ?>, <?= $serv->serv_id; ?>)" class="edit"></a> заказать хостинг</h3>
						<?php endif; ?>
						<?php if ($serv->service->parent_id == 68 and ! empty($client->attr['bm_id']->values[0]->value) and strtotime($serv->dt_end) < strtotime('now')): ?>
						<h3><a class="plus" title="заказать домен" id="linkid-<?= $serv->pack_id; ?>-<?= $serv->serv_id; ?>" onClick="bmDomainName(<?= $serv->pack_id; ?>, <?= $serv->serv_id; ?>)" class="edit"></a> заказать домен</h3>
						<?php endif; ?>
						<?php 
						$this->renderPartial('issue', array(
							'issue_id'=>$serv->to_redmine,'pack_id'=>$pack->primaryKey,'serv_id'=>$serv->serv_id
						));
						?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="buttons">
			<a onClick="packUpdate(<?=$client->primaryKey;?>);" class="buttonSave">Сохранить</a>
			<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
			<span>Передать заказ:</span>
			<?php $this->renderPartial('/snippets/userselect', array( 'group_id'=>4,'index'=>'manager' )); ?>
		</div>
	</form>
</div>
