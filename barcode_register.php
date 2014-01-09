<?php

require_once 'inc/standard.php';

foreach ($_GET as $key => $value) {
	//echo '$user[\''.$key.'\'] = '.$value.';<br>';
	$scan[$key] = trim(strip_tags($value));
}
	
//echo 'Registering...';

if($scan['barcode'])
{
    $DB = new DB();
	$barcode = new Barcode($scan['barcode']);
	$scanner = new Scanner();
	$errors = 1;

    if($barcode->validate())
    {
    	//echo 'Validating...';
    
    	if(!$barcode->exists())
    	{
    		$sql = 'INSERT INTO barcodes
    				SET barcode = "'.$barcode->code.'",
    				date = "'.NOW_DATE.'",
    				time = "'.NOW_TIME.'",
    				volunteer = "'.$_SESSION['ucinetid'].'"';
    		$DB->query($sql);
    		$output['message']['text'] = 'The barcode <strong>'.$barcode->code.'</strong> has been registered';
    		$output['message']['status'] = 'success';
    		
    		$sql = 'SELECT * FROM barcodes
    				WHERE barcode = "'.$barcode->code.'"';
    		$DB->query($sql);
            $output['scan'] = $DB->resultToArray();
    	}
    	else 
    	{
    		$output['message']['text'] = 'The barcode <strong>'.$barcode->code.'</strong> is already registered';
    		$output['message']['status'] = 'error';
    	}
    }
    else
    {
    	$output['message']['text'] = 'The barcode <strong>'.$barcode->code.'</strong> is not properly formatted';
    	$output['message']['status'] = 'error';
    }
}
else
{
    $output['message']['text'] = 'No Barcode Found';
	$output['message']['status'] = 'error';
}

echo json_encode($output);