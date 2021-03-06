<?php

require_once 'inc/standard.php';
$page = new Page('admin', ADMINISTRATOR);
$page->setTab('admin');
$user_box = new Box('User Administration');
$barcode_box = new Box('Barcode Administration');
$event_box = new Box('Event Administration');
$danger_box = new Box('Danger');
$js_slide_down = false;
$barcode_focus = false;
$select_from_post = false;
$var_array = new VarArray();

//Intro JS Stuff for tutorial purposes
$user_box->setBadge("<a class='startButton' href='#intro'>Start Tutorial</a>");
$user_box->setIntroStep(1);
$user_box->setIntroText("This section is used for user administration. 
	You will mostly use it for changing a person's access level. 
	If you need to make a <b>Participant</b> into a <b>Volunteer</b>, 
	choose 'Participant' from the dropdown menu, then find their name 
	in the second dropdown. Lastly, change their <strong>Access Level</strong> 
	to <b>Volunteer</b> and push submit. If you cannot find their name on the list
	that means they aren't registered, and they will need to go to the 
	<a href='register.php'>Registration Page</a> to register for an account.");

$barcode_box->setIntroStep(2);
$barcode_box->setIntroText("This section is used for barcode administration. 
	This is where you will need to register the barcodes before you can use them. 
	Have a few volunteers scan in all of the barcodes before they are distributed. 
	The scan ticker will show the last 5 barcodes that were scanned in, 
	and if they are registered to anyone, you can click on the barcode for 
	more information.");

$event_box->setIntroStep(3);
$event_box->setIntroText("This will be the first step to setting up ESCan 
	for E-week. Select the week in which E-week takes place. You will need 
		to do this from a desktop computer. Once you've selected the date, 
	make sure to double check the calendar to ensure you chose the right week.
	the 'EWEEK' icon should fall on the President's Day Monday");

$danger_box->setIntroStep(4);
$danger_box->setIntroText("This section is used to clean the database for ESCan. Make sure 
	to backup the previous year's data before deleting any data. This will
	permanently delete any data from the database. If you need to delete
	everything, click the reinstall button which will run all of the commands
	to delete, barcodes, events, users and scans");

//check for valid access
if(!$page->login->checkValidAccess($page, $_SERVER['PHP_SELF']))
{
	$box = new Box('Access Denied', 'You do not have access to view this page');
	$page->setContent($box->display());
	$page->buildPage();
}

//clean each $_POST value of dangerous inputs
//example $user['email'] = 'stburke@uci.edu';
foreach ($_POST as $key => $value) {
	//echo '$user[\''.$key.'\'] = '.$value.';<br>';
	$user[$key] = trim(strip_tags($value));
}
$user['ucinetid'] = trim(strip_tags($_GET['ucinetid']));

$access_menu_select = new DropMenu('select_access', $var_array->getAccess(true), $_GET['select_access'], 'textarea');

//TODO finish this
$bottom = '
<form id="form_access" action="'.$_SERVER['PHP_SELF'].'" method="GET">
		<div class="row">
			<label class="fieldname" for="users">
			Access
			</label>
			'.$access_menu_select->display().'
		</div>
</form>
<script>
	$("#select_access").change(function () {
	          $("#form_access").submit();
	          });
</script>';


if($_GET['select_access'])
{
	$errors = 1;
	
	if(($_GET['select_access'] != PARTICIPANT) && 
	($_GET['select_access'] != VOLUNTEER) && 
	($_GET['select_access'] != ADMINISTRATOR) && 
	($_GET['select_access'] != WEBMASTER))
	{
		$errors++;
		$error_message[$errors] = 'Invalid Access Type';
	}
	
	if($errors == 1)
	{
	$sql = 'SELECT u.name, u.ucinetid 
			FROM users AS u
			WHERE access = "'.$_GET['select_access'].'"
			ORDER BY name ASC';
	
	$DB->query($sql);
	$users = $DB->resultToMakeArray('ucinetid','name', 'Select User');
	$user_menu = new DropMenu('ucinetid', $users, $_GET['ucinetid'], 'textarea');
	
	$bottom .= ' 
	<form id="ucinetid_form" action="'.$_SERVER['PHP_SELF'].'#" method="GET">
			<div class="row">
				<label class="fieldname" for="users">
				Users
				</label>
				'.$user_menu->display().'
			</div>
			<input name="select_access" type="hidden" value="'.$_GET['select_access'].'">
	</form>
	<script>
		$("#ucinetid").change(function () {
		          $("#ucinetid_form").submit();
		          });
	</script>';
	}
	else {
		$page->setMessage($error_message, 'failure');
	}
}

if($_GET['action'] == 'associate' && $_POST)
{
	$barcode = new Barcode($user['barcode']);
	if(strlen($barcode->code) > 0)
	{
		if($barcode->associate($user['ucinetid']))
		{
			$page->setMessage('You have successfully associated barcode #'.$barcode->code.' to '.$user['ucinetid'].'\'s account', 'success');
		}
		else
		{
			$page->setMessage($barcode->error, 'failure');
		}
	}
	else 
	{
		if($barcode->unassociate($user['ucinetid']))
		{
			$page->setMessage('You have successfully unassociated any barcode from this account', 'success');
			unset($user['barcode']);
		}
		else
		{
			$page->setMessage($barcode->error, 'failure');
		}
	}
	
}

if($_GET['action'] == 'Set' && $_GET['eweekstart'])
{
	$errors = 1;
	if(!strtotime($_GET['eweekstart']))
	{
		echo date('Y-m-d h:i:s', strtotime($_GET['eweekstart']));
		$error_message[$errors] = "This is not a valid date";
		$errors++;
		$error_name = 'failure';
	}
	if($errors === 1){
		$sql = "UPDATE settings SET value='".$_GET['eweekstart']."' WHERE name='eweekstart';";
		$sql = "INSERT INTO settings (name, value) VALUES('eweekstart', '".$_GET['eweekstart']."') ON DUPLICATE KEY UPDATE    
name='eweekstart', value='".$_GET['eweekstart']."';";
		$DB->execute($sql);
		$page->setMessage('The Monday of E-Week is: <strong>'.date('M d, Y', strtotime($_GET['eweekstart'])).'</strong>. Please update the events on the <a href="events.php">events page</a> accordingly.', 'success');
	}

}


if($_GET['action'] == 'update' && $_POST)
{
	$errors = 1;
	
	if(strlen($user['name']) < 2)
	{
		$error_message[$errors] = "This name is too short";
		$errors++;
		$error_name = 'failure';
	}
	if($user['major'] == '')
	{
		$error_message[$errors] = "Please select a major";
		$errors++;
		$error_major = 'failure';
	}
	if($user['level'] == '')
	{
		$error_message[$errors] = "Please select a level";
		$errors++;
		$error_level = 'failure';
	}
	
	if(substr($user['email'], 0, strlen($user['ucinetid'])) != $user['ucinetid'])
	{
		$error_message[$errors] = "The email must match the UCInetID";
		$errors++;
		$error_email = 'failure';
	}
	
	if(substr($user['email'], strlen($user['ucinetid']), 8) != '@uci.edu')
	{
		$error_message[$errors] = "You must use the UCI email address";
		$errors++;
		$error_email = 'failure';
	}
	
	if((strlen($user['password_new']) != 0) || 
	   (strlen($user['password_con']) != 0))
	{

		if(strlen($user['password_new']) < 6)
		{
			$errors++;
			$error_message[$errors] = 'Your password must be 6 characters or greater';
			$error_password_new = 'error';
		}
		
		if($user['password_new'] != $user['password_con'])
		{
			$errors++;
			$error_message[$errors] = 'Your passwords do not match';
			$error_password_con = 'error';
		}
	}
	
	if($errors == 1)
	{
		$password_new = md5($user['password_new']);
		
		if((strlen($user['password_new']) != 0) && 
		   (strlen($user['password_con']) != 0))
		{
			$sql = 'UPDATE logon 
					SET password = "'.$password_new.'"
					WHERE ucinetid = "'.$user['ucinetid'].'"';
					
			$page->login->qry($sql);
		}
		
		$sql = 'UPDATE users SET
				name = "'.$user['name'].'",
				access = "'.$user['access'].'",
				major = "'.$user['major'].'",
				level = "'.$user['level'].'",
				opt = "'.$user['opt'].'"
				WHERE ucinetid = "'.$user['ucinetid'].'"
				LIMIT 1';
		
		$DB->execute($sql);
		$page->setMessage('Settings updated for user: <strong>'.$user['ucinetid'].'</strong>', 'success');	
		$select_from_post = false;	
	}
	else
	{
		$page->setMessage($error_message, 'failure');
		$select_from_post = true;	
	}

}

if($_GET['action'] == "DELETE EVENTS" || $_GET['action'] == "REINSTALL")
{
	$errors = 1;
	
	if($_GET['delete_continue'] != "DELETE")
	{
		$errors++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
			Are you sure you want to delete all events?
			<input type="submit" value="DELETE" name="delete_continue">
			<input type="hidden" value="DELETE EVENTS" name="action">
		</form>';
	}
	
	if($errors == 1)
	{
		
		$sql = 'TRUNCATE TABLE events';
		
		$DB->execute($sql);
		$page->setMessage('All events have been deleted', 'failure');	
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}

}

/*
 * Why did I implement this feature in the first place??
 *
 
if($_GET['action'] == "DELETE PAGES")
{
	$errors = 1;
	
	if($_GET['delete_continue'] != "DELETE")
	{
		$errors++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
			Are you sure you want to delete all pages?
			<input type="submit" value="DELETE" name="delete_continue">
			<input type="hidden" value="DELETE PAGES" name="action">
		</form>';
	}
	
	if($errors == 1)
	{
		$sql = 'TRUNCATE TABLE pages';
		$DB->execute($sql);
		$sql = 'TRUNCATE TABLE tabs';
		$DB->execute($sql);
		
		$page->setMessage('All pages have been deleted', 'failure');	
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}*/

if($_GET['action'] == "DELETE USERS"  || $_GET['action'] == "REINSTALL")
{
	$errors = 1;
	
	if($_GET['delete_continue'] != "DELETE")
	{
		$errors++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
			Are you sure you want to delete all users?
			<input type="submit" value="DELETE" name="delete_continue">
			<input type="hidden" value="DELETE USERS" name="action">
		</form>';
	}
	
	if($errors == 1)
	{
	    //delete all but webmasters
	    
		$sql = 'DELETE FROM users WHERE access != '.WEBMASTER.';';
		$DB->execute($sql);
		$sql = 'DELETE FROM logon WHERE ucinetid NOT IN (SELECT users.ucinetid FROM users)'; 
		$DB->execute($sql);
		$sql = 'TRUNCATE TABLE reset';
		$DB->execute($sql);
			
		$page->setMessage('All users (except Webmasters) have been deleted', 'failure');
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}

if($_GET['action'] == "DELETE SCANS"  || $_GET['action'] == "REINSTALL")
{
	$errors = 1;
	
	if($_GET['delete_continue'] != "DELETE")
	{
		$errors++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
			Are you sure you want to delete all scans?
			<input type="submit" value="DELETE" name="delete_continue">
			<input type="hidden" value="DELETE SCANS" name="action">
		</form>';
	}
	
	if($errors == 1)
	{
		
		$sql = 'TRUNCATE TABLE scans'; 
		$DB->execute($sql);
			
		$page->setMessage('All scans have been deleted', 'failure');
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}


if($_GET['action'] == "DELETE BARCODES"  || $_GET['action'] == "REINSTALL")
{
	$errors = 1;
	
	if($_GET['delete_continue'] != "DELETE")
	{
		$errors++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
			Are you sure you want to delete all barcodes?
			<input type="submit" value="DELETE" name="delete_continue">
			<input type="hidden" value="DELETE BARCODES" name="action">
		</form>';
	}
	
	if($errors == 1)
	{
		
		$sql = 'TRUNCATE TABLE barcodes'; 
		$DB->execute($sql);
		$sql = 'UPDATE users SET barcode=""';
		$DB->execute($sql);
		$page->setMessage('All barcodes have been deleted', 'failure');
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}

if($_GET['action'] == "REINSTALL")
{
    $errors = 1;
	
	if($_GET['delete_continue'] != "DELETE")
	{
		$errors++;
		$error_message[0] = '
		<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="GET">
			Are you sure you want to reinstall ESCan? This will delete EVERYTHING!
			<input type="submit" value="DELETE" name="delete_continue">
			<input type="hidden" value="REINSTALL" name="action">
		</form>';
	}
	
	if($errors == 1)
	{
		$page->setMessage('ESCan has been reinstalled', 'failure');
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
	
}



	
if($user['ucinetid'])
{
	$errors = 1;
	
	if(!($page->login->exists($user['ucinetid'])))
	{
		$errors++;
		$error_message[$errors] = $user['ucinetid'].'is not registered with '.PRODUCT.'. Please register here at the <a href="register.php">Registration Page</a>';
	}
	
	if($errors == 1)
	{
	$sql = 'SELECT u.*
			FROM users AS u
			WHERE ucinetid = "'.$user['ucinetid'].'"
			ORDER BY name ASC';
	
	$DB->query($sql);
	$user = $DB->resultToSingleArray();
	
	//clean each $_POST value of dangerous inputs
	//example $user['email'] = 'stburke@uci.edu';
	foreach ($_POST as $key => $value) {
		//echo '$user[\''.$key.'\'] = '.$value.';<br>';
		$update[$key] = trim(strip_tags($value));
	}
	
	foreach ($user as $key => $value) {
		$display[$key] = ($select_from_post) ? $update[$key]:$user[$key];
		$class[$key] = ($select_from_post) ? 'success':'';
	}
	$display['barcode'] = ($select_from_post) ? '':$user['barcode'] ;
	
		$bottom .= '
		<div class="separator"></div>
		<form action="'.$_SERVER['PHP_SELF'].'?ucinetid='.$_GET['ucinetid'].'&select_access='.$_GET['select_access'].'&action=update" method="POST">
			<div class="row">
			    <label class="fieldname" for="ucinetid">
			        UCInetID
			    </label>
			    <input class="textarea readonly" name="ucinetid" type="text" value="'.$display['ucinetid'].'" READONLY>
			</div>
			<div class="row '.$class['email'].'">
			    <label class="fieldname" for="email">
			        Email
			    </label>
			    <input class="textarea" name="email" type="email" value="'.$display['email'].'">
			</div>
			<div class="row '.$class['name'].'">
			    <label class="fieldname" for="name">
			        Name
			    </label>
			    <input class="textarea" name="name" type="text" value="'.$display['name'].'">
			</div>';
			//echo 'display	:'.$display['access'].'<br>';
			//echo 'POST		:'.$_POST['access'].'<br>';
			//echo 'user		:'.$user['access'].'<br>';
			//echo 'update	:'.$update['access'].'<br>';
		$access_update = new DropMenu('access', $var_array->getAccess('setIntial'), $display['access'], 'textarea');
		$major_menu = new DropMenu('major', $var_array->getMajors('setIntial'), $display['major'], 'textarea');
		$level_menu = new DropMenu('level', $var_array->getLevels('setIntial'), $display['level'], 'textarea');
		
		$bottom .= '
			<div class="row '.$class['access'].'">
			    <label class="fieldname" for="name">
			        Access
			    </label>
			    '.$access_update->display().'
			</div>
			<div class="row '.$class['major'].'">
			    <label class="fieldname" for="major">
			    Major
			    </label>'.$major_menu->display().'
			</div>
			<div id="major_input" class="row '.$class['major'].'">
			    <label class="fieldname" for="major">Major <span class="require1">*</span>
			    </label>
			    <input id="major_val" class="textarea" placeholder="major" name="major_val" type="text" value="'.$display['major'].'">
			</div>
			<div class="row '.$class['level'].'">
			    <label class="fieldname" for="level">
			        Level
			    </label>
			    '.$level_menu->display().'
			</div>';
			    
			$opt_checked = ($display['opt'] == '') ? '':'CHECKED';
			    
			$bottom .= '
			<div class="row  '.$class['opt'].'">
			    <label class="fieldname" for="opt">
			        Receive Emails?
		    	</label><input name="opt" type="checkbox" class="right" '.$opt_checked.'>
		    </div>
		    <div class="separator"></div>
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
			    <input type="submit" value="Update" name="action">
			</div>
		</form>
		<div class="separator"></div>
		<div class="row">
			<img src="images/wristband.png" class="wristband">
		</div>
		<form action="'.$_SERVER['PHP_SELF'].'?ucinetid='.$_GET['ucinetid'].'&select_access='.$_GET['select_access'].'&action=associate" method="POST">
		
			<div class="row" '.$class['barcode'].'>
			   <label class="fieldname" for="barcode">
			       Barcode
			   </label>
			   <input class="textarea" name="barcode" type="text" value="'.$display['barcode'].'">
			</div>
			<div class="row">
			    <input type="submit" value="Associate" name="action">
			</div>
		</form>
		<script>
		$(document).ready( function(){
			if($("#major").val() != "Other")
			{
				$("#major_input").hide();
			}
		});
		
		$("#major").change(function () {
				  if($("#major").val() == "Other")
				  {
				  	$("#major_val").val("'.$display['major'].'");
		          	$("#major_input").slideDown("fast");
		          }
		          else{
		          	$("#major_val").val($("#major").val());
		          	$("#major_input").slideUp("fast");
		          }
		          });
		</script>';
	
	}
	else {
		$page->setMessage($error_message, 'failure');
	}
}

/* Barcode Section
 *
 *
 *
 */

$ticker_content .=	' <div class=" ">
			<form id="barcode-form" method="GET">
				<div class="row">
					<label>Barcode</label>
					<input type="text" name="barcode" id="barcode" class="textarea">
				</div>
				<div class="row">
					<input type="submit" name="scan" value="Scan" class="right">
				</div>
			</form>
		</div>';
$limit = ($_GET['view'] == 'all') ? '':'LIMIT 5';
		
$sql = 'SELECT barcodes.barcode, scans.*, users.name, users.ucinetid, users.major, users.level
    		FROM users 
    		LEFT JOIN barcodes
    		    LEFT JOIN scans
    		    ON scans.barcode = barcodes.barcode
    		ON barcodes.ucinetid = users.ucinetid
    		ORDER BY scans.date DESC, scans.time DESC
    		'.$limit;
		
$DB->query($sql);
$ticker_array = $DB->resultToArray();
$ticker_content .= '<div class="separator"></div>';
$ticker_content .= '<div id="ticker" class="ticker"></div>';

/* Start doing the JavaScript 
 *
 */
	
//set javascript after page is ready
$page->setJSInitial('
		$("#barcode").focus();
		loadBarcodes()
		');
	
$ticker_content .= '

<script>
	
    $("#barcode-form").on("submit", function(e) {
        e.preventDefault();  //prevent form from submitting
        var code = $("#barcode").val();
    	$.ajax({
          type: "GET",
          url: "barcode_register.php",
          dataType: "json",
          data: { 
            barcode: code,
            ucinetid: "'.$_SESSION['ucinetid'].'"},
          success: function(data) {
            if(data.message.status == "success")
            {
                appendBarcode(data.scan[0]);
            }
            setMessage(data.message.text, data.message.status);
          }
	    });
	    $("#barcode").val(" ");
	    $("#barcode").focus();
	    
    });
    </script>';


   /* 
    *
    *    Event Section
    *
    *
    */

$sql = 'SELECT value
		FROM settings
		WHERE name = "eweekstart"';
	
$DB->query($sql);
$eweekstart = $DB->resultToSingleArray();

$event_content .= ' 
		<div class=" ">
			<form id="event-form" name="event-form" method="GET">
				<div class="row">
					<label>When is E-Week?</label>
					<input type="week" name="eweekstart" id="eweekstart" class="textarea" value="'.$eweekstart[0].'">
				</div>
				<div class="row">
					<input type="submit" name="action" value="Set" class="right">
				</div>
			</form>

			<div class="disclaimer">
			Not sure? Check out the official E-Week dates at <a href="http://www.discovere.org/our-programs/engineers-week">http://www.discovere.org/</a>
			</div>
		</div>


		<div class="calendar">
			<div class="month"><span class="month-title">'.date('F Y', strtotime($eweekstart[0])).'</span></div>
			<div class="week">
				<div class="day dayofweek">'.date('D', strtotime($eweekstart[0].' -8 days')).'</div>
				<div class="day dayofweek">'.date('D', strtotime($eweekstart[0].' -7 days')).'</div>
				<div class="day dayofweek">'.date('D', strtotime($eweekstart[0].' -6 days')).'</div>
				<div class="day dayofweek">'.date('D', strtotime($eweekstart[0].' -5 days')).'</div>
				<div class="day dayofweek">'.date('D', strtotime($eweekstart[0].' -4 days')).'</div>
				<div class="day dayofweek">'.date('D', strtotime($eweekstart[0].' -3 days')).'</div>
				<div class="day dayofweek last">'.date('D', strtotime($eweekstart[0].' -2 days')).'</div>
			</div>
			<div class="week">
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' -8 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' -7 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' -6 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' -5 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' -4 days')).'</div>
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' -3 days')).'</div>
				<div class="last day eweek">'.date('n/d', strtotime($eweekstart[0].' -2 days')).'</div>
			</div>
			<div class="week">
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' -1 days')).'</div>
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' +0 days')).' <div class="eweekstamp">eweek</div></div>
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' +1 days')).'</div>
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' +2 days')).'</div>
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' +3 days')).'</div>
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' +4 days')).'</div>
				<div class="last day eweek">'.date('n/d', strtotime($eweekstart[0].' +5 days')).'</div>
			</div>
			<div class="last week">
				<div class="day eweek">'.date('n/d', strtotime($eweekstart[0].' +6 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' +7 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' +8 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' +9 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' +10 days')).'</div>
				<div class="day">'.date('n/d', strtotime($eweekstart[0].' +11 days')).'</div>
				<div class="last day">'.date('n/d', strtotime($eweekstart[0].' +12 days')).'</div>
			</div>
		</div>';


//$slot = new Vegas($page,$users);
//$slot_content = $slot->build();

$danger_content .= '

<form id="truncate_events" action="'.$_SERVER['PHP_SELF'].'" method="GET">
	<input type="submit" name="action" value="DELETE EVENTS">
	<div class="clear"></div>
	<input type="submit" name="action" value="DELETE USERS">
	<div class="clear"></div>
	<input type="submit" name="action" value="DELETE BARCODES">
	<div class="clear"></div>
	<input type="submit" name="action" value="DELETE SCANS">
	<div class="clear"></div>
	<input type="submit" name="action" value="REINSTALL">
	<div class="clear"></div>
</form>';


$user_box->setContent($bottom.$slot_content);
$barcode_box->setContent($ticker_content);
$event_box->setContent($event_content);
$danger_box->setContent($danger_content);


$content = '<div id="container_wrapper">'.
				$user_box->display('full').
				$barcode_box->display('full').
				$event_box->display('full').
				$danger_box->display('full').
			'</div>';

$endjs = '<script src="js/intro.min.js"></script>
		<script type="text/javascript">
			if(window.location.hash) {
				var hash = window.location.hash.substring(1);
				if(hash == "intro"){
					introJs().setOption("doneLabel", "Next page").start().oncomplete(function() {
					window.location.href = "register.php#intro";
				});
				}
			}
			$(".startButton").on("click", function() {
				introJs().setOption("doneLabel", "Next page").start().oncomplete(function() {
					window.location.href = "register.php#intro";
				});
			});
		</script>';

$page->setContent($content.$endjs);
$page->buildPage();

?>