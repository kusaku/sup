<?php 
# (c) 2011 aks - FabricaSaitov.ru

/**
 *  Класс для работы с ISPManager
 *  описание работы с API ISPManager через ISPRequest:
 *  https://redmine.fabricasaitov.ru/projects/hostsite/wiki/
 */
 
define('BM_URL', 'https://host.fabricasaitov.ru/manager/billmgr');
define('BM_MANAGER', 'apimanager');
define('BM_PASSWORD', 'rmPyzj3A');

class ISPRequest {

	private $ssl;
	private $url;
	private $headers;
	private $uagent;
	private $cookie;
	private $post;
	private $result;
	private $errcode;
	
	private function parse_xml($xml) {
		$sxmle = new SimpleXMLElement($xml);
		return $sxmle;
	}
	
	public function setSsl($ssl) {
		$this->ssl = $ssl;
		return $this;
	}
	
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}
	
	public function setHeaders($headers) {
		$this->headers = $headers;
		return $this;
	}
	
	public function setUagent($uagent) {
		$this->uagent = $uagent;
		return $this;
	}
	
	public function setCookie($authid = false) {
		$this->cookie = $authid ? "billmgr4=sirius:ru:$authid" : null;
		return $this;
	}
	
	public function setPost($post) {
		$this->post = array_merge($post, array('out'=>'xml'));
		return $this;
	}
	
	public function result() {
		return $this->result;
	}
	
	public function errcode() {
		return $this->errcode;
	}
	
	public function exec() {
		if ( empty($this->url)) {
			return false;
		}
		
		$post = array();
		
		if (is_array($this->post)) {
			foreach ($this->post as $name=>$value) {
				$post[] = $name.'='.urlencode($value);
			}
		}
		
		$post = join('&', $post);
		
		if (function_exists('curl_init')) {
			$ch = curl_init($this->url);
			
			if ($this->ssl) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			}
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024 * 10); // max 10 mb!
			
			if (is_array($this->post)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
			
			if (is_array($this->headers)) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
			}
			
			if (! empty($this->uagent)) {
				curl_setopt($ch, CURLOPT_USERAGENT, $this->uagent);
			}
			
			if (isset($this->cookie)) {
				curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
			}
			
			$result = curl_exec($ch);
			$this->errcode = curl_errno($ch);
			curl_close($ch);
		} else {
			$domain = substr($this->url, strpos($this->url, '//') + 2);
			$path = substr($domain, strpos($domain, '/'));
			$domain = substr($domain, 0, strpos($domain, '/'));
			
			$errno = 0;
			
			if ($this->ssl) {
				$fp = fsockopen("ssl://$domain", 443, $errno);
			} else {
				$fp = fsockopen("http://$domain", 80, $errno);
			}

			
			$send = "";
			$send .= "POST $path HTTP/1.1\r\n";
			$send .= "Host: $domain\r\n";
			$send .= "Content-length: ".strlen($post)."\r\n";
			
			if (is_array($this->headers))
				foreach ($this->headers as $header) {
					$send .= "$header\r\n";
				}
				
			if (! empty($this->uagent)) {
				$send .= "User-Agent: $this->uagent\r\n";
			}
			
			if (isset($this->cookie)) {
				$send .= "Cookie: $this->cookie\r\n";
			}
			
			$send .= "Connection: close\r\n";
			$send .= "\r\n";
			$send .= "$post\r\n\r\n";
			
			fwrite($fp, $send);
			$headers = fread($fp, 1024 * 1024);
			$result = '';
			while (!feof($fp)) {
				$result .= fread($fp, 1024 * 1024);
			}
			$this->errcode = $errno;
			fclose($fp);
		}
		
		if ($this->errcode != 0 && empty($result)) {
			$this->result = false;
		} else {
			$this->result = $this->parse_xml($result);
		}
		
		return $this;
	}
	
	function __construct($url, $post = array(), $ssl = false, $headers = array(), $uagent = '') {
		$this->setUrl($url);
		$this->setPost($post);
		$this->setSsl($ssl);
		$this->setHeaders($headers);
		$this->setUagent($uagent);
		$this->result = false;
		$this->errcode = 0;
	}
}
/**
 *  Класс для работы с BillManager через ISPRequest:
 *  https://redmine.fabricasaitov.ru/projects/hostsite/wiki/
 */
 
class BMRequest extends ISPRequest {
	/**
	 * Флаг работы от имени админа
	 * @var $isManager
	 */
	private $isManager;
	private $loggedIn = false;
	
	/**
	 * Генерация случайного ключа
	 * @param int $len [optional] длина ключа
	 * @return string $key
	 */
	static function generateKey($len = 16) {
		$set = '1234567890abcdef';
		$key = '';
		for ($i = 0; $i < $len; $i++) {
			$key .= $set[(rand() % strlen($set))];
		}
		return $key;
	}
	
	/**
	 * открытие сессии по ключу
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function getAuthKey($data = array()) {
	
		$key = BMRequest::generateKey(32);
		
		$post = array('func'=>'session.newkey', 'key'=>$key);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? BM_MANAGER.':'.BM_PASSWORD : @$data['username'].':'.@$data['passwd'];
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
		} elseif ($result->ok) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'username'=>@$data['username'], 'key'=>$key, 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * регистрация пользователя
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	 
	public function register($data = array()) {
		return $this->accounts($data, true);
	}
	
	/**
	 * XXX в текущей реализации не работает получение данных!!!
	 * изменение/получении информации о пользователе, плательщике, учетке
	 * @param array $data ассоциативный массив с данными
	 * @param bool $save сохранить данные
	 * @return array $result
	 */
	public function accounts($data = array(), $save = false) {
	
		$post = array('func'=>'register');
		$save and $post['sok'] = 'ok';
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
			
		} elseif ($result->ok) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * авторизация пользователя
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function login($data = array()) {
	
		$post = array('func'=>'auth', 'sok'=>'ok');
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->authfail) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'username', 'msg'=>'Auth fail', 'cdata'=>$cdata);
		} elseif ($result->auth) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			// установка сессии
			$this->setCookie($result->auth['id']);
			$this->loggedIn = true;
			return array('success'=>true, 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * сброс сессии
	 * @return array $result
	 */
	public function logout() {
	
		$post = array('func'=>'logon', 'sok'=>'ok');
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			// сброс сессии
			$this->setCookie();
			$this->loggedIn = false;
			return array('success'=>true, 'code'=>$this->errcode(), 'cdata'=>$cdata);
		}
	}
	
	/**
	 * заказ домена, customer и subjnic - id контакта домена,
	 * price - id тарифа
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function orderDomain($data = array()) {
	
		$post = array('func'=>'domain.order.4', 'sok'=>'ok', 'operation'=>'register', 'payfrom'=>'neworder');
		$this->loggedIn or $post['authinfo'] = $this->isManager ? BM_MANAGER.':'.BM_PASSWORD : @$data['username'].':'.@$data['passwd'];
		// XXX регистратор должен быть установлен 1 для spb.ru и msk.ru
		$post['registrar'] = 2;
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
		} elseif ($result->ok) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * заказ хостинга, price - id тарифа
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function orderVhost($data = array()) {
	
		$post = array('func'=>'vhost.order.5', 'sok'=>'ok');
		$this->loggedIn or $post['authinfo'] = $this->isManager ? BM_MANAGER.':'.BM_PASSWORD : @$data['username'].':'.@$data['passwd'];
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
		} elseif ($result->ok) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * запрос списка элементов от имени менеджера
	 * список доступных элементов суть switch в теле
	 * $ret['cdata'] содержит результат $cdata[id_элемента] => array(...)
	 * @param string $type тип элемента
	 * @return array $result
	 */
	public function listItems($type) {
	
		$post = array('func'=>$type);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? BM_MANAGER.':'.BM_PASSWORD : @$data['username'].':'.@$data['passwd'];
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $elem) {
				$celem = array();
				foreach ($elem as $name=>$value) {
					$celem[$name] = (string) $value;
				}
				$cdata[$celem['id']] = $celem;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'List of items', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * запрос/изменение информации об элементе,
	 * список доступных элементов суть switch в теле
	 * $ret['cdata'] содержит результат
	 * @param array $data ассоциативный массив - elid=>id_элемента
	 * @param string $type тип элемента
	 * @param boolean $save [optional] выполнить изменение
	 * @return array $result
	 */
	public function editItem($data, $type, $save = false) {
	
		$post = array('func'=>"$type.edit");
		$save and $post['sok'] = 'ok';
		$this->loggedIn or $post['authinfo'] = $this->isManager ? BM_MANAGER.':'.BM_PASSWORD : @$data['username'].':'.@$data['passwd'];
		
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'Item successfully edited!', 'cdata'=>$cdata);
		}
	}
	
	/**
	 * обертка editItem
	 * сохранеие информации об элементе
	 * список доступных элементов суть switch в теле
	 * $ret['cdata'] содержит результат
	 * @param array $data ассоциативный массив - elid=>id_элемента
	 * @param string $type тип элемента
	 * @param boolean $save [optional] выполнить изменение
	 * @return array $result
	 */
	public function saveItem($data, $type) {
		return $this->editItem($data, $type, true);
	}
	
	/**
	 * обертка editItem
	 * сохранеие информации об элементе
	 * список доступных элементов суть switch в теле
	 * $ret['cdata'] содержит результат
	 * @param array $data ассоциативный массив - elid=>id_элемента
	 * @param string $type тип элемента
	 * @param boolean $save [optional] выполнить изменение
	 * @return array $result
	 */
	public function viewItem($data, $type) {
		return $this->editItem($data, $type, false);
	}
	
	/**
	 * удаление элемента, список доступных элементов суть switch в теле
	 * @param array $data ассоциативный массив - elid=>id_элемента
	 * @param string $type тип элемента
	 * @param boolean $save [optional] выполнить изменение
	 * @return array $result
	 */
	public function deleteItem($data, $type) {
	
		$post = array('func'=>"$type.delete", 'sok'=>'ok');
		$this->loggedIn or $post['authinfo'] = $this->isManager ? BM_MANAGER.':'.BM_PASSWORD : @$data['username'].':'.@$data['passwd'];
		
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array('success'=>false, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'BILLManager error');
		} elseif ($result->error) {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>false, 'code'=>(int) $result->error['code'], 'val'=>(string) $result->error['val'], 'msg'=>(string) $result->error['msg'], 'cdata'=>$cdata);
		} else {
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array('success'=>true, 'code'=>$this->errcode(), 'val'=>'', 'msg'=>'Item successfully deleted!', 'cdata'=>$cdata);
		}
	}
	
	public function __construct($isManager = false) {
		$this->isManager = $isManager;
		$headers = array('Content-type: application/x-www-form-urlencoded');
		parent::__construct(BM_URL, array(), true, $headers);
	}
}

?>
