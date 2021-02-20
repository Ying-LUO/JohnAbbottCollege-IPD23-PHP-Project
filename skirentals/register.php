<?php

require_once 'vendor/autoload.php';
require_once 'init.php';

use Slim\Http\UploadedFile;

// Define app routes below

//display addequip form
$app->get('/register',function ($request, $response, $args){
    return $this->view-> render($response, 'register.html.twig');
});


$app->post('/register', function ($request, $response, $args) use ($log) {
    if (isset($_SESSION['user'])) {
        return $response->withHeader('Location', '/');
    }

    $firstName = $request->getParam('firstName');
    $lastName = $request->getParam('lastName');
    $userName = $request->getParam('userName');
    $email = $request->getParam('email');
    $phone = $request->getParam('phone');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    $city = $request->getParam('city');
    $street = $request->getParam('street');
    $province = $request->getParam('province');
    $postCode = $request->getParam('postCode');
    $isAgree = $request->getParam('isAgree');
    //
    $errorList = [];
    if (strlen($firstName) < 2 || strlen($firstName) > 50) {
        $errorList[] = "First Name must be 2-50 characters long";
        $registerInfo['firstName'] = '';
    }
    if (strlen($lastName) < 2 || strlen($lastName) > 50) {
        $errorList[] = "Last Name must be 2-50 characters long";
        $registerInfo['lastName'] = '';
    }
    if (strlen($userName) < 2 || strlen($userName) > 30) {
        $errorList[] = "User Name must be 2-30 characters long";
        $registerInfo['userName'] = '';
    }
    if (strlen($phone)!== 10) {
        $errorList[] = "Phone must be like 5144501234";
        $registerInfo['phone'] = '';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $errors['email'] = "Invalid Email";
        $registerInfo['email'] = '';
    } elseif (isEmailTaken($email)) {
        $errors['email'] = "User is already exist.";
        $registerInfo['email'] = '';
    }
    if (strlen($street) < 2 || strlen($street) > 100) {
        $errorList[] = "Street must be 2-100 characters long";
    }
    if (strlen($city) < 2 || strlen($city) > 100) {
        $errorList[] = "City must be 2-100 characters long";
    }
    if (!isset($province)) {
        $errorList[] = "Province cannot be empty";
    }
    if (strlen($postCode) !== 7) {
        $errorList[] = "Post Code must be in XXX YYY format";
    }
    if ($isAgree == FALSE) {
        $errorList[] = "Please agree terms before register new user";
    }

    $pass1Quality = verifyPasswordQuality($pass1);
    $pass2Quality = verifyPasswordQuality($pass2);
    if ($pass1Quality !== TRUE) {
        $errors['password'] = $pass1Quality;
    } elseif ( $pass2Quality !== TRUE) {
        $errors['password'] = $pass2Quality;
    }elseif ($pass1 !== $pass2) {
        $errors['password'] = "Passwords must be same.";
    }

    if (empty($errors)) {
        DB::insert('users', [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'userName' => $userName,
            'email' => $email,
            'password' => $pass1,
            'phone' => $phone,
            'street' => $street,
            'city' => $city,
            'province' => $province,
            'postalCode' => $postCode
        ]);
        $_SESSION['user'] = DB::queryFirstRow("SELECT * FROM users WHERE email = %s",$email);
        return $response->withHeader('Location', '/');
    }

    return $view->render($response, 'register.html.twig', [
        'errors' => $errors,
        'prevInput' => [
            'name' => $userName,
            'email' => $email
        ]
    ]);

});

$app->get('/register/isemailtaken/{email}', function ($request, $response, array $args) use ($log) {
    $error = '';

    if(isset($args['email'])){
        $error = isEmailTaken($args['email']) ? "It's already taken." :'';
    }

    $response->getBody()->write($error);
    return $response;
});

function isEmailTaken($email)
{
    $users = DB::queryFirstRow("SELECT COUNT(*) AS 'count' FROM users WHERE email = %s", $email);

    if ($users['count'] == 0) {
        return false;
    } elseif ($users['count'] == 1) {
        return true;
    } else {
        $log->debug(sprintf("Internal Error: duplicate email %s, uid=%d", $email, $_SERVER['REMOTE_ADDR']));
        return true;
    }
}

function verifyPasswordQuality($password) {
    if (strlen($password) < 6 || strlen($password) > 100
        || preg_match("/[a-z]/", $password) == false
        || preg_match("/[A-Z]/", $password) == false
        || preg_match("/[0-9#$%^&*()+=-\[\]';,.\/{}|:<>?~]/", $password) == false) {
        return "Password must be 6~100 characters,
                            must contain at least one uppercase letter, 
                            one lower case letter, 
                            and one number or special character.";
    }
    return TRUE;
}