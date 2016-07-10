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

$app->register(new \Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => $config['db.options']
]);

$app->register(new \Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider(), [
    'orm.proxies_dir' => sys_get_temp_dir() . '/' . md5(__DIR__ . getenv('APPLICATION_ENV')),
    'orm.em.options' => [
        'mappings' => [
            [
                'type' => 'annotation',
                'use_simple_annotation_reader' => false,
                'namespace' => 'Tutorial\Entity',
                'path' => __DIR__ . '/src'
            ]
        ]
    ],
    'orm.proxies_namespace' => 'EntityProxy',
    'orm.auto_generate_proxies' => true,
    'orm.default_cache' => $config['db.options']['cache']
]);

$app->get('/', function() use ($app) {
    $controller = new UserController();
    return $controller->allUsers($app);
});
$app->run();