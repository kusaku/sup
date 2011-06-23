<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$people = People::getById(11954);
$cont = $people->contacts;
foreach ($cont as $value) {
	print $value->fio.'<br>';
}

/*
$sites = People::getById(11954)->my_sites;
foreach ($sites as $site) {
	print $site->url.'<br>';
	$packages = $site->package;
	foreach ($packages as $package) {
		print '&nbsp;&nbsp;';
		print $package->id.'<br>';
		$uslugi = $package->servPack;
		foreach ($uslugi as $usluga) {
			print '&nbsp;&nbsp;&nbsp;&nbsp;';
			print $usluga->service->name.'<br>';
		}
	}
}

print Site::getTypeById(764);
*/

//print_r($a);
?>
