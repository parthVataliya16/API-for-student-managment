<?php

class LinkExpire extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function linkExpire($token)
    {
        try {
            $selectUser = $this->connection->query("SELECT created_at from reset_passwords where token = '$token'");
            if ($selectUser->num_rows) {
                $result = $selectUser->fetch_assoc();
                $createdTime = strtotime($result['created_at']);
                $currentTime = strtotime("now");
                $timeDiffernce = $currentTime - $createdTime;
    
                if (($timeDiffernce / 3600) > (24)) {
                    throw new Exception("Invalid link!", 400);
                } else {
                    $this->status = 200;
                    $this->message = "Link valid";
                }
    
            } else {
                throw new Exception("Invalid link!", 400);
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