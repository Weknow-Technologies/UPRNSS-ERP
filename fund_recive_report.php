<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
$msg = '';
$tab = 1;

page_header_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php
page_header_end();
page_sidebar();

/* ---------- Handle Verify ---------- */
if (isset($_GET['verify_id']) && is_numeric($_GET['verify_id'])) {
    $vid = intval($_GET['verify_id']);
    // Update status to 1 (verified) in invoice_fund_receive
    execute_query('UPDATE invoice_fund_receive SET status="1" WHERE sno="' . $vid . '"');
    $msg = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Voucher #' . $vid . ' verified successfully.</div>';
}

/* ---------- Filters ---------- */
$filter_dept = isset($_GET['dept']) ? intval($_GET['dept']) : 0;
$filter_subdept = isset($_GET['subdept']) ? intval($_GET['subdept']) : 0;
$filter_dist = isset($_GET['dist']) ? intval($_GET['dist']) : 0;
$filter_project = isset($_GET['project']) ? intval($_GET['project']) : 0;
$filter_from = isset($_GET['from_date']) && $_GET['from_date'] != '' ? $_GET['from_date'] : '';
$filter_to = isset($_GET['to_date']) && $_GET['to_date'] != '' ? $_GET['to_date'] : '';

/* ---------- Unit clause (filter by logged-in unit) ---------- */
// Determine unit WHERE clause
$unit_where = '';
$is_admin = ($_SESSION['usertype'] == 'sadmin' || $_SESSION['usertype'] == '6');
if (!$is_admin) {
    // Filter by unit of the logged-in user via billit_invoice_erp_receipt.unit_id
    if (!empty($_SESSION['divisions'])) {
        $divArr = array_map('intval', (array) $_SESSION['divisions']);
        $unit_where = ' AND j.unit_id IN (' . implode(',', $divArr) . ')';
    } elseif (!empty($_SESSION['division_id'])) {
        $unit_where = ' AND j.unit_id = "' . intval($_SESSION['division_id']) . '"';
    } elseif (!empty($_SESSION['userdivision'])) {
        $unit_where = ' AND j.unit_id = "' . intval($_SESSION['userdivision']) . '"';
    }
}

/* ---------- Build transaction-level query ---------- */
$where_parts = ["1=1"];

if ($filter_dept)
    $where_parts[] = 't.department_id = "' . $filter_dept . '"';
if ($filter_subdept)
    $where_parts[] = 't.sub_department_id = "' . $filter_subdept . '"';
if ($filter_dist)
    $where_parts[] = 't.district_id = "' . $filter_dist . '"';
if ($filter_project)
    $where_parts[] = 't.project_id = "' . $filter_project . '"';
if ($filter_from)
    $where_parts[] = 'h.receive_date >= "' . mysqli_real_escape_string($db, $filter_from) . '"';
if ($filter_to)
    $where_parts[] = 'h.receive_date <= "' . mysqli_real_escape_string($db, $filter_to) . '"';

$where_sql = implode(' AND ', $where_parts);

$sql = "
    SELECT
        t.sno            AS t_sno,
        t.invoice_id,
        t.department_id,
        t.sub_department_id,
        t.district_id,
        t.project_id,
        t.p_receive_amount,
        t.tds_alloc,
        t.gsttds_alloc,
        t.labour_cess_alloc,
        t.cgst_amount,
        t.sgst_amount,
        t.p_diff_amount,
        h.order_no,
        h.receive_date,
        h.voucher_no,
        h.installment,
        h.tds_per,
        h.gst_tds_per,
        h.bank_name,
        h.remark,
        h.status       AS h_status,
        j.unit_id,
        d.department_name_hindi,
        sd.sub_department_hindi,
        dist.district_name_hindi AS district_name,
        p.project_name_hindi,
        ud.division_name,
        ud.division_name_english
    FROM transaction_fund_receive t
    INNER JOIN invoice_fund_receive h ON h.sno = t.invoice_id
    LEFT  JOIN billit_invoice_erp_receipt j ON j.table_name = 'invoice_fund_receive' AND j.table_id = h.sno
    LEFT  JOIN uprnss_department_name d   ON d.sno = t.department_id
    LEFT  JOIN uprnss_sub_department sd   ON sd.sno = t.sub_department_id
    LEFT  JOIN uprnss_district dist         ON dist.sno = t.district_id
    LEFT  JOIN uprnss_project_temp p       ON p.sno = t.project_id
    LEFT  JOIN uprnss_division ud          ON ud.s_no = j.unit_id
    WHERE $where_sql $unit_where
    ORDER BY h.receive_date DESC, h.sno DESC, t.sno ASC";

$result = execute_query($sql);

/* ---------- Dept/SubDept/District dropdowns for filter bar ---------- */
$dept_q = 'SELECT sno, department_name_hindi FROM uprnss_department_name ORDER BY department_name_hindi';
$subdept_q = 'SELECT sno, sub_department_hindi FROM uprnss_sub_department ORDER BY sub_department_hindi';
$dist_q = 'SELECT sno, district_name_hindi AS district_name FROM uprnss_district ORDER BY district_name_hindi';
$proj_q = 'SELECT sno, project_name_hindi FROM uprnss_project_temp ORDER BY project_name_hindi LIMIT 500';

$dept_res = execute_query($dept_q);
$subdept_res = execute_query($subdept_q);
$dist_res = execute_query($dist_q);
$proj_res = execute_query($proj_q);
?>

<div class="content">
    <div class="container-fluid">

        <?php if ($msg)
            echo $msg; ?>

        <!-- ===== FILTER CARD ===== -->
        <div class="card card-custom mb-3">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-filter me-2"></i>
                <h6 class="mb-0 text-white">Fund Receive Report — Filter</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-2">

                        <div class="col-md-2">
                            <label class="form-label">Department</label>
                            <select name="dept" class="form-control">
                                <option value="">--- Select ---</option>
                                <?php
                                mysqli_data_seek($dept_res, 0);
                                while ($r = mysqli_fetch_assoc($dept_res)) {
                                    $sel = ($filter_dept == $r['sno']) ? 'selected' : '';
                                    echo '<option value="' . $r['sno'] . '" ' . $sel . '>' . htmlspecialchars($r['department_name_hindi']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">SubDepartment</label>
                            <select name="subdept" class="form-control">
                                <option value="">--- Select ---</option>
                                <?php
                                while ($r = mysqli_fetch_assoc($subdept_res)) {
                                    $sel = ($filter_subdept == $r['sno']) ? 'selected' : '';
                                    echo '<option value="' . $r['sno'] . '" ' . $sel . '>' . htmlspecialchars($r['sub_department_hindi']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">District</label>
                            <select name="dist" class="form-control">
                                <option value="">--- Select ---</option>
                                <?php
                                while ($r = mysqli_fetch_assoc($dist_res)) {
                                    $sel = ($filter_dist == $r['sno']) ? 'selected' : '';
                                    echo '<option value="' . $r['sno'] . '" ' . $sel . '>' . htmlspecialchars($r['district_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Project</label>
                            <select name="project" class="form-control">
                                <option value="">--- Select ---</option>
                                <?php
                                while ($r = mysqli_fetch_assoc($proj_res)) {
                                    $sel = ($filter_project == $r['sno']) ? 'selected' : '';
                                    echo '<option value="' . $r['sno'] . '" ' . $sel . '>' . htmlspecialchars($r['project_name_hindi']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control"
                                value="<?php echo htmlspecialchars($filter_from); ?>">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control"
                                value="<?php echo htmlspecialchars($filter_to); ?>">
                        </div>

                        <div class="col-md-1 d-flex align-items-end gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <a href="fund_recive_report.php" class="btn btn-secondary btn-sm w-100">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>

                    </div><!-- .row -->
                </form>
            </div>
        </div>

        <!-- ===== REPORT TABLE ===== -->
        <div class="card card-custom">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-table me-2"></i><span class="text-white fw-bold">Fund Receive
                        Report</span></span>
                <button class="btn btn-sm btn-light no-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
            <div class="card-body table-full-width table-responsive">
                <table class="table table-bordered table-hover table-striped" id="fundReciveTable"
                    style="font-size:13px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="vertical-align:middle; text-align:center;">S.No.</th>
                            <th rowspan="2" style="vertical-align:middle;">Voucher No.</th>
                            <th rowspan="2" style="vertical-align:middle;">Date</th>
                            <th rowspan="2" style="vertical-align:middle;">Unit</th>
                            <th rowspan="2" style="vertical-align:middle;">Department</th>
                            <th rowspan="2" style="vertical-align:middle;">SubDepartment</th>
                            <th rowspan="2" style="vertical-align:middle;">District</th>
                            <th rowspan="2" style="vertical-align:middle;">Project</th>
                            <th rowspan="2" style="vertical-align:middle; text-align:right;">Project Received Amount (₹)
                            </th>
                            <th colspan="7" style="text-align:center;">Heads / Deductions</th>
                            <th rowspan="2" style="vertical-align:middle;">Status</th>
                            <th rowspan="2" class="no-print" style="vertical-align:middle;">Actions</th>
                        </tr>
                        <tr>
                            <th style="text-align:right;">TDS (₹)</th>
                            <th style="text-align:right;">GST-TDS (₹)</th>
                            <th style="text-align:right;">Labour Cess (₹)</th>
                            <th style="text-align:right;">CGST (₹)</th>
                            <th style="text-align:right;">SGST (₹)</th>
                            <th style="text-align:right;">Net/Base (₹)</th>
                            <th>Bank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $grand_receive = 0;
                        $grand_tds = 0;
                        $grand_gsttds = 0;
                        $grand_labour = 0;
                        $grand_cgst = 0;
                        $grand_sgst = 0;
                        $grand_net = 0;

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {

                                $receive = (float) ($row['p_receive_amount'] ?? 0);
                                $tds = (float) ($row['tds_alloc'] ?? 0);
                                $gsttds = (float) ($row['gsttds_alloc'] ?? 0);
                                $labour = (float) ($row['labour_cess_alloc'] ?? 0);
                                $cgst = (float) ($row['cgst_amount'] ?? 0);
                                $sgst = (float) ($row['sgst_amount'] ?? 0);
                                $net = (float) ($row['p_diff_amount'] ?? 0);

                                $grand_receive += $receive;
                                $grand_tds += $tds;
                                $grand_gsttds += $gsttds;
                                $grand_labour += $labour;
                                $grand_cgst += $cgst;
                                $grand_sgst += $sgst;
                                $grand_net += $net;

                                $dept_name = $row['department_name_hindi'] ?? '—';
                                $subdept_name = $row['sub_department_hindi'] ?? '—';
                                $dist_name = $row['district_name'] ?? '—';
                                $proj_name = $row['project_name_hindi'] ?? '—';
                                $unit_name = ($row['division_name'] ?? $row['division_name_english'] ?? get_division($row['unit_id']));

                                $h_status = (int) ($row['h_status'] ?? 0);
                                $status_badge = ($h_status == 1)
                                    ? '<span class="badge" style="background:#28a745;color:#fff;">Verified</span>'
                                    : '<span class="badge" style="background:#ffc107;color:#333;">Pending</span>';

                                $inv_id = (int) $row['invoice_id'];

                                echo '<tr>
                                    <td style="text-align:center;">' . $i++ . '</td>
                                    <td>' . htmlspecialchars($row['voucher_no'] ?? '') . '</td>
                                    <td style="white-space:nowrap;">' . ($row['receive_date'] ? date('d-m-Y', strtotime($row['receive_date'])) : '') . '</td>
                                    <td>' . htmlspecialchars($unit_name) . '</td>
                                    <td>' . htmlspecialchars($dept_name) . '</td>
                                    <td>' . htmlspecialchars($subdept_name) . '</td>
                                    <td>' . htmlspecialchars($dist_name) . '</td>
                                    <td>' . htmlspecialchars($proj_name) . '</td>
                                    <td style="text-align:right;">' . number_format($receive, 2) . '</td>
                                    <td style="text-align:right;">' . ($tds > 0 ? number_format($tds, 2) : '—') . '</td>
                                    <td style="text-align:right;">' . ($gsttds > 0 ? number_format($gsttds, 2) : '—') . '</td>
                                    <td style="text-align:right;">' . ($labour > 0 ? number_format($labour, 2) : '—') . '</td>
                                    <td style="text-align:right;">' . ($cgst > 0 ? number_format($cgst, 2) : '—') . '</td>
                                    <td style="text-align:right;">' . ($sgst > 0 ? number_format($sgst, 2) : '—') . '</td>
                                    <td style="text-align:right;">' . ($net > 0 ? number_format($net, 2) : '—') . '</td>
                                    <td style="white-space:nowrap;">' . htmlspecialchars($row['bank_name'] ?? '') . '</td>
                                    <td style="text-align:center;">' . $status_badge . '</td>
                                    <td class="no-print" style="white-space:nowrap; text-align:center;">
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">';

                                // View print
                                echo '<a href="billit_recive_print.php?voucher=' . $inv_id . '" target="_blank"
                                        class="btn btn-sm btn-outline-info" title="View Voucher">
                                        <i class="fas fa-eye"></i>
                                    </a>';


                                // Verify button (only if not yet verified)
                                if ($h_status != 1) {
                                    echo '<a href="fund_recive_report.php?' . http_build_query(array_filter([
                                        'verify_id' => $inv_id,
                                        'dept' => $filter_dept ?: null,
                                        'subdept' => $filter_subdept ?: null,
                                        'dist' => $filter_dist ?: null,
                                        'project' => $filter_project ?: null,
                                        'from_date' => $filter_from ?: null,
                                        'to_date' => $filter_to ?: null,
                                    ])) . '"
                                        class="btn btn-sm btn-outline-success" title="Verify"
                                        onclick="return confirm(\'Verify this entry?\')">
                                        <i class="fas fa-check-circle"></i> Verify
                                    </a>';
                                }

                                echo '  </div>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="18" class="text-center text-muted py-3">No records found</td></tr>';
                        }
                        ?>
                    </tbody>

                    <!-- Grand Total Row -->
                    <?php if ($grand_receive > 0): ?>
                        <tfoot>
                            <tr style="background:#f2f2f2; font-weight:bold;">
                                <td colspan="8" style="text-align:right;">Grand Total</td>
                                <td style="text-align:right;"><?php echo number_format($grand_receive, 2); ?></td>
                                <td style="text-align:right;"><?php echo number_format($grand_tds, 2); ?></td>
                                <td style="text-align:right;"><?php echo number_format($grand_gsttds, 2); ?></td>
                                <td style="text-align:right;"><?php echo number_format($grand_labour, 2); ?></td>
                                <td style="text-align:right;"><?php echo number_format($grand_cgst, 2); ?></td>
                                <td style="text-align:right;"><?php echo number_format($grand_sgst, 2); ?></td>
                                <td style="text-align:right;"><?php echo number_format($grand_net, 2); ?></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div><!-- card-body -->
        </div><!-- card -->

    </div><!-- container-fluid -->
</div><!-- content -->

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        .sidebar {
            display: none !important;
        }

        .main-panel {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .card-custom .card-header {
            background: #444 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
        }

        table thead th {
            background: #444 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<?php
page_footer_end();
?>