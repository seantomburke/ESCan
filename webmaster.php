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

if(strpos($_SERVER['HTTP_HOST'], 'heroku') != false){
	$hostnames = explode('.', $_SERVER['HTTP_HOST']);
	$heroku_link = 'To get to the Heroku Dashboard click on this link: <a href="https://dashboard.heroku.com/apps/'.$hostnames[0].'/">https://dashboard.heroku.com/apps/'.$hostnames[0].'/</a>"';
}

$content = '
<h2>Database Access</h2>

<h3>phpMyAdmin</h3>
Click here to access 
<a class="" href="phpMyAdmin/?pma_username='.DBUSERNAME.'&pma_password='.DBPASSWORD.'">phpMyAdmin</a> with the following username and password:
<br>';
$content .= '<br><b>Username:</b> '.DBUSERNAME;
$content .= '<br><b>Password:</b> '.DBPASSWORD.'<br>

<br>Use phpMyAdmin to view and export all the raw data. Make sure not to modify any data unless you know what you are doing. You could end up making permanent damage that would involve reinstalling ESCan and losing data permanently. This is the raw data that runs ESCan.
To export the data, click on the database called "'.DBDATABASE.'" and then click "Export" in the tab at the top of the page. You can then chose the "Custom" option, then chose to export the data to a file in the "Output" section.
<br><br>
<div class="separator"></div>
<h3>Heroku</h3>

'.$heroku_link.'

If you are on a <a href="http://www.heroku.com">Heroku</a> hosted application, ClearDB, the MySQL host provided by Heroku, won\'t allow you to connect with phpMyAdmin. Instead enter the credentials below into a program such as <a href="http://www.sequelpro.com/"> Sequel Pro</a> for Mac or <a href="http://www.mysql.com/products/workbench/">MySQL Workbench</a> for Windows<br><br>';

$content .= '<br><b>Host:</b> '.DBSERVER;
$content .= '<br><b>Username:</b> '.DBUSERNAME;
$content .= '<br><b>Password:</b> '.DBPASSWORD;
$content .= '<br><b>Database:</b> '.DBDATABASE;
$content .= '<br><b>Port:</b> 3306<br><br>';
$content .= '<div class="separator"></div>';
$content .= '<h3>Command Line</h3>

Or you can use the Command Line to access MySQL<br><br>';
$content .= '<div class="code"><span class="noselect">&gt;$ </span><span class="selectable">mysql -h '.DBSERVER.' -u '.DBUSERNAME.' --password='.DBPASSWORD.' '.DBDATABASE.' -P 3306</span></div><br>';
$content .= '<div class="code"><span class="noselect">mysql&gt; </span><span class="selectable">SELECT * FROM users LIMIT 20;</div><br><br>';



$table .= '<div class="separator"></div><h2>ESCan Errors</h2>';

$table .= '<table class="error-table">';
$table .= '  <thead>
				<tr>
			    	<th colspan="10">Error Table</th>
				</tr>
			</thead>';
$table .= '<tr>';
$table .= '	<td class="error-table-eid">Eid</td>';
$table .= '	<td class="error-table-message">Message</td>';
$table .= '	<td>status</td>';
$table .= '	<td>ucinetid</td>';
$table .= '	<td>page</td>';
$table .= '	<td>referer</td>';
$table .= '	<td class="error-table-browser">browser</td>';
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

$user_box->setContent($content.$table);


$content = $user_box->display('full');

$page->setContent($content);
$page->buildPage();

?>