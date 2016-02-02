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

	private $db;
	public $eid;
	public $name;
	public $host;
	public $prize;
	public $date;
	public $time;
	public $description;

	function __construct($eid = ''){
		$this->db = new DB();
		if($eid != '')
		{
			$this->eid = $eid;
			if($this->exists())
			{
				$sql = 'SELECT * FROM events
			WHERE eid = "'.$this->eid.'"';

				$result = $this->db->resultToSingleArray();

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

		$this->db->query($sql);
	}

	function exists(){
		$sql = 'SELECT eid FROM events
				WHERE eid = "'.$this->eid.'"';

		$this->db->query($sql);
		if(!$this->db->isEmpty()) {
			return true;
		} else {
			return false;
		}
	}

	function db_close(){
		$this->db->close();
	}


}


?>