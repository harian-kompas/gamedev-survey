<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Asia/Jakarta');

/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim.php';
require_once 'config.php';
require_once 'gamedev.class.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */


// GET route
$app->get('/', function () {
	$gamedev = new GameDev;
    $gamedev->get_data_visualization_page();
});

$app->get('/hasil', function () {
    $gamedev = new GameDev;
    $gamedev->get_data_visualization_page();
});

$app->get('/direktori(/:alfabet)', function ($alphabet = 'a') {
    $gamedev = new GameDev;
    $gamedev->get_studios_directory_page($alphabet);
});

$app->get('/formulir(/:kunci)', function ($key = null) {
    $gamedev = new GameDev;
    $gamedev->get_entry_data_form($key);
});

$app->get('/api(/:type)', function ($type = '') {
    // print_r($type);
	$gamedev = new GameDev;
	$options = array(
		'callback' => trim($_GET['callback'])
	);
    if ($type === 'hasil') {
        $gamedev->get_api_results($options);
    } else if ($type === 'isi') {
        $gamedev->get_api_others($options);
    } else {
        $gamedev->get_api_results($options);
    }
        
});

// POST route
$app->post('/formulir/post', function () {
   $gamedev = new GameDev;
   $gamedev->save_users_inputs();
});

$app->post('/formulir/kunci', function () {
    $gamedev = new GameDev;
    $gamedev->save_users_key_request();
});

// PUT route
$app->put('/put', function () {
    echo 'This is a PUT route';
});

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete('/delete', function () {
    echo 'This is a DELETE route';
});

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
