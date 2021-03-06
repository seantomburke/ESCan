<?php

/**
 * This class contains the functions for registering, associating and determining barcodes
 * @author Sean Burke, http://www.seantburke.com
 *
 */

class Barcode{

	public $DB;
	public $code;
	public $error;
	public $login;
	public $REGEX = "/^[E|S|C|A|N][0-9]{5,5}$/"; //used in new validate method
	public $BARCODE_LENGTH = 6;		//OLD These need to be changed to validate different types of barcodes, this length is 6
	public $PREFIX = 'E';			//OLD This is the prefix that will validate the barcode

	function __construct($code){
		$this->code = $code;
		$this->DB = $GLOBALS['DB'];
		$this->login = new Login();
	}
	
	function isEmpty()
	{
		return $this->code == '';
	}
	
	function checkForRegistration($ucinetid){
		$temp = false;
		if($this->validate())
		{
			if($this->isNonAssociated($ucinetid))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->error = 'Invalid Barcode <strong>#'.$this->code.'</strong>';
			return false;
		}
		return ($this->validate()) && ($this->exists()) && ($this->isNonAssociated($ucinetid));
	}
	
	/**
	 * determine whether a barcode is valid or not
	 * OLD METHOD, NEW METHOD IS validate()
	 * @param string $barcode
	 * @return boolean
	 */
	function validate2(){
		if(strlen($this->code) != $this->BARCODE_LENGTH)
		{
			//echo 'Strlen('.strlen($this->code).')';
			$this->error = 'Barcode <strong>'.$this->code.'</strong> must be of length: '.$this->BARCODE_LENGTH;
			return false;
		}
		$pos = strpos($this->code, $this->PREFIX);
		
		if ($pos === false) {
		    $this->error = 'The prefix <strong>'.$this->PREFIX.'</strong> was not found in the barcode <strong>'.$this->code.'</strong>';
		    return false;
		} 
		
		if ($pos !== 0) {
		    $this->error = 'The prefix of <strong>'.$this->code.'</strong> is not valid';
		    return false;
		}
		return true;
	}
	/**
	 * determine whether a barcode is valid or not
	 * NEW METHOD! USES REGEX PATTERN
	 * @param string $barcode
	 * @return boolean
	 */
	function validate(){
		return true; //use this to bypass barcode validation

		$value = preg_match($this->REGEX, $this->code);
		if($value != 1)
		{
			$this->error = 'The barcode <strong>'.$this->code.'</strong> is not valid';
		}

		return $value;
	}

	/**
	 * determine whether a barcode exists or not
	 * @param string $barcode
	 * @return boolean
	 */
	function exists(){
		$sql = 'SELECT barcode FROM barcodes
				WHERE barcode = "'.$this->code.'"';
		$this->DB->query($sql);
		if(!$this->DB->isEmpty())
		{
			return true;
		}
		else
		{
			$this->error = 'The barcode <strong>'.$this->code.'</strong> was not properly pre-registered. Please put this wristband aside, and distribute a new one.';
			return false;
		}
	}
	
	function registerAndAssociate($user)
	{
	    if($this->register())
	    {
	        $this->associate($user);
	    }
	}

	/**
	 * Register a barcode into the MySQL Table: barcodes
	 * @param string $barcode
	 * @return booleanSE
	 */
	
	function register()
	{	
		//echo 'Registering...';
		if($this->validate())
		{
			//echo 'Validating...';
			if(!$this->isEmpty())
			{
				if(!$this->exists())
				{
					$sql = 'INSERT INTO barcodes
							SET barcode = "'.$this->code.'",
							date = "'.NOW_DATE.'",
							time = "'.NOW_TIME.'",
							volunteer = "'.$_SESSION['ucinetid'].'"';
					$this->DB->execute($sql);
					return true;
				}
				else 
				{
					$this->error = 'The barcode <strong>'.$this->code.'</strong> is already registered';
					return false;
				}
			}
			else {
				$this->error = 'There is no Barcode';
				return false;
			}
			return false;
		}
		else
		{
			return false;
		}
	}

	function isNonAssociated($current_user = '')
	{
		$sql = 'SELECT ucinetid
				FROM users
				WHERE barcode = "'.$this->code.'"';
		$this->DB->query($sql);
		$user = $this->DB->resultToSingleArray();
		
		if($this->DB->isEmpty())
		{
		return true;
		}
		elseif($current_user == $user['ucinetid'])
		{
		return true;
		}
		else
		{
		$this->error = 'The barcode <strong>#'.$this->code.'</strong> is already associated with <strong>'.$user['ucinetid'].'</strong>';
		return false;
		}
	}

	/**
	 * Associate a barcode with a user
	 * @param string $barcode
	 * @param string $ucinetid
	 * @return boolean
	 */
	function associate($ucinetid)
	{
		if($this->exists())
		{
			if($this->isNonAssociated($ucinetid))
			{
				if($this->login->exists($ucinetid))
				{
					$sql = 'UPDATE barcodes
						SET ucinetid = "'.$ucinetid.'"
						WHERE barcode = "'.$this->code.'"';
					$this->DB->query($sql);

					$sql = 'UPDATE users
						SET barcode = "'.$this->code.'"
						WHERE ucinetid = "'.$ucinetid.'"';
					$this->DB->query($sql);
					return true;
				}
				else
				{
					$this->error = 'Login <strong>'.$ucinetid.'</strong> does not exist';
					return false;
				}
			$this->error = 'This is already associated';
			}
			return false;
		}
		$this->error = 'Barcode does not exist';
		return false;
	}
	
	function unassociate($ucinetid)
	{
		if($this->login->exists($ucinetid))
		{
			$sql = 'UPDATE barcodes
				SET ucinetid = ""
				WHERE ucinetid = "'.$ucinetid.'"';
			$this->DB->query($sql);

			$sql = 'UPDATE users
				SET barcode = ""
				WHERE ucinetid = "'.$ucinetid.'"';
			$this->DB->query($sql);
			return true;
		}
		else
		{
			return false;
		}
		$this->error = 'Login <strong>'.$ucinetid.'</strong> does not exist';
		return false;
	}

	/**
	 * Enter description here ...
	 * @param string $barcode
	 * @return Array $userArray
	 */
	function getUserArray($code)
	{
		$sql = 'SELECT *
				FROM users
				WHERE barcode = "'.$code.'"';

		$this->DB->query($sql);
		$userArray = $this->DB->resultToSingleArray();
		if($this->DB->isEmpty())
		{
			$this->error = 'The barcode <strong>'.$this->barcode.'</strong> is not associated with a UCInetID';
			return false;
		}
		else
		{
			return $userArray;
		}
	}
	
	function getUCInetID()
	{
		$sql = 'SELECT b.ucinetid
				FROM barcodes as b
				WHERE barcode = "'.$this->code.'"';
	
		$this->DB->query($sql);
		$result = $this->DB->resultToSingleArray();
		if($this->DB->isEmpty())
		{
			$this->error = 'The barcode <strong>'.$this->barcode.'</strong> is not associated with a UCInetID';
			return false;
		}
		else
		{
			return $result['ucinetid'];
		}
	}
	
	function getName()
	{
		$sql = 'SELECT name
				FROM users
				WHERE barcode = "'.$this->code.'"';
	
		$this->DB->query($sql);
		$result = $this->DB->resultToSingleArray();
		if($this->DB->isEmpty())
		{
			$this->error = 'The <strong>'.$this->barcode.'</strong> is not associated with a UCInetID';
			return false;
		}
		else
		{
			return $result['name'];
		}
	}



}


?>