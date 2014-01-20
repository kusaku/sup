<?php
/**
 * Класс реализует API системы SUP
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 11.05.12
 */

class ApiModule extends CWebModule {
	const PROTOCOL_VERSION='0.5';
	public $defaultController = 'default';
	public $APIActions = array();
	public $MailParams = array();
	public $tokenLifeTime=0;
	public $logRequests = false;
	public $log=null;
	private $obApplicationUser = false;

	/**
	 * Initializes the module.
	 */
	public function init() {
		// import the module-level models and components
		$this->setImport(array(
			// models
			$this->getId().'.models.*',
			// components
			$this->getId().'.components.*',
			//special
			$this->getId().'.controllers.supuser.*',
			//for reports inheritance
			$this->getId().'.controllers.report.ReportPartnerManagerPackagesAction',
		));
	}

	/**
	 * Метод уменьшает первую букву
	 */
	private function lcfirst($str) {
		$sFirst=strtolower(substr($str,0,1));
		return $sFirst.substr($str,1);
	}

	/**
	 * Метод используется для фильтрации путей к действиям
	 */
	private function _walkFilePath($sPath) {
		$sPath=str_replace(array(Yii::getPathOfAlias('api.controllers').DIRECTORY_SEPARATOR,'Action.php'),'',$sPath);
		if(strpos($sPath,'Controller.php')===false) {
			$iSlashPos=strpos($sPath,DIRECTORY_SEPARATOR);
			$sAction=$this->lcfirst(substr($sPath,$iSlashPos+1));
			$sDirectory=substr($sPath,0,$iSlashPos);
			$this->APIActions[$sAction]=$sDirectory.'/'.$sAction;
		}
	}

	/**
	 * Метод вызывается до инициализации модуля и расставляет настройки по умолчанию
	 */
	public function preinit() {
		$arControllers=CFileHelper::findFiles(Yii::getPathOfAlias('api.controllers'),array('fileTypes'=>array('php'),'exclude'=>array('Controller.php','/apierror/'),'level'=>1));
		array_walk($arControllers,array($this,'_walkFilePath'));
		$this->MailParams=array(
			'emailFrom'=>'cabinet@fabricasaitov.ru',
			'emailNameFrom'=>'Компания Фабрика сайтов',
		);
		$this->tokenLifeTime=864000;
		$this->components=array(
			'applicationAuth'=>array(
	        	'class'=>'CDbAuthManager',
	        	'connectionID'=>'db', // db connection as above
	        	'itemTable'=>'api_auth_item',
	        	'itemChildTable'=>'api_auth_item_child',
	        	'assignmentTable'=>'api_auth_assignment',
	        	'showErrors'=>true, // show eval()-errors in buisnessRules
			),
			'userAuth'=>array(
				'class'=>'CDbAuthManager',
	        	'connectionID'=>'db', // db connection as above
	        	'itemTable'=>'api_auth_item',
	        	'itemChildTable'=>'api_auth_item_child',
	        	'assignmentTable'=>'api_auth_user_assignment',
	        	'showErrors'=>true, // show eval()-errors in buisnessRules
			),
			'applicationTokens'=>array(
				'class'=>'ApiTokens'
			)
		);
	}

	/**
	 * Метод выполняет создание объекта авторизации приложения
	 */
	public function getApplicationAuth() {
		return $this->getComponent('applicationAuth');
	}

	/**
	 * Метод возвращает объект управления пользовательским доступом
	 */
	public function getUserAuth() {
		return $this->getComponent('userAuth');
	}

	/**
	 * Метод возвращает объект управления токенами приложений
	 */
	public function getApplicationTokens() {
		return $this->getComponent('applicationTokens');
	}

	/**
	 * Метод возвращает "учётку" текущего приложения
	 */
	public function getApplicationUser() {
		if(!$this->obApplicationUser)
			$this->obApplicationUser=$this->_initApplicationUser();
		return $this->obApplicationUser;
	}

	/**
	 * Метод выполняет создание объекта CApplicationAuth в качестве пользователя API
	 */
	private function _initApplicationUser() {
		try {
			$obSession=$this->getApplicationTokens();
			if($obSession->getIsStarted()) {
				$obApplication=$obSession->getApplication();
				return new ApiApplication($obApplication->id,$obApplication->title);
			}
			throw new CHttpException('504','Auth required','504');
		} catch (CException $e) {
			return new ApiApplication();
		}
	}

	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action and all its filters
	 * are executed. You may override this method in the following way:
	 * <pre>
	 * if(parent::beforeControllerAction($controller,$action))
	 * {
	 *     // your code
	 *     return true;
	 * }
	 * else
	 *     return false;
	 * </pre>
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @return boolean whether the action should be executed.
	 * @since 1.0.4
	 */

	public function beforeControllerAction($controller, $action) {
		if (($parent = $this->getParentModule()) === null)
			$parent = Yii::app();
		return $parent->beforeControllerAction($controller, $action);
	}

	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action and all its filters
	 * are executed. If you override this method, make sure you call the parent implementation at the end.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @since 1.0.4
	 */

	public function afterControllerAction($controller, $action) {
		if (($parent = $this->getParentModule()) === null)
			$parent = Yii::app();
		$parent->afterControllerAction($controller, $action);
	}
}