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

// generated random string
$passwordPepper = 'vG3iNzWMwKARpChq5KDZ';

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
    }
    if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phone)) {
        $errorList['phone'] = "Phone: " . $phone . " must be like ***-***-****";
        $phone = '';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $errors['email'] = "Invalid Email";
        $email = '';
    }
    if (strlen($street) < 2 || strlen($street) > 100) {
        $errorList['street'] = "Street must be 2-100 characters long";
    }
    if (strlen($city) < 2 || strlen($city) > 100) {
        $errorList['city'] = "City must be 2-100 characters long";
    }
    if (!isset($province)) {
        $errorList['province'] = "Province must be provided";
    }
    if(!preg_match("/^[A-Za-z0-9_ ]{3,4}[A-Za-z0-9]{3}$/", $postCode)) {
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
            'user' => [
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
        $log->debug(sprintf("Register new user successfully: email %s, username %s, uid=%d", $email, $userName, $_SERVER['REMOTE_ADDR']));
        setFlashMessage("Register New User Successfully");
        return $response->withRedirect("/productlines");
    }
});

// used via AJAX
$app->get('/isemailtaken/{email}', function ($request, $response, $args) use ($log) {
    // get email address from url
    $email = isset($args['email']) ? $args['email'] : "";
    $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($record) {
        $log->debug(sprintf("Internal Error: duplicate email %s, uid=%d", $email, $_SERVER['REMOTE_ADDR']));
        return $response->write("Email already in use");
    } else {
        return $response->write("");
    }
});

$app->get('/isusernametaken/{username}', function ($request, $response, $args) use ($log) {
    // get username from url
    $username = isset($args['username']) ? $args['username'] : "";
    $record = DB::queryFirstRow("SELECT * FROM users WHERE username=%s", $username);
    if ($record) {
        $log->debug(sprintf("Internal Error: duplicate username %s, uid=%d", $username, $_SERVER['REMOTE_ADDR']));
        return $response->write("UserName already in use");
    } else {
        return $response->write("");
    }
});

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
    $errorList = [];
    $emailOrUsername = $request->getParam('emailOrUsername');
    $password = $request->getParam('password');
    $record = DB::queryFirstRow("SELECT id, email, password, username, role FROM users WHERE (email=%s) OR (username=%s)", $emailOrUsername, $emailOrUsername);
    $loginSuccess = false;
    if ($record) {
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $password, $passwordPepper);
        $pwdHashed = $record['password'];
        if (password_verify($pwdPeppered, $pwdHashed)) {
            $loginSuccess = true;
        }else{
            $errorList[] = "Password is incorrect";
        }
    }else{
        $errorList[] = "Username Or Email Address is not existed";
    }
    //
    if (!$loginSuccess) {
        $log->debug(sprintf("Login failed for email or username: %s and password: %s from %s", $emailOrUsername, $password, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'errors' => $errorList ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['user'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for email or username: %s, uid=%d, from %s", $emailOrUsername, $record['id'], $_SERVER['REMOTE_ADDR']));
        setFlashMessage("Login Successfully");
        if(strcmp($record['role'],'user') === 0){
            return $response->withRedirect("/productlines");
        }elseif(strcmp($record['role'],'admin') === 0){
            return $response->withRedirect("/admin/equipments/list");
        }
    }
});

$app->get('/logout', function ($request, $response, $args) use ($log) {
    if(isset($_SESSION['user'])){
        $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['user']['id'], $_SERVER['REMOTE_ADDR']));
        unset($_SESSION['user']);
        setFlashMessage("You have been logout!");
        return $response->withRedirect("/productlines");
    }
});

$app->get('/account', function ($request, $response, $args) use ($log){
    if(isset($_SESSION['user'])) {
        $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $_SESSION['user']['id']);
    }
    if(isset($user)){
        $log->debug(sprintf("Trying to update my account with userName %s, %s", $user['username'], $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'register.html.twig',['user' => $user]);
    }else{
        $log->error(sprintf("Internal Error: Cannot find userName %s\n:%s", $_SESSION['user']['username'], $_SERVER['REMOTE_ADDR']));
        return $response->withHeader("Location", "/error_internal",403);
    }
});

$app->post('/account', function ($request, $response, $args) use ($log) {
    if(isset($_SESSION['user'])) {
        $originUser = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $_SESSION['user']['id']);
    }
    if(isset($originUser)){
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
        }
        if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phone)) {
            $errorList['phone'] = "Phone: " . $phone . " must be like ***-***-****";
            $phone = '';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            $errors['email'] = "Invalid Email";
            $email = '';
        }
        if (strlen($street) < 2 || strlen($street) > 100) {
            $errorList['street'] = "Street must be 2-100 characters long";
        }
        if (strlen($city) < 2 || strlen($city) > 100) {
            $errorList['city'] = "City must be 2-100 characters long";
        }
        if (!isset($province)) {
            $errorList['province'] = "Province must be provided";
        }
        if(!preg_match("/^[A-Za-z0-9]{3} [A-Za-z0-9]{3}$/", $postCode)) {
            $errorList['postalCode'] = "PostalCode: " . $postCode . " must be in XXX YYY format";
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
            $log->error(sprintf("Account information change failed: email %s, username %s, uid=%d", $email, $userName, $_SERVER['REMOTE_ADDR']));
            return $this->view->render($response, 'register.html.twig', [
                'errors' => $errorList,
                'user' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'username' => $userName,
                    'phone' => $phone,
                    'email' => $email,
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

            $updateUser = ['firstName' => $firstName,
                'lastName' => $lastName,
                'userName' => $userName,
                'email' => $email,
                'password' => $pwdHashed,
                'phone' => $phone,
                'street' => $street,
                'city' => $city,
                'province' => $province,
                'postalCode' => strtoupper($postCode)];

            DB::update('users', $updateUser, "id=%d", $originUser['id']);
            // refresh new user data
            $_SESSION['user'] = DB::queryFirstRow("SELECT * FROM users WHERE id = %d",$originUser['id']);
            $log->debug(sprintf("Update user account successfully: new email %s, new username %s, uid=%d", $_SESSION['user']['email'], $_SESSION['username'], $_SERVER['REMOTE_ADDR']));
            setFlashMessage("Update user account successfully");
            return $response->withRedirect("/productlines");
        }

    }else{
        $log->error(sprintf("Internal Error: Cannot find userName %s\n:%s", $_SESSION['user']['username'], $_SERVER['REMOTE_ADDR']));
        return $response->withHeader("Location", "/error_internal",403);
    }
});

$app->get('/contact', function ($request, $response, $args) use ($log){
    if(isset($_SESSION['user'])) {
        $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $_SESSION['user']['id']);
        if(isset($user)) {
            $log->debug(sprintf("UserName=%s is trying to contact us with, %s", $user['username'], $_SERVER['REMOTE_ADDR']));
            return $this->view->render($response, 'contact.html.twig',['user' => $user]);
        }
    }else{
        return $this->view->render($response, 'contact.html.twig');
    }
});

$app->post('/contact', function ($request, $response, $args) use ($log){
    $firstName = $request->getParam('firstName');
    $lastName = $request->getParam('lastName');
    $email = $request->getParam('email');
    $comment = $request->getParam('comment');
    if (strlen($firstName) < 2 || strlen($firstName) > 50) {
        $errorList['firstName'] = "First Name must be 2-50 characters long";
        $firstName = '';
    }
    if (strlen($lastName) < 2 || strlen($lastName) > 50) {
        $errorList['lastName'] = "Last Name must be 2-50 characters long";
        $lastName = '';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $errorList['email'] = "Invalid Email";
        $email = '';
    }
    if (strlen($comment) < 2 || strlen($comment) > 1000) {
        $errorList[] = "Comments must be 2-1000 characters long";
    }

    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if (!$user) {
        $errorList[] = "Email is not from a registered User";
    }
    if ($errorList) {
        $log->error(sprintf("Someone is trying to contact us: email %s, uid=%d", $email, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'contact.html.twig', [
            'errors' => $errorList,
            'user' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'comment' => $comment
            ]
        ]);
    } else {
        // primitive template with string replacement
        $emailBody = file_get_contents('templates/contact_email.html.strsub');
        $emailBody = str_replace('NAME', $firstName . " " . $lastName, $emailBody);
        $emailBody = str_replace('EMAIL', $email, $emailBody);
        $emailBody = str_replace('COMMENT', $comment, $emailBody);

        // OPTION 2: USING EXTERNAL SERVICE - should not land in Spam / Junk folder
        $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key',
            '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');
        $apiInstance = new SendinBlue\Client\Api\SMTPApi(new GuzzleHttp\Client(), $config);

        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
        $sendSmtpEmail->setSubject("Comments From User");
        $sendSmtpEmail->setSender(new \SendinBlue\Client\Model\SendSmtpEmailSender(
            ['name' => $user['username'], 'email' => 'noreply@teacher.ip20.com']) );
        $sendSmtpEmail->setTo([ new \SendinBlue\Client\Model\SendSmtpEmailTo(
            ['name' => 'Customer Service', 'email' => 'ying.luo@johnabbottcollege.net']) ]); // TODO: CHANGE MAIL ADDRESS IF NEEDED
        $sendSmtpEmail->setHtmlContent($emailBody);
        //
        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            $log->debug(sprintf('username=%s is contact us by email=%s , %s', $user['username'], $email, $_SERVER['REMOTE_ADDR']));
            setFlashMessage("Thank you for contacting us! Our customer service representive will feedback to you soon!");
            return $response->withRedirect("/productlines");
        } catch (Exception $e) {
            $log->error(sprintf("Error sending contact us email from %s\n:%s", $email, $e->getMessage()));
            return $response->withHeader("Location", "/error_internal",403);
        }
    }
});

$app->get('/passreset_request', function ($request, $response, $args) {
    return $this->view->render($response, 'password_reset.html.twig');
});

$app->post('/passreset_request', function ($request, $response, $args) use ($log){
    $email = $request->getParam('email');
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        setFlashMessage("Please input an valid email address");
        return $response->withRedirect("/passreset_request");
    }else{
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
            $emailBody = file_get_contents('templates/password_reset_email.html.strsub');
            $emailBody = str_replace('EMAIL', $email, $emailBody);
            $emailBody = str_replace('SECRET', $secret, $emailBody);

            // OPTION 2: USING EXTERNAL SERVICE - should not land in Spam / Junk folder
            $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key',
                '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');
            $apiInstance = new SendinBlue\Client\Api\SMTPApi(new GuzzleHttp\Client(), $config);

            $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
            $sendSmtpEmail->setSubject("Password reset for skirentals.ipd23.com");
            $sendSmtpEmail->setSender(new \SendinBlue\Client\Model\SendSmtpEmailSender(
                ['name' => 'No-Reply from Ski Rentals', 'email' => 'noreply@teacher.ip20.com']) );
            $sendSmtpEmail->setTo([ new \SendinBlue\Client\Model\SendSmtpEmailTo(
                ['name' => $user['username'], 'email' => $email])  ]);
            $sendSmtpEmail->setHtmlContent($emailBody);
            //
            try {
                $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                $log->debug(sprintf('password reset has been sent to email=%s for username=%s, %s', $email, $user['username'], $_SERVER['REMOTE_ADDR']));
                setFlashMessage("Password reset has been sent (if an account with this email exists). Check your email in a moment.");
                return $response->withRedirect("/");
            } catch (Exception $e) {
                $log->error(sprintf("Error sending password reset email to %s\n:%s", $email, $e->getMessage()));
                return $response->withHeader("Location", "/error_internal",403);
            }
        }
    }
});

$app->get('/passresetaction/{secret}', function ($request, $response, $args) use ($log){
    return $this->view->render($response, 'password_reset_action.html.twig');
});

$app->post('/passresetaction/{secret}', function ($request, $response, $args) use ($log){
    $secret = $args['secret'];
    $resetRecord = DB::queryFirstRow("SELECT * FROM passwordresets WHERE secret=%s", $secret);
    if (!$resetRecord) {
        $log->debug(sprintf('password reset token not found, token=%s', $secret));
        setFlashMessage("Password reset token not found or not valid (expired).");
        return $response->withRedirect("/");
    }
    // check if password reset has not expired
    $creationDT = strtotime($resetRecord['creationDateTime']); // convert to seconds since Jan 1, 1970 (UNIX time)
    $nowDT = strtotime(gmdate("Y-m-d H:i:s")); // current time GMT
    if ($nowDT - $creationDT > 60*60) { // expired
        DB::delete('passwordresets', 'secret=%s', $secret);
        $log->debug(sprintf('password reset token expired userid=%s, token=%s', $resetRecord['userId'], $secret));
        setFlashMessage("Password reset token not found or not valid (expired).");
        return $response->withRedirect("/login");
    }

    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    $errorList = array();
    if ($pass1 != $pass2) {
        array_push($errorList, "Passwords don't match");
    } else {
        $passQuality = verifyPasswordQuality($pass1);
        if ($passQuality !== TRUE) {
            array_push($errorList, $passQuality);
        }
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'password_reset_action.html.twig', ['errors' => $errorList]);
    } else {
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $pass1, $passwordPepper);
        $pwdHashed = password_hash($pwdPeppered, PASSWORD_DEFAULT); // PASSWORD_ARGON2ID);
        DB::update('users', ['password' => $pwdHashed], "id=%d", $resetRecord['userId']);
        DB::delete('passwordresets', 'secret=%s', $secret); // cleanup the record
        setFlashMessage("Password has been reset Successfully");
        return $response->withRedirect("/login");
    }
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
