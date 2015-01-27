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

    $url = parse_url(getenv("CLEARDB_DATABASE_URL")); //if heroku cleardb credentials are defined
    var_dump($url);

    if($url){
        define(DBDATABASE, substr($url["path"], 1));        //cleardb database
        define(DBSERVER, $url["host"]); 				    //cleardb host server
        define(DBUSERNAME, $url["user"]);			//cleardb username
        define(DBPASSWORD, $url["pass"]);           //cleardb password
    }
    else{
        define(DBDATABASE, 'escan');  			            //MySQL Database Name. try 'escan'
        define(DBSERVER, '127.0.0.1'); 				    //MySQL Server. Try 'localhost' or '127.0.0.1'
        define(DBUSERNAME, 'escan');			//MySQL Username. Try 'escan'
        define(DBPASSWORD, ''); 			            //MySQL Password. Lookup in ESC transition files
    }
    echo "<br>DBDATABASE: ".DBDATABASE;
    echo "<br>DBSERVER: ".DBSERVER;
    echo "<br>DBUSERNAME: ".DBUSERNAME;
    echo "<br>DBPASSWORD: ".DBPASSWORD;
    
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