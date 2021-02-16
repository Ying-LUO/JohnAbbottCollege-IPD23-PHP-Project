<?php

//session_start();

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

$log->pushProcessor();

$log->pushProcessor(function ($record) {
    //$record['extra']['user'] = isset($_SESSION['user']) ? $_SESSION['user']['username'] : '=anonymous=';
    //$record['extra']['ip'] = $_SERVER['REMOTE_ADDR'];
    return $record;
});

// if (strpos($_SERVER['HTTP_HOST'], "ipd23.com") !== false) {
//     //hosting on ipd23.com database connection setup
    DB::$dbName = 'cp4996_skirentals';
    DB::$user = 'cp4996_skirentals';
    DB::$password = 'OS5a2m]qDfdK';
// } else {// local computer
    // DB::$dbName = 'day02people';
    // DB::$user = 'day02people';
    // DB::$password = 'sIjlCel0a0oENBhu';
    // DB::$host = 'localhost';
    // DB::$port = 3333;
// }

// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true
]];
$app = new \Slim\App($config);

// Fetch DI Container
$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
        'cache' => dirname(__FILE__) . '/tmplcache',
        'debug' => true, // This line should enable debug mode
    ]);
    //
    $view->getEnvironment()->addGlobal('test1','VALUE');
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

// Define app routes below

$app->get('/home',function ($request, $response, $args){
    
    return $this->view-> render($response, 'home.html.twig');
});

// $app->post('/home',function ($request, $response, $args){
//     $name = $request->getParam('name');
//     $age = $request->getParam('age');
    
//     $errorList = [];
//     if (strlen($name) < 2 || strlen($name) > 50) {
//         $errorList[] = "Name must be 2-50 characters long";
//         $name = "";
//     }
//     if (filter_var($age, FILTER_VALIDATE_INT) === false || $age < 0 || $age > 150) {
//         $errorList[] = "Age must be a number between 0 and 150";
//         $age = "";
//     }
//     //
//     if($errorList){
//         $valueList = ['name'=>$name, 'age'=>$age];
//         return $this->view-> render($response, 'home.html.twig', ['errorListZ' => $errorList, 'v' => $valueList]);
//     } else {
//         DB::insert('people', [
//             'name' => $name,
//             'age' => $age
//           ]);
//         return $this->view-> render($response, 'home_success.html.twig');
//     }


    
    
// });



// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();
