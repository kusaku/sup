<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<header>
		<title><?php echo $user->fio?></title>
	</header>
	<body>
		Здравствуйте, <?php echo $manager->fio?>!<br>
		Клиент <?php echo $user->fio?> (<?php echo $user->mail?>) обновил реквизиты юридического лица.
		<hr>
		<table border="1" cellspacing="0" cellpadding="4">
			<col width="200" />
			<col width="600" />
			<tr>
				<td valign="top">Название организации:</td><td><?php echo htmlspecialchars($jurPerson->title,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
			<tr>
				<td valign="top">Ф.И.О.</td>
				<td><?php echo htmlspecialchars($jurPerson->director_fio,ENT_COMPAT,'utf-8',true)?><br>
					(<?php echo htmlspecialchars($jurPerson->director_position,ENT_COMPAT,'utf-8',true)?>, действующий на основании <?php
					switch($jurPerson->director_source) {
						case 'charter':
								echo "устава";
							break;
						case 'warrant':
								echo "доверенности ".htmlspecialchars($jurPerson->director_source_info,ENT_COMPAT,'utf-8',true);
							break;
						case 'order':
								echo "приказа ".htmlspecialchars($jurPerson->director_source_info,ENT_COMPAT,'utf-8',true);
							break;
						case 'protocol':
								echo "протокола ".htmlspecialchars($jurPerson->director_source_info,ENT_COMPAT,'utf-8',true);
							break;
					}
					?>)</td>
			</tr>
			<tr>
				<td valign="top">Юридический адрес:</td><td><?php echo nl2br(htmlspecialchars($jurPerson->address,ENT_COMPAT,'utf-8',true))?></td>
			</tr>
			<tr>
				<td valign="top">Фактический адрес:</td><td><?php echo nl2br(htmlspecialchars($jurPerson->real_address,ENT_COMPAT,'utf-8',true))?></td>
			</tr>
			<tr>
				<td valign="top">Название банка:</td><td><?php echo htmlspecialchars($jurPerson->bank_title,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
			<tr>
				<td valign="top">КПП:</td><td><?php echo htmlspecialchars($jurPerson->kpp,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
			<tr>
				<td valign="top">ИНН:</td><td><?php echo htmlspecialchars($jurPerson->inn,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
			<tr>
				<td valign="top">К/С:</td><td><?php echo htmlspecialchars($jurPerson->correspondent_account,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
			<tr>
				<td valign="top">Р/С:</td><td><?php echo htmlspecialchars($jurPerson->settlement_account,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
			<tr>
				<td valign="top">БИК:</td><td><?php echo htmlspecialchars($jurPerson->bank_bik,ENT_COMPAT,'utf-8',true)?></td>
			</tr>
		</table>
		<hr>
		Письмо сформированно автоматически, при помощи Системы Управления Проектами Фабрики Сайтов
	</body>
</html>