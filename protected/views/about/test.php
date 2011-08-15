<h1>TEST</h1>
<?php

$config = Yii::app()->params['RedmineConfig'];
print_r ($config);

var_dump( Redmine::getIssues() );

print Yii::app()->user->login.":".Yii::app()->user->key;

?>