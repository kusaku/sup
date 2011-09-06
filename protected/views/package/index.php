<?php if ($client = People::model()->findByPk($client_id)): ?>
	<ul id="ul<?= $client->primaryKey?>" class="columnsBody">
		<?php $condition = Yii::app()->user->checkAccess('admin') ? null : 'packages.manager_id = '.Yii::app()->user->id; ?>
		<?php $packages = $client->packages(array(
			//'condition'=>'packages.status_id NOT IN (15, 999)',
			'condition'=>$condition, 
			//'order'=>'status_id ASC, dt_change DESC',
			'order'=>'dt_change DESC',			
			)); ?>
		<?php if (count($packages) > 1): ?>
		<li class="more"><a class="lessClick" onClick="ShowHide('ul<?= $client->primaryKey?>');"></a>
		<?php else : ?>
		<li>
		<?php endif; ?>
		<div class="clientInfo">
			<span class="clientName"><a onClick="addEditClient(<?=$client->id?>)" class="nameText"><?=$client->mail?></a>
				<div class="tips">
					<div class="tipsTop"></div>
					<div class="tipsBody">
						<b>Имя</b>: <?= $client->fio?>
						<br>
						<b>E-mail</b>: <a href="mailto:<?=$client->mail?>">&lt;<?= $client->mail?>&gt;</a>
						<br>
						<b>Телефон</b>: <?= $client->phone?>
						<br>
						<?php foreach ($client->attr as $attr): ?>
							<?php if (! empty($attr->values[0]->value)): ?>
							<div><b><?= $attr->name?></b>: <?= $attr->values[0]->value?></div>								
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="tipsBottom"></div>
				</div>
			</span>
			<a onClick="Package(0, <?=$client->id?>)" title="Создать новый заказ">Новый заказ</a>&nbsp;<a onClick="clientCard(<?=$client->id?>)" title="Просмотреть заказы клиента">Карточка клиента</a>&nbsp;<a onClick="loggerForm(<?=$client->id?>)" title="Просмотреть журнал">Журнал</a>
		</div>							
		<?php $class = ''; ?>
		<?php $style = ''; ?>		
		<?php foreach ($packages as $package): ?>
			<?php 
				switch ($package->status_id) {
					case 0: $color = 'orange'; break;
					case 1: $color = 'red'; break;
					case 10: $color = 'grey'; break;
					case 15: $color = 'red'; break;					
					case 17: $color = 'grey'; break;
					case 20: $color = 'lightgreen'; break;
					case 30: $color = 'green'; break;
					case 50: $color = 'lightgreen'; break;
					case 70: $color = 'green'; break;
					default: $color = 'grey'; break;
				}
			?>
			<div class="projectBox <?=$class?> <?=$class?>" <?=$style?>>
		
			<div class="projectType">
				<a onClick="Package(<?=$package->primaryKey?>, 0)" class="type">#<?= $package->primaryKey?>: <?= $package->name?>
				<?= $package->summa ? " <strong style=\"float:right;\">{$package->summa} руб.</strong>" : ''?></a>				
				<a onClick="Package(<?=$package->primaryKey?>, 0)" class="more">Подробно...</a> 
				<?php if($package->manager_id != Yii::app()->user->id): ?>
				<br/>
				<span style="color:#999999"><?=($package->manager) ? "({$package->manager->fio})" : '(не присвоен менеджеру)'?></span>
				<?php endif; ?>
			</div>
			
			<?php $package->redmine_proj and $percent = Redmine::getIssuePercent($package->redmine_proj) or $percent = 0; ?>
			
			<?php switch ($package->status_id): 
				case 0: ?>
				<?php case 1: ?>
				<div class="projectState new">
					<a onClick="takePack(<?= $package->primaryKey?>, <?= $client->primaryKey?>);"><strong>ВЗЯТЬ ЗАКАЗ</strong></a>
					<br/>
					<a onClick="decline(<?= $package->primaryKey?>, <?= $client->primaryKey?>)" class="icon">Отклонить</a>
				</div>
				<?php break; ?>
				
				<?php case 15: ?>
				<div class="projectState new">					
					<strong>ОТКЛОНЁН</strong>
					<br/>
					<a onClick="takePack(<?= $package->primaryKey?>, <?= $client->primaryKey?>);"><strong>ВЗЯТЬ ЗАКАЗ</strong></a>
				</div>
				<?php break; ?>
				
				<?php case 10: ?>
				<?php case 17: ?>
				<div class="projectState">
					<strong class="uppper">НЕ ОПЛАЧЕН</strong>
					<a onClick="payForm(<?= $package->primaryKey?>, <?= $client->primaryKey?>, <?= $package->summa-$package->paid?>);" class="icon"><img src="images/icon04.png" title="Поставить оплату (<?=$package->summa-$package->paid?>руб.)"/></a><a onClick="selectMailTemplate(<?= $client->primaryKey?>)" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a><a onClick="decline(<?= $package->primaryKey?>, <?= $client->primaryKey?>)" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
				</div>
				<?php break; ?>
				
				<?php case 20: ?>
				<div class="projectState">
					<strong class="uppper">ЧАСТ. ОПЛ.</strong>
					<a onClick="payForm(<?= $package->primaryKey?>, <?= $client->primaryKey?>, <?= $package->summa-$package->paid?>);" class="icon"><img src="images/icon04.png" title="Поставить оплату (<?=$package->summa-$package->paid?>руб.)"/></a><a onClick="selectMailTemplate(<?= $client->primaryKey?>)" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a><a onClick="decline(<?= $package->primaryKey?>, <?= $client->primaryKey?>)" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
				</div>
				<?php break; ?>
				
				<?php case 30: ?>
				<div class="projectState">
					<div class="progressBar">
						<div class="progressStat" style="width:<?= $percent ?>%">
							<?= $percent?>%
						</div>
					</div>
					<a onClick="createAllRedmineIssues(<?= $package->primaryKey?>, <?= $client->primaryKey?>);" class="icon"><img src="images/towork.png" title="Отдать в работу все заказанные услуги"/></a><a onClick="selectMailTemplate(<?= $client->primaryKey?>)" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a><a onClick="decline(<?= $package->primaryKey?>, <?= $client->primaryKey?>)" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
				</div>
				<?php break; ?>
							
				<?php case 50: ?>
				<div class="projectState">
					<div class="progressBar">
						<div class="progressStat" style="width:<?= $percent ?>%">
							<?= $percent?>%
						</div>
					</div>
					<a onClick="alert('Пока не работает =(');" class="icon"><img src="images/complete.png" title="Все работы выполнены"/></a><a onClick="selectMailTemplate(<?= $client->primaryKey?>)" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a><a onClick="decline(<?= $package->primaryKey?>, <?= $client->primaryKey?>)" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
				</div>
				<?php break; ?>
				
				<?php case 70: ?>
				<div class="projectState">
					<strong class="done">ВЫПОЛНЕН</strong>
					<br/>
					<a onClick="alert('Пока не работает =(');" class="icon"><img src="images/icon01.png" title="Подготовить документы к отправке"/></a><a onClick="selectMailTemplate(<?= $client->primaryKey?>)" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a><a onClick="alert('Пока не работает =(');" class="icon"><img src="images/icon03.png" title="В архив"/></a>
				</div>
				<?php break; ?>
				
				<?php default: ?>
				<div class="projectState">
					<strong class="done"><?= strtoupper($package->status->name)?></strong>
				</div>
				<?php break; ?>
			<?php endswitch; ?>
			
			<div class="projectDomain">
				<?php if(isset($package->site)): ?>
					<a onClick='editDomain(<?=$package->site->id?>)' title="<?=$package->site->url?>"><?= $package->site->url?></a>				
				<?php endif; ?>
				<br/>
				<?= date('d.m.Y', strtotime($package->dt_change)) ?>
			</div>
			<div class="projectDate act">
				<?= $package->descr?>
			</div>
		</div>
		<?php $class = 'forhide'; ?>
		<?php $style = 'style="display:none;"'; ?>						
		<?php endforeach; ?>
		<?php if(!isset($package)) :?>
			<div class="projectBox grey">
				<div class="projectType"></div>
				<div class="projectState"></div>
				<div class="projectDomain"></div>
				<div class="projectDate act"></div>
			</div>
		<?php endif; ?>
		</li>
	</ul>		
<?php else: ?>
<ul class="columnsBody">
	<li><strong>Клиент #<?= $client_id?> не существует, и данных по нему нет.</strong></li>
</ul>
<?php endif; ?>