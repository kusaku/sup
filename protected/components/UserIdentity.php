<?php 
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
		if (!function_exists('ldap_connect')) {
			throw new CHttpException(500, 'LDAP extension is not installed');
		}
		if (!($config = Yii::app()->params['ldapConfig'])) {
			throw new CHttpException(500, 'LDAP config is not defined');
		}
		try {
			if ($connect = ldap_connect($config['server'])) {
				throw new CHttpException(500, 'Cannot connect to LDAP');
			}
			if (ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3) and ldap_set_option($connect, LDAP_OPT_REFERRALS, 0)) {
				throw new CHttpException(500, 'Cannot set LDAP options');
			}
			if (ldap_bind($connect, "{$this->username}@{$config['domain']}", $this->password)) {
				throw new CHttpException(500, 'Cannot bind LDAP USER');
			}
			if ($search = ldap_search($connect, $config['base_dn'], "(&(objectClass=user)(samaccountname={$this->username}))")) {
				throw new CHttpException(500, 'LDAP user not found');
			}
			if ($info = ldap_get_entries($connect, $search)) {
				throw new CHttpException(500, 'Cannot get LDAP entries');
			}
			return $info[0] or false;
		}
		catch(Exception $e) {
			if (YII_DEBUG) {
				throw $e;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * авторизуем пользователя. если пользователь новый, и есть его учетка в LDAP - заводим нового.
	 * @return boolean
	 */

	public function authenticate() {
	
		// пробуем найти пользователя по login и psw
		if ($user = People::model()->findByAttributes(array(
			'login'=>$this->username,'psw'=>md5($this->password)
		)) or $ldap_user = $this->ldap_authenticate()) {
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
				 '***FS-Бухгалтерия'
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
				
					// админ
					case '***FS-Дирекция':
						$user->pgroup_id = 1;
						break;
					// модератор
					case '***FS-Программисты':
						$user->pgroup_id = 2;
						break;
						
					// старший менеджер
					case '***FS-Бухгалтерия':
						$user->pgroup_id = 3;
						break;
						
					// менеджер
					case '***FS-Менеджеры':
					case '***FS-Менеджеры-МСК':
					case '***FS-Менеджеры-СПБ':
						$user->pgroup_id = 4;
						break;
						
					// мастер
					case '***FS-Вебдизайнеры':
					case '***FS-Вебмастера':
					case '***FS-Хостинг':
					case '***FS-Копирайтеры':
					case '***FS-Удаленщики':
						$user->pgroup_id = 5;
						break;
						
					// маркетолог (?)
					case '***FS-SEO':
						$user->pgroup_id = 12;
						break;
						
					// клиент
					default:
						$user->pgroup_id = 7;
						break;
				}
				// другое
				$user->state = 'Россия';
				$user->phone = '+7 (812) 495-65-54';
				$user->firm = 'Фабрика Сайтов';
				$user->descr = 'Пользователь создан из LDAP, его группы'.implode(', ', $ldap_user['department']);
				
				// сохраняем
				if ($user->save()) {
					// добавляем атрибуты
					foreach (array(
						'email','person','name','phone','fax'
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
			$this->setState('group_id', $user->pgroup_id);
			$this->setState('login', $this->username);
			$this->setState('password', $this->password);
			$this->setState('fio', $user->fio);
			$this->setState('mail', $user->mail);
			$this->setState('rmToken', $user->rm_token);
			
			// ура, залогинились
			$this->errorCode = self::ERROR_NONE;
		} else {
			// не залогинились
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		}
		return !$this->errorCode;
	}
}
