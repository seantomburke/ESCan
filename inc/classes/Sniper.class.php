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
	private $self;
	
	/**
	*	Creates the Sniper
	*
	*/

	function Sniper()
	{
		
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->referer = $_SERVER['HTTP_REFERER'];
		$this->browser = $_SERVER['HTTP_USER_AGENT'];
		$this->snipe = $_GET['s'];
		$this->self = $_SERVER['PHP_SELF'];
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
		$DB = $GLOBALS['DB'];
		$message = strip_tags(trim(addslashes($message)));
		
		$sql = 'INSERT INTO errors (ucinetid, message, page, referer, browser, ip, status, date, time)
				VALUES ("'.$ucinetid.'", "'.$message.'", "'.$this->self.'", "'.$this->referer.'", "'.$this->browser.'", "'.$this->ip.'", "'.$status.'", "'.NOW_DATE.'", "'.NOW_TIME.'")';
		$DB->execute($sql);
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
}