<?php
require_once 'inc/standard.php';

$page = new Page('login', ALL);

$page->setTitle('Sign In');
$post = [
	'ucinetid' 	=> isset($_POST['ucinetid']) ? $_POST['ucinetid']: '',
	'action' 	=> isset($_POST['action']) ? $_POST['action']: ''
	];
$get = [
	'ucinetid' 	=> isset($_GET['ucinetid']) ? $_GET['ucinetid']: '',
	'redirect' 	=> isset($_GET['redirect']) ? $_GET['redirect']: ''
	];

$ucinetid = $post['ucinetid'] or $get['ucinetid'];
$person = new UCIPerson($ucinetid);
$ucinetid = $person->ucinetid;

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

if( $post['action'] == 'login' && $post['ucinetid'])
{
	$errors=1;	
	if(!($login->login($ucinetid, $_POST['password'])))
	{
		$errors++;
		$error_message = $login->error;
	}
	
	if($errors == 1)
	{
		if($get['redirect']){
			header('Location:'.$get['redirect']);
		}
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
