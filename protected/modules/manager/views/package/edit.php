<?php
/**
 * @var Package $pack
 */
$client = $pack->client;

$orderedServs = $pack->servPack;

// массив с id групп, в которых есть заказанные услуги.
$activeGroups = array();
foreach ($orderedServs as $serv) {
	$activeGroups[$serv->service->parent_id] = 1;
}

?>
<div class="wrapper">
	<form name="megaform" action="/manager/package/save" method="POST">
		<div class="editClientWindow" id="sm_content">
			<input type="hidden" name="pack_id" value="<?=$pack->primaryKey; ?>">
			<input type="hidden" name="client_id" value="<?=$client->primaryKey; ?>">
			<div class="clientHead">
				Просмотр заказа в работе #<?= $pack->primaryKey; ?>
			</div>
			<div class="clientInfo">
				<div class="column">
					<p class="label">Имя:</p>
					<p>
						<?= $client->fio; ?>&nbsp;
					</p>
					<p class="label">E-mail:</p>
					<p>
						<?= $client->mail; ?>&nbsp;
					</p>
				</div>
				<div class="column">
					<p class="label">Телефон:</p>
					<p>
						<?= $client->phone; ?>&nbsp;
					</p>
					<p class="label">Город:</p>
					<p>
						<?= $client->state; ?>&nbsp;
					</p>
				</div>
				<div class="column">
					<p class="label">Примечание:</p>
					<p>
						<?= $client->descr; ?>&nbsp;
					</p>
					<p class="label">Сумма:</p>
					<p id="package_summ">
						<?= $pack->summ ? number_format($pack->summ, 2, ',', ' ').' руб.' : 'нет'; ?>
						<?= $pack->period ? number_format($pack->period, 1, ',', ' ').' дн.' : ''; ?>
					</p>
				</div>
				<div class="column" style="width:200px;">
					<p class="label">Анкета:</p>
					<p style="width:100px;">
						<?php if($pack->questionnaire): ?>
							<a href="#packageQuestionnaire_<?php echo $pack->id?>" title="Просмотреть анкету">посмотреть</a>
						<?php else: ?>
						нет
						<?php endif; ?>
					</p>
					<p class="label">Оплачено:</p>
					<p style="width:100px;">
						<?= $pack->paid ? number_format($pack->paid, 2, ',', ' ').' руб.' : 'нет'; ?>
					</p>
				</div>
			</div>
			<div class="domainInfo">
				<table style="width:100%">
					<tr>
						<td style="vertical-align:top;">
							<b>Название:</b>
							<?php echo CHtml::textField('pack_name',$pack->name);?>
							<br/>
							<b>Описание:</b>
						<br/>
						<textarea name="pack_descr" cols="30" rows="5"><?= $pack->descr; ?></textarea>
						</td>
						<td style="vertical-align:top;">
							<div id="site_selector">
								<strong>Сайт:</strong>
								<?php $this->renderPartial('/snippets/siteselect', array( 'client_id'=>$client->primaryKey,'selected'=>$pack->site_id )); ?>
								<a href="javascript:loadNewSite();" class="plus">+</a>
								<br/>
								<strong title="Юридическое лицо от имени которого выполнять обработку заказа" style="cursor:help;">Юр. лицо:</strong>
								<select name="jur_person_id">
									<option value="0">--Не указывать--</option>
									<?php foreach($jur_reference as $obPerson): ?>
										<option value="<?= $obPerson->id; ?>"<?= ($obPerson->id == $pack->jur_person_id) ? ' selected="selected"' : ''; ?>><?= $obPerson->title; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</td>
						<td style="vertical-align:top;">
							<strong>Промокод:</strong>
							<input style="width:40px;" type="text" name="pack_promocode" value="<?= isset($pack->promocode) ? $pack->promocode->code : ''; ?>"/>
						</td>
						<td style="vertical-align:top;">
							<strong>Заказ создан:</strong>
							<input type="text" id="pack_dt_beg" name="pack_dt_beg" value="<?=date('Y-m-d H:i:s', strtotime($pack->dt_beg)); ?>"><!--<strong>Заказ сдан:</strong> <input type="text" id="pack_dt_end" name="pack_dt_end" value="<?=date('Y-m-d', strtotime($pack->dt_end)); ?>">-->
						</td>
					</tr>
				</table>
			</div>
			<div class="scroll-wrap">
				<div class="scroll-pane tabscontainer modal">
					<?php $parents = Service::model()->findAllByAttributes(array( 'parent_id'=>0 )); ?>
					<ul>
						<?php foreach ($parents as $group): ?>
						<li>
							<a href="#tabs-serv-<?= $group->primaryKey; ?>"><?= $group->name; ?> <span class="marked"><?= in_array($group->primaryKey, array_keys($activeGroups)) ? '*' : ''; ?></span></a>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php foreach ($parents as $group): ?>
					<div class="projectBlock" id="tabs-serv-<?= $group->primaryKey; ?>">
						<div class="projectPart">
							
							<?php if ($group->exclusive): ?>
							<div class="subPart">
								<label class="column1">
									Не выбрано:
								</label>
								<input<?= !in_array($group->primaryKey, array_keys($activeGroups)) ? ' checked="checked"' : ''; ?> style="margin:0 5px;" type="radio" name="service[<?= $group->primaryKey; ?>]" title="заказ" value=""/>
							</div>
							<?php endif; ?>
							
							<?php foreach ($group->childs as $serv):
								$activeService = in_array($serv->primaryKey, array_keys($orderedServs));
								if($serv->disabled && ($pack->isNewRecord || !$activeService)) continue;?>
							<div class="subPart">
								<label class="column1"><?= $serv->name; ?>:</label>
								<?php if ($group->exclusive){
									echo CHtml::radioButton('service['.$group->id.']',$activeService,array('class'=>'cbox','value'=>$serv->id,'title'=>'Заказ'));
								} else {
									echo CHtml::checkBox('service['.$serv->id.']',$activeService,array('class'=>'cbox','value'=>$serv->id,'title'=>'Заказ'));
								}?>
								
								<?php if ($activeService): ?>
								<input class="column2" type="text" name="descr[<?= $serv->primaryKey; ?>]" value="<?= $orderedServs[$serv->primaryKey]->descr; ?>" size="30">
								<?php else : ?>
								<input class="column2" type="text" name="descr[<?= $serv->primaryKey; ?>]" value="" size="30">
								<?php endif; ?>
								
								<img src="/images/cross_gray.png" />
								
								<?php if ($activeService): ?>
								<input class="column3" type="text" id="count<?= $serv->primaryKey; ?>" style="width: 20px;" name="count[<?= $serv->primaryKey; ?>]" title="количество" value="<?= $orderedServs[$serv->primaryKey]->quant; ?>" size="3">
								<?php else : ?>
								<input class="column3" type="text" id="count<?= $serv->primaryKey; ?>" style="width: 20px;" name="count[<?= $serv->primaryKey; ?>]" value="<?= $serv->quant; ?>" size="3">
								<?php endif; ?>шт.
								
								<img src="/images/cross_gray.png" />
								
								<?php if ($activeService): ?>
								<input class="column3" type="text" id="price<?= $serv->primaryKey; ?>" style="width: 50px;" name="price[<?= $serv->primaryKey; ?>]" title="стоимость" value="<?= $orderedServs[$serv->primaryKey]->price; ?>" size="10">
								<?php else : ?>
								<input class="column3" type="text" id="price<?= $serv->primaryKey; ?>" style="width: 50px;" name="price[<?= $serv->primaryKey; ?>]" title="стоимость" value="<?= $serv->price; ?>" size="10">
								<?php endif; ?>руб.
								
								<img src="/images/cross_gray.png" />
								
								<?php if ($activeService): ?>
								<input class="column3" type="text" id="duration<?= $serv->primaryKey; ?>" style="width: 20px;" name="duration[<?= $serv->primaryKey; ?>]" title="длительность" value="<?= $orderedServs[$serv->primaryKey]->duration; ?>" size="3">
								<?php else : ?>
								<input class="column3" type="text" id="duration<?= $serv->primaryKey; ?>" style="width: 20px;" name="duration[<?= $serv->primaryKey; ?>]" title="длительность" value="<?= $serv->duration; ?>" size="3">
								<?php endif; ?>дн.
								
								<?php if ($activeService): ?>
								<?= $this->renderPartial('/snippets/userselect', array( 'group_id'=>5,'index'=>$serv->primaryKey,'selected'=>$orderedServs[$serv->primaryKey]->master_id )); ?>
								<?php else : ?>
								<?= $this->renderPartial('/snippets/userselect', array( 'group_id'=>5,'index'=>$serv->primaryKey )); ?>
								<?php endif; ?>
								
								<?php 
								/*
								<?php if ($active): ?>	 
								<input type="hidden" name="dt_beg[<?= $serv->primaryKey; ?>]" value="<?= $orderedServs[$serv->primaryKey]->dt_beg; ?>">
								<?php else : ?>
								<input type="hidden" name="dt_beg[<?= $serv->primaryKey; ?>]" value="<?= date('Y-m-d H:i:s'); ?>">
								<?php endif; ?>
								
								<?php if ($active): ?>	 
								<input type="hidden" name="dt_end[<?= $serv->primaryKey; ?>]" value="<?= $orderedServs[$serv->primaryKey]->dt_end; ?>">
								<?php else : ?>
								<input type="hidden" name="dt_end[<?= $serv->primaryKey; ?>]" value="<?= date('Y-m-d H:i:s'); ?>">
								<?php endif; ?>
								*/ 
								 ?>
								
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="buttons">
				<a onClick="packSave(<?=$client->primaryKey; ?>)" class="buttonSave">Сохранить</a>
				<a onClick="hidePopUp()" class="buttonCancel">Отмена</a>
				<?php $this->widget('manager.widgets.PackageStatusWidget',array('package'=>$pack,'client'=>$client)); ?>
				<?php $this->widget('manager.widgets.PackagePaymentWidget',array('package'=>$pack,'client'=>$client)); ?>
				<span>Передать заказ:</span>
				<?php $this->widget('manager.widgets.UserselectWidget',array('group_id'=>4,'index'=>'manager')); ?>
				<span>Это заказ основного клиента</span>
				<input type="checkbox" name="set_to_parent"/><span id="summ"></span>
			</div>
		</div>
	</form>
</div>