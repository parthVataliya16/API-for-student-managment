<?php

class UserData extends Connection
{
    protected $status, $message;

    function __construct()
    {
        parent::__construct();
    }

    function userData($id = 0)
    {
        try {
            $userArr = [];
            if ($id) {
                $users = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.gender, u.phone_number, g.grade, u.message, u.profile_picture from users as u inner join grades as g on u.grade_id = g.id where u.id = $id");
                $userHobbies = $this->connection->query("SELECT name from hobbies where user_id = $id");
                $hobby = [];

                if ($userHobbies->num_rows) {
                    while ($row = $userHobbies->fetch_assoc()) {
                        array_push($hobby, $row['name']);
                    }
                }
                if ($users->num_rows) {
                    $row = $users->fetch_assoc();
                    $row['hobby'] = $hobby;
                    array_push($userArr, $row);
                    
                    $this->status = 200;
                    $this->message = "Fetch user's data successfully!";
                } else {
                    throw new Exception ("Invalid user id!", 400);
                }
            } else {
                $users = $this->connection->query("SELECT u.id, u.profile_picture, u.first_name, u.last_name, u.email, u.gender, u.phone_number, g.grade, u.status, u.is_verified, u.is_approved from users as u inner join grades as g on u.grade_id = g.id where u.id != 1 order by created_at DESC");

                if ($users->num_rows) {
                    while($row = $users->fetch_assoc()) {
                        $row['userName'] = $row['first_name'] . " " . $row['last_name'];
                        $row = array_slice($row, 0, 2, true) + ['userName' => $row['userName']] + array_slice($row, 2, count($row) - 1, true);
                        $row['userName'] = ucfirst($row['userName']);
                        unset($row['first_name']);
                        unset($row['last_name']);

                        $hobby = [];
                        $id = $row['id'];
                        $userHobbies = $this->connection->query("SELECT name from hobbies where user_id = $id");
                        
                        if ($userHobbies->num_rows) {
                            while ($result = $userHobbies->fetch_assoc()) {
                                $result['name'] == NULL ? : array_push($hobby, $result['name']);
                            }
                        }  
                        $row['hobby'] = $hobby;
                        array_push($userArr, $row);
                    }
                    $this->status = 200;
                    $this->message = "Fetch all user's data successfully!";
                }
            }
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " . $this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $responseArr = [
                'status' => $this->status,
                'message' => $this->message,
                'users' => $userArr
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}
?>