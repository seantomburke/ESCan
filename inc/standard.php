<?php
date_default_timezone_set('America/Los_Angeles');
/**
 * Standard
 * 
 * includes all standard PHP inc files
 * including this page is required for all pages
 *
 * @author Sean Burke, http://www.seantburke.com
 */

/**
 * Used to compare the version number of PHP
 *
 */
 function addZero($number, $length)
 {
 	if(strlen($number) === $length)
 	{
 		return $number;
 	}
 	elseif(strlen($number) > $length)
 	{
 		return addZero(substr($number,0, -1),$length);
 	}
 	elseif (strlen($number) < $length){
 		return addZero($number.'0',$length);
 	}
 }

/**
 * Check the current version of php and if it's less than 5.4 then use a different DB class.
 * This is needed because the mysql functions differ after 5.4
 */

if(addZero(str_replace(".","0",phpversion()),6) <= 504000){
	require_once 'inc/classes/DB.class.php';
}
else{
	require_once 'inc/classes/DBi.class.php';
}

/*Classes*/
require_once 'inc/classes/Page.class.php';
require_once 'inc/classes/UCIPerson.class.php';
require_once 'inc/classes/Sniper.class.php';
require_once 'inc/classes/Login.class.php';
require_once 'inc/classes/Box.class.php';
require_once 'inc/classes/DropMenu.class.php';
require_once 'inc/classes/VarArray.class.php';
require_once 'inc/classes/Mail.class.php';
require_once 'inc/classes/Barcode.class.php';
require_once 'inc/classes/Scanner.class.php';
require_once 'inc/classes/Event.class.php';
require_once 'inc/classes/Color.class.php';
require_once 'inc/classes/Nav.class.php';
require_once 'inc/classes/Statistic.class.php';
require_once 'inc/classes/Vegas.class.php';

/*incs*/

require_once 'inc/constants.inc.php';

?>
