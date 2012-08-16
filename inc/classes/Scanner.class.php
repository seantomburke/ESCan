<?php

/**
 * This class contains the functions for scanning
 * @author Sean Burke, http://www.seantburke.com
 *
 */

class Scanner{

	public $db;
	public $error;

	function __construct(){
		$this->db = new DB();
	}

	/**
	 * determine whether a scan for an event exists or not
	 * @param string $barcode //scan id
	 * @param int $eid //event id
	 * @return boolean
	 */
	function exists($barcode, $eid){
		$sql = 'SELECT * FROM scans
				WHERE barcode = "'.$barcode.'" 
				AND eid = "'.$eid.'"';

		$this->db->query($sql);
		if(!$this->db->isEmpty())
		return true;
		else
		{
		$this->error = 'The scan for this event already exists';
		return false;
		}
	}

	/**
	 * Register a scan into the MySQL Table: scans
	 * @param string $barcode
	 * @return boolean
	 */
	function scan($barcode, $eid, $ucinetid){
	
		$event = new Event($eid);
		
		if(!$this->exists($barcode, $eid))
		{
			$sql = 'INSERT INTO scans
					SET barcode = "'.$barcode.'",
					eid = "'.$eid.'",
					volunteer = "'.$ucinetid.'",
					date = "'.NOW_DATE.'",
					time = "'.NOW_TIME.'"';
					
			$this->db->execute($sql);
			return true;
		}
		else
		{
			$this->error = 'The barcode #'.$barcode.' has already been scanned to the event '.$event->name;
			return false;
		}

	}
	
	function register($code)
	{
		$barcode = new Barcode($code);
		if($barcode->register())
			return true;
		else {
			$this->error = $barcode->error;
			return false;
		}
	}

	/**
	 * Displays an individual scan for the barcode and event
	 * @param string $barcode
	 * @param string $eid
	 * @return boolean
	 */
	function ticker($barcode, $eid)
	{
	
		
	}


}


?>