<?php 
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate() {
	
		$user = People::getByLogin($this->username);
		
		if (!isset($user))
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else if ($user->psw !== md5($this->password))
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		else {
			// Успешно залогинились!
			$this->errorCode = self::ERROR_NONE;
			
			// Сохраняем всякие полезные данные о пользователе
			$this->setState('id', $user->id);
			$this->setState('login', trim($user->login));
			$this->setState('key', base64_encode($this->password));
			$this->setState('fio', $user->fio);
			$this->setState('mail', $user->mail);
			$this->setState('group_id', $user->pgroup_id);			
		}
		
		return !$this->errorCode;
	}
	
	/*
	 public function authenticate()
	 {
	 $users=array(
	 // username => password
	 'demo'=>'demo',
	 'admin'=>'admin',
	 );
	 if(!isset($users[$this->username]))
	 $this->errorCode=self::ERROR_USERNAME_INVALID;
	 else if($users[$this->username]!==$this->password)
	 $this->errorCode=self::ERROR_PASSWORD_INVALID;
	 else
	 $this->errorCode=self::ERROR_NONE;
	 return !$this->errorCode;
	 }
	 */
	
}
