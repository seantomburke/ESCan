<?php

require_once 'inc/standard.php';
$page = new Page('webmaster', WEBMASTER);
$user_box = new Box('Webmaster');

//check for valid access
if(!$page->login->checkValidAccess($page, $_SERVER['PHP_SELF']))
{
	$box = new Box('Access Denied', 'You do not have access to view this page');
	$page->setContent($box->display());
	$page->buildPage();
}

//clean each $_POST value of dangerous inputs
//example $newsettings['email'] = 'stburke@uci.edu';

$sql = 'SELECT errors.*
		FROM errors
		ORDER BY date DESC, time DESC
		LIMIT 100';
		
$page->DB->query($sql);
$error_array = $page->DB->resultToArray();
	
$table = '<table>';
$table .= '<tr>';
$table .= '	<td>Eid</td>';
$table .= '	<td>Message</td>';
$table .= '	<td>status</td>';
$table .= '	<td>ucinetid</td>';
$table .= '	<td>page</td>';
$table .= '	<td>referer</td>';
$table .= '	<td>browser</td>';
$table .= '	<td>ip</td>';
$table .= '	<td>date</td>';
$table .= '	<td>time</td>';
$table .= '	</tr>';


foreach ($error_array as $row)
{
	$table .= '<tr>';
	$table .= '	<td>'.$row['eid'].'</td>';
	$table .= '	<td>'.$row['message'].'</td>';
	$table .= '	<td>'.$row['status'].'</td>';
	$table .= '	<td>'.$row['ucinetid'].'</td>';
	$table .= '	<td>'.$row['page'].'</td>';
	$table .= '	<td>'.$row['referer'].'</td>';
	$table .= '	<td>'.$row['browser'].'</td>';
	$table .= '	<td>'.$row['ip'].'</td>';
	$table .= '	<td>'.$row['date'].'</td>';
	$table .= '	<td>'.$row['time'].'</td>';
	$table .= '</tr>';
}

$table .= '</table>';

$content = 'Use phpMyAdmin to view and export all the raw data. Make sure not to modify any data unless you know what you are doing. You could end up making permanent damage that would involve reinstalling ESCan and losing data permanently. This is the raw data that runs ESCan.
To export the data, click on the database called "'.DBDATABASE.'" and then click "Export" in the tab at the top of the page. You can then chose the "Custom" option, then chose to export the data to a file in the "Output" section.
<br><br>
Click below access phpMyAdmin.
<br><br>
<a class="" href="phpMyAdmin">phpMyAdmin</a>';
$content .= '<br>Username: '.DBUSERNAME;
$content .= '<br>Password: '.DBPASSWORD."<br>";
	
$user_box->setContent($content.$table);


$content = $user_box->display('full');

$page->setContent($content);
$page->buildPage();

?>