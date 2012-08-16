<?php

require_once 'inc/standard.php';
$page = new Page('recover', PARTICIPANT);
$box = new Box('Recover');
$ucinetid = $_GET['ucinetid'];
$secret = $_GET['secret'];

if($_GET['action'] == 'change' && isset($_GET['ucinetid']) && $_POST)
{
	$errors_change = 1;
	if(!($page->login->exists($ucinetid)))
	{
		$errors_change++;
		$error_message[$errors_change] = $ucinetid.' is not registered with '.PRODUCT.'. Please register here at the <a href="register.php">Registration Page</a>';
	}
	if(strlen($_POST['password']) < 6)
	{
		$errors_change++;
		$error_message[$errors_change] = 'Your new password is too short';
	}
	
	if($_POST['password'] != $_POST['password2'])
	{
		$errors_change++;
		$error_message[$errors_change] = 'Your passwords do not match';
	}
	if($errors_change == 1)
	{
		$e_password = md5($_POST['password']);
		$sql = "UPDATE logon 
				SET password = '$e_password'
				WHERE ucinetid = '$ucinetid'
				LIMIT 1";
		$page->DB->query($sql);
		
		$sql = "DELETE FROM reset
				WHERE ucinetid = '$ucinetid'";
		$page->DB->query($sql);
		
		$page->setMessage('Your password has been successfully changed', 'success');
		$display_login = true;
		$page->login->logout();
	}
	else
	{
		if(is_array($error_message))
		{
			$error_message = implode(', ', $error_message);
		}
			
		$page->setMessage($error_message, 'failure');
	}	
	
}

if($display_login)
{
	$page->login->checkValidAccess($page, 'settings.php', $ucinetid);
}

if($_GET['ucinetid'] && $_GET['secret'])
{
	$errors = 1;
	$sql = "SELECT * FROM reset WHERE ucinetid = '$ucinetid' AND secret = '$secret'";
	$result = $page->DB->query($sql);
	$user = $page->DB->resultToSingleArray();
	
	if($page->DB->isEmpty())
	{
		$errors++;
		$error_message = 'Sorry, there was an error with reseting your password please visit the <a href="/
		iforgot.php">Forgot Password</a> page to reset your password again.';
	}
	
	if($errors == 1)
	{
		$var_array = new VarArray();
		
		$bottom = '
		<form action="'.$_SERVER['PHP_SELF'].'?action=change&ucinetid='.$ucinetid.'&secret='.$secret.'" method="POST">
				<div class="row">
				    <label class="fieldname" for="ucinetid">
				        UCInetID 
				        <span class="require1">*</span>
				    </label>
				    <input class="textarea readonly" name="ucinetid" type="text" value="'.$ucinetid.'" readonly>
				</div>
				<div class="row">
				    <label class="fieldname" for="password">
				        New Password
				        <span class="require1">*</span>
				    </label>
				    <input class="textarea" name="password" type="password">
				</div>
				<div class="row">
				    <label class="fieldname" for="password2">
				        Confirm
				        <span class="require1">*</span>
				    </label>
				    <input class="textarea" name="password2" type="password">
				</div>
				<div class="separator"></div>
				<div class="row">
				    <input type="submit" value="Change Password" name="submit_change">
				</div>
			</form>';
	}
	
	else
	{
		$bottom = $error_message;
	}
}
else
{
	$bottom = 'You have arrived to this page in error, please return to the <a href="index.php">Home Page</a>.';
}

$box->setContent($bottom);
$page->setContent($box->display('half'));
$page->buildPage();

?>