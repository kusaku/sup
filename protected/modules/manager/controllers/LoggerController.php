<?php 
class LoggerController extends Controller {

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
					'put'
				),
				'roles'=>array(
					'admin',
					'moder',
					'topmanager',
					'manager',
					'master',
					'marketolog'
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
	
	/**
	 *
	 * @param int $client_id
	 */
	public function actionIndex($client_id) {
		$arResult=array(
			'client'=>People::getById($client_id)
		);
		if(is_null($arResult['client'])) {
			throw new CHttpException(404,'Client not found');
		}
		$this->renderPartial('putform', $arResult);
	}
	
	/**
	 *
	 */
	public function actionPut() {
		$data = array_merge(array(
			'manager_id'=>Yii::app()->user->id
		), $_POST);
		
		$success = Logger::put($data);
		
		print(json_encode(array(
			'success'=>$success
		)));
	}
}

