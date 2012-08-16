<?php

/**
 * VarArray Class
 *
 * This class contains all of the Arrays that need to be generated
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 *
 **/

class VarArray
{
	public $majors;
	public $levels;
	public $hours;
	public $minutes;
	public $access;

	function __construct()
	{
		
	}
	
	function getDates($initial = false, $debug = false)
	{
		if($initial)
			$temp = array('--' 	=> 	'Select Date');
			
		$debug_dates = array (
		'2012-02-17'	=> 	'2/17 Friday',	
		'2012-02-18'	=> 	'2/18 Saturday',
		'2012-02-19' 	=> 	'2/19 Sunday',
		'2012-02-20'  	=> 	'2/20 Monday',
		'2012-02-21' 	=> 	'2/21 Tuesday',	
		'2012-02-22' 	=>	'2/22 Wednesday', 
		'2012-02-23' 	=>	'2/23 Thursday', 
		'2012-02-24'	=>	'2/24 Friday', 
		'2012-02-25'	=>	'2/25 Saturday',
		'2012-02-26' 	=>	'2/26 Sunday');
		
		$dates = array (
		'2012-2-17'		=> 	'2/17 Friday',	
		'2012-2-18'		=> 	'2/18 Saturday',
		'2012-2-19' 	=> 	'2/19 Sunday',
		'2012-2-20'  	=> 	'2/20 Monday',
		'2012-2-21' 	=> 	'2/21 Tuesday',	
		'2012-2-22' 	=>	'2/22 Wednesday', 
		'2012-2-23' 	=>	'2/23 Thursday', 
		'2012-2-24'		=>	'2/24 Friday', 
		'2012-2-25'		=>	'2/25 Saturday',
		'2012-2-26' 	=>	'2/26 Sunday');
		
		//for debugging purposes
		if($debug)
			$dates = $debug_dates;
			
			
		if($initial)
			$this->dates = ($temp + $dates);
		else 
			$this->dates = $dates;
			
		return $this->dates;
	}

	function getHours($initial = false)
	{
		if($initial)
			$temp = array('--' => '--');
			
		$hours = array(
		'1' => '1', 
		'2' => '2', 
		'3' => '3', 
		'4' => '4', 
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		);
		
		if($initial)
			$this->hours = ($temp + $hours);
		else 
			$this->hours = $hours;
			
		return $this->hours;
	}
	
	function getMinutes($initial = false)
	{
		if($initial)
			$temp = array('--' => '--');
			
		$minutes = array(
		'00' => '00', 
		'10' => '10', 
		'20' => '20', 
		'30' => '30', 
		'40' => '40', 
		'50' => '50');
		if($initial)
			$this->minutes = ($temp + $minutes);
		else 
			$this->minutes = $minutes;
			
		return $this->minutes;
	}
	
	function getAMPM($initial = false)
	{
		if($initial)
			$temp = array('--' => '--');
			
		$ampm = array (
		'am' => 'am',
		'pm' => 'pm');
		
		if($initial)
			$this->ampm = ($temp + $ampm);
		else 
			$this->ampm = $ampm;
			
		return $this->ampm;
	}
	
	function getMajors($initial = false)
	{
		if($initial)
			$temp = array('' => 'Select Major');
			
		$majors = array(
		'Aerospace Engineering' => 'Aerospace Engineering',
		'Biomedical Engineering' => 'Biomedical Engineering', 
		'Biomedical Engineering: Premedical' => 'Biomedical Engineering: Premedical',
		'Chemical Engineering' => 'Chemical Engineering',
		'Civil Engineering' => 'Civil Engineering',
		'Computer Engineering' => 'Computer Engineering',
		'Computer Science Engineering' => 'Computer Science Engineering',
		'Electrical Engineering' => 'Electrical Engineering',
		'Environmental Engineering' => 'Environmental Engineering',
		'Material Science Engineering' => 'Material Science Engineering',
		'Mechanical Engineering' => 'Mechanical Engineering', 
		'Mechanical Aerospace Engineering' => 'Mechanical Aerospace Engineering',
		'Other' => 'Other');
		
		if($initial)
			$this->majors = ($temp + $majors);
		else 
			$this->majors = $majors;
			
		return $this->majors;
	}
	
	function getLevels($initial = false)
	{
		if($initial)
			$temp = array('' => 'Select Level');
			
		$levels = array(
		'Freshman' => 'Freshman',
		'Sophomore' => 'Sophomore',
		'Junior' => 'Junior',
		'Senior' => 'Senior',
		'Graduate' => 'Graduate',
		'Faculty/Staff' => 'Faculty/Staff');
		
		if($initial)
			$this->levels = ($temp + $levels);
		else 
			$this->levels = $levels;
			
		return $this->levels;
	}
	
	function getAccess($initial = false)
	{
		if($initial)
			$temp = array('' => 'Select Access');
			
		$access = array (
		PARTICIPANT => 'Participant',
		VOLUNTEER => 'Volunteer',
		ADMINISTRATOR => 'Admin',
		WEBMASTER => 'Webmaster');
		
		if($initial)
		{
			$this->access = ($temp + $access);
		}
		else 
			$this->access = $access;
			
		return $this->access;
	}
	
}

?>