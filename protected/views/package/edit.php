<?php $client = $pack->client or $client = People::model()->findByPk($client_id); ?>
<?php $orderedServs = $pack->servPack;?>
<div class="wrapper">
	<form name="megaform" action="/package/save" method="POST">
		<div class="editClientWindow" id="sm_content">
			<input type="hidden" name="pack_id" value="<?=$pack->primaryKey;;?>;">
			<input type="hidden" name="client_id" value="<?=$client->primaryKey;;?>">
			<input type="hidden" name="pack_summa" id="pack_summa" value="<?=$pack->summa;;?>">
			<div class="clientHead">
				Просмотр заказа в работе #<?= $pack->primaryKey;?>
			</div>
			<div class="clientInfo">
				<div class="column">
					<p class="label">Имя:</p>
					<p><?= $client->fio;?>&nbsp;</p>
					<p class="label">E-mail:</p>
					<p><?= $client->mail;?>&nbsp;</p>
				</div>
				<div class="column">
					<p class="label">Телефон:</p>
					<p><?= $client->phone;?>&nbsp;</p>
					<p class="label">Город:</p>
					<p><?= $client->state;?>&nbsp;</p>
				</div>
				<div class="column wide">
					<p class="label">Примечание:</p>
					<p><?= $client->descr?>&nbsp;</p>
				</div>
			</div>
			<div class="domainInfo">
				<table>
					<tr>
						<td style="vertical-align: top;">
							<b>Название:</b> <input type="text" name="pack_name" value="<?=$pack->name;?>">
							<br/>
							<b>Описание:</b>
							<br/>
							<textarea name="pack_descr" cols="30" rows="5"><?= $pack->descr;?></textarea>
						</td>
						<td style="vertical-align: top;">
							<div id="site_selector">
								<strong>Сайт:</strong>
								<?php $this->renderPartial('/snippets/siteselect', array('client_id'=>$client->primaryKey, 'selected'=>$pack->site_id ));?>
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
							<a onClick="$('#projectPart<?= $group->primaryKey;?>').removeClass('hidden').children().removeClass('hidden'); $(this).attr('onClick', '$(\'#projectPart<?= $group->primaryKey;?>\').toggleClass(\'hidden\');')"><?= $group->name;?>:</a>
						</div>
						<div class="projectPart <?= in_array($group->primaryKey, array_keys($orderedServs)) ? '' : 'hidden';?>" id="projectPart<?= $group->primaryKey;?>">
							<?php foreach (Service::getAllByParent($group->primaryKey) as $serv): ?>
							<div class="subPart  <?= ($active = in_array($serv->primaryKey, array_keys($orderedServs))) ? '' : 'hidden';;?>">
								<label class="column1"><?= $serv->name;?>:</label>
								
								<?php if ($group->exclusive): ?>
									<input <?= $active ? 'checked="checked"' : '';?> class="cbox" type="radio" name="service[<?= $group->primaryKey;?>]" value="<?= $serv->primaryKey;?>">
								<?php else : ?>
									<input <?= $active ? 'checked="checked"' : '';?> class="cbox" type="checkbox" name="service[<?= $serv->primaryKey;?>]" value="<?= $serv->primaryKey;?>">
								<?php endif;?>
								
								<?php if ($active): ?>
									<input class="column2" type="text" name="descr[<?= $serv->primaryKey;?>]" value="<?= $orderedServs[$serv->primaryKey]->descr;?>" size="30">
								<?php else : ?>
									<input class="column2" type="text" name="descr[<?= $serv->primaryKey;?>]" value="" size="30">
								<?php endif;?>
								
								<?php if ($active): ?>
									<input class="column3" type="text" id="count<?= $serv->primaryKey;?>" style="width: 20px;" name="count[<?= $serv->primaryKey;?>]" onChange="javascript:sumka()" value="<?= $orderedServs[$serv->primaryKey]->quant;?>" size="3">
								<?php else : ?>
									<input class="column3" type="text" id="count<?= $serv->primaryKey;?>" style="width: 20px;" name="count[<?= $serv->primaryKey;?>]" onChange="javascript:sumka()" value="1" size="3">
								<?php endif;?>
								
								<img src="/images/cross_gray.png" />
								
								<?php if ($active): ?>								
									<input class="column3" type="text" id="price<?= $serv->primaryKey;?>" style="width: 50px;" name="price[<?= $serv->primaryKey;?>]" onChange="javascript:sumka()" title="<?= $serv->price;?>" value="<?= $orderedServs[$serv->primaryKey]->price;?>" size="10">
								<?php else : ?>
									<input class="column3" type="text" id="price<?= $serv->primaryKey;?>" style="width: 50px;" name="price[<?= $serv->primaryKey;?>]" onChange="javascript:sumka()" title="<?= $serv->price;?>" value="<?= $serv->price;?>" size="10">
								<?php endif;?>руб.
								
								
								<label class="column4" for="mastername2">Мастер:</label>
								
								<?php if ($active): ?>
									<?= $this->renderPartial('/snippets/userselect', array( 'group_id'=>5, 'selected'=>$orderedServs[$serv->primaryKey]->master_id, 'index'=>$serv->primaryKey )) ;?>
								<?php else : ?>
									<?= $this->renderPartial('/snippets/userselect', array( 'group_id'=>5, 'index'=>$serv->primaryKey )) ;?>
								<?php endif;?>
								
								<?php if ($active): ?>
									<input type="hidden" name="dt_beg[<?= $serv->primaryKey;?>]" value="<?= $orderedServs[$serv->primaryKey]->dt_beg;?>">
								<?php else : ?>
									<input type="hidden" name="dt_beg[<?= $serv->primaryKey;?>]" value="<?= date('Y-m-d H:i:s');;?>">
								<?php endif;?>
								
								<?php if ($active): ?>
									<input type="hidden" name="dt_end[<?= $serv->primaryKey;?>]" value="<?= $orderedServs[$serv->primaryKey]->dt_end;?>">
								<?php else : ?>
									<input type="hidden" name="dt_end[<?= $serv->primaryKey;?>]" value="<?=  date('Y-m-d H:i:s');;?>">
								<?php endif;?>
							</div>
							<?php endforeach;?>
							<div class="projectPartBottom"></div>
						</div>
					</div>
					<?php endforeach;?>
				</div>
			</div>
		<div class="buttons">
			<a onClick="packSave(<?=$client->primaryKey;?>)" class="buttonSave">Сохранить</a>
			<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
			<span>Передать заказ:</span>
			<?php $this->renderPartial('/snippets/userselect', array( 'group_id'=>4, 'index'=>'manager' ));?>
			<input type="checkbox" name="set_to_parent"> <span>Это заказ основного клиента</span> 
			<span id="summa"></span>
		</div>
	</form>
</div>