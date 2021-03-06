<?php

require_once 'inc/standard.php';

$DB = $GLOBALS['DB'];

foreach ($_GET as $key => $value) {
	//echo '$user[\''.$key.'\'] = '.$value.';<br>';
	$scan[$key] = trim(strip_tags($value));
}

if($scan['barcode'] && $scan['eid'])
{
	$barcode = new Barcode($scan['barcode']);
	$scanner = new Scanner();
	$event = new Event($scan['eid']);
	$errors = 1;
	
	if(!$event->exists())
	{
		$errors++;
		$output['message']['text'] = 'This event does not exist';
		$output['message']['status'] = 'error';
	}
	
	if(!$barcode->exists())
	{
		$errors++;
		$output['message']['text'] = 'The barcode <strong>#'.$barcode->code.'</strong> does not exist. Try scanning again.';
		$output['message']['status'] = 'error';
	}
	
	if($scanner->exists($barcode->code, $scan['eid']))
	{
		$errors++;
		$form = '<form action="register.php" method="GET"><input type="text" name="ucinetid" placeholder="UCInetID"><input type="submit"></form>'; //todo associate barcode if the took one with out registering.
		$name = ($barcode->getName()) ? 'The user <strong>'.$barcode->getName().'</strong>':'The barcode <strong>#'.$barcode->code.'</strong>';
		$extra = ($barcode->getName()) ? 'Nice Try.':'Please register this user\'s UCInetID below: '.$form;
		$output['message']['text'] = $name.' has already been scanned. '.$extra;
		$output['message']['status'] = 'error';
	}
	
	if($scanner->alreadyExists($barcode->getUCInetID(), $scan['eid']))
	{
		$errors++;
		$form = '<form action="register.php" method="GET"><input type="text" name="ucinetid" placeholder="UCInetID"><input type="submit"></form>'; //todo associate barcode if the took one with out registering.
		$name = ($barcode->getUCInetID()) ? 'The user <strong>'.$barcode->getUCInetID().'</strong>':'The barcode <strong>#'.$barcode->code.'</strong>';
		$extra = ($barcode->getUCInetID()) ? 'Nice Try.':'Please register this user\'s UCInetID below: '.$form;
		$output['message']['text'] = $name.' has already been scanned. '.$extra;
		$output['message']['status'] = 'error';
	}
		
	if($errors == 1)
	{
		$scanner->scan($barcode->code, $scan['eid'], $_SESSION['ucinetid']);
		$form = '<form action="register.php" method="GET"><input type="text" name="ucinetid" placeholder="UCInetID"><input type="submit"></form>'; //todo associate barcode if they took one with out registering.
		$name = ($barcode->getName()) ? 'The user <strong>'.$barcode->getName().'</strong>':'The barcode <strong>#'.$barcode->code.'</strong>';
		$welcome = ($barcode->getName()) ? 'Welcome.':'Please register this user\'s UCInetID below: '.$form;
		$output['message']['text'] = $name.' has been scanned in'.$welcome;
		$output['message']['status'] = 'success';
		
    	$sql = 'SELECT scans.*, users.name, users.ucinetid, users.major, users.level
    		FROM scans LEFT JOIN users
    		ON scans.barcode = users.barcode
    		WHERE scans.eid = "'.$scan['eid'].'"
    		AND scans.barcode = "'.$scan['barcode'].'"
    		ORDER BY date DESC, time DESC';
    		
    	$sql = 'SELECT barcodes.barcode, scans.*, users.name, users.ucinetid, users.major, users.level
    		FROM scans 
    		LEFT JOIN barcodes
    		    LEFT JOIN users
    		    ON barcodes.ucinetid = users.ucinetid
    		ON barcodes.barcode = scans.barcode
    		WHERE scans.eid = "'.$scan['eid'].'"
    		AND scans.barcode = "'.$scan['barcode'].'"
    		ORDER BY scans.date DESC, scans.time DESC';
    		
    	$DB->query($sql);
        $output['scan'] = $DB->resultToArray();
	}
	unset($scanner);
	unset($barcode);
	unset($event);
}
else{
    $output['message']['text'] = 'Must provide barcode id and event id';
	$output['message']['status'] = 'error';
}

echo json_encode($output);

?>