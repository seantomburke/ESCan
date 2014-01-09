<?php
date_default_timezone_set('America/Los_Angeles');

function define_basic()
{
define(PRODUCT,'ESCan');									//Name of Product
define(ORGANIZATION, 'The Engineering Student Council');	//Name of Organization or Company
define(EMAIL, 'esc.uci@email.com');							//Email seen by users when notified
define(DESCRIPTION, 'ESCan Description');	                //Description of the system

$scriptname=end(explode('/',$_SERVER['PHP_SELF']));         //Defines the web url
$scriptpath=str_replace($scriptname,'',$_SERVER['PHP_SELF']);
define(WEBSITE, 'http://'.$_SERVER['SERVER_NAME'].$scriptpath);
}


function define_db()
{
define(DBDATABASE, 'c9');  			            //MySQL Database Name. try 'escan'
define(DBSERVER, '127.8.37.129'); 				    //MySQL Server. Try 'localhost' or '127.0.0.1'
define(DBUSERNAME, 'hawaiianchimp');			//MySQL Username. Try 'escan'
define(DBPASSWORD, ''); 			            //MySQL Password. Lookup in ESC transition files
}

function define_webmaster()
{
define(WEBMASTER_USERNAME, 'stburke'); 	        //Enter the username of the webmaster
define(WEBMASTER_PASSWORD, 'escan'); 	        //Enter the password of the webmaster
define(WEBMASTER_EMAIL, 'stburke@uci.edu'); 	//Enter the webmaster's email address
}

define_db();
define_basic();
define_webmaster();
?>