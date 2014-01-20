<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Система хранения документов</title>
		<link href="/css/reset.css" rel="stylesheet" type="text/css" />
		<link href="/js/jquery.ui/custom.css" rel="stylesheet" type="text/css" />
		<link href="/js/jwysiwyg/jquery.wysiwyg.css" rel="stylesheet" type="text/css" />
		<link href="/css/smartbox.css" rel="stylesheet" type="text/css" />
		<link href="/css/style.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/docs2.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<!-- CSS по модулям -->
		<link href="/css/sup.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/tabs.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/calendar.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<link href="/css/hint.css?<?= Yii::app()->name['version']?>" rel="stylesheet" type="text/css" />
		<!-- общие скрипты -->
		<script language="JavaScript" src="/js/jquery.min.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/jquery.ui.min.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/main.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/hint.js?<?= Yii::app()->name['version']?>"></script>
		<!-- скрипты по модулям -->
		<script language="JavaScript" src="/js/calendar.js?<?= Yii::app()->name['version']?>"></script>
		<script language="JavaScript" src="/js/docs2.js?<?= Yii::app()->name['version']?>"></script>
	</head>
	<body>
		<?= $content?>
	</body>
</html>
