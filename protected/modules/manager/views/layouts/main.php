<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?= Yii::app()->name['name']?></title>
		<link href="/css/reset.css" rel="stylesheet" type="text/css" />
		<link href="/js/jquery.ui/custom.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" type="text/css" href="/assets/8477ab15/gridview/styles.css" />
		<link href="/js/jwysiwyg/jquery.wysiwyg.css" rel="stylesheet" type="text/css" />
		<link href="/css/smartbox.css" rel="stylesheet" type="text/css" />
		<link href="/css/style.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<!-- CSS по модулям -->
		<link href="/css/sup.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/tabs.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/calendar.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/hint.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/jquery.contextMenu.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/jquery.jscrollpane.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<!-- общие скрипты -->
		<script type="text/javascript" src="/js/jquery.min.js?<?= Yii::app()->name['version']?>"></script>
		<!--<script type="text/javascript" src="/js/jquery.ui.min.js?<?= Yii::app()->name['version']?>"></script>-->
		<script type="text/javascript" src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
		<script type="text/javascript" src="/js/jquery.mousewheel.min.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.autocomplete.sup.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.ui.datepicker.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.form.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.smartBox.min.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jwysiwyg/jquery.wysiwyg.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery-ui-timepicker-addon.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery-ui-timepicker-ru.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.jscrollpane.min.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.tablesorter.min.js?<?=Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/hashevents.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/main.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/hint.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/jquery.contextMenu.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/timer.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/popup.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/supWindow.js?<?= Yii::app()->name['version']?>"></script>
		<!-- скрипты по модулям -->
		<script type="text/javascript" src="/js/calendar.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/billmgr.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/redmine.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/mail.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/logger.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/report.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/package.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/payment.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/partner.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/people.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/cabinet.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/domainrequest.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/dashboard.js?<?= Yii::app()->name['version']?>"></script>
		<script type="text/javascript" src="/js/messageTemplate.js?<?= Yii::app()->name['version']?>"></script>

		<style type="text/css">
			@media print {
				body {overflow-y:visible;}
			}
		</style>
	</head>
	<body>
		<?= $content?>
	</body>
</html>
