<?php $client = $pack->client; ?>
<div class="wrapper">
	<form name="megaform" action="/manager/package/edit" method="POST">
		<div class="editClientWindow" id="sm_content">
			<input type="hidden" name="pack_id" value="<?=$pack->primaryKey;?>;"><input type="hidden" name="client_id" value="<?=$client->primaryKey;?>">
			<div class="clientHead">
				Просмотр заказа в работе #<?= $pack->primaryKey; ?>
			</div>
			<div class="clientInfo">
				<div class="column">
					<p class="label">Имя:</p>
					<p>
						<a href="#people_<?=$client->id?>"><?= $client->fio?>&nbsp;</a>
					</p>
					<p class="label">E-mail:</p>
					<p>
						<?= $client->mail?>&nbsp;
					</p>
				</div>
				<div class="column">
					<p class="label">Телефон:</p>
					<p>
						<?= $client->phone?>&nbsp;
					</p>
					<p class="label">Город:</p>
					<p>
						<?= $client->state?>&nbsp;
					</p>
				</div>
				<div class="column">
					<p class="label">Примечание:</p>
					<p>
						<?= $client->descr?>&nbsp;
					</p>
					<p class="label">Сумма:</p>
					<p>
						<?= $pack->summ ? number_format($pack->summ, 2, ',', ' ').' руб.' : 'нет'; ?>
						<?= $pack->period ? number_format($pack->period, 1, ',', ' ').' дн.' : ''; ?>
					</p>
				</div>
				<div class="column" style="width:200px;">
					<p class="label">Анкета:</p>
					<p style="width:100px;">
						<?php if ($pack->questionnaire): ?>
							<a href="#packageQuestionnaire_<?php echo $pack->id?>" title="Просмотреть анкету">посмотреть</a>
						<?php else : ?>
						нет
						<?php endif?>
					</p>
					<p class="label">Оплачено:</p>
					<p style="width:100px;">
						<a href="#payments_<?php $pack->id;?>" style="color:#999999"><?= $pack->paid ? number_format($pack->paid, 2, ',', ' ').' руб.' : 'нет'; ?></a>
					</p>
				</div>
			</div>
			<div class="domainInfo">
				<div style="float:left;width:280px;margin-right:15px;">
					<strong>Название:</strong>
					<?= $pack->name?>
					<br/>
					<strong>Описание:</strong>
					<?= $pack->descr?>
				</div>
				<div id="site_selector" style="float:left;margin-right:15px;">
					<strong>Сайт:</strong>
					<?php if ($pack->site_id): ?>
					<b><?= $pack->site->url; ?></b>
					<?php else : ?>
					<?php $this->renderPartial('/snippets/siteselect', array( 'client_id'=>$client->primaryKey )); ?>
					<a href="javascript:loadNewSite();" class="plus">+</a>
					<?php endif; ?>
					<br/>
					<strong title="Юридическое лицо от имени которого выполнять обработку заказа" style="cursor:help;">Юр. лицо:</strong>
					<select name="jur_person_id">
						<option value="0">--Не указывать--</option>
						<?php foreach ($jur_reference as $obPerson): ?>
						<option value="<?php echo $obPerson->id?>"<?php if ($obPerson->id == $pack->jur_person_id) echo ' selected="selected"'; ?>><?php echo $obPerson->title?></option>
						<?php endforeach?>
					</select>
				</div>
				<div style="float:left;margin-right:15px;">
					<strong>Заказ создан:</strong>
					<?= date('Y-m-d H:i:s', strtotime($pack->dt_beg)); ?>
				</div>
				<div style="float:left;">
					<strong>Промокод:</strong>
					<input style="width:40px;" type="text" name="pack_promocode" value="<?= isset($pack->promocode) ? $pack->promocode->code : '';?>"/>
				</div>
				<div style="clear:both;"></div>
			</div>
			<div class="scroll-wrap">
				<div class="scroll-pane tabscontainer modal">
					<ul>
						<li>
							<a href="#tabs-redmine-0">Главная задача<?= ($pack->rm_issue_id) ? ' *' : ''; ?></a>
						</li>
						<?php foreach ($pack->servPack as $serv): ?>
						<li>
							<a href="#tabs-redmine-<?= $serv->service->primaryKey; ?>"><?= $serv->service->name; ?> <span class="marked"><?= ($serv->rm_issue_id) ? ' *' : ''; ?></span></a>
						</li>
						<?php endforeach; ?>
					</ul>
					<div id="tabs-redmine-0">
						<?php 
						$this->renderPartial('issue', array(
							'issue_id'=>$pack->rm_issue_id,'pack_id'=>$pack->primaryKey,'serv_id'=>0
						));
						?>
					</div>
					<?php foreach ($pack->servPack as $serv): ?>
					<div id="tabs-redmine-<?= $serv->service->primaryKey; ?>">
						<?php if ($serv->service->parent_id == 67 and ! empty($client->attr['bm_id']->values[0]->value) and strtotime($serv->dt_end) < strtotime('now')): ?>
						<h3><a class="plus" title="заказать хостинг" id="linkid-<?= $serv->pack_id; ?>-<?= $serv->serv_id; ?>" onClick="bmVHost(<?= $serv->pack_id; ?>, <?= $serv->serv_id; ?>)" class="edit"></a>заказать хостинг</h3>
						<?php endif; ?>
						<?php if ($serv->service->parent_id == 68 and ! empty($client->attr['bm_id']->values[0]->value) and strtotime($serv->dt_end) < strtotime('now')): ?>
						<h3><a class="plus" title="заказать домен" id="linkid-<?= $serv->pack_id; ?>-<?= $serv->serv_id; ?>" onClick="bmDomainName(<?= $serv->pack_id; ?>, <?= $serv->serv_id; ?>)" class="edit"></a>заказать домен</h3>
						<?php endif; ?>
						<?php 
						$this->renderPartial('issue', array(
							'issue_id'=>$serv->rm_issue_id,'pack_id'=>$pack->primaryKey,'serv_id'=>$serv->serv_id
						));
						?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="buttons">
				<a onClick="packUpdate(<?=$client->primaryKey;?>);" class="buttonSave">Сохранить</a>
				<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
				<?php $this->widget('manager.widgets.PackageStatusWidget', array( 'package'=>$pack,'client'=>$client )); ?>
				<?php /*$this->renderPartial('/snippets/statusesselect', array( 'selected'=>$pack->status_id )); */ ?>
				<?php $this->widget('manager.widgets.PackagePaymentWidget', array( 'package'=>$pack,'client'=>$client )); ?>
				<?php /*$this->renderPartial('/snippets/paymentsselect', array( 'selected'=>$pack->payment_id )); */ ?>
				<span>Передать заказ:</span>
				<?php /*$this->renderPartial('/snippets/userselect', array( 'group_id'=>4,'index'=>'manager' )); */ ?>
				<?php $this->widget('manager.widgets.UserselectWidget', array( 'group_id'=>4,'index'=>'manager' )); ?>
			</div>
		</div>
	</form>
</div>
