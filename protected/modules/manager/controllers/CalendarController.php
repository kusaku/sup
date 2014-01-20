<?php
/**
 * Контроллер управления работой уведомлений
 */
class CalendarController extends Controller {
	/**
	 * Использовать фильтр прав доступа
	 * @return array
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
					'ready',
					'list',
					'last',
					'window',
					'delay'
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
	 * Метод выбирает список удовлетворящих запросу уведомлений из БД
	 * @param int $mode
	 * @param int $from
	 *
	 * @return array
	 */
	private function getNoticesList($mode=0,$from=0) {
		$obCriteria=new CDbCriteria();
		$obCriteria->addCondition('people_id='.Yii::app()->user->id);
		$obCriteria->order='date DESC';
		switch($mode) {
			case 1:
				$obCriteria->addCondition('status=0');
			break;
			case 2:
			break;
			default:
				$obCriteria->addCondition('status=1');
		}
		$obCriteria->addCondition("date<='".date('Y-m-d H:i:s')."'");
		$obCriteria->limit=100;
		$obCriteria->offset=$from;
		return Calendar::model()->findAll($obCriteria);
	}

	/**
	 * @param int $mode - режим выборки, 0 - только новые, 1 - только прочитанные, 2 - все
	 * @param int $from
	 */
	public function actionList($mode=0,$from=0) {
		$arList=$this->getNoticesList($mode,$from);
		$this->renderPartial('list',array('list'=>$arList));
	}

	/**
	 * Action выбирает из базы 2 самых последних актуальных уведомления
	 * @param string $hash
	 *
	 * @throws CHttpException
	 */
	public function actionLast($hash='') {
		if(Yii::app()->request->isAjaxRequest) {
			$obCriteria=new CDbCriteria();
			$obCriteria->addCondition('people_id='.Yii::app()->user->id);
			$obCriteria->addCondition("date<='".date('Y-m-d H:i:s')."'");
			$obCriteria->order='status DESC, date DESC';
			$obCriteria->limit=2;
			$arNotices=Calendar::model()->findAll($obCriteria);
			$sHash='';
			if(is_array($arNotices) && count($arNotices)>0) {
				foreach($arNotices as $obNotice) {
					$sHash.=$obNotice->id.'|';
				}
			}
			if($sHash!=$hash) {
				$arResult=array(
					'html'=>$this->widget('manager.widgets.LastNoticeListWidget',array('list'=>$arNotices),true),
					'hash'=>$sHash
				);
			} else {
				$arResult=array();
			}
			echo json_encode($arResult);
		} else {
			throw new CHttpException(400,'Wrong request');
		}
	}

	/**
	 * Action подготавливает данные для вывода окна с фильтром уведомлений по состояниям
	 */
	public function actionWindow() {
		$sQuery="SELECT status, count(id) as cnt FROM ".Calendar::model()->tableName().' WHERE people_id='.Yii::app()->user->id." AND date<='".date('Y-m-d H:i:s')."' GROUP BY status ASC";
		$arResult=Yii::app()->db->createCommand($sQuery)->queryAll();
		if(!isset($arResult[1])) {
			$arResult[1]=array('cnt'=>0);
		}
		if(!isset($arResult[0])) {
			$arResult[0]=array('cnt'=>0);
		}
		$arList=$this->getNoticesList(0,0);
		$this->renderPartial('window',array('statuses'=>$arResult,'list'=>$arList));

	}

	/**
	 * Сохраняем формы редактирования событий
	 */
	public function actionSave() {
		if(Yii::app()->request->isPostRequest && isset($_POST['Calendar'])) {
			if($_POST['Calendar']['id']>0) {
				$event=Calendar::getById($_POST['Calendar']['id']);
				if(is_null($event)) {
					throw new CHttpException(404,'Calendar event not found');
				}
			} else {
				$event=new Calendar();
				$event->people_id=Yii::app()->user->id;
				$event->status = 1;
			}
			$event->date = date('Y-m-d H:i:s', strtotime($_POST['Calendar']['date']));
			$event->message = $_POST['Calendar']['message'];
			$event->interval = (int) $_POST['Calendar']['interval'];
			$arResult=array();
			$arResult['save']=$event->save()?'ok':'';
			$arResult['event']=$event;
			$this->renderPartial('view', $arResult);
		} else {
			throw new CHttpException(400,'Wrong request');
		}
	}

	/**
	 * Action выполняемый при отрисовке подробной информации и формы редактирования события календаря
	 * @param integer $id
	 *
	 * @throws CHttpException
	 */
	public function actionView($id) {
		if ($id>0) {
			$event = Calendar::getById($id);
			if(is_null($event)) {
				throw new CHttpException(404,'Calendar event not found');
			}
			if($event->people_id!=Yii::app()->user->id) {
				throw new CHttpException(403,'Event access denied');
			}
		} else {
			$event = new Calendar();
			$event->date = date("Y-m-d H:i:s");
			$event->status = 1;
			$event->people_id = Yii::app()->user->id;
		}
		$this->renderPartial('view', array('event'=>$event));
	}

	/**
	 * Action выполняет подтверждение того, что уведомление прочитано и закрыто
	 */
	public function actionReady($id) {
		if ($id>0) {
			$event = Calendar::GetById($id);
			if(is_null($event)) {
				throw new CHttpException(404,'Calendar event not found');
			}
			$event->status = 0;
			$event->save();
			if ($event->interval > 0) {
				$new=new Calendar();
				$new->people_id=$event->people_id;
				$new->date=date('Y-m-d H:i:s', strtotime($event->date." + {$event->interval} month"));
				$new->message=$event->message;
				$new->interval=$event->interval;
			}
			print 1;
		} else {
			throw new CHttpException(400,'Wrong request');
		}
	}

	public function actionDelay($id) {
		if ($id>0) {
			$event = Calendar::GetById($id);
			if(is_null($event)) {
				throw new CHttpException(404,'Calendar event not found');
			}
			if(isset($_REQUEST['days']) && $_REQUEST['days']>0) {
				$iPossibleDate=strtotime($event->date." + {$_REQUEST['days']} days");
				$iFromToday=strtotime(date('Y-m-d H:i:s')." + {$_REQUEST['days']} days");
			} elseif(isset($_REQUEST['minutes']) && $_REQUEST['minutes']>0) {
				$iPossibleDate=strtotime($event->date." + {$_REQUEST['minutes']} minutes");
				$iFromToday=strtotime(date('Y-m-d H:i:s')." + {$_REQUEST['minutes']} minutes");
			}
			$event->date=date('Y-m-d H:i:s', max($iPossibleDate,$iFromToday));
			if(strtotime($event->date)>time()) {
				$event->status=1;
			}
			$event->save();
			print 1;
		} else {
			throw new CHttpException(400,'Wrong request');
		}
	}

	public function getIntervals() {
		return array(
			'0'=>'Не повторять',
			'1'=>'1 мес.',
			'3'=>'3 мес.',
			'6'=>'6 мес.',
			'12'=>'12 мес.',
		);
	}
}
