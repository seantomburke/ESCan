<?php

include_once 'inc/standard.php';

$page = new Page('register', ALL);

$js = ' if($("#major").val() != "Other")
    	{
    		$("#major_input").hide();
    	}
    	
        var barcode = $("#barcode");
        if(barcode)
        {
    	    $("#barcode").focus();
        }
        else
        {                
            $("#search").focus();
    	}';
			
$page->setJSInitial($js);


$intro1 = "This section is used to register participants. Enter their 
8 digit UCInetID (their email handle) and ESCan will search for them in the
UCI Directory. If they are an engineering major, they will be sent to the next
page. Scan their wristband and instruct them to head to the event booth. If they
are not an engineering major, or they are a double major, grad student or faculty member,
you will be presented with a pop up explaining this, then click continue.";

$intro2 = "Here the UCInetID <b>stburke</b> is entered";

$intro3 = "This is the information for <b>stburke</b> pulled from the UCI Directory. Make sure it is correct, then
scan the users barcode wristband. This will associate their UCInetID to their Barcode. Make sure
to remind them to keep the wristband on all week for a change to win prizes.";


//clean each $_POST value of dangerous inputs
//example $user['email'] = 'stburke@uci.edu';
foreach ($_POST as $key => $value) {
	//echo '$user[\''.$key.'\'] = '.$value.';<br>';
	$user[$key] = trim(strip_tags($value));
}
$user['ucinetid'] = trim(strip_tags($_GET['ucinetid']));

//The old way to do messages
/*if($_GET['msg'] == 'success')
{
$page->setMessage($_GET['message'], 'success');
}*/

//let the person know that they are already signed in
if($_SESSION['access'] > PARTICIPANT)
{
	if(!($_GET))
	{
	$page->setMessage('Hello '.$_SESSION['name'].', please ask for users UCInetID (their Email)', 'success');
	}
}
if($_GET['submit_search'] == "Search"){
	$autofocus = ' autofocus onfocus="this.select();" onmouseup="return false;"';
}
$introfirst = ($_GET['ucinetid']) ? $intro2: $intro1;

$introtext = ' data-step="5" data-intro="'.$introfirst.'" data-position="right"';
$intro_scripts = '<script src="js/intro.min.js"></script>
		<script type="text/javascript">
		if(window.location.hash) {
			var hash = window.location.hash.substring(1);
			if(hash == "intro"){
				introJs().setOption("doneLabel", "Next page").start().oncomplete(function() {
					window.location.href = "events.php?#intro";
				});
			}
		}
    </script>';

$search = '	<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
			<div class="row">
				<div class="disclaimer">
				All information is requested from the <a href="http://directory.uci.edu/index.php?uid='.$user['ucinetid'].'">UCI Directory</a> based on your UCInetID for your convenience. Only public information can be requested, and no other information is collected.
				</div>
			</div>
			<div class="clear"></div>
			<div class="row"'.$introtext.'>
			    <label class="fieldname" for="ucinetid">
			        UCInetID 
			        <span class="require1">*</span>
			    </label>
			    <input id="search" class="textarea" placeholder="UCInetID" autocapitalize="off" type="search" name="ucinetid" value="'.$user['ucinetid'].'"'.$autofocus.'>
			</div>
			<div class="clear"></div>
			<div class="row">
			    <input type="submit" value="Register" name="submit_search">
			</div>
			<div class="clear"></div>
			</form>';

if($_POST['submit_signup'] == 'Register')
{
	$errors_r = 1;
	$barcode = new Barcode($user['barcode']);

	if(strlen($user['name']) < 2)
	{
		$error_message[1][$errors_r] = "Your name is too short";
		$errors_r++;
		$error_name = 'error';
	}
	if($user['major'] == '')
	{
		$error_message[1][$errors_r] = "Please select your major";
		$errors_r++;
		$error_major = 'error';
	}
	if($user['level'] == '')
	{
		$error_message[1][$errors_r] = "Please select your level";
		$errors_r++;
		$error_level = 'error';
	}
	
	if(substr($user['email'], 0, strlen($user['ucinetid'])) != $user['ucinetid'])
	{
		$error_message[1][$errors_r] = "Your email must match your UCInetID";
		$errors_r++;
		$error_email = 'error';
	}
	
	if(substr($user['email'], strlen($user['ucinetid']), 8) != '@uci.edu')
	{
		$error_message[1][$errors_r] = "You must use your UCI email address";
		$errors_r++;
		$error_email = 'error';
	}
	if(strlen($user['barcode']) > 0)
	{	
		if(!$barcode->checkForRegistration($user['ucinetid']))
		{
			$error_message[1][$errors_r] = $barcode->error;
			$errors_r++;
			$error_barcode = 'error';
		}
	}	
	if($errors_r == 1)
	{
		//Insert into SQL
		
		if($page->login->exists($user['ucinetid']))
		{
			if(strlen($barcode->code) > 0)
			{
				if($barcode->associate($user['ucinetid']))
				{
					if($_SESSION['access'] > PARTICIPANT)
						{$message = 'You have successfully associated barcode <strong>#'.$barcode->code.'</strong> to <strong>'.$user['ucinetid'].'</strong>';}
					else
						{$message = 'You have successfully associated barcode <strong>#'.$barcode->code.'</strong> to the UCInetID: <strong>'.$user['ucinetid'].'</strong>';}
						$page->setMessage($message, 'success');
				}
				else
				{
				    if(!$barcode->exists())
				    {
				        $barcode->registerAndAssociate($user['ucinetid']);
				        $message = 'You have registered the barcode <strong>#'.$barcode->code.'</strong> and have successfully associated it to the UCInetID: <strong>'.$user['ucinetid'].'</strong>';
				        $page->setMessage($message, 'success');
				    }
				    else
				    {
					    $page->setMessage($barcode->error, 'success');
				    }
				}
			}
			else
			{
				$page->setMessage('The UCInetID <strong>'.$user['ucinetid'].'</strong> is already registered. Click <a href="iforgot.php?ucinetid='.$ucinetid.'">here</a> if you have forgotten your password.', 'failure');
			}
		}
		else
		{
		$password = $page->login->createPassword();
		$e_pass = md5($password);
		$secret = substr(base64_encode(crypt('', '')), 0, 32);
		
		$sql = 'INSERT INTO logon (ucinetid, password, date, time) 
				VALUES ("'.$user['ucinetid'].'", "'.$e_pass.'", "'.NOW_DATE.'","'.NOW_TIME.'")';
				
		$page->login->qry($sql);
		
		$sql = 'INSERT INTO users 
				(ucinetid, name, email, major, level, access, opt, date, time, volunteer)
				VALUES 
				("'.$user['ucinetid'].'",
				"'.$user['name'].'",
				"'.$user['email'].'",
				"'.$user['major_val'].'",
				"'.$user['level'].'",
				"'.PARTICIPANT.'",
				"'.$user['opt'].'",
				"'.NOW_DATE.'",
				"'.NOW_TIME.'",
				"'.$_SESSION['ucinetid'].'")';
				
		$page->login->qry($sql);
		
		$sql = 'REPLACE INTO reset (ucinetid, secret, date)
				VALUES ("'.$user['ucinetid'].'", "'.$secret.'", NOW())';
		$result = $DB->query($sql);
		
		//associate barcode
		if($barcode->exists())
		{
    		$barcode->associate($user['ucinetid']);
		}
		else
		{
		    $barcode->registerAndAssociate($user['ucinetid']);
		}
		//mail;
		$link = WEBSITE.'recover.php?ucinetid='.$user['ucinetid'].'&secret='.$secret;
		$to = $user['email'];
		$subject = 'Setup your Password';
		$body = ' <p>Hi '.$user['name'].',</p>
		<p>Thank you for signing up for '.PRODUCT.', '.DESCRIPTION.'
		Please click the following link or copy the link into your web browser to setup your password:</p>
		<br>
		<a href="'.$link.'">'.$link.'</a><br>';
		
		$mail = new Mail($to, $subject, $body);
		$mail->send();
		
		if($_SESSION['access'] > PARTICIPANT)
		{
			$message = 'You successfully registered '.$user['name'].'. Instruct them to head to the scanning booth.';
		}
		else
		{
			$message = 'An email has been sent to <a href="https://webmail.uci.edu/rcm/?_user='.$user['ucinetid'].'">'.$user['email'].'</a> with instructions on how to setup your password. Please allow for 5 to 10 minutes for the email to arrive. Be sure to check your spam folder as well. Visit <a href="https://webmail.uci.edu/rcm/?_user='.$user['ucinetid'].'">WebMail</a>';
		}
		$page->setMessage($message, 'success');
		unset($ucinetid);
		unset($_GET['ucinetid']);
		unset($_POST['ucinetid']);
		}
		//header('Location:'.$_SERVER['PHP_SELF'].'?msg=success&message='.$message);
		
	}
	else
	{
		$page->setMessage($error_message[1], 'failure');
	}

}

elseif($_GET['ucinetid'])
{
	$errors_s = 1;
	$person = new UCIPerson($_GET['ucinetid']);
	
	$page->setMessage('Scan wristband now to register <strong>'.$person->name.'</strong>.', 'success');
	
	if(!($person->isValid()))
	{
		$errors_s++;
		$error_message[0] = $person->error;
	}	
	elseif(!($person->isEngineer()))
	{
		if($_GET['submit_continue'] != 'Continue')
		{
			$errors_s++;
			if($_SESSION['access'] > PARTICIPANT)
			{
				$error_message[0] = '
				<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
				This person is registered as a <strong>'.$person->major.'</strong> major. If they are a grad student, they may have an engineering major that is not recognized by the system, click contine and select the appropriate major. If they are another major, click continue, select other, and enter in their major.
					<input type="submit" value="Continue" name="submit_continue">
					<input type="hidden" name="ucinetid" value="'.$_GET['ucinetid'].'">
				</form>';
			}
			else
			{
				$error_message[0] = '
				<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
				This event is primarily for Engineers and Computer Scientists. Your major is registered as a <strong>'.$person->major.'</strong> major. If you believe you received this message in error, you can still register below by clicking continue and entering your major.
					<input type="submit" value="Continue" name="submit_continue">
					<input type="hidden" name="ucinetid" value="'.$_GET['ucinetid'].'">
				</form>';
			}
		}
	}
	elseif($person->getBarcode())
	{
		if($_GET['submit_continue'] != 'Continue')
		{
		$errors_s++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
		The user <strong>'.$person->ucinetid.'</strong> is already registered with the Barcode <strong>#'.$person->getBarcode().'</strong>. If they have lost their wristband and require a new one, please click continue and give them a new one, but remind them to keep their wristband all week.
			<input type="submit" value="Continue" name="submit_continue">
			<input type="hidden" name="ucinetid" value="'.$_GET['ucinetid'].'">
		</form>';
		}
	}
	if($errors_s == 1)
	{	
		$var_array = new VarArray();
		$major_menu = new DropMenu('major', $var_array->getMajors('setIntial'), $person->major, 'textarea');
		$level_menu = new DropMenu('level', $var_array->getLevels('setIntial'), $person->level, 'textarea');
		
		$signup .= '
		<div class="separator"></div>
			<form class="form" action="'.$_SERVER['PHP_SELF'].'?submit_search=Search&ucinetid='.$person->ucinetid.'" method="POST">
				<div class="disclaimer">
				    For security purposes, your UCInetID must match up with your UCI email. This security measure is to prevent users from using a UCInetID that does not belong to them. By using your UCI email, this will ensure that you have control over your account under all circumstances.
				</div>
				<div class="clear"></div>
				<div class="row '.$error_id.'">
				    <label class="fieldname" for="ucinetid">
				        UCInetID 
				        <span class="require1">*</span>
				    </label>
				    <input class="textarea readonly" placeholder="UCInetID" name="ucinetid" type="text" autocapitalize="off" value="'.$person->ucinetid.'" readonly>
				</div>
				<div class="clear"></div>
				<div class="row '.$error_email.'">
				    <label class="fieldname" for="email">
				        Email
				        <span class="require1">*</span>
				    </label>
				    <input class="textarea readonly" placeholder="Email Address" name="email" type="email" value="'.$person->getEmail().'" READONLY>
				</div>
				<div class="clear"></div>
				<div class="row '.$error_name.'">
				    <label class="fieldname" for="name">
				        Name
				        <span class="require1">*</span>
				    </label>
				    <input class="textarea" placeholder="First and Last name" name="name" type="text" value="'.$person->name.'">
				</div>
				<div class="clear"></div>
				<div class="row '.$error_major.'">
				    <label class="fieldname" for="major">Major <span class="require1">*</span>
				    </label>'.$major_menu->display().'
				</div>
				<div class="clear"></div>
				<div id="major_input" class="row '.$error_major.'">
				    <label class="fieldname" for="major">Major <span class="require1">*</span>
				    </label>
				    <input id="major_val" class="textarea" placeholder="major" name="major_val" type="text" value="'.$person->major.'">
				</div>
				<div class="clear"></div>
				<div class="row '.$error_level.'">
				    <label class="fieldname" for="level">
				        Level
				        <span class="require1">*</span>
				    </label>
				    '.$level_menu->display().'
				</div>
				<div class="clear"></div>
				<div class="row">
				    <label class="fieldname" for="opt">
				        Receive Emails?
				        <span class="require1">*</span>
				    </label>
				    <input class="right" name="opt" type="checkbox" value="1" CHECKED>
				</div>
				<div class="clear"></div>
				<div class="separator"></div>
				<div class="clear"></div>
				<div class="disclaimer">
				    Barcodes can be entered in at a later time if you have not been issued a barcode yet.
				</div>
				<div class="clear"></div>
				<div class="row">
					<img src="images/wristband.png" class="wristband">
				</div>
				<div class="clear"></div>
				<div class="row '.$error_barcode.'">
					<label class="fieldname" for="barcode">
					Barcode
					</label>
					<input id="barcode" type="text" class="textarea" name="barcode" autofocus"> 
				</div>
				<div class="clear"></div>
				<div class="separator"></div>
				<div class="disclaimer">
				    In order to attend E-Week Events and win prizes, each participant will need to register for '.PRODUCT.', an electronic method for tracking participants. 
				    <!--By clicking Register or using '.PRODUCT.', you are indicating that you have read, understood, and agreed to '.PRODUCT.'\'s 
				    <a target="_blank" href="/content/terms_of_service/">Terms of Use</a> and 
				    <a target="_blank" href="/content/privacy_policy/">Privacy Policy</a>.-->
				</div>
				<div class="clear"></div>
				<div class="row">
				    <input type="submit" value="Register" name="submit_signup">
				</div>
				<div class="clear"></div>
			</form>
			<script>
			
			$("#major").change(function () {
					  if($("#major").val() == "Other")
					  {
					  	$("#major_val").val("'.$person->major.'");
			          	$("#major_input").slideDown("fast");
			          }
			          else{
			          	$("#major_val").val($("#major").val());
			          }
			          });
			</script>';
					
	}
	else
	{
		$page->setMessage($error_message[0],'failure');
	}
}
			
$bottom = $search.$signup.$intro_scripts;

$box = new Box('Register', $bottom);
$box->setBadge('Sign in', 'login.php');

$page->setContent($box->display('half'));
$page->buildPage();
?>


