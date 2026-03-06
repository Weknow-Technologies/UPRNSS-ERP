<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$msg = '';
$module_name= "invoice_deduction_release";

/* ---------- Helpers ---------- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---------- Vendor config ---------- */
$VENDOR_TABLE     = 'vendor';
$VENDOR_ID_COL    = 'sno';
$VENDOR_NAME_COL  = 'firm_name';

/* Load vendors (used in modal dropdown) */
$vendors = [];
$vendor_sql = "SELECT {$VENDOR_ID_COL} AS vid, {$VENDOR_NAME_COL} AS vname FROM {$VENDOR_TABLE} ORDER BY {$VENDOR_NAME_COL}";
if ($vres = $db->query($vendor_sql)) {
    while($vr = $vres->fetch_assoc()){
        $vendors[] = ['id' => (int)$vr['vid'], 'name' => $vr['vname']];
    }
}

/* ---------- Handle modal payment update ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idr_payment_update'])) {
    $id       = (int)($_POST['id'] ?? 0);
    $mode     = trim($_POST['payment_mode'] ?? '');
    $type     = trim($_POST['type_of_payment'] ?? '');
    $ref      = trim($_POST['payment_ref'] ?? '');
    $remarks  = trim($_POST['payment_remarks'] ?? '');
    $voucher  = trim($_POST['voucher_no'] ?? '');
    $date_raw = trim($_POST['payment_date'] ?? '');
    $vendor_id_hidden = (int)($_POST['vendor_id'] ?? 0);

    // New manual bank fields:
    $acc_no   = trim($_POST['payee_account_no'] ?? '');
    $ifsc     = trim($_POST['payee_ifsc_code'] ?? '');
    $bank     = trim($_POST['payee_bank_name'] ?? '');

    $user_id  = (int)($_SESSION['usersno'] ?? ($_SESSION['user_id'] ?? 0));

    // Validate date (YYYY-MM-DD) or set null
    $payment_date = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_raw)) ? $date_raw : null;

    if ($id > 0 && $mode !== '' && $type !== '') {
        // ensure only approved rows can be updated
        $chk = $db->prepare("SELECT status, vendor_id FROM invoice_deduction_release WHERE id=? LIMIT 1");
        $chk->bind_param("i", $id);
        $chk->execute();
        $res = $chk->get_result();
        $row_s = $res ? $res->fetch_assoc() : null;

        if ($row_s && (int)$row_s['status'] === 1) {
            $stmt = $db->prepare("
                UPDATE invoice_deduction_release
                   SET payment_mode        = ?,
                       type_of_payment     = ?,
                       payment_ref         = ?,
                       payment_remarks     = ?,
                       voucher_no          = ?,
                       payment_date        = ?,
                       payee_account_no    = ?,
                       payee_ifsc_code     = ?,
                       payee_bank_name     = ?,
                       payment_updated_by  = ?,
                       payment_updated_at  = NOW()
                 WHERE id = ?
                 LIMIT 1
            ");
            if ($stmt) {
                $stmt->bind_param(
                    "sssssssssii",
                    $mode, $type, $ref, $remarks, $voucher, $payment_date,
                    $acc_no, $ifsc, $bank,
                    $user_id, $id
                );
                if ($stmt->execute()) {
                    $msg = '<div class="alert alert-success">Payment details saved.</div>';
                } else {
                    $msg = '<div class="alert alert-danger">Failed to save payment details. Please try again.</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">DB prepare failed. Check columns in invoice_deduction_release.</div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">Only approved records can be updated.</div>';
        }
    } else {
        $msg = '<div class="alert alert-warning">Mode and Type of Payment are required.</div>';
    }
}
$heads = [
    'gstdeduction' => 'GST',
    'gsttds' => 'GST TDS',
    'incometax' => 'Income Tax',
    'security' => 'Security',
    'royalty' => 'Royalty',
    'sentage'      => 'Sentage',
    'leborses'     => 'LeborCess',
    'contingency'  => 'Contingency'
];

$divisions = array_map('intval', $_SESSION['divisions']);
$division_ids = implode(',', $divisions);

page_header_start();
page_header_end();
page_sidebar();

$case = $_GET['case'] ?? 'summary';

switch ($case) {

// ======================== CASE: VIEW ========================
case 'view':
    $invoice_id = intval($_GET['id']);

    $sql = "SELECT d.*, iat.bill_no, upt.project_name_hindi 
            FROM deduction_release d
            JOIN invoice_account_fund_transafer iat ON iat.sno = d.bill_id
            LEFT JOIN uprnss_project_temp upt ON upt.sno = d.project_id
            JOIN tender_allotment ta ON ta.project_id = iat.project_name
            WHERE d.invoice_id = $invoice_id AND ta.division_id IN ($division_ids) AND ta.status!=5";
    $res = $db->query($sql);
    ?>

    <div class="card mt-4">
        <div class="card-header text-center">
            <h5>Invoice #<?= $invoice_id ?> - Transaction Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>Project Name</th>
                        <th>Bill No</th>
                        <th>Held Amount</th>
                        <th>Released Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_held = $total_released = 0;
                    while ($row = $res->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['project_name_hindi']}</td>
                            <td>{$row['bill_no']}</td>
                            <td>₹ " . number_format($row['held_amount'], 2) . "</td>
                            <td>₹ " . number_format($row['released_amount'], 2) . "</td>
                        </tr>";
                        $total_held += $row['held_amount'];
                        $total_released += $row['released_amount'];
                    }
                    echo "<tr class='bg-light font-weight-bold text-right'>
                            <td colspan='2'>Total</td>
                            <td>₹ " . number_format($total_held, 2) . "</td>
                            <td>₹ " . number_format($total_released, 2) . "</td>
                          </tr>";
                    ?>
                </tbody>
            </table>
            <div class="text-center mt-3">
                <a href="release_amount_report.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

    <?php
    break;

// ======================== CASE: SUMMARY (default) ========================
default:
?>

<div class="card">
    <div class="card-header text-center">
        <h4>Released Deduction Report</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($msg)) echo $msg; ?>

        <style>
          .idr-cell { min-width: 280px; }
          .idr-details { font-size: 12px; line-height: 1.35; display: grid; gap: 4px; }
          .idr-details .r { display: grid; grid-template-columns: 120px 1fr; align-items: baseline; }
          .idr-details .r > span { color: #6c757d; }
          .idr-details .r > b { font-weight: 600; word-break: break-word; }
          .align-top { vertical-align: top; }
        </style>
		<style>
          /* Wider modal */
          #paymentModal .modal-dialog {
            max-width: 960px;
          }

          /* 2 columns grid inside modal body */
          #paymentModal .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
          }
          #paymentModal .grid-2 .form-group {
            margin-bottom: 12px;
          }

          /* mobile par 1 column */
          @media (max-width: 576px) {
            #paymentModal .grid-2 {
              grid-template-columns: 1fr;
            }
          }
        </style>
        <form method="get" action="">
            <input type="hidden" name="case" value="summary">
            <div class="row">
                <div class="col-md-3">
                    <label>Vendor</label>
                    <select name="vendor_id" class="form-control">
                        <option value="">-- All Vendors --</option>
                        <?php
                        $vendor_sql = "
                            SELECT DISTINCT 
                                v.sno, v.contractor_name, v.firm_name 
                            FROM vendor v
                            JOIN tender_allotment ta ON ta.project_awarded_to = v.sno
                            JOIN invoice_account_fund_transafer iat ON iat.project_name = ta.project_id
                            WHERE ta.division_id IN ($division_ids) AND ta.status!=5
                            AND iat.genrate_note_sheet = 1
                            ORDER BY contractor_name
                        ";
                        $vendor_q = $db->query($vendor_sql);
                        while ($v = $vendor_q->fetch_assoc()) {
                            $sel = ($_GET['vendor_id'] ?? '') == $v['sno'] ? 'selected' : '';
                            echo "<option value='{$v['sno']}' $sel>{$v['contractor_name']} ({$v['firm_name']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Deduction Type</label>
                    <select name="deduction_type" class="form-control">
                        <option value="">-- All Types --</option>
                        <?php
                        foreach ($heads as $key => $label) {
                            $sel = ($_GET['deduction_type'] ?? '') == $key ? 'selected' : '';
                            echo "<option value='$key' $sel>$label</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date'] ?? '' ?>">
                </div>

                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date'] ?? '' ?>">
                </div>

                <div class="col-md-2 pt-4">
                    <button class="btn btn-primary mt-2">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Apply default filters if not set
$default_to   = date('Y-m-d');
$default_from = date('Y-m-d', strtotime('-1 month'));

$where = "WHERE 1=1 AND ta.division_id IN ($division_ids)";

// Vendor
if (!empty($_GET['vendor_id'])) {
    $vendor_id = intval($_GET['vendor_id']);
    $where .= " AND h.vendor_id = $vendor_id";
} else {
    $_GET['vendor_id'] = '';
}

// Deduction Type
if (!empty($_GET['deduction_type'])) {
    $type = $_GET['deduction_type'];
    $where .= " AND h.deduction_type = '$type'";
} else {
    $_GET['deduction_type'] = '';
}

// From Date
if (!empty($_GET['from_date'])) {
    $from = $_GET['from_date'];
} else {
    $from = $default_from;
    $_GET['from_date'] = $default_from;
}
$where .= " AND h.release_date >= '$from'";

// To Date
if (!empty($_GET['to_date'])) {
    $to = $_GET['to_date'];
} else {
    $to = $default_to;
    $_GET['to_date'] = $default_to;
}
$where .= " AND h.release_date <= '$to'";

// Final SQL
$sql = "
    SELECT h.*, v.contractor_name,h.status, v.firm_name, COUNT(dr.id) AS total_bills, SUM(dr.released_amount) AS total_released
    FROM invoice_deduction_release h 
    JOIN vendor v ON v.sno = h.vendor_id
    JOIN deduction_release dr ON dr.invoice_id = h.id
    JOIN invoice_account_fund_transafer iat ON iat.sno = dr.bill_id
    JOIN tender_allotment ta ON ta.project_id = iat.project_name
    $where
    AND ta.status!=5 GROUP BY h.id
    ORDER BY h.release_date DESC
";

$res = $db->query($sql);
if ($res && $res->num_rows > 0) {
    echo "<div class='card mt-4'><div class='card-body'>";
    echo "<table class='table table-bordered table-sm table-striped'>";
    echo "<thead class='thead-dark'>
            <tr>
                <th>Sr.No.</th>
                <th>Release Date</th>
                <th>Vendor</th>
                <th>Deduction Type</th>
                <th>Total Bills</th>
                <th>Total Released</th>
                <th>Current With</th>
                    <th>Status</th>
                    <th class='no-print'>Action</th>
                <th>Details</th>
                <th>Advise</th>
                <th class='no-print'>Payment Details</th>
            </tr>
        </thead><tbody>";
		$i=1;
    while ($row = $res->fetch_assoc()) {
        $label = $heads[$row['deduction_type']] ?? strtoupper($row['deduction_type']);
        echo "<tr>
                <td>".$i++."</td>
                <td>{$row['release_date']}</td>
                <td>{$row['contractor_name']} ({$row['firm_name']})</td>
                <td>$label</td>
                <td>{$row['total_bills']}</td>
                <td>₹ " . number_format($row['total_released'], 2) . "</td>";
				echo getApprovalTrailRow($module_name, $row['id']);
                echo"<td>
                    <a href='release_amount_report.php?case=view&id={$row['id']}' class='btn btn-sm btn-info'>View</a>
                </td>"; 
					if ($row['status']==1){
				echo "<td>
                   <a href='print_release_advice.php?id={$row['id']}' target='_blank' class='btn btn-success'>Print Advice</a>
                </td>";
				}else{
					echo"<td>Pending</td>";
				}
			
			// Payment Details column
			$isApproved = ((int)$row['status'] === 1);
			$isDone = (trim((string)($row['payment_mode'] ?? '')) !== '' || trim((string)($row['voucher_no'] ?? '')) !== '');
			
			// derive friendly vendor name from loaded vendor list
			$row_vendor_id = (int)($row['vendor_id'] ?? 0);
			$row_vendor_name = '';
			foreach($vendors as $v){
				if ($v['id'] === $row_vendor_id){ $row_vendor_name = $v['name']; break; }
			}
			
			echo '<td class="no-print align-top idr-cell">';
			
			if ($isApproved) {
				if ($isDone) {
					// Details view
					$mode = trim((string)($row['payment_mode'] ?? '')) !== '' ? h($row['payment_mode'] ?? '') : '<span class="text-muted">—</span>';
					$type = trim((string)($row['type_of_payment'] ?? '')) !== '' ? h($row['type_of_payment'] ?? '') : '<span class="text-muted">—</span>';
					$ref  = trim((string)($row['payment_ref'] ?? ''))   !== '' ? h($row['payment_ref'] ?? '')   : '<span class="text-muted">—</span>';
					$pdt  = trim((string)($row['payment_date'] ?? ''))  !== '' ? h($row['payment_date'] ?? '')  : '<span class="text-muted">—</span>';
					$rem  = trim((string)($row['payment_remarks'] ?? '')) !== '' ? h($row['payment_remarks'] ?? '') : '<span class="text-muted">—</span>';
					$acc  = trim((string)($row['payee_account_no'] ?? '')) !== '' ? h($row['payee_account_no'] ?? '') : '<span class="text-muted">—</span>';
					$ifsc = trim((string)($row['payee_ifsc_code'] ?? ''))  !== '' ? h($row['payee_ifsc_code'] ?? '')  : '<span class="text-muted">—</span>';
					$bank = trim((string)($row['payee_bank_name'] ?? ''))  !== '' ? h($row['payee_bank_name'] ?? '')  : '<span class="text-muted">—</span>';
					$vname = $row_vendor_name !== '' ? h($row_vendor_name) : '<span class="text-muted">—</span>';

					echo '<div class="idr-details">';
					echo '  <div class="r"><span>Vendor</span><b>'.$vname.'</b></div>';
					echo '  <div class="r"><span>Mode</span><b>'.$mode.'</b></div>';
					echo '  <div class="r"><span>Type</span><b>'.$type.'</b></div>';
					echo '  <div class="r"><span>Ref#</span><b>'.$ref.'</b></div>';
					echo '  <div class="r"><span>Date</span><b>'.$pdt.'</b></div>';
					echo '  <div class="r"><span>A/C No</span><b>'.$acc.'</b></div>';
					echo '  <div class="r"><span>IFSC</span><b>'.$ifsc.'</b></div>';
					echo '  <div class="r"><span>Bank</span><b>'.$bank.'</b></div>';
					echo '  <div class="r"><span>Remarks</span><b>'.$rem.'</b></div>';
					echo '</div>';

					// Edit (prefill)
					echo '<button type="button" class="btn btn-sm btn-outline-secondary mt-1 js-idr-open"'
					   .' data-id="'.(int)$row['id'].'"'
					   .' data-mode="'.h($row['payment_mode'] ?? '').'"'
					   .' data-type="'.h($row['type_of_payment'] ?? '').'"'
					   .' data-ref="'.h($row['payment_ref'] ?? '').'"'
					   .' data-remarks="'.h($row['payment_remarks'] ?? '').'"'
					   .' data-voucher="'.h($row['voucher_no'] ?? '').'"'
					   .' data-date="'.h($row['payment_date'] ?? '').'"'
					   .' data-vendor-id="'.(int)$row_vendor_id.'"'
					   .' data-acc="'.h($row['payee_account_no'] ?? '').'"'
					   .' data-ifsc="'.h($row['payee_ifsc_code'] ?? '').'"'
					   .' data-bank="'.h($row['payee_bank_name'] ?? '').'"'
					   .'>Edit</button>';

				} else {
					// Add details button
					echo '<button type="button" class="btn btn-sm btn-outline-primary js-idr-open"'
					   .' data-id="'.(int)$row['id'].'"'
					   .' data-mode="'.h($row['payment_mode'] ?? '').'"'
					   .' data-type="'.h($row['type_of_payment'] ?? '').'"'
					   .' data-ref="'.h($row['payment_ref'] ?? '').'"'
					   .' data-remarks="'.h($row['payment_remarks'] ?? '').'"'
					   .' data-voucher="'.h($row['voucher_no'] ?? '').'"'
					   .' data-date="'.h($row['payment_date'] ?? '').'"'
					   .' data-vendor-id="'.(int)$row_vendor_id.'"'
					   .' data-acc="'.h($row['payee_account_no'] ?? '').'"'
					   .' data-ifsc="'.h($row['payee_ifsc_code'] ?? '').'"'
					   .' data-bank="'.h($row['payee_bank_name'] ?? '').'"'
					   .'>Add Details</button>';
				}
			} else {
				echo '—';
			}
			echo '</td>';
			
            echo"</tr>";
    }
    echo "</tbody></table></div></div>";
} else {
    echo "<div class='alert alert-warning text-center mt-4'>No records found for selected filters.</div>";
}
break;
}
?>

<!-- Modal (re-usable for all rows) -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <button type="button" class="close d-inline-flex align-items-center" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="border:0;background:none;font-size:1.6rem;line-height:1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
          <input type="hidden" name="idr_payment_update" value="1">
          <input type="hidden" name="id" id="idr_id" value="">
          <input type="hidden" name="vendor_id" id="idr_vendor_hidden" value="">

        <h5 class="modal-title bg-dark text-white" id="paymentModalLabel">Payment Details</h5>
          <div class="form-group">
            <label for="idr_vendor">Vendor</label>
            <select id="idr_vendor" class="form-control" disabled>
              <option value="">--Select Vendor--</option>
              <?php foreach($vendors as $v): ?>
                <option value="<?php echo (int)$v['id'];?>"><?php echo h($v['name']);?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label for="idr_mode">Mode of Payment <span class="text-danger">*</span></label>
              <select name="payment_mode" id="idr_mode" class="form-control" required>
                <option value="">--Select--</option>
                <option value="Cheque">Cheque</option>
                <option value="Advice">Advice</option>
              </select>
            </div>

            <div class="form-group">
              <label for="idr_type">Type of Payment <span class="text-danger">*</span></label>
              <select name="type_of_payment" id="idr_type" class="form-control" required>
                <option value="">--Select--</option>
                <option value="RTGS">RTGS</option>
                <option value="NEFT">NEFT</option>
                <option value="Account Transfer">Account Transfer</option>
                <option value="Online Transfer">Online Transfer</option>
              </select>
            </div>

            <div class="form-group">
              <label for="idr_ref">Cheque/Advice/Ref Number</label>
              <input type="text" name="payment_ref" id="idr_ref" class="form-control" placeholder="Reference number">
            </div>

            <div class="form-group">
              <label for="idr_date">Cheque/Advice Date</label>
              <input type="date" name="payment_date" id="idr_date" class="form-control" placeholder="YYYY-MM-DD">
            </div>
		</div>
			<h5 class="modal-title bg-dark text-white" id="paymentModalLabel">Vendor Bank Details</h5>
		<div class="grid-2">
            <div class="form-group">
              <label for="idr_acc">Firm A/C No</label>
              <input type="text" name="payee_account_no" id="idr_acc" class="form-control" placeholder="Account Number">
            </div>

            <div class="form-group">
              <label for="idr_ifsc">IFSC Code</label>
              <input type="text" name="payee_ifsc_code" id="idr_ifsc" class="form-control" placeholder="IFSC Code">
            </div>

            <div class="form-group">
              <label for="idr_bank">Bank Name</label>
              <input type="text" name="payee_bank_name" id="idr_bank" class="form-control" placeholder="Bank Name">
            </div>

            <div class="form-group">
              <label for="idr_remarks">Remarks</label>
              <input type="text" name="payment_remarks" id="idr_remarks" class="form-control" placeholder="Optional remarks">
            </div>
          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
(function () {
  var modalEl = document.getElementById('paymentModal');

  // ---------- Helpers ----------
  function setVal(id, v){ var el=document.getElementById(id); if(el) el.value=(v||'').trim(); }
  function clearModal(){
    setVal('idr_id','');
    setVal('idr_vendor','');
    setVal('idr_vendor_hidden','');
    setVal('idr_mode','');
    setVal('idr_type','');
    setVal('idr_ref','');
    setVal('idr_remarks','');
    setVal('idr_voucher','');
    setVal('idr_date','');
    setVal('idr_acc','');
    setVal('idr_ifsc','');
    setVal('idr_bank','');
  }
  function prefillFromBtn(btn){
    setVal('idr_id', btn.getAttribute('data-id') || '');
    setVal('idr_mode', btn.getAttribute('data-mode') || '');
    setVal('idr_type', btn.getAttribute('data-type') || '');
    setVal('idr_ref', btn.getAttribute('data-ref') || '');
    setVal('idr_remarks', btn.getAttribute('data-remarks') || '');
    setVal('idr_voucher', btn.getAttribute('data-voucher') || '');
    setVal('idr_date', btn.getAttribute('data-date') || '');

    // Vendor (read-only select + hidden field)
    var vid = btn.getAttribute('data-vendor-id') || '';
    setVal('idr_vendor', vid);
    setVal('idr_vendor_hidden', vid);

    // Manual bank fields
    setVal('idr_acc', btn.getAttribute('data-acc') || '');
    setVal('idr_ifsc', btn.getAttribute('data-ifsc') || '');
    setVal('idr_bank', btn.getAttribute('data-bank') || '');
  }

  // ---------- Open modal across BS versions ----------
  function openModalBS5(){
    if (!window.bootstrap || !bootstrap.Modal) return false;
    var MCtor = bootstrap.Modal;
    var inst = null;
    try {
      if (typeof MCtor.getOrCreateInstance === 'function') {
        inst = MCtor.getOrCreateInstance(modalEl);
      } else if (typeof MCtor.getInstance === 'function') {
        inst = MCtor.getInstance(modalEl);
        if (!inst) inst = new MCtor(modalEl);
      } else {
        inst = new MCtor(modalEl);
      }
      inst.show();
      return true;
    } catch (e) { return false; }
  }

  function openModalBS4(){
    if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
      jQuery('#paymentModal').modal('show');
      return true;
    }
    return false;
  }

  function openModal(){
    if (openModalBS5()) return;
    if (openModalBS4()) return;
    // Fallback (no Bootstrap present)
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
  }

  function hookClose(){
    if (window.bootstrap && bootstrap.Modal) {
      modalEl.addEventListener('hidden.bs.modal', clearModal);
    } else if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
      jQuery('#paymentModal').on('hidden.bs.modal', clearModal);
    }
  }

  // Delegated click for Add Details / Edit buttons
  document.addEventListener('click', function(e){
    var t = e.target.closest('.js-idr-open');
    if (!t) return;
    prefillFromBtn(t);
    openModal();
  });

  hookClose();
})();
</script>
<?php page_footer_end(); ?>
