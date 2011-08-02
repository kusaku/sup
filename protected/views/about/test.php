<?php
$package = Package::getById(11199);
foreach ($package->payments as $value) {
	print $value->name.'<br>';
}
print '<br><hr>';
$people = People::getById(5582);
print $people->fio.'<br>';
print $people->mail.'<br><br>';
if ($people->rekviz)
foreach ($people->rekviz as $rekviz){
	print $rekviz->id.' '.$rekviz->val.'<br>';
}

?>