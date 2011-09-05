<?php 
define('LDAP_DOMAIN', 'fabrica.local');

class AboutController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */
	/*public function filters() {
	 return array(
	 'accessControl'
	 );
	 }*/
	
	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */
	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner');
		return array(
			array(
				'allow',
					'actions'=>array(
					'index',
					'test'
				),
					'roles'=>array(
					'admin',
					'manager',
					'guest'
				)
			),
					array(
				'deny',
					'users'=>array(
					'*'
				)
			)
		);
	}
	
	public function actionIndex() {
		$this->renderPartial('index');
	}
	
	public function actionTest() {
		echo '<pre>';
		
		$array = Redmine::readIssue(5978, false);
		
		echo count($array);
		print_r($array);
		//$this->renderPartial('test');
	}
}
