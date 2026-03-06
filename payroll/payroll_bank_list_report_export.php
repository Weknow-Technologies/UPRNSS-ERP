<?php
include("scripts/settings.php");
error_reporting(E_ALL);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
$sheet->setCellValue('F3', 'Name Of Bank');
$sheet->setCellValue('G3', 'Branch Name');
$sheet->setCellValue('H3', 'IFSC Code ');
$sheet->setCellValue('I3', 'Account Number');
$sheet->setCellValue('J3', 'Net Payment ');


$sql ='SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as emp_id , `employee`.`employee_name` ,`payslip_report`.`company_id` ,`employee`.`employee_code` ,`employee`.`bank_name` ,`employee`.`acount_number` ,`employee`.`ifsc_code` ,`employee`.`branch` , `employee`.`employee_designation` , `payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`total_income` , `payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`  WHERE 1=1 ';
	
if(isset($_POST['company_name'])){
	if($_POST['company_name'] != ''){
		$sql .= ' AND `payslip_report`.`company_id`="'.$_POST['company_name'].'" ';
	}
}
if(isset($_POST['emp_name'])){
	if($_POST['emp_name'] != ''){
		$sql .= ' AND `payslip_report`.`emp_id`="'.$_POST['emp_name'].'" ';
	}
}
if(isset($_POST['financial_year'])){
	if($_POST['financial_year'] != ''){
		$sql .= ' AND `payslip_report`.`financial_year`="'.$_POST['financial_year'].'" ';
	}
}
if(isset($_POST['for_month'])){
	if($_POST['for_month'] != ''){
		$sql .= ' AND `payslip_report`.`for_month`="'.$_POST['for_month'].'" ';
	}
}
$sql .= ' ORDER BY ABS(`payslip_report`.`financial_year`) DESC , ABS(`payslip_report`.`for_month`) DESC , ABS(`payslip_report`.`company_id`) DESC , ABS(`payslip_report`.`total`) DESC';
// echo $sql;
$result = execute_query($sql);
$i=1;
$total_basic_salary = 0;
$total_payable_amount = 0;
$total_allowances = 0;
$total_deduction = 0;
$total_grand = 0;
$allo[] = 0;
$allo = array();
$dedu = array();
$advance_amount = 0;
$remainnung_amount = 0;
$row_count=4;
while($row=mysqli_fetch_array($result)){ 
	if($row['total'] > 0){
		$sql_amount_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="salary_amount"';
		$result_amount_payable = execute_query($sql_amount_payable);
		$row_amount_payable = mysqli_fetch_array($result_amount_payable);
		// $total_payable_amount += $row_amount_payable['amount'];

		$sql = 'select * from dp_designation where sno="'.$row['employee_designation'].'"';
			$des = mysqli_query($db_erp, $sql);
			if(mysqli_num_rows($des)!=0){
				$des = mysqli_fetch_assoc($des);
				}
			else{
				unset($des);
				$des['designation'] = '';
			}
		$sql = 'select * from uprnss_division where s_no="'.$row['company_id'].'"';
			$row_div = mysqli_query($db_erp, $sql);
			if(mysqli_num_rows($row_div)!=0){
				$row_div = mysqli_fetch_assoc($row_div);
				}
			else{
				unset($row_div);
				$row_div['division_name'] = '';
			}
		$sheet->setCellValue('A'.$row_count, $i++);
		$sheet->setCellValue('B'.$row_count, $row["employee_code"]);
		$sheet->setCellValue('C'.$row_count, $row["employee_name"]);
		$sheet->setCellValue('D'.$row_count, $des['designation']);
		$sheet->setCellValue('E'.$row_count, $row_div["division_name"]);
		$sheet->setCellValue('F'.$row_count, $row["bank_name"]);
		$sheet->setCellValue('G'.$row_count, $row["branch"]);
		$sheet->setCellValue('H'.$row_count, $row["ifsc_code"]);
		//$sheet->setCellValue('I'.$row_count, $row['acount_number']);
		$sheet->getCell('I'.$row_count)->setValueExplicit($row['acount_number'],\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING2);
		$sheet->setCellValue('J'.$row_count, $row['total']);
		$total_grand += $row['total'];
		$row_count++;
	}
	$sheet->setCellValue('I'.$row_count, "Total : ");
	$sheet->setCellValue('J'.$row_count, $total_grand);
	$sheet->getStyle('I'.$row_count)->getFont()->setSize(14);
	$sheet->getStyle('J'.$row_count)->getFont()->setSize(14);
}

foreach ($sheet->getColumnIterator() as $column) {
   $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}


// Redirect output to a client’s web browser (Xls)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Payroll_'.date("Y_m_d_H_i_s").'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

//$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
?>