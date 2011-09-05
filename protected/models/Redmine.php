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
	 * @param SimpleXMLElement $data [optional] параметры
	 * @param object $method [optional] метод запроса
	 * @return SimpleXMLElement
	 */
	public static function runRequest($function, $data = null, $method = 'GET') {
	
		// здесь кешируем все GET запросы в течении жизни проложения
		static $cache;
		static $config;
		
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
				
			// плюемся, если в настройках не разрешено использовать Redmine
			if (!$config['enabled'])
				throw new CHttpException(500, 'Redmine is disabled');
				
			// формируем url сервара с указанием протокола
			$url = $config['protocol'].'://'.$config['url'];
			
			// открываем ресурс
			$curl = curl_init();
			
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
			
			// формируем url запроса
			curl_setopt($curl, CURLOPT_URL, $url.'/'.$function);
			curl_setopt($curl, CURLOPT_PORT, $config['port']);
			
			if (stristr('users.xml', $function))
				// от суперадмина
				curl_setopt($curl, CURLOPT_USERPWD, "{$config['rootLogin']}:{$config['rootPassword']}");
			else				
				// от имени текущего пользователя				
				curl_setopt($curl, CURLOPT_USERPWD, Yii::app()->user->login.':'.base64_decode(Yii::app()->user->key));
				
			// пробуем все типы авторизации
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			
			// если мы сидим за проксей
			if (2 == count(@list($proxy,$port) = explode(':', @$config['proxy']))) {
				curl_setopt($curl, CURLOPT_PROXY, $proxy);
				curl_setopt($curl, CURLOPT_PROXYPORT, $port);
				//curl_setopt($curl, CURLOPT_PROXYAUTH,  CURLAUTH_BASIC | CURLAUTH_NTLM);
				//curl_setopt($curl, CURLOPT_PROXYUSERPWD, "user:password");
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
			// кодировка UTF-8 и длина сообщения
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: text/xml; charset=UTF-8','Content-Length: '.strlen($data)
			));

			
			// делаем запрос
			if (false === $response = curl_exec($curl))
				throw new CHttpException(500, 'cURL request failed: '.curl_error($curl));
				
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			// проверяем успешность
			switch ($method) {
				case 'POST':
					if (!in_array($http_code, array(
						201,422
					)))
						throw new CHttpException(500, 'cURL request failed: '.self::getCurlInfo($curl));
					break;
				case 'PUT':
					if (!in_array($http_code, array(
						200,422
					)))
						throw new CHttpException(500, 'cURL request failed: '.self::getCurlInfo($curl));
					break;
				case 'DELETE':
					if (!in_array($http_code, array(
						200,422
					)))
						throw new CHttpException(500, 'cURL request failed: '.self::getCurlInfo($curl));
					break;
				case 'GET':
					if (!in_array($http_code, array(
						200,422
					)))
						throw new CHttpException(500, 'cURL request failed: '.self::getCurlInfo($curl));
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
			throw new CHttpException(500, 'XML is not well-formed: '.self::getXMLErrors());
			
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
		}
	}
	
	/**
	 * получить список задач
	 * @param string $index [optional] по какому полю проекта проидексировать список
	 * @param string $project_id [optional] get issues from the project with the given id
	 * @param string $tracker_id [optional] get issues from the tracker with the given id
	 * @param string $status_id [optional] get issues with the given status id only (you can use * to get open and closed issues)
	 * @param string $assigned_to_id [optional] get issues which are assigned to the given user id
	 * @return array
	 */
	public static function readIssues($index = 'id', $project_id = false, $tracker_id = false, $status_id = false, $assigned_to_id = false, $useCache = true) {
		static $cache;
		
		$query = '';
		$project_id and $queryAdd .= "&project_id={$project_id}";
		$tracker_id and $queryAdd .= "&tracker_id={$tracker_id}";
		$status_id and $queryAdd .= "&status_id={$status_id}";
		$assigned_to_id and $queryAdd .= "&assigned_to_id={$assigned_to_id}";
		
		$hash = md5($index.$queryAdd);
		
		if (!isset($cache[$hash])) {
			if ($useCache and $cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash))
				return $cache[$hash];
			// если много данных не влезло
			elseif ($cache[$hash] = Persistent::getData(__METHOD__.'.'.$hash.'.0')) {
				$index = 1;
				while ($partial = Persistent::getData(__METHOD__.'.'.$hash.'.'.($index++))) {
					$cache[$hash] += $partial;
				}
				return $cache[$hash];
			}
			
			try {
				for ($cache[$hash] = array(
				), $offset = 0, $limit = 50; count($data = self::xml2array(self::runRequest("issues.xml?offset={$offset}&limit={$limit}{$query}"), $index)); $offset += $limit) {
					$cache[$hash] += $data;
				}
			}
			catch(CHttpException $e) {
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
			}
			
			// много данных не влезет, разобъем по 100
			if (($count = count($cache[$hash])) > 100) {
				for ($index = 0, $offset = 0, $limit = 100; $offset < $count; $index++, $offset += $limit) {
					$partial = array_slice($cache[$hash], $offset, $limit, true);
					// а еще - увеличим время хранения - процедура получения слишком дорого обходится
					Persistent::setData(__METHOD__.'.'.$hash.'.'.$index, $partial, '+10 minutes');
				}
			} else {
				Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash]);
			}
		}
		
		return $cache[$hash];
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
			}
			
			Persistent::setData(__METHOD__.'.'.$hash, $cache[$hash]);
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
			throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
				throw new CHttpException(500, __METHOD__.' failed: '.$e->getMessage());
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
}
