<?php 
return array(
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
		'version'=>'1.2.3-2'
	),

	// конфигурация внешних сервисов
	'params'=>array(
		// ldap, на продуктиве свой
		'ldapConfig'=>array(
			'server'=>'ldap://ldap2.fabricasaitov.ru',
			//
			'domain'=>'fabrica.local',
			//
			'base_dn'=>'dc=fabrica,dc=local',
		),
		//Настройки приложения SUP в отношении SUP API (позднее переделать)
		'apiConfig'=>array(
			'id'=>0,
			'title'=>'sup',
			'code_hash'=>'',
			'date_add'=>'2012-05-14 14:38:41',
			'date_edit'=>'2012-05-14 14:38:41',
			'active'=>1,
			'url'=>'http://sup.fabricasaitov.ru/',
			'login_field'=>'login',
			'new_people_pgroup_id'=>7,
			'new_people_auth_item'=>'Client'
		),
		// redmine, на продуктиве свой
		'redmineConfig'=>array(
			'url'=>'https://redmine.fabricasaitov.ru:443',
			//
			'login'=>'sup',
			//
			'password'=>'ekISozOrs9ixcLRt',
			//
			'token'=>'a9e90cd08c6745028dec89e805b955b545fa0d16',
			//
			'defaulProject'=>'sites',
			//
			'assignTo'=>array(
				'Администратор'=>'sites','Модератор'=>'sites','Старший менеджер'=>'sites','Менеджер'=>'sites',
					'Мастер'=>'sites','Партнер'=>'sites','Клиент'=>'sites','Ведущий мастер'=>'sites',
					'Удаленный мастер'=>'sites','Супер партнер'=>'sites','Бывший сотрудник'=>'sites',
					'Маркетолог'=>'sites','Бухгалтер'=>'sites',
			)
		),
		// billmanager
		'bmConfig'=>array(
			// api биллинга
			'bm_url'=>'https://host.fabricasaitov.ru/manager/billmgr',
			//
			'bm_login'=>'apimanager',
			//
			'bm_password'=>'UYmWGA9v',
			// api хостинга
			'isp_url'=>'https://host.fabricasaitov.ru/manager/ispmgr',
			//
			'isp_login'=>'apsadmin',
			//
			'isp_password'=>'rg1KD4AQLeaLTTWB',
		),
		//
		'pdfGen'=>'mPDF',
		// phpmailer
		'PHPMailer'=>array(
			'method'=>'sendmail',
			//
			//'host'=>'mail.fabricasaitov.ru',
			//
			//'user'=>false,
		),
	),
	
	// preloading
	'preload'=>array(
		// users
		'authManager',
		// persistent stotage (registry, etc)
		'persistent',
		'log'
	),
	
	// load registry on load
	'onBeginRequest'=>array(
		'Registry','registryLoad'
	),
	// save registry on exit
	'onEndRequest'=>array(
		'Registry','registrySave'
	),
	
	// autoloading model and component classes
	'import'=>array(
		// all models
		'application.models.*',
		'application.models.isp.*',
		'application.models.BillManager.*',
		'application.models.reports.*',
		// all components
		'application.components.*',
		'application.components.dss.*',
		'application.components.isp.*',
		'application.components.events.*'
	),
	'modules'=>array(
		'manager','admin',
		'api'=>array(
			'logRequests'=>true,
		),
		'documents',
	),
	
	//
	'defaultController'=>'default',
	'sourceLanguage' => 'en_us',
	'language'=>'ru',
	// application components
	'components'=>array(
		'dbUpdate'=>array(
			'class'=>'FSDbUpdate',
		),
		//
		'documents'=>array(
			'class'=>'FSDocumentsAPI'
		),
		//
		'user'=>array(
			//
			'allowAutoLogin'=>true,
			//
			'loginUrl'=>array(
				'default/login'
			),
		),
		//
		'authManager'=>array(
			'class'=>'AuthManager',
			//
			'defaultRoles'=>array(
				'guest'
			),
		),
		
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path','rules'=>array(
				//
				'<module:\w+>/<controller:\w+>/<id:\d+>'=>'<module>/<controller>/view',
				//
				'<module:\w+>/<controller:\w+>/<action:\w+>/<id:\w+>'=>'<module>/<controller>/<action>',
				//
				'<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
				//
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				//
				'<controller:\w+>/<action:\w+>/<id:\w+>'=>'<controller>/<action>',
				//
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),'showScriptName'=>false,
		),
		
		// database settings
		'db'=>array(
			//
			'connectionString'=>'mysql:host=localhost;dbname=sup',
			//
			'username'=>'root',
			//
			'password'=>'nxnj5AOs',
			//
			'charset'=>'utf8',
			//
			//'emulatePrepare'=>true,
			//
			//'enableProfiling'=>true,
			//
			//'enableParamLogging'=>true,
			//
			//'tablePrefix'=>'',
		),
		'billManager'=>array(
			'class'=>'ISPConnection',
			// api биллинга
			'bm_url'=>'https://host.fabricasaitov.ru/manager/billmgr',
			//
			'bm_login'=>'apimanager',
			//
			'bm_password'=>'nhnpddgJ',
			//'bm_password'=>'wrong one',
		),
		
		//
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'/error',
		),
		// debug
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				'CFileLogRoute'=>array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		// JPhpMailer Extension
		'JPhpMailer'=>array(
			'class'=>'application.extensions.phpmailer.JPhpMailer',
		),
		// Yii-PDF Extension
		'ePdf'=>array(
			'class'=>'application.extensions.yii-pdf.EYiiPdf',
			//
			'params'=>array(
				'mpdf'=>array(
					'librarySourcePath'=>'application.vendors.mpdf.*',
					//
					'classFile'=>'mpdf.php','constants'=>array(
						'_MPDF_TEMP_PATH'=>Yii::getPathOfAlias('application.runtime'),
					),
					// the literal class filename to be loaded from the vendors folder
					'class'=>'mpdf',
					// More info: http://mpdf1.com/manual/index.php?tid=184
					'defaultParams'=>array(
						// This parameter specifies the mode of the new document.
						'mode'=>'',
						// format A4, A5, ...
						'format'=>'A4',
						// Sets the default document font size in points (pt)
						'default_font_size'=>0,
						// Sets the default font-family for the new document.
						'default_font'=>'',
						// margin_left. Sets the page margins for the new document.
						'mgl'=>15,
						// margin_right
						'mgr'=>15,
						// margin_top
						'mgt'=>16,
						// margin_bottom
						'mgb'=>16,
						// margin_header
						'mgh'=>9,
						// margin_footer
						'mgf'=>9,
						// landscape or portrait orientation
						'orientation'=>'P',
					),
				),
			),
		),
	),
);
