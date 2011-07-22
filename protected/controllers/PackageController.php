<?php

class PackageController extends Controller
{
	
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
					'index', 'view', 'save', 'addPay', 'takePay', 'addRedmineMessage', 'bindRedmineMessage', 'decline'
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
		
	public function actionIndex()
	{
		//print get_class();
		$this->renderPartial('index',array('client_id'=>Yii::app()->request->getParam('client_id')));
	}

	public function actionView()
	{
		// Если не передали ИД клиента, то он = 0 - просматриваем существующий заказ.
		$client_id = ( int ) Yii::app()->request->getParam('client_id');

		// ID заказа. При создании нового = 0
		$package_id = ( int ) Yii::app()->request->getParam('package_id');
		$status = 0;
		if ( $package_id )
		{
			$pack = Package::getById($package_id);
			$status = $pack->status_id;
		}
		
		if ( $status > 17 ) // Больше его нельзя редактировать - только читать.
				$this->renderPartial('read',array( 'package_id'=>$package_id, 'pack'=>$pack ) );
		else
		if ( $client_id || $package_id )
			$this->renderPartial('view',array( 'package_id'=>$package_id, 'client_id'=>$client_id ) );
		else return FALSE;
	}


	/*
	 * Сохраняем заказ (пакет) со всеми заказанными услугами
	 */
	public function actionSave()
	{

		$client = People::GetById($_POST['pack_client_id']);
		/* Если заказ по контактному лицу, вешаем заказ на клиента, на не на это контактное лицо.
		 */
		if ( !empty($client->parent_id) )
		{
			$_POST['pack_client_id'] = $client->parent_id;
			$_POST['pack_descr'] = 'Контактное лицо: '.$client->fio."\n".'Телефон: '.$client->phone."\n".'EMail: '.$client->mail."\n".$_POST['pack_descr'];
		}

		if ( $_POST['pack_id'] ) $pack = Package::getById ($_POST['pack_id']);
		else
		{
			$pack = new Package();

			// для нового заказа
			$pack->status_id = 17; // Не оплачен
			$pack->dt_beg = date('Y-m-d H:i:s'); // Дата начала
			$pack->client_id = $_POST['pack_client_id'];
			$pack->manager_id = Yii::app()->user->id; // Что-б не отбирать заказ себе (допустим при редактировании администратором)
		}

		//	Был запрос на создание нового сайта
		if ( @$_POST['site_add_new'] )
		{
			$site = new Site();

			$site->url = $_POST['site_url'];
			$site->host = $_POST['site_host'];
			$site->ftp = $_POST['site_ftp'];
			$site->db = $_POST['site_db'];
			$site->client_id = $_POST['pack_client_id'];

			$site->save();
			$pack->site_id = $site->id;
		}
		else
			$pack->site_id = $_POST['pack_site_id'];

		$pack->name = $_POST['pack_name'];
		$pack->descr = $_POST['pack_descr'];
		$pack->dt_change = date('Y-m-d H:i:s');
		$pack->summa = $_POST['pack_summa'];

		$pack->save();

		Serv2pack::delByPack($pack->id);

		if ( isset($_POST['service']) )
		foreach($_POST['service'] as $id)
		{
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
	 * Отмечаем заказ как оплаченный. Создём задачу по самому заказу (родительскую задачу).
	 */
	public function actionAddPay()
	{
		if ( Yii::app()->request->getParam('id') )
		{
			$package = Package::getById( Yii::app()->request->getParam('id') );
			
			if ( Yii::app()->request->getParam('message') != '' )
			$package->descr = $package->descr."\nПодробности оплаты: ".Yii::app()->request->getParam('message');

			$usersArray = Redmine::getUsersArray();
			$package->status_id = 30;

			$issue = Redmine::addIssue('Заказ #'.$package->id.' '.$package->name,$package->descr,$usersArray[ trim( (string)Yii::app()->user->login ) ],0);

			$package->redmine_proj = $issue->id;
			$package->dt_change = date('Y-m-d H:i:s');

			$package->save();

			// Возвращаем данные для замены аяксом
			Package::genClientBlock($package->client_id);
		}
	}


	/*
	 * Создаём задачу по запросу. Если нет родительской, то и её создаём.
	 */
	public function actionNewRedmineIssue()
	{
		$pack_id = Yii::app()->request->getParam('pack_id');
		$serv_id = Yii::app()->request->getParam('serv_id');
		if ( $pack_id )	{
			$package = Package::getById( $pack_id );
			$usersArray = Redmine::getUsersArray();

			if ( !$package->redmine_proj ){
				$issue = Redmine::addIssue('Заказ #'.$package->id.' '.$package->name,$package->descr,$usersArray[ trim( (string)Yii::app()->user->login ) ],0);
				$package->redmine_proj = $issue->id;
			}

			$package->dt_change = date('Y-m-d H:i:s');
			$package->save();

			if ( $serv_id )	{
				$service = Serv2pack::getByIds($serv_id, $pack_id);
				$master = @$service->master->login ? $usersArray[ trim( mb_strToLower($service->master->login) ) ] : 0;
				$issue = Redmine::addIssue(
					'#'.$package->id.' '.$service->service->name,	// Название
					'Задача по проекту #'.$package->id.'. Предмет заказа: '.$service->service->name.'.',	// Описание
					$master,	// Кому назначена
					$package->redmine_proj);	// Родительская задача

				$service->to_redmine = $issue->id;
				$service->save();
			}

			$this->renderPartial('issue', array('issue_id'=>$issue->id, 'pack_id'=>$pack_id));
		} else print 0;
	}

	/*
	 * Отдать в работу весь заказ - создать задачи по всем заказанным услугам.
	 */
	public function actionCreateAllRedmineIssues()
	{
		if ( Yii::app()->request->getParam('id') )
		{
			$package = Package::getById( Yii::app()->request->getParam('id') );
			$usersArray = Redmine::getUsersArray();
			$package->status_id = 50;
			$package->dt_change = date('Y-m-d H:i:s');

			if ( !$package->redmine_proj ){
				$issue = Redmine::addIssue('Заказ #'.$package->id.' '.$package->name,$package->descr,$usersArray[ trim( (string)Yii::app()->user->login ) ],0);
				$package->redmine_proj = $issue->id;
			}

			$package->save();

			foreach ($package->servPack as $service) {
				$master = @$service->master->login ? $usersArray[ trim( mb_strToLower($service->master->login) ) ] : 0;

				if ( !$service->to_redmine ){
					$issue = Redmine::addIssue(
							'#'.$package->id.' '.$service->service->name,	// Название
							'Задача по проекту #'.$package->id.'. Предмет заказа: '.$service->service->name.'.',	// Описание
							$master,	// Кому назначена
							$package->redmine_proj);	// Родительская задача

					$service->to_redmine = $issue->id;
					$service->save();
				}
			}

			// Возвращаем данные для замены аяксом
			Package::genClientBlock($package->client_id);
		}
	}

	/*
	 * Берём себе поступивший заказ
	 */
	public function actionTakePack()
	{
		if ( Yii::app()->request->getParam('id') )
		{
			$package = Package::getById( Yii::app()->request->getParam('id') );
			if ( $package->manager_id == 0 or $package->manager_id == Yii::app()->user->id ) // Не перехватил-ли заказ другой менеджер
			{
				$package->manager_id = Yii::app()->user->id;
				$package->status_id = 17;
				$package->dt_change = date('Y-m-d H:i:s');
				$package->save();
			}
			
			// Возвращаем данные для замены аяксом
			Package::genClientBlock($package->client_id);
		}

	}

	/*
	 * Добавляем в Redmine новое сообщение
	 */
	public function actionAddRedmineMessage()
	{
		$id	= Yii::app()->request->getParam('id');
		$message = Yii::app()->request->getParam('message');
		$pack_id = Yii::app()->request->getParam('pack');

		if ( $id && $message ){
			Redmine::addNoteToIssue($id, $message);

			$pack = Package::getById( Yii::app()->request->getParam('pack') );
			$pack->dt_change = date('Y-m-d H:i:s');
			$pack->save();

			$this->renderPartial('issue', array('issue_id'=>$id, 'pack_id'=>$pack_id));
		} else {
			print 0;
		}
	}

	public function actionBindRedmineIssue()
	{
		$issue_id	= (int) Yii::app()->request->getParam('issue_id');
		$pack_id	= (int) Yii::app()->request->getParam('pack_id');
		$serv_id	= (int) Yii::app()->request->getParam('serv_id');

		if ( $issue_id && $pack_id && $serv_id ){
			$s2p = Serv2pack::getByIds($serv_id, $pack_id);
			$s2p->to_redmine = $issue_id;
			$s2p->save();
			print 1;
		} elseif ( $issue_id && $pack_id && !$serv_id){
			$pack = Package::getById($pack_id);
			$pack->redmine_proj = $issue_id;
			$pack->save();
			print 1;
		} else {
			print 0;
		}
	}

	/*
	 * Отмечаем заказ как не нужный - в архив
	 */
	public function actionDecline()
	{
		if ( Yii::app()->request->getParam('id') )
		{
			$package = Package::getById( Yii::app()->request->getParam('id') );
			$package->status_id = 15; // Отказ
			$package->save();

			// Возвращаем данные для замены аяксом
			Package::genClientBlock($package->client_id);
		}

	}

}