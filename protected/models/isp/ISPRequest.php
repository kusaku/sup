<?php
/**
 *  Класс для работы с ISPManager
 *  описание работы с API ISPManager через ISPRequest:
 *  https://redmine.fabricasaitov.ru/projects/hostsite/wiki/
 */
class ISPRequest {
	private $ssl;
	private $url;
	private $headers;
	private $uagent;
	private $cookie;
	private $post;
	private $result;
	private $errcode;
	private $errtext;

	/**
	 * Конструктор класса, 
	 * @param string $url - адрес
	 * @param mixed $post - массив данных для post запроса
	 * @param bool $ssl - 
	 * @param mixed $headers - массив данных содержащий заголовки запроса
	 * @param string $uagent - строка user-agent 
	 */
	function __construct($url, $post=false, $ssl=false, $headers=false, $uagent='') {
		$this->setUrl($url);
		if($post)
			$this->setPost($post);
		$this->setSsl($ssl);
		if($headers)
			$this->setHeaders($headers);
		$this->setUagent($uagent);
		$this->result=false;
		$this->errcode=0;
	}

	/**
	 * @param $xml string
	 *
	 * @return SimpleXMLElement
	 */
	private function parse_xml($xml) {
		return new SimpleXMLElement($xml);
	}

	public function setSsl($ssl) {
		$this->ssl=$ssl;
		return $this;
	}

	public function setUrl($url) {
		$this->url=$url;
		return $this;
	}

	public function setHeaders($headers) {
		$this->headers=$headers;
		return $this;
	}

	public function setUagent($uagent) {
		$this->uagent=$uagent;
		return $this;
	}

	public function setCookie($authid=false) {
		$this->cookie=$authid?"billmgr4=sirius:ru:$authid":NULL;
		return $this;
	}

	public function setPost(array $post) {
		$this->post=array_merge($post, array('out'=>'xml'));
		return $this;
	}

	/**
	 * @return SimpleXMLElement|bool
	 */
	public function result() {
		return $this->result;
	}

	public function errcode() {
		return $this->errcode;
	}
	
	public function errtext() {
		return $this->errtext;
	}

	public function exec() {
		if (empty($this->url)) {
			return false;
		}

		$post='';
		if (is_array($this->post)) {
			$post=$this->EncodePostData($this->post);
		}
		
		if (function_exists('curl_init')) {
			$curl=curl_init($this->url);
			if ($this->ssl) {
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}
			// если есть прокси
			if ($proxy=Yii::app()->params['proxyConfig']) {
				if (count(@list($server, $port)=explode(':', $proxy['server']))==2) {
					curl_setopt($curl, CURLOPT_PROXY, $server);
					curl_setopt($curl, CURLOPT_PROXYPORT, $port);
				} else {
					curl_setopt($curl, CURLOPT_PROXY, $proxy['server']);
				}
				if ($proxy['login'] and $proxy['password']) {
					// вид авторизации можно было бы тоже брать из конфига
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, "{$proxy['login']}:{$proxy['password']}");
					curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC|CURLAUTH_NTLM);
				}
			}
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_BUFFERSIZE, 1024*1024*10);
			// max 10 mb!
			if (is_array($this->post)) {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			}
			if (is_array($this->headers)) {
				curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
			}
			if (!empty($this->uagent)) {
				curl_setopt($curl, CURLOPT_USERAGENT, $this->uagent);
			}
			if (isset($this->cookie)) {
				curl_setopt($curl, CURLOPT_COOKIE, $this->cookie);
			}
			$result=curl_exec($curl);
			$this->errcode=curl_errno($curl);
			$this->errtext=curl_error($curl);
			curl_close($curl);
		} else {
			throw new CException('Not implemented',1);
		}

		if ($this->errcode!=0&&empty($result)) {
			$this->result=false;
		} else {
			$this->result=$this->parse_xml($result);
		}
		return $this;
	}

	/**
	 * Метод кодирует массив в формат x-www-form-encode. Поддерживает многоуровневые массивы.
	 * @param array $data массив ключ=>значение который необходимо закодировать
	 * @return bool|string
	 */
	public function EncodePostData($data) {
		if(is_array($data)) {
			$arResult=array();
			self::_encodeArray($arResult,$data);
			return join('&',$arResult);
		}
		return false;
	}

	/**
	 * Рекурсивный метод для обхода массива $arToEncode и превращения его
	 * в массив строк $arResult, которые являются полным именем каждого элемента
	 * массива $arToEncode. При этом элементы $arToEncode, которые являются
	 * пустыми массивами, игнорируются.
	 *
	 * @param string[] $arResult результирующий строковый массив
	 * @param array $arToEncode массив для обхода
	 * @param string[] $arPrefix массив строковых префиксов
	 */
	private static function _encodeArray(&$arResult, $arToEncode, $arPrefix = array()){
		foreach($arToEncode as $aKey => $aValue) {
			if(is_array($aValue)){
				self::_encodeArray($arResult, $aValue, array_merge($arPrefix, array($aKey)));
			}
			else {
				$strResult = '';
				$level = 0;
				foreach ($arPrefix as $strPrefix) {
					$strResult .= $level == 0 ? urlencode($strPrefix) : "[" .urlencode($strPrefix). "]";
					$level++;
				}
				$strResult .= $level > 0 ? "[" .urlencode($aKey). "]=" .urlencode($aValue) : urlencode($aKey). "=" .urlencode($aValue);
				array_push($arResult, $strResult);
			}
		}
	}
}
