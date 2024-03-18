<?php
class Connection
{
    private $serverName;
    private $userName;
    private $password;
    private $dbname;
    public $connection;

    public function __construct()
    {
        global $database;
        $this->serverName = $database['serverName'];
        $this->userName = $database['userName'];
        $this->password = $database['password'];
        $this->dbname = $database['dbName'];
        try {
            $this->connection = new mysqli($this->serverName, $this->userName, $this->password, $this->dbname);
        } catch (Exception $error) {
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $error->getCode() . ", error: " . $error->getMessage() . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
            die("Connection failed: " . $error->getMessage());
        }
    }
}

?>