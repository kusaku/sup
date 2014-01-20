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
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow','actions'=>array(
					'index','generate',
				),'roles'=>array(
					'admin','moder','topmanager','manager','master','marketolog'
				),

			),array(
				'deny','users'=>array(
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
				3,4
			)
		));

		$this->renderPartial('index', array(
			'roles'=>$roles,'managers'=>$managers,
		));
	}

	/**
	 * Метод обеспечивает генерацию отчёта по оплатам партнеров
	 */
	public function actionPartnerRecsPays() {
		$dt_beg = UserRegistry::model()->report_dt_beg;
		$dt_end = UserRegistry::model()->report_dt_end;
		$total = array('dt_beg'=>$dt_beg,'dt_end'=>$dt_end);


		$criteria = new CDbCriteria();
		$criteria->order = 'dt ASC';
		$criteria->scopes = array(
			'recpay',
		);
		$criteria->with = array(
			'package'=>array(
				'joinType'=>'INNER JOIN',
			),
			'package.client.owner_partner'=>array(
				'joinType'=>'INNER JOIN',
			)
		);
		$criteria->compare('dt', '>='.date('Y-m-d H:i:s', strtotime($dt_beg)));
		$criteria->compare('dt', '<='.date('Y-m-d H:i:s', strtotime($dt_end) + 86399));

		$totalSumm = 0;
		$totalCount = 0;

		$arPartners = Partner::model()->findAll();

		$data = array();
		foreach ($arPartners as $obPartner) {

			$pays = array(
			);
			$partnerSumm = 0;

			$criteriaPayment = clone $criteria;
			$criteriaPayment->addColumnCondition(array('id_partner' => $obPartner->id));
			$payments = Payment::model()->findAll($criteriaPayment);
			foreach ($payments as $payment) {

				$package = $payment->package;

				$totalCount++;
				$partnerSumm += $payment->amount;
				$pays[] = array(
					'name'=> empty($package->name) ? "#{$package->primaryKey}" : $package->name,
					'site'=> empty($package->site->url) ? '' : $package->site->url,
					'client'=> empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio,
					'description'=>htmlspecialchars($payment->description),
					'mail'=> empty($package->client->mail) ? '' : $package->client->mail,
					'dt'=>date('d.m.Y', strtotime($payment->dt)),
					'amount'=>$payment->amount * $payment->debit
				);
			}

			$totalSumm += $partnerSumm;
			$data[$obPartner->id] = array(
				'name'=>$obPartner->user_data['fio'],
				'mail'=>$obPartner->user_data['mail'],
				'pays'=>$pays,
				'count'=>count($pays),
				'summ'=>$partnerSumm,
			);
		}

		$total = array(
			'dt_beg'=>$dt_beg,
			'dt_end'=>$dt_end,
			'count'=>$totalCount,
			'summ'=>$totalSumm
		);

		$this->render('/report/partner_recs_pays', array(
			'data'=>$data,'total'=>$total,
		));


	}

	/**
	 * Метод обеспечивает генерацию отчёта по партнёрам
	 */
	public function actionPartnerReport() {
		$manager_id = UserRegistry::model()->report_manager_id;
		$dt_beg = UserRegistry::model()->report_dt_beg;
		$dt_end = UserRegistry::model()->report_dt_end;
		$status_id = UserRegistry::model()->report_status_id;
		$payment_id = UserRegistry::model()->report_payment_id;
		$show_empty = UserRegistry::model()->report_show_empty;
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$total = array('dt_beg'=>$dt_beg,'dt_end'=>$dt_end);
		$dt_beg=date('Y-m-d 00:00:00',strtotime($dt_beg));
		$dt_end=date('Y-m-d 23:59:59',strtotime($dt_end));
		$arResult=array();
		//Выбираем партнёров
		$sql="SELECT people.id as id, people.fio as fio, partner.status as status FROM partner INNER JOIN people ON partner.id=people.id WHERE partner.status='active'";
		$arPartners=$connection->createCommand($sql)->queryAll();
		foreach($arPartners as $arPartner) {
			$arPartner['new_clients']=0;
			$arPartner['orders']=0;
			$arResult[$arPartner['id']]=$arPartner;
		}
		//Считаем сколько клиентов у партнёра за период
		$sql="SELECT partner_people.id_partner as partner_id, count(people.id) as cnt FROM partner_people LEFT JOIN people ON people.id=partner_people.id_client
			WHERE people.regdate>='$dt_beg' AND people.regdate<='$dt_end' GROUP BY partner_people.id_partner";
		$arItems=$connection->createCommand($sql)->queryAll();
		$iTotalClients=0;
		foreach($arItems as $arItem) {
			if(isset($arResult[$arItem['partner_id']])) {
				$arResult[$arItem['partner_id']]['new_clients']=$arItem['cnt'];
				$iTotalClients+=$arItem['cnt'];
			}
		}
		//Считаем сколько заказов через партнёра за период
		$iTotalOrders=0;
		$sql="SELECT partner_people.id_partner as partner_id, count(package.id) as cnt FROM partner_people LEFT JOIN package ON package.client_id=partner_people.id_client
			WHERE package.dt_beg>='$dt_beg' AND package.dt_end<='$dt_end' GROUP BY partner_people.id_partner";
		$arItems=$connection->createCommand($sql)->queryAll();
		foreach($arItems as $arItem) {
			if(isset($arResult[$arItem['partner_id']])) {
				$arResult[$arItem['partner_id']]['orders']=$arItem['cnt'];
				$iTotalOrders+=$arItem['cnt'];
			}
		}
		//Считаем статистику по статусам
		///@TODO Запрос требует оптимизации
		$sql="SELECT count(A.partner_id) as cnt, A.status FROM (SELECT partner_id, status FROM (SELECT status, partner_id FROM partner_status_log WHERE date<='$dt_end' ORDER BY partner_id DESC, date DESC) as A GROUP BY partner_id) as A GROUP BY status";
		$arItems=$connection->createCommand($sql)->queryAll();
		$arItems=CHtml::listData($arItems, 'status', 'cnt');
		if(isset($arItems[Partner::STAT_NEW]))
			$total['new_partners']=$arItems[Partner::STAT_NEW];
		else
			$total['new_partners']=0;
		if(isset($arItems[Partner::STAT_ACTIVE]))
			$total['active_partners']=$arItems[Partner::STAT_ACTIVE];
		else
			$total['active_partners']=0;
		if(isset($arItems[Partner::STAT_NEGOTIATIONS]))
			$total['negotiations_partners']=$arItems[Partner::STAT_NEGOTIATIONS];
		else
			$total['negotiations_partners']=0;
		if(isset($arItems[Partner::STAT_CLOSED]))
			$total['closed_partners']=$arItems[Partner::STAT_CLOSED];
		else
			$total['closed_partners']=0;
		$total['orders']=$iTotalOrders;
		$total['clients']=$iTotalClients;

		$this->render('partner', array('total'=>$total,'partners'=>$arResult));
	}

	/**
	 * Метод обеспечивает генерацию отчёта по партнёрам
	 */
	public function actionPartnerReport2() {
		$dt_beg = UserRegistry::model()->report_dt_beg;
		$dt_end = UserRegistry::model()->report_dt_end;
		$status_id = UserRegistry::model()->report_status_id;
		$payment_id = UserRegistry::model()->report_payment_id;
		$show_empty = UserRegistry::model()->report_show_empty;
		$total = array('dt_beg'=>$dt_beg,'dt_end'=>$dt_end);


		// установка критериев отбора
		$criteria = new CDbCriteria();

		// выборка по дате
		$criteria->compare('dt_beg', '>='.date('Y-m-d H:i:s', strtotime($total['dt_beg'])));
		$criteria->compare('dt_beg', '<'.date('Y-m-d H:i:s', strtotime($total['dt_end']) + 86399));

		// если выбран статус заказа делаем выборку по нему
		switch ($status_id) {

			// любой статус
			case '-1':
				break;

			// конкретный статус
			default:
				$criteria->addColumnCondition(array(
					'status_id'=>$status_id
				));
				break;
		}

		// если выбран статус оплаты делаем выборку по нему
		switch ($payment_id) {

			// любой статус
			case '-1':
				break;

			// конкретный статус
			default:
				$criteria->addColumnCondition(array(
					'payment_id'=>$payment_id
				));
				break;
		}

		// выборка по периоду
		// Седрак просил переделать
		$criteriaPay = new CDbCriteria();

		// выберем все платежи
		$criteriaPay->scopes = array(
			'recpay'
		);

		$totalSumm = 0;
		$totalCount = 0;

		$arPartners = Partner::model()->findAll();

		foreach ($arPartners as $obPartner) {
			$packs = array(
			);
			$managerSumm = 0;
			$managerCount = 0;

			// клонируем критерий и добавляем к нему менеджера
			$criteriaPack = clone $criteria;
			$criteriaPack->addColumnCondition(array(
				'id_partner' => $obPartner->id,
			));

			$payments = Payment::model()->with(array(
				'package'=>array(
					'joinType'=>'INNER JOIN',
					'condition'=>$criteria->condition,
					'params'=>$criteria->params
				),
				'package.client.owner_partner'=>array(
					'joinType'=>'INNER JOIN',
					'condition'=>$criteriaPack->condition,
					'params'=>$criteriaPack->params
				)
			))->findAll($criteriaPay);

			foreach ($payments as $payment) {

				$package = $payment->package;

				if (!($show_empty or count($package->servPack)))
					continue;

				$managerSumm += $payment->amount;
				$managerCount++;

				$packs[] = array(
					'client'=> empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio,
					'partner_name' => empty($obPartner->user_data->fio) ? "#{$obPartner->id}" : $obPartner->user_data->fio,
					'partner_mail' => empty($obPartner->user_data->mail) ? "#{$obPartner->id}" : $obPartner->user_data->mail,
					'manager'=> empty($package->manager->fio) ? "#{$package->manager_id}" : $package->manager->fio,
					'mail'=> empty($package->client) ? '' : $package->client->mail,'name'=> empty($package->name) ? "#{$package->primaryKey}" : $package->name,
					'descr'=>$package->descr,'site'=> empty($package->site->url) ? '' : $package->site->url,
					'summ'=>$payment->amount,'promocode'=> empty($package->promocode->code) ? '(не указан)' : $package->promocode->code,
					'type'=>isset($package->services) ? $package->services[0]->name : '(нет)',
					'id'=>$package->primaryKey,'status'=>$payment->ptype_id == 0 ? 'обещан' : 'подтвержден',
					'dt'=>$payment->dt != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($payment->dt)) : '(дата не указана)',
					'dt_beg'=>$package->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_beg)) : '(дата не указана)',
					'dt_end'=>$package->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_end)) : '(дата не указана)',
					'dt_change'=>$package->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_change)) : '(дата не указана)',
				);
			}

			$totalSumm += $managerSumm;
			$totalCount += $managerCount;

			$data[$obPartner->id] = array(
				'partner_name' => empty($obPartner->user_data->fio) ? "#{$obPartner->id}" : $obPartner->user_data->fio,
				'partner_mail' => empty($obPartner->user_data->mail) ? "#{$obPartner->id}" : $obPartner->user_data->mail,
				'packs'=>$packs,
				'summ'=>$managerSumm,
				'count'=>$managerCount
			);
		}

		$total = array(
			'dt_beg'=>$dt_beg,'dt_end'=>$dt_end,'summ'=>$totalSumm,'count'=>$totalCount
		);

		$this->render('/report/partner2', array(
			'data'=>$data,'total'=>$total,
		));

	}

	/**
	 * генерирует отчет
	 */
	public function actionGenerate() {

		$request = Yii::app()->getRequest();
		$reportType = (string) $request->getParam('reportType');
		$manager_id = (int) $request->getParam('manager_id');
		$dt_beg = (string) $request->getParam('dt_beg');
		$dt_end = (string) $request->getParam('dt_end');
		$status_id = (int) $request->getParam('status_id');
		$payment_id = (int) $request->getParam('payment_id');
		$show_empty = (bool)$request->getParam('show_empty');

		// по-умолчанию период - весь текущий месяц
		$dt_beg or $dt_beg = date('01.m.Y');
		$dt_end or $dt_end = date('01.m.Y', strtotime('+1 month'));

		$data = array(
		);

		$total = array(
			'dt_beg'=>$dt_beg,'dt_end'=>$dt_end,
		);

		// запоминаем настрйки
		UserRegistry::model()->report_reportType = $reportType;
		UserRegistry::model()->report_manager_id = $manager_id;
		UserRegistry::model()->report_dt_beg = $dt_beg;
		UserRegistry::model()->report_dt_end = $dt_end;
		UserRegistry::model()->report_status_id = $status_id;
		UserRegistry::model()->report_payment_id = $payment_id;
		UserRegistry::model()->report_show_empty = $show_empty;

		// по типу отчета
		switch ($reportType) {
			case 'partner':
				return $this->actionPartnerReport();
			case 'partner2':
				return $this->actionPartnerReport2();
			case 'partnerRecsPays':
				return $this->actionPartnerRecsPays();
			case 'recs':
			case 'recspays':
			case 'projects':
			case 'seo':
				if ($manager_id != 0) {
					// выберем переданного менеджера
					$managers = array(
						People::getById((int) $manager_id),
					);
				} else {
					// выберем менеджеров и старших менеджеров
					$managers = People::model()->findAllByAttributes(array(
						'pgroup_id'=>array(
							3,4,5,8,11,12
						)
					));
				}
				break;
			case 'recs2':
			break;
			case 'myrecs':
			case 'mypays':
			case 'myrecspays':
			case 'myprojects':
			default:
				$managers = array(
					People::getById(Yii::app()->user->id),
				);
				break;
		}

		switch ($reportType) {
			case 'pays':
				$obReport=new PaysReport($dt_beg,$dt_end,$manager_id);
				$this->render('/report/pays', $obReport->getData());
			break;
			case 'recs':
				$obReport=new RecsReport($dt_beg,$dt_end,$manager_id);
				$this->render('/report/pays', $obReport->getData());
			break;
			case 'recspays':
				$obReport=new RecpaysReport($dt_beg,$dt_end,$manager_id);
				$this->render('/report/pays', $obReport->getData());
			break;
			case 'myrecs':
				$obReport=new RecsReport($dt_beg,$dt_end,Yii::app()->user->id);
				$this->render('/report/pays', $obReport->getData());
			break;
			case 'mypays':
				$obReport=new PaysReport($dt_beg,$dt_end,Yii::app()->user->id);
				$this->render('/report/pays', $obReport->getData());
			break;
			case 'myrecspays':
				$obReport=new RecpaysReport($dt_beg,$dt_end,Yii::app()->user->id);
				$this->render('/report/pays', $obReport->getData());
			break;
			case 'projects':
			case 'myprojects':

				// установка критериев отбора
				$criteria = new CDbCriteria();

				// если выбран статус заказа делаем выборку по нему
				switch ($status_id) {

					// любой статус
					case '-1':
						break;

					// конкретный статус
					default:
						$criteria->addColumnCondition(array(
							'status_id'=>$status_id
						));
						break;
				}

				// если выбран статус оплаты делаем выборку по нему
				switch ($payment_id) {

					// любой статус
					case '-1':
						break;

					// конкретный статус
					default:
						$criteria->addColumnCondition(array(
							'payment_id'=>$payment_id
						));
						break;
				}

				// выборка по периоду
				$criteria->compare('dt_change', ' >= '.date('Y-m-d H:i:s', strtotime($dt_beg)));
				$criteria->compare('dt_change', ' < '.date('Y-m-d H:i:s', strtotime($dt_end) + 86399));

				// сортировка - берем из модели
				$criteria->scopes = array(
					'byclient','bychanged'
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
						$pack = array(
							'client'=> empty($package->client->fio) ? "#{$package->client_id}" : $package->client->fio,
								'mail'=> empty($package->client) ? '' : $package->client->mail,'name'=> empty($package->name) ? "#{$package->primaryKey}" : $package->name,
								'descr'=>$package->descr,'site'=> empty($package->site->url) ? '' : $package->site->url,
								'promocode'=> empty($package->promocode->code) ? '(не указан)' : $package->promocode->code,
								'status'=>$package->wf_status->name,'dt_beg'=>$package->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_beg)) : '(дата не указана)',
								'dt_end'=>$package->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_end)) : '(дата не указана)',
								'dt_change'=>$package->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($package->dt_change)) : '(дата не указана)',
								'summ'=>0,

						);

						// заказанные услуги
						$servs = array(
						);

						foreach ($package->servPack as $service) {
							$servs[] = array(
								'name'=>$service->service->name,'descr'=>$service->descr,'dt_beg'=>$service->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_beg)) : '(дата не указана)',
									'dt_end'=>$service->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($service->dt_end)) : '(дата не указана)',
									'count'=>$service->quant,'price'=>$service->price,'summ'=>$service->price * $service->quant,

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
								'dt'=>$payment->dt != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($payment->dt)) : '(дата не указана)',
									'summ'=>$payment->amount * $payment->debit,'description'=>htmlspecialchars($payment->description),

							);

							$pack['paid'] += $payment->amount * $payment->debit;
						}

						$pack['pays'] = $pays;

						$packs[] = $pack;

						$managerSumm += $pack['summ'];
						$managerPaid += $pack['paid'];
					}

					$data[$manager->primaryKey] = array(
						'name'=>$manager->fio,'packs'=>$packs,'count'=>count($packs),'summ'=>$managerSumm,
							'paid'=>$managerPaid,

					);

					$totalCount += count($packs);
					$totalSumm += $managerSumm;
					$totalPaid += $managerPaid;
				}
				$total = array(
					'dt_beg'=>$dt_beg,'dt_end'=>$dt_end,'count'=>$totalCount,'summ'=>$totalSumm,
						'paid'=>$totalPaid,

				);
				$this->render('/report/projects', array(
					'data'=>$data,'total'=>$total,
				));
				break;
			case 'seo':
				$obReport=new SeoReport($dt_beg,$dt_end,$manager_id,$status_id,$payment_id,$show_empty);
				$this->render('/report/seo', $obReport->getData());
			break;
			case 'seo2':
				$obSiteOrdersReport=new SiteOrdersReport($total['dt_beg'],strtotime($total['dt_end']) + 86399);
				$data['from_site']['total']=$obSiteOrdersReport->getTotalOrdersCount();
				$data['from_site']['total_list']=$obSiteOrdersReport->getTotalOrders();
				$data['from_site']['conditionally_paid']=$obSiteOrdersReport->getVirtuallyPaidOrdersCount();
				$data['from_site']['conditionally_paid_list']=$obSiteOrdersReport->getVirtuallyPaidOrders();
				$data['from_site']['really_paid']=$obSiteOrdersReport->getReallyPaidOrdersCount();
				$data['from_site']['really_paid_list']=$obSiteOrdersReport->getReallyPaidOrders();
				$data['from_site']['total_paid']=$obSiteOrdersReport->getPaidOrdersCount();
				$data['from_site']['total_paid_list']=$obSiteOrdersReport->getPaidOrders();
				$this->render('/report/seo2', array('data'=>$data,'total'=>$total));
			break;
			case 'recs2':
				$this->_reportRecs2();
			break;
		}
	}

	/**
	 * Функция генерирует отчёт по платежам, которые не были подтверждены
	 */
	private function _reportRecs2() {
		$request = Yii::app()->getRequest();
		$manager_id = (int) $request->getParam('manager_id');
		$dt_beg = (string) $request->getParam('dt_beg');
		$dt_end = (string) $request->getParam('dt_end');

		// по-умолчанию период - весь текущий месяц
		$dt_beg or $dt_beg = date('01.m.Y');
		$dt_end or $dt_end = date('01.m.Y', strtotime('+1 month'));

		$total = array(
			'dt_beg'=>$dt_beg,'dt_end'=>$dt_end,
		);
		$obModel=Payment::model();
		$condition = new CDbCriteria();
		// выборка по дате
		$condition->compare('dt', '>='.date('Y-m-d H:i:s', strtotime($dt_beg)));
		$condition->compare('dt', '<'.date('Y-m-d H:i:s', strtotime($dt_end) + 86399));
		$arPayments=array();
		$arPaymentsObj=$obModel->rec()->findAll($condition);
		if($manager_id>0) {
			foreach($arPaymentsObj as $obPay) {
				if(!$obPay->package) continue;
				if($obPay->package->manager_id!=$manager_id) continue;
				$arRow=array(
					'order_id'=>$obPay->package_id,
					'client'=>'',
					'manager'=>'',
					'date'=>date('Y-m-d',strtotime($obPay->dt)),
					'summ'=>$obPay->amount
				);
				if($obPay->package) {
					if($obPay->package->client)
						$arRow['client']=$obPay->package->client->mail.' '.$obPay->package->client->fio;
					if($obPay->package->manager)
						$arRow['manager']=$obPay->package->manager->fio;
				}
				$arPayments[]=$arRow;
			}
		} else {
			foreach($arPaymentsObj as $obPay) {
				$arRow=array(
					'order_id'=>$obPay->package_id,
					'client'=>'',
					'manager'=>'',
					'date'=>date('Y-m-d',strtotime($obPay->dt)),
					'summ'=>$obPay->amount
				);
				if($obPay->package) {
					if($obPay->package->client)
						$arRow['client']=$obPay->package->client->mail.' '.$obPay->package->client->fio;
					if($obPay->package->manager)
						$arRow['manager']=$obPay->package->manager->fio;
				}
				$arPayments[]=$arRow;
			}
		}
		$this->render('/report/recs2',array('payments'=>$arPayments,'total'=>$total));
	}
}
