<?php

/**
 * Navigation Class
 *
 * this class will create a nab bar that displays only what the user has access to.
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 **/

class Nav
{
	public $tabs;
	public $access;
	public $output;
	public $db;
	public $tab;
	public $title;

	/**
	 * Constructor for creating the Navigation Object
	 * 
	 * @param $access
	 */
	function __construct($access, $tab)
	{	
		$this->db = new DB();
		$this->tab = $tab;
		$this->access = ($access == '') ? 0:$access;
		
		if($access == 0)
		{
			$sql = 'SELECT tabs.title, pages.name, pages.url, tabs.public_only, tabs.access
			FROM tabs LEFT JOIN pages
			ON pages.name = tabs.page
			WHERE tabs.access <= "'.$this->access.'"
			OR tabs.public_only = "1"
			ORDER BY tid ASC';
		}
		else
		{
			$sql = 'SELECT tabs.title, pages.name, pages.url, tabs.public_only, tabs.access
			FROM tabs LEFT JOIN pages
			ON pages.name = tabs.page
			WHERE tabs.access <= "'.$this->access.'"
			AND tabs.public_only = "0"
			ORDER BY tid ASC';
		}

		$result = $this->db->query($sql);
			
		$tabs = $this->db->resultToArray($result);

		$highlight = ' id="highlight"';

		$this->output .= '
		<div id="navigation-section">
		<div id="nav">
		<ul>';
		if(count($tabs) > 0)
		{
			foreach($tabs as $tab)
			{
				$active = ($this->tab == $tab['name']) ? $highlight:'';
				$this->output .= '
					<li><a class="menu-item" '.$active.' href="'.$tab['url'].'?s=nav">'.$tab['title'].'</a></li>';
			}
		}
		else 
		{
			$this->output .= '
				<li><a class="menu-item" href="index.php">Home</a></li>';
		}	
		
			
		$this->output .= '</ul></div></div>';
	}
	
		public function addNewTab($tab, $title, $only, $access = 0)
		{
			$this->tab = $tab;
			if(!$this->tabExists())
			{
				$sql = 'INSERT INTO tabs SET
						access = "'.$access.'",
						page = "'.$tab.'",
						title = "'.$title.'",
						public_only = "'.$only.'"';
	
				$result = $this->db->query($sql);
			}
			else
			{
				$sql = 'UPDATE tabs SET
						access = "'.$access.'",
						page = "'.$this->tab.'",
						title = "'.$title.'",
						public_only = "'.$only.'"
						WHERE page = "'.$this->tab.'"';
	
				$result = $this->db->query($sql);
	
			}
		}
	
		private function tabExists()
		{
			$sql = "SELECT t.page FROM tabs AS t";
				
			$result = $this->db->query($sql);
			$tabs_array = $this->db->resultToArray();
	
			if(count($tabs_array) > 0)
			{
				foreach($tabs_array as $row)
				{
					if($this->tab == $row['page'])
					{
						return true;
					}
				}
			}
			return false;
		}
	

	/**
	 * Display the DropMenu. This needs to be called in order to see the output of the drop menu in HTML format
	 * @return string $ouput
	 */
	public function display()
	{
		return $this->output;
	}

}

?>