<?php
session_start();
/**
 * Page Class
 *
 * @description this class will contain all the output code such as headers and footers. A session is started to utilize php sessions, and will be used to validate login. More code can be found in the Login.class.php and Session.class.php files. This class constructs a new page template and stores the page in the MySQL Table "pages". It also has a DB object from DB.class.php available, a Login object from Login.class.php, and a Session Object from Sessions.class.php. Each page consists of several parts: HTML, header, navigation, content and footer. Each function builds a part of the page after it is called. See the buildPage() for more details.
 * @author Sean Burke, http://www.seantburke.com
 *
 * Page($name, $access);
 * setCSS($css);
 * setMessage($message, $class);
 * setTitle($title);
 * setContent($content_bottom);
 * buildPage();
 *
 **/

class Page
{

	public $ID; 				//MySQL unique identifier
	public $access; 			//access of each page. See 'inc/constants.inc.php' for access constants
	public $alert;				//class for the $message (e.g. 'failure' , 'success')
	public $banner; 			//html for the $message
	public $css;				//additional css file for the page
	public $content;			//the HTML code for the completed page
	public $content_bottom;		//content of the bottom section; exludes HTML headers, nav, etc.
	public $description;		//Description of the page
	public $extra_meta;			//Any extra meta data for the page to include in the header
	public $footer;				//the footer HTMl
	public $graph;				//if you are going to use graphs on this page, you set this
	public $google;				//Google Analytics Code
	public $html_tag;			//if any extra information needs to be added to <html>
	public $html;				//the html code between the <head> tag
	public $header;				//the header content html code
	public $isPhone;			//is this being viewed on an iPhone?
	public $js;					//Initial JavaScript without the Document portion
	public $js_inital;			//any initial javascript that needs to be run, e.g. div.hide()
	public $logo_src;			//src of the logo image
	public $keywords;			//keywords for the page, (not really important)
	public $message;			//the alert message at the top of the page, needs $banner+$alert
	public $name;				//name of the page
	public $navigation;			//the navigation html code
	public $pageviews;			//count of page views
	public $tab;				//the tab, if any, that the page belongs to
	public $title;				//the <title> of the page
	public $url;				//url of the page

	//objects
	public $DB;					// DB Object from 'inc/classes/DB.class.php'
	//public $session;			// Session Object from 'inc/classes/Session.class.php'
	public $login;				// Login Object from 'inc/classes/Login.class.php'
	public $nav;				//the NavBar object
	public $sniper;				// Stores all error messages and/or hackers


	/**
	 * Constructs page object
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param string $name, Integer $access
	 * @return void
	 * @desc This constructor will construct the Page object by taking in the Name of the page, and the Access Level. See 'inc/constants.inc.php' for access constants.
	 */
	function __construct($name, $access = ALL)
	{
		$this->DB = new DB();
		//$this->session = new Session();
		$this->login = new Login();
		$this->sniper = new Sniper();
		$this->name = $name;
		$this->access = $access;
		$this->setGoogleAnalytics();

		$sql = "SELECT p.* FROM pages AS p
				WHERE name = '$name'";

		if(!($this->DB->query($sql)))
		die('Could not get page: '.$this->DB->getError());
			
		$page = $this->DB->resultToSingleArray($result);

		if ($page == '')
		{
			$sql = "INSERT INTO pages (name, access)
					VALUES ('$name', '$access')";
			if(!($this->DB->query($sql)))
			die('Could not insert page: '.$this->DB->getError());
		}
		
		if(strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone'))
			$this->isPhone = true;
		else 
			$this->isPhone = false;
			
		$this->name = strtolower($page['name']);
		$this->access = $access;
		$this->ID = $page['ID'];
		$this->css = $page['css'];
		$this->url = $page['url'];
		$this->tab = $page['tab'];
		$this->pageviews = $page['views'];
		$this->description = $page['description'];
		$this->content = $page['content'];
		$this->title = ucfirst($page['name']);
		$this->logo_src = WEBSITE.'/images/logo200.png';
		
		$this->nav = new Nav($_SESSION['access'], $page['tab']);
	}


	/**
	 * Sets Message
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @return void
	 * @param string $message, string $class
	 * @desc Sets the text of the message and the class which could be 'failure' or 'success' depending on the CSS associated. 'failure' will display a red alert and 'success' will display a green alert.
	 */

	public function setMessage($message, $class)
	{
		if(is_array($message))
		{
			$this->message = implode(', ', $message);
		}
		else {
			$this->message = $message;
		}
		$this->alert = $class;
		
		//store the message with the sniper class for debugging
		$this->sniper->storeMessage($message, $_SESSION['ucinetid'], $class);
	}

	/**
	 * Sets Title
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param $title
	 * @return void
	 * @desc Sets the title
	 */

	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Sets CSS
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param string $css
	 * @return void
	 * @desc Sets the css. Do not need to include the extension, e.g. enter $css = 'global' instead of 'global.css';
	 */

	public function setCSS($css)
	{
		$this->css = $css;
	}

	/**
	 * Sets description
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param string $description
	 * @return void
	 * @desc Sets the description of the page
	 */

	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Sets Keywords for the page
	 * @param string $keywords
	 */

	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	public function setJSInitial($js)
	{
		$this->js .= $js;
	}
	
	public function buildJSInitial()
	{
		$this->js_initial = '<script type="text/javascript">
		$(document).ready(function() {';
		$this->js_initial .= $this->js;
		$this->js_initial .= ' });</script>';
	}

	public function addExtraMeta($extra_meta)
	{
		$this->extra_meta .= $extra_meta;
	}

	public function setContent($content_bottom)
	{
		$this->content_bottom = $content_bottom;
	}
	
		
	public function setTab($tab, $public_only = '0')
	{
		$this->tab = $tab;
		//$this->nav->addNewTab($this->tab, $this->title, $public_only, $this->access);
	}
	
	
	public function startGraph()
	{
		$this->graph = '<script type="text/javascript" src="http://omnipotent.net/jquery.sparkline/2.1.1/jquery.sparkline.min.js"></script>';
	}
	
	public function setGoogleAnalytics()
	{
		$this->google = '<script type="text/javascript">
		
		  var _gaq = _gaq || [];
		  _gaq.push([\'_setAccount\', \'UA-25773399-2\']);
		  _gaq.push([\'_trackPageview\']);
		
		  (function() {
		    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
		    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
		    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		
		</script>';
	}

	private function updatePageView()
	{
		$this->pageviews += 1;
	}

	private function updateURL()
	{
		$this->url = $_SERVER['PHP_SELF'];
	}

	private function updateAll()
	{
		$this->updatePageView();
		$this->updateURL();
	}

	private function writeSQL()
	{
		$content_bottom = addslashes($this->content_bottom);
		$title = addslashes($this->title);
		$content = addslashes($this->content);
		$description = addslashes($this->description);

		$sql = "UPDATE pages SET
				description = '$description',
				access = '$this->access',
				url = '$this->url',
				css = '$this->css',
				title = '$title',
				views = '$this->pageviews',
				tab = '$this->tab'
				WHERE name = '$this->name'";

		if(!($result = $this->DB->query($sql)))
		die('Could not update page: '.$this->DB->getError());

	}
	/**
	 * Outputs the header html code
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param $css, $html_tag, $title, $description, $keywords, $extra_header_tags,
	 * @return void
	 * @desc This method will construct the header after each parameter is set
	 * @access private
	 */

	private function buildHTML()
	{
		if($this->isPhone)
		{
			$css = 'iPhone';
			$this->addExtraMeta('
			<link rel="apple-touch-startup-image" href="images/fb-splash.png">
			<link rel="apple-touch-icon-precomposed" href="images/facebook.png"/>
			<link media="only screen and (max-device-width: 480px)"
			    href="/css/iPhone.css" type="text/css" rel="stylesheet" />
			
			<meta name="apple-mobile-web-app-status-bar-style" content="black" />
			<meta name="apple-mobile-web-app-capable" content="yes" />
			<meta name="viewport" content="user-scalable=no,width=device-width,height=device-height" />
			<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />			
			<meta name="apple-touch-fullscreen" content="yes" />
			<meta name="viewport" content="width=320" />');
		}	
		else
			$css = 'global';
			
		$css = ($css) ? '<link rel=stylesheet type="text/css" href="css/'.$css.'.css"  >' : '';

		$this->html =
		'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
			<title>'.$this->title.' - '.PRODUCT.'</title>
			<link rel="shortcut icon" href="/favicon.ico">
			<link rel="icon" href="/favicon.ico" type="image/x-icon">
			<meta property="og:title" content="'.PRODUCT.'" />
			<meta property="og:type" content="website" />
			<meta property="og:url" content="'.$_SERVER['PHP_SELF'].'" />
			<meta property="og:image" content="'.$this->logo_src.'" />
			<meta property="og:description" content="'.$this->description.'" />
			<meta property="fb:app_id" content="203055886388488" />
			<meta property="fb:admin" content="hawaiianchimp" />
			'.$this->extra_meta.'
			<meta http-equiv="X-UA-Compatible" content="chrome=1" />
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta name="description" content="'.$this->description.'">
			<meta name="keywords" content="'.$this->keywords.'">
			<script src="http://code.jquery.com/jquery-latest.js"></script>
			<script type="text/javascript" src="http://www.google.com/jsapi"></script>
			<script type="text/javascript">
				google.load(\'visualization\', \'1\', {packages: [\'corechart\']});
			</script>
			<script type="text/javascript" src="javascript/escan.js?t='.time().'"'
		    .$this->graph
		    .$this->js_initial
		    .$css
		    .$this->google
		.'</head>';
	}
	private function buildMessage()
	{
		if(isset($this->message))
		{
			$this->banner = '
			<div id="delay">
				<div id="message" class="message2 '.$this->alert.'">
					<span id="x" class="'.$this->alert.'">X</span> '.$this->message.'
				</div>
			</div>
			
			<script>
				$("#x").click(function(){
					$("#message").fadeOut("slow");
				});
				$("#delay").delay(20000).fadeOut("slow");
			</script>';
		}
	}

	private function buildHeader()
	{
		$this->buildMessage();
		
		$this->header = ' <body>
		<div id="wrapper">
		<div id="banner">'
		.$this->banner.'
		</div>
		<div id="header">
			<div id="logo">
				<a href="index.php?r=logo">
				<img src="'.$this->logo_src.'" alt="'.PRODUCT.'">
				<div id="logoname"><h2>'.PRODUCT.'</h2></div>
				</a>
			</div>
			'.$this->login->loginFormMini('login','login.php','loginmini', $this->isPhone).'
			<div id="today"><h2>Today: '.date('M d, h:i A', strtotime(NOW_DATE.' '.NOW_TIME)).'</h2></div>
		</div>';
	}

	/**
	 * Builds the navigation bar
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @return void
	 * @desc This function will build the navigation bar
	 * @access private
	 */
	private function buildNav()
	{	
		$this->navigation = $this->nav->display();
	}

	/**
	 * builds Content with the top and bottom
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param $content_top, $content_bottom
	 * @return void
	 * @access private
	 * @desc This function will build the content of the page
	 */
	private function buildContent()
	{
		$this->content = '
		<div id="container">
 		'.$this->content_bottom.'</div>';
	}


	/**
	 * Outputs the footer html code
	 *
	 * @author Sean Burke, http://www.seantburke.com
	 * @param $pageviews
	 * @return void
	 * @access private
	 * @desc This function will build the footer of the page
	 */
	private function buildFooter()
	{
		$this->footer = '
		</div>
		
		<div id="copyright">
			<p>Created for <a href="'.WEBSITE.'">'.ORGANIZATION.'</a> | 
			Developed by <a href="http://www.seantburke.com/?r=escan">Sean Thomas Burke</a> | 
			&copy; '.date('Y').' '.PRODUCT.' | 
			Views: '.$this->pageviews.'</p>
		</div>
		
		</div>
		
		<script type="text/javascript">
		
		  var _gaq = _gaq || [];
		  _gaq.push([\'_setAccount\', \'UA-25773399-3\']);
		  _gaq.push([\'_trackPageview\']);
		
		  (function() {
		    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
		    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
		    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		
		</script>
		
		</body>
		</html>';

	}



	/**
	 * @author Sean Burke, http://www.seantburke.com
	 * @desc This method will build the entire page using each private build function above
	 * @example buildPage()
	 * @return void
	 * @access public
	 * @copyright 2011 Sean Burke
	 */
	public function buildPage()
	{
		$this->updateAll();
		$this->buildJSInitial();
		$this->buildHTML();
		$this->buildHeader();
		$this->buildNav();
		$this->buildContent();
		$this->buildFooter();
		$this->content = $this->html.$this->header.$this->navigation.$this->content.$this->footer;
		echo $this->content;
		$this->writeSQL();
		exit;
	}
}

?>