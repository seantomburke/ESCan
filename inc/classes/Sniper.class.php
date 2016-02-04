<?php

/**
 * Page Sniper
 *
 * this class will track potential hackers and improve upon certain errors
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 * Sniper();
**/

class Sniper
{
	private $ip;
	private $referer;
	private $browser;
	private $timestamp;
	private $snipe;
	private $db;
	private $phpself;
	
	/**
	*	Creates the Sniper
	*
	*/

	function Sniper()
	{
		$server = [
			'REMOTE_ADDR' 		=> isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 0,
			'PHP_SELF' 			=> isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 0,
			'HTTP_REFERER' 		=> isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 0,
			'HTTP_USER_AGENT' 	=> isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 0,
		];
		
		$this->db = new DB();
		$this->ip = $server['REMOTE_ADDR'];
		$this->phpself = $server['PHP_SELF'];
		$this->referer = $server['HTTP_REFERER'];
		$this->browser = $server['HTTP_USER_AGENT'];
		$this->snipe = isset($_GET['s']) ? $_GET['s']:'';
	}
	
	public function getIP()
	{
		return $this->ip;
	}
	public function getBrowser()
	{
		return $this->browser;
	}
	public function getSnipe()
	{
		return $this->snipe;
	}
	
	/**
	*	Inserts message into the Database
	*
	*/
	private function insert($message, $ucinetid, $status)
	{
		$message = strip_tags(trim(addslashes($message)));
		
		$sql = 'INSERT INTO errors (ucinetid, message, page, referer, browser, ip, status, date, time)
				VALUES ("'.$ucinetid.'", "'.$message.'", "'.$this->phpself.'", "'.$this->referer.'", "'.$this->browser.'", "'.$this->ip.'", "'.$status.'", "'.NOW_DATE.'", "'.NOW_TIME.'")';
		$this->db->execute($sql);
	}
	
	/**
	*	Stores a message or array of messages from another object, usually an error message
	*
	*/
	public function storeMessage($message, $ucinetid = '', $status = 'default')
	{
		if(is_array($message))
		{
			foreach ($message as $row)
			{
				$this->insert($row, $ucinetid, $status);
			}
		}
		else 
		{
			$this->insert($message, $ucinetid, $status);
		}
	}

	public function db_close(){
		$this->db->close();
	}
}