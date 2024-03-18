<?php

class SendNotification extends Connection
{
    private $status, $message, $sendMail;

    public function __construct($mail = new SendMail())
    {
        parent::__construct();
        $this->sendMail = $mail;
    }

    public function sendNotification()
    {
        try {
            // $subject = $_POST['subject'];
            // $recipient = $_POST['recipient'];
            // $description = $_POST['description'];
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);

            $dataArr = [
                'subject' => isset($data['subject']) ? $data['subject'] : NULL,
                'recipient' => isset($data['recipient']) ? $data['recipient'] : NULL,
                'description' => isset($data['description']) ? $data['description'] : NULL,
            ];

            foreach ($dataArr as $key => $value) {
                if ($value == NULL) {
                    throw new Exception($key . " is required", 400);
                }
            }
            $dataArr = sanitizeData($dataArr);

            $subject = $dataArr['subject'];
            $recipient = $dataArr['recipient'];
            $description = $dataArr['description'];
            $notificationArr = [
                'subject' => $subject,
                'recipient' => $recipient,
                'description' => $description
            ];

            $usersMailArr = explode(",", $recipient);
            $lengthOfUserMailArr = count($usersMailArr);

            if ($lengthOfUserMailArr > 5) {
                throw new Exception ("Maximum number of recipint selected is 5", 400);
            } else {
                $insertNotification = $this->connection->prepare("INSERT into notifications (subject, recipient, description) values (?, ?, ?)");
                $insertNotification->bind_param("sss", $subject, $recipient, $description);
                if ($insertNotification->execute()) {
                    $selectAdminMail = $this->connection->query("SELECT email from users where id = 1");
                    $result = $selectAdminMail->fetch_assoc();
                    $adminEmail = $result['email'];
                    $sendMailFrom = $adminEmail;
                    $subject = $subject;
                    $body = $description;
                    
                    for ($i = 0; $i < $lengthOfUserMailArr - 1; $i++ ) {
                        $sendMailTo = $usersMailArr[$i];
                        $this->sendMail->sendMail($sendMailFrom, $sendMailTo, $subject, $body);
                    }
                    
                    $this->status = 200;
                    $this->message = "Send mail successfully!";
                    
                } else {
                    throw new Exception ("Notification is not send!", 400);
                }
            }

        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " .$this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen('./../errors.log', 'a');
            fwrite($errorFile, $errorMessage);
        } finally {
            $response = [
                'status' => $this->status,
                'message' => $this->message,
                'notification' => $notificationArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}

?>