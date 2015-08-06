<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);
	date_default_timezone_set('Asia/Jakarta');

	require_once('config.php');
	require_once('class.gamedev.php');

	$page = (isset($_GET['p']) && !empty($_GET['p']) && ctype_alpha($_GET['p'])) ? $_GET['p'] : 'formulir';
	$subpage = (isset($_GET['sp']) && !empty($_GET['sp']) && ctype_alpha($_GET['sp'])) ? $_GET['sp'] : 'hasil';
	$callback = (isset($_GET['callback']) && ctype_alnum($_GET['callback'])) ? $_GET['callback'] : '';
	$gamedev = new GameDev;
	// print_r($callback);
	switch ($page) {
		case 'api':
			header('content-type: application/json; charset: utf-8');
			$apiOptions = array(
				'subpage' => $subpage,
				'callback' => $callback	
			);
			$gamedev->get_api($apiOptions);
			break;
		
		default:
			$gamedev->get_page($page);
			break;
	}

?>