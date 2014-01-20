<?php 
/**
 * Класс обеспечивает авторизацию пользователя в SUP API
 */
class ApiUserIdentity extends CUserIdentity {
	/**
	 * авторизуем пользователя. если пользователь новый, и есть его учетка в LDAP - заводим нового.
	 * @return boolean
	 */

	public function authenticate($sLoginField='login') {
		$arFilter=array(
			$sLoginField=>$this->username,
		);
		// пробуем найти пользователя по login и psw
		if ($user = People::model()->findByAttributes($arFilter)) {
			if($user->psw==People::hashPassword($this->password)) {
				// сохраняем всякие полезные данные о пользователе
				$this->setState('id', $user->primaryKey);
				$this->setState('login', $this->username);
				$this->setState('password', $this->password);
				$this->setState('fio', $user->fio);
				$this->setState('mail', $user->mail);
				$this->setState('group_id', $user->pgroup_id);
				// ура, залогинились
				$this->errorCode = self::ERROR_NONE;
			} else {
				$this->errorCode = self::ERROR_PASSWORD_INVALID;
			}
		} else {
			// не залогинились
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		}
		return !$this->errorCode;
	}
	/**
	 * Метод авторизует пользователя только по имени
	 */
	public function authenticateSimple($sLoginField) {
		$arFilter=array(
			$sLoginField=>$this->username
		);
		// пробуем найти пользователя по login и psw
		if ($user = People::model()->findByAttributes($arFilter)) {
			// сохраняем всякие полезные данные о пользователе
			$this->setState('id', $user->primaryKey);
			$this->setState('login', $this->username);
			$this->setState('password', $this->password);
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
