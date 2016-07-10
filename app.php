<?php

require __DIR__ . "/vendor/autoload.php";

use Silex\Application;
use Tutorial\Controller\UserController;

$config = require_once __DIR__ . "/config/config.php";
if (!$config || !is_array($config)) {
    throw new Exception('Error processing config file', 1);
}

$app = new Application();
$app["debug"] = true;

$app->get('/', function() use ($app) {
    $controller = new UserController();
    return $controller->allUsers();
});
$app->run();