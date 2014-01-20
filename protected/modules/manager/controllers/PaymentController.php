<?php
/**
 * Контроллер обеспечивающий управление оплатами заказов
 */
class PaymentController extends Controller {
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
					'index','view','save','edit','delete','approve','list',
				),'roles'=>array(
					'admin','moder','topmanager',
				)
			),array(
				'allow','actions'=>array(
					'index','view','save','edit',
				),'roles'=>array(
					'manager'
				)
			),array(
				'allow','actions'=>array(
					'index','view',
				),'roles'=>array(
					'marketolog',
				)
			),array(
				'deny','users'=>array(
					'*'
				)
			)
		);
	}

	/**
	 * Метод выводит список платежей по заказу
	 * @param integer $package_id
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionIndex($package_id) {
		if($package = Package::model()->findByPk($package_id)) {
			$this->renderPartial('pays', array('package'=>$package));
		} else {
			throw new CHttpException(404,'Package not found');
		}
	}

	/**
	 * Метод выводит все неподтверждённые платежи
	 */
	public function actionList() {
		$dataProvider=new CActiveDataProvider('Payment', array(
			'criteria'=>array(
				'condition'=>'ptype_id=0',
				'order'=>'dt DESC',
				'with'=>array('package'),
			),
			'pagination'=>array(
				'pageSize'=>100,
			),
		));
		$this->renderPartial('all_pays', array('list'=>$dataProvider));
	}

	/**
	 * Метод отображает форму редактирования платежа
	 * @param integer $package_id
	 * @param integer $payment_id
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionView($package_id, $payment_id = 0) {
		if($package = Package::model()->findByPk($package_id)) {
			if($payment_id>0) {
				$payment=Payment::model()->findByAttributes(array('id'=>$payment_id,'package_id'=>$package_id));
				if(is_null($payment)) {
					throw new CHttpException(404,'Payment not found');
				}
			} else {
				$payment=new Payment();
				$payment->amount = $package->summ - $package->paid;
				$payment->debit = $package->summ > $package->paid ? 1 : - 1;
				$payment->dt = date('Y-m-d H:i:s');
				$payment->ptype_id = 0;
				$payment->package_id=$package->id;
				$payment->description = $package->client->fio;
			}
			$this->renderPartial('payform', array(
				'package'=>$package,'payment'=>$payment,
			));
		} else {
			throw new CHttpException(404,'Package not found');
		}
	}

	/**
	 * Метод выполняет сохранение записи в БД
	 */
	public function actionSave() {
		if(Yii::app()->request->isPostRequest) {
			if(isset($_POST['Payment']['id']) && $_POST['Payment']['id']>0){
				$obPayment=Payment::model()->findByPk($_POST['Payment']['id']);
				if(is_null($obPayment)) {
					throw new CHttpException(404,'Payment not found');
				}
			} else {
				$obPayment=new Payment();
			}
			$obPayment->setAttributes($_POST['Payment']);
			//Обрабатываем информацию о заказе
			/**
			 * @var Package $obPackage
			 */
			$obPackage = Package::model()->findByPk($obPayment->package_id);
			if(is_null($obPackage)) {
				throw new CHttpException(404,'Package not found');
			}
			$save='';
			if($obPayment->validate()) {
				$obPayment->dt=date('Y-m-d 00:00:00',strtotime($obPayment->dt));
				if($obPayment->dt_pay!=NULL) {
					$obPayment->dt_pay=date('Y-m-d 00:00:00',strtotime($obPayment->dt_pay));
				}
				//Сохраняем платёжку
				if($obPayment->isNewRecord) {
					$obPayment->name='Оплата заказа '.$obPackage->id;
					$obPayment->debit=$obPayment->amount>0?1:-1;
					$obMe=Yii::app()->user;
					if($obMe->checkAccess('manager')) {
						$obPayment->ptype_id=0;
					} else {
						$obPayment->ptype_id=1;
					}
					if($obPayment->save(false)) {
						//Уведомляем менеджера
						if($obPayment->ptype_id==0) {
							//Уведомление бухгалтера
							foreach (People::getByFilter('topmanagers') as $topmanager) {
								$obNotify=new ManagerNotifier(false);
								$obNotify->log='[SUP][auto] Добавлен обещанный платеж для заказа #'.$obPackage->id;
								$obNotify->calendar='[auto] '.$obMe->fio.' внесла обещанный платеж для заказа <a href="http://sup.fabricasaitov.ru/manager#package_'.$obPackage->id.'_'.$obPackage->client_id.'">#'.$obPackage->id.'</a> - Оплата на сумму <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">'.$obPayment->amount.'</a> руб. <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">Подробно</a>';
								$obNotify->manager_id=$topmanager->id;
								$obNotify->client_id=$obPackage->client_id;
								$obNotify->Send();
							}
							//Уведомление в задачу и в лог клиента
							try {
								// если нет главной задачи
								if (!$obPackage->rm_issue_id) {
									Redmine::postIssue($obPackage);
								}
								// добавим в Redmine комментарий
								Redmine::updateIssue($obPackage->rm_issue_id, array(
									'notes'=>"h2. поступили сведения об оплате\n\n*сумма* - ".number_format($obPayment->amount, 0, ',', ' ')."руб."
									.($obPackage->summ > 0 ? number_format(100 * $obPackage->paid/$obPackage->summ, 0).'%' : '0%')
									."\n\n*подробности:* ".$obPayment->description,
								));
							} catch(Exception $e) {}
							Logger::put(array(
								'client_id'=>$obPackage->client_id,'manager_id'=>Yii::app()->user->id,
								'info'=>'[auto] поступили сведения об оплате, оплачено <b>'.number_format($obPayment->amount, 0, ',', ' ').'руб.</b>, дата оплаты <b>'.date('d.m.Y', strtotime($obPayment->dt)).'</b> <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">подробно</a>'
							));
						} elseif($obPayment->ptype_id==1) {
							$obNotify=new ManagerNotifier();
							$obNotify->log='[auto] Пришла оплата к заказу #'.$obPackage->id.' на сумму <b>'.number_format($obPayment->amount, 2, ',', ' ').'руб.</b>, дата оплаты <b>'.date('d.m.Y', strtotime($obPayment->dt)).'</b> <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">подробно</a>';
							$obNotify->calendar='[auto] '.$obMe->fio.' внесла оплату для заказа <a href="http://sup.fabricasaitov.ru/manager#package_'.$obPackage->id.'_'.$obPackage->client_id.'">#'.$obPackage->id.'</a> - Оплата на сумму <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">'.$obPayment->amount.'</a> руб. <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">Подробно</a>';
							$obNotify->manager_id=$obPackage->manager_id;
							$obNotify->client_id=$obPackage->client_id;
							$obNotify->Send();
							try {
								// если нет главной задачи
								if (!$obPackage->rm_issue_id) {
									Redmine::postIssue($obPackage);
								}
								// добавим в Redmine комментарий
								Redmine::updateIssue($obPackage->rm_issue_id, array(
									'notes'=>"h2. пришла оплата\n\n*сумма* - ".number_format($obPayment->amount, 0, ',', ' ')."руб., оплачено "
									.($obPackage->summ>0?number_format(100*$obPackage->paid/$obPackage->summ, 0).'%' : '0%')
									."\n\n*подробности:* ".$obPayment->description
								));
							}
							catch(Exception $e) {}
						}
						$save='ok';
					}
				} else {
					if($obPayment->save(false)) {
						//Сохраняем уведомления
						try {
							// если нет главной задачи
							if (!$obPackage->rm_issue_id) {
								Redmine::postIssue($obPackage);
							}
							// добавим в Redmine комментарий
							Redmine::updateIssue($obPackage->rm_issue_id, array(
								'notes'=>"h2. оплата отредактирована\n\n*сумма* - ".number_format($obPayment->amount, 0, ',', ' ')."руб., оплачено "
								.($obPackage->summ > 0 ? number_format(100 * $obPackage->paid / $obPackage->summ, 0).'%' : '0%')
								."\n\n*подробности:* ".$obPayment->description,
							));
						} catch(Exception $e) {}
						Logger::put(array(
							'client_id'=>$obPackage->client_id,'manager_id'=>Yii::app()->user->id,
							'info'=>'[auto] Оплата отредактирована, <b>'.number_format($obPayment->amount, 0, ',', ' ').'руб.</b>, дата оплаты <b>'.date('d.m.Y', strtotime($obPayment->dt)).'</b> <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">подробно</a>'
						));
						$save='ok';
					}
				}
			}
			$this->renderPartial('payform', array('package'=>$obPackage,'payment'=>$obPayment,'save'=>$save));
		} else {
			throw new CHttpException(400,'Bad Request');
		}
	}

	/**
	 * Метод выполняет удаление записи о платеже
	 * @param object $payment_id
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionDelete($payment_id) {
		if ($payment = Payment::model()->findByPk($payment_id)) {
			$package = $payment->package;
			$payment->delete();
			
			try {
				// если нет главной задачи
				if (!$package->rm_issue_id) {
					Redmine::postIssue($package);
				}
				// добавим в Redmine комментарий
				Redmine::updateIssue($package->rm_issue_id, array(
					'notes'=>"h2. оплата удалена\n\n*сумма* - ".number_format($payment->amount, 0, ',', ' ')."руб., оплачено "
					.($package->summ > 0 ? number_format(100 * $package->paid / $package->summ, 0).'%' : '0%')
					."\n\n*подробности:* ".$payment->description,
				));
			} catch(Exception $e) {}
			
			Logger::put(array(
				'client_id'=>$package->client_id,'manager_id'=>Yii::app()->user->id,
				'info'=>'[auto] Оплата заказа #'.$package->id.' удалена, <b>'.number_format($payment->amount, 0, ',', ' ').'руб.</b>, дата оплаты <b>'.date('d.m.Y', strtotime($payment->dt)).'</b>. Пподробности: '.$payment->description
			));
			if(Yii::app()->getRequest()->isAjaxRequest) {
				echo json_encode(array('done'=>1));
			} else {
				$this->renderPartial('pays', array(
					'package'=>$package,
				));
			}
		} else {
			throw new CHttpException(404,'Payment not found');
		}
	}

	/**
	 * Метод выполняет операцию подтверждения платежа
	 * @param object $payment_id
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionApprove($payment_id) {
		if ($obPayment = Payment::model()->findByPk($payment_id)) {
			$obPackage = $obPayment->package;
			if ($obPayment->ptype_id == 0) {
				$obPayment->ptype_id = 1;
				$obPayment->save(false);
				
				// ставим нотайс менеджеру
				$obMe = Yii::app()->user;
				$obManager = $obPackage->manager;
				$obNotify=new ManagerNotifier();
				$obNotify->log='[auto] Подтверждена оплата к заказу #'.$obPackage->id.' на сумму <b>'.number_format($obPayment->amount, 2, ',', ' ').'руб.</b>, дата оплаты <b>'.date('d.m.Y', strtotime($obPayment->dt)).'</b> <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">подробно</a>';
				$obNotify->calendar='[auto] '.$obMe->fio.' подтвердила оплату для заказа <a href="http://sup.fabricasaitov.ru/manager#package_'.$obPackage->id.'_'.$obPackage->client_id.'">#'.$obPackage->id.'</a> - Оплата на сумму <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">'.$obPayment->amount.'</a> руб. <a href="http://sup.fabricasaitov.ru/manager#payment_'.$obPayment->id.'_'.$obPackage->id.'">Подробно</a>';
				$obNotify->manager_id=$obManager->id;
				$obNotify->client_id=$obPackage->client_id;
				$obNotify->Send();

				try {
					// если нет главной задачи
					if (!$obPackage->rm_issue_id) {
						Redmine::postIssue($obPackage);
					}
					Redmine::updateIssue($obPackage->rm_issue_id, array(
						'notes'=>"h2. оплата подтверждена\n\n*сумма* - ".number_format($obPayment->amount, 0, ',', ' ')."руб., оплачено "
						.($obPackage->summ > 0 ? number_format(100 * $obPackage->paid/$obPackage->summ, 0).'%' : '0%')
						."\n\n*подробности:* ".$obPayment->description,
					));
				} catch(Exception $e) {}
			}
			// данные для замены аяксом
			$this->renderPartial('pays', array(
				'package'=>$obPackage,
			));
		} else {
			throw new CHttpException(404,'Payment not found');
		}
	}
}

