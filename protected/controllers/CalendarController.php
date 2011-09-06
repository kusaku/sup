<?php 
class CalendarController extends Controller {

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
					'index',
					'save',
					'view',
					'ready'
				),
				'roles'=>array(
					'admin',
					'moder',
					'topmanager',
					'manager',
					'master'
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
		if (Yii::app()->user->id) {
			$calendar = Calendar::getAllActual();
			foreach ($calendar as $event) {
				$this->renderPartial('event', array(
					'event'=>$event
				));
			}
		}
	}
	
	/**
	 *	Сохраняем новое событие в календаре.
	 * @param array $data - именованный массив, содержащий информацию о добавляемом событии.
	 * @return integet ID добавленного события
	 */
	public function actionSave($data = null) {
		if ($data == null) {
			$data = $_POST;
		}
		if (isset($data['event_id'])) {
			if ($data['event_id'])
				$event = Calendar::GetById($data['event_id']);
			else
				$event = new Calendar();
				
			$event->people_id = $data['people_id'] ? $data['people_id'] : Yii::app()->user->id;
			$event->date = date('Y-m-d', strtotime($data['date']));
			$event->message = $data['message'];
			$event->interval = (int) $data['interval'];
			$event->status = 1;
			
			$event->save();
			if ($data['NOredirect'])
				return 1;
			else
				$this->redirect(Yii::app()->homeUrl);
				
		} else
			return 0;
	}
	
	public function actionView() {
		$id = Yii::app()->request->getParam('id');
		
		if ($id) // Если передан нулевой ID, создаём новый event
			$event = Calendar::getById($id);
		else {
			$event = new Calendar();
			$event->date = date("Y-m-d");
			$event->status = 1;
			$event->people_id = Yii::app()->user->id;
		}

		
		$this->renderPartial('view', array(
			'event'=>$event
		));
	}
	
	public function actionReady() {
	
		if (Yii::app()->request->getParam('id')) {
			$id = Yii::app()->request->getParam('id');
			if ($id) {
				$event = Calendar::GetById($id);
				$event->status = 0;
				$event->save();
				if ($event->interval > 0) {
					self::actionSave(array(
						//
						'event_id'=>0,
						//
						'people_id'=>$event->people_id,
						//
						'date'=>date('Y-m-d', 
						//
						(int) strtotime($event->date." + {$event->interval} month")),
						//
						'message'=>$event->message,
						//
						'interval'=>$event->interval,
						//
						'NOredirect'=>true,
					));
				}
				print 1;
			}
		}
	}
}
