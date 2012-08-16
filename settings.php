<?php

require_once 'inc/standard.php';
$page = new Page('settings', PARTICIPANT);
$ucinetid = $_SESSION['ucinetid'];
$redirect = $_SERVER['PHP_SELF'];

//clean each $_POST value of dangerous inputs
//example $newsettings['email'] = 'stburke@uci.edu';
foreach ($_POST as $key => $value) {
	//echo '$newsettings[\''.$key.'\'] = '.$value.';<br>';
	$newsettings[$key] = trim(strip_tags($value));
}

if(!($page->login->checkValidAccess($page, $redirect)))
{
	$box = new Box('Access Denied', 'You do not have access to view this page');
	$page->setContent($box->display('half'));
	$page->buildPage();
}

//select the current users settings
$sql = 'SELECT users.*, logon.* 
		FROM users LEFT JOIN logon
 		ON users.ucinetid = logon.ucinetid
 		WHERE users.ucinetid = "'.$ucinetid.'"
 		LIMIT 1';
$page->DB->query($sql);

//set users settings to $user
$user = $page->DB->resultToSingleArray();


//if the action is update then do this
if($_GET['action'] == 'update' && $_POST)
{
	$errors = 1;
	if(strlen($newsettings['name']) < 2)
	{
		$error_message[$errors] = "Your name is too short";
		$errors++;
		$error_name = 'error';
	}
	if($newsettings['major'] == '')
	{
		$error_message[$errors] = "Please select your major";
		$errors++;
		$error_major = 'error';
	}
	if($newsettings['level'] == '')
	{
		$error_message[$errors] = "Please select your Level";
		$errors++;
		$error_level = 'error';
	}
	
	if((strlen($newsettings['password_old']) != 0) || 
	   (strlen($newsettings['password_new']) != 0) || 
	   (strlen($newsettings['password_con']) != 0))
	{
		if($user['password'] != md5($newsettings['password_old']))
		{
			$errors++;
			$error_message[$errors] = 'Your old password is not correct';
			$error_password_old = 'error';
		}
		if(strlen($newsettings['password_new']) < 6)
		{
			$errors++;
			$error_message[$errors] = 'Your password must be 6 characters or greater';
			$error_password_new = 'error';
		}
		
		if($newsettings['password_new'] != $newsettings['password_con'])
		{
			$errors++;
			$error_message[$errors] = 'Your passwords do not match';
			$error_password_con = 'error';
		}
	}
	
	if($errors == 1)
	{
		$name = $newsettings['name'];
		$major = $newsettings['major'];
		$level = $newsettings['level'];
		$password_old = $newsettings['password_old'];
		$password_new = md5($newsettings['password_new']);
		
		$opt = $newsettings['opt'];
		
		if((strlen($newsettings['password_old']) != 0) && 
		   (strlen($newsettings['password_new']) != 0) && 
		   (strlen($newsettings['password_con']) != 0))
		{
			$sql = "UPDATE logon 
					SET ucinetid = '$ucinetid',
					password = '$password_new'
					WHERE ucinetid = '$ucinetid'";
			$page->login->qry($sql);
		}
		
		$sql = "UPDATE users SET
				name = '$name',
				major = '$major',
				level = '$level',
				opt = '$opt'
				WHERE ucinetid = '$ucinetid'";
		$page->login->qry($sql);
		
		$message = 'Your settings have been successfully updated.';
		$page->setMessage($message, 'success');
	}
	else
	{
		if(is_array($error_message))
			$page->setMessage(implode(', ', $error_message), 'failure');
		else
			$page->setMessage($error_message, 'failure');
	}
}

if($_GET['action'] == 'barcode' && $_POST)
{
	$barcode = new Barcode($newsettings['barcode']);
	$errors = 1;
	
	if(strlen($barcode->code) > 0)
	{
		if(!$barcode->exists())
		{
			$errors++;
			$error_message[$errors] = 'The barcode #'.$newsettings['barcode'].' does not exist in the system';
			$error_barcode = 'error';
		}
		elseif($barcode->getUCInetID() == $ucinetid)
		{
			$errors++;
			$error_message[$errors] = 'The barcode #'.$newsettings['barcode'].' is already associated with your account';
			$error_barcode = 'error';
		}
		elseif(!$barcode->isNonAssociated())
		{
			$errors++;
			$error_message[$errors] = $barcode->error;
			$error_barcode = 'error';
		}
	}	
	
	if($errors == 1)
	{
		if(strlen($barcode->code) > 0)
		{
			if($barcode->associate($ucinetid))
			{
				$page->setMessage('You have successfully associated barcode #'.$newsettings['barcode'].' to your account', 'success');
			}
			else
			{
				$page->setMessage($barcode->error, 'failure');
			}
		}
		else 
		{
			if($barcode->unassociate($ucinetid))
			{
				$page->setMessage('You have successfully unassociated any barcode from your account', 'success');
				unset($newsettings['barcode']);
				unset($user['barcode']);
			}
			else
			{
				$page->setMessage($barcode->error, 'failure');
			}
		}
	}
	else
	{
		$page->setMessage($error_message, 'failure');
	}
	
}

$name = ($newsettings['name']) ? $newsettings['name']:$user['name'];
$major = ($newsettings['major']) ? $newsettings['major']:$user['major'];
$level = ($newsettings['level']) ? $newsettings['level']:$user['level'];
$opt = ($newsettings['opt']) ? $newsettings['opt']:$user['opt'];
$barcode = ($newsettings['barcode']) ? $newsettings['barcode']:$user['barcode'];

$opt_checked = ($opt == '1') ? 'CHECKED':'';

$barcode_settings = '<form action="'.$_SERVER['PHP_SELF'].'?action=barcode" method="POST">
		<div class="disclaimer">
		    This form will be used to associate your Barcode with your profile.
		</div>
		<div class="row">
			<img src="images/wristband.png" class="wristband">
		</div>
		<div class="row '.$error_barcode.'">
		    <label class="fieldname" for="name">
		        Barcode ID
		        <span class="require1">*</span>
		    </label>
		    <input class="textarea" name="barcode" type="text" value="'.$barcode.'">
		</div>
		<div class="row">
		    <input type="submit" value="Associate Barcode">
		</div>
	</form>';

$user_settings = '
<form action="'.$_SERVER['PHP_SELF'].'?action=update" method="POST">
		<div class="disclaimer">
		    For security purposes, you may not change your UCInetID or your UCI email. This security measure is to prevent users from using a UCInetID that does not belong to them. By using your UCI email, this will ensure that you have control over your account under all circumstances.
		</div>
		<div class="separator"></div>
		<div class="row">
		    <label class="fieldname" for="ucinetid">
		        UCInetID 
		    </label>
		    <input class="textarea readonly" name="ucinetid" type="text" value="'.$user['ucinetid'].'" READONLY>
		</div>
		<div class="row">
		    <label class="fieldname" for="email">
		        Email
		    </label>
		    <input class="textarea readonly" name="email" type="email" value="'.$user['email'].'" READONLY>
		</div>
		<div class="row">
		    <label class="fieldname" for="email">
		        Access
		    </label>
		    <input class="textarea readonly" name="email" type="email" value="'.switchAccess($user['access']).'" READONLY>
		</div>
		<div class="row '.$error_name.'">
		    <label class="fieldname" for="name">
		        Name
		        <span class="require1">*</span>
		    </label>
		    <input class="textarea" name="name" type="text" value="'.$name.'">
		</div>';
		
		$var_array = new VarArray();
		$major_menu = new DropMenu('major', $var_array->getMajors('setIntial'), $major, 'textarea');
		$level_menu = new DropMenu('level', $var_array->getLevels('setIntial'), $level, 'textarea');
		
		$user_settings .= '
		<div class="row '.$error_major.'">
		    <label class="fieldname" for="major">Major <span class="require1">*</span>
		    </label>'.$major_menu->display().'
		</div>
		<div class="row '.$error_level.'">
		    <label class="fieldname" for="level">
		        Level
		        <span class="require1">*</span>
		    </label>
		    '.$level_menu->display().'
		</div>
		<div class="row">
		    <label class="fieldname" for="opt">
		        Receive Emails?
		        <span class="require1">*</span>
		    </label>
		    <input class="right" name="opt" type="checkbox" value="1" '.$opt_checked.'>
		</div>
		<div class="separator"></div>
		<div class="row '.$error_password_old.'">
		    <label class="fieldname" for="password_old">
		        Old Password
		        <span class="require1">*</span>
		    </label>
		    <input class="textarea" name="password_old" type="password">
		</div>
		<div class="row '.$error_password_new.'">
		    <label class="fieldname" for="password_new">
		        New Password
		        <span class="require1">*</span>
		    </label>
		    <input class="textarea" name="password_new" type="password">
		</div>
		<div class="row '.$error_password_con.'">
		    <label class="fieldname" for="password_con">
		        Confirm
		        <span class="require1">*</span>
		    </label>
		    <input class="textarea" name="password_con" type="password">
		</div>
		<div class="separator"></div>
		<div class="row">
		    <input type="submit" value="Update Settings">
		</div>
	</form>';
	
		
$box_barcode = new Box('Barcode', $barcode_settings);
$box_user = new Box('User Settings', $user_settings);

$content = ' <div id="container_wrapper">'.
			$box_user->display('full').'
			<div class="separator"></div>
			'.$box_barcode->display('full').
			'</div>';

$page->setContent($content);
$page->buildPage();
?>