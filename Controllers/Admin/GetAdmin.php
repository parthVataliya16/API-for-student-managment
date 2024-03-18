<?php

class GetAdmin extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function getAdmin()
    {
        try {
            $adminArr = [];
            $userName = $_SESSION['user'];
            $admin = $this->connection->query("SELECT profile_picture from users where user_name = '$userName'");
            $result = $admin->fetch_assoc();
            array_push($adminArr, $result);

            $this->status = 200;
            $this->message = "Fetch admin detail successfully!";

        } catch (Exception $error) {
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $error->getCode() . ", error: " . $error->getMessage() . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $responseArr = [
                'status' => $this->status,
                'message' => $this->message,
                'admin' => $adminArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}

?>