<?php


function define_basic()
{
define(PRODUCT,'ESCan');
define(ORGANIZATION, 'The Engineering Student Council');
define(EMAIL, 'esc.uci@email.com');
define(WEBSITE, 'http://escan.site90.com/escan');
define(DESCRIPTION, 'ESCan Description');
}


function define_db()
{
define(DBDATABASE, '');  			//MySQL Database Name
define(DBSERVER, ''); 				//MySQL Server usually localhost
define(DBUSERNAME, '');				//MySQL Username
define(DBPASSWORD, ''); 			//MySQL Password
}

function define_webmaster()
{
define(WEBMASTER_USERNAME, ''); 	//Enter the username of the webmaster
define(WEBMASTER_PASSWORD, ''); 	//Enter the password of the webmaster
define(WEBMASTER_EMAIL, ''); 		//Enter the webmaster's email address
}

define_db();
define_basic();
define_webmaster();
?>