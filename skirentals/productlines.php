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

         $selectedItem = DB::queryFirstRow("SELECT * FROM equipments WHERE id=%d", $args['id']);

       return $this->view->render($response, 'itemdetails.html.twig', ['selectedItem' =>  $selectedItem]);
    });

    $app->post('/itemdetails/{id:[0-9]+}', function ($request, $response, $args) use ($log){
        // TODO: WHAT IF NO SESSION ID?
        if($args['id']){
            $selectedEquip = DB::queryFirstRow("SELECT * FROM equipments WHERE id=%d", $args['id']);
        }
        $errorList = [];
        if (session_id() && $selectedEquip) {
            $rentalType = $request->getParam('rentalType');
            $quantity = $request->getParam('quantity');

            if ($quantity > $selectedEquip['inStock']) {
                $errorList[] = "Out of Stock now";
                $quantity = '';
            }
            if (!isset($rentalType)) {
                $errorList[] = "Please choose a rentalType";
            }

            if ($errorList) {
                $log->error(sprintf("Failed to add item into cart: equipment id %d, uid=%d", $args['id'], $_SERVER['REMOTE_ADDR']));
                return $this->view->render($response, 'contact.html.twig', [
                    'errors' => $errorList,
                    'selectedItem' => [
                        'quantity' => $quantity
                    ]
                ]);
            } else {
                // add to cart
                $newCartItem = ['session_id' => session_id(), 'equipId' => $args['id'], 'quantity' => $quantity, 'rentalType' => $rentalType];
                $itemInCart = DB::queryFirstRow("SELECT * FROM cartitems WHERE session_id=%s AND equipId=%d AND rentalType=%s", session_id(), $args['id'], $rentalType);
                if($itemInCart){
                    DB::update('cartitems', ['quantity' => $itemInCart['quantity']+$quantity], "id=%d", $itemInCart['id']);
                    $_SESSION['cart'] += $quantity;
                    $log->debug(sprintf("Equipment id %d quantity changed in cart with session id %s, uid=%d, cart=%d", $args['id'], session_id(), $_SERVER['REMOTE_ADDR'], $_SESSION['cart']));
                }else{
                    DB::insert('cartitems', $newCartItem);
                    $_SESSION['cart'] += $quantity;
                    $log->debug(sprintf("New item added into cart: equipment id %d with session id %s, uid=%d, cart=%d", $args['id'], session_id(), $_SERVER['REMOTE_ADDR'], $_SESSION['cart']));
                }
                setFlashMessage("Add into cart successfully");
                return $response->withRedirect("/category/" . $selectedEquip['category']);
            }
        }
    });


    $app->get('/cart', function ($request, $response, $args) use($log){
        if (session_id()) {
            $cartList = DB::query("SELECT * FROM cartitems AS C LEFT JOIN equipments AS E ON C.equipId = E.id WHERE session_id=%s", session_id());
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

    $app->post('/cart/add/{id:[0-9]+}', function ($request, $response, $args) use($log){
        // equipment id from argument
        if($args['id'] == 0){
            setFlashMessage("No item selected");
        }else{
            $newCartItem = ['session_id' => session_id(), 'equipId' => $args['id'], 'quantity' => 1];
            $itemInCart = DB::queryFirstRow("SELECT * FROM cartitems WHERE session_id=%s AND equipId=%d AND rentalType=%s", session_id(), $args['id'], 'month');
            if($itemInCart){
                DB::update('cartitems', ['quantity' => $itemInCart['quantity']+1], "id=%d", $itemInCart['id']);
                $_SESSION['cart'] += 1;
                $log->debug(sprintf("Equipment id %d quantity changed in cart with session id %s, uid=%d, cart:%d", $args['id'], session_id(), $_SERVER['REMOTE_ADDR'], $_SESSION['cart']));
            }else{
                DB::insert('cartitems', $newCartItem);
                $_SESSION['cart'] += 1;
                $log->debug(sprintf("New item added into cart: equipment id %d with session id %s, uid=%d, cart:%d", $args['id'], session_id(), $_SERVER['REMOTE_ADDR'], $_SESSION['cart']));
            }
            setFlashMessage("Add into cart successfully");
            return $response->withRedirect("/cart");
        }
    });

    $app->post('/cart/remove/{id:[0-9]+}', function ($request, $response, $args) use($log){
        if($args['id'] == 0){
            setFlashMessage("No item in cart to delete");
        }else{
            $todelete = DB::queryFirstRow("SELECT * FROM cartitems WHERE id=%d", $args['id']);
            if(!$todelete){
                $log->error(sprintf("No found item in cart to delete id=%d successfully, uid=%d", $args['id'], $_SERVER['REMOTE_ADDR']));
                $response = $response->withStatus(404);
                return $this->view->render($response, '/error_notfound.html.twig');
            }
            DB::delete('cartitems', "id=%d", $args['id']);
            $log->debug(sprintf("Remove id=%d from cart successfully, uid=%d", $args['id'], $_SERVER['REMOTE_ADDR']));
            setFlashMessage("Remove from cart Successfully");
            return $response->withRedirect("/cart");
        }
    });

    $app->post('/cart/checkout', function ($request, $response, $args) use($log){

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
  
