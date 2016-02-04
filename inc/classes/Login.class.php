<?php

class Login {

	public $db;
	public $session;

	//table fields
	public $user_table = 'logon';			//Users table name
	public $ucinetid = '';					//ucinetid column (value MUST be valid email)
	public $password = '';					//PASSWORD column
	public $user_level = 'userlevel';		//(optional) userlevel column
	public $is_error;
	public $error;

	//encryption
	public $encrypt = true;		//set to true to use md5 encryption for the password

	function __construct()
	{
		$this->db = new DB();
		//$this->session = new Session();
		$is_error = false;
	}

	function isLoggedIn()
	{
		return (isset($_SESSION['loggedin']));
	}

	function checkValidAccess($page, $redirect = 'settings.php', $ucinetid = '')
	{
		//echo $_SESSION['access'].' >= '.$page->access.'<br>';
		if(!(isset($_SESSION['ucinetid'])))
		{
			$box = new Box('Verify Access');
			$bottom = $this->loginForm('login', 'login', $redirect, $ucinetid);
			$box->setContent($bottom);
			$page->setContent($box->display('half'));
			$page->buildPage();
		}
		elseif($_SESSION['access'] >= $page->access)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	 
	//login function
	function login($ucinetid, $password, $table = 'logon'){
		//conect to DB
		//make sure table name is set
		
		if($this->user_table == ""){
			$this->user_table = $table;
		}
		//check if encryption is used
		if($this->encrypt == true){
			$password = md5($password);
		}
		//execute login via qry function that prevents MySQL injections
		$query = "SELECT * FROM logon
				 WHERE ucinetid = '".$ucinetid."' 
				 LIMIT 1";
		$result = $this->qry($query);

		if($this->db->numRows() == 0)
		{
			$this->is_error = true;
			$this->error = $ucinetid.' is not registered with '.PRODUCT.'. Please register here at the <a href="register.php">Registration Page</a>';
			return false;
		}
		$query = "SELECT l.*, u.*
				FROM logon AS l, users AS u
				WHERE l.ucinetid = '".$ucinetid."' 
				AND password = '".$password."'
				AND l.ucinetid = u.ucinetid
				LIMIT 1";

		$result = $this->qry($query);

		if($this->db->numRows() == 0)
		{
			$this->is_error = true;
			$this->error = 'The UCInetID and password do not match. If you forgot your password, you can reset your password by clicking <a href="iforgot.php?ucinetid='.$ucinetid.'">here</a>.';
			return false;
		}

		$row=$this->db->resultToSingleArray();
		if($row){
			if($row['ucinetid'] !="" && $row['password'] != ""){
				//register sessions
				//you can add additional sessions here if needed
				
				$offset = 3;
				$date = date('Y-m-d', time()-($offset*60*60));
				$time = date('H:i:s', time()-($offset*60*60));
				
				$sql = 'UPDATE logon
						SET last_login = "'.$date.' '.$time.'"
						WHERE ucinetid = "'.$row['ucinetid'].'"';
				$this->db->execute($sql);
				
				$_SESSION['loggedin'] = true;
				$_SESSION['ucinetid'] = $row['ucinetid'];
				$_SESSION['name'] = $row['name'];
				$_SESSION['level'] = $row['level'];
				$_SESSION['email'] = $row['email'];
				$_SESSION['major'] = $row['major'];
				$_SESSION['access'] = $row['access'];
				return true;
			}else{
				//$this->session->delete();
				//session_destroy();
				return false;
			}
		}else{
			return false;
		}

	}

	//prevent injection
	function qry($query) {
		$args  = func_get_args();
		$query = array_shift($args);
		$query = str_replace("?", "%s", $query);
		$args  = array_map('mysqli_real_escape_string', $args);
		array_unshift($args,$query);
		$query = call_user_func_array('sprintf',$args);
		$result = $this->db->query($query) or die(mysqli_error());
		if($result){
			return $result;
		}else{
			$error = "Error";
			return $result;
		}
	}

	//logout function
	function logout(){
		session_destroy();
		return;
	}

	function exists($ucinetid)
	{
		$query = "SELECT ucinetid
				FROM logon
				WHERE ucinetid = '$ucinetid'";

		$this->db->query($query);
		if($this->db->num_rows == 0)
		{
			$this->error = 'The UCInetID, '.$ucinetid.', is not registered with '.PRODUCT.'. Please register by going to the <a href="register.php?ucinetid='.$ucinetid.'">Registration Page</a>.';
			return false;
		}
		else
		{
			return true;
		}
	}

	//reset password

	//create random password with 8 alphanumerical characters
	function createPassword() {
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;
		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}

	//login form
	function loginForm($formname = 'login', $formclass = 'login', $redirect = 'settings.php', $ucinetid = ''){

		//echo 'Redirect: '.$redirect.'<br>';
		//echo 'Server_name: '.$_SERVER['SERVER_NAME'].'<br>';
		if($pos = strpos($redirect, $_SERVER['SERVER_NAME']))
		{
			//echo '$pos == '.strlen($redirect).' + '.$pos.'<br>';
			$redirect = substr($redirect, $pos+strlen($_SERVER['SERVER_NAME']));
		}
		//echo 'Redirect: '.$redirect.'<br>';
		$redirect = ($redirect == 'login.php') ? 'settings.php' : $redirect;
		//echo 'Redirect: '.$redirect.'<br>';
		//conect to DB
		return '<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="login.php?redirect='.$redirect.'">
				<div class="row">
					<label class="fieldname" for="ucinetid">UCInetID</label>
					<input class="textarea" name="ucinetid" id="ucinetid" type="text" value='.$ucinetid.'>
				</div>
				<div class="row">
					<label class="fieldname" for="password">Password</label>
					<input class="textarea" name="password" id="password" type="password">
				</div>
				<div class="row">
					<span class="forgot fieldname"><a href="register.php">Register</a> | <a href="iforgot.php">Forgot your password?</a></span>
					<input name="submit" id="submit" value="Login" type="submit">
					<input name="action" id="action" value="login" type="hidden">
				</div>
				<div clas="row">
					
				</div>
			</form>';
	}
	//reset password form
	function resetForm($formname, $formclass, $formaction, $ucinetid = ''){
		//conect to DB
		return '<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
				<div class="row">
					<label class="fieldname" for="ucinetid">UCInetID</label>
					<input class="textarea" name="ucinetid" id="ucinetid" type="text" value='.$ucinetid.'>
					<input name="action" id="action" value="resetlogin" type="hidden">
				</div>
				<div class="row">
					<input name="submit" id="submit" value="Reset Password" type="submit">
				</div>
			</form>';
	}

	function loginFormMini($formname, $formaction, $formclass = '', $isPhone)
	{
		$loggedin = isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 0;
		$name = 	isset($_SESSION['name']) ? $_SESSION['name'] : '';
		$access = 	isset($_SESSION['access']) ? $_SESSION['access'] : '';
		
		if($loggedin == 1)
		{
			$output ='
			<div class="phone '.$formclass.'">
				<span>'.$name.'</span> | <a href="settings.php">Settings</a> | <a href="logout.php">Logout</a>
			</div>
			<div class="'.$formclass.'">
				<span><strong>'.$name.'</strong>: '.switchAccess($access).'</span> | <a href="settings.php">Settings</a> | <a href="logout.php">Logout</a>
			</div>';
		} else {
			$output = '
			<div class="phone '.$formclass.'">
				<span><a href="login.php">Login</a>
			</div>
			<div class="nophone '.$formclass.'">
			<form name="'.$formname.'" method="post" id="'.$formname.'"
			 enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
				<div class="row">
					<input name="ucinetid" id="ucinetid" type="text" placeholder="UCInetID">
					<input name="password" id="password" type="password" placeholder="Password">
					<input name="action" id="action" value="login" type="hidden">
					<input name="submit" id="submit" value="Login" type="submit">
				</div>
				<div class="row">
					<span class="forgot"><a href="register.php">Register</a> | <a href="iforgot.php">Forgot your password?</a></span>
				</div>
			</form>
			</div>';
		}
		return $output;
	}	

	function db_close(){
		$this->db->close();
	}
}
?>