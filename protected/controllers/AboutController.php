<?php 
define('LDAP_DOMAIN', 'fabrica.local');

class AboutController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */

	public function filters() {
		return array(
			//'accessControl'
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
					'index','test'
				),'roles'=>array(
					'admin','manager','guest'
				)
			),array(
				'deny','users'=>array(
					'*'
				)
			)
		);
	}

	public function actionIndex() {
		$this->renderPartial('//default/about');
	}

	public function actionTest() {
		$criteria = new CDbCriteria();
		$criteria->compare('package.dt_change', ' >= '.date('Y-m-d', strtotime('2012-04-01')));
		$criteria->compare('package.dt_change', ' < '.date('Y-m-d', strtotime('2012-04-10') + 86399));
		
		$criteria3 = new CDbCriteria;
		$criteria3->select = 'SUM(amount) as summ';
		$criteria3->alias = 'payment';
		$criteria3->group = 'NULL';
		
		$res = Payment::model()->with(array(
			'package'=>array(
				'select'=>false,'joinType'=>'INNER JOIN','condition'=>$criteria->condition,'params'=>$criteria->params,'scopes'=>array(
					'paid'
				),
			)
		));
		
		$summ = $res->find($criteria3)->summ;
		
		$res = Payment::model()->with(array(
			'package'=>array(
				'select'=>false,'joinType'=>'INNER JOIN','condition'=>$criteria->condition,'params'=>$criteria->params,'scopes'=>array(
					'paid'
				),
			)
		));
		$count = $res->count();
		
		var_dump($summ, $count);
		
		$this->render('//default/test');
	}
}
