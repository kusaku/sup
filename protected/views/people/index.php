<?php
/*
	Вывод полного списка людей из БД
*/
print "<a href='/people/0'>Новый клиент</a><br><br>\n";

print "<table>\n";

	print "<tr>";
	print '<th>ID</th>';
	print '<th>ФИО</th>';
	print '<th>Email</th>';
	print '<th>Login</th>';
	print '<th>Телефон</th>';
	print "</tr>\n";

	foreach ($peoples as $people)
	{

		print "<tr>";
		print '<td>'.$people->id.'</td>';
		print '<td><a href="/people/'.$people->id.'">'.$people->fio.'</td>';
		print '<td>'.$people->mail.'</td>';
		print '<td>'.$people->login.'</td>';
		print '<td>'.$people->phone.'</td>';
		print "</tr>\n";

	}
print "</table>\n";


?>