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
			<script src="javascript/chart.js"></script>';
			
*/

$box = new Box('Statistics', $bottom);

$content = $box->display('full');

$page->setContent($content);
$page->buildPage();
?>