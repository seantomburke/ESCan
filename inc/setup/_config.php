<?php
date_default_timezone_set('America/Los_Angeles');

function define_basic()
{
define(PRODUCT,'ESCan');									//Name of Product
define(ORGANIZATION, 'The Engineering Student Council');	//Name of Organization or Company
define(EMAIL, 'esc.uci@email.com');							//Email seen by users when notified
define(WEBSITE, 'http://esc.eng.uci.edu/escan/');					//url of your site where this application is hosted
define(DESCRIPTION, 'ESCan Description');					//Description of the system
}


function define_db()
{
define(DBDATABASE, 'c9');  			//MySQL Database Name
define(DBSERVER, '127.11.251.1'); 				//MySQL Server usually localhost
define(DBUSERNAME, 'hawaiianchimp');				//MySQL Username
define(DBPASSWORD, ''); 			//MySQL Password
}

function define_webmaster()
{
define(WEBMASTER_USERNAME, 'stburke'); 	//Enter the username of the webmaster
define(WEBMASTER_PASSWORD, 'escan'); 	//Enter the password of the webmaster
define(WEBMASTER_EMAIL, 'stburke@uci.edu'); 		//Enter the webmaster's email address
}

define_db();
define_basic();
define_webmaster();
?>