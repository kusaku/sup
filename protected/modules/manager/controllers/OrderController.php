<?php 
class OrderController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */
	public function filters() {
		return array(
			//'accessControl'
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
				'actions'=>array(
					'index',
				'view',
				'save'
				),
				'roles'=>array(
					'admin',
				'moder',
				'topmanager',
				'manager',
				'master'
				)
			),
				array(
				'allow',
				'actions'=>array(
					'index',
				'view'
				),
				'roles'=>array(
					'marketolog'
				)
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
	 * Вывод списка услуг, в удобном виде
	 * @return
	 */
	public function actionIndex() {
		$groups = array(
		);
		foreach (Service::model()->with('childs')->findAllByAttributes(array(
			'parent_id'=>0
		)) as $group) {
			$childs = array(
			);
			foreach ($group->childs as $serv) {
				$childs[$serv->primaryKey] = array(
					'name'=>$serv->name,
					'price'=>$serv->price,
					'discount'=>$serv->discount,
					'parent_id'=>$serv->parent_id,
					'descr'=>$serv->descr,
					'duration'=>$serv->duration,
					'form'=>$serv->form,
					'sort_order'=>$serv->sort_order,
					'measure'=>$serv->measure,
					'exclusive'=>$serv->exclusive
				);
			}
			$groups[$group->primaryKey] = array(
				'name'=>$group->name,
				'price'=>$group->price,
				'discount'=>$group->discount,
				'parent_id'=>$group->parent_id,
				'descr'=>$group->descr,
				'duration'=>$group->duration,
				'form'=>$group->form,
				'sort_order'=>$group->sort_order,
				'measure'=>$group->measure,
				'exclusive'=>$group->exclusive,
				'childs'=>$childs
			);
		}
		print(json_encode($groups));
	}
	
	/**
	 * Добаление заказа
	 * @return
	 */
	public function actionSave() {
		// ищем пользователя по email или создаем нового
		if (!$user = People::model()->findByAttributes(array(
			'mail'=>@$_POST['mail']
		))) {
			$user = new People();
			$user->pgroup_id = 7;
			$user->mail = @$_POST['mail'];
			$user->login = @$_POST['login'];
			$user->psw = md5(@$_POST['psw']);
			$user->fio = @$_POST['fio'];
			$user->state = @$_POST['state'];
			$user->phone = @$_POST['phone'];
			$user->firm = @$_POST['firm'];
			$user->descr = @$_POST['descr'];
			
			if ($attr = Attributes::model()->findByAttributes(array(
				'type'=>'promocode'
			)) and $p_attr = PeopleAttr::model()->findByAttributes(array(
				'attribute_id'=>$attr->primaryKey,'value'=>@$_POST['code']
			))) {
				$user->referer_id = $p_attr->people_id;
			}
			
			// сохраняем
			if ($user->save()) {
				// добавляем атрибуты
				foreach (array(
					'refcode',
					'email',
					'person',
					'name',
					'phone',
					'fax'
				) as $name) {
					$attr = new PeopleAttr();
					$attr->people_id = $user->primaryKey;
					$attr->attribute_id = Attributes::getByType($name)->primaryKey;
					switch ($name) {
						case 'refcode':
							$attr->value = @$_POST['code'];
							break;
						case 'email':
							$attr->value = @$_POST['email'];
							break;
						case 'person':
							$attr->value = @$_POST['fio'];
							break;
						case 'name':
							$attr->value = @$_POST['firm'];
							break;
						case 'phone':
							$attr->value = @$_POST['phone'];
							break;
						case 'fax':
							$attr->value = @$_POST['phone'];
							break;
					}
					$attr->save();
				}
			}
		}
		
		$pack = new Package();
		$pack->client_id = $user->primaryKey;
		
		if (isset($_POST['service'])) {
			foreach ($_POST['service'] as $id) {				
				$s2p = new Serv2pack();
				$s2p->serv_id = $id;
				$s2p->pack_id = $pack->primaryKey;
				$s2p->save();
			}
		}
		print(json_encode(array('success'=>true)));
	}
}
