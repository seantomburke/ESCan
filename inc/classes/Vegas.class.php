<?php 

class Vegas
{
	/**
	 * Vegas Class
	 *
	 * This class will display the winner of each event with jQuery animations
	 * //TODO not done yet, maybe next year 1/12/2012
	 * @author Sean Burke, http://www.seantburke.com
	 *
	 *
	 **/

	private $array;
	private $page;
	
	function __construct($page, $array)
	{
		$this->page = $page; //needed in order to initialize js
		$this->array = $array;
	}
	
	
	function build()
	{
		$this->page->setJSInitial('$(".slot_item").hide();');
		
		$slot = ' <div class="slot">
					<input id="crank" type="submit" value="Crank">
					<span class="display">';
		$i=0;
		foreach($this->array as $item)
		{
		$i++;
		$slot .= '<input id="slot'.$i.'" class="slot_item" type="text" value="'.$item.'" READONLY>';
		}
		$slot .=  '<script>
		$("#crank").click(function () 
		{';
		$i = 0;
		foreach($this->array as $item)
		{
		$i++;
		$delay = 1000;
		$delay1 = $delay * $i;
		$delay2 = $delay * $i + ($delay/2);
		$slot .=  '
			$("#slot'.$i.'").("slow", function(){
				$("#slot'.$i.'").slideUp("slow");
			});';
		}
		$slot .= '
		});
		</script>';
						
		$slot .= '</span>
				</div>
				';
		return $slot;
	}
	

}

?>