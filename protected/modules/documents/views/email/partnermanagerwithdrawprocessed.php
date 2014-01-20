<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head></head>
<body>
Запрос #<?php echo $withdraw->id?> на вывод средств от партнера #<?php echo $withdraw->id_partner?>
 <?php echo $withdraw->partner_data->user_data->mail?> обработан бухгалтером.<br>
Статус запроса: <?php echo Yii::t('withdraw',$withdraw->status) ?>
<br>
<br>
---
<br>
С уважением,<br>
служба поддержки <br>
компании Фабрика сайтов<br>
<a href="mailto:info@fabricasaitov.ru">info@fabricasaitov.ru</a><br>
</body>
</html>