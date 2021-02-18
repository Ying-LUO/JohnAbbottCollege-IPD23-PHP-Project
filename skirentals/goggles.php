<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';

    //display equipment_1 
    $app->get('/goggles', function ($request, $response, $args) {

        $equipList = DB::query("SELECT equipName, photo FROM equipments WHERE category='Goggles'");
        foreach ($equipList as &$equip) {
        $equipName = $equip['equipName'];
        $photo = $equip['photo'];
        }

        return $this->view->render($response, 'goggles.html.twig',['equipList' => $equipList]);
    });
