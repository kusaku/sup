<?php
/**
 * Класс обеспечивает ведение сессий для различных токенов переданных при инициализации объекта
 */

class ApiTokens implements IApplicationComponent {
	private $bIsInit=false;
	private $obToken=false;
	private $obData=false;
	private $obApplication=false;
	private $bIsStarted=false;
	public $iLifetime=864000;
	const LETTERS='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()';
	/**
	 * Конструктор класса, выполняет инициализацию внутренних переменных
	 */
	public function __construct() {
		
	}
	
	public function getIsInitialized() {
		return $this->bIsInit;
	}
	
	public function init() {
		if(isset($_GET['token']) && strlen($_GET['token'])==40) {
			if($obToken=FSAPITokens::model()->findByAttributes(array('token'=>$_GET['token']))) {
				if($obToken->expired || strtotime($obToken->date_expire)<time()) {
					$obToken->expired=1;
					$obToken->save();
					throw new CHttpException(504,'Token expired',504);
				} else {
					if($obApplication=FSAPIApplications::model()->findByPk($obToken->application_id)) {
						$this->obToken=$obToken;
						$this->obApplication=$obApplication;
						$this->bIsStarted=true;
					} else {
						$obToken->expired=1;
						$obToken->save();
						throw new CHttpException(504,'Token expired',504);
					}
				}
			}
		}
		$this->bIsInit=true;		
	}
	
	/**
	 * Метод возвращает объект класса ActiveRecord описывающий приложение
	 */
	public function getApplication() {
		if($this->getIsStarted())
			return $this->obApplication;
		return false;
	}
	
	/**
	 * Метод устанавливает авторизацию пользователя в случае, если присутствует авторизация приложения
	 */
	public function setUserId($iUserId) {
		if($this->getIsStarted()) {
			if($this->obToken->user_id>0) {
				if($this->obToken->user_id!=$iUserId) {
					//Ошибочная ситуация, в одной сессии грузятся разные пользователи
					//Убиваем сессию
					$this->obToken->expired=1;
					$this->obToken->save();
					throw new CHttpException(504,'Token expired',504);
				}
			} else {
				$this->obToken->user_id=$iUserId;
				$this->obToken->update(array('user_id'));
			}
		}
		return false;
	}
	
	/**
	 * Метод возвращает ID авторизованного пользователя (если он авторизован)
	 */
	public function getUserId() {
		if($this->getIsStarted())
			return $this->obToken->user_id;
		throw new CHttpException(403,'Auth required',403);
	}
	
	/**
	 * Метод проверяет наличие роли у пользователя
	 */
	public function hasRole($role) {
		if($this->getIsStarted() && $this->obToken->user_id>0) {
			//Пользователь авторизован
			$obModule=Yii::app()->getModule('api');
			return $obModule->getUserAuth()->checkAccess($role,$this->obToken->user_id);
		}
		return false;
	}
	
	/**
	 * Данный метод проверяет уровень доступа с учётом доступа пользователей и требованием к авторизации пользователя
	 */
	public function checkUserAccess($operation, $params=array()) {
		if($this->getIsStarted() && $this->obToken->user_id>0) {
			//Пользователь авторизован
			$obModule=Yii::app()->getModule('api');
			return $obModule->getApplicationAuth()->checkAccess($operation,$this->obToken->application_id,$params)&&$obModule->getUserAuth()->checkAccess($operation,$this->obToken->user_id,$params);
		}
		return false;
	}
	
	/**
	 * Метод возвращает статус запуска сессии приложения
	 */
	public function getIsStarted() {
		return $this->bIsStarted;
	}
	
	/**
	 * Метод генерирует строку токена
	 */
	private function _genTokenString($iAppId,$salt) {
		return sha1($iAppId.time().str_shuffle(self::LETTERS).md5($salt));
	}
	
	/**
	 * Метод возвращает дату создания токена, если токен не был создан, возвращает текущее время
	 */
	public function getDateAdd() {
		if($this->getIsStarted()) {
			return strtotime($this->obToken->date_add);
		}
		return time();
	}
	
	/**
	 * Метод возвращает дату истечения токена. Если токен не авторизован возвращает текущее время
	 */
	public function getExpireDate() {
		if($this->getIsStarted()) {
			return strtotime($this->obToken->date_expire);
		}
		return time();
	}
	
	/**
	 * Метод устанавливает дату истечения токена, если токен не авторизован, возвращает false
	 */
	public function setExpireDate($iTime) {
		if($this->getIsStarted()) {
			$this->obToken->date_expire=date('Y-m-d H:i:s',$iTime);
			if($iTime<time())
				$this->obToken->expired=1;
			$this->obToken->save();
		}
		return false;
	}
	
	/**
	 * Метод генерирует новый токен
	 * @param $iAppId - ID приложения для которого генерируется токен
	 * @param $salt - соль добавляемая к токену
	 * @return array(<token>,<expire>)
	 */
	public function NewToken($iAppId,$salt='') {
		$sToken=$this->_genTokenString($iAppId, $salt);
		///@todo Исправить перегенерацию токена 
		if($obToken=FSAPITokens::model()->findByAttributes(array('token'=>$sToken)))
			$sToken=$this->_genTokenString($iAppId, $salt.$sToken);
		$obToken=new FSAPITokens();
		$obToken->token=$sToken;
		$obToken->application_id=$iAppId;
		$obToken->date_add=date('Y-m-d H:i:s');
		$iExpire=time()+$this->iLifetime;
		$obToken->date_expire=date('Y-m-d H:i:s',$iExpire);
		$obToken->previous_token_id=0;
		$obToken->user_id=0;
		$obToken->expired=0;
		$obToken->save();
		return array($sToken,$iExpire);
	}
	
	/**
	 * Метод выполняет перевод сессии с одним номером токена на сессиию с новым токеном
	 * в случае, если исходный токен не найден, выбрасывается исключение CException
	 * @param $sToken - старый токен, который требуется обновить
	 */
	public function UpdateToken($sToken,$iAppId,$salt='') {
		if($obOldToken=FSAPITokens::model()->findByAttributes(array('token'=>$sToken))) {
			$sToken=$this->_genTokenString($iAppId, $salt.$sToken);
			///@todo Исправить перегенерацию токена 
			if($obToken=FSAPITokens::model()->findByAttributes(array('token'=>$sToken)))
				$sToken=$this->_genTokenString($iAppId, $salt.$sToken);
			$obToken=new FSAPITokens();
			$obToken->token=$sToken;
			$obToken->application_id=$iAppId;
			$obToken->date_add=date('Y-m-d H:i:s');
			$iExpire=time()+$this->iLifetime;
			$obToken->date_expire=date('Y-m-d H:i:s',$iExpire);
			$obToken->previous_token_id=$obOldToken->id;
			$obToken->user_id=$obOldToken->user_id;
			$obToken->expired=0;
			$obToken->save();
			return array($obToken->token,$iExpire);
		} else {
			throw new Exception('Old token not found');
		}
	}
}
