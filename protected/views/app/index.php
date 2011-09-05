<?php
	$weekdays = array('Пн.','Вт.','Ср.','Чт.','Пт.','Сб.','Вс.');
	$day= $weekdays[date('N')-1];
	$months = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	$month = $months[date('n')-1];
?>
<!--	Всё для поп-ап окна	-->
<div id="modal"></div>
<div id="sup_popup" class="popup"></div>
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
			<li><a onClick="massMail()" class="Mailing">Создать рассылку</a></li>
			<li><a href="http://doc.fabricasaitov.ru/" target="_blank" class="Wiki">Wiki</a></li>
		</ul>
		<form method="post" action="#">
			<input class="searchClient" id="searchClient" name="clientName" placeholder="Поиск клиента..." size="67"/>
			<a onClick="searchClear()" class="buttonClear hidden" id="buttonClear"></a>
		</form>
		<a onclick="addEditClient(<?=Yii::app()->user->id?>)" class="userName"><?=Yii::app()->user->fio?></a>
		<a href="/app/logout" class="logout">выход</a>
	</div>
	<div class="today">
		<span class="name"><?=$day?></span> - <?=date('d')?> <?=$month?> <?=date('Y')?>г.
		<a onClick="calendarToggle()" style="text-decoration: none;">
			<div class="datePicker"></div>
			<div id="eventsCount"></div>
		</a>
	</div>
	<div class="newOrders">
		<a href="#">Новые заказы</a><a href="#" class="newOrdersCount" id="newOrdersCount">0</a>
		<!--<div class="tips">
			<div class="tipsTop"></div>
			<div class="tipsBody">Вывод списка всего нового<br/>Вывод списка всего нового<br/>Вывод списка всего нового</div>
			<div class="tipsBottom"></div>
		</div>-->
	</div>
	<div class="newEvents">
		<a href="#">Новые события</a><a href="#" class="newEventsCount" id="newEventsCount">0</a>
		<!--<div class="tips">
			<div class="tipsTop"></div>
			<div class="tipsBody">Вывод списка всего нового<br/>Вывод списка всего нового<br/>Вывод списка всего нового</div>
			<div class="tipsBottom"></div>
		</div>-->
	</div>
	<div class="doneProjects">
		<a href="#">Выполненные проекты</a><a href="#" class="doneProjectsCount" id="doneProjectsCount">0</a>
		<!--<div class="tips">
			<div class="tipsTop"></div>
			<div class="tipsBody">Вывод списка всего нового<br/>Вывод списка всего нового<br/>Вывод списка всего нового</div>
			<div class="tipsBottom"></div>
		</div>-->
	</div>

	<?php if(Yii::app()->user->checkAccess('admin')): ?>
	<div class="tabs" style="clear:both;">
		<div class="tabcontainer">
			<span id="tabAllProjects" class="tab selected" onclick="selectTab('AllProjects')">Все проекты</span>
			<span id="tabUsers" class="tab" onclick="selectTab('Users')">Пользователи</span>
		</div>
		<div id="tabContentAllProjects" class="tabContent">
			<ul class="columnsHead">
				<li><a href="#" class="colClient">Клиент</a></li>
				<li><a href="#" class="colOrder">Заказ</a></li>
				<li><a href="#" class="colState">Состояние</a></li>
				<li><a href="#" class="colDomain">Домен</a></li>
				<li><a href="#" class="colDate active desc">Дата</a></li>
			</ul>		
			<div id="sup_content">
				<?php /*$this->forward('/package', false);*/ ?>
			</div>
		</div>
		<div id="tabContentUsers" class="tabContent hidden">
			<?php $this->renderPartial('/snippets/users'); ?>
		</div>
	</div>
	<?php else: ?>
	<ul class="columnsHead">
		<li><a href="#" class="colClient">Клиент</a></li>
		<li><a href="#" class="colOrder">Заказ</a></li>
		<li><a href="#" class="colState">Состояние</a></li>
		<li><a href="#" class="colDomain">Домен / Дата</a></li>
		<li><a href="#" class="colDate active desc">Коммент</a></li>
	</ul>		
	<div id="sup_content">
		<!-- прелодер загружает сюда данные -->
	</div>	
	<?php endif; ?>
</div>
<div style="position: fixed; top: 0; left: 0; height: 10px; width: 10px;" onClick="about()"></div>