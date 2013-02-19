<?php
require_once 'inc/standard.php';

//$page = new Page($name, $css);
/*
if(!DBUSERNAME)
{
	die('Read the <a href="README.md">README.md</a> file to learn how to install this application');
}
*/
$page = new Page('index', ALL);
	
$page->setTitle('Home');
$page->setDescription(PRODUCT.'-'.DESCRIPTION);

$splash = '
	<div id="splash_left">
	<div id="splash_left_inside">
		<h1>'.PRODUCT.'</h1>
		<h4>'.DESCRIPTION.'</h4>
		<br>
		<h5>Inspiration for this project is credited to the <a href="http://esc.calpoly.edu">California Polytechnic State University Engineering Student Council</a>.</h5>
	</div>
	</div>
	';
$bottom = '<form action="register.php" method="GET">
			<div class="row">
			    <label class="fieldname" for="ucinetid">
			        UCInetID
			    </label>
			    <input class="textarea" autocapitalize="off" type="search" name="ucinetid" value="'.$person->ucinetid.'">
			</div>
			<div class="row">
			    <input type="submit" value="Register" name="submit_search">
			</div>
			</form>';
	
$box = new Box('Register',$bottom, 290, 200);

$signup = ' <div id="splash_right">'.$box->display('full').'</div>';
$content = $splash.$signup;

$page->setContent($content);
$page->buildPage();
?>