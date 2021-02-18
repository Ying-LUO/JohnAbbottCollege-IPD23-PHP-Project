<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';


//display equipment_2
    $app->get('/skiboots', function ($request, $response, $args) {

        $equipList = DB::query("SELECT equipName, photo FROM equipments WHERE category='Ski Boots'");
        foreach ($equipList as &$equip) {
        $equipName = $equip['equipName'];
        $photo = $equip['photo'];
        }

        return $this->view->render($response, 'skiboots.html.twig',['equipList' => $equipList]);
    });
