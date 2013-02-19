<?php
require_once 'inc/standard.php';

$page = new Page('login', ALL);

$page->setTitle('Sign In');

$ucinetid = $_POST['ucinetid'] or $_GET['ucinetid'];
$person = new UCIPerson($ucinetid);
$ucinetid = $person->ucinetid;

$redirect = $_GET['redirect'];
$login = new Login();

if(isset($_SESSION['name']))
{
	$message = 'You are already signed in as '.$_SESSION['ucinetid'].'. If you would like to logout, please visit the <a href="logout.php">Log Out</a> page.';
	$page->setMessage($message, 'success');
	$bottom = ' <span class="alert"> Hi '.$_SESSION['name'].',<br><br>'.$message.'</span>';
	$box = new Box('Already Signed In', $bottom);
	$page->setContent($box->display('half'));
	$page->buildPage();
}

if($_POST['action'] == 'login' && isset($_POST['ucinetid']))
{
	$errors=1;	
	if(!($login->login($ucinetid, $_POST['password'])))
	{
		$errors++;
		$error_message = $login->error;
	}
	
	if($errors == 1)
	{
		if($redirect)
			header('Location:'.$redirect);
		else
		{
			header('Location: settings.php');
		}
	}
	else
	{
		$page->setMessage($error_message, 'failure');
	}
}
$bottom = $login->loginform('login','login', 'settings.php', $ucinetid);

$box = new Box('Login', $bottom);
$box->setBadge('Register', 'register.php');

$page->setContent($box->display('half'));
$page->buildPage();
?>
