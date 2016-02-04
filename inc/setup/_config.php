<?php
error_reporting(-1);
date_default_timezone_set('America/Los_Angeles');

function define_basic()
{
    //Name of APP
    if(getenv("APP_NAME"))
        define('PRODUCT',getenv("APP_NAME")); 
    else                 
        define('PRODUCT','ESCan');                  

    //Name of Organization or Company
    if(getenv("ORGANIZATION"))
        define('ORGANIZATION',getenv("ORGANIZATION")); 
    else                 
        define('ORGANIZATION', 'The Engineering Student Council');  

    //Email seen by users when notified
    if(getenv("ORG_EMAIL"))
        define('EMAIL', getenv('ORG_EMAIL'));                           
    else
        define('EMAIL', 'esc.uci@email.com');                           

    //Description of the app
    if(getenv("DESCRIPTION"))
        define('DESCRIPTION', getenv("DESCRIPTION"));
    else
        define('DESCRIPTION', 'ESCan is a system developed by Sean Burke in 2012 which keeps track of participation at UC Irvine\'s National Engineers week. Register today and experience the celebration of National Engineers Week!');                   //Description of the system

    if(getenv("EWEEKSTART"))
        define('EWEEKSTART', getenv("EWEEKSTART"));
    else
        define('EWEEKSTART', '2015-W09');
    
    $weburl = explode('/',$_SERVER['PHP_SELF']);
    $scriptname=end($weburl);         //Defines the web url
    $scriptpath=str_replace($scriptname,'',$_SERVER['PHP_SELF']);
    define('WEBSITE', $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['SERVER_NAME'].$scriptpath);
}

function define_db()
{
    $url = parse_url(getenv("CLEARDB_DATABASE_URL")); //if heroku cleardb credentials are defined

    if(count($url) > 1) {
        define('DBDATABASE', substr($url["path"], 1));      //cleardb database
        define('DBSERVER', $url["host"]); 				    //cleardb host server
        define('DBUSERNAME', $url["user"]);			        //cleardb username
        define('DBPASSWORD', $url["pass"]);                 //cleardb password
    } else if( getenv('C9_USER') ) {
        define('DBDATABASE', 'c9');  			            //c9 Database Name. try 'escan'
        define('DBSERVER', getenv('IP')); 				    //c9 Server. Try 'localhost' or '127.0.0.1'
        define('DBUSERNAME', getenv('C9_USER'));			//c9 Username. Try 'escan'
        define('DBPASSWORD', ''); 			                //c9 Password. Lookup in ESC transition files
    } else {
        define('DBDATABASE', 'escan');  			        //MySQL Database Name. try 'escan'
        define('DBSERVER', '127.0.0.1'); 				    //MySQL Server. Try 'localhost' or '127.0.0.1'
        define('DBUSERNAME', 'root');			            //MySQL Username. Try 'escan'
        define('DBPASSWORD', 'root'); 			            //MySQL Password. Lookup in ESC transition files
    }
}

function define_webmaster()
{
	if(getenv("WEBMASTER_USERNAME") && getenv("WEBMASTER_PASSWORD") && getenv("WEBMASTER_EMAIL")){
    	//heroku stuff
    	define('WEBMASTER_USERNAME', getenv("WEBMASTER_USERNAME")); 	       
    	define('WEBMASTER_PASSWORD', getenv("WEBMASTER_PASSWORD")); 	        
    	define('WEBMASTER_EMAIL', getenv("WEBMASTER_EMAIL"));
    }
    else{
    	define('WEBMASTER_USERNAME', 'stburke'); 	        //Enter the username of the webmaster
    	define('WEBMASTER_PASSWORD', 'escan'); 	        //Enter the password of the webmaster
    	define('WEBMASTER_EMAIL', 'stburke@uci.edu'); 	//Enter the webmaster's email address
    }
}

define_db();
define_basic();
define_webmaster();
?>