<?php 
/**
 * BILLManager controller
 */
class BMController extends Controller {

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
				'allow',
				'actions'=>array('register','open','updateAttributes','orderVhost','orderDomain','index'),
				'roles'=>array('admin','moder','topmanager','manager','master')
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$obConnection=Yii::app()->getComponent('billManager');
		/**
		 * @var BillManagerUser $obRequest
		 */
		$obRequest=$obConnection->createUserModel();
		$arFilter=false;
		$obFilter=new BMUserFilterForm();
		if(isset($_REQUEST['BMUserFilterForm'])) {
			$obFilter->attributes=$_REQUEST['BMUserFilterForm'];
			if($obFilter->validate()) {
				$arFilter=$obFilter->getFilter();
			}
		} else {
			$obFilter->attributes=$obRequest->getCurrentFilter();
			if($obFilter->validate()) {
				$obFilter->getFilter();
			}
		}
		$arUsers=$obRequest->getList($arFilter);
		$dataProvider=new CArrayDataProvider($arUsers,array('id'=>'bmlist','pagination'=>array('pageSize'=>10)));
		$this->renderPartial('userList',array('list'=>$dataProvider,'filter'=>$obFilter));
	}
	
	/**
	 * Регистрирует клиента в биллинге
	 * @return
	 */
	public function actionRegister($client_id) {
		$arAnswer=array();
		if($obClient = People::getById($client_id)) {
			$obConnection=Yii::app()->getComponent('billManager');
			if($obClient->bm_user_data==NULL) {
				$obRequest=$obConnection->createRegisterModel();
				$obRequest->setScenario('init');
				///TODO Заменить на данные из нормальных таблиц (заявки на домены и прочее)
				$data = array();
				foreach ($obClient->attr as $name=>$attr) {
					$data[$name] = $attr->values[0]->value;
				}
				$obRequest->attributes=$data;
				if($obRequest->ptype==BillManagerRegister::USER_TYPE_COMPANY)
					$obRequest->setScenario('company');
				if($obRequest->validate()) {
					try {
						$obRequest->register();
						$obBMUserData=new BMUserData();
						$obBMUserData->attributes=$obRequest->attributes;
						$obBMUserData->people_id=$obClient->id;
						$obBMUserData->save();
						$obGetUserDataRequest=$obConnection->createUserModel();
						$obGetUserDataRequest->id=$obRequest->user_id;
						$obGetUserDataRequest->load();
						$obBMUsers=new BMUsers();
						$obBMUsers->attributes=$obGetUserDataRequest->attributes;
						$obBMUsers->date_update=date('Y-m-d H:i:s');
						$obBMUsers->save();
						$arAnswer['result']='ok';
					} catch (ISPAnswerException $e) {
						$arAnswer['error']=2;
						$arAnswer['errorText']=$e->getMessage();
						$arAnswer['errorCode']=$e->getCode();
					}
				} else {
					$arAnswer['error']=1;
					$arAnswer['errors']=$obRequest->getErrors();
				}
			} else {
				try {
					$obGetUserDataRequest=$obConnection->createUserModel();
					$obGetUserDataRequest->id=$obClient->bm_user_data->user_id;
					$obGetUserDataRequest->load();
					$obBMUsers=$obClient->bm_user_data->user;
					$obBMUsers->attributes=$obGetUserDataRequest->attributes;
					$obBMUsers->date_update=date('Y-m-d H:i:s');
					$obBMUsers->save();
					$arAnswer['result']='update';
				} catch(ISPAnswerException $e) {
					$arAnswer['error']=2;
					$arAnswer['errorText']=$e->getMessage();
					$arAnswer['errorCode']=$e->getCode();
				}
			}
		}
		echo json_encode($arAnswer);
		die();
	}
	
	/**
	 * Генерирует и возвращает ключ для входа в биллинг
	 * @return
	 */
	public function actionOpen($client_id) {
		// клиент
		$client = People::getById($client_id);
		
		$bm_id = isset($client->attr['bm_id']) ? $client->attr['bm_id']->values[0]->value : 0;
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		
		$result = $bmr->listItems('user');
		
		// получаем имя пользователя (логин) для входа
		$username = isset($result['cdata'][$bm_id]) ? $result['cdata'][$bm_id]['name'] : (isset($client->attr['username']) ? $client->attr['username']->values[0]->value : '');
		
		// получаем временный ключ для входа
		$result = $bmr->getAuthKey(array(
			'username'=>$username
		));
		
		// вывод результатов
		!$result['success'] and $result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
		print(json_encode($result));
	}
	
	/**
	 * Генерирует и возвращает ключ для входа в биллинг
	 * @return
	 */
	public function actionUpdateAttributes($client_id) {
		// клиент
		$client = People::getById($client_id);
		
		// аттрибуты клиента
		$values = $client->values;
		
		$bm_id = isset($client->attr['bm_id']) ? $client->attr['bm_id']->values[0]->value : 0;
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		
		// получаем логин
		$result = $bmr->viewItem(array(
			'elid'=>$bm_id
		), 'user');
		
		// при ошибке  - выход
		if (!$result['success']) {
			$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));
			return;
		}
		
		$username = $result['cdata']['uname'];
		
		// сохраним логин
		$id = Attributes::getByType('username')->primaryKey;
		$values[$id]->value = $username;
		$values[$id]->save();
		
		// получаем ключ для входа
		$result = $bmr->getAuthKey(array(
			'username'=>$username
		));
		
		// от имени пользователя - выполняем вход
		$bmr = new BMRequest();
		$result = $bmr->login(array(
			'username'=>$username,'key'=>$result['key']
		));
		
		// при ошибке авторизации - выход
		if (!$result['success']) {
			$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));
			return;
		}
		
		// получим данные учетки
		$result = $bmr->listItems('user');
		$item = array_pop($result['cdata']);
		$result = $bmr->viewItem(array(
			'elid'=>$item['id']
		), 'user');
		
		if ($result['success']) {
			foreach ($result['cdata'] as $name=>$value) {
				if (Attributes::getByType($name)) {
					$id = Attributes::getByType($name)->primaryKey;
					$values[$id]->value = $value;
					$values[$id]->save();
				}
			}
		}
		
		// получим данные плательщика
		$result = $bmr->listItems('profile');
		$item = array_pop($result['cdata']);
		$result = $bmr->viewItem(array(
			'elid'=>$item['id']
		), 'profile');
		
		if ($result['success']) {
			foreach ($result['cdata'] as $name=>$value) {
				$name == 'ptypeval' and $name = 'ptype';
				if (Attributes::getByType($name)) {
					$id = Attributes::getByType($name)->primaryKey;
					$values[$id]->value = $value;
					$values[$id]->save();
				}
			}
		}
		
		// XXX получим данные аккаунта (не работает)
		$result = $bmr->listItems('account');
		$item = array_pop($result['cdata']);
		$result = $bmr->viewItem(array(
			'elid'=>$item['id']
		), 'account');
		
		if ($result['success']) {
			$item = array_pop($result['cdata']) or $item = array(
			);
			
			foreach ($result['cdata'] as $name=>$value) {
				if (Attributes::getByType($name)) {
					$id = Attributes::getByType($name)->primaryKey;
					$values[$id]->value = $value;
					$values[$id]->save();
				}
			}
		}
		
		// получим данные контактов домена
		$result = $bmr->listItems('domaincontact');
		$item = array_pop($result['cdata']);
		$result = $bmr->viewItem(array(
			'elid'=>$item['id']
		), 'domaincontact');
		
		if ($result['success']) {
			$item = array_pop($result['cdata']) or $item = array(
			);
			
			foreach ($result['cdata'] as $name=>$value) {
				if (Attributes::getByType($name)) {
					$id = Attributes::getByType($name)->primaryKey;
					$values[$id]->value = $value;
					$values[$id]->save();
				}
			}
		}
		
		print(json_encode(array(
			'success'=>true
		)));
	}
	
	/**
	 * Заказывает витруальный хостинг
	 * @return
	 */
	public function actionOrderVhost($package_id, $service_id, $use_promo = false) {
		$package = Package::model()->findByPk($package_id);
		
		// клиент и сайт
		$client = $package->client;
		$username = isset($client->attr['username']) ? $client->attr['username']->values[0]->value : '';
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		$result = $bmr->getAuthKey(array(
			'username'=>$username
		));
		
		// при ошибке - выход
		if (!$result['success']) {
			$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));
			return;
		}
		
		// от имени пользователя
		$bmr = new BMRequest();
		$result = $bmr->login(array(
			'username'=>$username,'key'=>$result['key']
		));
		
		// при ошибке авторизации - выход
		if (!$result['success']) {
			$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));
			return;
		}
		
		// данные для конкретного тарифа - с периодом и дополнениями
		$prices = array(
			// оптимальный
			70=>array(
				'price'=>27,
				'period'=>10,
				'addon_28'=>3000,
				'addon_31'=>10,
				'addon_32'=>10
			),
			71=>array(
				'price'=>27,
				'period'=>7,
				'addon_28'=>3000,
				'addon_31'=>10,
				'addon_32'=>10
			),
			72=>array(
				'price'=>27,
				'period'=>8,
				'addon_28'=>3000,
				'addon_31'=>10,
				'addon_32'=>10
			),
			73=>array(
				'price'=>27,
				'period'=>47,
				'addon_28'=>3000,
				'addon_31'=>10,
				'addon_32'=>10
			),
			74=>array(
				'price'=>27,
				'period'=>9,
				'addon_28'=>3000,
				'addon_31'=>10,
				'addon_31'=>10
			),
			75=>array(
				'price'=>27,
				'period'=>46,
				'addon_28'=>3000,
				'addon_31'=>10,
				'addon_32'=>10
			),
					
			// легкий
			76=>array(
				'price'=>39,
				'period'=>24,
				'addon_40'=>1000,
				'addon_43'=>1,
				'addon_44'=>1
			),
			77=>array(
				'price'=>39,
				'period'=>21,
				'addon_40'=>1000,
				'addon_43'=>1,
				'addon_44'=>1
			),
			78=>array(
				'price'=>39,
				'period'=>22,
				'addon_40'=>1000,
				'addon_43'=>1,
				'addon_44'=>1
			),
			79=>array(
				'price'=>39,
				'period'=>23,
				'addon_40'=>1000,
				'addon_43'=>1,
				'addon_44'=>1
			),
					
			// профессиональный
			80=>array(
				'price'=>47,
				'period'=>29,
				'addon_48'=>5000,
				'addon_51'=>20,
				'addon_52'=>20
			),
			81=>array(
				'price'=>47,
				'period'=>26,
				'addon_48'=>5000,
				'addon_51'=>20,
				'addon_52'=>20
			),
			82=>array(
				'price'=>47,
				'period'=>27,
				'addon_48'=>5000,
				'addon_51'=>20,
				'addon_52'=>20
			),
			83=>array(
				'price'=>47,
				'period'=>28,
				'addon_48'=>5000,
				'addon_51'=>20,
				'addon_52'=>20
			)
		);

		
		// использование промокода
		if ($use_promo) {
			$manager = People::model()->findByPk(Yii::app()->user->id);
			$promocode = isset($manager->attr['promocode']) ? $manager->attr['promocode']->values[0]->value : NULL;
		} else {
			$promocode = NULL;
		}
		
		$serv2pack = Serv2pack::getByIds($service_id, $package_id);
		
		// передаем данные
		$data = array_merge($prices[$service_id], array(
			'domain'=>$serv2pack->descr,'payfrom'=>'neworder','promocode'=>$promocode,
		));
		$result = $bmr->orderVhost($data);
		
		// период хостинга относительно now
		$next = array(
			70=>'+10 days',
				71=>'+3 month',
				72=>'+6 month',
				73=>'+9 month',
				74=>'+1 year',
				75=>'+2 year',
				76=>'+10 days',
				77=>'+3 month',
				78=>'+6 month',
				79=>'+1 year',
				80=>'+10 days',
				81=>'+3 month',
				82=>'+6 month',
				83=>'+1 year'
		);
		
		// при успехе
		if ($result['success']) {
			// обновим дату создания и истечения услуги			
			$serv2pack->dt_beg = date('Y-m-d H:i:s');
			$serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now '.$next[$service_id]));
			$serv2pack->save();
			// добавим напоминалку
			$event = new Calendar();
			$event->people_id = Yii::app()->user->id;
			$event->date = date('Y-m-d', strtotime('now '.$next[$service_id]));
			$event->message = "У $client->fio заканчивается хостинг";
			$event->status = 1;
			$event->save();
		}
		
		// вывод результатов
		!$result['success'] and $result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
		print(json_encode($result));
	}
	
	/**
	 * Заказывает доменное имя
	 * @return
	 */
	public function actionOrderDomain($package_id, $service_id, $use_promo = false) {
		$package = Package::model()->findByPk($package_id);
		
		// клиент и сайт
		$client = $package->client;
		$username = isset($client->attr['username']) ? $client->attr['username']->values[0]->value : '';
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		$result = $bmr->getAuthKey(array(
			'username'=>$username
		));
		
		// при ошибке - выход
		if (!$result['success']) {
			$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));
			return;
		}
		
		// от имени пользователя
		$bmr = new BMRequest();
		$result = $bmr->login(array(
			'username'=>$username,'key'=>$result['key']
		));
		
		// при ошибке авторизации - выход
		if (!$result['success']) {
			$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));
			return;
		}
		
		// опрос списка контактов домена
		$result = $bmr->listItems('domaincontact');
		
		// если есть контакты домена, используем последний, иначе - создаем новый
		if (! empty($result['cdata'])) {
			$lastdc = array_pop($result['cdata']);
			$lastdcid = $lastdc['id'];$serv2pack = Serv2pack::getByIds($service_id, $package_id);
		} else {
			$data = array(
			);
			foreach ($client->attr as $name=>$attr) {
				$data[$name] = $attr->values[0]->value;
			}
			$data['name'] = "AutoContact from SUP";
			
			$result = $bmr->saveItem($data, 'domaincontact');
			
			// при ошибке создания - выход
			if (!$result['success']) {
				$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
				print(json_encode($result));
				return;
			}
			
			$lastdcid = $result['cdata']['domaincontact.id'];
		}
		
		// данные для конкретного тарифа - с периодом и дополнениями
		$prices = array(
			84=>array(
				'price'=>54,
				'period'=>30,
				'autoprolong'=>30
			),
			85=>array(
				'price'=>55,
				'period'=>34,
				'autoprolong'=>30
			),
			86=>array(
				'price'=>56,
				'period'=>38,
				'autoprolong'=>30
			),
			87=>array(
				'price'=>57,
				'period'=>42,
				'autoprolong'=>30
			),
			88=>array(
				'price'=>38,
				'period'=>16,
				'autoprolong'=>30
			),
			89=>array(
				'price'=>58,
				'period'=>48,
				'autoprolong'=>30
			),
			90=>array(
				'price'=>59,
				'period'=>52,
				'autoprolong'=>30
			),
			91=>array(
				'price'=>60,
				'period'=>56,
				'autoprolong'=>30
			),
			92=>array(
				'price'=>61,
				'period'=>60,
				'autoprolong'=>30
			),
			93=>array(
				'price'=>62,
				'period'=>64,
				'autoprolong'=>30
			),
			94=>array(
				'price'=>63,
				'period'=>68,
				'autoprolong'=>30
			),
			95=>array(
				'price'=>64,
				'period'=>72,
				'autoprolong'=>30
			)
		);
		
		// использование промокода
		if ($use_promo) {
			$manager = People::model()->findByPk(Yii::app()->user->id);
			$promocode = isset($manager->attr['promocode']) ? $manager->attr['promocode']->values[0]->value : NULL;
		} else {
			$promocode = NULL;
		}
		
		$serv2pack = Serv2pack::getByIds($service_id, $package_id);
		
		$data = array_merge($prices[$service_id], array(
			'customer'=>$lastdcid,'subjnic'=>$lastdcid,'domain'=>$serv2pack->descr,'elid'=>$lastdcid,'customertype'=>'person','promocode'=>$promocode
		));
		
		$result = $bmr->orderDomain($data);
		
		// при успехе
		if ($result['success']) {
			// обновим дату создания и истечения услуги			
			$serv2pack->dt_beg = date('Y-m-d H:i:s');
			$serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now +12 month'));
			$serv2pack->save();
			// добавим напоминалку
			$event = new Calendar();
			$event->people_id = Yii::app()->user->id;
			$event->date = date('Y-m-d', strtotime('now +12 month'));
			$event->message = "У $client->fio заканчивается срок регистрация домена";
			$event->status = 1;
			$event->save();
		}
		
		// вывод результатов
		!$result['success'] and $result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
		print(json_encode($result));
	}
}
