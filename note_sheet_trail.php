<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");
$moduleName = "project_note_sheet";
$module_name_bill = "project_bill_adviser";
error_reporting(E_ALL);
ini_set('display_errors', 1);
$msg = '';
$tab = 1;

function renderImageTd($path, $label = 'Photo')
{
    $p = trim((string) $path);
    if ($p === '') {
        return '<td class="no-print"><span class="text-muted">—</span></td>';
    }
    $safe = htmlspecialchars($p, ENT_QUOTES, 'UTF-8');
    $alt = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    return '

  <a href="' . $safe . '" class="img-link" onclick="return openLightbox(this);" data-full="' . $safe . '">
    <img src="' . $safe . '" alt="' . $alt . '" class="thumb-img" onerror="this.style.opacity=0.35;this.title=\'Image not found\'">
  </a>
';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_action'])) {
    $req_id = intval($_POST['request_id']);
    $action = $_POST['approve_action'];
    $remarks = $_POST['remarks'] ?? '';
    processApprovalAction($req_id, $employee_id, $action, $moduleName, $remarks);

    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF'] . "?request_id=" . $_GET['request_id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="hi">

<head>
    <meta charset="UTF-8">
    <title>Note Sheet</title>
    <style>
        body {
            font-family: "Kruti Dev", Arial, sans-serif;
            font-size: 14px;
            margin-top: 0px;
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

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
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

        .body-text {
            display: flex;
            justify-content: space-between;
            text-align: right;
            margin: 1rem 0;
            font-size: 1.54rem;
        }

        .left-div {
            width: 15%;
        }

        .right-div {
            width: 85%;
            text-indent: 1rem;
            text-align: left;
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
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
    <style>
        .thumb-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
            cursor: zoom-in;
        }

        #imgLightbox {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .8);
            z-index: 9999;
            padding: 2rem;
        }

        #imgLightbox.show {
            display: flex;
        }

        #imgLightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .6);
        }

        #imgLightbox .close {
            position: absolute;
            top: 12px;
            right: 16px;
            color: #fff;
            font-size: 32px;
            cursor: pointer;
            line-height: 1;
        }

        @media print {
            #imgLightbox {
                display: none !important;
            }
        }
    </style>

    <div id="imgLightbox" class="no-print" onclick="closeLightbox()">
        <span class="close" aria-label="Close">&times;</span>
        <img src="" alt="Preview">
    </div>

    <script>
        function openLightbox(a) {
            var full = a.getAttribute('data-full') || a.href;
            var lb = document.getElementById('imgLightbox');
            lb.querySelector('img').src = full;
            lb.classList.add('show');
            return false; // anchor open रोकना
        }
        function closeLightbox() {
            var lb = document.getElementById('imgLightbox');
            lb.classList.remove('show');
            lb.querySelector('img').src = '';
        }
    </script>


</head>

<body>
    <div>
        <img src="images/icon.png" id="overlays" alt="overlay image">

    </div>
    <p class="text-center"><b><u>जांच प्रति</u></b></p><br />
    <div class="body-text">
        <div class="left-div">
            <p></p>
        </div>
        <div class="right-div">
            <p><b><u>लेखाकार/वित्तीय सलाहकार/अधिशासी अभियन्ता</u></b></p><br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; कृपया पत्रावली के दाहिनी तरफ संलग्न बिलों का अवलोकन
            करना चाहें जो कि अवर अभियन्ता/सहायक अभियन्ता द्वारा मापें अंकित कर बिल का भुगतान हेतु प्रस्तुत किया गया है।
            बिल का विवरण निम्नवत् है-
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SL NO.</th>
                <th>Firm Name</th>
                <th>Project Name</th>
                <th>Bill No.</th>
                <th>Bill Date</th>
                <th>Bill Amt.After T. Less</th>
                <th>I.Tax</th>
                <th>Security</th>
                <th>Royalty</th>
                <th>GST TDS</th>
                <th>GST</th>
                <th>Other</th>
                <th>Net Payment</th>
                <th class="no-print">Photo 1</th>
                <th class="no-print">Photo 2</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql1 = "SELECT * FROM `project_note_sheet` where id= {$_GET['request_id']}";
            $result_sheet = execute_query($sql1);
            if($result_sheet) {
                $row_fund_id = mysqli_fetch_assoc($result_sheet);
                $bill_snos = $row_fund_id['related_bill_snos'];
            } else {
                die("Error: Unable to fetch note sheet data.");
            }

            $sql = "SELECT * FROM `invoice_account_fund_transafer` WHERE sno IN ($bill_snos) ORDER BY sno DESC";
            $result = execute_query($sql);
            $i = 1;
            $net_payment = 0;
            $total_transafer_amount = 0;
            $total_incometax = 0;
            $total_security = 0;
            $total_royalty = 0;
            $total_gsttds = 0;
            $total_other_amount = 0;
            $total_sentage = 0;
            $total_gstdeduction = 0;
            $total_total_expen = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $divisions = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_division WHERE s_no="' . intval($row['unit_id']) . '"'));
                $district = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_district WHERE sno="' . intval($row['district']) . '"'));
                $row_project = mysqli_fetch_assoc(execute_query('SELECT * FROM uprnss_project_temp WHERE sno="' . intval($row['project_name']) . '"'));
                $vendor = mysqli_fetch_assoc(execute_query('SELECT tender_allotment.sno, firm_name, contractor_name FROM tender_allotment LEFT JOIN vendor ON tender_allotment.project_awarded_to = vendor.sno WHERE project_id = "' . intval($row['project_name']) . '" AND tender_allotment.status != 5'));

                // NaN fix: Check if other_title is numeric
                $other_title_id = is_numeric($row['other_title']) ? intval($row['other_title']) : 0;
                $row_oth = mysqli_fetch_assoc(execute_query('SELECT * FROM fund_transfar_other_title WHERE sno="' . $other_title_id . '"'));

                $net_payment += floatval($row['praposemoney']);
                $total_transafer_amount += floatval($row['transafer_amount']);
                $total_incometax += floatval($row['incometax']);
                $total_security += floatval($row['security']);
                $total_royalty += floatval($row['royalty']);
                $total_gsttds += floatval($row['gsttds']);
                $total_other_amount += floatval(preg_replace('/[^\d.]/', '', $row['other_amount'] ?? "0"));
                $total_sentage += floatval($row['sentage']);
                $total_gstdeduction += floatval($row['gstdeduction']);
                $total_total_expen += floatval($row['total_expen']);

                $firmName = (is_array($vendor) && isset($vendor['firm_name'])) ? $vendor['firm_name'] . ' (' . $vendor['contractor_name'] . ')' : '-';
                $projectNameHindi = (is_array($row_project) && isset($row_project['project_name_hindi'])) ? $row_project['project_name_hindi'] : '-';
                echo '<tr>
                                <td>' . $i++ . '</td>
                                <td>' . $firmName . '</td>
                                <td>' . $projectNameHindi . '</td>
                                <td>' . $row['bill_no'] . '</td>
                                <td>' . ($row['bill_date'] ? date("d-m-Y", strtotime($row['bill_date'])) : '-') . '</td>
                                <td>' . $row['transafer_amount'] . '</td>
                                <td>' . $row['incometax'] . '(' . $row['it_per'] . '%)</td>
                                <td>' . $row['security'] . '(' . $row['security_per'] . '%)</td>
                                <td>' . ($row['royalty'] ?? "0") . '</td>
                                <td>' . $row['gsttds'] . '(' . $row['gsttdspercentage'] . '%)</td>
                                <td>' . $row['gstdeduction'] . '(' . $row['gst_per'] . '%)</td>
                                <td>' . (isset($row_oth['title_name']) ? $row_oth['title_name'] : '') . '<br>' . floatval(preg_replace('/[^\d.]/', '', $row['other_amount'] ?? "0")) . '</td>
                                <td>' . $row['praposemoney'] . '</td>';

                ?>
                <td class="no-print"><?= renderImageTd($row['photo1'], 'Photo 1') ?></td>
                <td class="no-print"><?= renderImageTd($row['photo2'], 'Photo 1') ?></td>
                <?php echo ' </tr>';

            }

            echo '<tr>
									<th colspan="5">Total</th>
									<th>' . $total_transafer_amount . '</th>
									<th>' . $total_incometax . '</th>
									<th>' . $total_security . '</th>
									<th>' . $total_royalty . '</th>
									<th>' . $total_gsttds . '</th>
									<th>' . $total_gstdeduction . '</th>
									<th>' . $total_other_amount . '</th>
									<th>' . $net_payment . '</th>
							</tr>';
            ?>
        </tbody>
    </table>
    <div class="body-text">
        <div class="left-div">
            <p></p>
        </div>
        <div class="right-div">
            <p>

                <?php $sanction_cost_safe = (isset($row_project) && isset($row_project['sanction_cost'])) ? $row_project['sanction_cost'] : '0'; ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;उक्त निर्माण की स्वीकृत लागत रू0
                <b><?php echo $sanction_cost_safe; ?></b> लाख है।

                जिसके सापेक्ष रू0 <b><?php echo number_format($row_fund_id['total_rcv_amt'] / 100000, 2); ?></b> लाख की
                धनराशि प्राप्त हुई है।

                उक्त प्रस्तावित बिल को जोड़कर रू0
                <b><?php echo number_format(intval($row_fund_id['grand_total_expense']) / 100000, 2); ?></b> लाख का व्यय
                हो जायेगा।

                विभाग के पास रू0
                <b><?php echo number_format(($row_fund_id['total_rcv_amt'] - $row_fund_id['grand_total_expense']) / 100000, 2); ?></b>
                लाख का अवशेष रह जायेगा।

                </br>
                </br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;महोदय यदि उक्त प्रस्ताव से सहमत हो तो कार्यहित में उपरोक्त
                फर्म के पक्ष में रू0 <b><?php echo $net_payment; ?></b>
                <b>(<?php echo int_to_words($net_payment) ?>)</b> का एंव Centege के मद मे रू0
                <b><?php echo $total_sentage; ?></b> एवं I.Tax मद मे रू0 <b><?php echo $total_incometax; ?></b> व GST
                TDS
                मद मे रू0 <b><?php echo $total_gsttds; ?></b> का UPRNSS के पक्ष में भुगतान करने की स्वीकृति प्रदान करना
                चाहें।
            </p>
        </div>
    </div>



    <?php

    $myDesignation = getDesignation($employee_id);
    $levelWise = $db->query("SELECT level FROM approvals_config WHERE module_name = '$moduleName' AND designation_id = '$myDesignation'");
    $allowedLevels = [];
    while ($r = $levelWise->fetch_assoc())
        $allowedLevels[] = $r['level'];

    $inClause = !empty($allowedLevels) ? implode(",", $allowedLevels) : 'NULL';
    $request_id = intval($_GET['request_id']);
    $sql = "SELECT * FROM approval_requests WHERE row_id = $request_id AND module_name = '$moduleName' AND current_level IN ($inClause) AND (status = 'pending' OR status = 'reverted')";

    // echo "<pre>$sql</pre>"; // OR use error_log($sql); for logging
    
    // Step 3: Run the actual query
    $otherRequests = $db->query($sql);

    ?>
    <?php
    // Fetch all forward actions for this request to show the trail
    $forwardChain = [];
    $hasForwarded = false;

    $logSql = "SELECT u.user_name, ut.user_type as designation
               FROM approval_logs al
               JOIN approval_requests ar ON al.request_id = ar.id
               LEFT JOIN users u ON al.approved_by = u.sno
               LEFT JOIN user_type ut ON u.type = ut.sno
               WHERE ar.row_id = $request_id 
                 AND ar.module_name = '$moduleName'
                 AND al.action = 'forward'
               ORDER BY al.id ASC";

    $logRes = $db->query($logSql);
    if ($logRes && $logRes->num_rows > 0) {
        $hasForwarded = true;
        while ($row = $logRes->fetch_assoc()) {
            $name = htmlspecialchars($row['user_name'] ?? '');
            $desig = htmlspecialchars($row['designation'] ?? '');
            $forwardChain[] = "$name ($desig)";
        }
    }
    ?>
    <?php if ($hasForwarded): ?>
        <table class="table1">
            <tr>
                <th>Approved By</th>
            </tr>
            <tr>
                <td style="text-align:left; padding:10px;">
                    <span style="font-weight: bold; color: green; font-size: 16px;">
                        <?php echo implode(" <span style='color:black;'>></span> ", $forwardChain); ?>
                    </span>
                </td>
            </tr>
        </table>
        <br>
    <?php endif; ?>

    <?php if ($otherRequests && $otherRequests->num_rows > 0): ?>
        <?php foreach ($otherRequests as $req): ?>
            <table class="table1">
                <tr>
                    <th>Action</th>
                </tr>
                <tr>
                    <td>
                        <form method="post" data-confirm="true" data-message="Are you sure you want to approve this request?">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <textarea name="remarks" rows="4" cols="90" required placeholder="Your remarks..."></textarea><br>
                            <button name="approve_action" value="forward" style="font-size: 18px; padding: 10px 20px;">✅
                                Approve</button>
                            <!-- <button name="approve_action" value="revert" style="font-size: 18px; padding: 10px 20px;">↩️
                                Revert</button> -->
                        </form>
                    </td>
                </tr>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <table class="table1">
            <tr>
                <th>Action</th>
            </tr>
            <tr>
                <td style="text-align:left; padding:10px;">
                    वर्तमान उपयोगकर्ता/पद के लिए कोई लंबित या रिवर्टेड एक्शन उपलब्ध नहीं है।
                </td>
            </tr>
        </table>
    <?php endif; ?>


    <br>
    <br>
    <br>
</body>
<script>
    // window.onload = function() {
    // window.print();
    // }
</script>

</html>