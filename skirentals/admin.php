<?php
    require_once 'vendor/autoload.php';
    require_once 'init.php';

    // admin interface example crud operations handling
    $app->get('/admin/users/list', function($request, $response, $args){
        $usersList = DB::query("SELECT * FROM users");
        return $this->view->render($response, 'admin/users_list.html.twig',['usersList'=>$usersList]);
    });

    // state 1: first display
    $app->get('/admin/users/{id:[0-9]+}/edit', function($request, $response, $args){
        $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $args['id']);
        if($user){
            $response = $response->withStatus(404);
            return $this->view->render($response, '/error_notfound.html.twig');
        }
        return $this->view->render($response, 'register.html.twig');
    });