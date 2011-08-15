<?php 
/**
 * Параметры нашего LDAP-сервера:
 */
define('LDAP_SERVER', 'ldap://192.168.0.1');
define('LDAP_DOMAIN', 'fabrica.local');
define('LDAP_BASE_DN', 'dc=fabrica,dc=local');

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {
	/**
	 * аутентификация по LDAP
	 * @return array
	 */
	public function ldap_authenticate() {
		return ($connect = @ldap_connect(LDAP_SERVER)
		//
		and @ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3)
		//
		and @ldap_set_option($connect, LDAP_OPT_REFERRALS, 0)
		//
		and @ldap_bind($connect, "{$this->username}@".LDAP_DOMAIN, $this->password)
		// получаем данные этого пользователя
		and $search = @ldap_search($connect, LDAP_BASE_DN, "(&(objectClass=user)(samaccountname={$this->username}))")
		//
		and $info = @ldap_get_entries($connect, $search)
		//
		and isset($info[0])) ?
		//
		$info[0] : false;
	}
	
	/**
	 * авторизуем пользователя. если пользователь новый, и есть его учетка в LDAP - заводим нового.
	 * @return boolean
	 */
	public function authenticate() {
		if ($user = People::getByLogin($this->username) and $user->psw == md5($this->password) or $ldap_user = $this->ldap_authenticate()) {
			// регистрируем пользователя - он есть в LDAP, но нет в SUP
			if (!isset($user)) {
				$user = new People();
				$user->login = $this->username;
				$user->psw = md5($this->password);
				// данные из LDAP
				$user->fio = @$ldap_user['displayname'][0];
				$user->mail = @$ldap_user['mail'][0];
				
				/*
				 // группы, которые сейчас есть в LDAP:
				 '***FS-Дирекция'
				 '***FS-Менеджеры-МСК'
				 '***FS-Менеджеры-СПБ'
				 '***FS-SEO'
				 '***FS-Вебдизайнеры'
				 '***FS-Удаленщики'
				 '***FS-Программисты'
				 '***FS-Вебмастера'
				 '***FS-Копирайтеры'
				 '***FS-Хостинг'
				 '***FS-Мониторинг'
				 */
				
				// назначаем группу пользователю
				switch ($ldap_user['department'][0]) {
				
					case '***FS-Дирекция':
						$user->pgroup_id = 1;
						break;
						
					case '***FS-Программисты':
						$user->pgroup_id = 2;
						break;
						
					case '***FS-Менеджеры':
					case '***FS-Менеджеры-МСК':
					case '***FS-Менеджеры-СПБ':
						$user->pgroup_id = 4;
						break;
						
					case '***FS-Вебдизайнеры':
					case '***FS-Вебмастера':
					case '***FS-Хостинг':
					case '***FS-SEO':
					case '***FS-Копирайтеры':
					case '***FS-Удаленщики':
						$user->pgroup_id = 5;
						break;
						
					// по-умолчанию - клиент
					default:
						$user->pgroup_id = 7;
						break;
				}
				// другое
				$user->state = 'Россия';
				$user->phone = '+7 (812) 495-65-54';
				$user->firm = 'Фабрика Сайтов';
				$user->descr = 'Пользователь создан из LDAP';
				
				// сохраняем
				if ($user->save()) {
					// добавляем атрибуты
					foreach (array(
						'email', 'person', 'name', 'phone', 'fax'
					) as $name) {
						$attr = new PeopleAttr();
						$attr->people_id = $user->primaryKey;
						$attr->attribute_id = Attributes::getByType($name)->primaryKey;
						switch ($name) {
							case 'email':
								$attr->value = @$ldap_user['mail'][0];
								break;
							case 'person':
								$attr->value = @$ldap_user['displayname'][0];
								break;
							case 'name':
								$attr->value = 'Фабрика Сайтов';
								break;
							case 'phone':
								$attr->value = '+7 (812) 495-65-54';
								break;
							case 'fax':
								$attr->value = '+7 (812) 495-65-54';
								break;
						}
						$attr->save();
					}
				}
			}
			
			// сохраняем всякие полезные данные о пользователе
			$this->setState('id', $user->primaryKey);
			$this->setState('login', trim($user->login));
			$this->setState('key', base64_encode($this->password));
			$this->setState('fio', $user->fio);
			$this->setState('mail', $user->mail);
			$this->setState('group_id', $user->pgroup_id);
			
			// ура, залогинились
			$this->errorCode = self::ERROR_NONE;
		} else {
			// не залогинились
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		}
		return !$this->errorCode;
	}
}
