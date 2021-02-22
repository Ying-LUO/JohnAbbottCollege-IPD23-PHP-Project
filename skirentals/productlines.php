<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';

    
    //display Home
    $app->get('/productlines', function ($request, $response, $args) {
         // $ProductLines = Array('Ski Boots','Ski Bindings','Goggles','Helmets','Snow Board Boots','Snow Board Bindings');

          $productLines = DB::query("SELECT category, photo FROM equipments WHERE id IN
          (SELECT MIN(id) FROM equipments GROUP BY category)");

        return $this->view->render($response, 'productlines.html.twig',['ProductLines' => $productLines], ['userSession' => $_SESSION['user'] ] );
    });

    $app->get('/category/{cat:[A-Za-z0-9_ -]+}', function ($request, $response, $args) use ($log){
      $cat =  $args['cat'];


      if (!in_array($cat, ['skiBoots', 'skiBindings', 'goggles','snowboardBindings', 'helmets', 'snowboardBoots'])) { // TODO add more
            
          throw new Slim\Exception\NotFoundException($request, $response); // this will cause 404
      }
      $equipList = DB::query("SELECT * FROM equipments WHERE category=%s", $cat);
      return $this->view->render($response, 'category.html.twig', ['equipList' => $equipList]);
  });

  $app->get('/itemdetails/{id:[0-9]+}', function ($request, $response, $args) {

     $selectedItem = DB::queryFirstRow("SELECT * FROM equipments WHERE id=%i", $args['id']);

   return $this->view->render($response, 'itemdetails.html.twig', ['selectedItem' =>  $selectedItem]);
});

  
  
