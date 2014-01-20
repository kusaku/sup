<?php
/**
 * Класс обеспечивает взаимодействие с BM panel
 * @method BillManagerDomainRegister createDomainRegisterModel()
 * @method BillManagerDomainContact createDomainContactModel()
 */
class ISPConnection extends CComponent {
	public $bm_url;
	public $bm_login;
	public $bm_password;
	/**
	 * @var $obConnection ISPRequest
	 */
	private $obConnection;
	private $bIsLogin;
	private $sLoginSession;
	private $bUserLogin;
	private $sUserLogin;
	private $sUserLoginSession;
	private $sAuthSession;

	/**
	 * Метод выполняет инициализацию объекта и подготавливает объект запроса к BM
	 */
	function init() {
		$this->obConnection=new ISPRequest($this->bm_url,false,true);
		$this->bIsLogin=false;
		$this->sAuthSession='';
	}
	
	/**
	 * Деструктор контроллирует наличие открытой сессии авторизации и если она есть вызывает метод закрывающий соединение
	 */
	public function __destruct() {
		if($this->bIsLogin)
			$this->logout();
		parent::__destruct();
	}
	
	/**
	 * "Магический" метод выполняющий обработку запросов к не заданным методам. Используется для реализации группы методов create*Model
	 */
	public function __call($name,$arguments) {
		if(preg_match('#^create([A-z]+)Model$#',$name,$matches)) {
			return $this->createRequest($matches[1]);
		}
		return NULL;
	}
	
	/**
	 * Метод инициализирует класс запроса и возвращает его
	 */
	public function createRequest($sRequest) {
		$sClassName='BillManager'.$sRequest;
		$obModel=new $sClassName();
		$obModel->setConnection($this);
		return $obModel;
	}

	/**
	 * Метод выполняет авторизацию текущего соединения
	 */
	public function login() {
		if($this->bIsLogin)
			return true;
		$arRequest=array(
			'func'=>'auth',
			'username'=>$this->bm_login,
			'password'=>$this->bm_password,
			'sok'=>'ok'
		);
		$obResult=$this->sendPost($arRequest);
		if($obResult->authfail) {
			throw new ISPAnswerException('Wrong login or password, BM unavailable',1);
		} elseif($obResult->auth) {
			$this->sLoginSession=(string) $obResult->auth;
			$this->bIsLogin=true;
		} elseif($obResult->expirepass) {
			throw new ISPAnswerException('Password expired please update password',3);
		} else {
			throw new ISPAnswerException('Wrong protocol answer',2);
		}
		return true;
	}

	/**
	 * Метод выполняет разавторизацию текущего соединения
	 */
	public function logout() {
		$arRequest=array(
			'func'=>'logon'
		);
		$this->sendPost($arRequest);
		$this->bIsLogin=false;
	}

	private function generateUserLoginKey($username) {
		return md5($username.time().rand(1000,9999));
	}

	/**
	 * Метод выполняет авторизацию от имени пользователя исходя из того, что я авторизован под менеджером
	 * @param $username string
	 *
	 * @throws ISPRequestException
	 * @throws ISPAnswerException
	 * @return bool
	 */
	public function loginUser($username) {
		if($this->login()) {
			if($this->bUserLogin && $this->sUserLogin!=$username) {
				throw new ISPRequestException('Cant\'t login as another user while login as user.');
			}
			$sUserLoginKey=$this->generateUserLoginKey($username);
			$arRequest=array(
				'func'=>'session.newkey',
				'username'=>$username,
				'key'=>$sUserLoginKey,
			);
			$obResult=$this->asAdmin()->sendPost($arRequest);
			if($obResult->ok) {
				$arRequest=array(
					'func'=>'auth',
					'username'=>$username,
					'key'=>$sUserLoginKey,
					'checkcookie'=>'no',
				);
				$obResult=$this->asGuest()->sendPost($arRequest);
				if($obResult->authfail) {
					throw new ISPAnswerException('Wrong login or password, BM unavailable',2);
				} elseif($obResult->auth) {
					$this->sUserLogin=$username;
					$this->sUserLoginSession=(string) $obResult->auth;
					$this->bUserLogin=true;
				} else {
					throw new ISPAnswerException('Wrong protocol answer',3);
				}
			} else {
				throw new ISPAnswerException('Wrong protocol answer',4);
			}
		} else {
			throw new ISPAnswerException('Wrong admin login or password, BM unavailable',3);
		}
		return true;
	}

	public function logoutUser() {

	}

	public function asUser() {
		if(!$this->bUserLogin) {
			throw new ISPRequestException('Not authored as user');
		}
		$this->sAuthSession=$this->sUserLoginSession;
		return $this;
	}

	public function asAdmin() {
		$this->login();
		$this->sAuthSession=$this->sLoginSession;
		return $this;
	}

	public function asGuest() {
		$this->sAuthSession='';
		return $this;
	}

	/**
	 * @param $arData
	 *
	 * @return SimpleXMLElement|bool
	 * @throws ISPRequestException
	 */
	public function sendPost($arData) {
		if($this->sAuthSession!='') {
			$arData['auth']=$this->sAuthSession;
		}
		$result=$this->obConnection->setPost($arData)->exec()->result();
		if($result===false) {
			throw new ISPRequestException($this->obConnection->errtext(),$this->obConnection->errcode());
		} 
		return $result;
	}
}

