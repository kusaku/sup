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
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow','actions'=>array(
					'index','view','save','edit','addPay','takePack','addRedmineMessage','bindRedmineIssue',
						'decline','createAllRedmineIssues','newRedmineIssue','closeRedmineIssue','getaddPay',
						'packageIsReady','questionnaire'
				),'roles'=>array(
					'admin','moder','topmanager','manager',
				)
			),array(
				'allow','actions'=>array(
					'index','view'
				),'roles'=>array(
					'leadmaster','master','marketolog'
				)
			),array(
				'deny','users'=>array(
					'*'
				)
			)
		);
	}

	/**
	 * Метод обеспечивает вывод списка заказов
	 * @param string $search
	 * @param int    $page
	 * @param int    $limit
	 *
	 * @internal param object $id [optional]
	 * @return void
	 */

	public function actionIndex($search = '', $page = 0, $limit = 20) {
		$count = 0;
		if(is_numeric($search)) {
			$people=array(
				array(
					'id'=>$search
				)
			);
			$count=1;
		} else {
			$people = People::search($search, $page, $limit, $count);
		}
		// если есть что
		if ($count > 0) {
			$this->renderPartial('header');
			// то в цикле рекурсивно это вызываем представление
			foreach ($people as $client) {
				$this->renderPartial('index', array(
					'client_id'=>$client['id']
				));
			}
			if ($count > $limit) {
				$this->widget('manager.widgets.PackagePaginatorWidget', array(
					'search'=>$search,'current'=>$page,'total'=>ceil($count / $limit),
				));
			}
			
		} else {
			// иначе выводим пустоту
			$this->renderPartial('index');
		}
	}

	/**
	 *
	 * @param object $client_id
	 * @param object $package_id
	 * @return
	 */

	public function actionView($id = 0, $client_id = 0) {
	
		if (!$pack = Package::model()->findByPk($id)) {
			$pack = new Package();
			$pack->client_id = $client_id;
			$pack->status_id = 17;
			$pack->manager_id = 17;
			$pack->dt_beg = date('Y-m-d H:i:s');
		}
		
		$obJurPersonReference = JurPersonReference::model();
		$arJurPersons = $obJurPersonReference->findAllByAttributes(array(
			'internal'=>1
		));
		
		// если не отдан в работу и оплата еще не пришла, открываем форму редактирования
		if ($pack->status_id < 20 and $pack->payment_id < 20) {
			// создание или редактирование
			$this->renderPartial('edit', array(
				'pack'=>$pack,'jur_reference'=>$arJurPersons
			));
		} elseif ($id or $client_id) {
			// просмотр
			$this->renderPartial('view', array(
				'pack'=>$pack,'jur_reference'=>$arJurPersons
			));
		} else {
			throw new CHttpException(500, 'Не указан ID пакета и ID клиента');
		}
	}

	/**
	 * Действие выполняет сохранение пакета заказа
	 * @return void
	 */
	public function actionSave() {
		$pack = Package::model()->findByPk($_POST['pack_id']) or $pack = new Package();

		// обязан существовать
		$client = People::model()->findByPk($_POST['client_id']);
		
		// перевесить заказ на основного клиента
		if (isset($_POST['set_to_parent'])) {
			while ($client->parent) {
				$client = $client->parent;
			}
		}
		
		$pack->client_id = $client->primaryKey;
		
		// отдаём другому менеджеру
		if (isset($_POST['people_id']['manager']) and ($manager_id = $_POST['people_id']['manager']) > 0) {
			$pack->setManager($manager_id);
		} elseif ($pack->isNewRecord) {
			$pack->setManager(Yii::app()->user->id);
		}
		
		// XXX тут может случиться перепривязка, ибо циклические зависимости
		// создание сайта при $_POST['site_url']
		if (isset($_POST['site_url'])) {
			$site = Site::getByUrl($_POST['site_url']);
			if (!$site) {
				$site = new Site();
				$site->url = $_POST['site_url'];
				$site->host = $_POST['site_host'];
				$site->ftp = $_POST['site_ftp'];
				$site->db = $_POST['site_db'];
				$site->client_id = $pack->client_id;
				$site->save();
				$pack->site_id = $site->primaryKey;
			} elseif (!$site->client_id) {
				// Если заданы параметры доступа, то сохраняем новые значения.
				// Иначе оставляем то, что есть.
				if ($_POST['site_host'])
					$site->host = $_POST['site_host'];
				if ($_POST['site_ftp'])
					$site->ftp = $_POST['site_ftp'];
				if ($_POST['site_db'])
					$site->db = $_POST['site_db'];

				$site->client_id = $pack->client_id;
				$site->save();
				$pack->site_id = $site->primaryKey;
			}
		}
		// изменение сайта при $_POST['site_id']
		elseif (isset($_POST['site_id']) and $site = Site::model()->findByPk($_POST['site_id'])) {
			$site->client_id = $pack->client_id;
			$site->save();
			$pack->site_id = $site->primaryKey;
		}

		// присвоение заказу промокода или сброс его
		if (! empty($_POST['pack_promocode'])) {
			// ищем промокод
			$promocode = Promocode::model()->findByAttributes(array(
				'code'=>$_POST['pack_promocode']
			))
			// если не нашли - создаем новый
			or $promocode = new Promocode();
			$promocode->code = $_POST['pack_promocode'];
			$promocode->save();
			$pack->promocode_id = $promocode->primaryKey;
		} else {
			$pack->promocode_id = NULL;
		}

		//Обрабатываем инфокоды
		$obInfocode=false;
		if (! empty($_POST['pack_promocode'])) {
			$sValue = htmlspecialchars($_POST['pack_promocode'],ENT_QUOTES,'utf-8',false);
			$obInfocode = Infocode::model()->with('partner')->findByAttributes(array('value'=>$sValue));

			if($obInfocode){
				//Добавляем инфокод заказу
				$pack->infocode_id = $obInfocode->primaryKey;

				//Приписываем инфокод к клиенту, если у него еще нет инфокода и если он не партнер
				$obUser = People::model()->with('partner_data','infocode','owner_partner')->findByPk($pack->client_id);
				if(!$obUser->partner_data){
					if(!$obUser->infocode){
						$obUser->infocode_id = $obInfocode->primaryKey;
						$obUser->save();
					}
				}

				//приписываем клиента партнеру, если он еще ни к кому не приписан и если инфокод является партнерским
				if(!$obUser->owner_partner && $obInfocode->partner){
					$obParnterPeople = new PartnerPeople();
					$obParnterPeople->id_client = $obUser->primaryKey;
					$obParnterPeople->id_partner = $obInfocode->partner['id'];
					$obParnterPeople->save();
				}
			}
		}

		//Проверяем, есть ли у клиента партнер и приписываем заказу процент партнера
		//Так как $obUser->owner_partner после записи $obParnterPeople
		//не обновляется, следовательно делаем отдельный запрос.
		$obPartnerPeople2 = PartnerPeople::model()->with('partner','partner.partner_data')->findByAttributes(array('id_client'=>$pack->client_id));
		if($obPartnerPeople2){
			$pack->partner_percent = $obPartnerPeople2->partner->partner_data['percent'];
		}

		// если всё было в работе и основная задача готова - ставим статус "готово"
		if (Redmine::getIssuePercent($pack->rm_issue_id) == 100 and $pack->status_id = 50) {
			$pack->status_id = 60;
		}

		// изменение статусов
		if (isset($_POST['status_id']) and $_POST['status_id'] > 0) {
			$pack->status_id = $_POST['status_id'];
		}
		if (isset($_POST['payment_id']) and $_POST['payment_id'] > 0) {
			$pack->payment_id = $_POST['payment_id'];
		}

		$pack->name = $_POST['pack_name'];
		$pack->descr = $_POST['pack_descr'];
		
		//Исправление бага 17074
		$pack->dt_beg = $pack->isNewRecord ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($_POST['pack_dt_beg']));
		//$pack->dt_end = date('Y-m-d H:i:s', strtotime($_POST['pack_dt_end']));
		$pack->dt_change = date('Y-m-d H:i:s');

		// Перерасчет суммы и периода
		
		$pack->summ = 0;
		$pack->period = 0;
		
		// нужно быть увереным, что уже есть ID
		if ($pack->isNewRecord) {
			$pack->payment_id=17;
			$pack->save();
			//если новая запись, то проверяем отправляли ли пользователю пароль, а затем
			//сохраняем созданный пакет
			if ($pack->client->psw == '' || $pack->client->psw == md5($pack->client->mail)) {
				$sOpenPassword = People::genPassword();
				$pack->client->psw = People::hashPassword($sOpenPassword);
				if ($pack->client->login == '')
					$pack->client->login = $pack->client->mail;
				if ($pack->client->save()) {
					//Отправляем письмо и сохраняем его в лог только если удалось сохранить его данные
					try {
						Yii::app()->getComponent('documents')->createEmailRegister($pack->client, $sOpenPassword)->send($pack->client->mail, $pack->client->fio);
					}
					catch(exception $e) {
					}
				}
			}
			// XXX пакет в любом случае сохраниться, валидации то нет!
			// Если пакет создали, отправляем уведомление о заказе
			// Формируем и отправляем письмо
			try {
				Yii::app()->getComponent('documents')->createEmailNewPackageManager($pack)->send($pack->client->mail, $pack->client->fio);
			}
			catch(exception $e) {
			}
		}

		if(!$obInfocode && isset($_REQUEST['promocode'])){
			//TODO: сделать отправку письма о ненайденном инфокоде
			// после перехода на инфокоды вместо промокодов
			Yii::log('There is no such Infocode: "'. $_REQUEST['promocode'] .'". Package ID: '. $pack->primaryKey, CLogger::LEVEL_INFO, 'manager.package.save');
		}

		$pack->summ = 0;

		$arMessages=array();
		foreach ($pack->servPack as $id=>$s2p) {
			/**
			 * @var Serv2pack $s2p
			 */
			if (isset($_POST['service']) and $post_id = array_search($id, $_POST['service'])) {
				$arMessage=array();
				if($s2p->descr!=$_POST['descr'][$id]) {
					$arMessage[]='Изменилось описание с <b>'.$s2p->descr.'</b> на <b>'.$_POST['descr'][$id].'</b>';
					$s2p->descr = $_POST['descr'][$id];
				}
				if($s2p->quant!=$_POST['count'][$id]) {
					$arMessage[]='Изменилось количество с <b>'.$s2p->quant.'</b> на <b>'.$_POST['count'][$id].'</b>';
					$s2p->quant = $_POST['count'][$id];
				}
				if($s2p->price!=$_POST['price'][$id]) {
					$arMessage[]='Изменилась цена с <b>'.$s2p->price.'</b> на <b>'.$_POST['price'][$id].'</b>';
					$s2p->price = $_POST['price'][$id];
				}
				if($s2p->duration!=$_POST['duration'][$id]) {
					$arMessage[]='Изменилась длительность с <b>'.$s2p->duration.'</b> на <b>'.$_POST['duration'][$id].'</b>';
					$s2p->duration = $_POST['duration'][$id];
				}
				if($s2p->master_id!= $_POST['people_id'][$id]) {
					if($_POST['people_id'][$id]>0) {
						$obNewMaster=People::getById($_POST['people_id'][$id]);
						if($s2p->master_id==0) {
							$arMessage[]='Установлен веб-мастер <a href="#people_'.$obNewMaster->id.'">'.$obNewMaster->fio.'</a>';
						} else {
							$arMessage[]='Изменился веб-мастер с <a href="#people_'.$s2p->master_id.'">'.$s2p->master->fio.'</a> на <a href="#people_'.$obNewMaster->id.'">'.$obNewMaster->fio.'</a>';
						}
					} else {
						$arMessage[]='Удалён веб-мастер <a href="#people_'.$s2p->master_id.'">'.$s2p->master->fio.'</a>';
					}
					$s2p->master_id=$_POST['people_id'][$id];
				}
				if(count($arMessage)>0) {
					$arMessages[]='Изменена услуга <b>'.$s2p->service->name.'</b> с услугой произошли следующие изменения:<ul><li>'.join('</li><li>',$arMessage).'</li></ul>';
				}
				$s2p->dt_beg = date('Y-m-d H:i:s');
				$s2p->dt_end = date('Y-m-d H:i:s');
				$s2p->save();
				$pack->summ += $s2p->quant * $s2p->price;
				$pack->period += $s2p->quant * $s2p->duration;
				// оставляем новые
				unset($_POST['service'][$post_id]);
			} else {
				// удаляем удаленные
				$arMessages[]='Удалена услуга '.$s2p->service->name;
				$s2p->delete();
			}
		}

		// новые услуги
		if (isset($_POST['service'])) {
			foreach ($_POST['service'] as $post_id=>$id) {
				if (is_numeric($id)) {
					$s2p = new Serv2pack();
					$s2p->serv_id = $id;
					$s2p->pack_id = $pack->id;
					$s2p->descr = $_POST['descr'][$id];
					$s2p->quant = $_POST['count'][$id];
					$s2p->price = $_POST['price'][$id];
					$s2p->duration = $_POST['duration'][$id];
					$s2p->master_id = $_POST['people_id'][$id];
					$s2p->dt_beg = date('Y-m-d H:i:s');
					$s2p->dt_end = date('Y-m-d H:i:s');
					$s2p->save();
					$arMessages[]='Добавлена услуга <b>'.$s2p->service->name.'</b>:<ul><li>Описание: <b>'.$s2p->descr.
						'</b></li><li>Количество: <b>'.$s2p->quant.'</b></li><li>Стоимость: <b>'.$s2p->price.
						'</b></li><li>Длительность: <b>'.$s2p->duration.'</b></li></ul>';
					$pack->summ += $s2p->quant * $s2p->price;
					$pack->period += $s2p->quant * $s2p->duration;
				}
			}
		}
		if(count($arMessages)>0) {
			$arNotification=array(
				'client_id'=>$pack->client_id,
				'manager_id'=>Yii::app()->user->id,
				'info'=>'[auto] В заказе #'.$pack->id.' изменился состав:<ul><li>'.join('</li><li>',$arMessages).'</li></ul>',
				'dt'=> date('Y-m-d H:i:s')
			);
			Logger::put($arNotification);
		}
		//Сохранение привязанного юрлица
		if (isset($_POST['jur_person_id']) && $_POST['jur_person_id'] > 0) {
			$pack->jur_person_id = intval($_POST['jur_person_id']);
		}

		$pack->save();

		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$pack->client_id
		));
	}

	/**
	 *
	 * @return
	 */

	public function actionEdit() {
	
		if ($pack = Package::model()->findByPk($_POST['pack_id'])) {
		
			// XXX тут может случиться перепривязка, ибо циклические зависимости
			// создание сайта при $_POST['site_url']
			if (isset($_POST['site_url'])) {
				$site = Site::model()->findByAttributes(array(
					'url'=>$_POST['site_url']
				)) or $site = new Site();
				
				$site->url = $_POST['site_url'];
				$site->host = $_POST['site_host'];
				$site->ftp = $_POST['site_ftp'];
				$site->db = $_POST['site_db'];
				
				$site->client_id = $pack->client_id;
				$site->save();
				$pack->site_id = $site->primaryKey;
				
			}
			// изменение сайта при $_POST['site_id']
			elseif (isset($_POST['site_id']) and $site = Site::model()->findByPk($_POST['site_id'])) {
				$site->client_id = $pack->client_id;
				$site->save();
				$pack->site_id = $site->primaryKey;
			}
			
			// отдаём другому менеджеру
			if (isset($_POST['people_id']['manager'])) {
				$pack->setManager($_POST['people_id']['manager']);
			}
			
			// если всё было в работе и основная задача готова - ставим статус "готово"
			if (Redmine::getIssuePercent($pack->rm_issue_id) == 100 and $pack->status_id = 50) {
				$pack->status_id = 60;
			}
			
			// изменение статусов
			if (isset($_POST['status_id']) and $_POST['status_id'] > 0) {
				$pack->status_id = (int) $_POST['status_id'];
			}
			
			if (isset($_POST['payment_id']) and $_POST['payment_id'] > 0) {
				$pack->payment_id = (int) $_POST['payment_id'];
			}
			//Сохранение привязанного юрлица
			if (isset($_POST['jur_person_id']) and $_POST['jur_person_id'] > 0)
				$pack->jur_person_id = intval($_POST['jur_person_id']);
				
			$pack->dt_change = date('Y-m-d H:i:s');
			$pack->save();
		} else {
			throw new CHttpException(500, 'Не нашелся заказ с таким ID');
		}
		
		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$pack->client_id
		));
	}
	
	/**
	 * Метод выполняет операцию привязки менеджера к заказу. Также при этом происходит привязка
	 * юридического лица к заказу и менеджера к пользователю, в случае если ранее менеджер не был привязан
	 * Юридические лица по умолчанию 1 и 2, 1 - ООО "Фабрика сайтов", 2 - ООО "ФСГрупп"
	 * @param object $id - ID заказа к которому привязывается текущий менеджер
	 * @return
	 */

	public function actionTakePack($id) {
		$package = Package::model()->findByPk($id);
		// XXX а зачем нам $obManager? Yii::app()->user->id будет совпадать с Yii::app()->user->id
		$obManager = People::model()->findByPk(Yii::app()->user->id);
		if ($package && $obManager) {
			// Не перехватил-ли заказ другой менеджер?
			// TODO Кирилл, разберись зачем такое странное сравнение
			// FIXME Егор, а в чем оно страннное? "взять" можно только ничейный или свой заказ (последнее уже не актуально)
			if ($package->manager_id == 0 or $package->manager_id == $obManager->id) {
				$package->setManager($obManager->id);
				$package->status_id = 17;
				// Добавляем cвязь менеджера и клиента, если у клиента нет менеджера
				// XXX не надо сбрасывать???
				//$package->payment_id = 17;
				$package->dt_change = date('Y-m-d H:i:s');
				if ($package->save()) {
					//сохраняем созданный пакет
					if ($package->client->psw == '' || $package->client->psw == md5($package->client->mail)) {
						$obAPIModule = Yii::app()->getModule('api');
						if (!$obAPIModule->getUserAuth()->isAssigned(Yii::app()->params['apiConfig']['new_people_auth_item'], $package->client->id))
							$obAPIModule->getUserAuth()->assign(Yii::app()->params['apiConfig']['new_people_auth_item'], $package->client->id);
						$sOpenPassword = People::genPassword();
						$package->client->psw = People::hashPassword($sOpenPassword);
						if ($package->client->login == '')
							$package->client->login = $package->client->mail;
						if ($package->client->save()) {
							//Отправляем письмо и сохраняем его в лог
							try {
								Yii::app()->getComponent('documents')->createEmailRegister($package->client, $sOpenPassword)->send($package->client->mail, $package->client->fio);
							}
							catch(exception $e) {
							}
						}
					}
				}
			}
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

		// нельзя отклонить оплаченный заказ
		if ($package->payment_id < 20) {
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
	 * @param object $pack_id
	 * @param object $serv_id [optional]
	 * @param object $master_id
	 * @return
	 */

	public function actionNewRedmineIssue($pack_id, $serv_id = 0, $master_id = 0) {
		$package = Package::model()->findByPk($pack_id);

		// если нет главной задачи
		if (!$package->rm_issue_id) {
			try {
				Redmine::postIssue($package, $master_id);
			}
			catch(Exception $e) {
				throw $e;
				return $this->renderPartial('issue', array(
					// номер задачи
					'issue_id'=>0,
					// ID заказа и услуги
					'pack_id'=>$pack_id,'serv_id'=>$serv_id
				));
			}
		}

		// если передан сервис и задача для него не существует
		if ($serv_id and $service = Serv2pack::getByIds($serv_id, $pack_id) and !$service->rm_issue_id) {
			try {
				if (Redmine::postIssue($service, $master_id)) {
					$service->save();
				}
			}
			catch(Exception $e) {
				throw $e;
				return $this->renderPartial('issue', array(
					// номер задачи
					'issue_id'=>0,
					// ID заказа и услуги
					'pack_id'=>$pack_id,'serv_id'=>$serv_id
				));
			}
		}

		// если всё распределено, то меняем статус проекта на 50 - всё в работе.
		if (!count($package->servPack(array(
			'condition'=>'rm_issue_id = 0'
		)))) {
			$package->status_id = 50;
		} else {
			$package->status_id = 40;
		}

		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();

		$this->renderPartial('issue', array(
			// номер задачи
			'issue_id'=>$serv_id ? $service->rm_issue_id : $package->rm_issue_id,
			// ID заказа и услуги
			'pack_id'=>$pack_id,'serv_id'=>$serv_id
		));
	}

	/**
	 *
	 * @param object $id
	 * @return
	 */

	public function actionCreateAllRedmineIssues($id) {
		/**
		 * XXX! Переписать! Использовать в качестве базы функцию создания одной задачи!
		 */

		$package = Package::model()->findByPk($id);

		// если нет главной задачи
		if (!$package->rm_issue_id) {
			try {
				Redmine::postIssue($package);
			}
			catch(Exception $e) {
				return $this->renderPartial('index', array(
					'client_id'=>$package->client_id
				));
			}
		}

		// распределяем нераспределенные сервисы
		foreach ($package->servPack(array(
			'condition'=>'rm_issue_id = 0'
		)) as $service) {
			try {
				if (Redmine::postIssue($service)) {
					$service->save();
				}
			}
			catch(Exception $e) {
				return $this->renderPartial('index', array(
					'client_id'=>$package->client_id
				));
			}
		}

		// если всё распределено, то меняем статус проекта на 50 - всё в работе.
		if (!count($package->servPack(array(
			'condition'=>'rm_issue_id = 0'
		)))) {
			$package->status_id = 50;
		} else {
			$package->status_id = 40;
		}

		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();

		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}

	/**
	 * добавляет комментарий в Redmine
	 * @return
	 */

	public function actionAddRedmineMessage() {

		$issue_id = (int) @$_POST['issue_id'];
		$package_id = (int) @$_POST['pack_id'];
		$serv_id = (int) @$_POST['serv_id'];
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
	 * связывает заказ/услугу с задачей в Redmine
	 * @param object $issue_id
	 * @param object $pack_id
	 * @param object $serv_id [optional]
	 * @return
	 */

	public function actionBindRedmineIssue($issue_id, $pack_id, $serv_id = 0) {

		if ($issue_id && $pack_id && $serv_id) {
			$s2p = Serv2pack::getByIds($serv_id, $pack_id);
			$s2p->rm_issue_id = $issue_id;
			$s2p->save();

			$this->renderPartial('issue', array(
				'issue_id'=>$issue_id,'pack_id'=>$pack_id,'serv_id'=>$serv_id
			));
			return;
		} elseif ($issue_id && $pack_id && !$serv_id) {
			$pack = Package::model()->findByPk($pack_id);
			$pack->rm_issue_id = $issue_id;
			$pack->save();

			$this->renderPartial('issue', array(
				'issue_id'=>$issue_id,'pack_id'=>$pack_id,'serv_id'=>$serv_id
			));
			return;
		}

		$this->renderPartial('issue', array(
			'issue_id'=>$issue_id,'pack_id'=>$pack_id,'serv_id'=>$serv_id
		));
	}

	/**
	 * закрывает задачу в Redmine
	 * @param object $issue_id
	 * @param object $pack_id
	 * @param object $serv_id
	 * @return
	 */

	public function actionCloseRedmineIssue($issue_id, $pack_id, $serv_id = 0) {

		if (!Redmine::closeIssue($issue_id)) {
			$this->renderPartial('issue', array(
				'issue_id'=>$issue_id,'pack_id'=>$pack_id,'serv_id'=>$serv_id
			));
			return;
		}

		if ($serv_id) {
			$serv2pack = Serv2pack::getByIds($serv_id, $pack_id);
			$serv2pack->dt_end = date('Y-m-d H:i:s');
			$serv2pack->save();
		}

		$package = Package::model()->findByPk($pack_id);
		$package->dt_change = date('Y-m-d H:i:s');
		$package->save();

		$this->renderPartial('issue', array(
			'issue_id'=>$issue_id,'pack_id'=>$pack_id,'serv_id'=>$serv_id
		));
	}

	/**
	 * закрывает все задачи заказа в Redmine
	 * @param int $id
	 */

	public function actionPackageIsReady($id) {
		$package = Package::model()->findByPk($id);

		$success = true;

		foreach ($package->servPack as $service) {
			$service->rm_issue_id and $success &= Redmine::closeIssue($service->rm_issue_id);
		}

		$package->rm_issue_id and $success &= Redmine::closeIssue($package->rm_issue_id);
		
		if ($success) {
			$package->status_id = 70;
			$package->dt_change = date('Y-m-d H:i:s');
			$package->dt_end = date('Y-m-d H:i:s');
			$package->save();
		}

		// данные для замены аяксом
		$this->renderPartial('index', array(
			'client_id'=>$package->client_id
		));
	}

	/**
	 * Метод выполняет отображение результатов заполнения анкет пользователем
	 */

	public function actionQuestionnaire($id) {
		$package = Package::model()->findByPk($id);
		if (!$package)
			throw new CHttpException(404, 'Заказа не найден');
		if(Yii::app()->getRequest()->getIsAjaxRequest()) {
			$this->renderPartial('questionnaire', array('pack'=>$package));
		} else {
			$this->render('questionnaire', array(
				'pack'=>$package
			));
		}
	}
}

