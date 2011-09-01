<?php 
class AppController extends Controller {

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
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner');
		return array(
			array(
				'allow', 'actions'=>array(
					'index'
				), 'roles'=>array(
					'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner'
				),
			), array(
				'allow', 'actions'=>array(
					'logout'
				), 'roles'=>array(
					'authenticated'
				),
			), array(
				'allow', 'actions'=>array(
					'login'
				), 'roles'=>array(
					'guest'
				),
			), array(
				'allow', 'actions'=>array(
					'error'
				), 'users'=>array(
					'*'
				),
			), array(
				'deny', 'users'=>array(
					'*'
				),
			),
		);
	}
	
	public function actionIndex() {
		$this->render('index');
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
		
		// if it is ajax validation request
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		// collect user input data
		if (isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
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
