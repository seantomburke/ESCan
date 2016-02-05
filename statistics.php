<?php
require_once 'inc/standard.php';

//$page = new Page($name, $css);
$page = new Page('statistics', ALL);
$page->setTitle('Statistics');
$page->startGraph();
//adds the jQuery Sparkline to the header

$var_array = new VarArray();
 
 /* User Statistics 
  * 
  */
  
  
//Top Participants

$sql = "SELECT users.ucinetid, COUNT(*) as num_events FROM  users, barcodes, scans, events 
        WHERE users.ucinetid = barcodes.ucinetid
        AND barcodes.barcode = scans.barcode
        AND scans.eid = events.eid
        AND users.elig = 1
        GROUP BY users.name
        ORDER BY num_events DESC
        LIMIT 10;";
$page->DB->query($sql);

$top_participants = '<div class="row">
                        <h2>Top Participants</h2>
                        <div class="legend_pie">
                            <div class="legend_pie_inside">
                                <div class="row center">
            						<span class="left">User</span>
            						<span class="right">Number of Events</span>
            					</div>';
$result = $page->DB->resultToArray();
if(!empty($result))
{
    foreach($result as $key => $value)
    {
            $top_participants .=  '<div class="row">
            						<font color="#003333">
            						<span class="left">'.$value[0].'</span>
            						<span class="right">'.$value[1].'</span>
            						</font><br>
            					    </div>';
    }
}

$top_participants .= "      </div>
                        </div>
                    </div>";


foreach($var_array->getAccess() as $key => $value)
{
	//echo $key.' => '.$value;
	$access[$value] = $page->DB->countOf('users', 'access = "'.$key.'"');
}

$access_stat = new Statistic('pie', 'user_access', 'Registered Users - Access', $access, 6); //play with this number to change up the colors

/*end Major Statistics*/	
 
 /*
  * Major Statistics
  */
foreach ($var_array->getMajors() as $major) {
	$where .= 'major NOT LIKE \''.$major.'\' AND ';
	if($major != "Other")
	{
		$majors[$major] = $page->DB->countOf('users', 'major = "'.$major.'"');
	}
	else
	{
		$where = substr($where, 0, strlen($where)-5);
		//echo "where: ".$where;
		//$majors["other"] = $page->DB->countOf('users', $where_majors_not_like, 1);
		$majors["Other"] = $page->DB->countOf('users', $where);
	}
}

$majors_stat = new Statistic('pie', 'user_majors', 'Registered Users - Major', $majors, 0);//play with this number to change up the colors

/*end Major Statistics*/	


/*
 * Levels Statistics
 */
foreach ($var_array->getLevels() as $level) {
	$levels[$level] = $page->DB->countOf('users', 'level = "'.$level.'"');
}

$levels_stat = new Statistic('pie', 'user_levels', 'Registered Users - Level', $levels, 2);//play with this number to change up the colors

/* end Levels Statistic */

/*
 * Events Statistics
 */
foreach ($var_array->getDates() as $key => $value) {
	$new_key = date('n/d',strtotime($key));
	$events[$new_key] = $page->DB->countOf('events', 'date = "'.$key.'"');
	//echo 'date = "'.$key.'" - '.$events[$new_key].'<br>';
}

$events_stat = new Statistic('bar', 'events_stat', 'Number of Events on Each Day', $events, 2);//play with this number to change up the colors

/* end Events Statistic */

/*
 * Events Statistics
 */
foreach ($var_array->getDates(false, true) as $key => $value) {
	$new_key = date('n/d',strtotime($key));
	$sql = 'SELECT COUNT(*)
			FROM scans
			LEFT JOIN events 
			ON scans.eid = events.eid
			WHERE events.date LIKE "'.$key.'"';
	$scans[$new_key] = $page->DB->queryUniqueValue($sql);
	//echo 'date = "'.$key.'"';
}

$participation_stat = new Statistic('bar', 'scans_week', 'Participation on Each Day', $scans, 7);//play with this number to change up the colors

/* end Events Statistic */

$bottom .= $top_participants;
$bottom .= '<div class="separator"></div>';
$bottom .= $access_stat->display();
$bottom .= '<div class="separator"></div>';
$bottom .= $majors_stat->display();
$bottom .= '<div class="separator"></div>';
$bottom .= $levels_stat->display();
$bottom .= '<div class="separator"></div>';
$bottom .= $events_stat->display();
$bottom .= '<div class="separator"></div>';
$bottom .= $participation_stat->display();


/*
$bottom .= '<div id="chart"></div>
			<script src="js/chart.js"></script>';
			
*/

$box = new Box('Statistics', $bottom);

$content = $box->display('full');

$page->setContent($content);
$page->buildPage();
?>