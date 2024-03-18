<?php
use Dotenv\Dotenv;

require("./../../vendor/autoload.php");
$dotenv = Dotenv::createImmutable('./../../');
$dotenv->load();

$googleCredentials = include('./../../config/google.php');
$mail = include('./../../config/mail.php');
$database = include('./../../config/database.php');

require_once './../../services/googleService.php';
require_once './../../services/mailService.php';
require_once './../Connection.php';

class LoginWithgoogle extends Connection
{
    private $status, $message, $loginWithGoogle, $mail;

    public function __construct()
    {
        parent::__construct();
        $this->loginWithGoogle = new LoginWithGoogleService();
        $this->mail = new SendMail();
    }

    public function loginWithgoogle()
    {
        try {
            session_start();
            // if (isset($_SESSION['user'])) {
            //     require_once __DIR__ . './../../middleware/checkRole.php';
            // }
            $code = $_GET['code'];

            if (isset($code)) {
                $userData = $this->loginWithGoogle->getAccessTokenFromAuthCode($code);    
                // $refreshToken = $this->loginWithGoogle->getAccessTokenFromRefreshToken();
                // var_dump($refreshToken);
                $email = $userData['email'];
                $selectUser = $this->connection->query("SELECT status, is_approved, profile_picture from users where email = '$email'");

                $familyName = $userData['familyName'];
                $givenName = $userData['givenName'];
                $gender = $userData['gender'];
                $profilePicture = $userData['picture'];
                $imageName = time();
                $userName = explode('@', $email)[0];

                $image = file_get_contents($profilePicture);
                $uploadImage = './../../public/uploads/';
                file_put_contents($uploadImage . $imageName, $image);

                if ($selectUser->num_rows) {
                    $updatedAt = date('Y-m-d');
                    $this->connection->query("UPDATE users set first_name = '$givenName', last_name = '$familyName', gender = '$gender', profile_picture = '$imageName', updated_at = '$updatedAt', user_name = '$userName' where email = '$email'");

                    $result = $selectUser->fetch_assoc();
                    $profilePicture = $result['profile_picture'];
                    unlink($uploadImage . $profilePicture);

                    if ($result['is_approved']) {
                        if ($result['status'] == 'active') {
                            $_SESSION['status'] = 'active';
                            $_SESSION['user'] = $email;
                            header('location: ./../../views/students/dashboard.php');
                            exit;
                        } else {
                            $_SESSION['status'] = 'de-active';
                            header('location: ./../../views/auth/login.php');
                            exit;
                        }
                    } else {
                        $_SESSION['approve'] = 0;
                        header('location: ./../../views/auth/login.php');
                        exit;
                    }
                } else {
                    $gradeId = 1;
                    $status = 'active';
                    $verified = 1;
                    $createdAt = date("Y-m-d H:i:s");
                    $insertUser = $this->connection->prepare("INSERT into users (first_name, last_name, email, gender, profile_picture, grade_id, status, is_verified, is_approved, created_at, user_name) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $insertUser->bind_param("sssssisiiss", $givenName, $familyName, $email, $gender, $imageName, $gradeId, $status, $verified, $appoved, $createdAt, $userName);
                    if ($insertUser->execute()) {
                        $userId = $this->connection->prepare("SELECT id from users where email = ?");
                        $userId->bind_param("s", $email);
                        $userId->execute();
                        $result = $userId->get_result();
                        $userId = $result->fetch_assoc();
                        $userId = $userId['id'];
                        $insertHobby = $this->connection->prepare("INSERT INTO hobbies (user_id) values (?)");
                        $insertHobby->bind_param("i", $userId);
                        $insertHobby->execute();
                        $selectAdminMail = $this->connection->query("SELECT email from users where id = 1");
                        $result = $selectAdminMail->fetch_assoc();
                        $adminMail = $result['email'];

                        $mailFrom = $email;
                        $mailTo = $adminMail;
                        $subject = 'Approve user';
                        $body = "<h2>Hello admin!</h2>
                            <p>Plase approve this user: $givenName $familyName </p>
                            <p>Email address is: $email </p>
                            <a href='http://localhost/practice/userManagement/views/auth/login.php'>approve user</a>";
                        $this->mail->sendMail($mailFrom, $mailTo, $subject, $body);

                        $_SESSION['approve'] = 0;
                        header('location: ./../../views/auth/login.php');
                        exit;
                    }
                }
            } else {
                header('location: ./../views/auth/login.php');
                exit;
            }
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: $this->status, error: $this->message, Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen('./../../errors.log', 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $response = [
                'status' => $this->status,
                'message' => $this->message
            ];

            http_response_code($this->status);
            header('content-type: application/json');
            return json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}

$loginWithgoogle = new LoginWithgoogle();
$loginWithgoogle->loginWithgoogle();

?>