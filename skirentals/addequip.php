<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';

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