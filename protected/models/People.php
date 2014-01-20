<?php
/**
 * Модель для управления пользователями
 * @property BMUserData $bm_user_data
 * @property JurPersonReference $jur_person
 * @property integer $id
 * @property string $login
 * @property string $mail
 * @property integer $pgroup_id
 * @property string $psw
 * @property string $rm_token
 * @property string $fio
 * @property string $state
 * @property string $phone
 * @property string $firm
 * @property string $descr
 * @property integer $parent_id
 *
 * @property Package[] $packages
 * @property People[] $contacts
 * @method Package[]|boolean packages() packages(array)
 */
class People extends CActiveRecord {
	const PASSWORD_BASE_STRING = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()';
	public $partner_id; // ID партнёра, если заполнен, то модель автоматически привязывает клиента к партнёру.
	public $partner_type = Partner::TP_DEFAULT; // Тип партнера, используется только при создании партнера.

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * Метод выполняет хэширование строки по алгоритму хэширования пароля
	 */

	public static function hashPassword($sPassword) {
		return md5($sPassword);
	}

	/**
	 * Правила валидации
	 */

	public function rules() {
		return array(
			// правила для PeopleController/actionSave
			// !!! необходимо добавить сценарий peopleControllerSave для того, чтобы это сработало...
			array(
				'login, fio, mail','required','on'=>array(
					'peopleControllerSave'
				)
			),array(
				'mail','email','on'=>array(
					'peopleControllerSave'
				)
			),
		);
	}

	public function tableName() {
		return 'people';
	}

	public function relations() {
		return array(
			'people_group'=>array(
				self::BELONGS_TO,'PeopleGroup','pgroup_id'
			),'my_packages'=>array(
				self::HAS_MANY,'Package','manager_id'
			),'sites'=>array(
				self::HAS_MANY,'Site','client_id'
			),'promocodes'=>array(
				self::HAS_MANY,'Promocode','client_id'
			),'infocode'=>array(
				self::BELONGS_TO,'Infocode','infocode_id'
			),'contacts'=>array(
				self::HAS_MANY,'People','parent_id'
			),'parent'=>array(
				self::BELONGS_TO,'People','parent_id'
			),'referer'=>array(
				self::BELONGS_TO,'People','referer_id'
			),'partner_data'=>array(
				self::HAS_ONE,'Partner',array(
					'id'=>'id'
				),
			),'owner_partner'=>array(
				self::HAS_ONE,'PartnerPeople',array(
					'id_client'=>'id'
				)
			),'packages'=>array(
				self::HAS_MANY,'Package','client_id'
			),'manager'=>array(
				self::HAS_ONE,'PeopleToManager','user_id'
			),
			// все события календаря для этого человека
			'calendar'=>array(
				self::HAS_MANY,'Calendar','people_id'
			),'values'=>array(
				self::HAS_MANY,'PeopleAttr','people_id','index'=>'attribute_id'
			),'s2p'=>array(
				self::HAS_MANY,'Serv2pack','master_id'
			),'rekviz'=>array(
				self::HAS_MANY,'Rekviz','people_id'
			),
			// следующая конструкция работает так: $client->attr['phone']->values[0]->value
			'attr'=>array(
				self::MANY_MANY,'Attributes','people_attr(people_id, attribute_id)','with'=>'values',
					'condition'=>'`attr_attr`.`people_id`=`values`.`people_id`','index'=>'type',
					'joinType'=>'INNER JOIN',
			),
			// ID записи о реквизитах в справочнике юридических лиц
			'jur_person'=>array(
				self::BELONGS_TO,'JurPersonReference','jur_person_id'
			),'documents'=>array(
				self::MANY_MANY,'Documents','document_people(people_id,document_id)'
			),'bm_user_data'=>array(
				self::HAS_ONE,'BMUserData','people_id'
			),'contacts'=>array(
				self::HAS_MANY,'PeopleContacts','people_id'
			)
		);
	}


	public function attributeLabels() {
		return array(
			'login'=>'Логин','fio'=>'ФИО','mail'=>'E-mail'
		);
	}

	/**
	 * Метод возвращает результат проверки, является ли данный пользователь активным партнёром
	 */

	public function isActivePartner() {
		if ($this->partner_data) {
			return $this->partner_data->status == 'active';
		}
		return false;
	}

	/**
	 * После сохранения, смотри стали ли мы партнёром. Если стали, проверяем наличие записи
	 * в соответствующей таблице, если её там нет - создаём, если есть - меняем статус на closed
	 */

	protected function afterSave() {
		parent::afterSave();
		if ($this->pgroup_id == 6) {
			$partnerType = in_array($this->partner_type, array_keys(Partner::getTypes())) ?  $this->partner_type : Partner::TP_DEFAULT;
			$obAPIModule = Yii::app()->getModule('api');
			if (!($obAPIModule->getUserAuth()->isAssigned(Partner::TP_DEFAULT, $this->id)
					|| $obAPIModule->getUserAuth()->isAssigned(Partner::TP_CONSULTANT, $this->id))) {
				$obAPIModule->getUserAuth()->assign($partnerType, $this->id);
			}
			if (!$this->partner_data) {
				$obPartner = new Partner('new');
				$obPartner->id = $this->id;
				$obPartner->name = '';
				$obPartner->date_sign = NULL;
				$obPartner->status = 'new';
				$obPartner->agreement_num = '';
				$obPartner->type = $this->partner_type;
				$obPartner->save();
			}
		} else {
			$obAPIModule = Yii::app()->getModule('api');
			if ($obAPIModule->getUserAuth()->isAssigned('Partner', $this->id)) {
				$obAPIModule->getUserAuth()->revoke('Partner', $this->id);
			}
			if ($this->partner_data) {
				$this->partner_data->status = 'closed';
				$this->partner_data->save();
			}
		}
	}

	protected function beforeSave() {
		$bResult = true;
		if ($this->isNewRecord) {
			if ($this->findByAttributes(array(
				'mail'=>$this->mail
			))) {
				$this->addError('mail', 'Пользователь с таким email уже зарегистрирован в базе');
				$bResult = false;
			}
			if ($this->findByAttributes(array(
				'login'=>$this->login
			))) {
				$this->addError('login', 'Пользователь с таким логином уже зарегистрирован в базе');
				$bResult = false;
			}
		} else {
			$obCondition = new CDbCriteria();
			$obCondition->compare('id', " != {$this->primaryKey}");
			if ($this->findByAttributes(array(
				'mail'=>$this->mail
			), $obCondition)) {
				$this->addError('mail', 'Пользователь с таким email уже зарегистрирован в базе');
				$bResult = false;
			}
			if ($this->findByAttributes(array(
				'login'=>$this->login
			), $obCondition)) {
				$this->addError('login', 'Пользователь с таким логином уже зарегистрирован в базе');
				$bResult = false;
			}
		}
		return $bResult;
	}

	/**
	 * Случайно сделал лишнюю, потом удалить
	 */

	public static function genPasswordHash($sPassword) {
		return self::hashPassword($sPassword);
	}

	/**
	 * Метод генерирует новый пароль указанной длины
	 * @param $length=6
	 * @return string - новый пароль
	 */

	public static function genPassword($length = 6) {
		return substr(str_shuffle(self::PASSWORD_BASE_STRING), rand(0, strlen(self::PASSWORD_BASE_STRING) - $length), $length);
	}

	public static function getById($id) {
		return self::model()->findByPk($id);
	}


	public static function getNameById($id) {
		return ($people = self::model()->findByPk($id)) ? $people->fio : '';
	}


	public static function getByLogin($login) {
		return self::model()->findByAttributes(array(
			'login'=>$login,
		));
	}


	public static function getAll() {
		return self::model()->findAll();
	}


	public static function getSearch($param) {
		$people = self::model()->findAll(array(
			'select'=>'id, fio, mail','condition'=>"(mail like '%$param%') or (fio like '%$param%')",
		));
		return $people;
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
					1,2,3,4,5,8,9
				) as $id)
					$peoples = array_merge($peoples, PeopleGroup::getById($id)->peoples);
				return $peoples;
				break;
			case 'topmanagers':
				return PeopleGroup::getById(3)->peoples;
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
					6,10
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

	/**
	 * Метод возвращает информацию о аватаре пользователя
	 */

	public function getAvatar() {
		$arResult = array(
			'avatar'=>'','avatar_hash'=>''
		);
		if ($this->image != '' && file_exists(Yii::getPathOfAlias('webroot').$this->image)) {
			$arResult['avatar'] = $this->image;
			$arResult['avatar_hash'] = md5_file(Yii::getPathOfAlias('webroot').$this->image);
		}
		return $arResult;
	}

	/**
	 * Функция возвращает название страны по номеру
	 */

	public static function getCountryById($id) {
		$arCountries=self::getCountriesList();
		if(isset($arCountries[$id]))
			return $arCountries[$id];
		return '';
	}

	/**
	 * Метод выполняет получение списка стран
	 */
	public static function getCountriesList() {
		$arResult=array();
		$obAttr=Attributes::model()->findByAttributes(array('type'=>'la_country'));
		if ($set = unserialize($obAttr->set)) {
			foreach($set as $country) {
				$arResult[intval($country['value'])]=$country['name'];
			}
		}
		return $arResult;
	}

	/**
	 * поиск клиентов по строке
	 * @param string $param строка поиска
	 * @return array $people
	 */

	public static function search($search, $page = 0, $limit = 20, &$count = false) {

		$offset = $page * $limit;
		$myid = Yii::app()->user->id;
		// вывод всех подряд
		if ( empty($search)) {

			// у админа и сеошников выводятся все проекты
			if (Yii::app()->user->checkAccess('admin') or Yii::app()->user->checkAccess('moder') or Yii::app()->user->checkAccess('marketolog')) {
				$query = "SELECT `client_id` AS `id`, MAX(`dt_change`) AS `label` FROM `package` WHERE `package`.`status_id` NOT IN (15, 999) GROUP BY `client_id` ORDER BY `label` DESC";
			}
			// у бухгалтера выводятся неоплаченные проекты
			elseif (Yii::app()->user->checkAccess('topmanager')) {
				$query = "SELECT `client_id` AS `id`, MAX(`dt_change`) AS `label` FROM `package` WHERE `package`.`status_id` NOT IN (15, 999) AND `package`.`payment_id` IN (18, 20) GROUP BY `client_id` ORDER BY `label` DESC";
			}
			// у менеджера выводятся только новые, и его заказы
			elseif (Yii::app()->user->checkAccess('manager')) {
				$query = "SELECT `client_id` AS `id`, MAX(`dt_change`) AS `label` FROM `package` WHERE `package`.`status_id` NOT IN (15, 999) AND `package`.`manager_id` IN (0, {$myid}) GROUP BY `client_id` ORDER BY `label` DESC";
			}
			// у остальных только те проекты, где они значатся исполнителями
			else {
				$query = "SELECT `client_id` AS `id`, MAX(`dt_change`) AS `label` FROM `package` LEFT JOIN `serv2pack` ON `serv2pack`.`pack_id` = `package`.`id` WHERE `package`.`status_id` NOT IN (15, 999) AND `serv2pack`.`master_id` = {$myid} GROUP BY `client_id` ORDER BY `label` DESC";
			}

			$count_query = "SELECT COUNT(*) FROM ({$query}) AS `helper`";
			$result_query = "$query LIMIT {$offset},{$limit}";
		} elseif (in_array($prefix = $search[0], array(
			'@','#','$','%','*','/','\\',':'
		)) and is_numeric($id = trim(substr($search, 1)))) {

			switch ($prefix) {
				case '@':
					$query = "SELECT `id`, CONCAT('ID Клиента: ', '{$id}') AS `label`, `id` as `sort` FROM `people` WHERE `id`={$id}";
				break;
				case '#':
					$query = "SELECT `client_id` AS `id`, CONCAT('ID Заказа: ', '{$id}') AS `label`, `id` as `sort` FROM `package` WHERE `id`={$id}";
				break;
				case '$':
					$query = "SELECT `client_id` AS `id`, CONCAT('Промокод: ', '{$id}') AS `label`, `id` as `sort` FROM `promocode` WHERE `code`={$id}";
				break;
				case '*': //По продукту
					if(Yii::app()->user->checkAccess('manager')) {
						$query = "SELECT `package`.`client_id` AS `id`, CONCAT('Продукт: ','{$id}',' - ',`service`.`name`) as `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `serv2pack` ON `package`.`id`=`serv2pack`.`pack_id` JOIN `service` ON `serv2pack`.`serv_id`=`service`.`id` WHERE `service`.`id`={$id} AND `package`.`manager_id`={$myid} GROUP BY `package`.`client_id`";
					} else {
						$query = "SELECT `package`.`client_id` AS `id`, CONCAT('Продукт: ','{$id}',' - ',`service`.`name`) as `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `serv2pack` ON `package`.`id`=`serv2pack`.`pack_id` JOIN `service` ON `serv2pack`.`serv_id`=`service`.`id` WHERE `service`.`id`={$id} GROUP BY `package`.`client_id`";
					}
				break;
				case ':': //По менеджеру
					if(Yii::app()->user->checkAccess('manager')) {
						$query = "SELECT `client_id` AS `id`, CONCAT('Менеджер: ', '{$myid}',' - ',`people`.`fio`) AS `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `people` ON `package`.`manager_id`=`people`.`id` WHERE `package`.`manager_id`={$myid} GROUP BY `package`.`client_id`";
					} else {
						$query = "SELECT `client_id` AS `id`, CONCAT('Менеджер: ', '{$id}',' - ',`people`.`fio`) AS `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `people` ON `package`.`manager_id`=`people`.`id` WHERE `package`.`manager_id`={$id} GROUP BY `package`.`client_id`";
					}
				break;
				case '/': //По статусу заказа
					if(Yii::app()->user->checkAccess('manager')) {
						$query = "SELECT `client_id` AS `id`, CONCAT('Статус заказа: ', '{$id}',' - ',`package_status`.`name`) AS `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `package_status` ON `package`.`status_id`=`package_status`.`id` WHERE `package`.`status_id`={$id} AND `package`.`manager_id`={$myid} GROUP BY `package`.`client_id`";
					} else {
						$query = "SELECT `client_id` AS `id`, CONCAT('Статус заказа: ', '{$id}',' - ',`package_status`.`name`) AS `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `package_status` ON `package`.`status_id`=`package_status`.`id` WHERE `package`.`status_id`={$id} GROUP BY `package`.`client_id`";
					}
				break;
				case '\\': //По статусу оплаты
					if(Yii::app()->user->checkAccess('manager')) {
						$query = "SELECT `client_id` AS `id`, CONCAT('Статус оплаты: ', '{$id}',' - ',`package_payment`.`name`) AS `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `package_payment` ON `package`.`payment_id`=`package_payment`.`id` WHERE `package`.`payment_id`={$id} AND `package`.`manager_id`={$myid} GROUP BY `package`.`client_id`";
					} else {
						$query = "SELECT `client_id` AS `id`, CONCAT('Статус оплаты: ', '{$id}',' - ',`package_payment`.`name`) AS `label`, max(`package`.`dt_beg`) as `sort` FROM `package` JOIN `package_payment` ON `package`.`payment_id`=`package_payment`.`id` WHERE `package`.`payment_id`={$id} GROUP BY `package`.`client_id`";
					}
				break;
				case '%':
					// набор для UNION
					$queries = array(
						"(SELECT `client_id` AS `id`, CONCAT('Задача: ', '{$id}') AS `label` FROM `package` WHERE `rm_issue_id`={$id})",
						"(SELECT `client_id` AS `id`, CONCAT('Подзадача: ', '{$id}') AS `label` FROM `serv2pack` INNER JOIN `package` ON `package`.`id` = `serv2pack`.`pack_id` AND `serv2pack`.`rm_issue_id`={$id})",
					);
					$query = implode("\nUNION ALL\n", $queries);
					break;
			}

			$count_query = "SELECT COUNT(*) FROM ({$query}) AS `helper`";
			$result_query = "SELECT `id`, `label` FROM ($query) AS `helper` ORDER BY `sort` DESC LIMIT {$offset},{$limit}";
			//$result_query = "$query LIMIT {$offset},{$limit}";
		}
		// задействуем полнотектовый поиск
		else {
			// набор для UNION
			$queries = array(
				"(SELECT `id`, CONCAT('Email: ', `mail`) AS `label` FROM `people` WHERE `mail` LIKE '%$search%')",
				"(SELECT `id`, CONCAT('Логин: ', `login`) AS `label` FROM `people` WHERE `login` LIKE '$search%')",
				"(SELECT `id`, CONCAT('Имя: ', `fio`) AS `label` FROM `people` WHERE `fio` LIKE '%$search%')",
				"(SELECT `id`, CONCAT('Фирма: ', `firm`) AS `label` FROM `people` WHERE `firm` LIKE '%$search%')",
				"(SELECT `id`, CONCAT('Город: ', `state`) AS `label` FROM `people` WHERE `state` LIKE '$search%')",
				"(SELECT `id`, CONCAT('Телефон: ', `phone`) AS `label` FROM `people` WHERE `phone` LIKE '%$search%')",
				"(SELECT `id`, CONCAT('Описание клиента: ', CONCAT(SUBSTRING(`descr`, 1, 50), '...')) AS `label` FROM `people` WHERE `descr` LIKE '%$search%')",
				//Поля из таблиц контактов
				"(SELECT `people_id` as `id`, CONCAT('Email: ', `email`) AS `label` FROM `people_contacts` WHERE `email` LIKE '%$search%')",
				"(SELECT `people_id` as `id`, CONCAT('Телефон: ', `phone`) AS `label` FROM `people_contacts` WHERE `phone` LIKE '%$search%')",
				"(SELECT `people_id` as `id`, CONCAT('Имя: ', `fio`) AS `label` FROM `people_contacts` WHERE `fio` LIKE '%$search%')",
				"(SELECT `people_id` as `id`, CONCAT('Мобильный: ', `mobile`) AS `label` FROM `people_contacts` WHERE `mobile` LIKE '%$search%')",
				"(SELECT `people_id` as `id`, CONCAT('Комментарий контакта: ', CONCAT(SUBSTRING(`comment`, 1, 50), '...')) AS `label` FROM `people_contacts` WHERE `comment` LIKE '%$search%')",
				//Другие параметры
				"(SELECT `client_id` AS `id`, CONCAT('Сайт: ', `url`) AS `label` FROM `site` WHERE `url` LIKE '{$search}%' OR `url` LIKE 'www.{$search}%')",
				"(SELECT `client_id` AS `id`, CONCAT('Описание заказа: ', CONCAT(SUBSTRING(`descr`, 1, 50), '...')) AS `label` FROM `package` WHERE `descr` LIKE '%{$search}%')",
				"(SELECT `client_id` AS `id`, CONCAT('Описание платежа: ', CONCAT(SUBSTRING(`descr`, 1, 50), '...')) AS `label` FROM `payment` INNER JOIN `package` ON `package`.`id` = `payment`.`package_id` WHERE `description` LIKE '%{$search}%')",
			);

			$query = implode("\nUNION ALL\n", $queries);
			$count_query = "SELECT COUNT(*) FROM (SELECT * FROM ($query) AS `helper` GROUP BY `id`) AS `helper`";
			$result_query = "SELECT * FROM ($query) AS `helper` GROUP BY `id` LIMIT {$offset},{$limit}";
		}

		// дополнительная обертка `label`
		$result_query = "SELECT `helper`.`id`, CONCAT(`people`.`fio`, ' - ', `people`.`mail`, ' (', `helper`.`label`, ')') AS `label` FROM ($result_query) AS `helper` LEFT JOIN `people` ON `people`.`id` = `helper`.`id`";

		$db = Yii::app()->getDb();

		if ($count !== false) {
			$count = $db->createCommand($count_query)->queryScalar();
		}

		$result = $db->createCommand($result_query)->queryAll();

		return $result;
	}

	/**
	 * Функция обходит все промокоды заказов клиента, проверяет их сохранение в базе
	 * и возвращает список найденных промокодов через запятую. Если промокод не был сохранён,
	 * выполняется его добавление в хранилище
	 *
	 * @return string
	 */
	public function proxyPromo() {
		$promocodes = array();

		if (! empty($this->promocodes)) {
			foreach ($this->promocodes as $promocode) {
				$promocodes[$promocode->code]=1;
			}
		}

		foreach ($this->packages(array('with'=>'promocode')) as $package) {
			if ($promocode = $package->promocode) {
				if(!array_key_exists($promocode->code, $promocodes)) {
					// припишем этот промокод клиенту
					$promocode->client_id = $this->primaryKey;
					$promocode->save();
				}
				$promocodes[$promocode->code]=1;
			}
		}
		if(count($promocodes)>0) {
			return implode(',', array_keys($promocodes));
		}
		return false;
	}
}

