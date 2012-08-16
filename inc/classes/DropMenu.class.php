<?php

/**
 * Class DropMenu
 *
 * this class will be able to create HTML <select> dropmenus with an Array as an input
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 **/

class DropMenu
{
	public $name;
	public $selected;
	public $keys;
	public $values;
	public $array;

	/**
	 * Constructor for creating the DropMenu Object
	 * 
	 * @param string $name
	 * @param Array $array
	 * @param string $selected
	 * @param string $class
	 */
	function __construct($name, $array, $selected = '', $class = '')
	{
		$this->name = $name;
		$this->class = $class;
		$this->selected = $selected;
		$this->array = $array;
		$this->keys = @array_keys($array);
		$this->values = @array_values($array);
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setSelected($seleted)
	{
		$this->selected = $selected;
	}

	/**
	 * Display the DropMenu. THis needs to be called in order to see the output of the drop menu in HTML format
	 * @return string $ouput
	 */
	public function display()
	{

		$output = '<select id='.$this->name.' class="'.$this->class.'" name="'.$this->name.'">';
		if(is_array($this->array))
		{
			foreach ($this->array as $key => $value)
				$output .=	'<option value="'.$key.'">'.$value.'</option>';
		}
		else 
		{
			$output .= '<option value="">No Results</option>';
		}

		$output .=	'</select>';

		if ($this->selected)
		{
			$output = preg_replace("|\"$this->selected\">*|", "\"$this->selected\" SELECTED>", $output);
		}

		return $output;
	}

}

?>