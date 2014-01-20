<?php
/**
 * @var Package $package
 * @var CabinetController $this
 * @var PackageWorkflowStep $step
 * @var DomainRequest[] $requests
 */
?>
<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-status">Заявка на домен</a>
		</li>
		<li>
			<a href="#tabs-domain-requests">Список заявок</a>
		</li>
	</ul>
	<div id="tabs-status" class="formBody">
		<?php
		$obWorkflow=$package->initWorkflow();
		$data=$obWorkflow->getData($step->primaryKey);
		if(is_array($data)):?>
			<p style="padding:5px 10px;">Пользователь указал следующие данные:</p>
			<div class="scrollPanel" style="height:275px;width:950px;padding:0 0 0 10px;">
			<div class="formRows">
				<?php if($data['mode']==1):?>
					<div class="formRow"><label>Вид заявки:</label><b>Свой домен</b></div><div class="formRow"></div>
					<div class="formRow"><label>Доменное имя:</label><b><?php echo htmlspecialchars($data['my_domain_name'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Доменная зона:</label><b><?php echo htmlspecialchars($data['my_domain_zone'],ENT_COMPAT,'utf-8')?></b></div>
				<?php elseif($data['mode']==2):?>
					<div class="formRow"><label>Вид заявки:</label><b>Заявка на новый домен</b></div><div class="formRow"></div>
					<div class="formRow"><label>Доменное имя:</label><b><?php echo htmlspecialchars($data['new_domain_name'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Доменная зона:</label><b><?php echo htmlspecialchars($data['new_domain_zone'],ENT_COMPAT,'utf-8')?></b></div>
					<?php if($data['domainOwnerType']==1):?>
						<div class="formRow"><label>Вид владельца:</label><b>физическое лицо</b></div>
						<div class="formRow"><label>ФИО (русское):</label><b><?php echo htmlspecialchars($data['new_domain_phis_fio_rus'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>ФИО (латинское):</label><b><?php echo htmlspecialchars($data['new_domain_phis_fio_lat'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>День рождения:</label><b><?php echo htmlspecialchars($data['new_domain_phis_birthday'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>Паспорт:</label><b><?php echo htmlspecialchars($data['new_domain_phis_passport'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>Выдан (организация):</label><b><?php echo htmlspecialchars($data['new_domain_phis_passport_issued_by'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>Выдан (дата):</label><b><?php echo htmlspecialchars($data['new_domain_phis_passport_issued_on'],ENT_COMPAT,'utf-8')?></b></div>
					<?php else:?>
						<div class="formRow"><label>Вид владельца:</label><b>юридическое лицо</b></div>
						<div class="formRow"><label>Название юр. лица (рус):</label><b><?php echo htmlspecialchars($data['new_domain_jur_title_rus'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>Название юр. лица (лат):</label><b><?php echo htmlspecialchars($data['new_domain_jur_title_eng'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>ИНН:</label><b><?php echo htmlspecialchars($data['new_domain_jur_inn'],ENT_COMPAT,'utf-8')?></b></div>
						<div class="formRow"><label>КПП:</label><b><?php echo htmlspecialchars($data['new_domain_jur_kpp'],ENT_COMPAT,'utf-8')?></b></div>
					<?php endif?>
					<?php if(isset($data['new_domain_phone'])):?>
					<div class="formRow fullRow">Контактные данные владельца домена:</div>
					<div class="formRow"><label>Телефон:</label><b><?php echo htmlspecialchars($data['new_domain_phone'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Мобильный телефон:</label><b><?php echo htmlspecialchars($data['new_domain_mobile_phone'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>E-mail:</label><b><?php echo htmlspecialchars($data['new_domain_email'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Страна:</label><b><?php echo People::getCountryById($data['new_domain_la_country'])?></b></div>
					<div class="formRow"><label>Регион:</label><b><?php echo htmlspecialchars($data['new_domain_la_state'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Индекс:</label><b><?php echo htmlspecialchars($data['new_domain_la_index'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Город:</label><b><?php echo htmlspecialchars($data['new_domain_la_city'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Адрес:</label><b><?php echo htmlspecialchars($data['new_domain_la_address'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow fullRow">Контактные данные администратора домена:</div>
					<div class="formRow"><label>Регион:</label><b><?php echo htmlspecialchars($data['new_domain_pa_state'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Индекс:</label><b><?php echo htmlspecialchars($data['new_domain_pa_index'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Город:</label><b><?php echo htmlspecialchars($data['new_domain_pa_city'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>Адрес:</label><b><?php echo htmlspecialchars($data['new_domain_pa_address'],ENT_COMPAT,'utf-8')?></b></div>
					<div class="formRow"><label>ФИО получателя почты:</label><b><?php echo htmlspecialchars($data['new_domain_pa_addressee'],ENT_COMPAT,'utf-8')?></b></div>
					<?php endif?>
				<?php else:?>
					<p>Режим заполнения формы определить не удалось</p>
				<?php endif?>
				<div style="clear:both;"></div>
			</div>
			</div>
		<?php else:?>
			<p>Пользователь не заполнял заявок на домен.</p>
		<?php endif?>
	</div>
	<div id="tabs-domain-requests" style="padding:10px;">
		<?php if(count($requests)>0):?>
			<p><a href="#domainRequests_package_<?php echo $package->id?>">Посмотреть подробно</a></p>
			<table class="tablesorter" style="border:1px solid #B6C3C7;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Домен</th>
						<th>Зона</th>
						<th>Вид регистрации</th>
						<th>Дата заявки</th>
						<th>Статус</th>
						<th>Дата изменения</th>
					</tr>
				</thead>
				<?php foreach($requests as $obRequest):?>
					<tr>
						<td>
							<?php echo $obRequest->id;?>
						</td>
						<td>
							<a href="#domainRequest_<?php echo $obRequest->id;?>" class="domainRequestLink"><?php echo $obRequest->domain;?></a>
						</td>
						<td>
							<?php echo $obRequest->zone;?>
						</td>
						<td>
							<?php echo $obRequest->mode;?>
						</td>
						<td>
							<?php echo $obRequest->date_add;?>
						</td>
						<td>
							<?php echo $obRequest->status;?>
						</td>
						<td>
							<?php echo $obRequest->date_change;?>
						</td>
					</tr>
				<?php endforeach;?>
			</table>
		<?php endif?>
	</div>
</div>