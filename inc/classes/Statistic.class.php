<?php

/**
 * Page Statistics
 *
 * This class provides code that will output pie graphs and bar graphs.
 * If you would like to create more types of graphs, they should go here.
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 * Statistic();
**/

class Statistic {

public $class;
public $title;
public $array;
public $where;
public $row;
public $color_offset;
public $color;
public $type;


function __construct($type, $class, $title, $array, $color_offset = 0)
{

	$this->type = $type;
	$this->class= $class;
	$this->title = $title;
	$this->array = $array;
	$this->color_offset = $color_offset;
	$this->color = new Color();
	
	$this->build();
}





function build()
{
/*
 * Statistics
 */
	if($this->type == 'pie')
	{
		$this->buildPie();
	}
	elseif($this->type == 'bar')
	{
		$this->buildBar();
	}
 
/* End Barcode Statistics */
}

function buildPie()
{
	$stat_colors = $this->color->getColorArray(count($this->array), $this->color_offset); //play with this number to change the colors of the graph
	$i=0;
	$total_count = 0;
	foreach($this->array as $key => $value)
	{
		//echo $key.' => '.$value;
		$stat_legend = '
					<div class="row">
						<font color="'.$stat_colors[$i].'">
						<span class="left">'.$key.'</span>
						<span class="right">'.$value.'</span>
						</font><br>
					</div>';
		$i++;
		$total_count += $value;
	}
	
	$stat_legend .= '<div class="separator"></div>
				<div class="row">
					<span class="left">Total</span>
					<span class="right">'.$total_count.'</span>
				</div>';
	$this->html = '';
	$this->html .= '
	 		<div class="row">
	 			<h2><span id="'.$this->class.'">'.$this->title.'</span></h2>
	 			<div id="'.$this->class.'_drop">
		 			<span id="'.$this->class.'_pie" class="pie">Loading...</span>
		 			<script>
		 			$("#'.$this->class.'_pie").sparkline(['.implode(', ', $this->array).'] , {
		 			 type:"pie", height:"250px", sliceColors:["'.implode('", "', $stat_colors).'"]
		 			 });
		 			$("#'.$this->class.'").click(function(){
		 			 $("#'.$this->class.'_drop").slideToggle();
		 			 });
		 			</script>
		 			<div class="legend_pie">
			 			<div class="legend_pie_inside">
			 				<div class="row"><span class="center">Legend</span></div>
			 				<div class="clear"></div>
			 				'.$stat_legend.'
			 			</div>
		 			</div>
		 		</div>
	 		</div>';
}

function buildBar()
{
	$bar_width = 35;
	$stat_colors = $this->color->getColorArray(count($this->array), $this->color_offset); //play with this number to change the colors of the graph
	$i=0;
	$stat_legend = '';
	$total_count = 0;
	foreach($this->array as $key => $value)
	{
		//echo $key.' => '.$value;
		$stat_legend .= '<div class="legend_bar_item" style="width:'.$bar_width.'px;">
							<span class="legend_bar_key">'.$key.'</span><br><span class="legend_bar_value">'.$value.'</span>
						</div>
						';
		$i++;
		$total_count += $value;
	}
					
	$this->html = '
			<div class="row">
				<h2><span id="'.$this->class.'">'.$this->title.'</span></h2>
				<div id="'.$this->class.'_drop">
					<span id="'.$this->class.'_bar" class="bar">Loading...</span>
					<script>
						$("#'.$this->class.'_bar").sparkline(['.implode(', ', $this->array).'] , {
						 	type:"bar", barColor: "'.$stat_colors[0].'", height:"250px", barWidth:'.$bar_width.'
						 });
						$("#'.$this->class.'").click(function(){
							$("#'.$this->class.'_drop").slideToggle();
							});
					</script>
					<div class="legend_bar">
						'.$stat_legend.'
					</div>		 		
		 		</div>
			</div>';
}


function display()
{
	return $this->html;
}

}
?>