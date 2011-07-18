<?php


$peoples = PeopleGroup::getById(5)->peoples;

$usersArray = Redmine::getUsersArray();


foreach ($peoples as $people) {
	print_r(array(
		$people->login,
		$usersArray[ trim( (string)$people->login ) ],
		));	// Родительская задача
print '<br>';

}/**/


?>