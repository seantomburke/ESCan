<?php
require_once 'inc/standard.php';

$page = new Page('iforgot', ALL);

$page->setTitle('Forgot Password');

//clean each $_POST value of dangerous inputs
//example $newsettings['email'] = 'stburke@uci.edu';
foreach ($_POST as $key => $value) {
	//echo '$newsettings[\''.$key.'\'] = '.$value.';<br>';
	$iforgot[$key] = trim(strip_tags($value));
}

$login = new Login();
$ucinetid = ($_POST['ucinetid'])? $_POST['ucinetid']:$_GET['ucinetid'];
$person = new UCIPerson($ucinetid);
$ucinetid = $person->ucinetid;

if($_POST['action'] == 'resetlogin')
{
	$errors=1;
	if(!($login->exists($ucinetid)))
	{
		$errors++;
		$error_message = $login->error;
	}

	if($errors == 1)
	{
		$secret = substr(base64_encode(crypt('', '')), 0, 32);


		$sql = 'SELECT l.*, u.*
				FROM users AS u, logon AS l
		 		WHERE u.ucinetid = "'.$ucinetid.'"
		 		LIMIT 1';
		$page->DB->query($sql);
		$user = $page->DB->resultToSingleArray();

		$sql = "REPLACE INTO reset (ucinetid, secret, date)
				VALUES ('$ucinetid', '$secret', NOW())";
		$result = $page->DB->query($sql);

		$message = 'An email was sent to <a href="https://webmail.uci.edu/rcm/?_user='.$ucinetid.'">'.$user['email'].'</a> with instructions on how to reset your password. Visit <a href="https://webmail.uci.edu/rcm/?_user='.$ucinetid.'">WebMail</a>';
		$page->setMessage($message, 'success');
		//mail;
		$link = WEBSITE.'recover.php?ucinetid='.$ucinetid.'&secret='.$secret;
		$to = $user['email'];
		$subject = 'Forgot your password?';
		$body = ' <p>Hi '.$user['name'].', </p>
				<p>A request to reset you password has been made. If you did not initiate this reset, please ignore this email.</p>
				<br>
				<p>To reset you password please follow the link provided, or copy and paste the following link into your browser:</p>
				<strong><a href="'.$link.'">'.$link.'</a></strong>';
		$mail = new Mail($to, $subject, $body);
		$mail->send();
		$page->login->logout();
	}
	else
	{
		$page->setMessage($error_message, 'failure');
	}
}

$bottom = $login->resetForm('resetform','resetform','iforgot.php', $ucinetid);

$box = new Box('Forgot Password?', $bottom);

$box->setBadge('Register', 'register.php?ucinetid='.$ucinetid);

$page->setContent($box->display('half'));
$page->buildPage();