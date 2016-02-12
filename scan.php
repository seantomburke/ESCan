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

$event = new Event($scan['eid']);
setcookie('eid', $scan['eid'], time() + (6*60*60)); //expires in 6 hours
if(!$event->exists())
{
	$page->setMessage('Please Select an Event Before Scanning', 'failure');
	$error_class_event_menu = 'error';
}

$sql = 'SELECT e.name, e.eid
		FROM events AS e
		ORDER BY date ASC, time ASC';
$DB->query($sql);

$event_menu_array = $DB->resultToMakeArray('eid','name', 'Select Event');

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
$total_count = $DB->countOf('scans', 'eid = "'.$_GET['eid'].'"');

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
			<form id="barcode-form" method="GET">
				<div class="row">
					<label>Barcode</label>
					<input type="text" name="barcode" id="barcode" class="textarea">
					<input type="hidden" name="eid" value="'.$scan['eid'].'">
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
    		WHERE scans.eid = "'.$scan['eid'].'"
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
		loadTicker('.$scan['eid'].')
		');
	
$ticker_content .= '

<script>
	
    $("#barcode-form").on("submit", function(e) {
        e.preventDefault();  //prevent form from submitting
        var code = $("#barcode").val();
    	$.ajax({
          type: "GET",
          url: "scanner.php",
          dataType: "json",
          data: { 
            eid: "'.$scan['eid'].'",
            barcode: code,
            ucinetid: "'.$_SESSION['ucinetid'].'"},
          success: function(data) {
            if(data.message.status == "success")
            {
                appendBarcode(data.scan[0]);
            }
            setMessage(data.message.text, data.message.status);
          },
          error: function(data, error, errorMessage) {
			setMessage(errorMessage, "error");
          }
	    });
	    $("#barcode").val(" ");
	    $("#barcode").focus();
	    
    });
    </script>';

$box = new Box('Scanning for '.$event->name, $bottom.$ticker_content);
$box->setBadge('Return to Events', 'events.php');
$var_array = new VarArray();


/*
 * 		start Statistics display
 *
 *
 */

/*
 * Commenting out for speed improvements
 *

foreach ($var_array->getMajors() as $major) {
	$sql = 'SELECT *
			FROM scans
			LEFT JOIN users
			ON scans.barcode = users.barcode
			WHERE scans.eid = "'.$scan['eid'].'"
			AND users.major = "'.$major.'"';
	$DB->query($sql);
	$temp = $DB->numRows();
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
		//$majors["other"] = $DB->countOf('users', $where_majors_not_like, 1);
		$majors["Other"] = $DB->queryUniqueValue($sql);
	}

}

$majors_stat = new Statistic('pie', 'user_majors', 'Registered Users - Major', $majors, 0);

$bottom_stats = $majors_stat->display();

if($scan['eid'] != 0)
{
$box_stat = new Box('Statistics', $bottom_stats);
$stat_display = $box_stat->display("full");
}
*/

$box->setIntroStep(8);
$box->setIntroText("This page is where Voluneers will scan users in 
	for the event. Users must have already registered their wristbands 
		at the registration booth in order to get scanned.<br><br>
	If the event hasn't started yet, you will see a red box indicating this.");

$intro_scripts = '<script src="js/intro.min.js"></script>
	<script type="text/javascript">
	$(".box_inside").css("min-height", "200px");
	if(window.location.hash) {
		var hash = window.location.hash.substring(1);
		if(hash == "intro"){
			introJs().setOption("doneLabel", "Finish Tour").start();
		}
	}
</script>';





$content = $box->display().$stat_display;
$page->setContent($content.$intro_scripts);
$page->buildPage();
?>