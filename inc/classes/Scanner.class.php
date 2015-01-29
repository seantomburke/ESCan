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
	 * determine whether a scan for an event exists or not
	 * @param string $barcode //scan id
	 * @param int $eid //event id
	 * @return boolean
	 */
	function alreadyExists($ucinetid, $eid){
	    
	   if(empty($ucinetid))
	   {
	       return false;
	   }
		$sql = 'SELECT * FROM scans
	            LEFT JOIN barcodes
	            ON scans.barcode = barcodes.barcode
				WHERE ucinetid = "'.$ucinetid.'" 
				AND eid = "'.$eid.'"';

		$this->db->query($sql);
		if(!$this->db->isEmpty())
		{
    		$this->error = 'The scan for this event already exists';
    		return true;
		}
		else
		{
    		return false;
		}
	}

	/**
	 * Register a scan into the MySQL Table: scans
	 * @param string $barcode
	 * @return boolean
	 */
	function scan($code, $eid, $volunteer){
	    
	    $barcode = new Barcode($code);
		$event = new Event($eid);
		
		if($this->alreadyExists($barcode->getUCInetID(), $eid))
		{
			$this->error = 'The user '.$barcode->getUCInetID().' has already been scanned to the event '.$event->name;
			return false;
		}
		elseif($this->exists($barcode->code, $eid))
		{
		    $this->error = 'The barcode #'.$barcode->code.' has already been scanned to the event '.$event->name;
			return false;
		}
		else
		{
			
			$sql = 'INSERT INTO scans
					SET barcode = "'.$barcode->code.'",
					eid = "'.$eid.'",
					volunteer = "'.$volunteer.'",
					date = "'.NOW_DATE.'",
					time = "'.NOW_TIME.'"';
					
			$this->db->execute($sql);
			return true;
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

	function db_close(){
		$this->db->close();
	}


}


?>