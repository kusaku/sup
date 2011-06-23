<?php /*

	Основной Layout - шаблон для всех страничек

*/?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="ru" />

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ui-lightness/jquery-ui-1.8.11.custom.css" />

	<script language="JavaScript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-1.5.2.js"></script>
	<script language="JavaScript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-1.8.11.custom.min.js"></script>
	<script language="JavaScript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/main.js"></script>
	<link rel="SHORTCUT ICON" href="/favicon.ico" />

	<title><?=Yii::app()->name['name']?></title>
</head>



<body>

<a href="/"><img src="/images/logo.png" border="0" /></a><br /><br />


<h1>Login</h1>

<p>Представьтесь пожалуйста:</p>

<div class="form">

<form action="/login" method="POST">
	<div class="row">
		<label for="login">LOGIN:</label>
		<input type="text" name="login" />
	</div>

	<div class="row">
		<label for="password">PASSWORD:</label>
		<input type="password" name="password" />
	</div>

	<div class="row rememberMe">
		<label for="remember_me">REMEMBER ME:</label>
		<input type="checkbox" name="remember_me" />
	</div>

	<div class="row buttons">
		<input type="submit" value="Поехали!" />
	</div>
</form>

</div><!-- form -->

<!-- footer -->
<div class="footer"><?=Yii::app()->name['vendor']?> / <?=Yii::app()->name['name']?> (<?=Yii::app()->name['version']?>)</div>
</body>
</html>
