<?php
return CMap::mergeArray(require(dirname(__FILE__).'/prod_main.php'),array(
	'name'=>array(
		'name'=>'СУП - Система Управления Проектами (dev)',
		'version'=>'1.19'
	),
	'params'=>array(
		'ldapConfig'=>array(
			'server'=>'ldap://192.168.0.1',
		),
		//Настройки приложения SUP в отношении SUP API (позднее переделать)
		'apiConfig'=>array(
			'url'=>'http://sup.fsdev.loc/',
		),
		// redmine, на продуктиве свой
		'redmineConfig'=>array(
			'url'=>'http://redmine.sandbox.loc:80',
			'login'=>'sup',
			'password'=>'12345678',
			'defaulProject'=>'dev',
			'assignTo'=>array(
				'Администратор'=>'dev','Модератор'=>'dev','Старший менеджер'=>'dev','Менеджер'=>'dev',
					'Мастер'=>'dev','Партнер'=>'dev','Клиент'=>'dev','Ведущий мастер'=>'dev','Удаленный мастер'=>'dev',
					'Супер партнер'=>'dev','Бывший сотрудник'=>'dev','Маркетолог'=>'dev','Бухгалтер'=>'dev',
			)
		),
	),
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=newsup_text',
			'username'=>'root',
			'password'=>'123',
			'charset'=>'utf8',
			'emulatePrepare'=>true,
			'enableProfiling'=>false,
			'enableParamLogging'=>true,
			'tablePrefix'=>'',
			//Кэш схемы базы данных
			'schemaCachingDuration'=>300
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				'CFileLogRoute'=>array(
					'enabled'=>false,
				),
				'CProfileLogRoute' => array(
					//Профилирование БД
					'class'=>'CProfileLogRoute',
					'levels'=>'profile',
					'enabled'=>false,
				),
				'CWebLogRoute' => array(
					//Вывод лога всего в интерфейс
					'class'=>'CWebLogRoute',
					'levels'=>'error, warning, trace, profile, info',
					'enabled'=>false,
				),
			),
		),
	),
));
