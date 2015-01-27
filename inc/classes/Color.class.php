<?php

/**
 * Page Class
 *
 * this class will contain all the output code such as headers and footers
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 *
 **/

class Color
{
	public $colors;
	
	function __construct()
	{
		$this->colors = array(
		'maroon' => '#800000',
		'red' => '#FF0000',
		'orange' => '#FF8000',
		'yellow' => '#FFFF00',
		'lime' => '#80FF00',
		'green' => '#00FF00',
		'cyan' => '#00FFFF',
		'bluegreen' => '#0080FF',
		'blue' => '#0000FF',
		'fusia' => '#8000FF',
		'magenta' => '#FF00FF',
		'crimson' => '#FF0080',
		'black' => '#000000',
		'white' => '#FFFFFF',
		'bludo' => '#4827AC',
		'purdo' => '#A48FC2',
		'greedo' => '#94f92B');
	}

	function getColorArray($count, $shift = 0)
	{
		$this->colors = array_values($this->colors);
		for ($i = 0; $i < $count; $i++) 
		{
			$j = $i;
			if($shift > 0)
			{
				$j = ($shift+($i*$shift)) % count($this->colors); //generate different colors shifted and modded
			}
			$result[$i] = $this->colors[$j];
		}
		return $result;
	}
	
}

?>