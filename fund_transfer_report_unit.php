<?php
include("scripts/settings.php");

$msg = '';
page_header_start();
page_header_end();
page_sidebar();

if (!isset($_POST['search'])) {
    $_POST['date_from'] = date('Y-m-01');
    $_POST['date_to'] = date('Y-m-d');
    $_POST['status'] = '';
}

?>

<div id="container" class="no-print">
    <form id="report_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title text-center text-white">Incoming Fund Transfer Report</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="date_from" value="<?php echo $_POST['date_from']; ?>"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="date_to" value="<?php echo $_POST['date_to']; ?>"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="0" <?php echo ($_POST['status'] === '0' ? 'selected' : ''); ?>>Pending
                                </option>
                                <option value="1" <?php echo ($_POST['status'] === '1' ? 'selected' : ''); ?>>Accepted
                                </option>
                                <option value="2" <?php echo ($_POST['status'] === '2' ? 'selected' : ''); ?>>Rejected
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" name="search" class="btn btn-primary btn-block mb-3">Search
                            Report</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered" id="fundReportTable">
                        <thead>
                            <tr>
                                <th class="text-center">S.No.</th>
                                <th>FT Code</th>
                                <th>Date</th>
                                <th>From Account</th>
                                <th>Received At Account</th>
                                <th class="text-right">Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Voucher</th>
                                <th>Unit Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $divIds = implode(',', array_map('intval', $_SESSION['divisions']));
                            $condition = " WHERE h.status != '5' AND bc2.unit_id IN ($divIds)";

                            if ($_POST['date_from'] != '')
                                $condition .= " AND h.transfer_date >= '" . mysqli_real_escape_string($db, $_POST['date_from']) . "'";
                            if ($_POST['date_to'] != '')
                                $condition .= " AND h.transfer_date <= '" . mysqli_real_escape_string($db, $_POST['date_to']) . "'";
                            if ($_POST['status'] !== '')
                                $condition .= " AND h.unit_confirmation_status = '" . mysqli_real_escape_string($db, $_POST['status']) . "'";

                            $sql = "SELECT h.*, bc.cus_name AS from_acc, bc2.cus_name AS to_acc 
                                    FROM invoice_fund_transfer h 
                                    LEFT JOIN billit_customer bc2 ON bc2.sno = h.fund_transfer_to
                                    LEFT JOIN billit_customer bc ON bc.sno = h.from_account_no
                                    $condition
                                    ORDER BY h.transfer_date DESC, h.sno DESC";

                            $result = execute_query($sql);
                            $i = 1;
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $status = $row['unit_confirmation_status'];
                                    $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                    if ($status == 1)
                                        $statusBadge = '<span class="badge badge-success">Accepted</span>';
                                    if ($status == 2)
                                        $statusBadge = '<span class="badge badge-danger">Rejected</span>';

                                    $voucherLink = '-';
                                    if (!empty($row['unit_journal_id'])) {
                                        $voucherLink = '<a href="billit_payment_print.php?id=' . $row['unit_journal_id'] . '" target="_blank" class="btn btn-xs btn-outline-info"><i class="fa fa-file-text"></i> View</a>';
                                    }

                                    echo '<tr>';
                                    echo '<td class="text-center">' . $i++ . '</td>';
                                    echo '<td class="font-weight-bold">' . htmlspecialchars($row['order_no']) . '</td>';
                                    echo '<td>' . date('d-M-Y', strtotime($row['transfer_date'])) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['to_acc']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['from_acc']) . '</td>';
                                    echo '<td class="text-right font-weight-bold">₹' . number_format($row['total_transfer_amount'], 2) . '</td>';
                                    echo '<td class="text-center">' . $statusBadge . '</td>';
                                    echo '<td class="text-center">' . $voucherLink . '</td>';
                                    echo '<td>' . htmlspecialchars($row['unit_confirmation_remark'] ?? '-') . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center py-4">No records found for the selected criteria.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer no-print text-right">
                <button type="button" class="btn btn-info" onclick="window.print()"><i class="fa fa-print"></i> Print
                    Report</button>
            </div>
        </div>
    </div>
</div>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<?php
page_footer_end();
?>