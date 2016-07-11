<?php

date_default_timezone_set('America/Sao_Paulo');

require __DIR__ . "/vendor/autoload.php";

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Tutorial\Controller\UserController;
use Tutorial\Provider\DatabaseUserProvider;

$config = require_once __DIR__ . "/config/config.php";
if (!$config || !is_array($config)) {
    throw new Exception('Error processing config file', 1);
}

$app = new Application();
$app["debug"] = true;

$app['security.jwt'] = [
    'secret_key' => 'Very_secret_key',
    'life_time'  => 86400,
    'options'    => [
        'username_claim' => 'name', // default name, option specifying claim containing username
        'header_name' => 'Authorization', // default null, option for usage normal oauth2 header
        'token_prefix' => 'Bearer',
    ]
];

$app['users'] = function () use ($app) {
    return new DatabaseUserProvider($app);
};

$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\SecurityJWTServiceProvider());

$app['security.firewalls'] = array(
    'login' => [
        'pattern' => 'login|register|oauth',
        'anonymous' => true,
    ],
    'secured' => array(
        'pattern' => '^.*$',
        'logout' => array('logout_path' => '/logout'),
        'users' => $app['users'],
        'jwt' => array(
            'use_forward' => true,
            'require_previous_session' => false,
            'stateless' => true,
        )
    ),
);

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

$app->post('/login', function(Request $request) use ($app){
    $vars = json_decode($request->getContent(), true);

    try {
        if (empty($vars['_username']) || empty($vars['_password'])) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $vars['_username']));
        }

        /**
         * @var $user User
         */
        $user = $app['users']->loadUserByUsername($vars['_username']);

        if (! $app['security.encoder.digest']->isPasswordValid($user->getPassword(), $vars['_password'], '')) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $vars['_username']));
        } else {
            $response = [
                'success' => true,
                'token' => $app['security.jwt.encoder']->encode(['name' => $user->getUsername()]),
            ];
        }
    } catch (UsernameNotFoundException $e) {
        $response = [
            'success' => false,
            'error' => 'Invalid credentials',
        ];
    }

    return $app->json($response, ($response['success'] == true ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST));
});

$app->run();