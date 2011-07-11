<?php


$package = Package::getById( 11230 );

$usersArray = Redmine::getUsersArray();
//print_r($package->servPack[2]->master->login);

print_r ($usersArray);
print '<br><br>';
foreach ($package->servPack as $key=>$service) {
	print_r(array(
		//md5($service->master->login),
		$usersArray[ trim( (string)$service->master->login ) ],
		//array_key_exists((string)$service->master->login,$usersArray),
		'"'.(string)$service->master->login.'"'
		));	// Родительская задача
print '<br>';

}/**/


?>