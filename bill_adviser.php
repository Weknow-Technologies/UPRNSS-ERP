<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$msg = '';
$msg1 = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();

$module_name = "project_bill_adviser";
$preview_bills = [];
$total_received_amount = 0;
$total_bill_amount = 0;
$project_id = 0;
$past_approved_expense = 0;

/* ---------- Helpers ---------- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---------- Vendor config (adjust if your table/columns differ) ---------- */
$VENDOR_TABLE     = 'vendor';     // <- change if needed
$VENDOR_ID_COL    = 'sno';                // <- change to your PK (e.g., 'sno')
$VENDOR_NAME_COL  = 'firm_name';       // <- change to your name column (e.g., 'name')

/* Load vendors (used in modal dropdown) */
$vendors = [];
$vendor_sql = "SELECT {$VENDOR_ID_COL} AS vid, {$VENDOR_NAME_COL} AS vname FROM {$VENDOR_TABLE} ORDER BY {$VENDOR_NAME_COL}";
if ($vres = $db->query($vendor_sql)) {
    while($vr = $vres->fetch_assoc()){
        $vendors[] = ['id' => (int)$vr['vid'], 'name' => $vr['vname']];
    }
}

/* ---------- Handle modal payment update ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pba_payment_update'])) {
    $id       = (int)($_POST['id'] ?? 0);
    $mode     = trim($_POST['payment_mode'] ?? '');
    $type     = trim($_POST['type_of_payment'] ?? '');
    $ref      = trim($_POST['payment_ref'] ?? '');
    $remarks  = trim($_POST['payment_remarks'] ?? '');
    $voucher  = trim($_POST['voucher_no'] ?? '');
    $date_raw = trim($_POST['payment_date'] ?? '');
    // Vendor is read-only (disabled select) so we receive via hidden field:
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
        $chk = $db->prepare("SELECT status, vendor_id FROM project_bill_adviser WHERE id=? LIMIT 1");
        $chk->bind_param("i", $id);
        $chk->execute();
        $res = $chk->get_result();
        $row_s = $res ? $res->fetch_assoc() : null;

        if ($row_s && (int)$row_s['status'] === 1) {

            // We DO NOT override vendor_id (read-only) — but we can sanity-check mismatch:
            // if ($vendor_id_hidden && $vendor_id_hidden != (int)$row_s['vendor_id']) { /* ignore/mute */ }

            $stmt = $db->prepare("
                UPDATE project_bill_adviser
                   SET payment_mode        = ?,
                       type_of_payment     = ?,
                       payment_ref         = ?,
                       payment_remarks     = ?,
                       voucher_no          = ?,
                       payment_date        = ?,
                       payee_account_no    = ?,   -- NEW
                       payee_ifsc_code     = ?,   -- NEW
                       payee_bank_name     = ?,   -- NEW
                       payment_updated_by  = ?,
                       payment_updated_at  = NOW()
                 WHERE id = ?
                 LIMIT 1
            ");
            if ($stmt) {
                // 9 strings + 2 ints
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
                $msg = '<div class="alert alert-danger">DB prepare failed. Check columns in project_bill_adviser.</div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">Only approved records can be updated.</div>';
        }
    } else {
        $msg = '<div class="alert alert-warning">Mode and Type of Payment are required.</div>';
    }
}
?>
<div class="card">
    <div class="card-header text-center">
        <h4>Generated Bill Adviser</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($msg)) echo $msg; ?>

        <style>
          .pba-cell { min-width: 130px; }
          .pba-details { font-size: 12px; line-height: 1.35; display: grid; gap: 4px; }
          .pba-details .r { display: grid; grid-template-columns: 120px 1fr; align-items: baseline; }
          .pba-details .r > span { color: #6c757d; }
          .pba-details .r > b { font-weight: 600; word-break: break-word; }
          .align-top { vertical-align: top; }

          table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: linear-gradient(45deg, #e53935, #b71c1c);
            text-align: center;
            vertical-align: middle;
            font-size: 15px !important;
            white-space: nowrap;
            color: white !important;
            padding: 12px;
          }

          table tbody td {
            font-size: 13px !important;
            vertical-align: middle;
            padding: 10px;
          }

          /* Ensure sidebar is always above sticky headers */
          .sidebar {
            z-index: 1050 !important;
          }
        </style>
		<style>
  /* Wider modal */
  #paymentModal .modal-dialog {
    max-width: 960px; /* thoda aur chahie ho to 1040/1140px kar sakte ho */
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
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: auto;">
        <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th class="text-right" style="width: 20px !important;">Sr. No</th>
                    <th class="text-left">Project Name</th>
                    <th class="text-left">Vendor Name</th>
                    <!--<th>Bill SNOs</th>-->
                    <th class="text-right">Total Received</th>
                    <th class="text-right">Total Bill</th>
                    <th class="text-right">Total Net Payment</th>
                    <th class="text-right">Bill Count</th>
                    <th class="text-center">Created At</th>
                    <th class="text-center">Current With</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                    <th class="text-center no-print">Note Sheet</th>
                    <th class="text-center no-print">Advise</th>
                    <th class="text-center no-print">Payment Details</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $note_sheet_sql = "SELECT pns.*, upt.project_name_hindi 
                                     FROM project_bill_adviser pns 
                               JOIN uprnss_project_temp upt ON pns.project_id = upt.sno 
                                    WHERE 1=1";

                if (!empty($_SESSION['divisions']) && is_array($_SESSION['divisions'])) {
                    $divs = array_values(array_filter(array_map('intval', $_SESSION['divisions']), fn($v)=>$v>0));
                    if ($divs) {
                        $note_sheet_sql .= ' AND upt.division_id IN ('.implode(",", $divs).')';
                    }
                }
                $note_sheet_sql .= " ORDER BY pns.id DESC";

                $note_sheet_res = $db->query($note_sheet_sql);
                
                if ($note_sheet_res && $note_sheet_res->num_rows > 0) {
                    $d=1;
                    while ($row = $note_sheet_res->fetch_assoc()) {
                        $isApproved = ((int)$row['status'] === 1);
                        $isDone = (trim((string)$row['payment_mode']) !== '' || trim((string)$row['voucher_no']) !== '');

                        // derive friendly vendor name from loaded vendor list
                        $row_vendor_id = (int)($row['vendor_id'] ?? 0);
                        $row_vendor_name = '';
                        foreach($vendors as $v){
                            if ($v['id'] === $row_vendor_id){ $row_vendor_name = $v['name']; break; }
                        }

                        echo "<tr>";
                        echo "<td class='text-right'>".$d++."</td>";
                        echo "<td class='text-left'>".h($row['project_name_hindi'])."</td>";
                        echo "<td class='text-left'>".$row_vendor_name."</td>";
                        echo "<td class='text-right'>₹".number_format((float)$row['total_rcv_amt'], 2)."</td>";
                        echo "<td class='text-right'>₹".number_format((float)$row['total_transfer'], 2)."</td>";
                        echo "<td class='text-right'>₹".number_format((float)$row['total_net_pay'], 2)."</td>";
                        echo "<td class='text-right'>".h($row['bill_count'])."</td>";
                        echo "<td class='text-center'>".h($row['created_at'])."</td>";

                        // Current With + Status (helper prints two <td>)
                        echo getApprovalTrailRow($module_name, $row['id']);

                        // Note Sheet column
                        echo '<td class="no-print text-center">';
                        echo '<a target="_blank" href="bill_note_sheet.php?id='.h($row['notesheet_id']).'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Note sheet">Note sheet</span></a><br/><br/>';
                        echo '</td>';

                        // Advise column
                        if ($isApproved){
                            echo '<td class="no-print text-center">';
                            echo '<a target="_blank" href="paymnet_adviser.php?id='.h($row['id']).'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Advise">Advise</span></a><br/><br/>';
                            echo '</td>';
                        } else {
                            echo '<td class="no-print">Pending</td>';
                        }

                        // Payment Details column
                        echo '<td class="no-print align-top pba-cell">';

                        if ($isApproved) {
                            if ($isDone) {
                                // Details view
                                $mode = trim((string)$row['payment_mode']) !== '' ? h($row['payment_mode']) : '<span class="text-muted">—</span>';
                                $type = trim((string)$row['type_of_payment']) !== '' ? h($row['type_of_payment']) : '<span class="text-muted">—</span>';
                                $ref  = trim((string)$row['payment_ref'])   !== '' ? h($row['payment_ref'])   : '<span class="text-muted">—</span>';
                                $pdt  = trim((string)$row['payment_date'])  !== '' ? h($row['payment_date'])  : '<span class="text-muted">—</span>';
                                $rem  = trim((string)$row['payment_remarks']) !== '' ? h($row['payment_remarks']) : '<span class="text-muted">—</span>';
                                $acc  = trim((string)$row['payee_account_no']) !== '' ? h($row['payee_account_no']) : '<span class="text-muted">—</span>';
                                $ifsc = trim((string)$row['payee_ifsc_code'])  !== '' ? h($row['payee_ifsc_code'])  : '<span class="text-muted">—</span>';
                                $bank = trim((string)$row['payee_bank_name'])  !== '' ? h($row['payee_bank_name'])  : '<span class="text-muted">—</span>';
                                $vname = $row_vendor_name !== '' ? h($row_vendor_name) : '<span class="text-muted">—</span>';

                                echo '<div class="pba-details">';
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
                                echo '<button type="button" class="btn btn-sm btn-outline-secondary mt-1 js-pba-open"'
                                   .' data-id="'.(int)$row['id'].'"'
                                   .' data-mode="'.h($row['payment_mode']).'"'
                                   .' data-type="'.h($row['type_of_payment']).'"'
                                   .' data-ref="'.h($row['payment_ref']).'"'
                                   .' data-remarks="'.h($row['payment_remarks']).'"'
                                   .' data-voucher="'.h($row['voucher_no']).'"'
                                   .' data-date="'.h($row['payment_date']).'"'
                                   .' data-vendor-id="'.(int)$row_vendor_id.'"'
                                   .' data-acc="'.h($row['payee_account_no']).'"'
                                   .' data-ifsc="'.h($row['payee_ifsc_code']).'"'
                                   .' data-bank="'.h($row['payee_bank_name']).'"'
                                   .'>Edit</button>';

                            } else {
                                // Add details button
                                echo '<button type="button" class="btn btn-sm btn-outline-primary js-pba-open"'
                                   .' data-id="'.(int)$row['id'].'"'
                                   .' data-mode="'.h($row['payment_mode']).'"'
                                   .' data-type="'.h($row['type_of_payment']).'"'
                                   .' data-ref="'.h($row['payment_ref']).'"'
                                   .' data-remarks="'.h($row['payment_remarks']).'"'
                                   .' data-voucher="'.h($row['voucher_no']).'"'
                                   .' data-date="'.h($row['payment_date']).'"'
                                   .' data-vendor-id="'.(int)$row_vendor_id.'"'
                                   .' data-acc="'.h($row['payee_account_no']).'"'
                                   .' data-ifsc="'.h($row['payee_ifsc_code']).'"'
                                   .' data-bank="'.h($row['payee_bank_name']).'"'
                                   .'>Add Details</button>';
                            }
                        } else {
                            echo '—';
                        }
                        echo '</td>';

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='13'>No Note Sheets Generated</td></tr>";
                } ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Modal (re-usable for all rows) -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document"> <!-- modal-lg + centered -->
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
        <button type="button" class="close d-inline-flex align-items-center" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="border:0;background:none;font-size:1.6rem;line-height:1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
          <input type="hidden" name="pba_payment_update" value="1">
          <input type="hidden" name="id" id="pba_id" value="">
          <!-- read-only vendor: disabled select + hidden actual value -->
          <input type="hidden" name="vendor_id" id="pba_vendor_hidden" value="">

          <!-- Vendor ko full-row rehne diya (read-only) -->
          <div class="form-group">
            <label for="pba_vendor">Vendor</label>
            <select id="pba_vendor" class="form-control" disabled>
              <option value="">--Select Vendor--</option>
              <?php foreach($vendors as $v): ?>
                <option value="<?php echo (int)$v['id'];?>"><?php echo h($v['name']);?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 2 columns layout starts -->
          <div class="grid-2">
            <div class="form-group">
              <label for="pba_mode">Mode of Payment <span class="text-danger">*</span></label>
              <select name="payment_mode" id="pba_mode" class="form-control" required>
                <option value="">--Select--</option>
                <option value="Cheque">Cheque</option>
                <option value="Advice">Advice</option>
              </select>
            </div>

            <div class="form-group">
              <label for="pba_type">Type of Payment <span class="text-danger">*</span></label>
              <select name="type_of_payment" id="pba_type" class="form-control" required>
                <option value="">--Select--</option>
                <option value="RTGS">RTGS</option>
                <option value="NEFT">NEFT</option>
                <option value="Account Transfer">Account Transfer</option>
                <option value="Online Transfer">Online Transfer</option>
              </select>
            </div>

            <div class="form-group">
              <label for="pba_ref">Cheque/Advice/Ref Number</label>
              <input type="text" name="payment_ref" id="pba_ref" class="form-control" placeholder="Reference number">
            </div>

            <div class="form-group">
              <label for="pba_date">Cheque/Advice Date</label>
              <input type="date" name="payment_date" id="pba_date" class="form-control" placeholder="YYYY-MM-DD">
            </div>

            <div class="form-group">
              <label for="pba_acc">A/C No</label>
              <input type="text" name="payee_account_no" id="pba_acc" class="form-control" placeholder="Account Number">
            </div>

            <div class="form-group">
              <label for="pba_ifsc">IFSC Code</label>
              <input type="text" name="payee_ifsc_code" id="pba_ifsc" class="form-control" placeholder="IFSC Code">
            </div>

            <div class="form-group">
              <label for="pba_bank">Bank Name</label>
              <input type="text" name="payee_bank_name" id="pba_bank" class="form-control" placeholder="Bank Name">
            </div>

            <div class="form-group">
              <label for="pba_remarks">Remarks</label>
              <input type="text" name="payment_remarks" id="pba_remarks" class="form-control" placeholder="Optional remarks">
            </div>
          </div>
          <!-- 2 columns layout ends -->
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
    setVal('pba_id','');
    setVal('pba_vendor','');
    setVal('pba_vendor_hidden','');
    setVal('pba_mode','');
    setVal('pba_type','');
    setVal('pba_ref','');
    setVal('pba_remarks','');
    setVal('pba_voucher','');
    setVal('pba_date','');
    setVal('pba_acc','');
    setVal('pba_ifsc','');
    setVal('pba_bank','');
  }
  function prefillFromBtn(btn){
    setVal('pba_id', btn.getAttribute('data-id') || '');
    setVal('pba_mode', btn.getAttribute('data-mode') || '');
    setVal('pba_type', btn.getAttribute('data-type') || '');
    setVal('pba_ref', btn.getAttribute('data-ref') || '');
    setVal('pba_remarks', btn.getAttribute('data-remarks') || '');
    setVal('pba_voucher', btn.getAttribute('data-voucher') || '');
    setVal('pba_date', btn.getAttribute('data-date') || '');

    // Vendor (read-only select + hidden field)
    var vid = btn.getAttribute('data-vendor-id') || '';
    setVal('pba_vendor', vid);
    setVal('pba_vendor_hidden', vid);

    // Manual bank fields
    setVal('pba_acc', btn.getAttribute('data-acc') || '');
    setVal('pba_ifsc', btn.getAttribute('data-ifsc') || '');
    setVal('pba_bank', btn.getAttribute('data-bank') || '');
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
    var t = e.target.closest('.js-pba-open');
    if (!t) return;
    prefillFromBtn(t);
    openModal();
  });

  hookClose();
})();
</script>
<?php
page_footer_end();
