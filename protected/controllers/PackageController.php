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
				'allow',
					'actions'=>array(
					'index',
					'view',
					'save',
					'edit',
					'addPay',
					'takePack',
					'addRedmineMessage',
					'bindRedmineIssue',
					'decline',
					'createAllRedmineIssues',
					'newRedmineIssue',
					'closeRedmineIssue',
					'getPayForm'
				),
					'roles'=>array(
					'admin',
					'moder',
					'topmanager',
					'manager',
					'master'
				),
					
			),
					array(
				'deny',
					'users'=>array(
					'*'
				)
			)
		);
	}
	
	/**
	 *
	 * @param object $id [optional]
	 * @return
	 */
	public function actionIndex($id = false) {
	
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
	
	/**
	 *
	 * @param object $client_id
	 * @param object $package_id
	 * @return
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
				'package_id'=>$package_id,'pack'=>$pack
			));
		else if ($client_id || $package_id)
			$this->renderPartial('view', array(
				'package_id'=>$package_id,'client_id'=>$client_id
			));
		else
			return false;
	}
	
	/**
	 *
	 * @return
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
					'client_id'=>$pack->client_id,'manager_id'=>Yii::app()->user->id,'info'=>$info
				));
				// Action
				$pack->manager_id = $newManager;
			}
			$pack->dt_change = date('Y-m-d H:i:s');
			$pack->save();
			
		}
		
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$pack->client_id
		));
	}
	
	/**
	 *
	 * @return
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
				'client_id'=>$pack->client_id,'manager_id'=>Yii::app()->user->id,'info'=>$info
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
			
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$pack->client_id
		));
	}
	
	/**
	 *
	 * @param object $id
	 * @return
	 */
	public function actionTakePack($id) {
		$package = Package::model()->findByPk($id);
		// Не перехватил-ли заказ другой менеджер?
		if ($package->manager_id == 0 or $package->manager_id == Yii::app()->user->id) {
			$package->manager_id = Yii::app()->user->id;
			$package->status_id = 17;
			$package->dt_change = date('Y-m-d H:i:s');
			$package->save();
		}
		
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/**
	 *
	 * @param object $id
	 * @return
	 */
	public function actionDecline($id) {
		$package = Package::model()->findByPk($id);
		
		// нельзя отклонить заказ
		if ($package->status_id < 20) {
			$package->manager_id = 0;
			$package->status_id = 15;
			$package->save();
		}
		
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/**
	 *
	 * @param object $summ
	 * @param object $ulid
	 * @param object $package_id
	 * @return
	 */
	public function actionGetPayForm($summ, $ulid, $package_id) {
		$this->renderPartial('payform', array(
			'summ'=>$summ,'ulid'=>$ulid,'package_id'=>$package_id
		));
	}
	
	/**
	 *
	 * @param object $package_id
	 * @param object $summa
	 * @param object $message [optional]
	 * @param object $noReporting [optional]
	 * @return
	 */
	public function actionAddPay($package_id, $summa, $message = null, $noReporting = false) {
		$package = Package::model()->findByPk($package_id);
		
		// создаем задачу в Redmine, если её еще нет
		if (!$package->redmine_proj) {
			$rmManager = Redmine::getUserByLogin(Yii::app()->user->login);
			$rmProject = Redmine::getProjectByIdentifier('sites');
			
			$subject = "#{$package->id} {$package->name} для {$package->client->mail}";
			
			$description = "h1. $subject\n\n";
			$description .= "h2. примечания:\n\n{$package->descr}\n\n";
			$description .= "h2. сумма".number_format($service->summa, 0, ',', ' ')."руб.\n";
			
			try {
				$issue = Redmine::createIssue(array(
					// в каком проекте создать задачу
					'project_id'=>$rmProject['id'],
					// параметры задачи
					'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
					// кто назначил и кому наначено
					'author_id'=>$rmManager['id'],'assigned_to_id'=>$rmManager['id'],
					// родительская задача
					//'parent_id'=>0,
					// тема и описание
					'subject'=>$subject,'description'=>$description,
					// когда начата и когда должна быть закончена
					'start_date'=>date('Y-m-d', strtotime($package->dt_beg)),'due_date'=>date('Y-m-d', strtotime($package->dt_beg)),
					// время на выполнение и потраченное время
					'estimated_hours'=>'0.0','spent_hours'=>'0.0'
				));
			}
			catch(Exception $e) {
				$this->renderPartial('index', array(
					'client_id'=>$package->client_id
				));
				return;
			}
			
			$package->redmine_proj = @$issue['id'];
		}
		
		// добавим в Redmine комментарий об оплате
		try {
			Redmine::updateIssue($package->redmine_proj, array(
				// сообщение
				'notes'=>"h2. поступила оплата\n\nсумма - *".number_format($summa, 0, ',', ' ')."руб.*",
			));
		}
		catch(Exception $e) {
		}
		
		// создадим оплату
		$pay = new Payment();
		$pay->name = 'Оплата заказа '.$package->name;
		$pay->dt = date('Y-m-d');
		$pay->package_id = $package->id;
		$pay->amount = abs($summa);
		$pay->debit = $summa > 0 ? 1 : - 1;
		$pay->rekvizit_id = 0;
		$pay->ptype_id = $noReporting ? 1 : null;
		$pay->description = $message;
		$pay->save();
		
		Logger::put(array(
			'client_id'=>$package->client_id,'manager_id'=>Yii::app()->user->id,
			//
			'info'=>'<p>оплачено <strong>'.number_format($summa, 0, ',', ' ').'руб.</strong></p><p><strong>подробности:</strong> '.$message
		));

		
		// обновим заказ
		
		$package->status_id = $summa >= ($package->summa - $package->paid) ? 30 : 20;
		$package->paid += $summa;
		$package->dt_change = date('Y-m-d H:i:s');
		
		$package->save();
		
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/**
	 *
	 * @param object $pack_id
	 * @param object $serv_id [optional]
	 * @param object $master_id
	 * @return
	 */
	public function actionNewRedmineIssue($pack_id, $serv_id = 0, $master_id) {
		$package = Package::model()->findByPk($pack_id);
		
		// если нет главной задачи - создаем
		if (!$package->redmine_proj) {
			$rmManager = Redmine::getUserByLogin(Yii::app()->user->login);
			$rmProject = Redmine::getProjectByIdentifier('sites');
			
			$subject = "#{$package->id} {$package->name} для {$package->client->mail}";
			
			$description = "h1. $subject\n\n";
			$description .= "h2. примечания:\n\n{$package->descr}\n\n";
			$description .= "h2. сумма".number_format($service->summa, 0, ',', ' ')."руб.";
			
			try {
				$issue = Redmine::createIssue(array(
					// в каком проекте создать задачу
					'project_id'=>$rmProject['id'],
					// параметры задачи
					'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
					// кто назначил и кому наначено
					'author_id'=>$rmManager['id'],'assigned_to_id'=>$rmManager['id'],
					// родительская задача
					//'parent_id'=>0,
					// тема и описание
					'subject'=>$subject,'description'=>$description,
					// когда начата и когда должна быть закончена
					'start_date'=>date('Y-m-d', strtotime($package->dt_beg)),'due_date'=>date('Y-m-d', strtotime($package->dt_beg)),
					// время на выполнение и потраченное время
					'estimated_hours'=>'0.0','spent_hours'=>'0.0'
				));
			}
			catch(Exception $e) {
				$this->renderPartial('issue', array(
					'issue_id'=>0,'pack_id'=>$pack_id,'serv_id'=>$serv_id
				));
				return;
			}
			
			$package->redmine_proj = @$issue['id'];
		}
		
		if ($serv_id and !$service->to_redmine) {
			$service = Serv2pack::getByIds($serv_id, $pack_id);
			
			// если задача уже существует, то ничего не создаём
			if ($service->to_redmine == 0) {
				$rmManager = Redmine::getUserByLogin(Yii::app()->user->login);
				$rmMaster = Redmine::readUser($master_id);
				$rmProject = Redmine::getProjectByIdentifier('sites');
				
				$subject = "#{$package->id} {$service->service->name} для {$package->client->mail}";
				
				$description = "h1. $subject\n\n";
				
				if (isset($package->site)) {
					$description .= "h2. сайт:* {$package->site->url}\n\n";
					$description .= "*хост:* {$package->site->host}\n";
					$description .= "*ftp:* {$package->site->ftp}\n";
					$description .= "*db:* {$package->site->db}\n";
					//$description .=  "*старт:* {$package->site->dt_beg}\n";
					//$description .=  "*финиш:* {$package->site->dt_end}\n";
					$description .= "\n";
				}
				$description .= "h2. примечания:\n\n{$service->descr}\n\n";
				$description .= "h2. стоимость".number_format($service->price, 0, ',', ' ')."руб.";
				
				try {
					$issue = Redmine::createIssue(array(
						// в каком проекте создать задачу
						'project_id'=>$rmProject['id'],
						// параметры задачи
						'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
						// кто назначил и кому наначено
						'author_id'=>$rmManager['id'],'assigned_to_id'=>$master_id,
						// родительская задача
						'parent_id'=>$package->redmine_proj,
						// тема и описание
						'subject'=>$subject,'description'=>$description,
						// когда начата и когда должна быть закончена
						'start_date'=>date('Y-m-d', strtotime($service->dt_beg)),'due_date'=>date('Y-m-d', strtotime($service->dt_beg)),
						// время на выполнение и потраченное время
						'estimated_hours'=>$service->service->duration,'spent_hours'=>'0.0',
						// XXX вид деятельности - исследовать это поле
						//'time_entry_activity_id'=>
					));
				}
				catch(Exception $e) {
				}
				
				$service->to_redmine = @$issue['id'];
				$service->save();
				
				// добавляем в главную задачу комментарий об этом действии
				
				$masterFullName = @$rmMaster['firstname'].' '.@$rmMaster['lastname'];
				try {
					Redmine::updateIssue($package->redmine_proj, array(
						// сообщение
						'notes'=>"h2. поставлена задача\n\nисполнитель - \"{$masterFullName}\":https://redmine.fabricasaitov.ru/users/{$rmMaster['id']}, задача #{$service->to_redmine}",
						// потраченное время (прибавляется)
						'spent_hours'=>$service->service->duration
					));
				}
				catch(Exception $e) {
				}
			}
		}
		
		$allIssuesExist = true;
		
		foreach ($package->servPack as $service) {
			if (!$service->to_redmine) {
				$allIssuesExist = false;
				break;
			}
		}
		
		// Если всё распределено, то меняем статус проекта на 50 - всё в работе.
		if ($allIssuesExist)
			$package->status_id = 50;

			
		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();
		
		$this->renderPartial('issue', array(
			'issue_id'=>$issue['id'],'pack_id'=>$pack_id,'serv_id'=>$serv_id
		));
	}
	
	/**
	 *
	 * @param object $id
	 * @return
	 */
	public function actionCreateAllRedmineIssues($id) {
		$package = Package::model()->findByPk($id);
		
		// если нет главной задачи - создаем
		if (!$package->redmine_proj) {
			$rmManager = Redmine::getUserByLogin(Yii::app()->user->login);
			$rmProject = Redmine::getProjectByIdentifier('sites');
			
			$subject = "#{$package->id} {$package->name} для {$package->client->mail}";
			
			$description = "h1. $subject\n\n";
			$description .= "h2. примечания:\n\n{$package->descr}\n\n";
			$description .= "h2. сумма".number_format($service->summa, 0, ',', ' ')."руб.";
			
			try {
				$issue = Redmine::createIssue(array(
					// в каком проекте создать задачу
					'project_id'=>$rmProject['id'],
					// параметры задачи
					'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
					// кто назначил и кому наначено
					'author_id'=>$rmManager['id'],'assigned_to_id'=>$rmManager['id'],
					// родительская задача
					//'parent_id'=>0,
					// тема и описание
					'subject'=>$subject,'description'=>$description,
					// когда начата и когда должна быть закончена
					'start_date'=>date('Y-m-d', strtotime($package->dt_beg)),'due_date'=>date('Y-m-d', strtotime($package->dt_beg)),
					// время на выполнение и потраченное время
					'estimated_hours'=>'0.0','spent_hours'=>'0.0'
				));
			}
			catch(Exception $e) {
				// данные для замены аяксом
				$this->renderPartial('index', array(
					'client_id'=>$package->client_id
				));
				return;
			}
			
			$package->redmine_proj = @$issue['id'];
		}
		
		// распределяем нераспределенные сервисы
		
		$rmManager = Redmine::getUserByLogin(Yii::app()->user->login);
		$rmProject = Redmine::getProjectByIdentifier('sites');
		
		foreach ($package->servPack as $service) {
			if (!$service->to_redmine) {
				// если не указан мастер, ставим менеджера мастером
				$rmMaster = Redmine::getUserByLogin(isset($service->master->login) ? $service->master->login : $rmManager['login']);

				
				$subject = "#{$package->id} {$service->service->name} для {$package->client->mail}";
				
				$description = "h1. $subject\n\n";
				
				if (isset($package->site)) {
					$description .= "h2. сайт:* {$package->site->url}\n\n";
					$description .= "*хост:* {$package->site->host}\n";
					$description .= "*ftp:* {$package->site->ftp}\n";
					$description .= "*db:* {$package->site->db}\n";
					//$description .=  "*старт:* {$package->site->dt_beg}\n";
					//$description .=  "*финиш:* {$package->site->dt_end}\n";
					$description .= "\n";
				}
				$description .= "h2. примечания:\n\n{$service->descr}\n\n";
				$description .= "h2. стоимость".number_format($service->price, 0, ',', ' ')."руб.";
				
				try {
					$issue = Redmine::createIssue(array(
						// в каком проекте создать задачу
						'project_id'=>$rmProject['id'],
						// параметры задачи
						'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
						// кто назначил и кому наначено
						'author_id'=>$rmManager['id'],'assigned_to_id'=>$master_id,
						// родительская задача
						'parent_id'=>$package->redmine_proj,
						// тема и описание
						'subject'=>$subject,'description'=>$description,
						// когда начата и когда должна быть закончена
						'start_date'=>date('Y-m-d', strtotime($service->dt_beg)),'due_date'=>date('Y-m-d', strtotime($service->dt_beg)),
						// время на выполнение и потраченное время
						'estimated_hours'=>$service->service->duration,'spent_hours'=>'0.0',
						// XXX вид деятельности - исследовать это поле
						//'time_entry_activity_id'=>
					));
				}
				catch(Exception $e) {
					continue;
				}
				
				// сохраняем
				$service->to_redmine = @$issue['id'];
				$service->save();
				
				// добавляем в главную задачу комментарий об этом действии
				
				$masterFullName = @$rmMaster['firstname'].' '.@$rmMaster['lastname'];
				try {
					Redmine::updateIssue($package->redmine_proj, array(
						// сообщение
						'notes'=>"h2. поставлена задача\n\nисполнитель - \"{$masterFullName}\":https://redmine.fabricasaitov.ru/users/{$rmMaster['id']}, задача #{$service->to_redmine}",
						// потраченное время (прибавляется)
						'spent_hours'=>$service->service->duration
					));
				}
				catch(Exception $e) {
				}
			}
		}
		
		$package->status_id = 50;
		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();
		
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}
	
	/**
	 *
	 * @return
	 */
	public function actionAddRedmineMessage() {
	
		$issue_id = (int) @$_POST['id'];
		$package_id = (int) @$_POST['pack'];
		$serv_id = (int) @$_POST['pack'];
		$message = (string) @$_POST['message'];
		
		try {
			Redmine::updateIssue($issue_id, array(
				// комментарий
				'notes'=>$message,
			));
		}
		catch(Exception $e) {
			// данные для замены аяксом
			$this->renderPartial('issue', array(
				'issue_id'=>$issue_id,'pack_id'=>$package_id,'serv_id'=>$serv_id
			));
			return;
		}
		
		$package = Package::model()->findByPk($package_id);
		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();
		
		// данные для замены аяксом
		$this->renderPartial('issue', array(
			'issue_id'=>$issue_id,'pack_id'=>$package_id,'serv_id'=>$serv_id
		));
	}
	
	/**
	 *
	 * @param object $issue_id
	 * @param object $pack_id
	 * @param object $serv_id [optional]
	 * @return
	 */
	public function actionBindRedmineIssue($issue_id, $pack_id, $serv_id = false) {
	
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
	
	/**
	 *
	 * @param object $issue_id
	 * @param object $pack_id
	 * @param object $serv_id
	 * @return
	 */
	public function actionCloseRedmineIssue($issue_id, $pack_id, $serv_id) {
		try {
			Redmine::updateIssue($issue_id, array(
				'done_ratio'=>100,'status_id'=>8
			));
		}
		catch(Exception $e) {
			$this->renderPartial('issue', array(
				'issue_id'=>$issue_id,'pack_id'=>$pack_id
			));
			return;
		}
		
		$serv2pack = Serv2pack::getByIds($serv_id, $pack_id);
		$serv2pack->dt_end = date('Y-m-d H:i:s');
		$serv2pack->save();
		
		$pack = Package::model()->findByPk($pack_id);
		$pack->dt_change = date('Y-m-d H:i:s');
		$pack->save();
		
		$this->renderPartial('issue', array(
			'issue_id'=>$issue_id,'pack_id'=>$pack_id
		));
	}
}
