<?php

class ForgotPassword extends Connection
{
    private $status;
    private $message;
    private $sendMail;

    public function __construct($mailSend = new SendMail())
    {
        parent::__construct();
        $this->sendMail = $mailSend;
    }

    public function forgotPassword()
    {
        try {
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);
            $data['email'] = isset($data['email']) ? $data['email'] : NULL;

            foreach ($data as $key => $value) {
                if ($value == NULL) {
                    throw new Exception($key . " is required", 400);
                }
            }
            $data = sanitizeData($data);
            $email = $data['email'];
            $token = bin2hex(random_bytes(16));
            $createdAt = date("Y-m-d H:i:s");

            $selectUser = $this->connection->query("SELECT id from users where email = '$email'");

            if ($selectUser->num_rows) {
                $result = $selectUser->fetch_assoc();
                $id = $result['id'];

                $insertTokenToResetPassword = $this->connection->prepare("INSERT into reset_passwords (token, user_id, created_at) values (?, ?, ?)");
                $insertTokenToResetPassword->bind_param("sis", $token, $id, $createdAt);
                $insertTokenToResetPassword->execute();

                $sendMailFrom = 'vatliyaparth111@gmail.com';
                $sendMailTo = $email;
                $subject = 'Reset password!';
                $body = "<a href='http://localhost/practice/userManagement/views/auth/resetPassword.php?token=$token'> click me</a>";

                // $this->sendMail->sendMail($sendMailFrom, $sendMailTo, $subject, $body);

                $this->status = 200;
                $this->message = "Mail send to user";
            } else {
                throw new Exception ("User not found!", 400);
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