<?php
session_start();
echo 'Initializing Setup<br>';

/** Instructions for setup
 *
 * Each page requires at the beginning "require_once 'inc/standard.php';"
 * If you are going to create a new page, be sure to include that at the beginning.
 * The template for every page can be found in the Page.class.php object class.
 * When you create a new Page($name, $access) object, you must specify a $name and $access
 * Access levels can be found in the constants.inc.php.
 * Most of the pages use objects to construct most of the visuals and layouts,
 * so be sure to look into the Classes for clarification.
 * 
 *
 *
 **/

require_once 'inc/setup/_config.php';
require_once 'inc/standard.php';

echo '<br>Checking Install<br>';

if(WEBMASTER_USERNAME && WEBMASTER_EMAIL && WEBMASTER_PASSWORD && DBDATABASE && DBUSERNAME && DBSERVER)
{
    echo "<br>All _config.php constants set<br>";

    $sql = 'SELECT COUNT( DISTINCT table_name) FROM information_schema.columns WHERE table_schema = "'.DBDATABASE.'";';
    $DB->query($sql);
    $result = $DB->resultToSingleArray();
    echo "<br>table count: ".$result[0]."<br>";
    if($result[0]==0)
    {
        echo '<br>No tables found, creating tables...<br>';
        $sql = explode(';', file_get_contents('inc/setup/setup.sql'));
        echo 'Getting setup.sql<br>';
        foreach($sql as $row)
        {
            $short = str_split($row, 100);
            echo 'Running '.$short[0].'...<br>';
            if($row)
                $DB->execute($row);
        }

        echo 'Database finished setup<br>';
    }
    else{
        $message = 'Databases and tables have already been installed.<br>';
        $message .= "If you need to reinstall ESCan go to the <a href='admin.php'>Admin page</a> and reinstall it there";
        die($message);
    }

    echo '<br>Connecting to Database<br>';


    $sql = 'REPLACE INTO `settings` VALUES ("eweekstart", "'.EWEEKSTART.'")';
    $DB->execute($sql);

    echo "<br>Eweek Start Week set<br>";

    $sql = "SELECT * FROM users WHERE access = ". WEBMASTER .";";
    $DB->query($sql);
    $web_admins = $DB->resultToArray();

    if(!$DB->isEmpty())
    {
        $authorized = false;
        $emails = '';
        foreach($web_admins as $webadmin)
        {
            $emails .= '<a href="mailto:'.$webadmin['email'].'">'.$webadmin['email'].'</a>, ';
            if($_SESSION['ucinetid'] == $webadmin['ucinetid'])
            {
                $authorized = true;
            }
        }
        if(!$authorized)
        {
        $sniper = new Sniper();
        $sniper->storeMessage("Illegall access of install.php", $_SESSION['ucinetid'], "hacker");
        die('ESCan has already been installed. If you are the webadmin and would like to reinstall ESCan go to the 
        <a href="admin.php">Admin Page</a>. This incident will be reported. Please contact the Web Admin at 
        '.$emails.' or <a href="mailto:'.ORG_EMAIL.'">'.ORG_EMAIL.'</a> if you feel you received this message in error');
        }
    }
    else{

        echo 'Creating Webmaster<br>';

        echo 'Inserting Webmaster<br>';
        $sql = 'REPLACE INTO `users` VALUES("'.WEBMASTER_USERNAME.'", "", "", "'.WEBMASTER_EMAIL.'", "", "", 1, 8, 1, "", "", "_setup.php")';
        $DB->execute($sql);
        $sql = 'REPLACE INTO `logon` VALUES("'.WEBMASTER_USERNAME.'", "'.md5(WEBMASTER_PASSWORD).'", "", "", "")';
        $DB->execute($sql);
        echo 'Insertion complete<br>';
        echo 'Done<Br>';
        $DB->close();
        echo 'Disconnecting from Database<Br>';
        echo '<a href="admin.php#intro">Click here to login and setup ESCan</a>';

    }
}
else
{
	$message = 'Please enter in all credentials on the _config.php found in "/inc/setup/_config.php".<br>';
	$message .= "<br>WEBMASTER_USERNAME: ".WEBMASTER_USERNAME."<br>WEBMASTER_EMAIL: ".WEBMASTER_EMAIL."<br>WEBMASTER_PASSWORD: ".WEBMASTER_PASSWORD ."<br>DBDATABASE: ". DBDATABASE ."<br>DBUSERNAME: ". DBUSERNAME ."<br>DBSERVER: ". DBSERVER ."<br>DBPASSWORD: ".DBPASSWORD."<br>";
	die($message);
}
?>