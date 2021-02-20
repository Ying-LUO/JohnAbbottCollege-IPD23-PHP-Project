<?php

use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

require_once 'vendor/autoload.php';
require_once 'init.php';

$passwordPepper = 'mmyb7oSAeXG9DTz2uFqu';

// root page
$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html.twig');
});

// STATE 1: first display of the form
$app->get('/register', function ($request, $response, $args) {
    return $this->view->render($response, 'register.html.twig');
});

$app->post('/register', function ($request, $response, $args) use ($log) {

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

    $errorList = [];
    if (strlen($firstName) < 2 || strlen($firstName) > 50) {
        $errorList['firstName'] = "First Name must be 2-50 characters long";
        $firstName = '';
    }
    if (strlen($lastName) < 2 || strlen($lastName) > 50) {
        $errorList['lastName'] = "Last Name must be 2-50 characters long";
        $lastName = '';
    }
    if (strlen($userName) < 2 || strlen($userName) > 30) {
        $errorList['userName'] = "User Name must be 2-30 characters long";
        $userName = '';
    }elseif (isUserNameTaken($userName)) {
        $errors['userName'] = "User Name is already exist.";
        $userName = '';
    }
    if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phone)) {
        $errorList['phone'] = "Phone: " . $phone . " must be like ***-***-****";
        $phone = '';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $errors['email'] = "Invalid Email";
        $email = '';
    } elseif (isEmailTaken($email)) {
        $errors['email'] = "Email is already exist.";
        $email = '';
    }
    if (strlen($street) < 2 || strlen($street) > 100) {
        $errorList['street'] = "Street must be 2-100 characters long";
    }
    if (strlen($city) < 2 || strlen($city) > 100) {
        $errorList['city'] = "City must be 2-100 characters long";
    }
    if (!isset($province)) {
        $errorList['province'] = "Province cannot be empty";
    }
    if(!preg_match("/^[A-Za-z0-9]{3} [A-Za-z0-9]{3}$/", $postCode)) {
        $errorList['postalCode'] = "PostalCode: " . $postCode . " must be in XXX YYY format";
    }
    if (strcmp($isAgree, 'on') <> 0 ) {
        $errorList['isAgree'] = "Please agree terms before register new user";
    }

    $pass1Quality = verifyPasswordQuality($pass1);
    $pass2Quality = verifyPasswordQuality($pass2);
    if ($pass1Quality !== TRUE) {
        $errorList['password1'] = $pass1Quality;
    } elseif ( $pass2Quality !== TRUE) {
        $errorList['password2'] = $pass2Quality;
    }elseif ($pass1 !== $pass2) {
        $errorList['password'] = "Passwords must be same.";
    }

    if ($errorList) {
        $log->error(sprintf("Register failed: email %s, username %s, uid=%d", $email, $userName, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'register.html.twig', [
            'errors' => $errorList,
            'prevInput' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'userName' => $userName,
                'phone' => $phone,
                'email' => $email,
                'pass1' => $pass1,
                'pass2' => $pass2,
                'street' => $street,
                'city' => $city,
                'province' => $province,
                'postalCode' => strtoupper($postCode)
            ]
        ]);
    } else {
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $pass1, $passwordPepper);
        $pwdHashed = password_hash($pwdPeppered, PASSWORD_DEFAULT); // PASSWORD_ARGON2ID);
        DB::insert('users', [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'userName' => $userName,
            'email' => $email,
            'password' => $pwdHashed,
            'phone' => $phone,
            'street' => $street,
            'city' => $city,
            'province' => $province,
            'postalCode' => strtoupper($postCode)
        ]);
        $_SESSION['user'] = DB::queryFirstRow("SELECT * FROM users WHERE email = %s",$email);
        $log->debug(sprintf("Register successfully: email %s, username %s, uid=%d", $email, $userName, $_SERVER['REMOTE_ADDR']));
        return $response->withHeader('Location', '/');
    }
});

$app->get('/register/isemailtaken/{email}', function ($request, $response, array $args) use ($log) {
    $error = '';

    if(isset($args['email'])){
        $error = isEmailTaken($args['email']) ? "It's already taken." :'';
    }

    $response->getBody()->write($error);
    return $response;
});

// used for register and validation
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

function isUserNameTaken($userName)
{
    $users = DB::queryFirstRow("SELECT COUNT(*) AS 'count' FROM users WHERE username = %s", $userName);

    if ($users['count'] == 0) {
        return false;
    } elseif ($users['count'] == 1) {
        return true;
    } else {
        $log->debug(sprintf("Internal Error: duplicate User Name %s, uid=%d", $userName, $_SERVER['REMOTE_ADDR']));
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


// STATE 1: first display
$app->get('/login', function ($request, $response, $args) {
    return $this->view->render($response, 'login.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/login', function ($request, $response, $args) use ($log) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    //
    $record = DB::queryFirstRow("SELECT id ,email , password, username FROM users WHERE email=%s", $email);
    $loginSuccess = false;
    if ($record) {
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $password, $passwordPepper);
        $pwdHashed = $record['password'];
        if (password_verify($pwdPeppered, $pwdHashed)) {
            $loginSuccess = true;
        }
        // WARNING: only temporary solution to allow for old plain-text passwords to continue to work
        // Plain text passwords comparison
        else if ($record['password'] == $password) {
            $loginSuccess = true;
        }
    }
    //
    if (!$loginSuccess) {
        $log->debug(sprintf("Login failed for email %s and %s from %s", $email, $password, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['user'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for email %s, uid=%d, from %s", $email, $record['id'], $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login_success.html.twig', ['userSession' => $_SESSION['user'] ] );
        echo('userSession');
    }
});

$app->get('/logout', function ($request, $response, $args) use ($log) {
    if(isset($_SESSION['user'])){
        $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['user']['id'], $_SERVER['REMOTE_ADDR']));
        unset($_SESSION['user']);
        return $this->view->render($response, 'index.html.twig', ['userSession' => null ]); // after logout direct to main page
    }
});

$app->get('/account', function ($request, $response, $args) use ($log){
    if(isset($_SESSION['user'])) {
        $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $_SESSION['user']['id']);
    }
    if(isset($user)){
        $log->debug(sprintf("Trying to update my account with userName %s, %s", $user['username'], $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'account.html.twig',['user' => $user]);
    }else{
        $log->error(sprintf("Internal Error: Cannot find userName %s\n:%s", $_SESSION['user']['username'], $_SERVER['REMOTE_ADDR']));
        return $response->withHeader("Location", "/error_internal",403);
    }
});

$app->post('/account', function ($request, $response, $args) {
    // update account information
});

$app->get('/contact', function ($request, $response, $args) use ($log){
    if(isset($_SESSION['user'])) {
        $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $_SESSION['user']['id']);
    }
    if(isset($user)){
        $log->debug(sprintf("Trying to contact us with userName %s, %s", $user['username'], $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'contact.html.twig',['user' => $user]);
    }else{
        $log->error(sprintf("Internal Error: Cannot find userName %s\n:%s", $_SESSION['user']['username'], $_SERVER['REMOTE_ADDR']));
        return $response->withHeader("Location", "/error_internal",403);
    }
});

$app->post('/contact', function ($request, $response, $args) {
    $firstName = $request->getParam('firstName');
    $lastName = $request->getParam('lastName');
    $email = $request->getParam('email');
    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($user) { // send email

    }
});

$app->get('/passreset_request', function ($request, $response, $args) {
    return $this->view->render($response, 'password_reset.html.twig');
});

$app->post('/passreset_request', function ($request, $response, $args) use ($log){

    $email = $request->getParam('email');
    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($user) { // send email
        $secret = generateRandomString(60);
        $dateTime = gmdate("Y-m-d H:i:s"); // GMT time zone
        DB::insertUpdate('passwordresets', [
            'userId' => $user['id'],
            'secret' => $secret,
            'creationDateTime' => $dateTime
        ], [
            'secret' => $secret,
            'creationDateTime' => $dateTime
        ]);

        // primitive template with string replacement
        $emailBody = file_get_contents('\password_reset_email.html.strsub');
        $emailBody = str_replace('EMAIL', $email, $emailBody);
        $emailBody = str_replace('SECRET', $secret, $emailBody);

        // OPTION 2: USING EXTERNAL SERVICE - should not land in Spam / Junk folder
        $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key',
            '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');
        $apiInstance = new SendinBlue\Client\Api\SMTPApi(new GuzzleHttp\Client(), $config);
        // \SendinBlue\Client\Model\SendSmtpEmail | Values to send a transactional email
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
        $sendSmtpEmail->setSubject("Password reset for teacher.ipd20.com");
        $sendSmtpEmail->setSender(new \SendinBlue\Client\Model\SendSmtpEmailSender(
            ['name' => 'No-Reply', 'email' => 'noreply@teacher.ip20.com']) );
        $sendSmtpEmail->setTo([ new \SendinBlue\Client\Model\SendSmtpEmailTo(
            ['name' => $user['name'], 'email' => $email])  ]);
        $sendSmtpEmail->setHtmlContent($emailBody);
        //
        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            $log->debug(sprintf("Password reset sent to %s, uid=%d", $email));
            return $this->view->render($response, 'password_reset_sent.html.twig');
        } catch (Exception $e) {
            $log->error(sprintf("Error sending password reset email to %s\n:%s", $email, $e->getMessage()));
            return $response->withHeader("Location", "/error_internal",403);
        }
        // end of option 2 code
    }
    //
    return $this->view->render($response, 'password_reset_sent.html.twig');
});

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
