<?php

class ChangeUserStatus extends Connection
{
    protected $status, $message;

    function __construct()
    {
        parent::__construct();
    }

    function changeUserStatus($id)
    {
        try {
            if ($id) {
                $userStatus = $this->connection->query("SELECT status from users where id = $id");
                if ($userStatus->num_rows) {
                    $result = $userStatus->fetch_assoc();
                    if ($result['status'] == 'active') {
                        $updateUserStatus = $this->connection->query("UPDATE users set status = 'de-active' where id = $id");
                    } else {
                        $updateUserStatus = $this->connection->query("UPDATE users set status = 'active' where id = $id");
                    }
                } else {
                    throw new Exception ("Invalid user!", 400);
                }
                $this->status = 200;
                $this->message = "Status change successfully!";
            } else {
                throw new Exception ("Id must be required", 400);
            }
            
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: $this->status, error: $this->message, Line: " . $error->getLine() . PHP_EOL;
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