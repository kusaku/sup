<?php 
class DefaultController extends Controller {

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
				'allow', 'actions'=>array(
					'index'
				), 'roles'=>array(
					'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog'
				),
			), array(
				'allow', 'actions'=>array(
					'logout'
				), 'roles'=>array(
					'authenticated'
				),
			), array(
				'allow', 'actions'=>array(
					'login'
				), 'roles'=>array(
					'guest'
				),
			), array(
				'allow', 'actions'=>array(
					'error'
				), 'users'=>array(
					'*'
				),
			), array(
				'deny', 'users'=>array(
					'*'
				),
			),
		);
	}
	
	public function actionIndex() {
		$this->render('index');
	}
}
