<?php 
class DefaultController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */
	public function filters() {
		return array(
			'accessControl'
		);
	}
	
	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */
	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow',
				'actions'=>array(
					'index'
				),
				'roles'=>array(
					'admin',
				'moder',
				'topmanager',
				'manager',
				'master',
				'partner',
				'client',
				'leadmaster',
				'remotemaster',
				'superpartner',
				'marketolog'
				),
				
			),
				array(
				'allow',
				'actions'=>array(
					'logout'
				),
				'roles'=>array(
					'authenticated'
				),
				
			),
				array(
				'allow',
				'actions'=>array(
					'login'
				),
				'roles'=>array(
					'guest'
				),
				
			),
				array(
				'allow',
				'actions'=>array(
					'error'
				),
				'users'=>array(
					'*'
				),
				
			),
				array(
				'deny',
				'users'=>array(
					'*'
				),
				
			),
		);
	}
	
	public function actionIndex() {
		switch ($authmanager = Yii::app()->getAuthManager()) {
			case $authmanager->checkAccess('admin', Yii::app()->user->getId()):
			case $authmanager->checkAccess('moder', Yii::app()->user->getId()):
			case $authmanager->checkAccess('topmanager', Yii::app()->user->getId()):
			case $authmanager->checkAccess('manager', Yii::app()->user->getId()):
			case $authmanager->checkAccess('leadmaster', Yii::app()->user->getId()):
			case $authmanager->checkAccess('master', Yii::app()->user->getId()):
			case $authmanager->checkAccess('remotemaster', Yii::app()->user->getId()):
			case $authmanager->checkAccess('marketolog', Yii::app()->user->getId()):
				$this->redirect('manager');
			break;			
			case $authmanager->checkAccess('client', Yii::app()->user->getId()):
			case $authmanager->checkAccess('partner', Yii::app()->user->getId()):
			case $authmanager->checkAccess('superpartner', Yii::app()->user->getId()):
				$this->redirect('client');
			break;
			case $authmanager->checkAccess('guest', Yii::app()->user->getId()):
				$this->redirect('/');
			break;			
		}
		throw new CHttpException(403, 'Unauthorized');
	}
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
	
	/**
	 * Displays the login page
	 */
	public function actionLogin() {
		$model = new LoginForm;
		
		if (isset($_POST['ajax']) and $_POST['ajax'] === 'login-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if (isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate() and $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		
		$this->renderPartial('login', array(
			'model'=>$model
		));
	}
	
	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
