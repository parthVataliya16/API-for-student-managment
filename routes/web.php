<?php
use Dotenv\Dotenv;

require_once "./../vendor/autoload.php";
require_once './../services/mailService.php';
require_once './../Controllers/Connection.php';
require_once './../middleware/sanitizeData.php';
require_once './../Controllers/Auth/UserLogin.php';
require_once './../Controllers/Auth/Register.php';
require_once './../Controllers/Grade.php';
require_once './../Controllers/UserData.php';
require_once './../Controllers/Admin/DeleteUser.php';
require_once './../Controllers/UpdateUserData.php';
require_once './../Controllers/Admin/ChangeUserStatus.php';
require_once './../Controllers/Student/GetUserProfile.php';
require_once './../Controllers/Student/VerifiedUser.php';
require_once './../Controllers/Auth/ForgotPassword.php';
require_once './../Controllers/Auth/ResetPassword.php';
require_once './../Controllers/Admin/ApproveUser.php';
require_once './../Controllers/Auth/LinkExpire.php';
require_once './../Controllers/Student/CheckUser.php';
require_once './../Controllers/Admin/GetAdmin.php';
require_once './../Controllers/Admin/ShowUser.php';
require_once './../Controllers/Admin/UsersMail.php';
require_once './../Controllers/Admin/SendNotification.php';
require_once './../Controllers/Admin/GetNotifications.php';
require_once './../Controllers/Admin/GetNotificationData.php';
require_once './api.php';
require_once './../middleware/checkJwt.php';
require_once './../middleware/checkRole.php';
require_once './../middleware/checkMail.php';
require_once './../middleware/apiRateLimit.php';

$dotenv = Dotenv::createImmutable('./../');
$dotenv->load();
$database = include('./../config/database.php');
$mail = include('./../config/mail.php');
$tokenSecretKey = include('./../config/jwt.php');


$request = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'];

preg_match("/^\/v1\/api\/deleteUser\/(\d+)$|^\/v1\/api\/userData\/(\d+)$|^\/v1\/api\/approveUser\/(\d+)$|^\/v1\/api\/updateUser\/(\d+)$|^\/v1\/api\/changeUserStatus\/(\d+)$|^\/v1\/api\/checkUser\/(\d+)$/", $endpoint, $matches);

$id = 0;
$lengthOfMatchesArr = count($matches);

if ($lengthOfMatchesArr >= 2) {
    $id = $matches[$lengthOfMatchesArr - 1];
}

if ($request == 'POST' && $endpoint == '/v1/api/login') {
    $user = new UserLogin();
    echo $user->userLogin();
} elseif ($request == 'POST' && $endpoint == '/v1/api/registration') {
    $register = new Register();
    echo $register->register();
} elseif ($request == 'POST' && $endpoint == '/v1/api/forgotPassword') {
    $forgotPassword = new ForgotPassword();
    echo $forgotPassword->forgotPassword();
} elseif ($request == 'PUT' && $endpoint == '/v1/api/resetPassword') {
    if (isset($_GET['token'])) {
        $resetPassword = new ResetPassword();
        echo $resetPassword->resetPassword($_GET['token']);
    } else {
        $response = [
            'message' => 'Token required'
        ];
        http_response_code(400);
        header("content-type: application/json");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
} else {
    $userId = tokenValid();

    if ($userId != false) {
        $role = checkrole($userId);
        if ($role == 'admin') {
            switch ($request) {
                case 'POST' :
                    switch ($endpoint) {
                        case '/v1/api/sendNotification':
                            $sendNotification = new SendNotification();
                            echo $sendNotification->sendNotification();
                            break;
                        default: 
                            $response = [
                                'message' => 'HTTP/1.0 400 Bad Request'
                            ];
                            http_response_code(400);
                            header("content-type: application/json");
                            echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                    break;
                case 'GET':
                    switch ($endpoint) {
                        case "/v1/api/userData":
                            $userData = new userData();
                            echo $userData->userData();
                            break;
                        case "/v1/api/userData/{$id}":
                            $userData = new UserData();
                            echo $userData->userData($id);
                            break;
                        case '/v1/api/grade':
                            $grade = new Grade();
                            echo $grade->grade();
                            break;
                        case '/v1/api/showUser':
                            $showUser = new ShowUser();
                            echo $showUser->showUser($_GET['id']);
                            break;
                        case '/v1/api/userMail':
                            $userMails = new UsersMail();
                            echo $userMails->usersMail();
                            break;
                        case '/v1/api/getNotifications':
                            $getNotifications = new GetNotifications();
                            echo $getNotifications->getNotifications();
                            break;
                        case '/v1/api/getNotificationData':
                            $getNotificationData = new GetNotificationData();
                            echo $getNotificationData->getNotificationData($_GET['id']);
                            break;
                        default: 
                            $response = [
                                'message' => 'HTTP/1.0 400 Bad Request'
                            ];
                            http_response_code(400);
                            header("content-type: application/json");
                            echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                    break;
                case 'DELETE':
                    switch ($endpoint) {
                        case "/v1/api/deleteUser/{$id}":
                            $deleteUser = new DeleteUser();
                            echo $deleteUser->deleteUser($id);
                            break;
                        default:
                            $response = [
                                'message' => 'HTTP/1.0 400 Bad Request'
                            ];
                            http_response_code(400);
                            header("content-type: application/json");
                            echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                    break;
                case 'PUT':
                    switch ($endpoint) {
                        case "/v1/api/updateUser/{$id}":
                            $updateUserData = new UpdateUserData();
                            echo $updateUserData->updateUserData($id);
                            break;
                    }
                    break;
                case 'PATCH':
                    switch ($endpoint) {
                        case "/v1/api/approveUser/{$id}":
                            $approveUser = new ApproveUser();
                            echo $approveUser->approveUser($id);
                            break;
                        case "/v1/api/changeUserStatus/{$id}":
                            $changeUserStatus = new ChangeUserStatus();
                            echo $changeUserStatus->changeUserStatus($id);
                            break;
                        default:
                            $response = [
                                'message' => 'HTTP/1.0 400 Bad Request'
                            ];
                            http_response_code(400);
                            header("content-type: application/json");
                            echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                    break;
            }
        } else {
            switch ($request) {
                case 'GET':
                    switch ($endpoint) {
                        case "/v1/api/userData/{$id}":
                            $userData = new UserData();
                            echo $userData->userData($id);
                            break;
                        case '/v1/api/grade':
                            $grade = new Grade();
                            echo $grade->grade();
                            break;
                        case "/v1/api/checkUser/{$id}" :
                            $checkUser = new CheckUser();
                            echo $checkUser->checkUser($id);
                            break;
                        case '/v1/api/userProfile':
                            $userProfile = new GetUserProfile();
                            echo $userProfile->getUserProfile();
                            break;
                        case 'v1/api/verifiedUser':
                            $verifiedUser = new VerifiedUser();
                            echo $verifiedUser->verifiedUser($_GET['token']);
                            break;
                        default :
                            $response = [
                                'message' => 'HTTP/1.0 400 Bad Request'
                            ];
                            http_response_code(400);
                            header("content-type: application/json");
                            echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                    break;
                case 'PUT':
                    switch ($endpoint) {
                        case "/v1/api/updateUser/{$id}":
                            $updateUserData = new UpdateUserData();
                            echo $updateUserData->updateUserData($id);
                            break;
                        default :
                            $response = [
                                'message' => 'HTTP/1.0 400 Bad Request'
                            ];
                            http_response_code(400);
                            header("content-type: application/json");
                            echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                    break;
                default:
                    $response = [
                        'message' => 'HTTP/1.0 401 Unauthorized'
                    ];
                    http_response_code(401);
                    header("content-type: application/json");
                    echo json_encode($response, JSON_PRETTY_PRINT);
            }
        }
    }
}

?> 