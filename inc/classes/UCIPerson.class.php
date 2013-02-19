<?php

/**
 * Page UCIPerson Class
 *
 * this class can only be used for UCI Students, it will access the directory 
 * and construct an object based on the users UCInetID.
 * 
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 *
 **/

class UCIPerson
{
	public $db;
	public $login;
	public $name;
	public $nickname;
	public $department;
	public $title;
	public $home_page;
	public $picture_url;
	public $major;
	public $level;
	public $ucinetid;
	public $user_array;
	public $search_url;
	public $email;
	public $is_valid;
	public $isEngineer;
	public $error;
	public $engineer_array;
	

	function __construct($id)
	{
		$id = $this->clean($id);
		$this->db = new DB();
		$this->login = new Login();
		$this->engineer_array = array('Engr AE','Engr BM','EngrBMP','EngrChm','Engr CE','EngrCpE','CSE','Engr EE','EngrEnv','Enr MSE','Engr ME');
		//have this set in the VarArray class instead
		
		
		if($this->validate($id))
		{
			$this->ucinetid = $id;
			$this->search_url = 'http://directory.uci.edu/index.php?uid='.$this->ucinetid.'&form_type=plaintext';
				
			$data = file_get_contents($this->search_url);

			$data = trim($data);
			$error_string = strpos($data, 'rror:');

			$pos = ($error_string) ? $error_string-1: strpos($data, '<body>')+8;
				

			$data = substr($data, $pos);
			$data_array = explode('<br/>', $data);
			$this->user_array = array();
				
			foreach($data_array as $line)
			{
				$temp = explode(':', $line);
				$extra = ($temp[2])? ':'.$temp[2]:'';
				$this->user_array[strtolower(trim($temp[0]))] = trim($temp[1]).$extra;
			}
				
			$this->error = $this->user_array['error'];
			$this->name = ucwords(strtolower($this->user_array['name']));
			$this->email = $this->user_array['e-mail'];
			$this->nickname = $this->user_array['nickname'];
			$this->title = $this->user_array['title'];
			$this->department = $this->user_array['department'];
			$this->home_page = $this->user_array['home page'];
			$this->picture_url = $this->user_array['picture url'];
			$this->level = $this->user_array['student\'s level'];
			$this->isEngineer = $this->isEngineer();
			$this->major = $this->user_array['major'];
			//$this->convertMajor();
			$this->convertEmail();
			$this->convertLevel();
			$this->is_valid = !(isset($this->user_array['error']));
			if(!$this->is_valid)
			{
				$this->error = '<strong>'.$this->ucinetid.'</strong> is not a valid UCInetID';
			}
		}
		return $this->is_valid;
	}

	private function clean($id)
	{
		//if user enters email address, strip the '@uci.edu' portion
		if($posi = strpos($id, '@uci.edu'))
		$id = substr($id, 0, $posi);

		//strip any other dangerous tags
		$id = strtolower($id);
		$id = strip_tags($id);
		$id = trim($id);
			
		return $id;
	}

	private function validate($id)
	{
		if($id > 8)
		{
			$this->is_valid = false;
			$this->error = $id.' is not a valid UCInetID';
			return false;
		}
		
		if(!(preg_match('/^[a-zA-Z0-9]+$/', $id , $array, PREG_OFFSET_CAPTURE)))
		{
			$this->is_valid = false;
			$this->error = $id.' is not a valid UCInetID';
			return false;
		}
		return true;

	}

	function isValid()
	{
		return $this->is_valid;
	}
	
	function getBarcode()
	{
		$sql = 'SELECT barcode
				FROM users
				WHERE ucinetid = "'.$this->ucinetid.'"';
	
		$this->db->query($sql);
		$result = $this->db->resultToSingleArray();
		if($this->db->isEmpty())
		{
			$this->error = 'The user <strong>'.$this->ucinetid.'</strong> does not have a barcode';
			return false;
		}
		else
		{
			return $result['barcode'];
		}
	}
	
	function getName()
	{
		return $this->name;
	}

	function getNickname()
	{
		return $this->nickname;
	}

	function getUCInetID()
	{
		return $this->ucinetid;
	}

	function getMajor()
	{
		return $this->major;
	}

	function getDepartment()
	{
		return $this->level;
	}

	function getHomePage()
	{
		return $this->home_page;
	}

	function getPictureURL()
	{
		return $this->picture_url;
	}

	function getUserArray()
	{
		return $this->user_array;
	}

	function getLevel()
	{
		return $this->level;
	}

	function getSearchURL()
	{
		return $this->search_url;
	}

	function getEmail()
	{
		return $this->email;
	}

	function isEngineer()
	{
		if(stripos($this->title, 'engineer'))
		{
			$this->isEngineer = true;
		}
		if(stripos($this->department, 'engineer'))
		{
			$this->isEngineer = true;
		}
		if(in_array($this->major, $this->engineer_array))
		{
			$this->isEngineer = true;
		}
		return $this->isEngineer;
	}

	private function convertEmail()
	{
		if(!(strstr($this->email, '@')))
		$this->email = $this->ucinetid.'@uci.edu';
	}

	private function convertLevel()
	{
		switch ($this->level)
		{

			case 'FR':
				$this->level = 'Freshman';
				break;
			case 'SO':
				$this->level = 'Sophomore';
				break;
			case 'JR':
				$this->level = 'Junior';
				break;
			case 'SR':
				$this->level = 'Senior';
				break;
			case 'GR':
				$this->level = 'Graduate';
				break;
			default:
				if($this->isEngineer())
					$this->level = 'Faculty/Staff';
				break;
		}
	}

	private function convertMajor()
	{
		switch ($this->major)
		{

			case 'Engr AE':
				$this->major = 'Aerospace Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr BM':
				$this->major = 'Biomedical Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrBMP':
				$this->major = 'Biomedical Engineering: Premedical';
				$this->isEngineer = true;
				break;
			case 'EngrChm':
				$this->major = 'Chemical Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr CE':
				$this->major = 'Civil Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrCpE':
				$this->major = 'Computer Engineering';
				$this->isEngineer = true;
				break;
			case 'CSE':
				$this->major = 'Computer Science Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr EE':
				$this->major = 'Electrical Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrEnv':
				$this->major = 'Environmental Engineering';
				$this->isEngineer = true;
				break;
			case 'Enr MSE':
				$this->major = 'Material Science Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr ME':
				$this->major = 'Mechanical Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrMAE':
				$this->major = 'Mechanical Aerospace Engineering';
				$this->isEngineer = true;
				break;
		
		}
	}
}

?>