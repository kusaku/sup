<?php 
class AboutController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */
	public function filters() {
		return array(
			'accessControl'
		);
<<<<<<< HEAD
=======
	}
	
	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */
	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner');
		return array(
			array(
				'allow', 'actions'=>array(
					'index', 'test'
				), 'roles'=>array(
					'admin', 'manager'
				),
			), array(
				'deny', 'users'=>array(
					'*'
				),
			),
		);
	}
	
	public function actionIndex() {
		$this->renderPartial('index');
>>>>>>> refs/remotes/shared/master
	}
	
<<<<<<< HEAD
	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */
	/*public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner');
		return array(
			array(
				'allow', 'actions'=>array(
					'index', 'test'
				), 'roles'=>array(
					'admin'
				),
			), array(
				'deny', 'users'=>array(
					'*'
				),
			),
		);
	}*/
	
	public function actionIndex() {
		$ldap_server = "ldap://ldap.fabricasaitov.ru";
		
		$auth_user = "cn=readLDAP,dc=fabricasaitov,dc=ru";
		$auth_pass = "eNgoo8na";
		
		//$auth_user = "uid=kirill.a,ou=***FS-Программисты,dc=fabricasaitov,dc=ru";
		//$auth_pass = "eBr_iMQ9";
		
		// Set the base dn to search the entire directory.
		
		$base_dn = "dc=fabricasaitov,dc=ru";
		//$base_dn = "";
		// Show only user persons
		//$filter = "(&(objectClass=user)(objectCategory=person)(cn=*))";
		
		// Enable to show only users
		// $filter = "(&(objectClass=user)(cn=$*))";

		
		// connect to server
		
		$connect = @ldap_connect($ldap_server) or print("Could not connect to ldap server: ".ldap_error($connect));
		
		ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
		
		// bind to server
		
		$bind = @ldap_bind($connect, $auth_user, $auth_pass) or print("Unable to bind to server: ".ldap_error($connect));
		//$bind = @ldap_bind($connect) or print("Unable to bind to server: ".ldap_error($connect));
		
		//print_r($bind);
		
		//exit();
		
		//$filter = "(&(objectClass=posixAccount)(uid=kirill.a))";
		$filter = "(objectClass=organizationalUnit)";
		//$attr = array("ou");
		
		$search = @ldap_search($connect, $base_dn, $filter) or print("Unable to search ldap server");

		$info = @ldap_get_entries($connect, $search) or print("No entries found");
		
		print_r($info);
		
	}
	
=======
>>>>>>> refs/remotes/shared/master
	public function actionTest() {
		$this->renderPartial('test');
	}
	
}
