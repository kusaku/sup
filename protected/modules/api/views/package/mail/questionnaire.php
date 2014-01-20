<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<header>
		<title><?php echo $package->name?></title>
	</header>
	<body>
		Здравствуйте, <?php echo $package->manager->fio?>!<br>
		Клиент <?php echo $package->client->fio?> (<?php echo $package->client->mail?>) заполнил анкету на создание сайта.
		<hr>
		<table border="1" cellspacing="0" cellpadding="4">
			<tr>
				<td valign="top">Тематика сайта:</td><td><?php echo nl2br(strip_tags($questionnaire->description))?></td>
			</tr>
			<tr>
				<td valign="top">Предпочтительные цвета:</td><td><?php echo nl2br(strip_tags($questionnaire->colors))?></td>
			</tr>
			<tr>
				<td valign="top">Сайты которые нравятся:</td><td><?php echo nl2br(strip_tags($questionnaire->favorite_sites))?></td>
			</tr>
		</table>
		<hr>
		Письмо сформированно автоматически, при помощи Системы Управления Проектами Фабрики Сайтов
	</body>
</html>