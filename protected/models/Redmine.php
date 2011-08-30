<?php 
# (c) 2011 Thomas Spycher - Zero-One
# (c) 2011 Krivchikov D.A. - FabricaSaitov.ru

/**
 * Redmine class.
 * Используется для взаимодействия по средствам API с Redmine (как ни странно).
 * За основу взят класс, разработанный Томасам Спайхером (Thomas Spycher).
 *
 *
 * Пожалуйста! И не благодарите :)
 * В прошлой версии было частично реализовано взаимодействие через прямое обращение к БД.
 * Ни в коем случае не делайте этого! Базу уже один раз чинили - хватит!
 */
class Redmine {
	/*	private static $config = array(
	 'allow_connect' => true, // Подключаться к редмайну? Если нет, то любое обращение будет возвращать FALSE
	 'protocol' => 'https',
	 'port' => '443',
	 'url' => "redmine.fabricasaitov.ru", //Без HTTP://
	 'targetProjectId' => 'suptask', // Целевой проект - в него будут попадать задачи
	 );/**/
	
	/*	private static $config = array(
	 'allow_connect' => true,
	 'protocol' => 'http',
	 'port' => '80',
	 'url' => "redmine.sandbox.loc",
	 'targetProjectId' => '1',
	 'rootLogin' => 'dmitry.k',
	 'rootPassword' => 'Ij3Ohmee',
	 );/**/
	
	/*	private static $config = array(
	 'allow_connect' => true,
	 'protocol' => 'http',
	 'port' => '80',
	 'url' => "redmine.fabricasaitov.ru",
	 'targetProjectId' => 'suptask',
	 'rootLogin' => 'sup',
	 'rootPassword' => 'zVRaDio(5mWEdFW',
	 );/**/

	
	private static function runRequest($function, $data = null, $method = 'GET') {
	
		static $cache;
		
		$hash = md5($function.','.$data);
		
		if ($method != 'GET' or !isset($cache[$hash])) {
		
			if (!function_exists('curl_init'))
				throw new CHttpException(500, 'cURL is not installed');
				
			$config = Yii::app()->params['RedmineConfig'];
			
			// если в настройках не разрешено использовать Редмайн, то вызываем исключение
			if (!(bool)$config['allow_connect'])
				throw new CHttpException(500, 'Redmine is disabled');
				
			// формируем url сервара с указанием протокола
			$url = $config['protocol'].'://'.$config['url'];
			
			$method = mb_strtoupper($method);
			
			$curl = curl_init();
			
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
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
					break;
				case 'GET':
				default:
					break;
			}

			
			curl_setopt($curl, CURLOPT_URL, $url.'/'.$function);
			curl_setopt($curl, CURLOPT_PORT, $config['port']);
			
			if (stristr('users.xml', $function))
				// от имени текущего пользователя
				curl_setopt($curl, CURLOPT_USERPWD, Yii::app()->user->login.":".base64_decode(Yii::app()->user->key));
			else
				// от имени Димы Кривчикова
				curl_setopt($curl, CURLOPT_USERPWD, $config['rootLogin'].':'.$config['rootPassword']);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_BUFFERSIZE, 1024 * 1024 * 10); // max 10 mb!
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: text/xml; charset=UTF-8', 'Content-length: '.strlen($data)
			));
			
			$response = curl_exec($curl);
			if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
				$errors = '';
				foreach (curl_getinfo($curl) as $key=>$value) {
					$errors .= "\n{$key}: {$value}";
				}
				//throw new CHttpException(500, 'cURL request failed: '.$errors);
			}
			
			// чтобы было понятно, в какой строке ошибка - разобъем ответ
			//$response = str_replace('>', ">\n", $response);
			
			//var_dump($response) and die();
			
			$method == 'GET' and $cache[$hash] = $response;
			
			curl_close($curl);
		} else {
			$response = $cache[$hash];
		}
		
		libxml_use_internal_errors(true);
		$sxml = simplexml_load_string($response);
		
		if ($method == 'GET' and !$sxml) {
			$errors = "\n";
			foreach (libxml_get_errors() as $error) {
			
				$errors .= "line {$error->line}: {$error->message}";
			}
			throw new CHttpException(500, 'XML is not well-formed:'.$errors);
		}
		
		//var_dump($sxml) and die();
		
		$sxml or $sxml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?><none/>");
	
		return $sxml;
	}

	
	/**
	 * преобразование xml в массив
	 * @param object $xml XML
	 * @param string $index [optional] использовать значение этого ребенка как индекс
	 * @param bool $withAttributes [optional] вывести атрибуты базового элемента
	 * @return
	 */
	private static function xml2array($xml, $index = false, $withAttributes = false) {
	
		$array = array(
		);
		
		foreach ($xml->children() as $child=>$node) {
			// если у элемента несколько одинаковых детей, дети индексируются
			// по своему атрибуту $index или елементу $index, если его нет - по порядку
			if (count($xml->$child) > 1) {
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
	static function array2xml($base, $children = null, &$xml = null) {
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

	
	/**
	 * Возвращаем список задач
	 * @param string $index [optional] по какому полю проекта проидексировать список
	 * @param string $project_id [optional] get issues from the project with the given id
	 * @param string $tracker_id [optional] get issues from the tracker with the given id
	 * @param string $status_id [optional] get issues with the given status id only (you can use * to get open and closed issues)
	 * @param string $assigned_to_id [optional] get issues which are assigned to the given user id
	 * @return array
	 */
	public static function getIssues($index = 'id', $project_id = false, $tracker_id = false, $status_id = false, $assigned_to_id = false) {
		static $cached;
		
		$queryAdd = '';
		$project_id and $queryAdd .= "&project_id={$project_id}";
		$tracker_id and $queryAdd .= "&tracker_id={$tracker_id}";
		$status_id and $queryAdd .= "&status_id={$status_id}";
		$assigned_to_id and $queryAdd .= "&assigned_to_id={$assigned_to_id}";
		
		$hash = md5($queryAdd);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = array(
			);
			
			for ($offset = 0, $limit = 50; count($data = self::xml2array(self::runRequest("issues.xml?offset={$offset}&limit={$limit}{$queryAdd}"), $index)); $offset += $limit) {
				$cached[$hash] += $data;
			}
		}
		
		return $cached[$hash];
	}
	
	/**
	 * Возвращаем задачу
	 * @param int $issue_id ID задачи
	 * @return array
	 */
	public static function getIssue($issue_id) {
		static $cached;
		
		$hash = md5($issue_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("issues/{$issue_id}.xml?include=relations,journals"), 'id');
		}
		
		return $cached[$hash];
	}

	
	/**
	 * Возвращаем список проектов
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */
	public static function getProjects($index = 'id') {
		static $cached;
		
		if (!isset($cached)) {
			$cached = array(
			);
			
			for ($offset = 0, $limit = 100; count($data = self::xml2array(self::runRequest("projects.xml?offset={$offset}&limit={$limit}"), $index)); $offset += $limit) {
				$cached += $data;
			}
		}
		
		return $cached;
	}

	
	/**
	 * Возвращаем проект
	 * @param string $project_id ID проекта
	 * @return array
	 */
	public static function getProject($project_id) {
		static $cached;
		
		$hash = md5($project_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("projects/{$project_id}.xml"), 'id');
		}
		
		return $cached[$hash];
	}

	
	/**
	 * Возвращаем список пользователей
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */
	public static function getUsers($index = 'id') {
		static $cached;
		
		if (!isset($cached)) {
			$cached = array(
			);
			
			for ($offset = 0, $limit = 100; count($data = self::xml2array(self::runRequest("users.xml?offset={$offset}&limit={$limit}"), $index)); $offset += $limit) {
				$cached += $data;
			}
		}
		
		return $cached;
	}

	
	/**
	 * Возвращаем пользователя
	 * @param int $user_id ID пользователя
	 * @return array
	 */
	public static function getUser($user_id) {
		static $cached;
		
		$hash = md5($user_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("users/{$user_id}.xml?include=memberships"), 'id');
		}
		
		return $cached[$hash];
	}
	
	/**
	 * Возвращаем список записей времени
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @param int $timeentry_id [optional] id записи времени
	 * @return array
	 */
	public static function getTimeEntries($index = 'id') {
		static $cached;
		
		if (!isset($cached)) {
			$cached = self::xml2array(self::runRequest("time_entries.xml"), $index);
		}
		
		return $cached;
	}
	
	/**
	 * Возвращаем запись времени
	 * @param int $time_entry_id ID записи времени
	 * @return array
	 */
	public static function getTimeEntry($time_entry_id) {
		static $cached;
		
		$hash = md5($time_entry_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("time_entries/{$time_entry_id}.xml?include=memberships"), 'id');
		}
		
		return $cached[$hash];
	}
	
	/**
	 * Возвращаем список новостей
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */
	public static function getNews($index = 'id') {
		static $cached;
		
		if (!isset($cached)) {
			$cached = array(
			);
			
			for ($offset = 0, $limit = 100; count($data = self::xml2array(self::runRequest("news.xml?offset={$offset}&limit={$limit}"), $index)); $offset += $limit) {
				$cached += $data;
			}
		}
		
		return $cached;
	}
	
	/**
	 * Возвращаем связанные задачи
	 * @param int $issue_id ID задачи
	 * @return array
	 */
	public static function getIssueRelations($issue_id) {
		static $cached;
		
		$hash = md5($issue_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("issues/{$issue_id}/relations.xml"), 'id');
		}
		
		return $cached[$hash];
	}
	
	/**
	 * Возвращаем версии проекта
	 * @param string $project_id ID проекта
	 * @return array
	 */
	public static function getProjectVersions($project_id) {
		static $cached;
		
		$hash = md5($project_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("project/{$project_id}/versions.xml"), 'id');
		}
		
		return $cached[$hash];
	}
	
	/**
	 * Возвращаем запросы
	 * @param string $index [optional] по какому полю элемента проидексировать список
	 * @return array
	 */
	public static function getQueries($index = 'id') {
		static $cached;
		
		if (!isset($cached)) {
			$cached = self::xml2array(self::runRequest("queries.xml"), $index);
		}
		
		return $cached;
	}
	
	/**
	 * Возвращаем вложение
	 * @param string $attacment_id ID вложения
	 * @return array
	 */
	public static function getAttachment($attacment_id) {
		static $cached;
		
		$hash = md5($attacment_id);
		
		if (!isset($cached[$hash])) {
			$cached[$hash] = self::xml2array(self::runRequest("attachments/{$attacment_id}.xml"), 'id');
		}
		
		return $cached[$hash];
	}
	
	/************************************
	 *             прикладное           *
	 ************************************/
	
	
	/**
	 * Возвращаем массив вида Array( [alaksey.d] => 50 [elena.c] => 39 [igor.p] => 5 ) соответствие Login-ID
	 * Нужно переделать с учётом, что пользователей может быть более 100 (переделано!)
	 * @return array
	 */
	public static function getUsersArray() {
		foreach (self::getUsers('login') as $login=>$data) {
			$users[trim(strtolower($login))] = (int) @$data['id'];
		}
		return $users;
	}
	
	/**
	 * Возвращаем пользователя Redmine по его логину.
	 * @param string $login
	 * @return array
	 */
	public static function getUserByLogin($login) {
		$users = self::getUsers('login');
		foreach ($users as $key=>$data) {
			if ($login == trim(strtolower($key)))
				return $data;
		}
	}
	
	/**
	 * закрыть задачу
	 * @param int $issue_id
	 * @return array
	 */
	public static function closeIssue($issue_id) {
		$xml = self::array2xml('issue', array(
			'id'=>$issue_id, 'status_id'=>8, 'done_ratio'=>100
		));
		
		return true; 
	}
	
	/**
	 * получить процент готовности
	 * @param int $issue_id ID задачи
	 * @return int
	 */
	public static function getIssuePercent($issue_id) {
		$issue = self::getIssue($issue_id);
		return $issue['done_ratio'];
	}
	
	/**
	 * добавить задачу
	 * @param string $subject заголовок
	 * @param string $description описание
	 * @param int $assigned_to_id [optional] ID кому назначена
	 * @param int $parent_issue_id [optional] ID родительской задачи
	 * @param string $start_date [optional] время создания
	 * @param string $due_date [optional] время готовности
	 * @return array
	 */
	public static function addIssue($subject, $description, $assigned_to_id = 0, $parent_issue_id = 0, $start_date = false, $due_date = false) {
		$xml = self::array2xml('issue', array(
			//
			'subject'=>htmlspecialchars($subject),
			//
			'description'=>htmlspecialchars($description),
			//
			'assigned_to_id'=>(int) $assigned_to_id,
			//
			'parent_issue_id'=>$parent_issue_id ? (int) $parent_issue_id : null,
			//
			'start_date'=>$start_date ? (string) $start_date : null,
			//
			'due_date'=>$due_date ? (string) $due_date : null,
			//
			'priority_id'=>4,
			//
			'project_id'=>Yii::app()->params['RedmineConfig']['targetProjectId']
			
		));
		
		return self::xml2array(Redmine::runRequest('issues.xml', $xml->asXML(), 'POST'));
	}
	
	/**
	 * добавить комментарий к задаче
	 * @param int $id ID задачи
	 * @param string $note комментарий
	 * @return array
	 */
	public static function addNoteToIssue($id, $note) {
		$xml = self::array2xml('issue', array(
			//
			'id'=>(int) $id,
			//
			'notes'=>htmlspecialchars($note)
		));
		
		return self::xml2array(Redmine::runRequest("issues/{$issueId}.xml", $xml->asXML(), 'PUT'));
	}
}
