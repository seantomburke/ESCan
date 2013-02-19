<?php
require_once 'inc/standard.php';

//$page = new Page($name, $css);
$page = new Page('instructions', ALL);
$box = new Box('Instructions');
$box->setBadge('Get Started!', 'register.php');

$bottom = '
			<div class="row">
				<h2>Step 1: </h2>
				<h3>Get your Wristband</h3>
			</div>
			<div class="row">
				<img src="images/wristband_model.png" class="instructions">
			</div>
			<div class="disclaimer">
				All throughout E-Week, '.ORGANIZATION.' will be distributing wristbands for all participants of E-Week. You will be registered with '.PRODUCT.' by one of the '.PRODUCT.' volunteers at the registration booth.
			</div>
			<div class="clear"></div>
			<div class="separator"></div>
			
			<div class="row">
				<h2>Step 2:</h2>
				<h3>Attend E-Week Events</h3>
			</div>
			<div class="row">
				<img src="images/attend_events.png" class="instructions">
			</div>
			<div class="disclaimer">
				By attending more E-Week events, you increase your chance of getting selected in the opportunity drawing. View all of the E-Week Events by visiting the <a href="events.php">Events page</a>.</div>
			<div class="clear"></div>
			<div class="separator"></div>
			<div class="row">
				<h2>Step 3:</h2>
				<h3>Win Prizes</h3>
			</div>
			<div class="row">
				<img src="images/ipod.png" class="instructions">
			</div>
			<div class="clear"></div>
			<div class="disclaimer">
				For each event that you attend, you will be entered into the opportunity drawing. Your UCInetID must be registered with '.PRODUCT.' in order for you to win prizes. We will give out prizes for the student who attends the most events, and also to randomly selected participants. The more you participate, the higher your chances of winning a prize!
			</div>
			<div class="clear"></div>
			<div class="separator"></div>
			<div class="row">
				<h2>How does '.PRODUCT.' Work?</h2>
			</div>
			<div class="row">
				<img src="images/scanner.png" class="instructions">
			</div>
			<div class="disclaimer">
				'.PRODUCT.' will provide '.ORGANIZATION.' with demographics about the participants in order to improve events for the future. For each event we can determine how many students are attending, which major, what year and which time of day has the most activity during an event. In return for your participation in '.PRODUCT.', we provide prizes! It\'s a win-win for everyone!
			</div>
			<div class="clear"></div>';
			
$box->setContent($bottom);
$page->setContent($box->display('half'));
$page->buildPage();
?>