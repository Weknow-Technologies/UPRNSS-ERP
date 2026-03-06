<?php
include("scripts/settings.php");
error_reporting(E_ALL);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$_POST = $_SESSION['payroll_post'];

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Payroll');

$sheet->mergeCells('A1:J1');
$sheet->mergeCells('A2:J2');

$sheet->setCellValue('A1', 'उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.) ');
$sheet->setCellValue('A2', 'UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH LTD.(UPRNSS)');
$sheet->getStyle('A1')->getFont()->setSize(16);
$sheet->getStyle('A2')->getFont()->setSize(16);
$sheet->getStyle('A3:J3')->getFont()->setSize(14);

$sheet->setCellValue('A3', 'S.No');
$sheet->setCellValue('B3', 'Employee Code');
$sheet->setCellValue('C3', 'Employee Name');
$sheet->setCellValue('D3', 'Designation');
$sheet->setCellValue('E3', 'Division Name');
$sheet->setCellValue('F3', 'Pan No.');
$sheet->setCellValue('G3', 'Gross Salary');
$sheet->setCellValue('H3', 'CPF');
$sheet->setCellValue('I3', 'Income Tax');

$sql = 'SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as emp_id , `employee`.`employee_name` ,`employee`.`company_id` ,`employee`.`employee_code` ,`employee`.`bank_name` ,`employee`.`acount_number`, `employee`.`pan` ,`employee`.`ifsc_code` ,`employee`.`branch` , `employee`.`employee_designation` , `payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`total_income` , `payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id` WHERE 1=1 ';

if (isset($_POST['company_name']) && $_POST['company_name'] != '') {
    $sql .= ' AND `employee`.`company_id`="' . $_POST['company_name'] . '" ';
}
if (isset($_POST['emp_name']) && $_POST['emp_name'] != '') {
    $sql .= ' AND `payslip_report`.`emp_id`="' . $_POST['emp_name'] . '" ';
}
if (isset($_POST['financial_year']) && $_POST['financial_year'] != '') {
    $sql .= ' AND `payslip_report`.`financial_year`="' . $_POST['financial_year'] . '" ';
}
if (isset($_POST['for_month']) && $_POST['for_month'] != '') {
    $sql .= ' AND `payslip_report`.`for_month`="' . $_POST['for_month'] . '" ';
}
$sql .= ' ORDER BY ABS(`payslip_report`.`financial_year`) DESC , ABS(`payslip_report`.`for_month`) DESC , ABS(`employee`.`company_id`) DESC , ABS(`payslip_report`.`total`) DESC';

$result = execute_query($sql);
$i = 1;

$total_income_tax = 0;
$total_cpf = 0;
$total_deduction = 0;
$total_grand = 0;
$income_tax = 0;
$grand_total_income = 0;
$row_count = 4; // Start from row 4 for data

while ($row = mysqli_fetch_array($result)) {
    $sql_amount_income_tax = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="' . $row['payslip_id'] . '" AND `head_id`="18" AND `amount` != "0"';
    $result_amount_income_tax = execute_query($sql_amount_income_tax);
    if (mysqli_num_rows($result_amount_income_tax) > 0) {
        $row_amount_income_tax = mysqli_fetch_array($result_amount_income_tax);
        $income_tax = $row_amount_income_tax['amount'];

        if ($income_tax > 0) {
            $sql = 'SELECT * FROM dp_designation WHERE sno="' . $row['employee_designation'] . '"';
            $des = mysqli_query($db_erp, $sql);
            if (mysqli_num_rows($des) != 0) {
                $des = mysqli_fetch_assoc($des);
            } else {
                $des['designation'] = '';
            }

            $sql = 'SELECT * FROM uprnss_division WHERE s_no="' . $row['company_id'] . '"';
            $row_div = mysqli_query($db_erp, $sql);
            if (mysqli_num_rows($row_div) != 0) {
                $row_div = mysqli_fetch_assoc($row_div);
            } else {
                $row_div['division_name'] = '';
            }

            $sql_amount_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="' . $row['payslip_id'] . '" AND `head_id`="salary_amount"';
            $result_amount_payable = execute_query($sql_amount_payable);
            $row_amount_payable = mysqli_fetch_array($result_amount_payable);

            $sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="' . $row['emp_id'] . '" AND `salary_generation_month`="' . $row['for_month'] . '" AND `financial_year`="' . $row['financial_year'] . '"';
            $result_attendance = execute_query($sql_attendance);
            $row_attendance = mysqli_fetch_array($result_attendance);

            $grand_total_income += $row["total_income"];

            $sql_head_cpf = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
            $result_head_cpf = execute_query($sql_head_cpf);
            $cpf = 0;
            while ($row_head_cpf = mysqli_fetch_array($result_head_cpf)) {
                $sql_amount_cpf = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="' . $row['payslip_id'] . '" AND `head_id`="9"';
                $result_amount_cpf = execute_query($sql_amount_cpf);
                $row_amount_cpf = mysqli_fetch_array($result_amount_cpf);

                $cpf = $row_amount_cpf['amount'];
            }

            $total_income_tax += $income_tax;
            $total_cpf += $cpf;
            
            // Writing data to the spreadsheet
            $sheet->setCellValue('A' . $row_count, $i++);
            $sheet->setCellValue('B' . $row_count, $row["employee_code"]);
            $sheet->setCellValue('C' . $row_count, $row["employee_name"]);
            $sheet->setCellValue('D' . $row_count, $des['designation']);
            $sheet->setCellValue('E' . $row_count, $row_div["division_name"]);
            $sheet->setCellValue('F' . $row_count, $row["pan"]);
            $sheet->setCellValue('G' . $row_count, $row["total_income"]);
            $sheet->setCellValue('H' . $row_count, $cpf);
            $sheet->setCellValue('I' . $row_count, $income_tax);
            $total_grand += $income_tax;
            $row_count++;
        }
    }
}

// Writing the total row
$sheet->setCellValue('H' . $row_count, "Total : ");
$sheet->setCellValue('I' . $row_count, $total_grand);
$sheet->getStyle('H' . $row_count)->getFont()->setSize(14);
$sheet->getStyle('I' . $row_count)->getFont()->setSize(14);

// Auto-sizing columns
foreach ($sheet->getColumnIterator() as $column) {
    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}

// Redirect output to a client’s web browser (Xls)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Payroll_' . date("Y_m_d_H_i_s") . '.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
?>
