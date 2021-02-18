<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';

   

    //display Home
    $app->get('/productlines', function ($request, $response, $args) {
         // $ProductLines = Array('Ski Boots','Ski Bindings','Goggles','Helmets','Snow Board Boots','Snow Board Bindings');

          $ProductLines = DB::query("SELECT category, photo FROM equipments WHERE id IN
          (SELECT MIN(id) FROM equipments GROUP BY category)");
        foreach ($ProductLines as &$equip) {
        $category = $equip['category'];
        $photo = $equip['photo'];
        }

        return $this->view->render($response, 'productlines.html.twig',['ProductLines' => $ProductLines]);
    });

