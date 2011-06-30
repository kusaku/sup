<?php
	$weekdays = array('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье');
	$day= $weekdays[date('N')-1];
	$months = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	$month = $months[date('n')-1];
?>
<!--	Всё для поп-ап окна	-->
<div id="modal" onclick="alert('Жми кнопку ЗАКРЫТЬ ;)');"></div>
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
			<li><a href="javascript:alert('Пока не работает')" class="lastDone">Последние выполненные</a></li>
			<li><a href="javascript:alert('Пока не работает')" class="notWorked">Не распределены</a></li>
		</ul>
		<form method="post" action="#">
			<input class="searchClient" id="searchClient" name="clientName" placeholder="Поиск клиента..." size="67"/>
			<a onClick="searchClear()" class="buttonClear hidden" id="buttonClear"></a>
		</form>
		<a href="#" class="userName"><?=Yii::app()->user->fio?></a>
		<a href="/app/logout" class="logout">выход</a>
	</div>
	<div class="today">
		<span class="name"><?=$day?></span> - <?=date('d')?> <?=$month?> <?=date('Y')?>г. <a onClick="calendarToggle()" class="datePicker">календарик</a>
		<span id="eventsCount"></span>
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
	<ul class="columnsHead">
		<li><a href="#" class="colClient">Клиент</a></li>
		<li><a href="#" class="colOrder">Заказ</a></li>
		<li><a href="#" class="colState">Состояние</a></li>
		<li><a href="#" class="colDomain">Домен</a></li>
		<li><a href="#" class="colDate active desc">Дата</a></li>
	</ul>

	<div id="sup_content"></div>

</div>
<div style="position: fixed; bottom: 0; right: 0; height: 10px; width: 10px;" onClick="about()"></div>