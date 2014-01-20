<?php
Yii::app()->getClientScript()->registerCoreScript('jquery');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?= Yii::app()->name['name']?></title>
		<link href="/css/reset.css" rel="stylesheet" type="text/css" />
		<link href="/js/jquery.ui/custom.css" rel="stylesheet" type="text/css" />
		<link href="/js/jwysiwyg/jquery.wysiwyg.css" rel="stylesheet" type="text/css" />
		<link href="/css/smartbox.css" rel="stylesheet" type="text/css" />
		<link href="/css/style.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/admin.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<!-- CSS по модулям -->
		<link href="/css/sup.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/tabs.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/calendar.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/hint.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/admin/services.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/jquery.contextMenu.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/jquery.jscrollpane.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<!-- общие скрипты -->
		<script language="JavaScript" src="/js/jquery.ui.min.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jquery.mousewheel.min.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jquery.form.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jquery-ui-timepicker-addon.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jquery-ui-timepicker-ru.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.jscrollpane.min.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jquery.contextMenu.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/timer.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/popup.js?<?= Yii::app()->name['version']?>"></script>
	</head>
	<body>
		<?php
			$weekdays = array('Пн.','Вт.','Ср.','Чт.','Пт.','Сб.','Вс.');
			$day= $weekdays[date('N')-1];
			$months = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
			$month = $months[date('n')-1];
		?>
		<script type="text/javascript">
			var iCurrentUserId=<?php echo Yii::app()->user->id;?>;
		</script>
		<!--    Всё для поп-ап окна -->
		<div id="modal"></div>
		<!--<div id="sup_popup" class="popup"></div>-->
		<div id="sup_preloader" class="popup"><img src="/images/preloader.gif" boreder="0"></div>

		<div class="wrapper" style="width:auto;padding:0 20px 10px;">
			<div class="logo">
				<h1><a href="/" title="go home"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
			</div>
			<div class="userBar">
				<ul>
					<li><a onClick="editCalendarEvent(0)" class="addOrder">Напоминание</a></li>
					<li><a onClick="addEditClient(0)" class="addClient">Добавить клиента</a></li>
					<li><a onClick="selectReportType()" class="Reports">Создать отчет</a></li>
					<li><a href="http://doc.fabricasaitov.ru/" target="_blank" class="Wiki">Wiki</a></li>
					<li><a href="/manager">Заказы</a></li>
				</ul>
				<a onclick="addEditClient(<?=Yii::app()->user->id?>)" class="userName"><?=Yii::app()->user->fio?></a>
				<a href="/default/logout" class="logout">выход</a>
			</div>
			<div style="clear:both;"><!-- --></div>
			<?php $this->widget('zii.widgets.CBreadcrumbs', array('links'=>$this->breadcrumbs));?>
			<div id="flashes">
				<?php $this->widget('Message')?>
			</div>
			<div id="sidebar">
<?php
	$this->beginWidget('zii.widgets.CPortlet', array(
		'title'=>'Управление SUP',
	));
	$this->widget('zii.widgets.CMenu', array(
		'items'=>array(
			array('label'=>'Шаблоны сообщений комментариев', 'url'=>array('/admin/waveMessages')),
			array('label'=>'Услуги','items'=>array(
				array('label'=>'Описание', 'url'=>array('/admin/serviceDescription')),
				array('label'=>'Дерево', 'url'=>array('/admin/serviceTree')),
			)),
			array('label'=>'Промокоды','items'=>array(
				array('label'=>'Список', 'url'=>array('/admin/promocode')),
				array('label'=>'Добавить', 'url'=>array('/admin/promocode/add')),
			)),
			array('label'=>'Выгрузка клиентов','items'=>array(
				array('label'=>'Были финансовые взаимоотношения', 'url'=>array('/admin/report/finance'), 'linkOptions'=>array('target'=>'_blank')),
				array('label'=>'Заказали у нас услуги, но пока их не оплатили', 'url'=>array('/admin/report/active'), 'linkOptions'=>array('target'=>'_blank')),
				array('label'=>'Клиенты оплатившие продвижение', 'url'=>array('/admin/report/advert')),
			)),
		),
		'htmlOptions'=>array('class'=>'operations'),
	));
	$this->endWidget();
?>
			</div><!-- sidebar -->
			<div id="admin-content">
				<?= $content?>
			</div>
		</div>
		<div style="position: fixed; top: 0; left: 0; height: 10px; width: 10px;" onClick="about()"></div>
	</body>
</html>
