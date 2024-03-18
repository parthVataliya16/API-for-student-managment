<?php

class UsersMail extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function usersMail()
    {
        try {
            $usersMailArr = [];
            $usersMail = $this->connection->query("SELECT email from users where id != 1");
            while ($result = $usersMail->fetch_assoc()) {
                array_push($usersMailArr, $result);
            }
            $this->status = 200;
            $this->message = "Get all users mail";
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " . $this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $responseArr = [
                'status' => $this->status,
                'message' => $this->message,
                'usersMail' => $usersMailArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}

?>