<?php
// Completely isolated AJAX file - no interference
include("settings.php");


// Buffer all output to prevent any warnings/errors from mixing
ob_start();

// Turn off error display for clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Database connection
//$db = mysqli_connect("p:localhost", "root", "mysql", "uprnss_erp");
if(!$db){
    echo json_encode(array("error" => "Database connection failed"));
    exit;
}

mysqli_query($db, 'SET character_set_results=utf8'); 
mysqli_query($db, 'SET names utf8'); 
mysqli_query($db, 'SET character_set_client=utf8'); 
mysqli_query($db, 'SET character_set_connection=utf8'); 
mysqli_query($db, 'SET character_set_results=utf8'); 
mysqli_query($db, 'SET collation_connection=utf8_general_ci');

if(!function_exists('execute_query')){
function execute_query($query){
    global $db;
    $result = mysqli_query($db, $query);
    if(!$result) {
        return false;
    }
    return $result;
}
}

// Get parameters
$id = isset($_POST['id']) ? $_POST['id'] : (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
$data = array();

if ($id == 'sub_dep') {
    $dept_id = isset($_POST['val']) ? $_POST['val'] : '';
    if (empty($dept_id)) {
        $data = array("error" => "Department ID required");
    } else {
        $sql = 'SELECT sno, sub_department_hindi 
                FROM uprnss_sub_department 
                WHERE department_id = "' . mysqli_real_escape_string($db, $dept_id) . '" 
                ORDER BY sub_department_hindi';
        
        $result = execute_query($sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = array("id" => $row['sno'], "sub_department_hindi" => $row['sub_department_hindi']);
            }
        } else {
            $data = array("error" => "Database query failed");
        }
    }
} elseif ($id == 'dist') {
    $dept_id = isset($_POST['val']) ? $_POST['val'] : '';
    if (empty($dept_id)) {
        $data = array("error" => "Department ID required");
    } else {
        $sql = 'SELECT DISTINCT d.sno, d.district_name_english as district_name 
                FROM uprnss_district d 
                INNER JOIN uprnss_project_temp p ON d.sno = p.district_id 
                WHERE p.department_id = "' . mysqli_real_escape_string($db, $dept_id) . '" 
                AND (p.status = "0" OR p.status IS NULL)';
        
        $result = execute_query($sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = array("id" => $row['sno'], "district_name" => $row['district_name']);
            }
        } else {
            $data = array("error" => "Database query failed");
        }
    }
} elseif ($id == 'proj') {
    $dept_id = isset($_POST['dept']) ? $_POST['dept'] : '';
    $dist_id = isset($_POST['val']) ? $_POST['val'] : '';
    
    if (empty($dept_id) || empty($dist_id)) {
        $data = array("error" => "Department ID and District ID required");
    } else {
        $sql = 'SELECT sno, project_name_hindi 
                FROM uprnss_project_temp 
                WHERE department_id = "' . mysqli_real_escape_string($db, $dept_id) . '" 
                AND district_id = "' . mysqli_real_escape_string($db, $dist_id) . '" 
                AND (status = "0" OR status IS NULL)
                ORDER BY project_name_hindi';
        
        $result = execute_query($sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = array("id" => $row['sno'], "project_name_hindi" => $row['project_name_hindi']);
            }
        } else {
            $data = array("error" => "Database query failed");
        }
    }
} elseif ($id == 'proj_info') {
    $prj = intval($_POST['val'] ?? 0);
    $exclude = intval($_POST['exclude_invoice'] ?? 0);

    $out = [
        'sanction_cost_lakh' => 0,
        'advance_amount' => 0,
        'received_to_date' => 0,
        'installments_count' => 0
    ];

    if ($prj) {
        $q1 = execute_query('SELECT sanction_cost, financial_go_amount FROM uprnss_project_temp WHERE sno=' . $prj . ' LIMIT 1');
        if ($q1 && mysqli_num_rows($q1)) {
            $r1 = mysqli_fetch_assoc($q1);
            $out['sanction_cost_lakh'] = customRound($r1['sanction_cost']);
            $out['advance_amount'] = customRound($r1['financial_go_amount']);
        }

        $sql2 = 'SELECT COALESCE(SUM(tfr.p_receive_amount),0) AS rcvd,
                            COUNT(DISTINCT ifr.installment) AS inst_cnt
                     FROM transaction_fund_receive tfr
                     JOIN invoice_fund_receive ifr ON ifr.sno = tfr.invoice_id
                     WHERE tfr.project_id=' . $prj;
        if ($exclude > 0) {
            $sql2 .= ' AND ifr.sno<>' . $exclude;
        }
        $q2 = execute_query($sql2);
        if ($q2 && mysqli_num_rows($q2)) {
            $r2 = mysqli_fetch_assoc($q2);
            $out['received_to_date'] = customRound($r2['rcvd']);
            $out['installments_count'] = customRound($r2['inst_cnt']);
        }
    }
    $data = $out;
} elseif ($id == 'order_no') {
    $orderNo = mysqli_real_escape_string($db, $_POST['val'] ?? '');
    
    if ($orderNo != '') {
        // Get order data from invoice_fund_receive table
        $sql = "SELECT * FROM invoice_fund_receive WHERE order_no='$orderNo' AND status!='5' LIMIT 1";
        $result = execute_query($sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // Get sanction date from GO number
            $go_date = '';
            if (!empty($orderNo)) {
                $go_sql = "SELECT go_date FROM invoice_new_project WHERE go_number LIKE '%$orderNo%' LIMIT 1";
                $go_result = execute_query($go_sql);
                if ($go_result && mysqli_num_rows($go_result) > 0) {
                    $go_row = mysqli_fetch_assoc($go_result);
                    $go_date = $go_row['go_date'];
                }
            }
            
            // Generate voucher number in same format as centage
            $current_month = date('m');
            $current_year = date('Y');
            
            if ($current_month >= 4) {
                $fy_start = $current_year;
                $fy_end = substr($current_year + 1, -2);
            } else {
                $fy_start = $current_year - 1;
                $fy_end = substr($current_year, -2);
            }
            
            $financial_year = $fy_start . '-' . $fy_end;
            $prefix = 'UPRNSS/' . $financial_year . '/FUND/';
            
            // Get next voucher number for FUND type
            $voucher_sql = "SELECT MAX(CAST(SUBSTRING(voucher_no, LENGTH('$prefix') + 1) AS UNSIGNED)) as max_num FROM invoice_fund_receive WHERE voucher_no LIKE '$prefix%'";
            $voucher_result = mysqli_query($db, $voucher_sql);
            $voucher_row = mysqli_fetch_assoc($voucher_result);
            $next_num = ($voucher_row['max_num'] ?? 0) + 1;
            
            $generated_voucher_no = $prefix . str_pad($next_num, 4, '0', STR_PAD_LEFT);
            
            $data = array(
                'status' => 'success',
                'voucher_no' => $generated_voucher_no,
                'order_date' => $go_date ?: $row['order_date'], // Use GO date as order date (sanction date)
                'receive_date' => date('Y-m-d'), // Always current date
                'tot_receive_amount' => $row['tot_receive_amount'],
                'tds_per' => $row['tds_per'],
                'tds_deducted' => $row['tds_deducted'],
                'gst_tds_per' => $row['gst_tds_per'],
                'gsttds_deducted' => $row['gsttds_deducted'],
                'labour_sess' => $row['labour_sess']
            );
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Order not found',
                'order_date' => date('Y-m-d'), // Still return current date
                'receive_date' => date('Y-m-d')  // Still return current date
            );
        }
    } else {
        $data = array(
            'status' => 'error',
            'message' => 'Order number is required'
        );
    }
} elseif ($id == 'fetch_transfer_row') {
    $orderNo = mysqli_real_escape_string($db, $_POST['order_no'] ?? '');
    $projectId = intval($_POST['project_id'] ?? 0);
    
    if ($orderNo != '' && $projectId > 0) {
        $sql = "SELECT t.* FROM transaction_fund_transfer t 
                JOIN invoice_fund_transfer i ON t.invoice_header_id = i.sno 
                WHERE i.order_no = '$orderNo' AND t.project_name = '$projectId' AND i.status != '5' LIMIT 1";
        $result = execute_query($sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $data = array(
                'status' => 'success',
                'transafer_amount' => $row['transafer_amount'],
                'sentage' => $row['sentage'],
                'gsttds' => $row['gsttds'],
                'leborses' => $row['leborses'],
                'incometax' => $row['incometax']
            );
        } else {
            $data = array('status' => 'not_found');
        }
    } else {
        $data = array('status' => 'error');
    }
}

// Clean any buffered output
ob_end_clean();

// Output clean JSON
echo json_encode($data);
?>
