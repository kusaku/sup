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
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner');
		return array(
			array(
				'allow', 'actions'=>array(
					'register', 'open', 'updateAttributes', 'orderVhost', 'orderDomain'
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
	 * Регистрирует клиента в биллинге
	 * @return
	 */
	public function actionRegister($client_id) {
		// клиент
		$client = People::getById($client_id);
		
		$data = array(
		);
		foreach ($client->attr as $name=>$attr) {
			$data[$name] = $attr->values[0]->value;
		}
		
		// от имени пользователя
		$bmr = new BMRequest();
		$result = $bmr->register($data);
		
		// при успешной регистрации сохряняем ID учетки в атрибутах
		if ($result['success']) {
			$attr = $client->attr['bm_id']->values[0] or $attr = new PeopleAttr();
			$attr->attribute_id = Attributes::getByType('bm_id')->primaryKey;
			$attr->people_id = $client->primaryKey;
			$attr->value = $result['cdata']['user.id'];
			$attr->save();
			
			// пробуем отредактировать плательщика
			$data = array(
			);
			foreach ($client->attr as $name=>$attr) {
				if (! empty($attr->values[0]->value)) {
					$data[$name] = $attr->values[0]->value;
				}
			}
			
			$result_temp = $bmr->listItems('profile');
			$item = array_pop($result_temp['cdata']);
			$data['elid'] = $item['id'];
			
			$bmr->saveItem($data, 'profile');
		}
		
		// вывод результатов
		!$result['success'] and $result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
		print(json_encode($result));
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
			'username'=>$username, 'key'=>$result['key']
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
	public function actionOrderVhost($site_id, $package_id, $service_id) {
		// сайт
		$site = Site::getById($site_id);
		
		// клиент и его учетка
		$client = $site->client;
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
			'username'=>$username, 'key'=>$result['key']
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
				'price'=>27, 'period'=>10, 'addon_28'=>3000, 'addon_31'=>10, 'addon_32'=>10
			), 71=>array(
				'price'=>27, 'period'=>7, 'addon_28'=>3000, 'addon_31'=>10, 'addon_32'=>10
			), 72=>array(
				'price'=>27, 'period'=>8, 'addon_28'=>3000, 'addon_31'=>10, 'addon_32'=>10
			), 73=>array(
				'price'=>27, 'period'=>47, 'addon_28'=>3000, 'addon_31'=>10, 'addon_32'=>10
			), 74=>array(
				'price'=>27, 'period'=>9, 'addon_28'=>3000, 'addon_31'=>10, 'addon_31'=>10
			), 75=>array(
				'price'=>27, 'period'=>46, 'addon_28'=>3000, 'addon_31'=>10, 'addon_32'=>10
			),
			
			// легкий
			76=>array(
				'price'=>39, 'period'=>24, 'addon_40'=>1000, 'addon_43'=>1, 'addon_44'=>1
			), 77=>array(
				'price'=>39, 'period'=>21, 'addon_40'=>1000, 'addon_43'=>1, 'addon_44'=>1
			), 78=>array(
				'price'=>39, 'period'=>22, 'addon_40'=>1000, 'addon_43'=>1, 'addon_44'=>1
			), 79=>array(
				'price'=>39, 'period'=>23, 'addon_40'=>1000, 'addon_43'=>1, 'addon_44'=>1
			),
			
			// профессиональный
			80=>array(
				'price'=>47, 'period'=>29, 'addon_48'=>5000, 'addon_51'=>20, 'addon_52'=>20
			), 81=>array(
				'price'=>47, 'period'=>26, 'addon_48'=>5000, 'addon_51'=>20, 'addon_52'=>20
			), 82=>array(
				'price'=>47, 'period'=>27, 'addon_48'=>5000, 'addon_51'=>20, 'addon_52'=>20
			), 83=>array(
				'price'=>47, 'period'=>28, 'addon_48'=>5000, 'addon_51'=>20, 'addon_52'=>20
			)
		);
		
		// передаем данные
		$data = array_merge($prices[$service_id], array(
			'domain'=>$site->url, 'payfrom'=>'neworder'
		));
		$result = $bmr->orderVhost($data);
		
		// период хостинга относительно now
		$next = array(
			70=>'+10 days', 71=>'+3 month', 72=>'+6 month', 73=>'+9 month', 74=>'+1 year', 75=>'+2 year', 76=>'+10 days', 77=>'+3 month', 78=>'+6 month', 79=>'+1 year', 80=>'+10 days', 81=>'+3 month', 82=>'+6 month', 83=>'+1 year'
		);
		
		// при успехе
		if ($result['success']) {
			// обновим дату создания и истечения услуги
			$serv2pack = Serv2pack::getByIds($service_id, $package_id);
			$serv2pack->dt_beg = date('Y-m-d H:i:s');
			$serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now '.$next[$service_id]));
			$serv2pack->save();
			// добавим напоминалку
			$event = new Calendar();
			$event->people_id = Yii::app()->user->id;
			$event->date = date('Y-m-d', strtotime('now '.$next[$service_id]));
			$event->message = "У $client->fio для сайта $site->url заканчивается хостинг";
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
	public function actionOrderDomain($site_id, $package_id, $service_id) {
		// сайт
		$site_id = (int) Yii::app()->request->getParam('site_id');
		$site = Site::getById($site_id);
		
		// ID пакета
		$package_id = (int) Yii::app()->request->getParam('package_id');
		// ID услуги
		$service_id = (int) Yii::app()->request->getParam('service_id');
		
		// клиент и его учетка
		$client = $site->client;
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
			'username'=>$username, 'key'=>$result['key']
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
			$lastdcid = $lastdc['id'];
		} else {
			$data = array(
			);
			foreach ($client->attr as $name=>$attr) {
				$data[$name] = $attr->values[0]->value;
			}
			$data['name'] = "AutoContact from SUP for $site->url";
			
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
				'price'=>54, 'period'=>30, 'autoprolong'=>30
			), 85=>array(
				'price'=>55, 'period'=>34, 'autoprolong'=>30
			), 86=>array(
				'price'=>56, 'period'=>38, 'autoprolong'=>30
			), 87=>array(
				'price'=>57, 'period'=>42, 'autoprolong'=>30
			), 88=>array(
				'price'=>38, 'period'=>16, 'autoprolong'=>30
			), 89=>array(
				'price'=>58, 'period'=>48, 'autoprolong'=>30
			), 90=>array(
				'price'=>59, 'period'=>52, 'autoprolong'=>30
			), 91=>array(
				'price'=>60, 'period'=>56, 'autoprolong'=>30
			), 92=>array(
				'price'=>61, 'period'=>60, 'autoprolong'=>30
			), 93=>array(
				'price'=>62, 'period'=>64, 'autoprolong'=>30
			), 94=>array(
				'price'=>63, 'period'=>68, 'autoprolong'=>30
			), 95=>array(
				'price'=>64, 'period'=>72, 'autoprolong'=>30
			)
		);
		
		$data = array_merge($prices[$service_id], array(
			'customer'=>$lastdcid, 'subjnic'=>$lastdcid, 'domain'=>$site->url, 'elid'=>$lastdcid, 'customertype'=>'person'
		));
		
		$result = $bmr->orderDomain($data);
		
		// при успехе
		if ($result['success']) {
			// обновим дату создания и истечения услуги
			$serv2pack = Serv2pack::getByIds($service_id, $package_id);
			$serv2pack->dt_beg = date('Y-m-d H:i:s');
			$serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now +12 month'));
			$serv2pack->save();
			// добавим напоминалку
			$event = new Calendar();
			$event->people_id = Yii::app()->user->id;
			$event->date = date('Y-m-d', strtotime('now +12 month'));
			$event->message = "У $client->fio для сайта $site->url заканчивается регистрация домена";
			$event->status = 1;
			$event->save();
		}
		
		// вывод результатов
		!$result['success'] and $result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
		print(json_encode($result));
	}
}
