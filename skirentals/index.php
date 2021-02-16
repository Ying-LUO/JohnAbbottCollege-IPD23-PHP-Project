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
// //     //hosting on ipd23.com database connection setup
//     DB::$dbName = 'cp4996_skirentals';
//     DB::$user = 'cp4996_skirentals';
//     DB::$password = 'OS5a2m]qDfdK';
// } else {// local computer
    DB::$dbName = 'skirentalphp';
    DB::$user = 'skirentalphp';
    DB::$password = 'fu83K9WJLKSAbaob';
    DB::$host = 'localhost';
    DB::$port = 3333;
//}

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
//display home page
$app->get('/home',function ($request, $response, $args){
    
    return $this->view-> render($response, 'home.html.twig');
});

//display addequip form
$app->get('/addequip',function ($request, $response, $args){
    
    return $this->view-> render($response, 'addequip.html.twig');
});



$app->post('/addequip', function ($request, $response, $args) {
    $equipName = $request->getParam('equipname');
    $category = $request->getParam('category');
    $itemsInStock = $request->getParam('itemsInStock');
    $description = $request->getParam('description');
    //
    $errorList = [];
    if (strlen($description) < 2 || strlen($description) > 2000) {
        $errorList[] = "Item description must be 2-2000 characters long";
    }
    if (preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $equipName) !== 1) {
        $errorList[] = "Seller's name must be 2-100 characters long made up of letters, digits, space, comma, dot, dash";
    }

    
    if (!is_numeric($itemsInStock) || $itemsInStock < 0 || $itemsInStock > 99999999.99) {
        $errorList[] = "Initial bid price must be a number between 0 and 99,999,999.99";
    }
    //
    $valuesList = ['equipDescription' => $description, 'equipName' => $equipName, 
                    'category' => $category, 'inStock' => $itemsInStock];
    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'addequip.html.twig', ['errorList' => $errorList, 'v' => $valuesList]);
    } else { // STATE 3: success
        DB::insert('equipments', $valuesList);
        
        return $this->view->render($response, 'addequip_success.html.twig');
    }
});



// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();
