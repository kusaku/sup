<?php 
/**
 * BILLManager controller
 */
class BMController extends Controller {

	/**
	 * Регистрирует клиента в биллинге
	 * @return
	 */
	public function actionRegister() {
		// клиент
		$client_id = (int) Yii::app()->request->getParam('client_id');
		$client = People::getById($client_id);
		
		$data = array();
		foreach ($client->attr as $name=>$attr) {
			$data[$name] = $attr->value[0]->value;
		}
		//$data['confirm'] = @$data['passwd'];
		$data['ptype'] = 'pcompany';
		
		// от имени пользователя
		$bmr = new BMRequest();
		$result = $bmr->register($data);
		
		// при успешной регистрации сохряняем ID учетки в атрибутах
		if ($result['success']) {
			// 5003 == attribute_id для атрибута bm_id
			isset($client->value[5003]) and $attr = $client->value[5003] or $attr = new PeopleAttr();
			$attr->attribute_id = 5003;
			$attr->people_id = $client->primaryKey;
			$attr->value = $result['cdata']['user.id'];
			$attr->save();
		}
		
		// вывод результатов
		print(json_encode($result));
	}
	
	/**
	 * Генерирует и возвращает ключ для входа в биллинг
	 * @return
	 */
	public function actionOpen() {
		// клиент
		$client_id = (int) Yii::app()->request->getParam('client_id');
		$client = People::getById($client_id);
		
		$username = isset($client->attr['username']) ? $client->attr['username']->value[0]->value : '';
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		$result = $bmr->getAuthKey(array('username'=>$username));
		
		// вывод результатов
		print(json_encode($result));
	}
	
	/**
	 * Заказывает витруальный хостинг
	 * @return
	 */
	public function actionOrderVhost() {
		// сайт
		$site_id = (int) Yii::app()->request->getParam('site_id');
		$site = Site::getById($site_id);
		
		// ID пакета
		$package_id = (int) Yii::app()->request->getParam('package_id');
		// ID услуги
		$service_id = (int) Yii::app()->request->getParam('service_id');
		
		// клиент и его учетка
		$client = $site->client;
		$username = isset($client->attr['username']) ? $client->attr['username']->value[0]->value : '';
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		$result = $bmr->getAuthKey(array('username'=>$username));
		
		// при ошибке - выход
		if (!$result['success']) {
			print(json_encode($result));
			return;
		}
		
		// от имени пользователя
		$bmr = new BMRequest();
		$result = $bmr->login(array('username'=>$username, 'key'=>$result['key']));
		
		// при ошибке авторизации - выход
		if (!$result['success']) {
			print(json_encode($result));
			return;
		}
		
		// данные для конкретного тарифа - с периодом и дополнениями
		$prices = array(68=>array('price'=>39,
				 'period'=>21,
				 'addon_40'=>1000,
				 'addon_43'=>1,
				 'addon_44'=>1),
				 69=>array('price'=>39,
				 'period'=>22,
				 'addon_40'=>1000,
				 'addon_43'=>1,
				 'addon_44'=>1),
				 70=>array('price'=>39,
				 'period'=>23,
				 'addon_40'=>1000,
				 'addon_43'=>1,
				 'addon_44'=>1));

		
		// передаем данные
		$data = array_merge($prices[$service_id], array('domain'=>$site->url, 'payfrom'=>'neworder'));
		$result = $bmr->orderVhost($data);
		
		// период хостинга относительно now
		$next = array(68=>'+3 month',
				 69=>'+6 month',
				 70=>'+12 month');
		
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
		print(json_encode($result));
	}
	
	/**
	 * Заказывает доменное имя
	 * @return
	 */
	public function actionOrderDomain() {
		// сайт
		$site_id = (int) Yii::app()->request->getParam('site_id');
		$site = Site::getById($site_id);
		
		// ID пакета
		$package_id = (int) Yii::app()->request->getParam('package_id');
		// ID услуги
		$service_id = (int) Yii::app()->request->getParam('service_id');
		
		// клиент и его учетка
		$client = $site->client;
		$username = isset($client->attr['username']) ? $client->attr['username']->value[0]->value : '';
		
		// от имени менеджера
		$bmr = new BMRequest(true);
		$result = $bmr->getAuthKey(array('username'=>$username));
		
		// при ошибке - выход
		if (!$result['success']) {
			print(json_encode($result));
			return;
		}
		
		// от имени пользователя
		$bmr = new BMRequest();
		$result = $bmr->login(array('username'=>$username, 'key'=>$result['key']));
		
		// при ошибке авторизации - выход
		if (!$result['success']) {
			print(json_encode($result));
			return;
		}
		
		// опрос списка контактов домена
		$result = $bmr->listItems('domaincontact');
		
		// если есть контакты домена, используем последний, иначе - создаем новый
		if (! empty($result['cdata'])) {
			$lastdc = array_pop($result['cdata']);
			$lastdcid = $lastdc;
		} else {
			$data = array();
			foreach ($client->attr as $name=>$attr) {
				$data[$name] = $attr->value[0]->value;
			}
			$data['name'] = "AutoContact from SUP for $site->url";
			
			$result = $bmr->editItem($data, 'domaincontact', true);
			
			// при ошибке создания - выход
			if (!$result['success']) {
				print(json_encode($result));
				return;
			}
			
			$lastdcid = $result['cdata']['domaincontact.id'];
		}
		
		// данные для конкретного тарифа - с периодом и дополнениями
		$prices = array(72=>array('price'=>38,
				 'period'=>16,
				 'autoprolong'=>30));
		
		$data = array_merge($prices[$service_id], array('customer'=>$lastdcid, 'subjnic'=>$lastdcid, 'domain'=>$site->url, 'elid'=>$lastdcid, 'customertype'=>'person'));
		
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
		print(json_encode($result));
	}
}
