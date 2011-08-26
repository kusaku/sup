<?php 
class PackageController extends Controller {

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
					'index', 'view', 'save', 'addPay', 'takePack', 'addRedmineMessage', 'bindRedmineIssue', 'decline',
					//
					'createAllRedmineIssues', 'newRedmineIssue', 'closeRedmineIssue', 'getPayForm',
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
	
	/*
	 *
	 */
	public function actionIndex($id = 0) {
	
		if ($id) {
			$this->renderPartial('index', array(
				'client_id'=>$id
			));
			
		} else {
			// у админа выводятся все проекты
			if (Yii::app()->user->checkAccess('admin')) {
				// нам нужно получить людей, последние прокты которых менялись
				$clients = People::model()->findAll(array(
					'select'=>'id',
					// это работает не так, как ожидалось
					//'limit'=>30,
					'with'=>array(
						'packages'=>array(
							// сами проекты нам не нужны
							'select'=>false,
							// этим убираем несуществующие прокты и клиентов
							'joinType'=>'INNER JOIN',
							// но нужно выбрать только людей с измененными проектами, которые не в архиве и не сданы
							'condition'=>"packages.status_id NOT IN (15, 999)",
							// по дате проекта, как нормально подставить LIMIT - не нашел =(
							'order'=>'packages.dt_change DESC LIMIT 30',
						)
					)
				));
			}
			// у менеджера выводятся только его проекты
			else {
				$myid = Yii::app()->user->id;
				$clients = People::model()->findAll(array(
					'select'=>'id',
					// это работает не так, как ожидалось
					//'limit'=>30,
					'with'=>array(
						'packages'=>array(
							// сами проекты нам не нужны
							'select'=>false,
							// этим убираем несуществующие прокты и клиентов
							'joinType'=>'INNER JOIN',
							// но нужно выбрать которые не в архиве и не сданы, и принадлежат менеджеру
							'condition'=>"packages.status_id NOT IN (15, 999) AND (packages.manager_id = 0 OR packages.manager_id = $myid)",
							// по дате проекта, как нормально подставить LIMIT - не нашел =(
							'order'=>'packages.dt_change DESC LIMIT 30',
						)
					)
				));
			}
			
			// в цикле рекурсивно это вызываем представление
			foreach ($clients as $client) {
				$this->renderPartial('index', array(
					'client_id'=>$client->primaryKey
				));
			}
		}
	}
	
	/*
	 *
	 */
	public function actionView($client_id, $package_id) {
	
		$status = 0;
		
		if ($package_id) {
			$pack = Package::model()->findByPk($package_id);
			$status = $pack->status_id;
		}
		
		// Больше его нельзя редактировать - только читать.
		if ($status > 17)
			$this->renderPartial('read', array(
				'package_id'=>$package_id, 'pack'=>$pack
			));
		else if ($client_id || $package_id)
			$this->renderPartial('view', array(
				'package_id'=>$package_id, 'client_id'=>$client_id
			));
		else
			return false;
	}
	
	/*
	 * Сохраняем заказ (пакет) со всеми заказанными услугами
	 */
	public function actionSave() {
	
		$client = People::GetById($_POST['pack_client_id']);
		
		// Если заказ по контактному лицу, вешаем заказ на клиента, на не на это контактное лицо.
		if (! empty($client->parent_id)) {
			$_POST['pack_client_id'] = $client->parent_id;
			$_POST['pack_descr'] = 'Контактное лицо: '.$client->fio."\n".'Телефон: '.$client->phone."\n".'EMail: '.$client->mail."\n".$_POST['pack_descr'];
		}
		
		if ($_POST['pack_id']) {
			$pack = Package::model()->findByPk($_POST['pack_id']);
		} else {
			$pack = new Package();
			
			// для нового заказа
			// Не оплачен
			$pack->status_id = 17;
			// Дата начала
			$pack->dt_beg = date('Y-m-d H:i:s');
			$pack->client_id = $_POST['pack_client_id'];
			$pack->manager_id = Yii::app()->user->id;
		}
		
		//	Был запрос на создание нового сайта
		if (array_key_exists('site_add_new', $_POST)) {
			if ($_POST['site_url']) {
				$site = Site::getByUrl($_POST['site_url']);
				if (!$site) {
					$site = new Site();
				}
				$site->url = $_POST['site_url'];
				$site->host = $_POST['site_host'];
				$site->ftp = $_POST['site_ftp'];
				$site->db = $_POST['site_db'];
				$site->client_id = $_POST['pack_client_id'];
				$site->save();
				
				$pack->site_id = $site->id;
			} else {
				$pack->site_id = 0;
			}
		} else {
			$pack->site_id = $_POST['pack_site_id'];
		}
		
		$newManager = @$_POST['newManager'];
		
		// отдаём другому менеджеру
		if ($newManager and $newManager != $pack->manager_id) {
		
			// Log write
			$info = date('d-m-Y')." Передача заказа: ".People::getById($pack->manager_id)->fio.' -> '.People::getById($newManager)->fio."<br>";
			Logger::put(array(
				'client_id'=>$pack->client_id, 'manager_id'=>Yii::app()->user->id, 'info'=>$info
			));
			// Action
			$pack->manager_id = $newManager;
		}
		
		$pack->name = $_POST['pack_name'];
		$pack->descr = $_POST['pack_descr'];
		$pack->dt_change = date('Y-m-d H:i:s');
		$pack->summa = $_POST['pack_summa'];
		
		$pack->save();
		
		Serv2pack::delByPack($pack->id);
		
		if (isset($_POST['service']))
			foreach ($_POST['service'] as $id) {
				$s2p = new Serv2pack();
				$s2p->serv_id = $id;
				$s2p->pack_id = $pack->id;
				$s2p->quant = $_POST['count'][$id];
				$s2p->price = $_POST['price'][$id];
				$s2p->descr = $_POST['descr'][$id];
				$s2p->master_id = $_POST['master'][$id];
				$s2p->dt_beg = $_POST['dt_beg'][$id];
				$s2p->dt_end = $_POST['dt_end'][$id];
				$s2p->save();
			}
			
		$this->redirect('/');
	}
	
	/*
	 * Берём себе поступивший заказ
	 */
	public function actionTakePack($id) {
		$package = Package::model()->findByPk($id);
		// Не перехватил-ли заказ другой менеджер
		if ($package->manager_id == 0 or $package->manager_id == Yii::app()->user->id) {
			$package->manager_id = Yii::app()->user->id;
			$package->status_id = 17;
			$package->dt_change = date('Y-m-d H:i:s');
			$package->save();
		}
		
		// Возвращаем данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/*
	 * Отмечаем заказ как не нужный - в архив
	 */
	public function actionDecline($id) {
		$package = Package::model()->findByPk($id);
		$package->status_id = 15; // Отказ
		$package->save();
		
		// Возвращаем данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/*
	 *
	 */
	public function actionGetPayForm($summ, $ulid, $package_id) {
		$this->renderPartial('payform', array(
			'summ'=>$summ, 'ulid'=>$ulid, 'package_id'=>$package_id
		));
	}
	
	/*
	 * Отмечаем заказ как оплаченный. Создём задачу по самому заказу (родительскую задачу).
	 */
	public function actionAddPay($package_id, $summa, $message = null, $noReporting = false) {
		$package = Package::model()->findByPk($package_id);
		
		$package->descr = $package->descr."\nПодробности оплаты: ".$message;
		
		if ($message)
			$package->descr = $package->descr."\nПодробности оплаты: ".Yii::app()->request->getParam('message');
			
		Logger::put(array(
			'client_id'=>$package->client_id, 'manager_id'=>Yii::app()->user->id, 'info'=>'Оплачен заказ №'.$package->id."<br> Подробности: ".Yii::app()->request->getParam('message')
		));
		
		$usersArray = Redmine::getUsersArray();

		$package->status_id = $summa >= ($package->summa - $package->paid) ? 30 : 20;
		
		$pay = new Payment();
		$pay->name = 'Оплата заказа '.$package->name;
		$pay->dt = date('Y-m-d');
		$pay->package_id = $package->id;
		$pay->amount = abs($summa);
		$pay->debit = $summa > 0 ? 1 : - 1;
		$pay->rekvizit_id = 0;
		$pay->ptype_id = $noReporting ? 1 : 0;
		$pay->description = $message;
		$pay->save();
		
		if ($package->redmine_proj == 0) {
			$issue = Redmine::addIssue('Заказ №'.$package->id.' '.$package->name.' ('.$package->client->mail.')', $package->descr, $usersArray[trim((string) Yii::app()->user->login)], 0);
			$package->redmine_proj = $issue->id;
		}
		
		$package->paid += $summa;
		$package->dt_change = date('Y-m-d H:i:s');
		
		$package->save();
		
		// Возвращаем данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/*
	 * обновляем заказ (оплаченный). Можем поменять мастера или привязать сайт.
	 */
	public function actionEdit() {
	
		if ($_POST['pack_id']) {
			$pack = Package::model()->findByPk($_POST['pack_id']);
			
			//if ($pack->site_id == 0 && $_POST['pack_site_id'])
			//$pack->site_id = $_POST['pack_site_id'];
			
			//	Был запрос на создание нового сайта и к заказу сайт пока не привязан
			if ($pack->site_id == 0) {
				if (array_key_exists('site_add_new', $_POST)) {
					if ($_POST['site_url']) {
						$site = Site::getByUrl($_POST['site_url']);
						if (!$site) {
							$site = new Site();
							$site->url = $_POST['site_url'];
							$site->host = $_POST['site_host'];
							$site->ftp = $_POST['site_ftp'];
							$site->db = $_POST['site_db'];
							$site->client_id = $_POST['pack_client_id'];
							$site->save();
						}
						$pack->site_id = $site->id;
					}
				} elseif ($_POST['pack_site_id']) {
					$pack->site_id = $_POST['pack_site_id'];
				}
			}
			
			$newManager = @$_POST['newManager'];
			if ($newManager && $newManager != $pack->manager_id) { // отдаём другому менеджеру
				// Log write
				$info = date('d-m-Y')." Передача заказа: ".People::getById($pack->manager_id)->fio.' -> '.People::getById($newManager)->fio."<br>";
				Logger::put(array(
					'client_id'=>$pack->client_id, 'manager_id'=>Yii::app()->user->id, 'info'=>$info
				));
				// Action
				$pack->manager_id = $newManager;
			}
			$pack->dt_change = date('Y-m-d H:i:s');
			$pack->save();
			
		}
		
		$this->redirect('/');
	}
	
	/*
	 * Создаём задачу по запросу. Если нет родительской, то и её создаём.
	 */
	public function actionNewRedmineIssue($pack_id, $serv_id = 0, $master_id) {
		$package = Package::model()->findByPk($pack_id);
		
		$usersArray = Redmine::getUsersArray();
		
		if (!$package->redmine_proj) {
			$issue = Redmine::addIssue('Заказ №'.$package->id.' '.$package->name, $package->descr, $usersArray[trim((string) Yii::app()->user->login)], 0);
			$package->redmine_proj = $issue->id;
		}
		
		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();
		
		if ($serv_id) {
			$service = Serv2pack::getByIds($serv_id, $pack_id);
			
			// Если задача уже существует, то ничего не создаём
			if ($service->to_redmine == 0) {
				$siteInfo = isset($package->site) ? 'Сайт: http://'.$package->site->url."\n"."Доступы: \n".'* HOST: '.$package->site->host."\n".'* FTP: '.$package->site->ftp."\n".'* DB: '.$package->site->db."\n" : 'В заказе сайт не указан';
				
				$issue = Redmine::addIssue(
				// Название
				'№'.$package->id.' '.$service->service->name.' ('.$package->client->mail.')',
				// Описание
				'Задача по заказу №'.$package->id.".\n Предмет заказа: ".$service->service->name.". \n".'Примечание: '.$service->descr." \n".'Стоимость: '.$service->price." \n".'Клиент: '.$package->client->mail." \n".$siteInfo,
				// Кому назначена
				$master_id,
				// Родительская задача
				$package->redmine_proj);
				
				$service->to_redmine = $issue->id;
				$service->save();
			}
		}
		
		// Проверяем, не пора-ли помянять статус задачи на 50 - все заказанные услуги в работе.
		// Допустим, что все задачи уже созданы
		$allIssuesExist = true;
		
		// И даже родительская задача по всему заказу.
		$allIssuesExist = (bool)$package->redmine_proj;
		
		// Если задача не создана
		foreach ($package->servPack as $service) {
			if ((int) $service->to_redmine == 0) {
				$allIssuesExist = false;
				break;
			}
		}
		
		if ($allIssuesExist) { // Если всё распределено, то меняем статус проекта на 50 - всё в работе.
			$package->status_id = 50;
			$package->save();
		}
		
		$this->renderPartial('issue', array(
			'issue_id'=>$issue->id, 'pack_id'=>$pack_id
		));
	}
	
	/*
	 * Отдать в работу весь заказ - создать задачи по всем заказанным услугам.
	 */
	public function actionCreateAllRedmineIssues($id) {
		$package = Package::model()->findByPk($id);
		
		$usersArray = Redmine::getUsersArray();
		
		$package->status_id = 50;
		$package->dt_change = date('Y-m-d H:i:s');
		
		if (!$package->redmine_proj) {
			$issue = Redmine::addIssue('Заказ №'.$package->id.' '.$package->name.' ('.$package->client->mail.')', $package->descr, $usersArray[trim((string) Yii::app()->user->login)], 0);
			$package->redmine_proj = $issue->id;
		}
		
		$package->save();
		
		foreach ($package->servPack as $service) {
			$master = @$service->master->login ? $usersArray[trim(mb_strToLower($service->master->login))] : 0;
			if (!$service->to_redmine) {
				$issue = Redmine::addIssue(
				// Название
				'№'.$package->id.' '.$service->service->name,
				// Описание
				'Задача по проекту #'.$package->id.'. Предмет заказа: '.$service->service->name.'.',
				// Кому назначена
				$master,
				// Родительская задача
				$package->redmine_proj);
				
				$service->to_redmine = $issue->id;
				$service->save();
			}
		}
		
		// Возвращаем данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}

	
	/*
	 * Добавляем в Redmine новое сообщение
	 */
	public function actionAddRedmineMessage($id, $message, $pack) {
		Redmine::addNoteToIssue($id, $message);
		$pack = Package::model()->findByPk($pack);
		$pack->dt_change = date('Y-m-d H:i:s');
		$pack->save();
		
		// Возвращаем данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/*
	 *
	 */
	public function actionBindRedmineIssue($issue_id, $pack_id, $serv_id = 0) {
	
		if ($issue_id && $pack_id && $serv_id) {
			$s2p = Serv2pack::getByIds($serv_id, $pack_id);
			$s2p->to_redmine = $issue_id;
			$s2p->save();
		} elseif ($issue_id && $pack_id && !$serv_id) {
			$pack = Package::model()->findByPk($pack_id);
			$pack->redmine_proj = $issue_id;
			$pack->save();
		}
	}
	
	/*
	 *
	 */
	public function actionCloseRedmineIssue($issue_id, $pack_id, $serv_id) {
		if (Redmine::closeIssue($issue_id) == true) {
			$serv2pack = Serv2pack::getByIds($serv_id, $pack_id);
			$serv2pack->dt_end = date('Y-m-d H:i:s');
			$serv2pack->save();
			$pack = Package::model()->findByPk($pack_id);
			$pack->dt_change = date('Y-m-d H:i:s');
			
			$pack->save();
			$this->renderPartial('issue', array(
				'issue_id'=>$issue_id, 'pack_id'=>$pack_id
			));
		} else {
			print 0;
		}
	}
}
