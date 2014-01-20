<?php
/**
 * @var DefaultController $this
 */
$weekdays = array('Пн.','Вт.','Ср.','Чт.','Пт.','Сб.','Вс.');
	$day= $weekdays[date('N')-1];
	$months = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	$month = $months[date('n')-1];
?>
<script type="text/javascript">
    var iCurrentUserId=<?php echo Yii::app()->user->id;?>;
</script> 
<!--	Всё для поп-ап окна	-->
<div id="modal"></div>
<!--<div id="sup_popup" class="popup"></div>-->
<div id="sup_preloader" class="popup"><img src="/images/preloader.gif" boreder="0"></div>

<div class="wrapper">

	<div class="logo">
		<h1><a href="/" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="userBar">
		<ul>
			<li><a onClick="editCalendarEvent(0)" class="addOrder">Напоминание</a></li>
			<li><a onClick="addEditClient(0)" class="addClient">Добавить клиента</a></li>
			<li><a onClick="selectReportType()" class="Reports">Создать отчет</a></li>
			<!--<li><a onClick="massMail()" class="Mailing">Создать рассылку</a></li>-->
			<li><a href="http://doc.fabricasaitov.ru/" target="_blank" class="Wiki">Wiki</a></li>
			<?php if(Yii::app()->user->checkAccess('admin')): ?>
			    <li><a href="/admin" class="admin">Управление SUP</a></li>
			<?php else:?>
                <li></li>
			<?php endif?>
		</ul>
		<form method="post" id="searchClient">
			<input class="inputField" placeholder="Поиск клиента..." size="67"/>
			<a class="buttonClear hidden"></a>
		</form>
		<a onclick="addEditClient(<?=Yii::app()->user->id?>)" class="userName"><?=Yii::app()->user->fio?></a>
		<a href="/default/logout" class="logout">выход</a>
	</div>
	<div class="today">
		<span class="name"><?=$day?></span> - <?=date('d')?> <?=$month?> <?=date('Y')?>г.
		<a onClick="calendarToggle()" style="text-decoration: none;">
			<div class="datePicker"></div>
			<div id="eventsCount"></div>
		</a>
	</div>
	<?php $this->widget('manager.widgets.LastNoticeWidget');?>
	<div class="tabscontainer projects" style="clear:both;">
		<ul>
			<li><a href="#tabs-projects">Все проекты</a></li>
			<?php switch(true):
				case Yii::app()->user->checkAccess('admin'):?>
					<li><a href="manager/dashboard">Состояние дел</a></li>
					<li><a href="manager/people">Пользователи</a></li>
				<?php case Yii::app()->user->checkAccess('topmanager'):?>
					<li><a href="manager/payment/list">Платежи</a></li>
			<?php endswitch?>
		</ul>
		<div id="tabs-projects"><div id="sup_content"></div></div>
	</div>
</div>
<div style="position: fixed; top: 0; left: 0; height: 10px; width: 10px;" onClick="about()"></div>