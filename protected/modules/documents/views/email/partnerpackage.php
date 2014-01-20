<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head></head>
<body>
Здравствуйте, <?php echo $user->fio?>!<br>
Заказ вашего клиента обновлен.<br>
Клиент: <?php echo $package->client->fio?><br>
Номер заказа: <?php echo $package->id?><br>
Текущий статус: <?php echo $package->wf_status->name?><br>
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