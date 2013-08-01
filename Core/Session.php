<?php
class Session {

	private $_session_id;
	private $_session_value;
	private $_session_values;
	private $_db;
	private $_logged_in;
	private $_user_id;
	private $_session_timeout = 100;
	private $_session_lifetime = 3600;
	//private $_user;
	private $_table = 'sz_session';
//	var $intLifeTime;


/*	public function __construct($db) {
		$this->_db = $db;
		session_set_save_handler(
			array(&$this, '_session_open_method'),
			array(&$this, '_session_close_method'),
			array(&$this, '_session_read_method'),
			array(&$this, '_session_write_method'),
			array(&$this, '_session_destroy_method'),
			array(&$this, '_session_gc_method')
		);

//		$sUsszgent
		session_set_cookie_params($this->_session_lifespan);
		session_start();
	}
*/
	public function __construct($config=null, $db=null) {
		//$this->_db = $db;
		$this->_db = DB::getInstance();
/*		
		session_set_save_handler(
			array($this, '_open'),
			array($this, '_close'),
			array($this, '_read'),
			array($this, '_write'),
			array($this, '_destroy'),
			array($this, '_gc')
		);
*/
//		$sUsszgent
		//session_set_cookie_params($this->_session_lifespan);
		session_start();

	
		//$this -> DB = new Fast_DB( $db_config );

//		$this->_table = 'sz_session';

		//$this->intLifeTime = $session_lifetime;
		
		//$this->logIn('Ashley Riot', 'gosia');
//		$arrat = $this->_db->getArray('SELECT * FROM sz_users');
	//	print_r($arrat);
	}

	public function isLoggedIn() {
		//return $this->_logged_in;
		if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 1) {
			return true;
		} else {
			return false;
		}
	}
/*
	public function getUserID() {
		if($this->_logged_in) {
			return $this->_user_id.'zalogowany';
		} else {
			return false;
		}
	}

	public function getAtributes() {
		//return unserialize(stripslashes($this->_session_value));
		return $this->_session_value;
	}

	public function getUserObject() {
		if($this->_logged_in) {
			//$this->_user = new User($this->_user_id, '');
			//...
		}
		return $this->_user->getUser();
	}
*/
	public function getUserPermission() {
		if(isset($_SESSION['perm'])) 
			return $_SESSION['perm'];
			//...
		
	}

	public function checkPermissions($perms) {
//		foreach($this->_user->getPerms() as $k => $v) {
		foreach($_SESSION['perms'] as $k => $v) {
			if(in_array($v, $perms)) {
				return true;
			} else {
				return false;
			}
		}
	}
/*
	public function getSessionIdentifier() {
		return $this->_session_id;
	}
*/
	public function getUserId() {
		if(isset($_SESSION['user_id'])) {
			return $_SESSION['user_id'];
		}
	}
	
	public function logIn($username, $password) {
		//$sSha1Password = sha1($password);
		$aRow = $this->_db->getRow('SELECT ID_MEMBER AS id, memberName AS login, sz_perm FROM smf2_members WHERE memberName = "'.$username.'" AND passwd = "'.sha1(strtolower($username.$password)).'"');

		if($aRow['id'] != null) {
			//$this->_user_id = $aRow['id'];
			//$this->_logged_in = true;

//	$this->_session_value = $aRow['']
			// ... 
			//$this->_db->execute('UPDATE sz_users SET logged_in = 1 WHERE id = '.$this->_user_id.'');

//    $_SESSION['logged_in']     = '1';
		//$this->_user = new User($aRow['id'], $aRow['login'], $aRow['sz_perm'], explode(',', $aRow['sz_perm']));

$_SESSION['user_id'] = $aRow['id'];
$_SESSION['username'] = $aRow['login'];
$_SESSION['logged_in'] = 1;

	//$this->_session_values = $_SESSION;
//$this->_session_value = 'AAA';

//    $_SESSION['member_id']     = $user['ID_MEMBER'];
//    $_SESSION['pass_hash']     = $user['passwd'];
    $_SESSION['perm']          = $aRow['sz_perm'];
//    $_SESSION['sz_last_visit'] = $user['sz_last_visit'];
    $_SESSION['perms']       = explode(',', $aRow['sz_perm']);


    //$this->_db->execute('UPDATE smf2_members SET logged_in = 1, last_visit = UNIX_TIMESTAMP() WHERE ID_MEMBER="'.$aRow['id'].'"');
    $this->_db->execute('UPDATE sz_session SET session_member="'.$aRow['login'].'" WHERE session_id="'.session_id().'"');

			return true;
		} else {
			return false;
		}
	}

	public function logOut() {
		if($this->isLoggedIn()) {
			echo 'logout';
//			$this->_db->execute('UPDATE sz_users SET logged_in = 0 WHERE id = '.$this->_user_id.'');
$this->_db->execute('UPDATE sz_session SET session_member="Guest" WHERE session_id="'.session_id().'"');
			$this->_logged_in = false;

//$this->_user = new User(0, 'Guest');
			//$this->_user_id = 0;
			session_unset();
			session_destroy();
			return true;
		} else {
			return false;
		}
	}
/*
	public function _open($session_savepath, $session_name) {
		return true;
	}
	
	public function _close() {
		$this->_gc($this->_session_timeout);
		return true;
	}

	function _read($session_id)
	{
		$strValue = $this->_db->getOne('SELECT `session_value` FROM `'.$this->_table.'` WHERE `session_id` = "'.$session_id.'"');
		$intCount = $this->_db->getOne('SELECT COUNT(`session_id`) FROM `'.$this->_table.'` WHERE `session_id` = "'.$session_id.'"'); 
		if( $intCount == 0)
		{
			$query = $this->_db->execute('INSERT INTO '.$this->_table .'(`session_id`, `session_start`, `session_time`) VALUES("'.$session_id.'", UNIX_TIMESTAMP() , UNIX_TIMESTAMP() )');
		}

		if( isset($strValue) )
		{
			return stripslashes($strValue);
		}
		else
		{
			return false;
		}
	}

	function _write( $session_id, $session_value )
	{	  
		$res = $this->_db->execute('UPDATE `'.$this->_table .'` 
										SET `session_time` = UNIX_TIMESTAMP(), `session_value` = "'.addslashes($session_value).'" 
									   WHERE `session_id` = "'.$session_id.'"');
		if($res)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function _destroy( $session_id )
	{
		$this->_db->execute('DELETE FROM `'.$this->_table.'` WHERE `session_id` = "'.$session_id.'"');
		return true;
	}

	function _gc( $session_lifetime )
	{
		$this->_db->execute('DELETE FROM `'.$this->_table.'` 
		WHERE `session_time` < (UNIX_TIMESTAMP() - "'.$session_lifetime.'")');
		return true;

	}
*/
/////////////////////

/*
	function _read_( $session_id )
	{
		$this->_session_id = $session_id;
		
		$sValue = $this->_db->getOne('SELECT `session_value` FROM sz_session WHERE `session_id` = "'.$session_id.'"');
		$aSession = $this->_db->getRow('SELECT * FROM sz_session WHERE `session_id` = "'.$session_id.'"');
		$iCount = $this->_db->getOne('SELECT COUNT(`session_id`) FROM sz_session WHERE `session_id` = "'.$session_id.'"'); 
		$aUser = $this->_db->getRow('SELECT ID_MEMBER, sz_last_visit, sz_perm FROM smf2_members WHERE `memberName` = "'.$aSession['session_member'].'"'); 



		if( $iCount == 0)
		{
			$query = $this->_db->execute('INSERT INTO sz_session(`session_id`, `session_start`, `session_time`) VALUES("'.$session_id.'", UNIX_TIMESTAMP() , UNIX_TIMESTAMP() )');

		}

		if($sValue) {
			//$this->_native_session_id = $aSession['id'];
			if($aSession['session_member'] != 'Guest') {
				$this->_logged_in = true;
				$this->_user_id = $aUser['ID_MEMBER'];
				$this->_session_value = stripslashes($aSession['session_value']);

				$this->_user = new User($aUser['ID_MEMBER'], $aSession['session_member'], $aUser['sz_perm'], explode(',', $aUser['sz_perm']));

				//$this->_session_values = unserialize($aSession['session_value']);
				return stripslashes($sValue);
			} else {
				$this->_logged_in = false;
				return false;

			}
		} else {
			$this->_logged_in = false;
			return false;
			//nowy wpis w _db
			//$this->_db->execute('INSERT INTO sz_user_session(ascii_session_id, logged_in, user_id, created, user_agent) VALUES ('.$id.', 0, 0, NOW(), "'.$sUsszgent.'")');
			//pobranie prawdziwego identyfikatora
			//$this->_session_id = $this->_db->getOne('SELECT session_id FROM sz_session WHERE session_id = '.$session_id.'');
		}
*/
/*
		$sUsszgent = $_SERVER['HTTP_USER_AGENT'];
		$this->_php_session_id = $id;
		$failed =1 ; // wtf... ??







		$aSession = $this->_db->getRow('SELECT id, logged_in, user_id FROM sz_user_session WHERE ascii_session_id = "'.$id.'" ');
		if($aSession) {
			$this->_native_session_id = $aSession['id'];
			if($aSession['logged_in'] == 1) {
				$this->_logged_in = true;
				$this->_user_id = $aSession['user_id'];
			} else {
				$this->_logged_in = false;

			}
		} else {
			$this->_logged_in = false;
			//nowy wpis w _db
			$this->_db->execute('INSERT INTO sz_user_session(ascii_session_id, logged_in, user_id, created, user_agent) VALUES ('.$id.', 0, 0, NOW(), "'.$sUsszgent.'")');
			//pobranie prawdziwego identyfikatora
			$this->_native_session_id = $this->_db->getOne('SELECT id FROM sz_user_session WHERE ascii_session_id = '.$id.'');
		}
		return ''; // wtf...
*/


/*/
		if( isset($sValue) )
		{
//$this->_session_values = unserialize($sValue);
			return stripslashes($sValue);
		}*/
/*
	}

	function _write_( $session_id, $session_value )
	{	  
		$res = $this->_db->execute('UPDATE sz_session 
										SET `session_time` = UNIX_TIMESTAMP(), `session_value` = "'.addslashes($session_value).'" 
									   WHERE `session_id` = "'.$session_id.'"');
*/
/*		$res = $this->_db->execute('UPDATE sz_session 
										SET `session_time` = UNIX_TIMESTAMP(), `session_value` = "'.addslashes($session_value).'" 
									   WHERE `session_id` = "'.$session_id.'"');
*/
/*
		if($res)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function _destroy_( $session_id )
	{
		$this->_db->execute('DELETE FROM sz_session WHERE `session_id` = "'.$session_id.'"');
		return true;
	}

	function _gc_( $session_lifetime )
	{
		$this->__db->execute('DELETE FROM sz_session 
		WHERE `session_time` < (UNIX_TIMESTAMP() - "'.$session_lifetime.'")');
		return true;

	}
*/
}

/*
class User {
	private $_id;
	private $_username;
	private $_perm;
	private $_perms;

	public function __construct($id, $username, $perm=null, $perms=null) {
		$this->_id = $id;
		$this->_username = $username;
		$this->_perm = $perm;
		$this->_perms = $perms;
	}

	public function getUserId() {
		return $this->_id;
	}

	public function getPerms() {
		return $this->_perms;
	}

	public function getPerm() {
		return $this->_perm;
	}

	public function isAdmin() {
		if($this->_group == 'admin') {
			return true;
		}
	}

	public function getUser() {
		return 'nazywam się '.$this->_username.', moje ID:'.$this->_id.', mam uprawnienia: '.$this->_perm;
	}
}
*/
?>