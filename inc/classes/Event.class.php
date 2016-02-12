<?php

class Event{

	/**
	* Class Event
	*
	* this class will create a new Event Object, used in the events.php
	*
	* @author Sean Burke, http://www.seantburke.com
	*
	**/

	private $DB;
	public $eid;
	public $name;
	public $host;
	public $prize;
	public $date;
	public $time;
	public $description;

	function __construct($eid = ''){
		$this->DB = $GLOBALS['DB'];
		
		if($eid != '')
		{
			$this->eid = $eid;
			if($this->exists())
			{
				$sql = 'SELECT * FROM events
			WHERE eid = "'.$this->eid.'"';
	
				$this->DB->query($sql);
				$result = $this->DB->resultToSingleArray();
	
				$this->name = $result['name'];
				$this->host = $result['host'];
				$this->prize = $result['prize'];
				$this->date = $result['date'];
				$this->time = $result['time'];
				$this->result = $result['description'];
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	function addEvent($name, $date, $hour, $minute, $ampm, $host, $prize, $description)
	{
		$ampm_push = ($ampm == 'pm') ? 12:0;
		$time = ($hour+$ampm_push).':'.$minute.':00';

		$sql = 'INSERT INTO events (name, date, time, host, prize, description, volunteer)
					VALUES ("'.$name.'", "'.$date.'", "'.$time.'", "'.$host.'", "'.$prize.'", "'.$description.'", "'.$_SESSION['ucinetid'].'")';

		$this->DB->query($sql);
	}

	function exists(){
		$sql = 'SELECT eid FROM events
				WHERE eid = "'.$this->eid.'"';

		$this->DB->query($sql);
		if(!$this->DB->isEmpty()) {
			return true;
		} else {
			return false;
		}
	}


}


?>