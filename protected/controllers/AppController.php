<?php

class AppController extends Controller
{

	public function actionIndex()	
	{
		
		if ( Yii::app()->user->isGuest )
			$this->redirect('/app/login'); // Незалогиненным тут не место!
		else
			$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if ( $error=Yii::app()->errorHandler->error )
	    {
	    	if( Yii::app()->request->isAjaxRequest )
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}


	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{

		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->renderPartial('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * Обновление задач при изменениях в редмайне
	 */
	public function actionRedmineupdate(){
		$issues = Redmine::getIssues();
		//print_r($issues);
		foreach ($issues as $issue) {
			print_r($issue);
		}/**/
		return true;
	}

}