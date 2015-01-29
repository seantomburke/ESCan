<?php

/**
 * Box Class
 *
 * this class will output the display for the box content, (the thing with the stripped header)  
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 *
 **/

class Box
{
	public $content;
	public $title;
	public $badge;
	public $badge_url;
	public $width;
	public $height;
	public $header_height;

	function __construct($title, $content = '', $width = 500, $height = 'auto')
	{
		$this->header_height = 50;
		$this->content = $content;
		$this->title = $title;
		$this->width = $width;
		$this->height = $height;
		return $this->display();
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}
	public function setBadge($badge, $url)
	{
		$this->badge = $badge;
		$this->badge_url = $url;
	}

	public function display($type = 'half')
	{
		if(!is_string($this->height))
		{
			$container_height = $this->height - $this->header_height;
			$container_height .= 'px';
		}
		else
		{
		$container_height = $this->height;
		}
		
		$output = '
		<div class="box" style="width:100%; height:'.$this->height.'">
			<div class="header_container" style="height: '.$this->header_height.'px">
				<h2 class="title">'.$this->title.'</h2>
		    	<span class="box_badge">
		        	<a href="'.$this->badge_url.'">
		        	'.$this->badge.'</a>
		        </span>
		    </div>            
			<div class="box_wrapper" style="height: '.$container_height.'">
				<div class="box_inside">';
				
		$output.= $this->content;
		
		$output .= '
				</div>
			</div>
		</div>';
		if($type == 'half')
		{
			return '<div id="container_wrapper">'.$output.'</div>';
		}
		elseif($type == 'full')
		{
			return $output;
		}
		else
		{
			return $output;
		}
	}
	
}

?>