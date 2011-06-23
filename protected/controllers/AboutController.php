<?php

class AboutController extends Controller
{
	public function actionIndex()
	{
		//$_redmine = new redmine();
		print ('123adsg');
		//print_r($_redmine);
		print_r(Redmine::getIssuePercent(2948));

		//$this->renderPartial('index');
	}

	public function actionTest()
	{
		$this->renderPartial('test');
	}

}