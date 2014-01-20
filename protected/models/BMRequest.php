<?php 

/**
 *  Класс для работы с BillManager через ISPRequest:
 *  https://redmine.fabricasaitov.ru/projects/hostsite/wiki/
 */
 
class BMRequest extends ISPRequest {

	private $config;
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
	public function getAuthKey($data = array(
	)) {
	
		$key = BMRequest::generateKey(32);
		
		$post = array(
			'func'=>'session.newkey',
			'key'=>$key
		);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? $this->config['bm_login'].':'.$this->config['bm_password'] : @$data['username'].':'.@$data['passwd'];
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
		} elseif ($result->ok) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'username'=>@$data['username'],
				'key'=>$key,
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error',
				'cdata'=>$cdata
			);
		}
	}
	
	/**
	 * регистрация пользователя
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	 
	public function register($data = array(
	)) {
		return $this->accounts($data, true);
	}
	
	/**
	 * XXX в текущей реализации не работает получение данных!!!
	 * изменение/получении информации о пользователе, плательщике, учетке
	 * @param array $data ассоциативный массив с данными
	 * @param bool $save сохранить данные
	 * @return array $result
	 */
	public function accounts($data = array(
	), $save = false) {
	
		$post = array(
			'func'=>'register'
		);
		$save and $post['sok'] = 'ok';
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
			
		} elseif ($result->ok) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error',
				'cdata'=>$cdata
			);
		}
	}
	
	/**
	 * авторизация пользователя
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function login($data = array(
	)) {
	
		$post = array(
			'func'=>'auth',
			'sok'=>'ok'
		);
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->authfail) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'username',
				'msg'=>'Auth fail',
				'cdata'=>$cdata
			);
		} elseif ($result->auth) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			// установка сессии
			$this->setCookie($result->auth['id']);
			$this->loggedIn = true;
			return array(
				'success'=>true,
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error',
				'cdata'=>$cdata
			);
		}
	}
	
	/**
	 * сброс сессии
	 * @return array $result
	 */
	public function logout() {
	
		$post = array(
			'func'=>'logon',
			'sok'=>'ok'
		);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			// сброс сессии
			$this->setCookie();
			$this->loggedIn = false;
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'cdata'=>$cdata
			);
		}
	}
	
	/**
	 * заказ домена, customer и subjnic - id контакта домена,
	 * price - id тарифа
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function orderDomain($data = array(
	)) {
	
		$post = array(
			'func'=>'domain.order.4',
			'sok'=>'ok',
			'operation'=>'register',
			'payfrom'=>'neworder'
		);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? $this->config['bm_login'].':'.$this->config['bm_password'] : @$data['username'].':'.@$data['passwd'];
		// XXX регистратор должен быть установлен 1 для spb.ru и msk.ru
		$post['registrar'] = 2;
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
		} elseif ($result->ok) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error',
				'cdata'=>$cdata
			);
		}
	}
	
	/**
	 * заказ хостинга, price - id тарифа
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function orderVhost($data = array(
	)) {
	
		$post = array(
			'func'=>'vhost.order.5',
			'sok'=>'ok'
		);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? $this->config['bm_login'].':'.$this->config['bm_password'] : @$data['username'].':'.@$data['passwd'];
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
		} elseif ($result->ok) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error',
				'cdata'=>$cdata
			);
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
	
		$post = array(
			'func'=>$type
		);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? $this->config['bm_login'].':'.$this->config['bm_password'] : @$data['username'].':'.@$data['passwd'];
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $elem) {
				$celem = array(
				);
				foreach ($elem as $name=>$value) {
					$celem[$name] = (string) $value;
				}
				$cdata[$celem['id']] = $celem;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'List of items',
				'cdata'=>$cdata
			);
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
	
		$post = array(
			'func'=>"$type.edit"
		);
		$save and $post['sok'] = 'ok';
		$this->loggedIn or $post['authinfo'] = $this->isManager ? $this->config['bm_login'].':'.$this->config['bm_password'] : @$data['username'].':'.@$data['passwd'];
		
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'Item successfully edited!',
				'cdata'=>$cdata
			);
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
	
		$post = array(
			'func'=>"$type.delete",
			'sok'=>'ok'
		);
		$this->loggedIn or $post['authinfo'] = $this->isManager ? $this->config['bm_login'].':'.$this->config['bm_password'] : @$data['username'].':'.@$data['passwd'];
		
		$post = array_merge($data, $post);
		
		$result = $this->setPost($post)->exec()->result();
		
		if ($result === false) {
			return array(
				'success'=>false,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'BILLManager error'
			);
		} elseif ($result->error) {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>false,
				'code'=>(int) $result->error['code'],
				'val'=>(string) $result->error['val'],
				'msg'=>(string) $result->error['msg'],
				'cdata'=>$cdata
			);
		} else {
			$cdata = array(
			);
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
			return array(
				'success'=>true,
				'code'=>$this->errcode(),
				'val'=>'',
				'msg'=>'Item successfully deleted!',
				'cdata'=>$cdata
			);
		}
	}
	
	/**
	 * @param object $isManager [optional] работа от имени админа
	 * @return
	 */
	public function __construct($isManager = false) {
		// пытаемся прочитать конфиг
		if (!($this->config = Yii::app()->params['bmConfig']))
			throw new CHttpException(500, 'Billmanager config is not defined');
			
		$this->isManager = $isManager;
		
		$headers = array(
			'Content-type: application/x-www-form-urlencoded'
		);
		
		parent::__construct($this->config['bm_url'], array(
		), true, $headers);
	}
}
