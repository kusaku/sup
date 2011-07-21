<?php 
/*
 Контроллер для таблицы PEOPLE
 * Пользоватли
 * Клиенты
 * Партнёты
 */
class PeopleController extends Controller {


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
					'getClients', 'getMamagers', 'index', 'view', 'save', 'globalSearchJSON', 'card'
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
	 Возвращаем всех клиентов
	 */
	public function actionGetClients() {
		$peoples = PeopleGroup::getById(7)->peoples;
		$this->renderPartial('getlist', array(
			'peoples'=>$peoples
		));
	}
	
	/*
	 Возвращаем всех менеджеров
	 */
	public function actionGetManagers() {
		$peoples = PeopleGroup::getById(4)->peoples;
		$this->renderPartial('getlist', array(
			'peoples'=>$peoples
		));
	}
	
	/*
	 Действие по умолчанию - выводим полный список всех людей
	 */
	public function actionIndex() {
		$peoples = People::getAll();
		$this->render('index', array(
			'peoples'=>$peoples
		));
	}
	
	/*
	 Действие при заданном параметре.
	 Возвращаем форму с данными человека.
	 */
	public function actionView() {
		$id = Yii::app()->request->getParam('id');
		$parent = Yii::app()->request->getParam('parent');
		
		if (!$parent)
			$parent = 0; // Может вообще ничего не придти, т.ч. присвоим 0
			
		if ($id) // Если передан нулевой ID, создаём нового человека (создавать нового человека всегда прикольно ;-) )
			$people = People::getById($id);
		else {
			$people = new People();
			$people->parent_id = $parent;
		}
		
		$this->renderPartial('view', array(
			'people'=>$people
		));
	}
	
	/*
	 Сохраняем данные, которые вернулись из формы.
	 */
	public function actionSave($data = null) {
		if (!$data)
			$data = $_POST; // Если нам не передали параметр $DATA, берём данные из $_POST
		if (isset($data['id'])) {
			if ($data['id'])
				$people = People::GetById($data['id']);
			else
				$people = new People();
				
			$people->fio = $data['fio'];
			$people->parent_id = $data['parent_id']; // ID клиента. Если задано, это это контактное лицо клиента.
			$people->mail = $data['mail'];
			$people->pgroup_id = 7; // Просто клиент
			$people->state = $data['state'];
			$people->phone = $data['phone'];
			$people->firm = $data['firm'];
			$people->descr = $data['descr'];
			$people->save();
			
			// сохраняем атрибуты
			foreach ($data['attr'] as $id=>$value) {
				$attr = isset($people->values[$id]) ? $people->values[$id] : new PeopleAttr();
				$attr->attribute_id = $id;
				$attr->people_id = $people->primaryKey;
				if ( empty($attr->attr->regexp) || preg_match($attr->attr->regexp, $value)) {
					$attr->value = $value;
				} else {
					$attr->value = $attr->attr->defval;
				}
				$attr->save();
			}
			
			if (@$data['isAJAX'])
				print(json_encode(array(
					'success'=>true
				)));
			else
				// Возвращаемся к редактируемому (добавляемому) элементу
				//$this->redirect(Yii::app()->homeUrl.'people/'.$people->id);
				$this->redirect(Yii::app()->homeUrl);
		} else
			throw new CHttpException('_00', 'Не указан идентификатор (ID) человека!');
	}
	
	/*
	 public function actionSearchJSON()
	 {
	 $peoples = People::getSearch(Yii::app()->request->getParam('term'));
	 $so = sizeof($peoples)-1;
	 $res = '[';
	 foreach ($peoples as $key => $people) {
	 $res = $res.' {"id": "'.$people->id.'","label": "'.$people->fio.' ('.$people->mail.')", "mail": "'.$people->mail.'"}';
	 if ($so > $key) $res = $res.",\n";
	 }
	 $res = $res.' ]';
	 print $res;
	 }
	 */
	
	public function actionGlobalSearchJSON() {
		$peoples = People::getGlobalSearch(Yii::app()->request->getParam('term'));
		
		$so = sizeof($peoples) - 1;
		$res = '[';
		$r = array(
		);
		foreach ($peoples as $key=>$people)
			$r[] = ' {"id": "'.$people["id"].'","label": "'.$people["label"].'", "mail": "'.$people["mail"].'"}';
			
		$res .= join(",", $r);
		$res .= ' ]';
		print $res;
	}
	
	public function actionCard() {
		if (Yii::app()->request->getParam('id'))
			$this->renderPartial('card', array(
				'client_id'=>Yii::app()->request->getParam('id')
			));
	}
}
