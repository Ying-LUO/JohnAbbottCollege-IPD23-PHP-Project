<?php

    require_once 'vendor/autoload.php';
    require_once 'init.php';

    use Slim\Http\UploadedFile;

    // Define app routes below
    
    //display addequip form
    $app->get('/addequip',function ($request, $response, $args){

        return $this->view-> render($response, 'addequip.html.twig');
    });

    // $app->post('/addequip', function ($request, $response, $args) {
    //     $equipName = $request->getParam('equipname');
    //     $category = $request->getParam('category');
    //     $itemsInStock = $request->getParam('itemsInStock');
    //     $description = $request->getParam('description');
    //     //
    //     $errorList = [];
    //     if (strlen($description) < 2 || strlen($description) > 2000) {
    //         $errorList[] = "Item description must be 2-2000 characters long";
    //     }
    //     if (preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $equipName) !== 1) {
    //         $errorList[] = "Seller's name must be 2-100 characters long made up of letters, digits, space, comma, dot, dash";
    //     }


    //     if (!is_numeric($itemsInStock) || $itemsInStock < 0 || $itemsInStock > 99999999.99) {
    //         $errorList[] = "Initial bid price must be a number between 0 and 99,999,999.99";
    //     }
    //     //
    //     $valuesList = ['equipDescription' => $description, 'equipName' => $equipName,
    //         'category' => $category, 'inStock' => $itemsInStock];
    //     if ($errorList) { // STATE 2: errors - redisplay the form
    //         return $this->view->render($response, 'addequip.html.twig', ['errorList' => $errorList, 'v' => $valuesList]);
    //     } else { // STATE 3: success
    //         DB::insert('equipments', $valuesList);

    //         return $this->view->render($response, 'addequip_success.html.twig');
    //     }
    // });


    $app->post('/addequip', function ($request, $response, $args) use ($log){
        $equipName = $request->getParam('equipname');
        $category = $request->getParam('category');
        $itemsInStock = $request->getParam('itemsInStock');
        $description = $request->getParam('description');
        //

    //     echo "<pre>\n";
    // print_r($category);
    // echo "</pre>\n";
    // die("\nstop here");
        $errorList = [];
        if (strlen($description) < 2 || strlen($description) > 1000) {
            $errorList[] = "Product description must be 2-1000 characters long";
        }
        if (preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $equipName) !== 1) {
            $errorList[] = "Product's name must be 2-100 characters long made up of letters, digits, space, comma, dot, dash";
        }
    
    
        if (!is_numeric($itemsInStock) || $itemsInStock < 0 || $itemsInStock > 99999999) {
            $errorList[] = "In-stock must be a number";
            $log->debug("In-Stock must be a number between 0 and 99,999,999");
        }

        if ($category == 'Choose...') {
            $errorList[] = "You have to select product category";
            $log->debug(" product category is Null");
        }
        // Verify image
        $uploadedImagePath = null;
        $uploadedImage = $request->getUploadedFiles()['image'];
        if ($uploadedImage->getError() != UPLOAD_ERR_NO_FILE) { //was anything uploaded?
            print_r($uploadedImage->getError());
           
            $result = verifyUploadedPhoto($uploadedImagePath, $uploadedImage);
            if ($result !== TRUE) {
                $errorList[] = $result; 
            }
        }

       


        //
        $valuesList = [
            'equipDescription' => $description, 'equipName' => $equipName,
            'category' => $category, 'inStock' => $itemsInStock
        ];
        if ($errorList) { // STATE 2: errors - redisplay the form
            return $this->view->render($response, 'addequip.html.twig', ['errors' => $errorList, 'v' => $valuesList]);
                
            
        } else { // STATE 3: success
            if ($uploadedImagePath != null){
                $directory = $this->get('upload_directory');
                $uploadedImagePath = moveUploadedFile($directory, $uploadedImage);
            }
            
            
            DB::insert('equipments', ['equipDescription' => $description, 'equipName' => $equipName,
            'category' => $category, 'inStock' => $itemsInStock, 'photo' => $uploadedImagePath]);
    
            return $this->view->render($response, 'addequip_success.html.twig');
        }
    });
    
    
    // returns TRUE on success
    // returns a string with error message on failure
    function verifyUploadedPhoto(&$photoFilePath, $photo)
    {
    
        if ($photo->getError() != 0) {
            return "Error uploading photo " . $photo['error'];
        }
        if ($photo->getSize() > 1024 * 1024) { // 1MB
            return "File too big. 1MB max is allowed.";
        }
        $info = getimagesize($photo->file);
        if (!$info) {
            return "File is not an image";
        }
        // echo "\n\nimage info\n";
        // print_r($info);
        if ($info[0] < 130 || $info[0] > 1000 || $info[1] < 130 || $info[1] > 1000) {
            return "Width and height must be within 200-1000 pixels range";
        }
        $ext = "";
        switch ($info['mime']) {
            case 'image/jpeg':
                $ext = "jpg";
                break;
            case 'image/gif':
                $ext = "gif";
                break;
            case 'image/png':
                $ext = "png";
                break;
            default:
                return "Only JPG, GIF and PNG file types are allowed";
        }
        $baseName = "aaa";
        $photoFilePath = "uploads/" .  $baseName . "." . $ext;
    
        return TRUE;
    }
    
    function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
    
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    
        return $filename;
    }
    