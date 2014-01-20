<?php 
class ErrorController extends Controller {

	public function actionIndex() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('//default/error', $error);
		} else {
			throw new CHttpException(500, 'Error controller ran without error');
		}
	}
	
}
