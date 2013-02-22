<?php
require_once 'inc/standard.php';

$page = new Page('scan', VOLUNTEER);
$page->setTitle('Scan');
$page->startGraph(); //adds the jQuery Sparkline to the header

//check user login
if(!$page->login->checkValidAccess($page, $_SERVER['PHP_SELF']))
{
	$box = new Box('Access Denied', 'You do not have access to view this page');
	$page->setContent($box->display('half'));
	$page->buildPage();
}

//clean each $_POST value of dangerous inputs
//example $newsettings['email'] = 'stburke@uci.edu';
foreach ($_POST as $key => $value) {
	//echo '$newsettings[\''.$key.'\'] = '.$value.';<br>';
	$scan[$key] = trim(strip_tags($value));
}

$scan['barcode'] = trim(strip_tags($_GET['barcode']));

//add the Event ID to the $scan array
if($_GET['eid'])
	$scan['eid'] = trim(strip_tags($_GET['eid']));
elseif($_COOKIE['eid'])
	$scan['eid'] = trim(strip_tags($_COOKIE['eid']));
	
$ucinetid = ($_POST['ucinetid'])? $_POST['ucinetid']:$_GET['ucinetid'];

/* make sure the slide animation does not trigger, 
 * may confuse Volunteer who is scanning that a scan went through
 * when there was really an error.
 */
$js_slide_down = false;

$event = new Event($scan['eid']);
setcookie('eid', $scan['eid'], time() + (6*60*60)); //expires in 6 hours
if(!$event->exists())
{
	$page->setMessage('Please Select an Event Before Scanning', 'failure');
	$error_class_event_menu = 'error';
}

if($_GET['barcode'])
{
	$barcode = new Barcode($scan['barcode']);
	$scanner = new Scanner();
	$errors = 1;
	
	if(!$event->exists())
	{
		$errors++;
		$error_message[$errors] = 'This event does not exist';
		$error_class_event_menu = 'error';
	}
	
	if(!$barcode->exists())
	{
		$errors++;
		$error_message[$errors] = 'The barcode <strong>#'.$barcode->code.'</strong> does not exist. Try scanning again.';
		$error_class_barcode = 'error';
	}
	
	if($scanner->exists($barcode->code, $scan['eid']))
	{
		$errors++;
		$form = '<form action="register.php" method="GET"><input type="text" name="ucinetid" placeholder="UCInetID"><input type="submit"></form>'; //todo associate barcode if the took one with out registering.
		$name = ($barcode->getName()) ? 'The user <strong>'.$barcode->getName().'</strong>':'The barcode <strong>#'.$barcode->code.'</strong>';
		$extra = ($barcode->getName()) ? 'Welcome.':'Please register this user\'s UCInetID below: '.$form;
		$error_message[$errors] = $name.' has already been scanned. '.$extra;
		$error_class_barcode = 'error';
	}
		
	if($errors == 1)
	{
		$scanner->scan($barcode->code, $scan['eid'], $_SESSION['ucinetid']);
		$form = '<form action="register.php" method="GET"><input type="text" name="ucinetid" placeholder="UCInetID"><input type="submit"></form>'; //todo associate barcode if the took one with out registering.
		$name = ($barcode->getName()) ? 'The user <strong>'.$barcode->getName().'</strong>':'The barcode <strong>#'.$barcode->code.'</strong>';
		$extra = ($barcode->getName()) ? 'Welcome.':'Please register this user\'s UCInetID below: '.$form;
		$message = $name.' has been scanned in'.$welcome;
		$page->setMessage($message, 'success');
		$js_slide_down = true;
	}
	else
	{
		$page->setMessage($error_message, 'failure');
	}
}
$sql = 'SELECT e.name, e.eid
		FROM events AS e
		ORDER BY date ASC, time ASC';
$page->DB->query($sql);

$event_menu_array = $page->DB->resultToMakeArray('eid','name', 'Select Event');

$event_menu = new DropMenu('eid', $event_menu_array, $scan['eid'], 'textarea');

//echo 'time: '.strtotime($event->date.' '.$event->time).' > '. strtotime(NOW_DATE.' '.NOW_TIME);
if($scan['eid'] != 0)
{
	if(strtotime($event->date.' '.$event->time) > strtotime(NOW_DATE.' '.NOW_TIME))
	{
			
		$bottom .= ' <div class="row error">
						Event doesn\'t start until '.date('h:i A', strtotime($event->time)).' on '.date('l, M d', strtotime($event->date)).'
					</div>';
	}
	elseif((strtotime($event->date.' '.$event->time) + time(12*60*60)) < strtotime(NOW_DATE.' '.NOW_TIME))
	{
		$bottom .= '<div class="row error">
						This event is over.
					</div>';
	}
	elseif(strtotime($event->date) == strtotime(NOW_DATE))
	{
		$bottom .= '<div class="row success">
					This event has started!
				</div>';
	}
}
$total_count = $page->DB->countOf('scans', 'eid = "'.$_GET['eid'].'"');

$bottom .= ' 
		<form id="select_event" action="'.$_SERVER['PHP_SELF'].'" method="GET">
			<div class="row '.$error_class_event_menu.'">
				<label for="eid" class="fieldname">Event</label>'
				.$event_menu->display().'
			</div>
		</form>
		<script>
			$("#eid").change(function () {
			          $("#select_event").submit();
			          });
		</script>';
		if($scan['eid'] != 0)
		{
		
		$bottom .= ' <div class="row">
			<label class="fieldname">Date</label>
			<div class="textarea">
			'.date('l, M d, Y',strtotime($event->date)).'
			</div>
		</div>
		<div class="row">
			<label class="fieldname">Time</label>
			<div class="textarea">
			'.date('g:i a',strtotime($event->time)).'
			</div>
		</div>
		<div class="row">
			<label class="fieldname">Host</label>
			<div class="textarea">
			'.$event->host.'
			</div>
		</div>
		<div class="row">
			<label class="fieldname">Prize</label>
			<div class="textarea">
			'.$event->prize.'
			</div>
		</div>
		<div class="row">
			<label class="fieldname">Description</label>
			<div class="textarea">
			'.$event->description.'
			</div>
		</div>
		<div class="row">
			<label class="fieldname">Total Participants</label>
			<div class="textarea">
			'.$total_count.'
			</div>
		</div>';
	}
	$bottom .=	' <div class=" ">
			<div class="separator"></div>
			<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
				<div class="row">
					<label>Barcode</label>
					<input type="text" name="barcode" id="barcode" class="textarea">
					<input type="hidden" name="eid" value="'.$scan['eid'].'">
				</div>
				<div class="row">
					<input type="submit" name="action" value="Scan" class="right">
				</div>
			</form>
		</div>';
$limit = ($_GET['view'] == 'all') ? '':'LIMIT 5';

$sql = 'SELECT scans.*, users.name, users.ucinetid, users.major, users.level
		FROM scans LEFT JOIN users
		ON scans.barcode = users.barcode
		WHERE scans.eid = "'.$scan['eid'].'"
		ORDER BY date DESC, time DESC
		'.$limit;
		
$page->DB->query($sql);
$ticker_array = $page->DB->resultToArray();
$ticker_content .= '<div class="separator"></div>';
$ticker_content .= '<div class="row center">
						<h3>Scan Ticker</h3>
					</div>';
					
$ticker_content .= '<div class="list">';

/* Start doing the JavaScript 
 *
 */
 
//set initial javascript
if($js_slide_down)
	$js_hide_row1 = '$("#row1").hide();';
$page->setJSInitial('$(".dropdown").hide(); '.$js_hide_row1);
	
//set javascript after page is ready
$ticker_content .= '<script>
	$(document).ready(function () {
		$("#barcode").focus();';
if($js_slide_down)
 $ticker_content .= '$("#row1").slideDown();';

$ticker_content .= '});</script>';
	
if(count($ticker_array) > 0)
{
	
	foreach ($ticker_array as $row)
	{
		$i++;
		
		$ticker_content .= '
			<div class="item" id="row'.$i.'">
				<div class="row">
					<label class="barcode">Barcode: </span> #'.$row['barcode'].'</label>
					<span class="right">'.$row['name'].'</span>
					<span class="date">'.date('M j,', strtotime($row['date'])).'</span>
					<span class="time">'.date('g:i:s A', strtotime($row['time'])).'</span>
				</div>
			</div>
			<div class="event_row">
				<script>
				    $("#row'.$i.'").click(function () {
				    	$("#drop'.$i.'").slideToggle("fast");
				    });
				</script>
				<div class="dropdown" id="drop'.$i.'" style="display:none;">
					<h5><strong>UCInetID:</strong> 	'.$row['ucinetid'].'</h5>
					<h5><strong>Name:</strong> 		'.$row['name'].'</h5>
					<h5><strong>Major:</strong> 	'.$row['major'].'</h5>
					<h5><strong>Level:</strong> 	'.$row['level'].'</h5>
					<h5><strong>Time:</strong>		'.date('M j, Y,', strtotime($row['date'])).' '.date('g:i:s A', strtotime($row['time'])).'</h5>
				</div>
			</div>';
	}
	
	//view all link at the bottom of page
	if($_GET['view'] == 'all')
	{
	$ticker_content .= '
		<div class="row center">
		<label id="view"><a href="'.$_SERVER['PHP_SELF'].'?view=less#view">View Less Scans</a></label>
		</div>';
	}
	else 
	{
	$ticker_content .= '
		<div class="row center">
		<label id="view"><a href="'.$_SERVER['PHP_SELF'].'?view=all#view">View All Scans</a></label>
		</div>';
	}
}
else {
	$ticker_content .= '
	<div class="list">
	<div class="row">
		<label>No Scans Yet</label>
	</div>
	</div>';
}
$ticker_content .= '</div>';


/*
 * 		start Statistics display 
 *
 *  
 */
 
$box = new Box('Scanning for '.$event->name, $bottom.$ticker_content);
$box->setBadge('Return to Events', 'events.php');
$var_array = new VarArray();


foreach ($var_array->getMajors() as $major) {
	$sql = 'SELECT *
			FROM scans
			LEFT JOIN users 
			ON scans.barcode = users.barcode
			WHERE scans.eid = "'.$scan['eid'].'"
			AND users.major = "'.$major.'"';
	$page->DB->query($sql);
	$temp = $page->DB->numRows();
	//echo '<br>value:'.$temp;
	
	$where .= 'major NOT LIKE \''.$major.'\' AND ';
	if($major != "Other")
	{
		$majors[$major] = $temp;
	}
	else
	{
		$where = substr($where, 0, strlen($where)-5);
		$sql = 'SELECT COUNT(*)
				FROM scans
				LEFT JOIN users 
				ON scans.barcode = users.barcode
				WHERE scans.eid = "'.$scan['eid'].'"
				AND '.$where;
		//echo "where: ".$where;
		//$majors["other"] = $page->DB->countOf('users', $where_majors_not_like, 1);
		$majors["Other"] = $page->DB->queryUniqueValue($sql);
	}
	
}

$majors_stat = new Statistic('pie', 'user_majors', 'Registered Users - Major', $majors, 0);

$bottom_stats = $majors_stat->display();

if($scan['eid'] != 0)
{
$box_stat = new Box('Statistics', $bottom_stats);
$stat_display = '<div class="separator"></div>'.$box_stat->display("full");
}
$content = $box->display().$stat_display;

$page->setContent($content);
$page->buildPage();
?>