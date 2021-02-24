<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';
    
    //display Home
    $app->get('/productlines', function ($request, $response, $args) {
         // $ProductLines = Array('Ski Boots','Ski Bindings','Goggles','Helmets','Snow Board Boots','Snow Board Bindings');
          $productLines = DB::query("SELECT category, photo FROM equipments WHERE id IN
          (SELECT MIN(id) FROM equipments GROUP BY category)");
        return $this->view->render($response, 'productlines.html.twig',['ProductLines' => $productLines]);
    });

    $app->get('/category/{cat:[A-Za-z0-9_ -]+}', function ($request, $response, $args){
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

    $app->get('/cart/{userId:[0-9]+}', function ($request, $response, $args) use($log){
        if (session_id()) {
            $cartList = DB::query("SELECT * FROM cartitems WHERE session_id=%s", session_id());
        }else{
            setFlashMessage("Oops! You have not put anything in shopping cart, keep shopping");
            return $response->withRedirect("/productionlines");
        }

        if(!$cartList){
            $response = $response->withStatus(404);
            return $this->view->render($response, '/error_notfound.html.twig');
        }
        return $this->view->render($response, 'cart.html.twig',['cartList'=>$cartList]);
    });

$app->post('/cart/{userId:[0-9]+}', function ($request, $response, $args) use($log){

    $userId =  $args['userId'];
    if(!isset($userId)){
        setFlashMessage("Please login first");
        return $response->withRedirect("/login");
    }
    $cartList = DB::query("SELECT * FROM cartitems WHERE userId=%d", $userId);
    if(!$cartList){
        $response = $response->withStatus(404);
        return $this->view->render($response, '/error_notfound.html.twig');
    }
    return $this->view->render($response, 'cart.html.twig',['cartList'=>$cartList]);
});
  
