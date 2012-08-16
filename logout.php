<?php
require_once 'inc/standard.php';

$page = new Page('logout', PARTICIPANT);

$login = new Login();

$login->logout();

header('Location:'.$_SERVER['HTTP_REFERER']);

$page->setContent($bottom);
$page->buildPage();
?>