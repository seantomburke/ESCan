<?php

require_once 'inc/standard.php';
$page = new Page('admin', ADMINISTRATOR);
$page->setTab('admin');
$user_box = new Box('User Administration');
$barcode_box = new Box('Barcode Administration');
$danger_box = new Box('Danger');
$js_slide_down = false;
$barcode_focus = false;
$select_from_post = false;
$var_array = new VarArray();

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
	
	$page->DB->query($sql);
	$users = $page->DB->resultToMakeArray('ucinetid','name', 'Select User');
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
		
		$page->DB->execute($sql);
		$page->setMessage('Settings updated for user: <strong>'.$user['ucinetid'].'</strong>', 'success');	
		$select_from_post = false;	
	}
	else
	{
		$page->setMessage($error_message, 'failure');
		$select_from_post = true;	
	}

}

if($_GET['action'] == "DELETE EVENTS")
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
		
		$page->DB->execute($sql);
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
		$page->DB->execute($sql);
		$sql = 'TRUNCATE TABLE tabs';
		$page->DB->execute($sql);
		
		$page->setMessage('All pages have been deleted', 'failure');	
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}*/

if($_GET['action'] == "DELETE USERS")
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
		$page->DB->execute($sql);
		$sql = 'DELETE FROM logon WHERE ucinetid NOT IN (SELECT users.ucinetid FROM users)'; 
		$page->DB->execute($sql);
		$sql = 'TRUNCATE TABLE reset';
		$page->DB->execute($sql);
			
		$page->setMessage('All users (except Webmasters) have been deleted', 'failure');
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}

if($_GET['action'] == "DELETE SCANS")
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
		$page->DB->execute($sql);
			
		$page->setMessage('All scans have been deleted', 'failure');
	}
	else
	{
		$page->setMessage($error_message, 'error');
	}
}

if($_GET['action'] == "DELETE BARCODES")
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
		$page->DB->execute($sql);
		$sql = 'UPDATE users SET barcode=""';
		$page->DB->execute($sql);
		$page->setMessage('All barcodes have been deleted', 'failure');
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
	
	$page->DB->query($sql);
	$user = $page->DB->resultToSingleArray();
	
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
			<div class="separator"></div>
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
		
$page->DB->query($sql);
$ticker_array = $page->DB->resultToArray();
$ticker_content .= '<div class="separator"></div>';
$ticker_content .= '<div id="ticker" class="ticker"></div>';

/* Start doing the JavaScript 
 *
 */
	
//set javascript after page is ready
$page->setJSInitial('
		$("#barcode").focus();
		loadBarcodes();
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
$danger_box->setContent($danger_content);


$content = '<div id="container_wrapper">'.
				$user_box->display('full')
				.'<div class="separator"></div>'.
				$barcode_box->display('full')
				.'<div class="separator"></div>'.
				$danger_box->display('full')
			.'</div>';

$page->setContent($content);
$page->buildPage();

?>