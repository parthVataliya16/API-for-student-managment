<?php

// use Dompdf\Adapter\CPDF;
use Dotenv\Dotenv;
use Dompdf\Dompdf;

require_once './../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable("./../../");
$dotenv->load();

$database = include('./../../config/database.php');
require_once './../Connection.php';

class GeneratePdf extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function generatePdf()
    {
        $tableHead = "
        <html>
        <style>
            table {
                font-family: arial;
                width:100%;
                border-collapse: collapse;
            }
            td, th {
                border: 1px solid black;
                text-align: left;
                padding: 8px;
            }
            tr:nth-child(even) {
                background-color: #c9c8c8a8;
            }
        </style>
        <table border='1'>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone_number</th>
                <th>Gender</th>
                <th>Grade</th>
            </tr>";
        
        $tableBody = "";
        $numberOfRows = $this->connection->query("SELECT count(*) from users");
        $result = $numberOfRows->fetch_assoc();
        $numberOfRows = $result['count(*)'];
        $chunkOfData = (int) $numberOfRows / 50;
        for ($i = 0; $i <= $chunkOfData; $i++) {
            $chunk = 0;
            $j = $i;
            while ($j) {
                $chunk += 50;
                $j--;
            }
            $data = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.gender, u.phone_number, g.grade from users as u inner join grades as g on u.grade_id = g.id limit $chunk, 50");
            while ($row = $data->fetch_assoc()) {
                $row = "<tr>" .
                "<td style='padding:5px;'>" . $row['id'] . "</td>" .
                "<td style='padding:5px;'>" . $row['first_name'] . $row['last_name'] . "</td>" .
                "<td style='padding:5px;'>" . $row['email'] . "</td>" .
                "<td style='padding:5px;'>" . $row['phone_number'] . "</td>" .
                "<td style='padding:5px;'>" . $row['gender'] . "</td>" .
                "<td style='padding:5px;'>" . $row['grade'] . "</td>" .
                "</tr>";
                $tableBody .= $row;
            }
        }

        $tableEnd = "</table> </html>";
        $table = $tableHead . $tableBody . $tableEnd;

        $dompdf = new Dompdf();
        $dompdf->loadHtml($table);
        $dompdf->setPaper('A4', 'landscape');

        // $protectPdf = new CPDF("letter", "portrait", $dompdf);
        $dompdf->render();
        $dompdf->getCanvas()->get_cpdf()->setEncryption("12345", "12345");


        $dompdf->stream();
    }
}

$generatePdf = new GeneratePdf();
$generatePdf->generatePdf();

?>