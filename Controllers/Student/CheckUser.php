<?php

class CheckUser extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function checkUser($id)
    {
        try {
            $userName = $_SESSION['user'];
            $selectUser = $this->connection->query("SELECT id from users where (email = '$userName' || user_name = '$userName') && id = $id");

            if ($selectUser->num_rows) {
                $this->status = 200;
                $this->message = "Valid user";
            } else {
                throw new Exception ("Invalid user", 401);
            }
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
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