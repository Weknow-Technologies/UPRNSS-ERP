<?php
include("scripts/settings.php");
error_reporting(E_ALL);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

$_POST = $_SESSION['payroll_post'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Payroll');

// Merging Cells and Setting Title
$sheet->mergeCells('A1:AH1');
$sheet->mergeCells('A2:AH2');
$sheet->mergeCells('A3:A4');
$sheet->mergeCells('B3:B4');
$sheet->mergeCells('C3:C4');
$sheet->mergeCells('D3:D4');
$sheet->mergeCells('E3:E4');
$sheet->mergeCells('F3:F4');
$sheet->mergeCells('G3:G4');
$sheet->mergeCells('H3:O3');
$sheet->mergeCells('P3:R3');
$sheet->mergeCells('AG3:AG4');
$sheet->mergeCells('AH3:AH4');
$sheet->mergeCells('AI3:AI3');
$sheet->mergeCells('S3:AA3');
$sheet->mergeCells('AB3:AF3');

$sheet->setCellValue('A1', 'उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.) ');
$sheet->setCellValue('A2', 'UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH LTD.(UPRNSS)');

$sheet->getStyle('A1')->getFont()->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getFont()->setSize(16);
$sheet->getStyle('A3:J3')->getFont()->setSize(14);

// Setting Column Headers
$sheet->setCellValue('A3', 'S.No');
$sheet->setCellValue('B3', 'Employee Name');
$sheet->setCellValue('C3', 'Employee Name');
$sheet->setCellValue('D3', 'UAN NO.');
$sheet->setCellValue('E3', 'DOJ');
$sheet->setCellValue('F3', 'Employee code');
$sheet->setCellValue('G3', 'Division Name');

$sheet->setCellValue('H3', 'Rate of Salary');
$sheet->setCellValue('H4', 'Basic');
$sheet->setCellValue('I4', 'Spl Pay');
$sheet->setCellValue('J4', 'Grade Pay');
$sheet->setCellValue('K4', 'DA');
$sheet->setCellValue('L4', 'CCA');
$sheet->setCellValue('M4', 'HRA');
$sheet->setCellValue('N4', 'MA');
$sheet->setCellValue('O4', 'CPF');

$sheet->setCellValue('P3', 'Pay Days');
$sheet->setCellValue('P4', 'Month Days');
$sheet->setCellValue('Q4', 'Paybill Days');
$sheet->setCellValue('R4', 'NCP Days');

$sheet->setCellValue('S3', 'Earned Salary');
$sheet->setCellValue('S4', 'Basic');
$sheet->setCellValue('T4', 'Spl Pay');
$sheet->setCellValue('U4', 'Grade Pay');
$sheet->setCellValue('V4', 'DA');
$sheet->setCellValue('W4', 'CCA');
$sheet->setCellValue('X4', 'HRA');
$sheet->setCellValue('Y4', 'MA');
$sheet->setCellValue('Z4', 'CPF');
$sheet->setCellValue('AA4', 'Gross Pay');

$sheet->setCellValue('AB3', 'Deduction');
$sheet->setCellValue('AB4', 'CPF Emp+');
$sheet->setCellValue('AC4', 'GIS');
$sheet->setCellValue('AD4', 'Inc. Tax');
$sheet->setCellValue('AE4', 'CM Relief Fund');
$sheet->setCellValue('AF4', 'CUG Deduction');

$sheet->setCellValue('AG3', 'Total Deduction');
$sheet->setCellValue('AH3', 'Net Pay');

// Fetching Data from Database
$sql = 'SELECT payslip_report.sno as payslip_id, employee.sno as emp_id, employee.employee_name, employee.employee_code, employee.company_id, employee.pay_level_id, employee.employee_designation, employee.employee_category_id, employee.esic_no, employee.joining_date, payslip_report.financial_year, payslip_report.for_month, payslip_report.main_basic, payslip_report.total_income, payslip_report.advance_amount, payslip_report.total_deduction, payslip_report.total FROM payslip_report JOIN employee ON employee.sno = payslip_report.emp_id WHERE 1=1 ';

if (!empty($_POST['company_name'])) {
    $sql .= ' AND employee.company_id="' . mysqli_real_escape_string($db_erp, $_POST['company_name']) . '" ';
}
if (!empty($_POST['emp_name'])) {
    $sql .= ' AND payslip_report.emp_id="' . mysqli_real_escape_string($db_erp, $_POST['emp_name']) . '" ';
}
if (!empty($_POST['employee_category_id'])) {
    $sql .= ' AND employee.employee_category_id="' . mysqli_real_escape_string($db_erp, $_POST['employee_category_id']) . '"';
}
if (!empty($_POST['financial_year'])) {
    $sql .= ' AND payslip_report.financial_year="' . mysqli_real_escape_string($db_erp, $_POST['financial_year']) . '" ';
}
if (!empty($_POST['for_month'])) {
    $sql .= ' AND payslip_report.for_month="' . mysqli_real_escape_string($db_erp, $_POST['for_month']) . '" ';
}
$sql .= ' ORDER BY ABS(payslip_report.financial_year) DESC, ABS(payslip_report.for_month) DESC, ABS(employee.company_id) DESC, ABS(payslip_report.total) DESC';

$result = execute_query($sql);
$i = 1;
$tot_main_basic = 0;
$tot_net_pay = 0;
$tot_net_total_deduction = 0;
$tot_total_income = 0;
$tot_heads = array();
$row_count = 0;
$tot_heads['total_deduction'] = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $sql_company = 'SELECT * FROM uprnss_division WHERE s_no="' . mysqli_real_escape_string($db_erp, $row['company_id']) . '"';
    $result_company = mysqli_query($db_erp, $sql_company);
    $row_company = mysqli_fetch_assoc($result_company);

    $sql_attendance = 'SELECT * FROM attendance_details WHERE emp_id="' . mysqli_real_escape_string($db_erp, $row['emp_id']) . '" AND salary_generation_month="' . mysqli_real_escape_string($db_erp, $row['for_month']) . '" AND financial_year="' . mysqli_real_escape_string($db_erp, $row['financial_year']) . '"';
    $result_attendance = execute_query($sql_attendance);
    $row_attendance = mysqli_fetch_assoc($result_attendance);

    $sql_designation = 'SELECT * FROM dp_designation WHERE sno="' . mysqli_real_escape_string($db_erp, $row['employee_designation']) . '"';
    $des = mysqli_query($db_erp, $sql_designation);
    if (mysqli_num_rows($des) != 0) {
        $des = mysqli_fetch_assoc($des);
    } else {
        $des = ['designation' => ''];
    }

    if ($row['total'] > 0) {
        $row_amount_income_array = array();
        $sql_head_income = "SELECT * FROM payroll_head_access LEFT JOIN head_type ON payroll_head_access.head_id=head_type.sno WHERE cat_id='" . mysqli_real_escape_string($db_erp, $_POST['employee_category_id']) . "' AND head_type.head_type='income' AND payslip_mode!='1'";
        $result_head_income = execute_query($sql_head_income);
        while ($row_head_income = mysqli_fetch_assoc($result_head_income)) {
            $sql_amount_income = 'SELECT * FROM payslip_details WHERE payslip_id="' . mysqli_real_escape_string($db_erp, $row['payslip_id']) . '" AND head_id="' . mysqli_real_escape_string($db_erp, $row_head_income['sno']) . '"';
            $result_income = execute_query($sql_amount_income);
            $row_amount_income_array[] = mysqli_fetch_assoc($result_income);
        }

        $tot_main_basic += $row['main_basic'];

        foreach ($row_amount_income_array as $v) {
            if (isset($v["amount"])) {
                if (!isset($tot_heads[$v['sno']])) {
                    $tot_heads[$v['sno']] = 0;
                }
                $tot_heads[$v['sno']] += $v["amount"];
            }
        }

        $tot_total_income += $row['total_income'];

        $sql_head_deduction = "SELECT * FROM payroll_head_access LEFT JOIN head_type ON payroll_head_access.head_id=head_type.sno WHERE cat_id='" . mysqli_real_escape_string($db_erp, $_POST['employee_category_id']) . "' AND head_type.head_type='deduction' AND payslip_mode!='1'";
        $result_head_deduction = execute_query($sql_head_deduction);

        $row_amount_deduction_array = array();
        while ($row_head_deduction = mysqli_fetch_assoc($result_head_deduction)) {
            $sql_amount_deduction = 'SELECT * FROM payslip_details WHERE payslip_id="' . mysqli_real_escape_string($db_erp, $row['payslip_id']) . '" AND head_id="' . mysqli_real_escape_string($db_erp, $row_head_deduction['sno']) . '"';
            $result_deduction = execute_query($sql_amount_deduction);
            $row_amount_deduction_array[] = mysqli_fetch_assoc($result_deduction);
        }

        foreach ($row_amount_deduction_array as $v) {
            if (isset($v["amount"])) {
                if (!isset($tot_heads[$v['sno']])) {
                    $tot_heads[$v['sno']] = 0;
                }
                $tot_heads[$v['sno']] += $v["amount"];
            }
        }

        $tot_net_total_deduction += $row['total_deduction'];
        $tot_net_pay += $row['total'];

        $sheet->setCellValue('A' . (6 + $row_count), $i);
        $sheet->setCellValue('B' . (6 + $row_count), $row['employee_name']);
        $sheet->setCellValue('C' . (6 + $row_count), $row['employee_name']);
        $sheet->getCell('D' . (6 + $row_count))->setValueExplicit($row['esic_no'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('E' . (6 + $row_count), $row['joining_date']);
        $sheet->setCellValue('F' . (6 + $row_count), $row['employee_code']);
        $sheet->setCellValue('G' . (6 + $row_count), $row_company['division_name'] ?? '');
        $sheet->setCellValue('H' . (6 + $row_count), $row['main_basic']);

        if (isset($row_amount_income_array[1]['amount'])) {
            $sheet->setCellValue('I' . (6 + $row_count), $row_amount_income_array[1]['amount']);
        }
        if (isset($row_amount_income_array[2]['amount'])) {
            $sheet->setCellValue('J' . (6 + $row_count), $row_amount_income_array[2]['amount']);
        }
        if (isset($row_amount_income_array[3]['amount'])) {
            $sheet->setCellValue('K' . (6 + $row_count), $row_amount_income_array[3]['amount']);
        }
        if (isset($row_amount_income_array[4]['amount'])) {
            $sheet->setCellValue('L' . (6 + $row_count), $row_amount_income_array[4]['amount']);
        }
        if (isset($row_amount_income_array[5]['amount'])) {
            $sheet->setCellValue('M' . (6 + $row_count), $row_amount_income_array[5]['amount']);
        }
        if (isset($row_amount_income_array[6]['amount'])) {
            $sheet->setCellValue('N' . (6 + $row_count), $row_amount_income_array[6]['amount']);
        }
        if (isset($row_amount_income_array[7]['amount'])) {
            $sheet->setCellValue('O' . (6 + $row_count), $row_amount_income_array[7]['amount']);
        }

        $sheet->setCellValue('P' . (6 + $row_count), $row_attendance['working_days'] ?? '');
        $sheet->setCellValue('Q' . (6 + $row_count), $row_attendance['presented_days'] ?? '');
        $sheet->setCellValue('R' . (6 + $row_count), $row_attendance['ncp_days'] ?? '');

        $sheet->setCellValue('S' . (6 + $row_count), $row['main_basic']);

        if (isset($row_amount_income_array[1]['amount'])) {
            $sheet->setCellValue('T' . (6 + $row_count), $row_amount_income_array[1]['amount']);
        }
        if (isset($row_amount_income_array[2]['amount'])) {
            $sheet->setCellValue('U' . (6 + $row_count), $row_amount_income_array[2]['amount']);
        }
        if (isset($row_amount_income_array[3]['amount'])) {
            $sheet->setCellValue('V' . (6 + $row_count), $row_amount_income_array[3]['amount']);
        }
        if (isset($row_amount_income_array[4]['amount'])) {
            $sheet->setCellValue('W' . (6 + $row_count), $row_amount_income_array[4]['amount']);
        }
        if (isset($row_amount_income_array[5]['amount'])) {
            $sheet->setCellValue('X' . (6 + $row_count), $row_amount_income_array[5]['amount']);
        }
        if (isset($row_amount_income_array[6]['amount'])) {
            $sheet->setCellValue('Y' . (6 + $row_count), $row_amount_income_array[6]['amount']);
        }
        if (isset($row_amount_income_array[7]['amount'])) {
            $sheet->setCellValue('Z' . (6 + $row_count), $row_amount_income_array[7]['amount']);
        }

        $sheet->setCellValue('AA' . (6 + $row_count), $row['total_income']);

        $sheet->setCellValue('AB' . (6 + $row_count), $row_amount_deduction_array[0]['amount'] ?? '');

        $sheet->setCellValue('AC' . (6 + $row_count), $row_amount_deduction_array[1]['amount'] ?? '');

        $sheet->setCellValue('AD' . (6 + $row_count), $row_amount_deduction_array[2]['amount'] ?? '');

        $sheet->setCellValue('AE' . (6 + $row_count), $row_amount_deduction_array[3]['amount'] ?? '');

        $sheet->setCellValue('AF' . (6 + $row_count), $row_amount_deduction_array[4]['amount'] ?? '');

        $sheet->setCellValue('AG' . (6 + $row_count), $row['total_deduction']);

        $sheet->setCellValue('AH' . (6 + $row_count), $row['total']);

        $i++;
        $row_count++;
    }
}
// Adding Total Deduction and Net Pay to the Spreadsheet
$sheet->setCellValue('AF' . (6 + $row_count), 'Total:');
$sheet->setCellValue('AG' . (6 + $row_count), $tot_net_total_deduction);
$sheet->setCellValue('AH' . (6 + $row_count), $tot_net_pay);

// Formatting the total row
$sheet->getStyle('AF' . (6 + $row_count) . ':AH' . (6 + $row_count))->getFont()->setBold(true);

$filename = "Payroll_Cpf_" . date("y_m_d") . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
?>