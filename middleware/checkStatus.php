<?php
use Dotenv\Dotenv;

require_once realpath("./../../vendor/autoload.php");
$dotenv = Dotenv::createImmutable('./../../');
$dotenv->load();
$database = include('./../../config/database.php');
require_once './../../Controllers/Connection.php';

class CheckStudentStatus extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkStudentStatus()
    {
        $user = $_SESSION['user'];
        $selectStudentStatus = $this->connection->query("SELECT status from users where email = '$user' || user_name = '$user' ");
        $result = $selectStudentStatus->fetch_assoc();
        if ($result['status'] == 'de-active') {
            $_SESSION['status'] = 'de-active';
            unset($_SESSION['user']);
            header('Location: ./../auth/login.php');
            exit;
        }
    }
}

$studentStatus = new CheckStudentStatus();
$studentStatus->checkStudentStatus();

?>