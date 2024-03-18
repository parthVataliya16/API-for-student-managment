<?php

class ApproveUser extends Connection
{
    private $status, $message, $sendMail;

    public function __construct($mail = new SendMail())
    {
        parent::__construct();
        $this->sendMail = $mail;
    }

    public function approveUser($id)
    {
        try {
            if ($id) {
                $userEmail = $this->connection->query("SELECT email, is_approved, first_name from users where id = $id");
                if ($userEmail->num_rows) {
                    $result = $userEmail->fetch_assoc();
                    if ($result['is_approved'] == 0) {
                        $this->connection->query("UPDATE users set is_approved = 1, status = 'active' where id = $id");
                        $email = $result['email'];
                        $firstName = $result['first_name'];
                        $sendMailFrom = 'vatliyaparth111@gmail.com';
                        $sendMailTo = $email;
                        $subject = 'Approved!';
                        $body = "<h2>Hello $firstName!</h2>
                        <p>You are now approved!</p>
                        <a href='http://localhost/practice/userManagement/views/auth/login.php'>Login</a>";
                        
                        $this->sendMail->sendMail($sendMailFrom, $sendMailTo, $subject, $body);
                        $this->status = 200;
                        $this->message = "User approved successfully!";
                    } else {
                        throw new Exception("User already approved", 400);
                    }
                } else {
                    throw new Exception ("Invalid user!", 400);
                }
            } else {
                throw new Exception ("Id must be required", 400);
            }
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: $this->status, error: $this->message, Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen('./../errors.log', 'a');
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