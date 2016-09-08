<?php

/**
 * Linna App
 *
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2016, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */
use Linna\Database\Database;
use Linna\Session\DatabaseSessionHandler;
use Linna\Session\Session;
use Linna\Http\Router;
use Linna\Http\FrontController;
use Linna\DI\DIContainer;
use Linna\DI\DIResolver;
use Linna\Autoloader;

//use \Linna\Storage\Storage;
//use \Linna\Storage\MysqlPDOAdapter;

/**
 * Set a constant that holds the project's folder path, like "/var/www/".
 * DIRECTORY_SEPARATOR adds a slash to the end of the path
 */
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);

/**
 * Set a constant that holds the project's "application" folder, like "/var/www/application".
 */
define('APP', ROOT . 'App' . DIRECTORY_SEPARATOR);

/**
 * The folder that is visible to public, users will only have access to that folder so nobody can have a look into
 * "/src" or other folder inside your application or call any other .php file than index.php inside "/public".
 */
define('URL_PUBLIC_FOLDER', 'public');

/**
 * The protocol. Don't change unless you know exactly what you do.
 */
define('URL_PROTOCOL', 'https://');

/**
 * The domain. Don't change unless you know exactly what you do.
 */
define('URL_DOMAIN', $_SERVER['HTTP_HOST']);

/**
 * The sub-folder. Leave it like it is, even if you don't use a sub-folder (then this will be just "/").
 */
define('URL_SUB_FOLDER', str_replace(URL_PUBLIC_FOLDER, '', dirname($_SERVER['SCRIPT_NAME'])));

/**
 * The final, auto-detected URL (build via the segments above). If you don't want to use auto-detection,
 * then replace this line with full URL (and sub-folder) and a trailing slash.
 */
define('URL', URL_PROTOCOL . URL_DOMAIN . URL_SUB_FOLDER);

/**
 * define namespace under app will build
 */
define('APP_NAMESPACE', 'app');

//load configuration from config file
require APP . '/config/config.php';

//load routes.
require APP . '/config/routes.php';


//composer autoload
require '../vendor/autoload.php';

//linna autoloader, load application class
//for more information see http://www.php-fig.org/psr/psr-4/
$loader = new Autoloader();
$loader->register();

$loader->addNamespaces([
    ['App\Models', __DIR__ . '/../App/Models'],
    ['App\Views', __DIR__ . '/../App/Views'],
    ['App\Controllers', __DIR__ . '/../App/Controllers'],
    ['App\Templates', __DIR__ . '/../App/Templates'],
    ['App\Mappers', __DIR__ . '/../App/Mappers'],
    ['App\DomainObjects', __DIR__ . '/../App/DomainObjects'],
]);

$DIContainer = new DIContainer();

$DIContainer->sessionHandler = function () {
    $dbase = Database::connect();
    return new DatabaseSessionHandler($dbase);
};

/*
$DIContainer->UserModel = function(){

    $dbase = Database::connect();
    $userMapper = new App\Mappers\UserMapper($dbase);

    return new App\Models\UserModel($userMapper);
};

$DIContainer->UserController = function($model) {

    $session = Session::getInstance();
    $login = new Linna\Auth\Login($session);

    return new App\Controllers\UserController($model, $login);
};

$DIContainer->UserView = function($model) {

    $session = Session::getInstance();
    $login = new Linna\Auth\Login($session);

    return new App\Controllers\UserView($model, $login);
};
*/


/*
$pdo = new MysqlPDOAdapter(
        'mysql:host=localhost;dbname=test;charset=utf8mb4',
        'root',
        'cagiva',
        array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING));

$storage = new Storage();
$storage->pdo = $pdo->connect();
*/

$sessionHandler = $DIContainer->sessionHandler;

//set session handler
//optional if not set, app will use php session standard storage
Session::setSessionHandler($sessionHandler());

//se session options
Session::withOptions(array(
    'expire' => 1800,
    'cookieDomain' => URL_DOMAIN,
    'cookiePath' => URL_SUB_FOLDER,
    'cookieSecure' => false,
    'cookieHttpOnly' => true
));

//get session instance
$session = Session::getInstance();

//start router
$router = new Router($_SERVER['REQUEST_URI'], $appRoutes, array(
    'basePath' => URL_SUB_FOLDER,
    'badRoute' => 'E404'
        ));

//get route
$route = $router->getRoute();

//get model linked to route
$routeModel = '\App\Models\\'.$route->getModel();
//get view linked to route
$routeView = '\App\Views\\'.$route->getView();
//get controller linked to route
$routeController = '\App\Controllers\\'.$route->getController();

//create dipendency injection resolver
$DIResolver = new DIResolver();

//add unresolvable class to DIResolver
$DIResolver->addUnResolvable('\Linna\Database\Database', Database::connect());
$DIResolver->addUnResolvable('\Linna\Session\Session', Session::getInstance());
        
//resolve model
$model = $DIResolver->resolve($routeModel);

//resolve view
$view = $DIResolver->resolve($routeView);

//resolve controller
$controller = $DIResolver->resolve($routeController);


//start front controller
$frontController = new FrontController($router->getRoute(), $model, $view, $controller);

//run
$frontController->run();

//get front controller response
$frontController->response();
