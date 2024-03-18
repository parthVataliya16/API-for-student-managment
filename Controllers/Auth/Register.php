<?php

class Register extends Connection
{
    protected $responseStatus, $responseMessage, $sendMail;
    
    public function __construct()
    {
        parent::__construct();
        $this->sendMail = new SendMail();
    }

    public function register()
    {
        try {
            $userData = [];
            $role = "Student";
            $createdAt = date("Y-m-d H:i:s");
            $targetDir = './../public/uploads/';
            $verificationToken = bin2hex(random_bytes(16));
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);
            $flag = true;

            $dataArr = [
                'first name' => isset($data['firstName']) ? $data['firstName'] : NULL,
                'last name' => isset($data['lastName']) ? $data['lastName'] : NULL,
                'email' => isset($data['email']) ? $data['email'] : NULL,
                'password' => isset($data['password']) ? $data['password'] : NULL,
                'confirm password' => isset($data['cPassword']) ? $data['cPassword'] : NULL,
                'phone number' => isset($data['phoneNumber']) ? $data['phoneNumber'] : NULL,
                'gender' => isset($data['gender']) ? $data['gender'] : NULL,
                'hobby' => isset($data['hobby']) ? $data['hobby'] : NULL,
                'grades' => isset($data['grades']) ? $data['grades'] : NULL,
                'message' => isset($data['message']) ? $data['message'] : NULL,
                'profile picture' => isset($data['profilePicture']) ? $data['profilePicture'] : NULL
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

            if (! $dataArr) {
                throw new Exception("Invalid email address!", 400);
            }

            if ($flag) {
                $firstname = $dataArr['first name'];
                $lastName = $dataArr['last name'];
                $email = $dataArr['email'];
                $data['password'] = $dataArr['confirm password'] == $dataArr['password'] ? $dataArr['password'] : throw new Exception("Password and confirm password must be same", 400);

                $password = $dataArr['password'];
                $phoneNumber = $dataArr['phone number'];
                $gender = $dataArr['gender'];
                $hobby = $dataArr['hobby'];
                $grades = $dataArr['grades'];
                $message = $dataArr['message'];
                $userName = explode('@', $email)[0];
                $profilePicture = base64_decode($dataArr['profile picture']);
                
                // if (!empty($_FILES["profilePicture"]["tmp_name"])) { // && in_array($imageFileType, $imgType
                $selectGradeID = $this->connection->query("SELECT id from grades where grade = '$grades'");
                $result = $selectGradeID->fetch_assoc();
                $gradeID = $result['id'];
                $imageName = time();
                
                $addStudent = $this->connection->prepare("INSERT INTO `users`(`first_name`, `last_name`, `email`, `password`, `phone_number`, `gender`, `message`, `grade_id`, `user_name`, `role`, `profile_picture`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $addStudent->bind_param("ssssississss", $firstname, $lastName, $email, $password, $phoneNumber, $gender, $message, $gradeID, $userName, $role, $imageName, $createdAt);
                
                if ($addStudent->execute()) {
                    file_put_contents($targetDir . $imageName, $profilePicture);

                    $selectID = $this->connection->query("SELECT id from users where email = '$email'");
                    $result = $selectID->fetch_assoc();
                    $id = $result['id'];

                    $addVerificationToken = $this->connection->prepare("INSERT into mail_verifications (token, user_id, created_at) values (?, ?, ?)");
                    $addVerificationToken->bind_param("sis", $verificationToken, $id, $createdAt);
                    $addVerificationToken->execute();
                    
                    $addHobby = $this->connection->prepare("INSERT into hobbies (user_id, name) values (?,?)");
                    foreach($hobby as $value) {
                        $addHobby->bind_param("is", $id, $value);
                        $addHobby->execute();
                    }
                    $sendMailFrom = 'vatliyaparth111@gmail.com';
                    $sendMailTo = $email;
                    $subject = 'Mail verification';
                    $body = "<a href='http://localhost/practice/userManagement/views/auth/emailVerified.php?token=$verificationToken'> click me</a>";
                    
                    // $this->sendMail->sendMail($sendMailFrom, $sendMailTo, $subject, $body);

                    $user = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.gender, u.phone_number, g.grade, u.message, u.profile_picture from users as u inner join grades as g on u.grade_id = g.id where u.id = $id");
                    $userHobby = $this->connection->query("SELECT name from hobbies where user_id = $id");
                    $hobby = [];

                    while($row = $userHobby->fetch_assoc()) {
                        array_push($hobby, $row['name']);
                    }
                
                    $row = $user->fetch_assoc();
                    $row['hobby'] = $hobby;
                    array_push($userData, $row);
                    
                    $this->responseStatus = 201;
                    $this->responseMessage = "Registration successfully. Mail send to your mail address";
                }
            } 
        } catch (Exception $error) {
            $this->responseStatus = $error->getCode();
            $this->responseMessage = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: $this->responseStatus, error: $this->responseMessage, Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen('./../errors.log', 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {   
            $responseArr = array(
                'status' =>$this->responseStatus,
                'message' =>$this->responseMessage,
                'user' => $userData
            );

            http_response_code($this->responseStatus);
            header("content-type: application/json");
            return json_encode($responseArr, JSON_PRETTY_PRINT);
        }
    }
}
?>
