<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");
$moduleName= "invoice_deduction_release";

if (isset ($_GET['request_id'])){
	$invoice_id= $_GET['request_id'];
	
	echo '<p class="text-center"><b><u>जांच प्रति</u></b></p><br/>';
}else{
	$invoice_id = intval($_GET['id'] ?? 0);
	if (!$invoice_id) die("Missing invoice ID");
}

$heads = [
    
    'gstdeduction' => 'GST',
    'incometax' => 'Income Tax',
    'security' => 'Security',
    'royalty' => 'Royalty'
];

$heads11 = [
    'gsttds' => 'GST TDS',
    'gstdeduction' => 'GST',
    'sentage' => 'Sentage',
    'leborses' => 'Leborses',
    'contingency' => 'Contingency'
];

$invoice = mysqli_fetch_assoc($db->query("SELECT * FROM invoice_deduction_release WHERE id = $invoice_id"));
if (!$invoice) die("Invalid invoice ID");

$deduction_type = $invoice['deduction_type'];
$head_label = $heads[$deduction_type] ?? strtoupper($deduction_type);

// HO Account
$ho_bank_row = mysqli_fetch_assoc(execute_query('SELECT * FROM billit_customer WHERE sno="1"'));

// Get division-wise data
$sql = "SELECT 
            dr.released_amount,
            iat.to_account_no,
            ta.division_id,
            d.division_name
        FROM deduction_release dr
        JOIN invoice_account_fund_transafer iat ON dr.bill_id = iat.sno
        JOIN tender_allotment ta ON ta.project_id = iat.project_name
        LEFT JOIN uprnss_division d ON d.s_no = ta.division_id
        WHERE dr.invoice_id = $invoice_id AND ta.status!=5
        ORDER BY d.division_name";

$res = $db->query($sql);

$grouped = [];
$grand_total = 0;

while ($row = $res->fetch_assoc()) {
    $div_name = $row['division_name'] ?: 'अन्य';
    $acc_id = $row['to_account_no'];
    $cust = mysqli_fetch_assoc(execute_query("SELECT * FROM billit_customer WHERE sno = $acc_id"));

    if (!isset($grouped[$div_name])) {
        $grouped[$div_name] = [
            'department' => 'Uttar Pradesh Rajya Nirman Sahkari Sangh Limited (UPRNSS).',
            'bank' => 'HDFC',
            'account' => $cust['account_no'] ?? '0000000000',
            'ifsc' => $cust['ifsc'] ?? 'UNKNOWN',
            'amount' => 0
        ];
    }
    $grouped[$div_name]['amount'] += floatval($row['released_amount']);
    $grand_total += floatval($row['released_amount']);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_action'])) {
    $req_id = intval($_POST['request_id']);
    $action = $_POST['approve_action'];
    $remarks = $_POST['remarks'] ?? '';
    processApprovalAction($req_id, $employee_id, $action,$moduleName, $remarks);
    echo "<p style='color:green;'>✔️ Action '$action'</p>";
    // echo "<p style='color:green;'>✔️ Action '$action' performed on request ID $req_id</p>";
}

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Bank Advice - <?= $head_label ?></title>
    <style>
        @page {
            margin: 30mm 20mm 20mm 20mm;
        }

        body {
            font-family: "Kruti Dev", Arial, sans-serif;
            font-size: 14px;
            /* margin-top: 90px; */
            padding: 0;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        .header-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-top: -50px;
        }
        .header-text {
            text-align: center;
            flex: 1;
        }
        .header-text h1 {
            margin: 0;
            font-size: 25px;
            color: #252964;
            line-height: 1.2;
        }
        .header-text p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #252964;
        }
        .header-meta {
            text-align: right;
            font-size: 12px;
            min-width: 140px;
            margin-top: 20px;
        }

        table {
            width: 100%;
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
            font-size: 1rem;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        #overlays {
            z-index: -1;
            opacity: 0.15;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 80%;
        }

        @media print {
            #overlays { opacity: 0.05; width: 50% !important; }
            .no-print { display: none; }
			.container {
				margin-top: 210px;
			}
        }
    </style>
</head>
<body>
    <div>
	<div>
             <img src="images/icon.png" id="overlays" alt="overlay image">
            </div>
            <div class="header-meta">
            <div>दूरभाष : 0522-2390150</div>
            <div>फैक्स : 0522-2390150</div>
            <div>वेबसाइट : www.uprnss.org</div>
            <div>ईमेल  :  paccfedho@gmail.com </div>
        </div><br>
            <div class="header">
        <img src="images/icon.png" alt="UPRNSS logo" class="header-logo">
        <div class="header-text">
            <h1>उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि० <span style="color: #b90b1a;"><br>(UPRNSS)</span></h1>
            <p>मुख्यालय : जी-4/5बी, सेक्टर-4, गोमती नगर विस्तार, लखनऊ - 226010<br><span style="color: #b90b1a; font-size:15px;">(राजकीय निर्माण एजेन्सी)</span></p>
        </div>
        
    </div>
    <!-- <hr class="sep"> -->
    <div class="header">
        <div style="font-weight: lighter;">पत्रांक : ______________</div>
        <div style="font-weight: lighter;">दिनांक  : ______________</div>
    </div>
    </div>
    <div style="margin-top: 30px; padding:40px;">
    <!-- <div class="container"> -->
        <div class="header">
            शाखा प्रबंधक<br>
            एचडीएफसी बैंक लिमिटेड<br>
            जनपद - लखनऊ।
        </div>

        <div class="body-text">
            आपकी शाखा में उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि० का खाता संख्या 
            <strong><?= $ho_bank_row['account_no']; ?></strong> संचालित है। कृपया नीचे दिए गए विवरण के अनुसार खातों में धनराशि ट्रांसफर करें:
        </div>

        <table>
            <thead>
                <tr>
                    <th>क्रम संख्या</th>
                    <th>विभाग का नाम</th>
                    <th>बैंक का नाम</th>
                    <th>खाता संख्या</th>
                    <th>आईएफएससी</th>
                    <th>धनराशि (₹)</th>
                    <th class="no-print">डिवीजन</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach ($grouped as $division => $entry) {
                    echo "<tr>
                        <td>{$i}</td>
                        <td>{$entry['department']}</td>
                        <td>{$entry['bank']}</td>
                        <td>{$entry['account']}</td>
                        <td>{$entry['ifsc']}</td>
                        <td class='right'> " . number_format_indian($entry['amount'], 2) . "</td>
                        <td class='no-print'>{$division}</td>
                    </tr>";
                    $i++;
                }
                ?>
                <tr>
                    <th colspan="5" class="right">कुल राशि</th>
                    <th class="right"> <?= number_format_indian($grand_total, 2) ?></th>
                    <th class='no-print'></th>
                </tr>
            </tbody>
        </table>
    <!-- </div> -->
    </div>
</body>
<?php
if (!isset ($_GET['request_id'])){
?>
<script> window.onload = function() { window.print(); } </script>
<?php	
}
?>
<?php

$myDesignation = getDesignation($employee_id);
$levelWise = $db->query("SELECT level FROM approvals_config WHERE module_name = '$moduleName' AND designation_id = $myDesignation");
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
<br>
<br>
<br>
</html>
