<?php 
if (file_exists('closed.php')) {
	include 'closed.php';
} else {
	switch (TRUE) {
		// development environment
		case stristr($_SERVER['HTTP_HOST'], 'sup.fsdev.loc'):
			define('YII_FRAMEWORK', dirname(__FILE__).'/framework/yii.php');
			define('YII_CONFIG', dirname(__FILE__).'/protected/config/dev_main.php');
			define('YII_DEBUG', true);
			define('YII_TRACE_LEVEL', 3);
			break;
		case stristr($_SERVER['HTTP_HOST'], '.aks'):
		case stristr($_SERVER['SERVER_ADDR'], '192.168.0.235'):
			define('YII_FRAMEWORK', dirname(__FILE__).'/../framework/yii.php');
			define('YII_CONFIG', dirname(__FILE__).'/../protected/config/dev_aks.php');
			define('YII_DEBUG', true);
			define('YII_TRACE_LEVEL', 0);
			break;
		case stristr($_SERVER['HTTP_HOST'], '.anton'):
		case stristr($_SERVER['SERVER_ADDR'], '192.168.0.244'):
			define('YII_FRAMEWORK', dirname(__FILE__).'/../framework/yii.php');
			define('YII_CONFIG', dirname(__FILE__).'/../protected/config/dev_anton.php');
			define('YII_DEBUG', true);
			define('YII_TRACE_LEVEL', 3);
			break;
		case stristr($_SERVER['HTTP_HOST'], '.blade'):
		case stristr($_SERVER['SERVER_ADDR'], '192.168.0.242'):
			define('YII_FRAMEWORK', dirname(__FILE__).'/../framework/yii.php');
			define('YII_CONFIG', dirname(__FILE__).'/../protected/config/dev_blade39.php');
			define('YII_DEBUG', true);
			define('YII_TRACE_LEVEL', 3);
			break;
		// production environment
		default:
			define('YII_FRAMEWORK', dirname(__FILE__).'/../framework/yii.php.lite');
			define('YII_CONFIG', dirname(__FILE__).'/../protected/config/prod_main.php');
			define('YII_DEBUG', false);
			define('YII_TRACE_LEVEL', 0);
			break;
	}
	require_once (YII_FRAMEWORK);
	Yii::createWebApplication(YII_CONFIG)->run();
}
