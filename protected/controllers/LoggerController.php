<?php 
class LoggerController extends Controller {

	/**
	 * @return
	 */
	public function actionIndex($client_id) {
		$this->renderPartial('putform', array('client_id'=>$client_id));
		
	}
	
	/**
	 *
	 * @param object $client_ud
	 * @param object $info
	 * @return
	 */
	public function actionPut() {
		$data = array_merge(array('manager_id'=>Yii::app()->user->id), $_POST);
		$success = Logger::put($data);
		print( exit(json_encode(array('success'=>$success))));
	}
}
