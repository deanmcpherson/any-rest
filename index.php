<?php

require_once('api.php');
/**Configuration object**/
/**
** $conf = new StdClass;
** $conf->collections = array('test', 'test2');
**/
$api = new API();
$app = $api->app;

$app->get('/', function(){
	echo file_get_contents('test.html');
	exit();
});

$api->run();