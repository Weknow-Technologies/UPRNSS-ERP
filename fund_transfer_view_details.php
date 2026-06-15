<?php
include("scripts/settings.php");
$msg = '';

/* Safety */
if (!isset($_POST) || !is_array($_POST)) {
  $_POST = [];
}
if (!isset($_SESSION)) {
  session_start();
}

page_header_start();
page_header_end();
page_sidebar();

function q($s)
{
  global $db;
  return mysqli_real_escape_string($db, (string) $s);
}
function money($v)
{
  return number_format((float) $v, 2, '.', '');
}

$header_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$header = null;

if (!$header_id) {
  $msg = '<div class="alert alert-danger">Invalid Transfer ID</div>';
} else {
  $header = mysqli_fetch_assoc(execute_query('
        SELECT h.*, bc2.cus_name AS transfer_to_name, bc.cus_name AS from_acc 
        FROM invoice_fund_transfer h 
        LEFT JOIN billit_customer bc2 ON bc2.sno = h.fund_transfer_to
        LEFT JOIN billit_customer bc ON bc.sno = h.from_account_no
        WHERE h.sno="' . q($header_id) . '" AND h.status!="5"
    '));

  if (!$header) {
    $msg = '<div class="alert alert-danger">Transfer not found or deleted.</div>';
  }
}
?>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Fund Transfer Details</h4>
        <div class="no-print" style="margin-top: 10px; margin-bottom: 20px;">
          <a href="fund_transfer1.php" class="btn btn-info btn-fill btn-sm">Back</a>
          <a href="fund_transfer1.php?edit_header_id=<?php echo $header_id; ?>"
            class="btn btn-warning btn-fill btn-sm">Edit</a>
          <?php if ($header && $header['journal_id']): ?>
            <a href="billit_payment_print.php?id=<?php echo $header['journal_id']; ?>" target="_blank"
              class="btn btn-danger btn-fill btn-sm">Voucher</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <?php echo $msg; ?>

        <?php if ($header): ?>
          <!-- Basic Table Layout -->
          <table class="table table-bordered">
            <thead>
              <tr class="bg-light">
                <td colspan="4"><strong>Transfer Information</strong></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th width="15%">Voucher No</th>
                <td width="35%"><?php echo htmlspecialchars($header['order_no']); ?></td>
                <th width="15%">Voucher ID</th>
                <td width="35%"><?php echo $header['journal_id'] ?: 'N/A'; ?></td>
              </tr>
              <tr>
                <th>Order Date</th>
                <td><?php echo date("d-m-Y", strtotime($header['order_date'])); ?></td>
                <th>Transfer Date</th>
                <td><?php echo date("d-m-Y", strtotime($header['transfer_date'])); ?></td>
              </tr>
              <tr>
                <th>From Account</th>
                <td><?php echo htmlspecialchars($header['from_acc']); ?></td>
                <th>Transfer To</th>
                <td><?php echo htmlspecialchars($header['transfer_to_name']); ?></td>
              </tr>
              <tr>
                <th>Created By</th>
                <td><?php echo htmlspecialchars($header['created_by']); ?></td>
                <th>Creation Time</th>
                <td><?php echo date("d-m-Y H:i:s", strtotime($header['creation_time'])); ?></td>
              </tr>
            </tbody>
          </table>

          <div class="row mt-4">
            <div class="col-md-6">
              <table class="table table-bordered">
                <tr class="bg-light">
                  <td colspan="2"><strong>Deduction Percentages</strong></td>
                </tr>
                <tr>
                  <th>GST %</th>
                  <td><?php echo $header['gst_per']; ?>%</td>
                </tr>
                <tr>
                  <th>Adv Centage %</th>
                  <td><?php echo $header['centage_per']; ?>%</td>
                </tr>
                <tr>
                  <th>GST-TDS %</th>
                  <td><?php echo $header['gsttds_per']; ?>%</td>
                </tr>
                <tr>
                  <th>Income Tax %</th>
                  <td><?php echo $header['it_per']; ?>%</td>
                </tr>
                <tr>
                  <th>Labour Cess (Amt)</th>
                  <td><?php echo money($header['labour_abs']); ?></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-bordered">
                <tr class="bg-light">
                  <td colspan="2"><strong>Summary Totals</strong></td>
                </tr>
                <tr>
                  <th>Total Transfer Amount</th>
                  <td class="text-right"><?php echo money($header['total_transfer_amount']); ?></td>
                </tr>
                <tr>
                  <th>Total GST</th>
                  <td class="text-right"><?php echo money($header['total_gst']); ?></td>
                </tr>
                <tr>
                  <th>Total Adv Centage</th>
                  <td class="text-right"><?php echo money($header['total_adv_centage']); ?></td>
                </tr>
                <tr>
                  <th>Total GST-TDS</th>
                  <td class="text-right"><?php echo money($header['total_gst_tds']); ?></td>
                </tr>
                <tr>
                  <th>Total Proposed</th>
                  <td class="text-right"><strong><?php echo money($header['total_proposed']); ?></strong></td>
                </tr>
              </table>
            </div>
          </div>

          <h5 class="mt-4 mb-2"><strong>Project Wise Details</strong></h5>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mt-2">
              <thead class="bg-dark text-white">
                <tr>
                  <th class="text-white">S.No</th>
                  <th class="text-white">Department</th>
                  <th class="text-white">District</th>
                  <th width="35%" class="text-white">Project Name</th>
                  <th class="text-right text-white">Amount</th>
                  <th class="text-white">Remark</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;
                $sql = '
                  SELECT t.*, 
                         COALESCE(d1.department_name_hindi, d2.department_name_hindi) as department_name,
                         COALESCE(dist1.district_name_hindi, dist2.district_name_hindi) as district_name,
                         p.project_name_hindi, p.project_name as project_name_eng,
                         bc.cus_name as bank_name
                  FROM transaction_fund_transfer t
                  LEFT JOIN uprnss_project_temp p ON p.sno = t.project_name
                  LEFT JOIN uprnss_department_name d1 ON d1.sno = t.department
                  LEFT JOIN uprnss_department_name d2 ON d2.sno = p.department_id
                  LEFT JOIN uprnss_district dist1 ON dist1.sno = t.district
                  LEFT JOIN uprnss_district dist2 ON dist2.sno = p.district_id
                  LEFT JOIN billit_customer bc ON bc.sno = t.to_bank_name
                  WHERE t.invoice_header_id="' . q($header_id) . '" AND (t.status <> \'5\' OR t.status IS NULL)
                  ORDER BY t.sno ASC
                ';
                $rs = execute_query($sql);

                while ($row = mysqli_fetch_assoc($rs)) {
                  $pname_display = '<strong>' . htmlspecialchars($row['project_name_eng'] ?: 'N/A') . '</strong>';
                  if ($row['project_name_hindi']) {
                    $pname_display .= '<br><small class="text-muted">' . htmlspecialchars($row['project_name_hindi']) . '</small>';
                  }
                  if (!$row['project_name_eng'] && !$row['project_name_hindi']) {
                    $pname_display = '<span class="text-danger">ID: ' . $row['project_name'] . ' (Not Found)</span>';
                  }

                  echo '<tr>
                    <td class="text-center">' . ($i++) . '</td>
                    <td>' . htmlspecialchars($row['department_name'] ?: 'N/A') . '</td>
                    <td>' . htmlspecialchars($row['district_name'] ?: 'N/A') . '</td>
                    <td>' . $pname_display . '</td>
                    <td class="text-right">' . money($row['transafer_amount']) . '</td>
                    <td>' . htmlspecialchars($row['remark'] ?? '') . '</td>
                  </tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php page_footer_start(); ?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<?php page_footer_end(); ?>