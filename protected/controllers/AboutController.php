<?php 

define('LDAP_DOMAIN', 'fabrica.local');

class AboutController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */
	/*public function filters() {
		return array(
			'accessControl'
		);
	}*/
	
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
		$ldap_server = "ldap://192.168.0.1";
		
		$auth_user = "kirill.a@fabrica.local";
		$auth_pass = "eBr_iMQ9";
		
		//$auth_user = "uid=kirill.a,ou=***FS-Программисты,dc=fabricasaitov,dc=ru";
		//$auth_pass = "eBr_iMQ9";
		
		// Set the base dn to search the entire directory.
		
		$base_dn = '';//"dc=fabrica,dc=local";
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
		echo "kirill.a@".LDAP_DOMAIN;
		
		$bind = @ldap_bind($connect, "kirill.a@".LDAP_DOMAIN, $auth_pass) or print("Unable to bind to server: ".ldap_error($connect));
		//$bind = @ldap_bind($connect) or print("Unable to bind to server: ".ldap_error($connect));
		
		//print_r($bind);
		
		//exit();
		
		//$filter = "(&(objectClass=posixAccount)(uid=kirill.a))";
		//$filter = "(objectClass=organizationalUnit)";
		$filter = "(&(objectClass=user)(objectClass=user))";
		//$attr = array('uid', 'mail');
		
		$search = @ldap_search($connect, $base_dn, $filter/*, $attr*/) or print("Unable to search ldap server");

		$info = @ldap_get_entries($connect, $search) or print("No entries found");
		
		
		print_r($info);
		
		/*foreach ($info as $i) {
			echo "'" . @$i['uid'][0] . "', ";
			echo "'" . @$i['name'][0] . "', ";
		}*/
		
	}
	
	public function actionTest() {
		$this->renderPartial('test');
	}
	
}
