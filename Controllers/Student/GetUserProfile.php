<?php

class GetUserProfile extends Connection
{
    protected $status, $message;

    function __construct()
    {
        parent::__construct();
    }

    function getUserProfile()
    {
        try {
            $userName = $_SESSION['user'];
            $userProfileArr = [];
            $userProfile = $this->connection->query("SELECT id, first_name, last_name, profile_picture from users where email = '$userName' || user_name = '$userName'");
            if ($userProfile->num_rows) {
                while ($row = $userProfile->fetch_assoc()){
                    array_push($userProfileArr, $row);
                }
                $this->status = 200;
                $this->message = "Get user profile successfully!";
            } else {
                throw new Exception("Unauthorized user", 401);
            }
        } catch(Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ]  file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " . $this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $response = [
                'status' => $this->status,
                'message' => $this->message,
                'userProfile' => $userProfileArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}
?>