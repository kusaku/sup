<?php 
/*
 * This file is part of StatusMine.
 *
 * StatusMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * StatusMine is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MailTeleport. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * коннектор Redmine
 */

class RedmineConnector {
	/**
	 * получить о курле
	 * @param resourse $curl
	 * @return
	 */

	private static function getCurlInfo($curl) {
		$errors = '';
		foreach (curl_getinfo($curl) as $key=>$value)
			$errors .= "\n{$key}: {$value}";
		return $errors;
	}
	
	/**
	 * получить инфо об ошибке XML
	 * @return
	 */

	private static function getXMLErrors() {
		$errors = '';
		foreach (libxml_get_errors() as $error) {
			$errors .= "line {$error->line}: {$error->message}";
		}
		return $errors;
	}
	
	/**
	 * запрос к Redmine
	 * @param string $function url функции
	 * @param string $data [optional] параметры (XML)
	 * @param object $method [optional] метод запроса
	 * @return SimpleXMLElement
	 */

	public static function runRequest($function, $data = null, $method = 'GET') {
	
		// $cache - здесь кешируем все GET запросы в течении жизни проложения

		static $cache, $config, $proxy;
		
		$http_headers = array(
		);
		
		// кодировка UTF-8
		$http_headers[] = 'Content-Type: text/xml; charset=UTF-8';
		// длина сообщения, если есть
		if (isset($data)) {
			$http_headers[] = 'Content-Length: '.strlen($data);
		}
		
		// уникальный хеш запроса
		$hash = md5($function.','.$data);
		
		// если не GET запрос или нет в кеше
		if ($method != 'GET' or !isset($cache[$hash])) {
		
			// плюемся исключением, если нет курля
			if (!function_exists('curl_init'))
				throw new CHttpException(500, 'cURL is not installed');
				
			// пытаемся прочитать конфиг
			if (!($config or $config = Yii::app()->params['redmineConfig']))
				throw new CHttpException(500, 'Redmine config is not defined');
				
			// открываем ресурс
			$curl = curl_init();
			
			// формируем url запроса
			curl_setopt($curl, CURLOPT_URL, "{$config['url']}/{$function}");
			
			// нужен ли порт?
			//curl_setopt($curl, CURLOPT_PORT, $config['port']);
			
			// ставим курлю тип запроса
			$method = strtoupper(trim($method));
			switch ($method) {
				case 'POST':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					break;
				case 'PUT':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					break;
				case 'DELETE':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					break;
				default:
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				case 'GET':
					break;
			}
			
			// в некоторых случаях нужно использовать суперадимина
			if (stristr($function, 'users.xml') or stristr($function, 'projects.xml')) {
				// от имени суперадмина
				if ( empty($config['token'])) {
					curl_setopt($curl, CURLOPT_USERPWD, "{$config['login']}:{$config['password']}");
				} else {
					$http_headers[] = 'X-Redmine-API-Key: '.$config['token'];
				}
			} else {
				// от имени текущего пользователя
				if ( empty(Yii::app()->user->rmToken)) {
					curl_setopt($curl, CURLOPT_USERPWD, Yii::app()->user->login.':'.Yii::app()->user->password);
				} else {
					$http_headers[] = 'X-Redmine-API-Key: '.Yii::app()->user->rmToken;
				}
			}
			// пробуем все типы авторизации
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			
			// если есть прокси
			if ($proxy or $proxy = Yii::app()->params['proxyConfig']) {
				if (count(@list($server,$port) = explode(':', $proxy['server'])) == 2) {
					curl_setopt($curl, CURLOPT_PROXY, $server);
					curl_setopt($curl, CURLOPT_PROXYPORT, $port);
				} else {
					curl_setopt($curl, CURLOPT_PROXY, $proxy['server']);
				}
				if ($proxy['login'] and $proxy['password']) {
					// вид авторизации можно было бы тоже брать из конфига
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, "{$proxy['login']}:{$proxy['password']}");
					curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
				}
			}
			
			// не проверяем SSL сертификаты
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			
			// каждый раз открывать новую сессию
			curl_setopt($curl, CURLOPT_COOKIESESSION, true);
			
			// быть разговорчивым
			//curl_setopt($curl, CURLOPT_VERBOSE, true);
			
			// возвращать результат curl_exec()
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			// не нужны HTTP заголовки
			curl_setopt($curl, CURLOPT_HEADER, false);
			// следовать редиректам (301, 302, Location: ...)
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			// 10 метров буфера, должно хватить
			curl_setopt($curl, CURLOPT_BUFFERSIZE, 1024 * 1024 * 10); // max 10 mb!
			// ставим заголовки
			curl_setopt($curl, CURLOPT_HTTPHEADER, $http_headers);
			
			// делаем запрос
			if (false === $response = curl_exec($curl))
				throw new CHttpException(500, 'cURL request failed: '.self::getCurlInfo($curl));
				
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			// проверяем http код
			switch ($method) {
				case 'POST':
					if (!in_array($http_code, array(
						201,422
					)))
						throw new CHttpException(500, "cURL request failed (http code $http_code): ".self::getCurlInfo($curl));
					break;
				case 'PUT':
					if (!in_array($http_code, array(
						200,422
					)))
						throw new CHttpException(500, "cURL request failed (http code $http_code): ".self::getCurlInfo($curl));
					break;
				case 'DELETE':
					if (!in_array($http_code, array(
						200,422
					)))
						throw new CHttpException(500, "cURL request failed (http code $http_code): ".self::getCurlInfo($curl));
					break;
				case 'GET':
					if (!in_array($http_code, array(
						200,422
					)))
						throw new CHttpException(500, "cURL request failed (http code $http_code): ".self::getCurlInfo($curl));
					break;
				default:
					break;
			}
			
			// закрываем ресурс
			curl_close($curl);
			
			// кэшируем!
			$method == 'GET' and $cache[$hash] = $response;
		} else {
			$response = $cache[$hash];
		}
		
		libxml_use_internal_errors(true);
		
		if (false === $sxml = simplexml_load_string($response))
			throw new CHttpException(500, 'XML is not well-formed: '.self::getXMLErrors(), 1);
			
		return $sxml;
	}
	
	/**
	 * преобразование xml в массив
	 * @param object $xml XML
	 * @param string $index [optional] использовать значение этого ребенка как индекс
	 * @param bool $withAttributes [optional] вывести атрибуты базового элемента
	 * @return
	 */

	protected static function xml2array($xml, $index = false, $withAttributes = false) {
	
		$array = array(
		);
		
		foreach ($xml->children() as $child=>$node) {
			// если у элемента несколько одинаковых детей, дети индексируются
			// по своему атрибуту $index или елементу $index, если его нет - по порядку
			if (count($xml->$child) > 1) {
				// у нас несколько одинаковых детей, атрибуты
				// выводить нельзя - они испортят индекс
				$withAttributes = false;
				if ($index and (string) $node[$index]) {
					$array[(string) $node[$index]] = self::xml2array($node, $index);
					
				} elseif ($index and (string) $node->$index) {
					$array[(string) $node->$index] = self::xml2array($node, $index);
					
				} else {
					echo (string) $node->$index;
					$array[] = self::xml2array($node, $index, true);
				}
				
			}
			// если же у ребенка тоже есть несколько детей или атрибутов, идем вглубь
			elseif (count($node->children()) + count($node->attributes()) > 1) {
				$array[$child] = self::xml2array($node, $index, true);
			}
			// если же нет, вернем значение элемента
			else {
				$array[$child] = (string) $node;
			}
		}
		
		// дополнительно выведем атрибуты элемента, но это далеко не всегда надо
		if ($withAttributes) {
			foreach ($xml->attributes() as $name=>$value) {
				$array[$name] = (string) $value;
			}
		}
		return $array;
	}
	
	/**
	 * преобразование из массива в xml
	 * @param object $base имя корневого элемента
	 * @param object $children [optional] массив с детьми
	 * @param object $xml [optional] над элементом объектом работать
	 * @return SimpleXMLElement
	 */

	protected static function array2xml($base, $children = null, &$xml = null) {
	
		$children or $children = array(
		);
		$xml or $xml = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><{$base}/>");
		
		foreach ($children as $key=>$value) {
			if (is_array($value))
				if ($key == '@attributes') {
					foreach ($value as $name=>$attr)
						$xml->addAttribute($name, $attr);
				} else {
				self::array2xml($key, $value, $xml);
			}
			else
				$xml->addChild($key, $value);
		}
		
		return $xml;
	}
}

/**
 * модель Redmine
 */

class RedmineModel extends RedmineConnector {

	/**
	 * создать задачу
	 * @param array $data параметы
	 * @return array
	 */

	public static function createIssue($data) {
		try {
			$xml = self::array2xml('issue', $data);
			return self::xml2array(self::runRequest('issues.xml', $xml->asXML(), 'POST'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить список задач
	 * @param string $index [optional] по какому полю проекта проидексировать список
	 * @param array $params [optional] параметры:
	 * string $params['sort'] [optional] sorting parameters
	 * string $params['project_id'] [optional] get issues from the project with the given id
	 * int $params['tracker_id'] [optional] get issues from the tracker with the given id
	 * int $params['status_id'] [optional] get issues with the given status id only (you can use * to get open and closed issues)
	 * int $params['assigned_to_id'] [optional] get issues which are assigned to the given user id
	 * @return array
	 */

	public static function readIssues($index = 'id', $params = null, $useCache = true) {

		static $cached;
		
		$query = '';
		
		isset($params['sort']) and $query .= "&sort={$params['sort']}";
		isset($params['project_id']) and $query .= "&project_id={$params['project_id']}";
		isset($params['tracker_id']) and $query .= "&tracker_id={$params['tracker_id']}";
		isset($params['status_id']) and $query .= "&status_id={$params['status_id']}";
		isset($params['assigned_to_id']) and $query .= "&assigned_to_id={$params['assigned_to_id']}";
		
		isset($params['start']) and $start = $params['start'] or $start = 0;
		isset($params['count']) and $count = $params['count'] or $count = PHP_INT_MAX;
		
		$hash = md5($index.','.$query.','.$start.','.$count);
		
		if (!isset($cached[$hash])) {
			if ($useCache and $cached[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cached[$hash];
			// если много данных не влезло
			elseif ($useCache and $cached[$hash] = Persistent::getData(__METHOD__.'.'.$hash.'.0')) {
				$index = 1;
				while ($partial = Persistent::getData(__METHOD__.'.'.$hash.'.'.($index++))) {
					$cached[$hash] += $partial;
				}
				return $cached[$hash];
			}
			
			try {
				for ($cached[$hash] = array(
				), $offset = $start, $limit = $count < 50 ? $count : 50; count($cached[$hash]) < $count and count($data = self::xml2array(self::runRequest("issues.xml?offset={$offset}&limit={$limit}{$query}"), $index)); $offset += $limit) {
					$cached[$hash] += $data;
				}
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			// много данных не влезет, разобъем по 100
			if (($count = count($cached[$hash])) > 100) {
				for ($index = 0, $offset = 0, $limit = 100; $offset < $count; $index++, $offset += $limit) {
					$partial = array_slice($cached[$hash], $offset, $limit, true);
					// а еще - увеличим время хранения - процедура получения слишком дорого обходится
					Persistent::setData(__METHOD__.'.'.$hash.'.'.$index, $partial, '+10 minutes');
				}
			} else {
				Persistent::setData(__METHOD__.'.'.$hash, $cached[$hash]);
			}
		}
		
		return $cached[$hash];
	}
	
	/**
	 * получить задачу
	 * @param int $issue_id ID задачи
	 * @return array
	 */

	public static function readIssue($issue_id, $useCache = true) {

		static $cache;
		
		$hash = md5($issue_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("issues/{$issue_id}.xml?include=relations,journals"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+ 12 hours');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * обновить задачу
	 * @param int $issue_id ID задачи
	 * @param array $data параметы
	 * @return array
	 */

	public static function updateIssue($issue_id, $data) {
		try {
			$xml = self::array2xml('issue', $data);
			return self::xml2array(self::runRequest("issues/{$issue_id}.xml", $xml->asXML(), 'PUT'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * удалить задачу
	 * @param int $version_id ID задачи
	 * @return array
	 */

	public static function deleteIssue($issue_id) {
		try {
			return self::xml2array(self::runRequest("issues/{$issue_id}.xml", null, 'DELETE'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * создать проект
	 * @param array $data параметы
	 * @return array
	 */

	public static function createProject($data) {
		try {
			$xml = self::array2xml('project', $data);
			return self::xml2array(self::runRequest('projects.xml', $xml->asXML(), 'POST'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить список проектов
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */

	public static function readProjects($index = 'id', $useCache = true) {

		static $cache;
		
		$hash = md5($index);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				for ($cache[$hash] = array(
				), $offset = 0, $limit = 50; count($data = self::xml2array(self::runRequest("projects.xml?offset={$offset}&limit={$limit}"), $index)); $offset += $limit) {
					$cache[$hash] += $data;
				}
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+1 day');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * получить проект
	 * @param string $project_id ID проекта
	 * @return array
	 */

	public static function readProject($project_id, $useCache = true) {

		static $cache;
		
		$hash = md5($project_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("projects/{$project_id}.xml"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+ 1 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * обновить проект
	 * @param string $project_id ID проекта
	 * @param array $data параметы
	 * @return array
	 */

	public static function updateProject($project_id, $data) {
		try {
			$xml = self::array2xml('project', $data);
			return self::xml2array(self::runRequest("projects/{$project_id}.xml", $xml->asXML(), 'PUT'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * удалить проект
	 * @param string $version_id ID проекта
	 * @return array
	 */

	public static function deleteProject($project_id) {
		try {
			return self::xml2array(self::runRequest("projects/{$project_id}.xml", null, 'DELETE'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * создать пользователя
	 * @param array $data параметы
	 * @return array
	 */

	public static function createUser($data) {
		try {
			$xml = self::array2xml('project', $data);
			return self::xml2array(self::runRequest('users.xml', $xml->asXML(), 'POST'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить список пользователей
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */

	public static function readUsers($index = 'id', $useCache = true) {

		static $cache;
		
		$hash = md5($index);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				for ($cache[$hash] = array(
				), $offset = 0, $limit = 50; count($data = self::xml2array($u = self::runRequest("users.xml?offset={$offset}&limit={$limit}"), $index)); $offset += $limit) {
					$cache[$hash] += $data;
				}
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+3 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * получить пользователя
	 * @param int $user_id ID пользователя
	 * @return array
	 */

	public static function readUser($user_id, $useCache = true) {

		static $cache;
		
		$hash = md5($user_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("users/{$user_id}.xml?include=memberships"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+ 1 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * обновить пользователя
	 * @param int $user_id ID пользователя
	 * @param array $data параметы
	 * @return array
	 */

	public static function updateUser($user_id, $data) {
		try {
			$xml = self::array2xml('project', $data);
			return self::xml2array(self::runRequest("users/{$user_id}.xml", $xml->asXML(), 'PUT'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * удалить пльзователя
	 * @param int $version_id ID пользователя
	 * @return array
	 */

	public static function deleteUser($user_id) {
		try {
			return self::xml2array(self::runRequest("users/{$user_id}.xml", null, 'DELETE'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * создать запись времени
	 * @param array $data параметы
	 * @return array
	 */

	public static function createTimeEntry($data) {
		try {
			$xml = self::array2xml('project', $data);
			return self::xml2array(self::runRequest('time_entries.xml', $xml->asXML(), 'POST'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * обновить запись времени
	 * @param int $time_entry_id ID записи времени
	 * @param array $data параметы
	 * @return array
	 */

	public static function updateTimeEntry($time_entry_id, $data) {
		try {
			$xml = self::array2xml('project', $data);
			return self::xml2array(self::runRequest("time_entries/{$time_entry_id}.xml", $xml->asXML(), 'PUT'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить список записей времени
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */

	public static function readTimeEntries($index = 'id', $useCache = true) {

		static $cache;
		
		$hash = md5($index);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest('time_entries.xml'), $index);
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+1 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * получить запись времени
	 * @param int $time_entry_id ID записи времени
	 * @return array
	 */

	public static function readTimeEntry($time_entry_id, $useCache = true) {

		static $cache;
		
		$hash = md5($time_entry_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("time_entries/{$time_entry_id}.xml?include=memberships"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+ 1 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * удалить запись времени
	 * @param int $version_id ID связи времени
	 * @return array
	 */

	public static function deleteTimeEntry($time_entry_id) {
		try {
			return self::xml2array(self::runRequest("time_entries/{$time_entry_id}.xml", null, 'DELETE'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить список новостей
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */

	public static function readNews($index = 'id', $useCache = true) {

		static $cache;
		
		$hash = md5($index);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				for ($cache[$hash] = array(
				), $offset = 0, $limit = 50; count($data = self::xml2array(self::runRequest("news.xml?offset={$offset}&limit={$limit}"), $index)); $offset += $limit) {
					$cache[$hash] += $data;
				}
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+1 day');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * создать связь задач
	 * @param int $issue_id ID задачи
	 * @param array $data параметы
	 * @return array
	 */

	public static function createIssueRelation($issue_id, $data) {
		try {
			$xml = self::array2xml('relation', $data);
			return self::xml2array(self::runRequest("relation/{$issue_id}.xml", $xml->asXML(), 'POST'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить связанные задачи
	 * @param int $issue_id ID задачи
	 * @return array
	 */

	public static function readIssueRelations($issue_id, $useCache = true) {

		static $cache;
		
		$hash = md5($issue_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("issues/{$issue_id}/relations.xml"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+1 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * обновить связь задач
	 * @param int $time_entry_id ID связи задач
	 * @param array $data параметы
	 * @return array
	 */

	public static function updateIssueRelation($issue_id, $data) {
		try {
			$xml = self::array2xml('relation', $data);
			return self::xml2array(self::runRequest("relation/{$issue_id}.xml", $xml->asXML(), 'PUT'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * удалить связь задач
	 * @param int $version_id ID связи задач
	 * @return array
	 */

	public static function deleteIssueRelation($issue_id) {
		try {
			return self::xml2array(self::runRequest("relation/{$issue_id}.xml", null, 'DELETE'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * создать версию проекта
	 * @param string $project_id ID проекта
	 * @param array $data параметы
	 * @return array
	 */

	public static function createProjectVersion($project_id, $data) {
		try {
			$xml = self::array2xml('version', $data);
			return self::xml2array(self::runRequest("projects/{$project_id}/versions.xml", $xml->asXML(), 'POST'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить список версий проекта
	 * @param string $project_id ID проекта
	 * @return array
	 */

	public static function readProjectVersions($project_id, $useCache = true) {

		static $cache;
		
		$hash = md5($project_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("project/{$project_id}/versions.xml"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+3 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * получить версию
	 * @param string $version_id ID версии
	 * @return array
	 */

	public static function readVersion($version_id, $useCache = true) {

		static $cache;
		
		$hash = md5($version_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("versions/{$version_id}.xml"));
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash], '+3 hour');
		}
		
		return $cache[$hash];
	}
	
	/**
	 * обновить версию
	 * @param int $version_id ID версии
	 * @param array $data параметы
	 * @return array
	 */

	public static function updateVersion($version_id, $data) {
		try {
			$xml = self::array2xml('version', $data);
			return self::xml2array(self::runRequest("versions/{$version_id}.xml", $xml->asXML(), 'PUT'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * удалить версию
	 * @param int $version_id ID версии
	 * @return array
	 */

	public static function deleteVersion($version_id) {
		try {
			return self::xml2array(self::runRequest("versions/{$version_id}.xml", null, 'DELETE'));
		}
		catch(CHttpException $e) {
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * получить запросы
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */

	public static function readQueries($index = 'id', $useCache = true) {

		static $cache;
		
		$hash = md5($index);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("queries.xml"), $index);
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash]);
		}
		
		return $cache[$hash];
	}
	
	/**
	 * получить вложение
	 * @param string $attacment_id ID вложения
	 * @return array
	 */

	public static function readAttachment($attacment_id, $useCache = true) {

		static $cache;
		
		$hash = md5($attacment_id);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
				
			try {
				$cache[$hash] = self::xml2array(self::runRequest("attachments/{$attacment_id}.xml"), 'id');
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage(), $e->getCode());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash]);
		}
		
		return $cache[$hash];
	}
}

/**
 * прикладные (полезные) функции
 */

class Redmine extends RedmineModel {
	/**
	 * получить массив вида Array( [alaksey.d] => 50 [elena.c] => 39 [igor.p] => 5 ) соответствие Login-ID
	 * Нужно переделать с учётом, что пользователей может быть более 100 (переделано!)
	 * @return array
	 */

	public static function getUsersArray() {
		try {
			foreach (self::readUsers('login') as $login=>$data) {
				$users[trim(strtolower($login))] = (int) @$data['id'];
			}
			return $users;
		}
		catch(Exception $e) {
			return array(
			);
		}
	}
	
	/**
	 * получить пользователя Redmine по его логину.
	 * @param string $login
	 * @return array
	 */

	public static function getUserByLogin($login) {
		try {
			$users = self::readUsers('login');
			
			foreach ($users as $key=>$data) {
				if ($login == trim(strtolower($key)))
					return $data;
			}
			return array(
			);
		}
		catch(Exception $e) {
			return array(
			);
		}
	}
	
	/**
	 * получить проект Redmine по его идентификатору
	 * @param string $login
	 * @return array
	 */

	public static function getProjectByIdentifier($identifier) {
		try {
			$projects = self::readProjects('identifier');
			
			foreach ($projects as $key=>$data) {
				if ($identifier == trim(strtolower($key)))
					return $data;
			}
			return array(
			);
		}
		catch(Exception $e) {
			return array(
			);
		}
	}
	
	/**
	 * получить процент готовности задачи
	 * @param string $issue_id ID задачи
	 * @return int
	 */

	public static function getIssuePercent($issue_id) {
		try {
			$issue = self::readIssue($issue_id);
			
			return (int) @$issue['done_ratio'];
		}
		catch(Exception $e) {
			return 0;
		}
	}
	
	/**
	 * закрыть задачу
	 * @param string $issue_id ID задачи
	 */

	public static function closeIssue($issue_id) {
		try {
			self::updateIssue($issue_id, array(
				'done_ratio'=>100,'status_id'=>8
			));
		}
		catch(Exception $e) {
			// операция успешна, только если $e->getCode() != 0
			return $e->getCode() != 0;
		}
		return true;
	}
	
	/**
	 * Расчитываем дату окончания с учётом выходных дней.
	 * Расчёт идёт от текущего дня.
	 * @param int $days
	 * @return string
	 */

	private static function dueDate($days) {
		$weeks = floor($days / 5);
		$unUsedDays = $days - $weeks * 5;
		$nowDay = date('w', time());
		
		if (5 - $nowDay < $unUsedDays)
			$unUsedDays += 2;
			
		$daysRes = $weeks * 7 + $unUsedDays;
		
		return strtotime('+ '.$daysRes.' days');
	}
	
	/**
	 * создает задачи заказа или услуг
	 * @param object $context объект Package или Serv2Pack
	 * @param object $master_id [optional] ID исполнителя
	 * @return bool
	 */

	public static function postIssue(&$context, $master_id = false) {
		switch (true) {
			case ($context instanceof Package):
				// это задача для менеджера
				$package = &$context;
				
				// создатель задачи
				if (!$rmManager = self::getUserByLogin(Yii::app()->user->login)) {
					break;
				}
				
				// если передан мастер, патаемся его найти
				$master_id and $master = People::model()->findByPk($master_id)
				// иначе используем назначенного менеджера
				or $master = $package->manager
				// но если и его нет, назначаем задачу пользователю
				or $master = People::model()->findByPk(Yii::app()->user->id);
				
				// исполнитель задачи
				if (!$rmMaster = self::getUserByLogin($master->login)) {
					break;
				}
				
				// XXX проект выбираем исходя из роли мастера
				$project = @Yii::app()->params['redmineConfig']['assignTo'][$master->people_group->name];
				
				// если не нашелся проект, выбираем проект по-умолчанию
				isset($project) or $project = Yii::app()->params['redmineConfig']['defaulProject'];
				$rmProject = self::getProjectByIdentifier($project);
				
				$subject = "#{$package->id} {$package->name} для {$package->client->mail}";
				isset($package->site) and $subject .= " ({$package->site->url})";
				
				$description = '';
				$description .= "h3. примечания:\n{$package->descr}\n\n";
				if (isset($package->site)) {
					$description .= "*сайт: {$package->site->url}*\n";
					$description .= "*хост:* {$package->site->host}\n";
					$description .= "*ftp:* {$package->site->ftp}\n";
					$description .= "*db:* {$package->site->db}\n";
					//$description .=  "*старт:* {$package->site->dt_beg}\n";
					//$description .=  "*финиш:* {$package->site->dt_end}\n";
					$description .= "\n";
				}
				$description .= "h3. сумма: ".number_format($package->summ, 0, ',', ' ')."руб.";
				
				$issue = self::createIssue(array(
					// в каком проекте создать задачу
					'project_id'=>$rmProject['id'],
					// параметры задачи
					'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
					// кто назначил и кому наначено
					'author_id'=>$rmManager['id'],'assigned_to_id'=>$rmManager['id'],
					// родительская задача
					//'parent_issue_id'=>0,
					// тема и описание
					'subject'=>$subject,'description'=>$description,
					// когда начата и когда должна быть закончена
					'start_date'=>date('Y-m-d', strtotime($package->dt_beg)),
					//'due_date'=>date('Y-m-d', strtotime($package->dt_beg)),
					
					// время на выполнение и потраченное время
					'estimated_hours'=>'0.0','spent_hours'=>'0.0'
				));
				
				if (!$package->rm_issue_id = @$issue['id']) {
					throw new CHttpException(500, __METHOD__.' failed: '.'Не удалось создать задачу');
				}
				
				break;
			///////////////////////////////
			case ($context instanceof Serv2pack):
			
				// это задача для мастера
				$service = &$context;
				$package = $service->package;
				
				// создатель задачи
				if (!$rmManager = self::getUserByLogin(Yii::app()->user->login)) {
					break;
				}
				
				// если передан мастер, патаемся его найти
				$master_id and $master = People::model()->findByPk($master_id)
				// иначе используем назначенного мастера
				or $master = $context->master
				// но если и его нет, назначаем задачу пользователю
				or $master = People::model()->findByPk(Yii::app()->user->id);
				
				// исполнитель задачи
				if (!$rmMaster = self::getUserByLogin($master->login)) {
					break;
				}
				
				// XXX проект выбираем исходя из роли мастера
				$project = @Yii::app()->params['redmineConfig']['assignTo'][$master->people_group->name];
				
				// если не нашелся проект, выбираем проект по-умолчанию
				isset($project) or $project = Yii::app()->params['redmineConfig']['defaulProject'];
				$rmProject = self::getProjectByIdentifier($project);
				
				$subject = "#{$package->id} {$service->service->name} для {$package->client->mail}";
				isset($package->site) and $subject .= " ({$package->site->url})";
				
				$description = '';
				if (isset($package->site)) {
					$description .= "*сайт: {$package->site->url}*\n";
					$description .= "*хост:* {$package->site->host}\n";
					$description .= "*ftp:* {$package->site->ftp}\n";
					$description .= "*db:* {$package->site->db}\n";
					//$description .=  "*старт:* {$package->site->dt_beg}\n";
					//$description .=  "*финиш:* {$package->site->dt_end}\n";
					$description .= "\n";
				}
				$description .= "h3. примечания:\n\n{$service->descr}\n\n";
				
				if ($service->quant == 1) {
					$description .= "h3. стоимость: ".number_format($service->price, 0, ',', ' ')."руб.";
				} else {
					$description .= "h3. количество: ".number_format($service->quant, 0, ',', ' ')."шт.\n";
					$description .= "h3. цена: ".number_format($service->price, 0, ',', ' ')."руб.\n";
					$description .= "h3. стоимость: ".number_format($service->quant * $service->price, 0, ',', ' ')."руб.";
				}
				
				$issue = self::createIssue(array(
					// в каком проекте создать задачу
					'project_id'=>$rmProject['id'],
					// параметры задачи
					'tracker_id'=>2,'status_id'=>1,'priority_id'=>4,
					// кто назначил и кому наначено
					'author_id'=>$rmManager['id'],'assigned_to_id'=>$rmMaster['id'],
					// родительская задача
					'parent_issue_id'=>$package->rm_issue_id,
					// тема и описание
					'subject'=>$subject,'description'=>$description,
					// когда начата и когда должна быть закончена
					'start_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d', self::dueDate($service->service->duration)),
					// XXX время на выполнение и потраченное время
					//'estimated_hours'=>$service->service->duration,'spent_hours'=>'0.0',
					// XXX вид деятельности - исследовать это поле
					//'time_entry_activity_id'=>
				));
				
				if (!$service->rm_issue_id = @$issue['id']) {
					throw new CHttpException(500, __METHOD__.' failed: '.'Не удалось создать подзадачу');
				}
				
				// если нет главной задачи
				if (!$package->rm_issue_id) {
					self::postIssue($package);
				}
				
				// добавляем в главную задачу комментарий об этом действии
				$masterFullName = @$rmMaster['firstname'].' '.@$rmMaster['lastname'];
				
				try {
					self::updateIssue($package->rm_issue_id, array(
						// сообщение
						'notes'=>"h2. поставлена задача - {$service->service->name}\n\nисполнитель - \"{$masterFullName}\":/users/{$rmMaster['id']}, задача #{$service->rm_issue_id}",
						// XXX потраченное время (прибавляется)
						//'spent_hours'=>$service->service->duration
					));
				}
				catch(Exception $e) {
					// XXX это исключение генерируется всегда!
					// это не логично, но нормально =)
				}
				break;
		}
		return true;
	}
}
