<?php

require_once 'inc/standard.php';

foreach ($_GET as $key => $value) {
	//echo '$user[\''.$key.'\'] = '.$value.';<br>';
	$scan[$key] = trim(strip_tags($value));
}

if($scan['barcode'])
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
		$extra = ($barcode->getName()) ? 'Welcome.':'Please register this user\'s UCInetID below: '.$form;
		$output['message']['text'] = $name.' has already been scanned. '.$extra;
		$output['message']['status'] = 'error';
	}
		
	if($errors == 1)
	{
		$scanner->scan($barcode->code, $scan['eid'], $_SESSION['ucinetid']);
		$form = '<form action="register.php" method="GET"><input type="text" name="ucinetid" placeholder="UCInetID"><input type="submit"></form>'; //todo associate barcode if they took one with out registering.
		$name = ($barcode->getName()) ? 'The user <strong>'.$barcode->getName().'</strong>':'The barcode <strong>#'.$barcode->code.'</strong>';
		$extra = ($barcode->getName()) ? 'Welcome.':'Please register this user\'s UCInetID below: '.$form;
		$output['message']['text'] = $name.' has been scanned in'.$welcome;
		$output['message']['status'] = 'success';
		$js_slide_down = true;
		echo json_encode($output);
	}
	else
	{
		echo json_encode($output);
	}
}


?>