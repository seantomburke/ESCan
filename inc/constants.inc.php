<?php


/**
 * Constants
 * 
 * includes all constants
 *
 * @author Sean Burke, http://www.seantburke.com
 * @tag value
 *
 *
 * 
 */
 date_default_timezone_set('America/Los_Angeles');

/**
 * Defines the date for NOW_TIME and NOW_DATE if an offset is defined.
 */
 function define_time()
 {
 	define('OFFSET', 0);
 	define('NOW_TIME', date('H:i:s', time()-(OFFSET*60*60)));
 	define('NOW_DATE', date('Y-m-d', time()-(OFFSET*60*60)));
 }

/**
 * Defines the access types for users
 */
 function define_access()
 {
 define('ALL', 0);
 define('PARTICIPANT', 2);
 define('VOLUNTEER', 4);
 define('ADMINISTRATOR', 6);
 define('WEBMASTER', 8);
 }

/**
 * Function for getting the correct access string
 *
 * @param $access [Integer] from the definitions above
 * @return string
 */
 function switchAccess($access)
 {
 	switch($access)
 	{
	 	case ALL:
	 	$output = 'Public';
	 	break;
	 	case PARTICIPANT:
	 	$output = 'Participant';
	 	break;
	 	case VOLUNTEER:
	 	$output = 'Volunteer';
	 	break;
	 	case ADMINISTRATOR:
	 	$output = 'Administrator';
	 	break;
	 	case WEBMASTER:
	 	$output = 'Webmaster';
	 	break;
	 	default:
	 	$output = 'None';
 	}
 	return $output;
 }

define_access();
define_time();
 ?>