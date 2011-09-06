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
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
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
		
		$project = Redmine::getProjectByIdentifier('dev');
		$user = Redmine::getUserByLogin(Yii::app()->user->login);
		
		//echo count($user);
		//print_r($user);
		//exit();
		
		$array = Redmine::createIssue(array(
					// в каком проекте создать задачу
					'project_id'=>$project['id'],
					// параметры задачи
					'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
					// кто назначил и кому наначено
					'author_id'=>$user['id'],'assigned_to_id'=>$user['id'],
					// родительская задача
					'parent_issue_id'=>3165,
					// тема и описание
					'subject'=>'test','description'=>'test',
					// когда начата и когда должна быть закончена
					'start_date'=>date('Y-m-d', strtotime('now')),'due_date'=>date('Y-m-d', strtotime('now +1 day')),
					// время на выполнение и потраченное время
					'estimated_hours'=>'0.0','spent_hours'=>'0.0'
				));
		
		echo count($array);
		print_r($array);
		//$this->renderPartial('test');
	}
}
