<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");
$moduleName="project_bill_adviser";
$module_name_bill="project_bill_adviser";
$msg='';
$tab=1;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_action'])) {
    $req_id = intval($_POST['request_id']);
    $action = $_POST['approve_action'];
    $remarks = $_POST['remarks'] ?? '';
    processApprovalAction($req_id, $employee_id, $action,$moduleName, $remarks);
    echo "<p style='color:green;'>✔️ Action '$action'</p>";
    // echo "<p style='color:green;'>✔️ Action '$action' performed on request ID $req_id</p>";
}


$note_sheet_id = $_GET['request_id'] ?? 0;

// Step 1: project_note_sheet से bill sno निकालना
$sql1 = "SELECT * FROM `project_bill_adviser` WHERE id = $note_sheet_id";
$note_result = execute_query($sql1);
$note_data = mysqli_fetch_assoc($note_result);
$bill_snos = $note_data['related_bill_snos'];

// Step 2: invoice_account_fund_transafer से data लाना with vendor information
$sql2 = "SELECT ft.to_account_no, ft.vendor_id, SUM(ft.praposemoney) AS total_amount, 
                 v.firm_name, v.contractor_name
          FROM `invoice_account_fund_transafer` ft 
          LEFT JOIN vendor v ON ft.vendor_id = v.sno 
          WHERE ft.sno IN ($bill_snos) 
          GROUP BY ft.to_account_no, ft.vendor_id, v.firm_name, v.contractor_name";
$fund_result = execute_query($sql2);

// Step 3: Data extract करके bank_data बनाना
$bank_data = [];
$total_amount = 0;
$i = 1;

while ($row = mysqli_fetch_assoc($fund_result)) {
    // अब हम fund_transafer_to_account से customer_id (billit_customer foreign key) निकालेंगे
   
        $cust_q = "SELECT * FROM billit_customer WHERE sno = {$row['to_account_no']}";
        $cust = mysqli_fetch_assoc(execute_query($cust_q));

        // Fallback values if data missing
        $department = 'Uttar Pradesh Rajya Nirman Sahkari Sangh Limited (UPRNSS).';
        $bank_name = 'HDFC';
        $account_no = $cust['account_no'] ?? '0000000000';
        $ifsc = $cust['ifsc'] ?? 'UNKNOWN';
        $amount = floatval($row['total_amount']);
        
        // Vendor information
        $vendor_name = '';
        if($row['firm_name']) {
            $vendor_name = $row['firm_name'];
            if($row['contractor_name']) {
                $vendor_name .= ' (' . $row['contractor_name'] . ')';
            }
        }

        $bank_data[] = [
            'department' => $department,
            'bank' => $bank_name,
            'account' => $account_no,
            'ifsc' => $ifsc,
            'amount' => $amount,
            'vendor' => $vendor_name
        ];

        $total_amount += $amount;
    
}

// HO बैंक डिटेल्स
$ho_bank_row = mysqli_fetch_assoc(execute_query('SELECT * FROM billit_customer WHERE sno="1"'));
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Bank Advice Letter</title>
    <style>
        body {
            font-family: "Kruti Dev", Arial, sans-serif;
            font-size: 14px;
            margin-top: 38px;
            padding: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
		.table1 {
            width: 80%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 0.4rem;
            text-align: center;
        }
		#overlays {
            z-index: -1;
            opacity: 0.15;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .header {
            text-align: left;
            margin-bottom: 1.2rem;
            font-weight: bold;
            font-size: 1rem;
        }

        .body-text {
            text-align: left;
            margin: 1rem 0;
            text-indent: 2rem;
            font-size: 0.95rem;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }
		@media print {
            .printonly {
                display: block !important;
            }
            #overlays {
                opacity: 0.05;
                width: 50% !important;
            }
        }
    </style>
</head>
<body>
	<div>
                <img src="images/icon.png" id="overlays" alt="overlay image">
            </div>
    <div class="header">
        शाखा प्रबन्धक<br>
        एच.डी.एफ.सी. बैंक लि0<br>
        जनपद-लखनऊ।
    </div>

    <div class="body-text">
        आपकी शाखा में उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.), का खाता संख्या-<strong><?php echo $ho_bank_row['account_no']; ?></strong> संचालित हैं, अधोलिखित विवरण के अनुसार उनके नाम के आगे अंकित धनराशि को उनके खाते में Transfer करने का कष्ट करें।
    </div>

    <table>
        <thead>
            <tr>
                <th>क्र0सं0</th>
                <th>विभाग का नाम</th>
                <th>वेंडर का नाम</th>
                <th>बैंक का नाम</th>
                <th>खाता संख्या</th>
                <th>आईएफएससी</th>
                <th>धनराशि</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($bank_data as $entry) {
                echo "<tr>
                    <td>{$i}</td>
                    <td>{$entry['department']}</td>
                    <td>{$entry['vendor']}</td>
                    <td>{$entry['bank']}</td>
                    <td>{$entry['account']}</td>
                    <td>{$entry['ifsc']}</td>
                    <td class='right'>".number_format($entry['amount'], 2)."</td>
                </tr>";
                $i++;
            }
            ?>
            <tr>
                <th colspan="6" class="right">Total</th>
                <th class="right"><?php echo number_format($total_amount, 2); ?></th>
            </tr>
        </tbody>
    </table>
<?php

$myDesignation = getDesignation($employee_id);

$rowsql="SELECT level FROM approvals_config WHERE module_name = '$moduleName' AND designation_id = '$myDesignation'";

$levelWise = $db->query("SELECT level FROM approvals_config WHERE module_name = '$moduleName' AND designation_id = '$myDesignation'");
$allowedLevels = [];
while ($r = $levelWise->fetch_assoc()) $allowedLevels[] = $r['level'];

$inClause = !empty($allowedLevels) ? implode(",", $allowedLevels) : 'NULL';
$request_id = intval($_GET['request_id']);
$sql = "SELECT * FROM approval_requests WHERE row_id = $request_id AND module_name = '$moduleName' AND current_level IN ($inClause) AND (status = 'pending' OR status = 'reverted')";

// echo "<pre>$sql</pre>"; // OR use error_log($sql); for logging

// Step 3: Run the actual query
$otherRequests = $db->query($sql);

?>
<?php foreach ($otherRequests as $req): ?>
<table class="table1">
    <tr><th>Action</th></tr>
        <tr>
            <td>
                <form method="post">
                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                    <textarea name="remarks" rows="4" cols="90" required placeholder="Your remarks..."></textarea><br>
                    <button name="approve_action" value="forward" style="font-size: 18px; padding: 10px 20px;">✅ Forward</button>
                    <button name="approve_action" value="revert" style="font-size: 18px; padding: 10px 20px;">↩️ Revert</button>
                </form>
            </td>
        </tr>
</table>
<?php endforeach; ?>			

</body>

</html>
