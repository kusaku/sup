<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?= Yii::app()->name['name']?></title>
		<link href="/css/reset.css" rel="stylesheet" type="text/css" />
		<link href="/css/ui-lightness/jquery-ui-1.8.11.custom.css" rel="stylesheet" type="text/css" />
		<link href="/js/jwysiwyg/jquery.wysiwyg.css" rel="stylesheet" type="text/css" />
		<!-- CSS по модулям -->		
		<link href="/css/smartbox.css" rel="stylesheet" type="text/css" />
		<link href="/css/style.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/sup.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/tabs.css" rel="stylesheet" type="text/css" />
		<link href="/css/calendar.css" rel="stylesheet" type="text/css" />
		<script language="JavaScript" src="/js/jquery-1.6.2.min.js"></script>
		<script language="JavaScript" src="/js/jquery.ui.datepicker-ru.js"></script>
		<script language="JavaScript" src="/js/jquery-ui-1.8.11.custom.min.js"></script>
		<script language="JavaScript" src="/js/jquery.smartBox.min.js"></script>
		<script language="JavaScript" src="/js/main.js?<?= Yii::app()->name['version']?>"></script>
		<!-- Скрипты по модулям -->
		<script language="JavaScript" src="/js/calendar.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/tabs.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/billmgr.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/redmine.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/mail.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jwysiwyg/jquery.wysiwyg.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/logger.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/report.js?<?= Yii::app()->name['version']?>"></script>
	</head>
	<body>
		<?= $content?>
	</body>
</html>
