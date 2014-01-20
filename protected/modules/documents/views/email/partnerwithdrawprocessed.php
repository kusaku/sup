<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head></head>
<body>
Ваш запрос #<?php echo $withdraw->id?> на вывод средств обработан.<br>
Статус запроса: вывод средств <?php echo Yii::t('withdraw',$withdraw->status) ?><br>
По всем вопросам просьба связаться с вашим менеджером <?php echo CHtml::link($withdraw->partner_data->manager->fio, 'mailto:'.$withdraw->partner_data->manager->mail) ?><br>

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