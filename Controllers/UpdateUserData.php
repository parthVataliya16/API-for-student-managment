<?php

class UpdateUserData extends Connection
{
    protected $responseStatus, $responseMessage;

    function __construct()
    {
        parent::__construct();
    }

    function updateUserData($id = 0)
    {
        try {
            $userData = [];
            
            $updatedAt = date("Y/m/d");
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);

            $dataArr = [
                'first name' => isset($data['firstName']) ? $data['firstName'] : NULL,
                'last name' => isset($data['lastName']) ? $data['lastName'] : NULL,
                'email' => isset($data['email']) ? $data['email'] : NULL,
                'phone number' => isset($data['phoneNumber']) ? $data['phoneNumber'] : NULL,
                'gender' => isset($data['gender']) ? $data['gender'] : NULL,
                'hobby' => isset($data['hobby']) ? $data['hobby'] : NULL,
                'grades' => isset($data['grades']) ? $data['grades'] : NULL,
                'message' => isset($data['message']) ? $data['message'] : NULL,
                'profile picture' => isset($data['profilePicture']) ? $data['profilePicture'] : NULL,
            ];

            $lengthOfPhoneNumber = strlen((string) $dataArr['phone number']);

            if ($lengthOfPhoneNumber > 10) {
                throw new Exception("Maximum length of phone number is 10", 400);
            }            

            foreach ($dataArr as $key => $value) {
                if ($value == NULL) {
                    $flag = false;
                    throw new Exception($key . " is required", 400);
                }
            }
            $dataArr = sanitizeData($dataArr);


            $firstname = $dataArr['first name'];
            $lastname = $dataArr['last name'];
            $email = $dataArr['email'];
            $grade = $dataArr['grades'];
            $phoneNumber = $dataArr['phone number'];
            $gender = $dataArr['gender'];
            $hobby = $dataArr['hobby'];
            $message = $dataArr['message'];
            $userName = explode('@', $email)[0];
            $profilePicture = base64_decode($dataArr['profile picture']);

            $selectUser = $this->connection->query("SELECT id from users where id = $id");

            if ($selectUser->num_rows) {
                $selectGradeID = $this->connection->query("SELECT id from grades where grade = '$grade'");
                $row = $selectGradeID->fetch_assoc();
                $gradeID = $row['id'];
                
                $deleteHobby = $this->connection->query("DELETE from hobbies where user_id = $id");
                $updateHobby = $this->connection->prepare("INSERT into hobbies (name, user_id) values(?, ?)");
                
                foreach ($hobby as $value) {
                    $updateHobby->bind_param('si', $value, $id);
                    $updateHobby->execute();
                }
                
                $selectImage = $this->connection->query("SELECT profile_picture from users where id = $id");
                $result = $selectImage->fetch_assoc();
                $userImage = $result['profile_picture'];
                
                if (file_exists("./../public/uploads/" . $userImage)) {
                    unlink('./../public/uploads/' . $userImage);
                    $imageName = time() . '.jpg';
                    file_put_contents('./../public/uploads/' . $imageName, $profilePicture);
                }
                
                $updateUserData = $this->connection->query("UPDATE users set first_name = '$firstname', last_name = '$lastname', email = '$email', grade_id = $gradeID, phone_number = $phoneNumber, gender = '$gender', message = '$message', user_name = '$userName', profile_picture = '$imageName', updated_at = '$updatedAt' where id = $id");
                
                $user = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.gender, u.phone_number, g.grade, u.message, u.profile_picture from users as u inner join grades as g on u.grade_id = g.id where u.id = $id");
                $userHobby = $this->connection->query("SELECT name from hobbies where user_id = $id");
                $hobby = [];

                while($row = $userHobby->fetch_assoc()) {
                    array_push($hobby, $row['name']);
                }
                
                $row = $user->fetch_assoc();
                $row['hobby'] = $hobby;
                array_push($userData, $row);
                
                $this->responseStatus = 200;
                $this->responseMessage = "User updated successfully";
                $_SESSION['status'] = $this->responseStatus;
            } else {
                throw new Exception ("User not found!", 400);
            }
        } catch (Exception $error) {
            $this->responseStatus = $error->getCode();
            $this->responseMessage = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: $this->responseStatus, error: $this->responseMessage, Line: " . $error->getLine() . PHP_EOL;
            file_put_contents('./../../errors.log', $errorMessage, FILE_APPEND);
        } finally {
            $response = [
                'status' => $this->responseStatus,
                'message' => $this->responseMessage,
                'user' => $userData
            ];

            http_response_code($this->responseStatus);
            header("content-type: application/json");
            return json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}

?>