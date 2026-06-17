<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
date_default_timezone_set('Asia/Calcutta');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<h3>Invalid request.</h3>");
}

$header_id = intval($_GET['id']);

$sql = "SELECT h.*, bc.cus_name AS from_acc, bc2.cus_name AS to_acc, bc2.unit_id AS target_unit
        FROM invoice_fund_transfer h
        LEFT JOIN billit_customer bc ON bc.sno = h.from_account_no
        LEFT JOIN billit_customer bc2 ON bc2.sno = h.fund_transfer_to
        WHERE h.sno = '$header_id'";
$result = execute_query($sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("<h3>Transfer record not found.</h3>");
}

$target_unit = $row['target_unit'];
$isAdmin = (isset($_SESSION['username']) && in_array(strtolower((string)$_SESSION['username']), ['sadmin', 'headacc']));
if (!$isAdmin && !(isset($_SESSION['divisions']) && in_array($target_unit, $_SESSION['divisions']))) {
    die("<h3>Unauthorized access.</h3>");
}

$ho_jid = $row['journal_id'];
$lines_debit = [];
$lines_credit = [];
$preview_voucher_no = generateVoucherNumber('invoice_fund_transfer', 'order_no', 'FR');
if ($ho_jid > 0) {
    $sql_lines = "SELECT * FROM billit_stock_erp_payment WHERE journal_id = '$ho_jid'";
    $res_lines = execute_query($sql_lines);
    while ($l = mysqli_fetch_assoc($res_lines)) {
        $flipped = [
            'by'     => $l['to'],
            'to'     => $l['by'],
            'amount' => $l['amount'],
        ];
        if (!empty($flipped['by'])) {
            $lines_debit[] = $flipped;
        } else {
            $lines_credit[] = $flipped;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fund Transfer Preview - <?php echo htmlspecialchars($row['order_no']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        h4 { margin: 4px 0; text-align: center; }
        p { margin: 2px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .debit, .credit { text-align: right; }
        .total-row { font-weight: bold; background-color: #ddd; }
        .header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .action-bar { margin: 20px auto; padding: 10px 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; display: inline-block; text-align: center; width: 500px; }
        .action-bar-wrapper { text-align: center; }
        .action-bar .btn { padding: 8px 20px; font-size: 14px; border: none; cursor: pointer; border-radius: 4px; margin-right: 10px; }
        .btn-accept { background-color: #28a745; color: white; }
        .btn-reject { background-color: #dc3545; color: white; }
        .btn-print  { background-color: #17a2b8; color: white; }
        #reject-section { display: none; margin-top: 12px; }
        #reject-section textarea { width: 100%; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        #reject-section .btn-submit-reject { background-color: #dc3545; color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 8px; font-size: 14px; }
        .badge-warning { background: #ffc107; color: #333; padding: 3px 8px; border-radius: 4px; font-size: 13px; }
        .no-print { }
        @media print {
            .no-print { display: none !important; }
            body::after {
                content: "Powered By AIPPCA";
                position: fixed;
                font-size: 50px;
                color: rgba(0,0,0,0.1);
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                z-index: -2;
            }
        }
    </style>
</head>
<body>

<div style="margin-left: 30px; margin-right: 15px;">
    <div class="header">उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)</div>
    <div class="header">G-4/5 SECTOR-4 GOMTINAGAR VISTAR LUCKNOW 226010</div>
    <p>State Name: Uttar Pradesh, Code: 09</p>
    <p>E-Mail: paccfedho@gmail.com</p>
    <h4><?php echo htmlspecialchars($row['to_acc']); ?></h4>
    <h4>Journal Voucher <span class="badge-warning no-print">Preview (Not Yet Saved)</span></h4>

    <div class="header-container">
        <div>Voucher No.: <?php echo htmlspecialchars($row['order_no']); ?></div>
        <div>Date: <?php echo date('d-m-Y', strtotime($row['transfer_date'])); ?></div>
    </div>

    <table>
        <thead>
        <tr>
            <th>S.No.</th>
            <th>Particulars</th>
            <th class="debit">Debit (₹)</th>
            <th class="credit">Credit (₹)</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        $tot_debit = 0;
        $tot_credit = 0;
        foreach ($lines_debit as $l):
            $tot_debit += $l['amount'];
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars(get_ledger($l['by'])); ?></td>
                <td class="debit"><?php echo number_format($l['amount'], 2); ?></td>
                <td class="credit"></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($lines_credit as $l):
            $tot_credit += $l['amount'];
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars(get_ledger($l['to'])); ?></td>
                <td class="debit"></td>
                <td class="credit"><?php echo number_format($l['amount'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="2" align="right">Total</td>
            <td class="debit"><?php echo number_format($tot_debit, 2); ?></td>
            <td class="credit"><?php echo number_format($tot_credit, 2); ?></td>
        </tr>
        <tr>
            <td colspan="4"><b>Narration:</b> <?php echo htmlspecialchars($row['narration'] ?? $row['remarks'] ?? ''); ?></td>
        </tr>
        </tbody>
    </table>

    <div class="header-container">
        <div></div>
        <div style="margin-top:60px; margin-right:70px;">Authorised Signatory</div>
    </div>
</div>

<?php if ($row['unit_confirmation_status'] == 0): ?>

    <div class="action-bar-wrapper no-print">
        <div class="action-bar no-print">
            <strong>Action:</strong>
            <button class="btn btn-accept" onclick="doAccept()">✔ Accept</button>
            <button class="btn btn-reject" onclick="showReject()">✖ Reject</button>
            <div id="reject-section">
                <textarea id="reject-remark" rows="3" placeholder="Enter reason for rejection (mandatory)..."></textarea><br>
                <button class="btn-submit-reject" onclick="doReject()">Submit Rejection</button>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="action-bar no-print" style="background:#fff3cd;">
        <strong>This transfer has already been
            <?php echo $row['unit_confirmation_status'] == 1 ? '<span style="color:green">Accepted</span>' : '<span style="color:red">Rejected</span>'; ?>.
        </strong>
        <?php if (!empty($row['unit_confirmation_remark'])): ?>
            &nbsp; Remark: <?php echo htmlspecialchars($row['unit_confirmation_remark']); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
    var headerId = <?php echo $header_id; ?>;

    function doAccept() {
        if (!confirm("Are you sure you want to Accept this fund?")) return;
        callAjax(1, '');
    }

    function showReject() {
        document.getElementById('reject-section').style.display = 'block';
    }

    function doReject() {
        var remark = document.getElementById('reject-remark').value.trim();
        if (remark === "") { alert("Remark is mandatory for Reject."); return; }
        callAjax(2, remark);
    }

    function callAjax(status, remark) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'scripts/ajax.php?id=confirm_fund', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    alert(status == 1 ? "Accepted successfully! Voucher created." : "Rejected successfully.");
                    window.location.reload();
                } else {
                    alert("Error: " + res.message);
                }
            } catch(e) {
                alert("Unexpected response from server.");
            }
        };
        xhr.send("term=b&header_id=" + headerId + "&status=" + status + "&remark=" + encodeURIComponent(remark));
    }
</script>
</body>
</html>