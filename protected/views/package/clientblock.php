<?php
/* 
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

	$client = People::getById($client_id);
	if ( $client )
	{
		if ( sizeof($client->packages) > 1 ) print '<li id="li'.$client_id.'" class="more"> <a class="lessClick" onClick="ShowHide(\'li'.$client_id.'\');"></a>';
		else
		print '<li id="li'.$client_id.'">';

		?>
		<div class="clientInfo">

			<span class="clientName">
				<a onClick='clientCard(<?=$client->id?>)'><?=$client->fio?></a>
				<div class="tips">
				<div class="tipsTop"></div>
				<div class="tipsBody">
					<b>Телефон</b>: <?=$client->phone?><br>
					<b>ID</b>: <a onClick="addEditClient(<?=$client->id?>)">#<?=$client->id?></a><br>
					<div style="text-align: center; margin: 10px 0 0 0;"><a onClick="Package(0, <?=$client->id?>)"><b>создать новый заказ</b></a></div>
				</div>
				<div class="tipsBottom"></div>
			</div>
			</span>
			&lt;<?=$client->mail?>&gt;
		</div>
	<?php
		$packs = $client->packages;
		$forhide = '';
		if ($packs)
		foreach ($packs as $key=>$package)
		//if ( ($package->status_id != 999)and($package->status_id != 15) ) // Не выводим законченные и отклонённые проекты
		{
		switch (@$package->status_id):
			case 0:print '<div class="projectBox red'.$forhide.'">';break;
			case 1:print '<div class="projectBox red'.$forhide.'">';break;
			case 15:print '<div class="projectBox orange'.$forhide.'">';break;
			case 17:print '<div class="projectBox grey'.$forhide.'">';break;
			case 50:print '<div class="projectBox lightgreen'.$forhide.'">';break;
			case 70:print '<div class="projectBox green'.$forhide.'">';break;
			default:print '<div class="projectBox grey'.$forhide.'">';break;
		endswitch;
		?>

			<div class="projectType">
				<a onClick="Package(<?=$package->id?>, 0)" class="type">Заказ #<?=$package->id?>:&nbsp;<?=$package->name?></a>
				<a onClick="Package(<?=$package->id?>, 0)" class="more">
				<?php
				if ( $package->summa )
					print $package->summa.'руб.&nbsp;&nbsp;&nbsp;&nbsp;';
				?>
				Подробно...</a><br>
				<?php
				if ( $package->manager_id != Yii::app()->user->id and $package->manager_id != 0 )
					print '('.@$package->manager->fio.')';
				?>
			</div>
		<?php

			if ( $package->redmine_proj )
				$percent = Redmine::getIssuePercent($package->redmine_proj);
			else
				$percent = '0';

			switch ($package->status_id):
			case 0:print	'<div class="projectState new"><a onClick="takePack('.$package->id.', '.$client_id.');"><strong>Взять заказ</strong></a><br><a onClick="decline('.$package->id.', '.$client_id.')" class="icon">Отклонить</a></div>';break;
			case 1:print	'<div class="projectState new"><a onClick="takePack('.$package->id.', '.$client_id.');"><strong>Взять заказ</strong></a><br><a onClick="decline('.$package->id.', '.$client_id.')" class="icon">Отклонить</a></div>';break;
			case 15:print	'<div class="projectState"><br/>'.@$package->status->name.'</div>';break;
			case 17:print	'<div class="projectState">			<strong class="uppper">Не оплачен!</strong>
				<a onClick="addPay('.$package->id.', '.$client_id.');" class="icon"><img src="images/icon04.png" title="Заказ оплачен"/></a>
				<a href="mailto:'.$client->mail.'" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
				<a onClick="decline('.$package->id.', '.$client_id.')" class="icon"><img src="images/icon03.png" title="Отклонить"/></a></div>';break;

			case 50:print	'<div class="projectState">			<strong>Выполняется</strong>
				<div class="progressBar">
					<div class="progressStat" style="width:'.$percent.'%">'.$percent.'%</div>
				</div></div>';break;

			case 70:print	'<div class="projectState"><strong class="done">Выполнен!</strong><br/>
				<a href="#" class="icon"><img src="images/icon01.png" title="Подготовить документы к отправке"/></a>
				<a href="mailto:'.$client->mail.'" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
				<a href="#" class="icon"><img src="images/icon03.png" title="В архив"/></a></div>';break;

			default:print	'<div class="projectState"><br/>'.@$package->status->name.'</div>';break;
		endswitch;
		?>
			<div class="projectDomain">
				<a onClick='editDomain(<?=@$package->site->id?>)' title="<?=@$package->site->url?>"><?=@$package->site->url?></a>
			</div>
			<div class="projectDate act"><?=$package->dt_beg?></div>
		</div>
	<?php
		$forhide = ' forhide';
		}
		print "</li>";
	}
	else return null;
?>
