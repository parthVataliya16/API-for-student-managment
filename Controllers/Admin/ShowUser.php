<?php

class ShowUser extends Connection
{
    private $status, $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function showUser($id)
    {
        try {
            $userArr = [];
            $user = $this->connection->query("SELECT first_name, last_name, email, phone_number, gender, message, grade_id, user_name, profile_picture from users where id = $id");
            if ($user->num_rows) {
                $result = $user->fetch_assoc();
                $gradeId = $result['grade_id'];
                $userGrade = $this->connection->query("SELECT grade from grades where id = $gradeId");
                $grade = $userGrade->fetch_assoc();
                $grade = $grade['grade'];

                $userHobby = $this->connection->query("SELECT name from hobbies where user_id = $id");
                $hobbies = "";
                while ($hobby = $userHobby->fetch_assoc()) {
                    $hobbies .= $hobby['name'];
                }
                $result['grade'] = $grade;
                $result['hobbies'] = $hobbies;

                array_push($userArr, $result);
                $this->status = 200;
                $this->message = "Fetch user data!";
            } else {
                throw new Exception ("No user found", 400);
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
                'user' => $userArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}

?>