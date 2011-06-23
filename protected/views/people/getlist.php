<?php
/*
	Универсальный вывод списка людей в селект.
	Написан, но пока в общем-то ни где не используется
*/
print '<select id="people_select" style="width: 500px;">';
	foreach ($peoples as $people)
	{

		print "<option value='".$people->id."'>";
		print $people->fio;
		print "</option>\n";

	}
print "</select>\n";

?>
