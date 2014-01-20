<?php
/**
 * контроллер для таблицы people
 * пользоватли
 * клиенты
 * партнёты
 */
class WithdrawController extends Controller {

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
				'allow','actions'=>array(
					'index',
					'approve',
					'reject'
				),'roles'=>array(
					'admin','moder','topmanager','master'
				)
			),
			array(
				'deny','users'=>array(
					'*'
				)
			)
		);
	}

	/**
	 * Метод обеспечивает вывод списка пользователей для редактирования
	 */
	public function actionIndex() {
		$obWithdrawForm = new WithdrawForm();
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['WithdrawForm'])) {
			$obWithdrawForm->attributes=$_POST['WithdrawForm'];
			if($obWithdrawForm->validate()){
				$obWithdrawForm->save();
			}
		}
		$this->render('/withdraw/index',array('obWithdrawForm'=>$obWithdrawForm));
	}
}
