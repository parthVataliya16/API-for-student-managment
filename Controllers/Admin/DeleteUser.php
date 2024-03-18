<?php

class DeleteUser extends Connection
{
    protected $status, $message;

    function __construct() {
        parent::__construct();
    }

    function deleteUser($id)
    {
        try {
            $userImage = $this->connection->query("SELECT profile_picture from users where id = $id");
            if ($userImage->num_rows) {
                $result = $userImage->fetch_assoc();
                $userImage = $result['profile_picture'];

                // if (file_exists('./../public/uploads/' . $userImage)) {
                //     unlink('./../public/uploads/' . $userImage);  
                // } 
                $this->connection->query("DELETE from mail_verifications where user_id = $id");
                $this->connection->query("DELETE from reset_passwords where user_id = $id");
                $this->connection->query("DELETE from hobbies where user_id = $id");
                $this->connection->query("DELETE from users where id = $id");

                $this->status = 200;
                $this->message = "Delete successfully!";
            } else {
                throw new Exception ("Invalid user!", 400);
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