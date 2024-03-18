<?php

class VerifiedUser extends Connection
{
    private $status;
    private $message;
    private $sendMail;

    public function __construct($mail = new SendMail())
    {
        parent::__construct();
        $this->sendMail = $mail;
    }

    public function verifiedUser($token)
    {
        try {
            $userID = $this->connection->query("SELECT user_id, created_at from mail_verifications where token = '$token'");
            if ($userID->num_rows) {
                $result = $userID->fetch_assoc();
                $id = $result['user_id'];
                $createdTime = strtotime($result['created_at']);
                $currentTime = strtotime("now");
                $timeDiffernce = $currentTime - $createdTime;
                
                $verifiedUser = $this->connection->query("DELETE from mail_verifications where user_id = $id");
                if (($timeDiffernce / 3600) > (24)) {
                    throw new Exception("Link expired! Contact admin!", 400);
                } else {
                    $this->connection->query("UPDATE users set is_verified = 1 where id = $id");
                    $adminEmail = $this->connection->query("SELECT email from users where role = 'admin'");
                    $adminEmail = $adminEmail->fetch_assoc();
                    $adminEmail = $adminEmail['email'];
                    $email = $this->connection->query("SELECT first_name,last_name, email from users where id = $id");
                    $result = $email->fetch_assoc();
                    $email = $result['email'];
                    $firstName = $result['first_name'];
                    $lastName = $result['last_name'];
                    $sendMailFrom = $email;
                    $sendMailTo = $adminEmail;
                    $subject = 'Approve user';
                    $body = "<h2>Hello admin!</h2>
                            <p>Plase approve this user: $firstName $lastName </p>
                            <p>Email address is: $email </p>
                            <a href='http://localhost/practice/userManagement/views/auth/login.php'>approve user</a>";

                    $this->sendMail->sendMail($sendMailFrom, $sendMailTo, $subject, $body);

                    $this->status = 200;
                    $this->message = "Mail is verified. Wait for admin approval!";
                }
            } else {
                throw new Exception("Email verification is already done!", 400);
            }
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " . $this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $response = [
                'status' => $this->status,
                'message' => $this->message
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}

?>