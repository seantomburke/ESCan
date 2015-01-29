<?php
require_once 'inc/standard.php';

//$page = new Page($name, $css);
$page = new Page('events', ALL);
$var = new VarArray();


$sql = "SELECT eweekstart FROM settings";
$page->DB->query($sql);
$eweekstart = $page->DB->resultToSingleArray();
$eweekfriday = date('Y-m-d', strtotime($eweekstart[0]." -3 days"));
$eweeksunday = date('Y-m-d', strtotime($eweekstart[0]." +6 days"));

//clean each $_POST value of dangerous inputs
//example $newsettings['email'] = 'stburke@uci.edu';
foreach ($_POST as $key => $value) {
	//echo '$newsettings[\''.$key.'\'] = '.$value.';<br>';
	$event[$key] = trim(strip_tags($value));
}

if($_GET['action'] == 'add')
{

	if($_POST['event_submit'] == 'Add Event')
	{
		$errors = 1;
		if(strlen($event['name']) < 2)
		{
			$error_message[$errors] = 'Event must have a name';
			$errors++;
			$error_name = 'error';
		}
		
		if(strlen($event['name']) < 2)
		{
			$error_message[$errors] = 'Event name is too long';
			$errors++;
			$error_name = 'error';
		}
		
		if($event['date'] == '--')
		{
			$error_message[$errors] = 'Event must have a date';
			$errors++;
			$error_date = 'error';
		}
		if(!($event['time'])){

		}
		if($errors == 1)
		{
			$sql = 'INSERT INTO events (name, date, time, host, prize, description, volunteer)
								VALUES ("'.$event['name'].'", "'.$event['date'].'", "'.$event['time'].'", "'.$event['host'].'", "'.$event['prize'].'", "'.$event['description'].'", "'.$_SESSION['ucinetid'].'")';
			
			$page->DB->query($sql);
			$page->setMessage('Event Successfully Added', 'success');
			unset($event);

		}
		else
		{
			if(is_array($error_message))
			$page->setMessage(implode(', ', $error_message), 'failure');
			else
			$page->setMessage($error_message, 'failure');
		}
	}

	if(!$page->login->checkValidAccess($page, $_SERVER['PHP_SELF']))
	{
		$box = new Box('Access Denied', 'You do not have access to view this page');
		$page->setContent($box->display('half'));
		$page->buildPage();
	}
	else
	{

		$box = new Box('Add Event');
		$box->setBadge('Return to Events', 'events.php');
		$bottom = '
					<form action="'.$_SERVER['PHP_SELF'].'?action=add" method="POST">
					<div class="row '.$error_name.'">
						<label class="fieldname">Name</label>
						<input name="name" placeholder="Event Name" type="text" class="textarea" value="'.$event['name'].'">
					</div>
					<div class="row '.$error_date.'">
						<label class="fieldname">Date</label>
						<input name="date" type="date" class="textarea" value="'.$event['date'].'" min="'.$eweekfriday.'" max="'.$eweeksunday.'">
					</div>
					<div class="row '.$error_time.'">
						<label class="fieldname">Time</label>
						<input name="time" type="time" class="textarea" value="'.$event['time'].'">
					</div>
					<div class="row">
						<label class="fieldname">Host</label>
						<input name="host" placeholder="Host of the Event" type="text" class="textarea" value="'.$event['host'].'">
					</div>
					<div class="row">
						<label class="fieldname">Prize</label>
						<input name="prize" placeholder="20" type="number" class="textarea" value="'.$event['prize'].'">
					</div>
					<div class="row">
						<label class="fieldname">Description</label>
						<textarea name="description" placeholder="Description of the Event" rows="5" class="textarea">'.$event['description'].'</textarea>
					</div>
					<div class="row">
						<input name="event_submit" type="submit" value="Add Event">
					</div>
					</form>';
		$box->setContent($bottom);
		$page->setContent($box->display('half'));
		$page->buildPage();
	}
}

if($_GET['action'] == 'Delete')
{
	$delete_event = new Event($_GET['eid']);
	$errors = 1;

	if($_GET['continue_delete'] != "Continue")
	{
		$errors++;
		$message  = '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
						Are you sure you want to delete the event: <strong>'.$delete_event->name.'</strong>
						<input type="submit" name="continue_delete" value="Continue">
						<input type="hidden" name="action" value="Delete">
						<input type="hidden" name="eid" value="'.$_GET['eid'].'"></form>';
		$page->setMessage($message, 'error');
	}
	if(!$delete_event->exists())
	{
		$errors++;
		$message  = 'This event does not exist';
		$page->setMessage($message, 'failure');
	}
	
	if($errors == 1)
	{
		$sql = 'DELETE FROM events WHERE eid = '.$_GET['eid'].';';
		
		$page->DB->query($sql);
		$page->setMessage('<strong>'.$delete_event->name.'</strong> Successfully deleted', 'success');

	}
		
}



$bottom = '<h2>List of Events</h2>';





for($i=0;$i<10;$i++)
{
	//echo date('l', strtotime($key)).' - '.$value.'<br>';
	$keys[$i] = strtotime($eweekfriday."+".$i." days");
	$sql = 'SELECT * FROM events
			WHERE date = "'.date('Y-m-d', $keys[$i]).'"
			ORDER BY date, time';
	$page->DB->query($sql);
	$events[$i] = $page->DB->resultToArray();
}


$bottom .= '
<div class="list">';

$j = 0;


$page->setJSInitial('$(".dropdown").hide();'.
					'$(".dropday").hide();'.
					'$(".today").slideToggle()');	
foreach ($events as $day => $value)
{
	//echo $day.' - '.$value.'<br>';
	
	
	if(date('l') == date('l',$keys[$j]))
	{
		//echo date('l')." - ".date('l',$keys[$j]).'<br>';
		$today = ' today';
	}
	
	$bottom .= ' 
	<div id="day'.$j.'" class="day_class">
		<h3>'.date('l m/d', $keys[$j]).'</h3>
		<script>
			'.$today_script.'
			$("#day'.$j.'").click(
			function () 
			{
				$("#daydrop'.$j.'").slideToggle();
			});
		</script>
	</div>
	
	<div class="clear"></div>
	<div id="daydrop'.$j.'" class="dropday'.$today.'">';
	unset($today);
	$i = 0;
	if(is_array($events[$day]))
	{
		foreach ($events[$day] as $row)
		{
				$i++;
				$bottom .= '
				<div class="item" id="row_'.$j.'_'.$i.'">
					<div class="row">
						<label>'.$row['name'].'</label>
					</div>
					
					<div class="clear"></div>
					
					<div class="row">
						<h5>Time: '.date('g:i a',strtotime($row['time'])).'</h5>
					</div>
					<div class="clear"></div>
				</div>
				<div class="event_row">
					<script>
					    $("#row_'.$j.'_'.$i.'").click(function () {
					    	$("#drop_'.$j.'_'.$i.'").slideToggle();
					    });
					</script>
					<div class="dropdown" id="drop_'.$j.'_'.$i.'">
						<h5>Host: '.$row['host'].'</h5>';
			
						if($row['prize'] != 0)
						$bottom .= '<h5>Prize: $'.$row['prize'].'</h5>';
					
						if($row['description'])
						$bottom .= '<p>Description: '.$row['description'].'</p>';
						
						$bottom .= '<form action="event.php" method="GET">
										<input class="right" type="submit" value="Stats and Details">
										<input type="hidden" name="eid" value="'.$row['eid'].'">
									</form>';
					
						if($_SESSION['access'] >= VOLUNTEER)
						{
						$bottom .= '<form action="scan.php" method="GET">
										<input class="right" type="submit" value="Scan">
										<input type="hidden" name="eid" value="'.$row['eid'].'">
									</form>
									<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
										<input class="right" type="submit" value="Delete">
										<input type="hidden" name="eid" value="'.$row['eid'].'">
										<input type="hidden" name="action" value="Delete">
									</form>';
						}
								
					$bottom .= '
					</div>
				</div>';
		}
	}
	else
	{
		$bottom .= ' 
				<div class="item">
					<div class="row">
						<label>No Events</label>
					</div>
					<div class="row">
						<h5>--</h5>
					</div>
				</div>';
	}
	$bottom .= '
	</div>';
	$j++;
}
$bottom .= '
</div>';


$box = new Box('Events', $bottom);
if($_SESSION['access'] >= ADMINISTRATOR)
$box->setBadge('Add Event', $_SERVER['PHP_SELF'].'?action=add');

$content = 
$page->setContent($box->display('half'));
$page->buildPage();
?>