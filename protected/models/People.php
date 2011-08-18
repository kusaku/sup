<?php 
/*
 Класс таблицы People
 */

class People extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'people';
	}
	
	public function relations() {
		return array(
			'people_group'=>array(
				self::BELONGS_TO, 'PeopleGroup', 'pgroup_id'
			), 'my_packages'=>array(
				self::HAS_MANY, 'Package', 'manager_id'
			), 'my_sites'=>array(
				self::HAS_MANY, 'Site', 'client_id'
			), 'contacts'=>array(
				self::HAS_MANY, 'People', 'parent_id'
			), 'parent'=>array(
				self::BELONGS_TO, 'People', 'parent_id'
			), 'packages'=>array(
				self::HAS_MANY, 'Package', 'client_id', 'order'=>'dt_change DESC'
			),
			// все события календаря для этого человека
			'calendar'=>array(
				self::HAS_MANY, 'Calendar', 'people_id'
			), 'values'=>array(
				self::HAS_MANY, 'PeopleAttr', 'people_id', 'index'=>'attribute_id'
			), 's2p'=>array(
				self::HAS_MANY, 'Serv2pack', 'master_id'
			), 'rekviz'=>array(
				self::HAS_MANY, 'Rekviz', 'people_id'
			),
			// следующая кострукция работает так: $client->attr['phone']->values[0]->value
			'attr'=>array(
				self::MANY_MANY, 'Attributes', 'people_attr(people_id, attribute_id)', 'with'=>'values', 'condition'=>'`attr_attr`.`people_id`=`values`.`people_id`', 'index'=>'type'
			)
		);
	}
	public function attributeLabels() {
		return array(
			'mail'=>'label'
		);
	}
	/*
	 Возвращает человека по его ID
	 */
	public static function getById($id) {
		return self::model()->find(array(
			'condition'=>"id=$id", 'limit'=>1
		));
	}
	/*
	 Возвращает имя человека по его ID
	 */
	public static function getNameById($id) {
		if ($id)
			return self::model()->find(array(
				'condition'=>"id=$id", 'limit'=>1
			))->fio;
		else
			return '';
	}
	/*
	 Возвращает человека по его Login
	 */
	public static function getByLogin($login) {
		return self::model()->find(array(
			'condition'=>"login='$login'", 'limit'=>1
		));
	}
	/*
	 Возвращает список всех людей (вне зависимости от роли)
	 */
	public static function getAll() {
		$people = self::model()->findAll();
		return $people;
	}
	/*
	 Возвращает список всех людей (вне зависимости от роли), соответствующих параметрам
	 */
	public static function getSearch($param) {
		$people = self::model()->findAll(array(
			'select'=>'id, fio, mail', 'condition'=>"(mail like '%$param%') or (fio like '%$param%')",
		));
		return $people;
	}
	/*
	 Возвращает список клиентов, соответствующих параметрам
	 */
	public static function getGlobalSearch($param) {
		$a = array(
		); // Массив с результатами поиска
		$sites = Site::FindAllByUrl($param);
		foreach ($sites as $key=>$site) {
			if (!isset($a['s'.$site->id])) {
				$people = $site->client;
				$a['s'.$site->id]['id'] = $people->id;
				$a['s'.$site->id]['label'] = $site->url.' ('.$people->fio.')';
				$a['s'.$site->id]['mail'] = $people->mail;
			}
		}
		$peoples = self::model()->findAll(array(
			'select'=>'id, fio, mail, descr, pgroup_id, regdate', 'condition'=>"mail like '%$param%'", 'order'=>'regdate DESC',
		));
		foreach ($peoples as $key=>$people) {
			if (!isset($a[$people->id])) {
				$a[$people->id]['id'] = $people->id;
				$a[$people->id]['label'] = $people->fio.' ('.$people->mail.')';
				$a[$people->id]['mail'] = $people->mail;
			}
		}
		$peoples = self::model()->findAll(array(
			'select'=>'id, fio, mail, descr, pgroup_id, regdate', 'condition'=>"fio like '%$param%'", 'order'=>'regdate DESC',
		));
		foreach ($peoples as $key=>$people) {
			if (!isset($a[$people->id])) {
				$a[$people->id]['id'] = $people->id;
				$a[$people->id]['label'] = $people->fio.' ('.$people->mail.')';
				$a[$people->id]['mail'] = $people->mail;
			}
		}
		$peoples = self::model()->findAll(array(
			'select'=>'id, fio, mail, descr, pgroup_id, regdate', 'condition'=>"descr like '%$param%'", 'order'=>'regdate DESC',
		));
		foreach ($peoples as $key=>$people) {
			if (!isset($a[$people->id])) {
				$a[$people->id]['id'] = $people->id;
				$a[$people->id]['label'] = $people->fio.' ('.$people->mail.')';
				$a[$people->id]['mail'] = $people->mail;
			}
		}
		return $a;
	}
	
	/**
	 * выборка людей по фильтру
	 * @param object $filter
	 * @return array People
	 */
	public static function getByFilter($filter) {
		switch ($filter) {
			case 'employers':
				$peoples = array(
				);
				foreach (array(
					1, 2, 3, 4, 5, 8, 9
				) as $id)
					$peoples = array_merge($peoples, PeopleGroup::getById($id)->peoples);
				return $peoples;
				break;
			case 'managers':
				return PeopleGroup::getById(4)->peoples;
				break;
			case 'clients':
				return PeopleGroup::getById(7)->peoples;
				break;
			case 'partners':
				$peoples = array(
				);
				foreach (array(
					6, 10
				) as $id)
					$peoples = array_merge($peoples, PeopleGroup::getById($id)->peoples);
				return $peoples;
				break;
			case 'bigclients':
				$peoples = array(
				);
				return $peoples;
				break;
			case 'newclients':
				$peoples = array(
				);
				return $peoples;
				break;
			case 'seoclients':
				$peoples = array(
				);
				return $peoples;
				break;
			default:
				$peoples = array(
				);
				return $peoples;
				break;
		}
	}
}
