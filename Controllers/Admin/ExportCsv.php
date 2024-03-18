<?php
use Dotenv\Dotenv;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Style;

require("./../../vendor/autoload.php");
$dotenv = Dotenv::createImmutable("./../../");
$dotenv->load();

$database = include('./../../config/database.php');
require_once './../Connection.php';

class ExportCsv extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function exportCsv()
    {
        try {
            if (isset($_POST['export'])) {
                header('content-type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=student.xlsx');
    
                $option = new Options();
                $writer = new Writer($option);
                $writer->openToFile('php://output');
                $style = new Style();
                $style->setFontBold();
                $style->setCellAlignment(CellAlignment::CENTER);
                $style->setCellVerticalAlignment(CellAlignment::CENTER);
                $header = ['ID', 'First_name', 'Last_name', 'Email', 'Phone_nubmer', 'Gender', 'Grade', 'Status'];
                $lengthOfHeader = count($header);
                $headerRow = Row::fromValues($header, $style);
                
                $activeUserSheet = $writer->getCurrentSheet();
                $activeUserSheet->setName('Active users');
                for ($i = 0; $i < $lengthOfHeader; $i++) {
                    $option->mergeCells($i, 1, $i, 2, 0);
                }
                $writer->addRow($headerRow);
                
                $DeactiveUserSheet = $writer->addNewSheetAndMakeItCurrent();
                $DeactiveUserSheet->setName('De-active users');
                for ($i = 0; $i < $lengthOfHeader; $i++) {
                    $option->mergeCells($i, 1, $i, 2, 1);
                }
                $writer->addRow($headerRow);

                $offset = 0;
                $limit= 50;
                $data = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.phone_number, u.gender, g.grade, u.status from users as u inner join grades as g on u.grade_id = g.id limit $offset, $limit");

                while ($data->num_rows) {
                    while ($row = $data->fetch_assoc()) {
                        if ($row['status'] == 'active') {
                            $writer->setCurrentSheet($activeUserSheet);
                            $writer->addRow(Row::fromValues($row));
                        } else {
                            $writer->setCurrentSheet($DeactiveUserSheet);
                            $writer->addRow(Row::fromValues($row));
                        }
                    }
                    $offset += $limit;
                    $data = $this->connection->query("SELECT u.id, u.first_name, u.last_name, u.email, u.phone_number, u.gender, g.grade, u.status from users as u inner join grades as g on u.grade_id = g.id limit $offset, $limit");
                }
                $writer->close();
            }
        } catch (Exception $error) {
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $error->getCode() . ", error: " . $error->getMessage() . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen('./../../errors.log', 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        }
        
    }
}
$exportCsv = new ExportCsv();
$exportCsv->exportCsv();

?>