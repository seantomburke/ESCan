<?php

require_once 'inc/standard.php';
$DB = new DB();

foreach ($_GET as $key => $value) {
	//echo '$user[\''.$key.'\'] = '.$value.';<br>';
	$scan[$key] = trim(strip_tags($value));
}

$sql = 'SELECT barcodes.*, users.name, users.ucinetid, users.major, users.level
    		FROM barcodes 
    		LEFT JOIN users ON barcodes.ucinetid = users.ucinetid
    		ORDER BY barcodes.date DESC, barcodes.time DESC
    		'.$limit;
    		
$DB->query($sql);
$output['scans'] = $DB->resultToArray();

echo json_encode($output);
?>