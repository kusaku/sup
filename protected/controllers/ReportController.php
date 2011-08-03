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
	 * ненерирует отчет
	 * @param object $reportType
	 * @param object $status_id [optional] выбрать только этот статус
	 * @param object $manager_id [optional] выбрать только этого менеджера
	 * @param object $dt_beg [optional] выбрать, начиная с этой даты
	 * @param object $dt_end [optional] выбрать, заканчивая этой датой
	 * @return
	 */
	public function actionGenerate($reportType, $status_id = 0, $manager_id = 0, $dt_beg = null, $dt_end = null, $show_empty = false) {
	
		$data = array(
		);
		
		// по-умолчанию период - весь текущий месяц
		$dt_beg or $dt_beg = date('Y-m-01');
		$dt_end or $dt_end = date('Y-m-01', strtotime('+1 month'));
		
		$total = array(
			'dt_beg'=>$dt_beg, 'dt_end'=>$dt_end,
		);
		
		UserRegistry::model()->report_reportType = $reportType;
		UserRegistry::model()->report_manager_id = $manager_id;
		UserRegistry::model()->report_dt_beg = $dt_beg;
		UserRegistry::model()->report_dt_end = $dt_end;
		UserRegistry::model()->report_show_empty = $show_empty;

		
		// по типу отчета
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
			default:
			case 'myself':
				$managers = array(
					People::getById(Yii::app()->user->id),
				);
				break;
		}
		
		$totalSumm = 0;
		$totalPaid = 0;
		$totalCount = 0;
		
		// установка критериев отбора
		$criteria = new CDbCriteria();
		
		// если выбран статус делаем выборку по нему
		$status_id and $criteria->addColumnCondition(array(
			'status_id'=>$status_id
		));
		
		// выборка по периоду
		$criteria->compare('dt_change', '>='.date('Y-m-d', strtotime($dt_beg)));
		$criteria->compare('dt_change', '<'.date('Y-m-d', strtotime($dt_end)));
		
		// сортировка - берем из модели
		$criteria->scopes = array(
			'byclient', 'bychanged'
		);
		
		foreach ($managers as $manager) {
			$packs = array(
			);
			$managerSumm = 0;
			$managerPaid = 0;
			
			$packages = Package::model();
			$packages->setDbCriteria($criteria);
			
			foreach ($packages->findAllByAttributes(array(
				'manager_id'=>$manager->primaryKey
			)) as $package) {
			
				if (!($show_empty or count($package->servPack)))
					continue;
					
				$pack['client'] = empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio;
				$pack['clientmail'] = empty($package->client) ? '' : $package->client->mail;
				$pack['name'] = empty($package->name) ? "#{$package->primaryKey}" : $package->name;
				$pack['descr'] = $package->descr;
				$pack['site'] = empty($package->site->url) ? '' : $package->site->url;
				
				$pack['status'] = $package->status->name;
				$pack['dt_beg'] = $package->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_beg)) : '(дата не указана)';
				$pack['dt_end'] = $package->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_end)) : '(дата не указана)';
				$pack['dt_change'] = $package->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_change)) : '(дата не указана)';
				$pack['summ'] = 0;
				$pack['paid'] = $package->paid;
				
				$servs = array(
				);
				
				foreach ($package->servPack as $service) {
				
					$serv['name'] = $service->service->name;
					$pack['descr'] = $service->descr;
					$serv['dt_beg'] = $service->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_beg)) : '(дата не указана)';
					$serv['dt_end'] = $service->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_end)) : '(дата не указана)';
					$serv['count'] = $service->quant;
					$serv['price'] = $service->price;
					$serv['summ'] = $service->price * $service->quant;
					
					$pack['summ'] += $serv['summ'];
					
					$servs[] = $serv;
				}
				
				$pack['servs'] = $servs;
				$pack['count'] = count($servs);
				$managerSumm += $pack['summ'];
				$managerPaid += $pack['paid'];
				$packs[] = $pack;
				
			}
			
			$data[$manager->primaryKey] = array(
				'name'=>$manager->fio, 'packs'=>$packs, 'count'=>count($packs), 'summ'=>$managerSumm, 'paid'=>$managerPaid,
			);
			
			$totalCount += count($packs);
			$totalSumm += $managerSumm;
		}
		
		$total = array(
			'dt_beg'=>$dt_beg, 'dt_end'=>$dt_end, 'count'=>$totalCount, 'summ'=>$totalSumm, 'paid'=>$totalPaid,
		);
		
		$this->render('/app/report', array(
			'data'=>$data, 'total'=>$total,
		));
	}
}

