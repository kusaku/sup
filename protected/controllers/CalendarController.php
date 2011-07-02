<?php

class CalendarController extends Controller
{

	public function actionIndex()
	{
		if ( Yii::app()->user->id ) {
			$calendar = Calendar::getAllActual();
			foreach ($calendar as $event) {
				$this->renderPartial('event',array('event'=>$event));
			}
		}
	}

	/**
	 *	Сохраняем новое событие в календаре.
	 * @param array $data - именованный массив, содержащий информацию о добавляемом событии.
	 * @return integet ID добавленного события
	 */
	public function actionSave($data = null){
		if ( $data == null ){
			$data = $_POST;
		}
		if ( isset ($data['event_id']) ){
			if ( $data['event_id'] ) $event = Calendar::GetById($data['event_id']);
			else $event = new Calendar();

			$event->people_id = $data['people_id'] ? $data['people_id'] : Yii::app()->user->id;
			$event->date = date('Y-m-d',strtotime($data['date']));
			//$event->date = $data['date'];
			$event->message = $data['message'];
			$event->status = 1;

			$event->save();
			$this->redirect(Yii::app()->homeUrl);
		}
		else return 0;
	}

	public function actionView()
	{
		$id = Yii::app()->request->getParam('id');

		if ( $id ) // Если передан нулевой ID, создаём новый event
			$event = Calendar::getById( $id );
		else{
			$event = new Calendar();
			$event->date = date("Y-m-d");
			$event->status = 1;
			$event->people_id = Yii::app()->user->id;
		}


		$this->renderPartial( 'view', array('event'=>$event) );
	}

	public function actionReady(){

		if ( Yii::app()->request->getParam('id')  ){
			$id = Yii::app()->request->getParam('id');
			if ( $id ) {
				$event = Calendar::GetById( $id );
				$event->status = 0;
				$event->save();
				print 1;
			}
		}
	}
}