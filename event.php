<?php
require_once 'inc/standard.php';

//$page = new Page($name, $css);
$page = new Page('event', ALL);
$var = new VarArray();
$eid = $_GET['eid'];
//clean each $_POST value of dangerous ps
//example $newsettings['email'] = 'stburke@uci.edu';



if(!$eid)
{
	$bottom = 'No event selected, please return to the <a href="events.php">Events Page</a>.';
	
	$box = new Box('No Existing Event');
	$box->setBadge('Return to Events', 'events.php');
	
	$box->setContent($bottom);
	$page->setContent($box->display('half'));
	$page->buildPage();
}
else
{
	$sql = 'SELECT * FROM events
			WHERE eid ="'.$eid.'"';
	$page->DB->query($sql);
	$event = $page->DB->resultToSingleArray();
	
	$total_count = $page->DB->countOf('scans', 'eid = "'.$eid.'"');
	
	$box = new Box($event['name']);
	$box->setBadge('Return to Events', 'events.php');
	$bottom = '
				<div class="row">
					<label class="fieldname">Name</label>
					<div class="textarea">
					'.$event['name'].'
					</div>
				</div>
				<div class="row">
					<label class="fieldname">Date</label>
					<div class="textarea">
					'.date('l, M d, Y',strtotime($event['date'])).'
					</div>
				</div>
				<div class="row">
					<label class="fieldname">Time</label>
					<div class="textarea">
					'.date('g:i a',strtotime($event['time'])).'
					</div>
				</div>
				<div class="row">
					<label class="fieldname">Host</label>
					<div class="textarea">
					'.$event['host'].'
					</div>
				</div>
				<div class="row">
					<label class="fieldname">Prize</label>
					<div class="textarea">
					'.$event['prize'].'
					</div>
				</div>
				<div class="row">
					<label class="fieldname">Description</label>
					<div class="textarea">
					'.$event['description'].'
					</div>
				</div>
				<div class="row">
					<label class="fieldname">Total Participants</label>
					<div class="textarea">
					'.$total_count.'
					</div>
				</div>';
				
				
	$limit = ($_GET['view'] == 'all') ? '':'LIMIT 5';
	
	$sql = 'SELECT scans.*, users.name, users.ucinetid, users.major, users.level
			FROM scans LEFT JOIN users
			ON scans.barcode = users.barcode
			WHERE scans.eid = "'.$eid.'"
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
			<label id="view"><a href="'.$_SERVER['PHP_SELF'].'?eid='.$eid.'&view=less#view">View Less Scans</a></label>
			</div>';
		}
		else 
		{
		$ticker_content .= '
			<div class="row center">
			<label id="view"><a href="'.$_SERVER['PHP_SELF'].'?eid='.$eid.'&view=all#view">View All Scans</a></label>
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
	 
	$box->setContent( $bottom.$ticker_content);
	$box->setBadge('Return to Events', 'events.php');
	
	foreach ($var->getMajors() as $major) {
		
		$sql = 'SELECT *
				FROM scans
				LEFT JOIN users 
				ON scans.barcode = users.barcode
				WHERE scans.eid = "'.$eid.'"
				AND users.major = "'.$major.'"';
		$page->DB->query($sql);
		$temp = $page->DB->numRows();
		//echo '<br>value:'.$temp;
	
		$majors[$major] = $temp;
	}
	
	$majors_stat = new Statistic('pie', 'user_majors', 'Registered Users - Major', $majors, 0);
	
	$bottom_stats = $majors_stat->display();
	
	$box_stat = new Box('Statistics', $bottom_stats);
	$stat_display = '<div class="separator"></div>'.$box_stat->display("full");
	$content = $box->display().$stat_display;
	
	$page->startGraph();
	$page->setContent($content);
	$page->buildPage();
}
?>
	