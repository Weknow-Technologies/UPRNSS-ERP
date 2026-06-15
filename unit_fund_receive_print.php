<?php
include("scripts/settings.php");
$msg = '';
include("scripts/billit_settings.php");
$msg = '';
$response = 0;
$finalmsg = '';
$tab = 1;

if (isset($_GET['id'])) {
    $sql = 'SELECT pay.* FROM billit_invoice_erp_payment pay WHERE pay.sno="' . $_GET['id'] . '"';
    $old_data = mysqli_fetch_assoc(execute_query($sql));

    if (!$old_data) {
        echo "<h2>Error: Voucher not found</h2>";
        exit;
    }
}
?>
<html>
<head>
    <title>Fund Receipt Voucher</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; width: 1024px; }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        h4 { margin: 5px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .debit, .credit { text-align: right; }
        .total-row { font-weight: bold; background-color: #ddd; }
        .header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div style="margin-left: 30px; margin-right: 15px;">
        <div class="header">उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)</div>
        <div class="header">G-4/5 SECTOR-4 GOMTINAGAR VISTAR LUCKNOW 226010</div>
        <p style="text-align:center;">State Name: Uttar Pradesh, Code:09</p>
        
        <h4>Payment At: <?php echo !empty($old_data['unit_id']) ? get_division($old_data['unit_id']) : ''; ?></h4>
        <h4 style="text-decoration: underline;">Fund Receipt Voucher</h4>

        <div class="header-container">
            <div class="left">Voucher No.: <?php echo $old_data['voucher_no']; ?></div>
            <div class="right">Date: <?php echo date("d-m-Y", strtotime($old_data['timestamp'])); ?></div>
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
                $sql = 'SELECT * FROM billit_stock_erp_payment WHERE journal_id="' . $old_data['sno'] . '"';
                $result_trans = execute_query($sql);
                $i = 1;
                $tot_debit = 0;
                $tot_credit = 0;

                while ($row = mysqli_fetch_assoc($result_trans)) {
                    $particulars = '';
                    $debit = '';
                    $credit = '';

                    if (!empty($row['by'])) {
                        $particulars = get_ledger($row['by']);
                        $credit = number_format($row['amount'], 2);
                        $tot_credit += $row['amount'];
                    } else {
                        $particulars = get_ledger($row['to']);
                        $debit = number_format($row['amount'], 2);
                        $tot_debit += $row['amount'];
                    }

                    echo '<tr>
							<td>' . $i++ . '</td>
							<td>' . $particulars . '</td>
							<td class="debit">' . $debit . '</td>
							<td class="credit">' . $credit . '</td>
						  </tr>';
                }
                echo '<tr class="total-row">
						<td colspan="2" align="right">Total</td>
						<td class="debit">' . number_format($tot_debit, 2) . '</td>
						<td class="credit">' . number_format($tot_credit, 2) . '</td>
					  </tr>';

                echo '<tr>
						<td colspan="4"><b>Narration:</b> ' . htmlspecialchars($old_data['remarks'] ?? '') . '</td>
					  </tr>';
                ?>
            </tbody>
        </table>
        <div class="header-container">
            <div class="left"></div>
            <div class="right" style="margin-top:60px; margin-right:70px;">Authorised Signatory</div>
        </div>
    </div>
</body>
</html>
