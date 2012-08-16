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
 
 function define_all()
 {
 	define_access();
 	define_time();
 }
 
 function define_time()
 {
 	define(OFFSET, 3);
 	define(NOW_TIME, date('H:i:s', time()-(OFFSET*60*60)));
 	define(NOW_DATE, date('Y-m-d', time()-(OFFSET*60*60)));
 }

 function define_access()
 {
 define(ALL, 0);
 define(PARTICIPANT, 2);
 define(VOLUNTEER, 4);
 define(ADMINISTRATOR, 6);
 define(WEBMASTER, 8);
 }
 
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
 
 ?>