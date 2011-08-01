<?php 
class ReportController extends Controller {

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
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner');
		return array(
			array(
				'allow', 'actions'=>array(
					'index', 'generate'
				), 'roles'=>array(
					'admin', 'moder', 'topmanager', 'manager', 'master'
				),
			), array(
				'deny', 'users'=>array(
					'*'
				),
			),
		);
	}
	
	/**
	 *
	 * @return
	 */
	public function actionIndex() {
		$authmanager = Yii::app()->getAuthManager();
		$roles = array(
		);
		
		foreach ($authmanager->getRoles() as $role=>$auth) {
			$roles[$role] = $authmanager->checkAccess($role, Yii::app()->user->getId());
		}
		
		$managers = $peoples = PeopleGroup::getById(4)->peoples;
		
		$this->renderPartial('index', array(
			'roles'=>$roles, 'managers'=>$managers,
		));
	}
	
	/**
	 *
	 * @return
	 */
	public function actionGenerate($reportType, $manager_id) {
	
		$data = array(
		);
		$total = array(
		);
		
		switch ($reportType) {
			case 'allmanagers':
				// выберем менеджеров и старших менеджеров
				$managers = array(
				);
				foreach (PeopleGroup::model()->findAllByPk(array(
					3, 4
				)) as $group) {
					$managers = array_merge($managers, $group->peoples);
				}
				break;
			case 'onemanager':
				$managers = array(
					People::getById($manager_id),
				);
				break;
			case 'monthly':
				$managers = array(
					People::getById(Yii::app()->user->id),
				);
				break;
		}
		$totalSumm = 0;
		$totalCount = 0;
		
		foreach ($managers as $manager) {
			$packs = array(
			);
			$managerSumm = 0;
			
			foreach (Package::model()->lastmonth()->byclient()->bychanged()->findAllByAttributes(array(
				'manager_id'=>$manager->primaryKey
			)) as $package) {
			
				$pack['client'] = ! empty($package->client->fio) ? $package->client->fio : "(клиент #{$package->client_id})";
				$pack['clientmail'] = ! empty($package->client) ? $package->client->mail : '';
				$pack['name'] = ! empty($package->name) ? $package->name : "(заказ #{$package->primaryKey})";
				$pack['descr'] = $package->descr;
				$pack['site'] = ! empty($package->site->url) ? $package->site->url : '(без привязки к сайту)';

				
				$pack['status'] = $package->status->name;
				$pack['dt_beg'] = $package->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_beg)) : '(дата не указана)';
				$pack['dt_end'] = $package->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_end)) : '(дата не указана)';
				$pack['dt_change'] = $package->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_change)) : '(дата не указана)';
				$pack['summ'] = 0;
				
				$servs = array(
				);
				
				foreach ($package->servPack as $service) {
				
					$serv['name'] = $service->service->name;
					$pack['descr'] = $service->descr;
					$serv['price'] = $service->price;
					$serv['count'] = $service->quant;
					$serv['summ'] = $service->price * $service->quant;
					$serv['dt_beg'] = $service->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_beg)) : '(дата не указана)';;
					$serv['dt_end'] = $service->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_end)) : '(дата не указана)';;
					$pack['summ'] += $serv['summ'];
					$servs[] = $serv;
				}
				
				$pack['servs'] = $servs;
				$pack['count'] = count($servs);
				$managerSumm += $pack['summ'];
				$packs[] = $pack;
				
			}
			
			$data[$manager->primaryKey] = array(
				'name'=>$manager->fio, 'packs'=>$packs, 'count'=>count($packs), 'summ'=>$managerSumm,
			);
			
			$totalCount += count($packs);
			$totalSumm += $managerSumm;
		}
		$total = array(
			'summ'=>$totalSumm, 'count'=>$totalCount,
		);
		
		$this->render('/app/report', array(
			'data'=>$data, 'total'=>$total,
		));
	}
}

