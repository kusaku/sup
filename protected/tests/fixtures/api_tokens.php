<?php
return array(
	'activeToken'=>array(
		'id'=>'1',
		'token'=>md5(123456),
		'application_id'=>1,
		'date_add'=>date('Y-m-d H:i:s',time()-86400),
		'date_expire'=>date('Y-m-d H:i:s',time()+86400),
		'previous_token_id'=>0,
		'user_id'=>0,
		'expired'=>0,
	),
);