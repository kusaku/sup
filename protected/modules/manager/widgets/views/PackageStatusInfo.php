<?php
/**
 * @var Package $package
 * @var PackageStatusInfoWidget $this
 */
$percent = $package->rm_issue_id ? Redmine::getIssuePercent($package->rm_issue_id) : 0;
$editPackageLink='<li class="packageEdit separator"><a href="#package" onClick="Package('.$package->primaryKey.','.$client->primaryKey.')">Редактировать заказ</a></li>';
$packageQuestionnaire='';
$packageDomainRequest='';
if($package->questionnaire) {
	$packageQuestionnaire='<li class="questionnaire"><a href="#packageQuestionnaire_'.$package->id.'" rel="auto" title="Просмотреть анкету">Анкета</a></li>';
}
$packageAddDomainRequest='<li class="addDomainRequest"><a href="#domainRequest_package_'.$package->id.'" rel="auto" title="Добавить заявку на домен">Добавить домен</a></li>';
if($package->domainRequests) {
	$packageDomainRequest='<li class="domainRequest"><a href="#domainRequests_package_'.$package->id.'" rel="auto" title="Посмотреть список заявок на домен">Заявки на домен</a></li>';
	$packageAddDomainRequest='';
}

switch ($package->status_id):
	case 0:
	case 1:?>
		<div class="projectState new">
			<a onClick="takePack(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);"><strong>ВЗЯТЬ ЗАКАЗ</strong></a>
			<br/>
			<a onClick="decline(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>)" class="icon">Отклонить</a>
		</div>
		<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
			 <li class="takePack">
				<a href="#takePack" onClick="takePack(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);">Взять заказ</a>
			</li>
			<li class="declinePack">
				<a href="#cut" onClick="decline(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);">Отклонить</a>
			</li>
			<?php echo $editPackageLink?>
			<?php echo $packageQuestionnaire?>
			<?php echo $packageDomainRequest?>
		</ul>
<?php
			break;
			case 15:
 ?>
		<div class="projectState">
			<a onClick="takePack(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);"><strong>ВЗЯТЬ ЗАКАЗ</strong></a>
			<br>Отклонён
		</div>
		<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
			 <li class="takePack">
				<a href="#takePack" onClick="takePack(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);">Взять заказ</a>
			</li>
			<?php echo $editPackageLink?>
		</ul>
<?php
			break;
			case 17:
 ?>
		<div class="projectState">
			<strong class="done"><?= strtoupper($package->wf_status->name); ?></strong>
			<br/>
			<a class="contextMenuButton" href="#menu"><img src="/images/menu.png" alt="" title="Показать контекстное меню" /></a>
			<?php $this->render('PackageSourceIcon',array('package'=>$package));?>
		</div>
		<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
			<li class="documents">
				<a target="_blank" href="/manager/docs2/<?= $package->primaryKey; ?>" title="Просмотреть и подготовить документы">Просмотреть документы</a>
			</li>
			<li class="addPay">
				<a href="#payment_0_<?php echo $package->id?>" rel="auto" title="Поставить оплату (<?=$package->summ-$package->paid; ?>руб.)">Поставить оплату</a>
			</li>
			<li class="cabinet separator">
				<a href="#cabinet_<?php echo $package->id?>" rel="auto">Общение в ЛКК</a>
			</li>
			<li class="declinePack separator">
				<a href="#cut" onClick="decline(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);">Отклонить</a>
			</li>
			<?php echo $editPackageLink?>
			<?php echo $packageQuestionnaire?>
			<?php echo $packageDomainRequest?>
			<?php echo $packageAddDomainRequest?>
		</ul>
<?php
			break;
			case 50:
			case 60:
 ?>
		<div class="projectState">
			<div class="progressBar">
				<div class="progressStat" style="width:<?= $percent ?>%">
					<?= $percent; ?>%          
				</div>
			</div>
			<a class="contextMenuButton" href="#menu"><img src="/images/menu.png" alt="" title="Показать контекстное меню" /></a>
			<?php $this->render('PackageSourceIcon',array('package'=>$package));?>
		</div>
		<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
			 <li class="packageIsReady">
				<a href="#packageIsReady" onClick="packageIsReady(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);">Все работы выполнены</a>
			</li>
			<li class="documents">
				<a target="_blank" href="/manager/docs2/<?= $package->primaryKey; ?>" title="Просмотреть и подготовить документы">Просмотреть документы</a>
			</li>
			<li class="cabinet separator">
				<a href="#cabinet_<?php echo $package->id?>" rel="auto">Общение в ЛКК</a>
			</li>
			<?php echo $editPackageLink?>
			<?php echo $packageQuestionnaire?>
			<?php echo $packageDomainRequest?>
			<?php echo $packageAddDomainRequest?>
		</ul>
<?php
			break;
			case 70:
 ?>
		<div class="projectState">
			<strong class="done"><?= strtoupper($package->wf_status->name); ?></strong>
			<br/>
			<a class="contextMenuButton" href="#menu"><img src="/images/menu.png" alt="" title="Показать контекстное меню" /></a>
			<?php $this->render('PackageSourceIcon',array('package'=>$package));?>
		</div>
		<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
			<li class="documents">
				<a target="_blank" href="/manager/docs2/<?= $package->primaryKey; ?>" title="Просмотреть и подготовить документы">Просмотреть документы</a>
			</li>
			<li class="cabinet separator">
				<a href="#cabinet_<?php echo $package->id?>" rel="auto">Общение в ЛКК</a>
			</li>
			<?php echo $editPackageLink?>
			<?php echo $packageQuestionnaire?>
			<?php echo $packageDomainRequest?>
			<?php echo $packageAddDomainRequest?>
		</ul>
<?php
			break;
			default:
			switch ($package->payment_id):
			case 0:
			case 17:
 ?>
				<div class="projectState">
					<strong class="done"><?= strtoupper($package->wf_status?$package->wf_status->name:'странно'); ?></strong>
					<a class="contextMenuButton" href="#menu"><img src="/images/menu.png" alt="" title="Показать контекстное меню" /></a>
					<?php $this->render('PackageSourceIcon',array('package'=>$package));?>
				</div>
				<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
					<li class="addPay">
						<a href="#payment_0_<?php echo $package->id?>" rel="auto" title="Поставить оплату (<?=$package->summ-$package->paid; ?>руб.)">Поставить оплату</a>
					</li>
					<li class="documents">
						<a target="_blank" href="/manager/docs2/<?= $package->primaryKey; ?>" title="Просмотреть и подготовить документы">Просмотреть документы</a>
					</li>
					<li class="cabinet separator">
						<a href="#cabinet_<?php echo $package->id?>" rel="auto">Общение в ЛКК</a>
					</li>
					<li class="declinePack separator">
						<a href="#cut" onClick="decline(<?= $package->primaryKey; ?>, <?= $client->primaryKey; ?>);">Отклонить</a>
					</li>
					<?php echo $editPackageLink?>
					<?php echo $packageQuestionnaire?>
					<?php echo $packageDomainRequest?>
					<?php echo $packageAddDomainRequest?>
				</ul>	
		<?php
					break;
					case 30:
 ?>
				<div class="projectState">
					<div class="progressBar" style="overflow:hidden;">
						<div class="progressStat" style="line-height:6px;overflow:hidden;width:<?= $percent ?>%;white-space:nowrap;"><?= $percent; ?> %</div>
					</div>
					<a class="contextMenuButton" href="#menu"><img src="/images/menu.png" alt="" title="Показать контекстное меню" /></a>
					<?php $this->render('PackageSourceIcon',array('package'=>$package));?>
				</div>
				<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
					<li class="createAllRedmineIssues">
						<a href="#createAllRedmineIssues" onclick="createAllRedmineIssues(<?=$package->primaryKey; ?>, 0, <?= $package->client_id; ?>);" title="Отдать в работу все заказанные услуги (<?=$package->summ-$package->paid; ?>руб.)">Отдать в работу</a>
					</li>
					<li class="documents">
						<a target="_blank" href="/manager/docs2/<?= $package->primaryKey; ?>" title="Просмотреть и подготовить документы">Просмотреть документы</a>
					</li>
					<li class="cabinet separator">
						<a href="#cabinet_<?php echo $package->id?>" rel="auto">Общение в ЛКК</a>
					</li>
					<?php echo $editPackageLink?>
					<?php echo $packageQuestionnaire?>
					<?php echo $packageDomainRequest?>
					<?php echo $packageAddDomainRequest?>
				</ul>
		<?php
					break;
					case 18:
					case 20:
					default:
 ?>
				<div class="projectState">
					<strong class="done"><?= strtoupper($package->wf_status?$package->wf_status->name:'странно'); ?></strong>
					<a class="contextMenuButton" href="#menu"><img src="/images/menu.png" alt="" title="Показать контекстное меню" /></a>
					<?php $this->render('PackageSourceIcon',array('package'=>$package));?>
				</div>
				<ul class="packageContextMenu" id="contextMenu<?php $package->id?>">
					<li class="addPay">
						<a href="#payment_0_<?php echo $package->id?>" rel="auto" title="Поставить оплату (<?=$package->summ-$package->paid; ?>руб.)">Поставить оплату</a>
					</li>
					<li class="createAllRedmineIssues">
						<a href="#createAllRedmineIssues" onclick="createAllRedmineIssues(<?=$package->primaryKey; ?>, 0, <?= $package->client_id; ?>);" title="Отдать в работу все заказанные услуги (<?=$package->summ-$package->paid; ?>руб.)">Отдать в работу</a>
					</li>
					<li class="documents">
						<a target="_blank" href="/manager/docs2/<?= $package->primaryKey; ?>" title="Просмотреть и подготовить документы">Просмотреть документы</a>
					</li>
					<li class="cabinet separator">
						<a href="#cabinet_<?php echo $package->id?>" rel="auto">Общение в ЛКК</a>
					</li>
					<?php echo $editPackageLink?>
					<?php echo $packageQuestionnaire?>
					<?php echo $packageDomainRequest?>
					<?php echo $packageAddDomainRequest?>
				</ul>
		<?php
					break;
					endswitch;
					break;
					endswitch;
 ?>