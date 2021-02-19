<?php
session_start();

require_once "vendor/autoload.php";
require_once "init.php";


/*
// STATE 1: first display of the form
$app->get('/', function ($request, $response, $args) {
    $auctionList = DB::query("SELECT * FROM auctions ORDER BY id DESC");
    return $this->view->render($response, 'index.html.twig', ['list' => $auctionList]);
});

/*
// STATE 1: first display of the form
$app->get('/', function ($request, $response, $args) {
    $equipList = DB::query("SELECT e.id, e.equipName
            , e.equipDescription, e.photo
            , r.rateByMonth, r.rateBySeason
        FROM equipments AS e
        JOIN rentalrates AS r
        ON e.id = r.equipId
        WHERE e.inStock >0 ");
    return $this->view->render($response, 'index.html.twig', ['equipmentList' => $equipList]);
});
*/

// Define app routes below
require_once 'register.php';
require_once 'login.php';
require_once "admin.php";
require_once "addequip.php";
require_once "skibindings.php";
require_once "skiboots.php";
require_once "goggles.php";
require_once "helmets.php";
require_once "snowboardboots.php";
require_once "snowboardbindings.php";
require_once "productlines.php";
require_once "cart.php";

// Run app - must be the last operation
// if you forget it all you'll see is a blank page



$app->run();

