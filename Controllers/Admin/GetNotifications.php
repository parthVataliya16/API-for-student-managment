<?php

class GetNotifications extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function getNotifications()
    {
        try {
            $notificationArr = [];
            $notification = $this->connection->query("SELECT id, subject, recipient from notifications");
            while ($result = $notification->fetch_assoc()) {
                array_push($notificationArr, $result);
            }
            $this->status = 200;
            $this->message = "Get all notifications";
        } catch (Exception $error) {
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " . $this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $responseArr = [
                'status' => $this->status,
                'message' => $this->message,
                'notifications' => $notificationArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}
?>