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

include_once 'inc/setup/_config.php';
include_once 'inc/standard.php';

if(WEBMASTER_USERNAME && WEBMASTER_EMAIL && WEBMASTER_PASSWORD && DBDATABASE && DBUSERNAME && DBSERVER)
{
    $db = new DB();
    $sql = "SELECT COUNT(DISTINCT `table_name`) FROM `information_schema`.`columns` WHERE `table_schema` = '".DBDATABASE."';";
    if($db->query($sql,1)===0)
    {
        $sql = explode(';', file_get_contents('inc/setup/setup.sql'));
        echo 'Getting setup.sql<br>';
        foreach($sql as $row)
        {
            $short = str_split($row, 100);
            echo 'Running '.$short[0].'...<br>';
            $db->execute($row);
        }

        echo 'Database setup<br>';
    }
}

echo '<br>Connecting to Database<br>';

$sql = "SELECT * FROM users WHERE access = ". WEBMASTER .";";
$db->query($sql);
$web_admins = $db->resultToArray();

if(!$db->isEmpty())
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
    '.$emails.' or <a href="mailto:esc.uci@gmail.com">esc.uci@gmail.com</a> if you feel you received this message in error');
    }
}

echo 'Creating Webmaster<br>';

echo 'Inserting Webmaster<br>';
$sql = 'REPLACE INTO `users` VALUES("'.WEBMASTER_USERNAME.'", "", "", "'.WEBMASTER_EMAIL.'", "", "", 1, 8, 1, "", "", "_setup.php")';
$db->execute($sql);
$sql = 'REPLACE INTO `logon` VALUES("'.WEBMASTER_USERNAME.'", "'.md5(WEBMASTER_PASSWORD).'", "", "", "")';
$db->execute($sql);
echo 'Insertion complete<br>';
echo 'Done<Br>';
echo '<a href="index.php">Click here to start ESCan</a>';
}
else
{
	$message = 'Please enter in all credentials on the _config.php found in "/inc/setup/_config.php".<br>';
	$message .= "<br>WEBMASTER_USERNAME: ".WEBMASTER_USERNAME."<br>WEBMASTER_EMAIL: ".WEBMASTER_EMAIL."<br>WEBMASTER_PASSWORD: ".WEBMASTER_PASSWORD ."<br>DBDATABASE: ". DBDATABASE ."<br>DBUSERNAME: ". DBUSERNAME ."<br>DBSERVER: ". DBSERVER ."<br>DBPASSWORD: ".DBPASSWORD."<br>";
	die($message);
}
?>