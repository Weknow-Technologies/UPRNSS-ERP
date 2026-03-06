<?php
include("scripts/settings.php");
$msg='';

/* Safety */
if (!isset($_POST) || !is_array($_POST)) { $_POST = []; }
if (!isset($_SESSION)) { session_start(); }

page_header_start();
page_header_end();
page_sidebar();

/* ---------------- Helpers ---------------- */
function q($s){ global $db; return mysqli_real_escape_string($db, (string)$s); }
function money($v){ return number_format((float)$v,2,'.',''); }

/* ---------------- Get Header Details ---------------- */
$header_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$header_id){
    $msg = '<div class="alert alert-danger">Invalid Transfer ID</div>';
} else {
    $header = mysqli_fetch_assoc(execute_query('
        SELECT h.*, tt.transfer_to, bc.cus_name AS from_acc 
        FROM invoice_fund_transfer h 
        LEFT JOIN transfer_to_type tt ON tt.sno=h.fund_transfer_to
        LEFT JOIN billit_customer bc ON bc.sno=h.from_account_no
        WHERE h.sno="'.q($header_id).'" AND h.status!="5"
    '));
    
    if(!$header){
        $msg = '<div class="alert alert-danger">Transfer not found</div>';
    }
}
?>

<div class="card">
  <div class="card-header">
    <h4 class="card-title text-center">Fund Transfer Details</h4>
    <div class="text-right">
      <a href="fund_transfer_report_new.php" class="btn btn-secondary">← Back to Report</a>
      <a href="fund_transfer1.php?edit_header_id=<?php echo $header_id; ?>" class="btn btn-warning">✏️ Edit Transfer</a>
      <?php if($header && $header['journal_id']): ?>
      <a href="billit_payment_print.php?id=<?php echo $header['journal_id']; ?>" target="_blank" class="btn btn-success">🧾 View Voucher</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="card-body">
    <?php echo $msg; ?>
    
    <?php if($header): ?>
    <!-- Header Information -->
    <div class="row mb-4">
      <div class="col-md-12">
        <h5>Transfer Information</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <tr>
              <th width="20%">Header ID</th>
              <td><?php echo $header['sno']; ?></td>
              <th width="20%">Voucher ID</th>
              <td><?php echo $header['journal_id'] ?: 'N/A'; ?></td>
            </tr>
            <tr>
              <th>Order No.</th>
              <td><?php echo htmlspecialchars($header['order_no']); ?></td>
              <th>Order Date</th>
              <td><?php echo date("d-m-Y", strtotime($header['order_date'])); ?></td>
            </tr>
            <tr>
              <th>Transfer Date</th>
              <td><?php echo date("d-m-Y", strtotime($header['transfer_date'])); ?></td>
              <th>Fund Transfer To</th>
              <td><?php echo htmlspecialchars($header['transfer_to']); ?></td>
            </tr>
            <tr>
              <th>From Account</th>
              <td><?php echo htmlspecialchars($header['from_acc']); ?></td>
              <th>Created By</th>
              <td><?php echo htmlspecialchars($header['created_by']); ?></td>
            </tr>
            <tr>
              <th>Creation Time</th>
              <td><?php echo date("d-m-Y H:i:s", strtotime($header['creation_time'])); ?></td>
              <th>Status</th>
              <td><span class="badge badge-success">Active</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- Deduction Percentages -->
    <div class="row mb-4">
      <div class="col-md-12">
        <h5>Deduction Percentages</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <tr>
              <th width="20%">GST %</th>
              <td><?php echo $header['gst_per']; ?>%</td>
              <th width="20%">Advance Centage %</th>
              <td><?php echo $header['centage_per']; ?>%</td>
            </tr>
            <tr>
              <th>GST-TDS %</th>
              <td><?php echo $header['gsttds_per']; ?>%</td>
              <th>Income Tax %</th>
              <td><?php echo $header['it_per']; ?>%</td>
            </tr>
            <tr>
              <th>Labour Cess (Amount)</th>
              <td><?php echo money($header['labour_abs']); ?></td>
              <th></th>
              <td></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- Summary Totals -->
    <div class="row mb-4">
      <div class="col-md-12">
        <h5>Summary Totals</h5>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <tr class="bg-light">
              <th>Total Transfer Amount</th>
              <th>Total GST</th>
              <th>Total Remain After GST</th>
              <th>Total Advance Centage</th>
              <th>Total GST-TDS</th>
              <th>Total Labour</th>
              <th>Total Income Tax</th>
              <th>Total Proposed</th>
            </tr>
            <tr>
              <td class="text-right font-weight-bold"><?php echo money($header['total_transfer_amount']); ?></td>
              <td class="text-right"><?php echo money($header['total_gst']); ?></td>
              <td class="text-right"><?php echo money($header['total_remain_after_gst']); ?></td>
              <td class="text-right"><?php echo money($header['total_adv_centage']); ?></td>
              <td class="text-right"><?php echo money($header['total_gst_tds']); ?></td>
              <td class="text-right"><?php echo money($header['total_labour']); ?></td>
              <td class="text-right"><?php echo money($header['total_income_tax']); ?></td>
              <td class="text-right font-weight-bold text-success"><?php echo money($header['total_proposed']); ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- Transaction Details -->
    <div class="row">
      <div class="col-md-12">
        <h5>Transaction Details</h5>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Department</th>
                <th>Sub Department</th>
                <th>District</th>
                <th>Project</th>
                <th>Unit</th>
                <th>To Bank</th>
                <th>To IFSC</th>
                <th>To Account No</th>
                <th>Transfer Amount</th>
                <th>GST</th>
                <th>CGST</th>
                <th>SGST</th>
                <th>Remain</th>
                <th>Advance</th>
                <th>GST-TDS</th>
                <th>CGST-TDS</th>
                <th>SGST-TDS</th>
                <th>Labour</th>
                <th>IT</th>
                <th>Proposed</th>
                <th>Remark</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $i=1;
              $rs = execute_query('
                SELECT t.*, d.department_name_hindi, sd.sub_department_hindi, 
                       dist.district_name_hindi, p.project_name_hindi, u.unit as unit_name,
                       bc.cus_name as bank_name
                FROM transaction_fund_transfer t
                LEFT JOIN uprnss_department_name d ON d.sno = t.department
                LEFT JOIN uprnss_sub_department sd ON sd.sno = t.sub_department_id
                LEFT JOIN uprnss_district dist ON dist.sno = t.district
                LEFT JOIN uprnss_project_temp p ON p.sno = t.project_name
                LEFT JOIN billit_unit u ON u.sno = t.unit_id
                LEFT JOIN billit_customer bc ON bc.sno = t.to_bank_name
                WHERE t.invoice_header_id="'.q($header_id).'" AND t.status!="5"
                ORDER BY t.sno ASC
              ');
              
              while($row = mysqli_fetch_assoc($rs)){
                echo '<tr>
                  <td>'.($i++).'</td>
                  <td>'.htmlspecialchars($row['department_name_hindi']).'</td>
                  <td>'.htmlspecialchars($row['sub_department_hindi']).'</td>
                  <td>'.htmlspecialchars($row['district_name_hindi']).'</td>
                  <td>'.htmlspecialchars($row['project_name_hindi']).'</td>
                  <td>'.htmlspecialchars($row['unit_name']).'</td>
                  <td>'.htmlspecialchars($row['bank_name']).'</td>
                  <td>'.htmlspecialchars($row['to_bank_ifsc']).'</td>
                  <td>'.htmlspecialchars($row['to_account_no']).'</td>
                  <td class="text-right">'.money($row['transafer_amount']).'</td>
                  <td class="text-right">'.money($row['gstdeduction']).'</td>
                  <td class="text-right">'.money($row['cgst_amount']).'</td>
                  <td class="text-right">'.money($row['sgst_amount']).'</td>
                  <td class="text-right">'.money($row['totelmgst']).'</td>
                  <td class="text-right">'.money($row['sentage']).'</td>
                  <td class="text-right">'.money($row['gsttds']).'</td>
                  <td class="text-right">'.money($row['tds_cgst']).'</td>
                  <td class="text-right">'.money($row['tds_sgst']).'</td>
                  <td class="text-right">'.money($row['leborses']).'</td>
                  <td class="text-right">'.money($row['incometax']).'</td>
                  <td class="text-right font-weight-bold">'.money($row['praposemoney']).'</td>
                  <td>'.htmlspecialchars($row['remark']).'</td>
                </tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php page_footer_start(); ?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<?php page_footer_end(); ?>
