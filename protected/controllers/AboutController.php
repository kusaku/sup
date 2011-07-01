<?php

class AboutController extends Controller
{
	public function actionIndex()
	{
		$this->renderPartial('index');
	}

	public function actionTest()
	{
		$this->renderPartial('test');
	}

}