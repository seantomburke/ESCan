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
	private $DB;

	function __construct()
	{
		$this->DB = new DB();
	}
	
	function getDates($initial = false, $debug = false)
	{
		if($initial) {
			$temp = array('--' 	=> 	'Select Date');
		}
			
		$sql = 'SELECT value
				FROM settings
				WHERE name = "eweekstart"';
				
		$this->DB->query($sql);
		
		$eweekstart = $this->DB->resultToSingleArray();
		
		$dates = [];
		for($i=0; $i< 10;$i++){
			$index = $i - 3;
			$key = date('Y-m-d', strtotime($eweekstart[0]." ".$index." days"));
			$value = date('m/d l', strtotime($eweekstart[0]." ".$index." days"));
			$dates[$key] = $value;
		}
		
		if($initial) {
			$this->dates = ($temp + $dates);
		} else {
			$this->dates = $dates;
		}
		
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
		'Engr AE' => 'Engr AE',
		'Engr BM' => 'Engr BM', 
		'EngrBMP' => 'EngrBMP',
		'EngrChm' => 'EngrChm',
		'Engr CE' => 'Engr CE',
		'EngrCpE' => 'EngrCpE',
		'CSE' => 'CSE',
		'Engr EE' => 'Engr EE',
		'EngrEnv' => 'EngrEnv',
		'Enr MSE' => 'Enr MSE',
		'Engr ME' => 'Engr ME', 
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
		'5th Year' => '5th Year',
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