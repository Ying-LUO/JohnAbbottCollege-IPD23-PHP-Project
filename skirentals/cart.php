<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';


    //display cart
    $app->get('/cart', function ($request, $response, $args) {

        return $this->view->render($response, 'cart.html.twig');
    });
