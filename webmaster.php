<?php

require_once 'inc/standard.php';
$page = new Page('webmaster', WEBMASTER);
$page->setTab('WebMaster');
$user_box = new Box('Errors');

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
	
$user_box->setContent($table);


$content = $user_box->display('full');

$page->setContent($content);
$page->buildPage();

?>