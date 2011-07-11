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
class Redmine
{
/*	private static $config = array(
		'allow_connect' => true, // Подключаться к редмайну? Если нет, то любое обращение будет возвращать FALSE
		'protocol' => 'https',
		'port' => '443',
		'url' => "redmine.fabricasaitov.ru", //Без HTTP://
		'targetProjectId' => 'suptask', // Целевой проект - в него будут попадать задачи
	);/**/

	private static $config = array(
		'allow_connect' => true, // Подключаться к редмайну? Если нет, то любое обращение будет возвращать FALSE
		'protocol' => 'http',
		'port' => '80',
		'url' => "redmine.sandbox.loc", //Без HTTP://
		'targetProjectId' => '1', // Целевой проект - в него будут попадать задачи
	);/**/

/*	private static $config = array(
		'allow_connect' => true, // Подключаться к редмайну? Если нет, то любое обращение будет возвращать FALSE
		'protocol' => 'http',
		'port' => '80',
		'url' => "redmine.fabricasaitov.ru", //Без HTTP://
		'targetProjectId' => 'suptask', // Целевой проект - в него будут попадать задачи
	);/**/


	private static function runRequest($restUrl, $method = 'GET', $data = "")
	{
		// Если в настройках не разрешено использовать Редмайн, то возвращаем FASLE
		if ( !Redmine::$config['allow_connect'] ) return FALSE;

		// Формируем правильный урл
		$url = Redmine::$config['protocol'].'://'.Redmine::$config['url'];

        $method = mb_strtolower($method);
        $curl = curl_init();

		switch ($method) {
			case "post":
				curl_setopt($curl, CURLOPT_POST, 1);
				if(isset($data)) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "put":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT'); 
				if(isset($data)) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "delete":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default: // get
				break;
		}
 
		try {
			curl_setopt($curl, CURLOPT_URL, $url.$restUrl);
			curl_setopt($curl, CURLOPT_PORT, Redmine::$config['port']);
			curl_setopt($curl, CURLOPT_USERPWD, Yii::app()->user->login.":".Yii::app()->user->password );
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_COOKIESESSION, true);
			curl_setopt($curl, CURLOPT_VERBOSE, false);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_AUTOREFERER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Charset=utf-8", "Content-length: ".strlen($data)));
 
			$response = curl_exec($curl); 
			if(!curl_errno($curl)){ 
		  		$info = curl_getinfo($curl);
			} else {
				curl_close($curl); 
				return false;
			}
			curl_close($curl); 
		} catch (Exception $e) {
    		return false;
		}

		if($response) {
			if(substr($response, 0, 1) == '<') {
				return new SimpleXMLElement($response);
			} else {
				return false;
			}
		}
		return true;
    }

	/**
	 * Возвращаем список всех пользователей
	 * @return Object
	 */
	public static function getUsers() {
		return Redmine::runRequest('/users.xml', 'GET', '');
	}

	/**
	 * Возвращаем массив вида Array( [alaksey.d] => 50 [elena.c] => 39 [igor.p] => 5 ) соответствие Login-ID
	 * @return Array
	 */
	public static function getUsersArray() {
		$users = Redmine::runRequest('/users.xml?limit=100&status=', 'GET', '');
		$usersArray = array();
		foreach ($users as $user) {
			$usersArray[ trim( mb_strToLower( (string)$user->login ) ) ] = (int)$user->id;
			//$usersArray[ md5( (string)$user->login ) ] = (string)$user->login;
		}
		return $usersArray;
	}

	/**
	 * Возвращаем пользователя Redmine по его логину.
	 * @param String $login
	 * @return Object
	 */
	public static function getUserByLogin($login) {
		if ( $login ){
			$login = trim( mb_strToLower( $login ) );
			$users = Redmine::runRequest('/users.xml?status=&limit=100', 'GET', '');
			foreach ($users as $user) {
				if ( trim( mb_strToLower( $user->login ) ) == $login ){
					return $user;
				}
			}
		}
		return false;
	}
 
	/**
	 * Возвращаем список всех проектов. Не используется, но коль реалезовано - пусть остаётся.
	 * @return Object
	 */
	public static function getProjects() {
		return Redmine::runRequest('/projects.xml', 'GET', '');
	}
 
	/**
	 * Возвращаем все задачи проекта. Не используется.
	 * @param <type> $projectId
	 * @return Object
	 */
	public static function getIssues($projectId = null) {
		// Если проект не передали, то используем проект по умолчанию
		if ( $projectId === null ) $projectId = Redmine::$config['targetProjectId'];
		return Redmine::runRequest('/issues.xml?project_id='.$projectId, 'GET', '');
	}

	/**
	 * Возвращает задачу с комментариями.
	 *
	 * @param int $IssueId
	 * @return Object
	 */
	public static function getIssue($IssueId) {
		return Redmine::runRequest('/issues/'.$IssueId.'.xml?include=journals', 'GET', '');
	}

	/**
	 * Возвращает процент готовности задачи по ID.
	 * @param int $IssueId
	 * @return int
	 */
	public static function getIssuePercent($IssueId) {
		return ( int ) Redmine::runRequest('/issues/'.$IssueId.'.xml', 'GET', '')->done_ratio;
	}


	/**
	 *  Добавляем задачу.
	 *
	 * @param string $subject
	 * @param text $description
	 * @param int $project_id
	 * @param int $assignmentUserId
	 * @param int $parentIssueId
	 * @param int $category_id
	 * @param <type> $created_on
	 * @param <type> $due_date
	 * @return 
	 */
	public static function addIssue($subject, $description, $project_id, $assignmentUserId = 1, $parentIssueId = 0, $category_id = 1, $created_on = false, $due_date = false) {
		$priority_id = 4;
 
		$xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
		$xml->addChild('subject', htmlspecialchars($subject));
//		$xml->addChild('project_id', $project_id);
		$xml->addChild('project_id', Redmine::$config['targetProjectId']); // Берём проект из настроек
		$xml->addChild('priority_id', $priority_id);
		$xml->addChild('description', htmlspecialchars($description));
		$xml->addChild('category_id', $category_id);
		if($parentIssueId) $xml->addChild('parent_issue_id', $parentIssueId);
		if($created_on) $xml->addChild('start_date', $created_on);		
		if($due_date) $xml->addChild('due_date', $due_date);
		$xml->addChild('assigned_to_id', $assignmentUserId);

		return Redmine::runRequest('/issues.xml', 'POST', $xml->asXML() );
	}
 
	/**
	 * Добавляем комментарий к задаче
	 * 
	 * @access public
	 * @param mixed $issueId
	 * @param mixed $note
	 * @return void
	 */
	public static function addNoteToIssue($issueId, $note) {
		$xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
		$xml->addChild('id', $issueId);
		$xml->addChild('notes', htmlspecialchars($note));
		return Redmine::runRequest('/issues/'.$issueId.'.xml', 'PUT', $xml->asXML() );
	}
 
}
?>
