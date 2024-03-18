<?php

class GetNotificationData extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function getNotificationData($id)
    {
        try {
            $notificationArr = [];
            $notification = $this->connection->query("SELECT subject, recipient, description from notifications where id = $id");
            if ($notification->num_rows) {
                $row = $notification->fetch_assoc();
                array_push($notificationArr, $row);

                $this->status = 200;
                $this->message = "Notification data fetch successfully!";
            } else {
                throw new Exception ("Notification not found!", 204);
            }
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
                'notification' => $notificationArr,
            ];

            http_response_code($this->status);
            header('content-type: application/json');
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}

?>