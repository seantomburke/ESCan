<?php

/**
 * Mail Class
 * 
 * @desc This object will be used when emails need to be sent via PHP's mail() function.
 * @author Sean Burke, http://www.seantburke.com
 *
 */

class Mail{

	public $subject;
	public $body;
	public $headers;
	public $from;
	static public $illegals = array("\n", "\r","%0A","%0D","%0a","%0d","bcc:","Content-Type","BCC:","Bcc:","Cc:","CC:","TO:","To:","cc:","to:");
	
	/**
	 * @desc contsructor for the Mail.class.php object
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 */
	function __construct($to, $subject, $body)
	{
		
		$to = str_replace(Mail::$illegals, "", $to);
		$this->setTo($to);
		$this->setSubject($subject);
		$this->setBody($body);
		$this->from = EMAIL;
		$this->setHeaders();
	}

	function setTo($to)
	{
		$this->to = $to;
		
		//remove this part after debugging
		//$this->body .= $to;
		//$this->to = 'minimonkey456@yahoo.com';
	}

	function setBody($body)
	{
		//remove after debugging
		//$debug = ' <font size="-1">Message Sent To:'.$this->to.'</font><br>';
		$header = '<font size="-1">This message was sent from <a href="'.WEBSITE.'">'.PRODUCT.'</a>.</font><br><br>';

		$footer = '<br><br><font size="-1">For more learn more about '.PRODUCT.' please visit <a href="'.WEBSITE.'">'.PRODUCT.'</a></font>';

		$this->body .= $header.$body.$footer;
	}

	function setSubject($subject)
	{
		$this->subject = '['.PRODUCT.'] '.$subject;
	}

	function setHeaders($headers = '')
	{
		//Set up headers
		if($headers != '')
		{
			$this->headers = $headers;
		}
		else
		{
			$this->headers .= 'MIME-Version: 1.0'."\r\n";
			$this->headers .= 'From: esc.uci@gmail.com'."\r\n";
			$this->headers .= 'Reply-To: esc.uci@gmail.com'."\r\n";
			$this->headers .= 'X-Mailer: PHP/'.phpversion()."\r\n";
			$this->headers .= 'Content-type: text/html; rn; charset=iso-8859-1'."\r\n";
		}
	}

	/**
	 * @desc Send needs to be called inorder to send the email to the recipient 
	 */
	function send()
	{
		ini_set("sendmail_from", $this->from);
		mail($this->to, $this->subject, $this->body, $this->headers, $this->additional);
	}

}
?>