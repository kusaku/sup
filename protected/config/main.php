<?php 
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	//
	'defaultController'=>'app',
	//
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	//
	'name'=>array(
		//
		'name'=>'СУП - Система Управления Проектами',
		//
		'shortName'=>'СУП',
		//
		'vendor'=>'ООО "Фабрика сайтов"',
		//
		'version'=>'1.08.24'
	),
	
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// конфиг редмайна. на продуктиве, естественно, свой.
		'redmineConfig'=>array(
			'defaulProject' => 'dev',
			'enabled' => true,
			//'proxy' => '192.168.0.254:3128',
			//'protocol' => 'https',
			'protocol' => 'http',
			//'port' => '443',
			'port' => '80',
			'url' => 'redmine.sandbox.loc',
			'rootLogin' => 'dmitry.k',
			'rootPassword' => 'Ij3Ohmee'
		),
	),
	
	// preloading
	'preload'=>array(
		// debug
		// 'log',
		// users
		'authManager',
		// нужная модель
		'persistent'
	),
	
	// load registry on load
	'onBeginRequest'=>array(
		'Registry', 'registryLoad'
	),
	// save registry on exit
	'onEndRequest'=>array(
		'Registry', 'registrySave'
	),
	
	// autoloading model and component classes
	'import'=>array(
		// all models
		'application.models.*',
		// all components
		'application.components.*',
		// debug
		//'application.extensions.yiiDebug.*',
	),
	
	// modules
	'modules'=>array(
		// uncomment the following to enable the Gii tool
		//		'gii'=>array(
		//			'class'=>'system.gii.GiiModule',
		//			'password'=>'Enter Your Password Here',
		//			// If removed, Gii defaults to localhost only. Edit carefully to taste.
		//			'ipFilters'=>array('127.0.0.1','::1'),
		//		),
		
	),
	
	// application components
	'components'=>array(
	
		'user'=>array(
			//
			'allowAutoLogin'=>true,
			//
			'loginUrl'=>array(
				'/app/login'
			),
		), 'authManager'=>array(
			'class'=>'AuthManager',
			//
			'defaultRoles'=>array(
				'guest'
			),
		),
		
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path', 'rules'=>array(
				//
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				//
				'<controller:\w+>/<action:\w+>/<id:\w+>'=>'<controller>/<action>',
				//
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
			//
			'showScriptName'=>false,
		),
		
		// database settings
		'db'=>array(
			//
			'connectionString'=>'mysql:host=localhost;dbname=sup',
			//
			'username'=>'root',
			//
			'password'=>'root',
			//
			'charset'=>'utf8',
			//
			//			'emulatePrepare'=>true,
			//
			//			'enableProfiling'=>true,
			//
			//			'enableParamLogging'=>true,
		),
		//
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'app/error',
		),
		// debug
		'log'=>array(
			'class'=>'CLogRouter', 'routes'=>array(
				//				array(
				//					//
				//					'class'=>'CProfileLogRoute',
				//					//
				//					'levels'=>'profile',
				//					//
				//					'enabled'=>true,
				//				),
				//
				array(
					//
					'class'=>'CWebLogRoute',
					//
					'levels'=>'error, warning, trace, profile, info',
					//
					'enabled'=>true,
				),
			),
		),
	),
);
