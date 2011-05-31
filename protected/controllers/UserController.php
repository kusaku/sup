<?php

class UserController extends Controller
{
	public function actionGet()
	{
		//print json_encode( People::getById(Yii::app()->request->getParam('id')) );
		$this->renderPartial('index');
	}

}