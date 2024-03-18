<?php
session_start();

class UserLogin extends Connection
{
    protected $status, $message, $userType;

    function __construct()
    {
        parent::__construct();
    }
    function userLogin()
    {
        try {
            $userArr = "";
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);

            $dataArr = [
                'user name' => isset($data['userName']) ? $data['userName'] : NULL,
                'password' => isset($data['password']) ? $data['password'] : NULL,
            ];

            foreach ($dataArr as $key => $value) {
                if ($value == NULL) {
                    $flag = false;
                    throw new Exception($key . " is required", 400);
                }
            }
            $dataArr = sanitizeData($dataArr);
            $userName = $dataArr['user name'];
            $password = $dataArr['password'];
            $userPassword = $this->connection->query("SELECT password, id from users where email = '$userName' || user_name = '$userName'");
            
            if ($userPassword->num_rows) {
                $result = $userPassword->fetch_assoc();
                $userPassword = $result['password'];
                $uId = $result['id'];

                if (password_verify($password, $userPassword)) {
                    $user = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.gender, u.phone_number, g.grade, u.message, h.name, u.status, u.is_verified, u.is_approved from users as u inner join grades as g on u.grade_id = g.id inner join hobbies as h on u.id = h.user_id where (email = '$userName' || user_name = '$userName')");
                    
                    if ($user->num_rows) {
                        $row = $user->fetch_assoc();
                        $userArr = $row;

                        if ($row['status'] == 'active' && $row['is_verified'] && $row['is_approved'] ) {
                            $_SESSION['user'] = $userName;
                        }
                        $this->status = 200;
                        $this->message = "Login successfully";
                    } else {
                        throw new Exception("Email or password invalid!", 400);
                    }
                } else {
                    throw new Exception ("Password not valid!", 400);
                }
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
            $jwtToken = "";         
            if ($this->status == 200) {
                $jwtToken = createJwtToken($uId);
            }
            $response = [
                'status' => $this->status,
                'message' => $this->message,
                'token' => $jwtToken,
                'user' => $userArr,
            ];

            http_response_code($this->status);
            header("content-type: application/json");
            return json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}
?>