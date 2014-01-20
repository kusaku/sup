<?php
class WaveMessagesController extends Controller {

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
				'allow', 'roles'=>array('admin')
			),
			array(
				'deny', 'users'=>array('*')
			)
		);
	}

	public function actionIndex() {
		$obModel=WaveMessageTemplates::model();
		$arMessages=$obModel->findAllByAttributes(array('common'=>1));
		$this->render('index',array('models'=>$arMessages));
	}

	public function actionEdit($id=0) {
		if($id==0) {
			$obMessage=new WaveMessageTemplates();
		} else {
			$obMessage=WaveMessageTemplates::model()->findByPk($id);
		}
		if(!$obMessage) {
			throw new CHttpException(404,'Message template not found',404);
		}
		if(Yii::app()->getRequest()->isPostRequest && isset($_POST['WaveMessageTemplates'])) {
			$obMessage->attributes=$_POST['WaveMessageTemplates'];
			$obMessage->common=1;
			$obMessage->people_id=0;
			if($obMessage->validate() && $obMessage->save()) {
				$this->redirect($this->createUrl('waveMessages/index'));
			}
		}
		$this->render('edit',array('model'=>$obMessage));
	}

	public function actionDelete($id) {
		$obMessage=WaveMessageTemplates::model()->findByPk($id);
		if(!$obMessage) {
			throw new CHttpException(404,'Message template not found',404);
		}
		$obMessage->delete();
		$this->redirect($this->createUrl('waveMessages/index'));
	}
}
