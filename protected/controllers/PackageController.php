<?php

class PackageController extends Controller
{
	public function actionIndex()
	{
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
		
		if ( $status == 50 )
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
			$site->bm_login = $_POST['site_bmlogin'];
			$site->bm_password = $_POST['site_bmpassword'];

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
			$s2p->save();
		}

		$this->redirect('/');

	}

	/*
	 * Отмечаем заказ как оплаченный
	 */
	public function actionAddPay()
	{
		if ( Yii::app()->request->getParam('id') )
		{
			$package = Package::getById( Yii::app()->request->getParam('id') );
			
			if ( Yii::app()->request->getParam('message') != '' )
			$package->descr = $package->descr."\nПодробности оплаты: ".Yii::app()->request->getParam('message');
			$package->status_id = 50;

			/* этот блок тут только для наглядного описания входных параметров
			addIssue(
			 * $subject,
			 * $description,
			 * $project_id,
			 * $assignmentUserId = 1,
			 * $parentIssueId = 0,
			 * $category_id = 1,
			 * $created_on = false,
			 * $due_date = false)
			 */
			
			$issue = Redmine::addIssue(
					'#'.$package->id.' '.$package->name,	// Название
					$package->descr,	// Описание
					0,	// Родительский проект
					53,	// Кому назначена
					0);	// Родительская задача

			$package->redmine_proj = $issue->id;
			$package->dt_change = date('Y-m-d H:i:s');

			$package->save();

			foreach ($package->servPack as $service) {
				$issue = Redmine::addIssue(
						'#'.$package->id.' '.$service->service->name,	// Название
						'Задача по проекту #'.$package->id.'. Предмет заказа: '.$service->service->name.'.',	// Описание
						0,	// Родительский проект
						53,	// Кому назначена
						$package->redmine_proj);	// Родительская задача

				$service->to_redmine = $issue->id;
				$service->save();
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
				$package->save();
			}
			
			// Возвращаем данные для замены аяксом
			Package::genClientBlock($package->client_id);
		}

	}

	/*
	 * Отмечаем заказ как не нужный - а врхив
	 */
	public function actionAddRedmineMessage()
	{
		$id	= Yii::app()->request->getParam('id');
		$message = Yii::app()->request->getParam('message');
		if ( $id & $message ){
			Redmine::addNoteToIssue($id, $message);
			$issue = Redmine::getIssue($id);
			foreach ($issue->journals->journal as $journal)
			{
				$str = $journal->user['name'].' ('.date('d-m-Y H:i', strtotime($journal->created_on)).')<br>';
				$str .= nl2br(htmlspecialchars($journal->notes));
				$str .= '<hr>';
			}
			print $str;
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