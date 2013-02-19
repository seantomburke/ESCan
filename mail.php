<?php
date_default_timezone_set('America/Los_Angeles');
require_once('inc/classes/class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();
$body             = file_get_contents('contents.html');
$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "mail.smtp.com"; // SMTP server
$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 587;                   // set the SMTP port for the GMAIL server
$mail->Username   = "esc.uci";  // GMAIL username
$mail->Password   = "dlmtavkhjcsb";            // GMAIL password

$mail->SetFrom('esc.uci@gmail.com', 'ESC UCI');

$mail->AddReplyTo("esc.uci@gmail.com","ESC UCI");

$mail->Subject    = "PHPMailer Test Subject via smtp (Gmail), basic";

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->Body = $body;

$address = "stburke@uci.edu";
$mail->AddAddress($address, "Sean Burke");

$mail->AddAttachment("images/phpmailer.gif");      // attachment
$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
    
?>