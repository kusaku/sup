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
		
		$managers = People::model()->findAllByAttributes(array(
			'pgroup_id'=>array(
				3, 4
			)
		));
		
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
	
		// по-умолчанию период - весь текущий месяц
		$dt_beg or $dt_beg = date('Y-m-01');
		$dt_end or $dt_end = date('Y-m-01', strtotime('+1 month'));
		
		$total = array(
			'dt_beg'=>$dt_beg, 'dt_end'=>$dt_end,
		);
		
		UserRegistry::model()->report_reportType = $reportType;
		UserRegistry::model()->report_status_id = $status_id;
		UserRegistry::model()->report_manager_id = $manager_id;
		UserRegistry::model()->report_dt_beg = $dt_beg;
		UserRegistry::model()->report_dt_end = $dt_end;
		UserRegistry::model()->report_show_empty = $show_empty;

		
		// по типу отчета
		switch ($reportType) {
		
			case 'pays':
			case 'projects':
				if ($manager_id != 0) {
					// выберем переданного менеджера
					$managers = array(
						People::getById((int) $manager_id),
					);
				} else {
					// выберем менеджеров и старших менеджеров
					$managers = People::model()->findAllByAttributes(array(
						'pgroup_id'=>array(
							3, 4
						)
					));
				}
				break;
				
			case 'mypays':
			case 'myprojects':
			default:
				$managers = array(
					People::getById(Yii::app()->user->id),
				);
				break;
		}
		
		switch ($reportType) {
			case 'pays':
			case 'mypays':
				$criteria = new CDbCriteria();
				
				// XXX почему не применяется при setDbCriteria()???
				$criteria->scopes = array(
					'pay'
				);
				$criteria->compare('dt', '>='.date('Y-m-d', strtotime($dt_beg)));
				$criteria->compare('dt', '<'.date('Y-m-d', strtotime($dt_end)));
				
				$payments = Payment::model()->findAll($criteria);
				
				$data = array(
				);
				
				$totalSumm = 0;
				$totalCount = 0;
				
				foreach ($managers as $manager) {
				
					$pays = array(
					);
					$managerSumm = 0;
					
					// XXX как бы выбрать только оплаты текущего менеждера???
					foreach ($payments as $payment) {
					
						$package = $payment->package;
						
						if ($manager->equals($package->manager)) {
							$totalCount++;
							$managerSumm += $payment->amount;
							$pays[] = array(
								'name'=> empty($package->name) ? "#{$package->primaryKey}" : $package->name,
								//
								'site'=> empty($package->site->url) ? '' : $package->site->url,
								//
								'client'=> empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio,
								//
								'description'=> htmlspecialchars($payment->description),
								//
								'mail'=> empty($package->client->mail) ? '' : $package->client->mail,
								//
								'dt'=>date('d.m.Y', strtotime($payment->dt)),
								//
								'amount'=>$payment->amount * $payment->debit
							);
						}
					}
					
					$totalSumm += $managerSumm;
					$data[$manager->primaryKey] = array(
						'name'=>$manager->fio, 'pays'=>$pays, 'count'=>count($pays), 'summ'=>$managerSumm
					);
				}
				
				$total = array(
					'dt_beg'=>$dt_beg, 'dt_end'=>$dt_end, 'count'=>$totalCount, 'summ'=>$totalSumm
				);
				
				$this->render('/report/pays', array(
					'data'=>$data, 'total'=>$total,
				));
				
				break;
				
			case 'projects':
			case 'myprojects':
			
				// установка критериев отбора
				$criteria = new CDbCriteria();
				
				// если выбран статус делаем выборку по нему
				switch ($status_id) {
				
					// любой статус
					case '-1':
						break;
						
					// любой оплаченный
					case '-2':
						$criteria->addColumnCondition(array(
							'status_id'=>20, 'status_id'=>30,
						), ' OR ');
						break;
						
					// конкретный статус
					default:
						$criteria->addColumnCondition(array(
							'status_id'=>$status_id
						));
						break;
				}
				
				// выборка по периоду
				$criteria->compare('dt_change', ' >= '.date('Y-m-d', strtotime($dt_beg)));
				$criteria->compare('dt_change', ' < '.date('Y-m-d', strtotime($dt_end)));
				
				// сортировка - берем из модели
				$criteria->scopes = array(
					'byclient', 'bychanged'
				);
				
				$data = array(
				);
				
				$totalSumm = 0;
				$totalPaid = 0;
				$totalCount = 0;
				
				foreach ($managers as $manager) {
					$packs = array(
					);
					$managerSumm = 0;
					$managerPaid = 0;
					
					// клонируем критерий и добавляем к нему менеджера
					$managerCriteria = clone $criteria;
					$managerCriteria->addColumnCondition(array(
						'manager_id'=>$manager->primaryKey
					));
					
					foreach (Package::model()->findAll($managerCriteria) as $package) {
					
						if (!($show_empty or count($package->servPack)))
							continue;
							
						$pack['client'] = empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio;
						$pack['mail'] = empty($package->client) ? '' : $package->client->mail;
						$pack['name'] = empty($package->name) ? "#{$package->primaryKey}" : $package->name;
						$pack['descr'] = $package->descr;
						$pack['site'] = empty($package->site->url) ? '' : $package->site->url;
						$pack['status'] = $package->status->name;
						$pack['dt_beg'] = $package->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_beg)) : '(дата не указана)';
						$pack['dt_end'] = $package->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_end)) : '(дата не указана)';
						$pack['dt_change'] = $package->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_change)) : '(дата не указана)';
						$pack['summ'] = 0;
						
						// заказанные услуги
						$servs = array(
						);
						
						foreach ($package->servPack as $service) {
							$servs[] = array(
								//
								'name'=>$service->service->name,
								//
								'descr'=>$service->descr,
								//
								'dt_beg'=>$service->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_beg)) : '(дата не указана)',
								//
								'dt_end'=>$service->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_end)) : '(дата не указана)',
								//
								'count'=>$service->quant,
								//
								'price'=>$service->price,
								//
								'summ'=>$service->price * $service->quant,
							);
							
							$pack['summ'] += $service->price * $service->quant;
						}
						
						$pack['servs'] = $servs;
						$pack['count'] = count($servs);
						
						// оплаты заказа клиентом
						$pack['paid'] = 0;
						$pays = array(
						);
						
						foreach ($package->payments('payments:pay') as $payment) {
							$pays[] = array(
								//
								'dt'=>$payment->dt != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($payment->dt)) : '(дата не указана)',
								//
								'summ'=>$payment->amount * $payment->debit,
								//
								'rekviz'=> empty($payment->rekviz) ? '(не указан)' : $payment->rekviz->val,
							);
							
							$pack['paid'] += $payment->amount * $payment->debit;
						}
						
						$pack['pays'] = $pays;
						
						$packs[] = $pack;
						
						$managerSumm += $pack['summ'];
						$managerPaid += $pack['paid'];
					}
					
					$data[$manager->primaryKey] = array(
						'name'=>$manager->fio, 'packs'=>$packs, 'count'=>count($packs), 'summ'=>$managerSumm, 'paid'=>$managerPaid,
					);
					
					$totalCount += count($packs);
					$totalSumm += $managerSumm;
					$totalPaid += $managerPaid;
				}
				$total = array(
					'dt_beg'=>$dt_beg, 'dt_end'=>$dt_end, 'count'=>$totalCount, 'summ'=>$totalSumm, 'paid'=>$totalPaid,
				);
				$this->render('/report/projects', array(
					'data'=>$data, 'total'=>$total,
				));
				break;
		}
	}
}

