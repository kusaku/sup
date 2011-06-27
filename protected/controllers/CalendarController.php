<?php

class CalendarController extends Controller
{

	public function actionIndex()
	{
		if ( Yii::app()->user->id )
		{
			$calendar = Calendar::getAllActual();

			foreach ($calendar as $event) {
				$this->renderPartial('event',array('event'=>$event));
			}
		}
	}
}