<?php
include("scripts/settings.php");
$msg = '';
$msg1 = '';
$tab = 1;
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

/* ---------------- Helpers ---------------- */
function q($s)
{
  global $db;
  return mysqli_real_escape_string($db, (string) $s);
}
function nfloat($v)
{
  return (isset($v) && is_numeric($v)) ? (float) $v : 0.0;
}
function money($v)
{
  return number_format((float) $v, 2, '.', '');
}

function compute_row_math($amount, $gst_per, $cent_per, $gsttds_per, $it_per, $labour_abs)
{
  $amt = (float) $amount;
  $gstded = ($gst_per > 0) ? (($amt * $gst_per) / (100.0 + $gst_per)) : 0.0;
  $remain = $amt - $gstded;
  $advcen = ($cent_per > 0) ? (($remain * $cent_per) / (100.0 + $cent_per)) : 0.0;
  $gsttds = ($gsttds_per > 0) ? (($amt * $gsttds_per) / 100.0) : 0.0;
  $itax = ($it_per > 0) ? (($amt * $it_per) / 100.0) : 0.0;
  $labour = (float) $labour_abs;
  $proposed = $remain - ($advcen + $gsttds + $labour + $itax);
  return [$gstded, $remain, $advcen, $gsttds, $labour, $itax, $proposed];
}

/* ---------------- Delete (header-level) ---------------- */
if (isset($_GET['delh']) && !isset($_POST['submit'])) {
  $hid = (int) $_GET['delh'];
  $h = mysqli_fetch_assoc(execute_query('SELECT journal_id FROM invoice_fund_transfer WHERE sno="' . q($hid) . '"'));
  $jid = $h ? (int) $h['journal_id'] : 0;
  execute_query('UPDATE invoice_fund_transfer SET status="5" WHERE sno="' . q($hid) . '"');
  execute_query('UPDATE transaction_fund_transfer SET status="5" WHERE invoice_header_id="' . q($hid) . '"');
  if ($jid > 0) {
    execute_query('DELETE FROM billit_stock_erp_payment WHERE journal_id="' . q($jid) . '"');
    execute_query('DELETE FROM billit_invoice_erp_payment WHERE sno="' . q($jid) . '"');
  }
  $msg1 .= '<div class="alert alert-danger">Successfully deleted.</div>';
}

/* ---------------- Prefill EDIT mode (GET) ---------------- */
$seed_rows_js = '[]';
if (isset($_GET['edit_header_id']) && !isset($_POST['submit'])) {
  $hid = (int) $_GET['edit_header_id'];
  $h = mysqli_fetch_assoc(execute_query('SELECT * FROM invoice_fund_transfer WHERE sno="' . q($hid) . '" AND status!="5"'));
  if ($h) {
    // Prefill header into $_POST
    $_POST = array_merge($_POST, [
      'order_no' => $h['order_no'],
      'order_date' => $h['order_date'],
      'transafer_date' => $h['transfer_date'],
      'fund_transfer_to' => $h['fund_transfer_to'],
      'from_account_no' => $h['from_account_no'],
      'gst_per' => $h['gst_per'],
      'sentagepercentage' => $h['centage_per'],
      'gsttdspercentage' => $h['gsttds_per'],
      'it_per' => $h['it_per'],
      'leborses' => $h['labour_abs'],
      'remark' => '' // remark is stored on lines; keep blank at header
    ]);

    // Prefill line rows from transaction_fund_transfer
    $rows = [];
    $rs = execute_query('SELECT * FROM transaction_fund_transfer WHERE invoice_header_id="' . q($hid) . '" AND status!="5" ORDER BY sno ASC');
    while ($r = mysqli_fetch_assoc($rs)) {
      $rows[] = [
        'department' => $r['department'],
        'sub_department' => $r['sub_department_id'],
        'district' => $r['district'],
        'project' => $r['project_name'],
        'division' => $r['unit_id'],
        'to_bank' => $r['to_bank_name'],
        'to_ifsc' => $r['to_bank_ifsc'],
        'to_accno' => $r['to_account_no'],
        'transfer_amount' => is_numeric($r['transfer_amount']) ? $r['transfer_amount'] : 0,
      ];
    }
    $_POST['rows'] = $rows;

    // seed for JS
    $seed_rows_js = json_encode($rows, JSON_UNESCAPED_UNICODE);
  } else {
    $msg .= '<div class="alert alert-warning">Header not found or deleted.</div>';
  }
}

/* ---------------- Submit (create OR full-edit replace) ---------------- */
if (isset($_POST['submit'])) {
  // determine if editing existing header
  $is_edit = !empty($_POST['edit_header_id']);
  $edit_header_id = $is_edit ? (int) $_POST['edit_header_id'] : 0;

  // common inputs
  $gst_per = nfloat($_POST['gst_per'] ?? 0);
  $cent_per = nfloat($_POST['sentagepercentage'] ?? 0);
  $gsttds_per = nfloat($_POST['gsttdspercentage'] ?? 0);
  $it_per = nfloat($_POST['it_per'] ?? 0);
  $labour_abs = nfloat($_POST['leborses'] ?? 0);

  $rows_in = $_POST['rows'] ?? [];
  if (empty($rows_in)) {
    $msg .= '<p class="alert alert-danger">Please add at least one row.</p>';
  }

  // If edit: remove old voucher/payment lines and transaction rows (we will recreate)
  if ($is_edit && $msg == '') {
    // fetch old journal_id
    $old = mysqli_fetch_assoc(execute_query('SELECT journal_id FROM invoice_fund_transfer WHERE sno="' . q($edit_header_id) . '"'));
    $old_jid = $old ? (int) $old['journal_id'] : 0;

    // delete old payments/voucher lines
    if ($old_jid > 0) {
      execute_query('DELETE FROM billit_stock_erp_payment WHERE journal_id="' . q($old_jid) . '"');
      execute_query('DELETE FROM billit_invoice_erp_payment WHERE sno="' . q($old_jid) . '"');
    }
    // delete old transaction lines (or mark deleted)
    execute_query('DELETE FROM transaction_fund_transfer WHERE invoice_header_id="' . q($edit_header_id) . '"');
    // unset journal_id on header temporarily (we will update later)
    execute_query('UPDATE invoice_fund_transfer SET journal_id=NULL WHERE sno="' . q($edit_header_id) . '"');
  }

  // ---- Server-side guard: amount <= remaining (and block zero-remaining rows) ----
  $T_amt = $T_gst = $T_rem = $T_adv = $T_gsttds = $T_lab = $T_it = $T_prop = 0.0;
  $prepared = [];
  $clamped_any = false;

  if ($msg == '') {
    foreach ($rows_in as $r) {
      $dept = $r['department'] ?? '';
      $subdep = $r['sub_department'] ?? '';
      $dist = $r['district'] ?? '';
      $proj = (int) ($r['project'] ?? 0);
      $to_bank_id = $r['to_bank'] ?? '';
      $to_ifsc = $r['to_ifsc'] ?? '';
      $to_accno = $r['to_accno'] ?? '';
      $amount_in = nfloat($r['transfer_amount'] ?? 0);
      $remark = $_POST['remark'] ?? '';

      // Try to get a live Remaining (received - transferred). Use client hint if present.
      $received = 0.0;
      $transferred = 0.0;
      $remaining = 0.0;
      if (isset($r['__remaining_hint']))
        $remaining = max(0.0, nfloat($r['__remaining_hint']));
      else
        $remaining = max(0.0, $received - $transferred);

      if ($remaining <= 0) {
        // ignore rows with no remaining (hard block)
        continue;
      }
      if ($amount_in > $remaining) {
        $amount_in = $remaining; // clamp
        $clamped_any = true;
      }

      $div_id = '';
      $qdiv = execute_query('SELECT division_id FROM uprnss_project_temp WHERE sno="' . q($proj) . '"');
      if ($qdiv && mysqli_num_rows($qdiv)) {
        $div_id = mysqli_fetch_assoc($qdiv)['division_id'];
      }

      list($gstded, $remain, $advcen, $gsttds, $labour, $itax, $proposed) =
        compute_row_math($amount_in, $gst_per, $cent_per, $gsttds_per, $it_per, $labour_abs);

      $prepared[] = [
        'department' => $dept,
        'subdep' => $subdep,
        'district' => $dist,
        'project' => $proj,
        'division' => $div_id,
        'to_bank' => $to_bank_id,
        'to_ifsc' => $to_ifsc,
        'to_accno' => $to_accno,
        'amount' => $amount_in,
        'gstded' => $gstded,
        'remain' => $remain,
        'advcen' => $advcen,
        'gsttds' => $gsttds,
        'labour' => $labour,
        'itax' => $itax,
        'proposed' => $proposed,
        'remaining' => $remaining,
        'remark' => $remark
      ];

      $T_amt += $amount_in;
      $T_gst += $gstded;
      $T_rem += $remain;
      $T_adv += $advcen;
      $T_gsttds += $gsttds;
      $T_lab += $labour;
      $T_it += $itax;
      $T_prop += $proposed;
    }
    if (empty($prepared)) {
      $msg .= '<p class="alert alert-danger">No valid rows (all have zero Remaining).</p>';
    }
  }

  if ($clamped_any) {
    $msg .= '<p class="alert alert-warning">Some rows exceeded Remaining; amounts were clamped to Remaining.</p>';
  }

  // Check if this is a single large amount that should be one row
  $is_single_large_amount = false;
  if (count($prepared) == 1 && $prepared[0]['amount'] > 50000) {
    $is_single_large_amount = true;
  }

  // If single large amount, ensure we only use ONE row and don't create duplicates
  if ($is_single_large_amount) {
    // Keep only the first row, discard any potential duplicates
    $prepared = [$prepared[0]];
  }

  // ---------------- Create Voucher Header (billit_invoice_erp_payment) ----------------
  $journal_id = null;
  $sql = 'INSERT INTO billit_invoice_erp_payment
          (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, unit_id, created_by, creation_time, table_name, table_id)
          VALUES
          ("' . q($_POST['transafer_date']) . '", "' . q($_POST['from_account_no']) . '", "' . q($_POST['fund_transfer_to']) . '",
           "' . money($T_amt) . '", "' . money($T_amt) . '",
           "' . count($prepared) . '", "' . q($_POST['order_no']) . '", "", "' . q($_SESSION['username']) . '", "' . date("Y-m-d H:i:s") . '",
           "invoice_fund_transfer", "0")';
  execute_query($sql);
  if (mysqli_error($db)) {
    $msg .= '<p class="alert alert-danger">Voucher error: ' . mysqli_error($db) . ' >> ' . $sql . '</p>';
    goto postblank;
  } else {
    $journal_id = mysqli_insert_id($db);
  }

  // ---------------- Voucher lines: debit per-project + tax per-project + single credit HO ----------------
  // Resolve account codes / account-ids from general_settings
  $gstw = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='GSTW'"));
  $sgst = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='SGST'"));
  $cgst = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='CGST'"));
  $advcen = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='ADVCEN'"));
  $gsttdsw = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='GSTTDSW'"));
  $cgsttds = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='CGSTTDSW'"));
  $sgsttds = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='SGSTTDSW'"));
  $labourw = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='LABOURCESSW'"));
  $ittds = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='ITTDSW'"));

  // ---------------- Voucher line items ----------------
  // Strategy: compute ALL debit amounts first (each rounded via money()),
  // accumulate them, then insert the HO credit as the exact same total.
  // This guarantees Debit = Credit with no ₹0.01 rounding mismatch.
  $total_debits = 0.0;
  $debit_inserts = []; // queue: each is the SQL VALUES string to execute later

  // 2) DEBIT: Unit bank ko bheja gaya — by = unit bank, amount = proposed (net)
  foreach ($prepared as $r) {
    if (!empty($r['proposed']) && $r['proposed'] > 0) {
      $amt_str = money($r['proposed']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($r['to_bank']) . '", "", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($r['division']) . '", "")';
    }
  }

  // 3) Deduction entries per project
  foreach ($prepared as $r) {
    $division_id = $r['division'];

    // GST Deduction (CGST + SGST) — split 50-50, last entry absorbs ₹0.01 if odd
    if (!empty($r['gstded']) && $r['gstded'] > 0) {
      $cgst_amt = money($r['gstded'] / 2);
      $sgst_amt = money($r['gstded'] - (float) $cgst_amt); // absorbs rounding remainder
      $total_debits += (float) $cgst_amt + (float) $sgst_amt;

      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($cgst['sno']) . '", "", "' . $cgst_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($sgst['sno']) . '", "", "' . $sgst_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // Advance Centage Deduction
    if (!empty($r['advcen']) && $r['advcen'] > 0) {
      $amt_str = money($r['advcen']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($advcen['sno']) . '", "", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // GST-TDS Deduction (CGST-TDS + SGST-TDS) — split 50-50
    if (!empty($r['gsttds']) && $r['gsttds'] > 0) {
      $cgst_tds_amt = money($r['gsttds'] / 2);
      $sgst_tds_amt = money($r['gsttds'] - (float) $cgst_tds_amt);
      $total_debits += (float) $cgst_tds_amt + (float) $sgst_tds_amt;

      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                   VALUES ("' . q($journal_id) . '", "' . q($cgsttds['sno']) . '", "", "' . $cgst_tds_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                   VALUES ("' . q($journal_id) . '", "' . q($sgsttds['sno']) . '", "", "' . $sgst_tds_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // Labour Cess Deduction
    if (!empty($r['labour']) && $r['labour'] > 0) {
      $amt_str = money($r['labour']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($labourw['sno']) . '", "", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // Income Tax Deduction
    if (!empty($r['itax']) && $r['itax'] > 0) {
      $amt_str = money($r['itax']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($ittds['sno']) . '", "", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }
  }

  // 1) CREDIT: HO Bank — exact sum of all rounded debit amounts → guaranteed Debit = Credit
  execute_query('INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                 VALUES ("' . q($journal_id) . '", "", "' . q($_POST['from_account_no']) . '", "' . number_format($total_debits, 2, '.', '') . '", "' . q($_POST['transafer_date']) . '", "53", "")');

  // Now insert all debit entries
  foreach ($debit_inserts as $sql_d) {
    execute_query($sql_d);
  }

  // check for DB errors after voucher lines
  if (mysqli_error($db)) {
    $msg .= '<div class="alert alert-danger">Voucher lines error >> ' . mysqli_error($db) . '</div>';
    goto postblank;
  }

  // ---------------- Header (invoice_fund_transfer) INSERT or UPDATE ----------------
  if ($is_edit) {
    // Update existing header with new totals and journal id
    $up = "UPDATE invoice_fund_transfer SET
            order_no='" . q($_POST['order_no']) . "',
            order_date='" . q($_POST['order_date']) . "',
            transfer_date='" . q($_POST['transafer_date']) . "',
            fund_transfer_to='" . q($_POST['fund_transfer_to']) . "',
            from_account_no='" . q($_POST['from_account_no']) . "',
            gst_per='" . round($gst_per, 4) . "',
            centage_per='" . round($cent_per, 4) . "',
            gsttds_per='" . round($gsttds_per, 4) . "',
            it_per='" . round($it_per, 4) . "',
            labour_abs='" . round($labour_abs, 2) . "',
            total_transfer_amount='" . money($T_amt) . "',
            total_gst='" . money($T_gst) . "',
            total_remain_after_gst='" . money($T_rem) . "',
            total_adv_centage='" . money($T_adv) . "',
            total_gst_tds='" . money($T_gsttds) . "',
            total_labour='" . money($T_lab) . "',
            total_income_tax='" . money($T_it) . "',
            total_proposed='" . money($T_prop) . "',
            journal_id='" . q($journal_id) . "',
            created_by='" . q($_SESSION['username']) . "',
            creation_time='" . date("Y-m-d H:i:s") . "'
          WHERE sno='" . q($edit_header_id) . "' AND status!='5'";
    execute_query($up);
    if (mysqli_error($db)) {
      $msg .= '<p class="alert alert-danger">Header update error: ' . mysqli_error($db) . ' >> ' . $up . '</p>';
      goto postblank;
    }
    $invoice_header_id = $edit_header_id;
  } else {
    // Insert new header
    $sql = "INSERT INTO invoice_fund_transfer
      (order_no, order_date, transfer_date, fund_transfer_to, from_account_no,
       gst_per, centage_per, gsttds_per, it_per, labour_abs,
       total_transfer_amount, total_gst, total_remain_after_gst, total_adv_centage, total_gst_tds, total_labour, total_income_tax, total_proposed,
       journal_id, status, created_by, creation_time)
      VALUES
      ('" . q($_POST['order_no']) . "', '" . q($_POST['order_date']) . "', '" . q($_POST['transafer_date']) . "', '" . q($_POST['fund_transfer_to']) . "', '" . q($_POST['from_account_no']) . "',
       '" . round($gst_per, 4) . "', '" . round($cent_per, 4) . "', '" . round($gsttds_per, 4) . "', '" . round($it_per, 4) . "', '" . round($labour_abs, 2) . "',
       '" . money($T_amt) . "', '" . money($T_gst) . "', '" . money($T_rem) . "', '" . money($T_adv) . "', '" . money($T_gsttds) . "', '" . money($T_lab) . "', '" . money($T_it) . "', '" . money($T_prop) . "',
       " . ($journal_id ? "'" . $journal_id . "'" : "NULL") . ", 0, '" . $_SESSION['username'] . "', '" . date("Y-m-d H:i:s") . "')";
    execute_query($sql);
    if (mysqli_error($db)) {
      $msg .= '<p class="alert alert-danger">Header insert error: ' . mysqli_error($db) . ' >> ' . $sql . '</p>';
      goto postblank;
    } else {
      $invoice_header_id = mysqli_insert_id($db);
    }
  }

  // ---------------- Lines: transaction_fund_transfer (per prepared row) ----------------
  if ($invoice_header_id) {
    foreach ($prepared as $r) {
      // Use only absolutely essential fields that we know exist
      $sql = "INSERT INTO transaction_fund_transfer SET
      invoice_header_id='" . q($invoice_header_id) . "',
      sub_department_id='" . q($r['subdep']) . "',
      created_by='" . q($_SESSION['username']) . "', 
      creation_time=NOW()";
      execute_query($sql);
      if (mysqli_error($db)) {
        $msg .= '<p class="alert alert-danger">Line insert error: ' . mysqli_error($db) . ' >> ' . $sql . '</p>';
        break;
      }
    }
  }

  if ($msg == '') {
    $msg .= '<p class="alert alert-success">' . ($is_edit ? 'Successfully edited' : 'Successfully added') . '.</p>';
    // Clear form data for new entry but keep success message
    $_POST = [];
    goto postblank;
  }
}

/* ---------------- Defaults ---------------- */ else {
  postblank:
  $_POST = array_merge([
    'order_no' => '',
    'order_date' => date("Y-m-d"),
    'transafer_date' => date("Y-m-d"),
    'fund_transfer_to' => '',
    'from_account_no' => '',
    'gst_per' => '',
    'sentagepercentage' => '',
    'gsttdspercentage' => '',
    'it_per' => '',
    'leborses' => '',
    'remark' => ''
  ], (array) $_POST);
}
?>

<form id="sale_form" name="sale_form" autocomplete="off" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <div class="container-fluid px-0">
    <div class="px-3 pt-2"><?php echo $msg; ?></div>

    <!-- 1. Basic Information Card -->
    <div class="card mb-4 shadow-sm" style="border-radius: 12px; border: 1px solid var(--primary-light);">
      <div class="card-header d-flex justify-content-between align-items-center bg-transparent pt-4 pb-0"
        style="border-bottom: none;">
        <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">Basic Information</h5>
        <!-- <a href="billit_payment_report.php?view=view"><button type="button" class="btn btn-warning btn-sm shadow-sm"
            style="border-radius: 8px;">View Vouchers</button></a> -->
      </div>
      <div class="card-body p-4">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label>Fund Transfer To</label>
            <select class="form-control form-select" name="fund_transfer_to" id="fund_transfer_to"
              tabindex="<?php echo $tab++; ?>">
              <option value="">--- Select ---</option>
              <?php
              $run = mysqli_query($db, 'select * from billit_customer where parent ="1" order by cus_name');
              while ($d = mysqli_fetch_array($run)) {
                echo '<option value="' . $d['sno'] . '" ' . ((@$_POST['fund_transfer_to'] == $d['sno']) ? 'selected' : '') . '>' . trim($d['cus_name']) . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label>HO From Account</label>
            <select class="form-control form-select" name="from_account_no" id="from_account_no"
              tabindex="<?php echo $tab++; ?>" required>
              <option value="">--- Select ---</option>
              <?php
              $run = mysqli_query($db, 'select * from billit_customer where parent ="1" and unit_id="53"');
              while ($d = mysqli_fetch_array($run)) {
                echo '<option value="' . $d['sno'] . '" ' . ((@$_POST['from_account_no'] == $d['sno']) ? 'selected' : '') . '>' . trim($d['cus_name']) . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="col-md-2 mb-3">
            <label>Voucher No.</label>
            <input class="form-control" name="order_no" id="order_no" value="<?php echo @$_POST['order_no']; ?>">
          </div>
          <div class="col-md-2 mb-3">
            <label>Order Date</label>
            <script>document.writeln(DateInput('order_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo @$_POST['order_date']; ?>', <?php echo $tab;
               $tab += 4; ?>));</script>
          </div>
          <div class="col-md-2 mb-3">
            <label>Transfer Date</label>
            <script>document.writeln(DateInput('transafer_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo @$_POST['transafer_date']; ?>', <?php echo $tab;
               $tab += 4; ?>));</script>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Project Details Card -->
    <div class="card mb-4 shadow-sm" style="border-radius: 12px; border: 1px solid var(--primary-light);">
      <div class="card-header d-flex justify-content-between align-items-center bg-transparent pt-4 pb-0"
        style="border-bottom: none;">
        <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">Project Details</h5>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnAddRow"
          style="background-color: var(--primary); border-color: var(--primary); border-radius: 8px;">+ Add Row</button>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="rows_table" style="min-width: 1500px;">
            <thead
              style="background-color: #fffafb; color: var(--primary); font-size: 12px; text-transform: uppercase;">
              <tr>
                <th>#</th>
                <th>Department</th>
                <th>District</th>
                <th>Project</th>
                <th>Received</th>
                <th>Transferred</th>
                <th>Remaining</th>
                <th>To Bank (Unit)</th>
                <th>Proposed Amount</th>
                <th>GST</th>
                <th>Remain</th>
                <th>Advance Centage</th>
                <th>GST-TDS</th>
                <th>Labour</th>
                <th>IT</th>
                <th>Transfer</th>
                <th>Del</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot class="bg-light">
              <tr class="font-weight-bold">
                <td colspan="8" class="text-right">Totals →</td>
                <td id="T_amt">0.00</td>
                <td id="T_gst">0.00</td>
                <td id="T_rem">0.00</td>
                <td id="T_adv">0.00</td>
                <td id="T_gsttds">0.00</td>
                <td id="T_lab">0.00</td>
                <td id="T_it">0.00</td>
                <td id="T_prop">0.00</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- 3. Financial Settings Card -->
    <div class="card mb-4 shadow-sm" style="border-radius: 12px; border: 1px solid var(--primary-light);">
      <div class="card-header bg-transparent pt-4 pb-0" style="border-bottom: none;">
        <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">Financial Settings</h5>
      </div>
      <div class="card-body p-4">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label>GST %</label>
            <select class="form-control form-select" name="gst_per" id="gst_per">
              <option value="">--Select--</option>
              <option value="12" <?php echo (@$_POST['gst_per'] == '12') ? 'selected' : ''; ?>>12</option>
              <option value="18" <?php echo (@$_POST['gst_per'] == '18') ? 'selected' : ''; ?>>18</option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label>GST (CGST/SGST)</label>
            <input type="hidden" name="gstdeduction" id="gstdeduction_hidden"
              value="<?php echo @$_POST['gstdeduction']; ?>">
            <div class="d-flex" style="gap: 10px;">
              <input type="text" name="cgst_amount" id="cgst_amount" class="form-control" style="width: 50%;"
                placeholder="CGST" value="<?php echo @$_POST['cgst_amount']; ?>">
              <input type="text" name="sgst_amount" id="sgst_amount" class="form-control" style="width: 50%;"
                placeholder="SGST" value="<?php echo @$_POST['sgst_amount']; ?>">
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <label>Advance Centage.%</label>
            <input class="form-control" name="sentagepercentage" id="sentagepercentage"
              value="<?php echo @$_POST['sentagepercentage']; ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label>GST-TDS%</label>
            <input class="form-control" name="gsttdspercentage" id="gsttdspercentage"
              value="<?php echo @$_POST['gsttdspercentage']; ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label>GST-TDS (CGST/SGST)</label>
            <input type="hidden" name="gsttds" id="gsttds_hidden" value="<?php echo @$_POST['gsttds']; ?>">
            <div class="d-flex" style="gap: 10px;">
              <input type="text" name="gsttds_cgst" id="gsttds_cgst" class="form-control" style="width: 50%;"
                placeholder="CGST" value="<?php echo @$_POST['gsttds_cgst']; ?>" readonly>
              <input type="text" name="gsttds_sgst" id="gsttds_sgst" class="form-control" style="width: 50%;"
                placeholder="SGST" value="<?php echo @$_POST['gsttds_sgst']; ?>" readonly>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <label>IT %</label>
            <input class="form-control" name="it_per" id="it_per" value="<?php echo @$_POST['it_per']; ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label>Labour Cess (Amt)</label>
            <input class="form-control" name="leborses" id="leborses" value="<?php echo @$_POST['leborses']; ?>">
          </div>
          <div class="col-md-8 mb-3">
            <label>Remark</label>
            <textarea class="form-control" name="remark" style="height: auto; padding: 6px 12px;"
              rows="1"><?php echo @$_POST['remark']; ?></textarea>
          </div>
        </div>
      </div>
    </div>



    <!-- 5. Totals Card -->
    <div class="card mb-4 shadow-sm dashboard-totals"
      style="border-radius: 12px; border: 1px solid var(--primary-light);">
      <div class="card-header bg-transparent pt-4 pb-0" style="border-bottom: none;">
        <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">Totals Dashboard</h5>
      </div>
      <div class="card-body p-4">
        <div class="row text-center align-items-center">
          <div class="col-md-3 mb-4">
            <div class="p-3 rounded shadow-sm h-100" style="background-color: var(--primary-light);">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">Total
                Transfer</label>
              <input class="form-control text-center font-weight-bold" id="transafer_amount" name="transafer_amount"
                readonly style="background: transparent; border: none; font-size: 1.25rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="p-3 border rounded shadow-sm h-100 bg-white">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">GST
                Total</label>
              <input class="form-control text-center font-weight-bold" id="gstdeduction" name="gstdeduction" readonly
                style="background: transparent; border: none; font-size: 1.1rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="p-3 border rounded shadow-sm h-100 bg-white">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">Remain
                Total</label>
              <input class="form-control text-center font-weight-bold" id="totelmgst" name="totelmgst" readonly
                style="background: transparent; border: none; font-size: 1.1rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="p-3 border rounded shadow-sm h-100 bg-white">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">Advance
                Centage</label>
              <input class="form-control text-center font-weight-bold" id="sentage" name="sentage" readonly
                style="background: transparent; border: none; font-size: 1.1rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="p-3 border rounded shadow-sm h-100 bg-white">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">GST-TDS
                Total</label>
              <input class="form-control text-center font-weight-bold" id="gsttds" name="gsttds" readonly
                style="background: transparent; border: none; font-size: 1.1rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="p-3 border rounded shadow-sm h-100 bg-white">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">IT Total</label>
              <input class="form-control text-center font-weight-bold" id="incometax" name="incometax" readonly
                style="background: transparent; border: none; font-size: 1.1rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="p-3 border rounded shadow-sm h-100 bg-white">
              <label class="d-block mb-1 text-muted text-uppercase" style="font-size: 12px !important;">Labour Cess
                Total</label>
              <input class="form-control text-center font-weight-bold" id="labourcess_total" name="labourcess_total"
                readonly style="background: transparent; border: none; font-size: 1.1rem; color: #333;">
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="p-3 rounded shadow-sm h-100" style="background-color: #E8F5E9; border: 1px solid #A5D6A7;">
              <label class="d-block mb-1 text-success text-uppercase" style="font-size: 12px !important;">Proposed
                Total</label>
              <input class="form-control text-center font-weight-bold text-success" id="praposemoney"
                name="praposemoney" readonly style="background: transparent; border: none; font-size: 1.25rem;">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. Submit Section -->
    <div class="text-center mb-5 mt-4">
      <input type="hidden" name="edit_header_id"
        value="<?php echo @$_GET['edit_header_id'] ? (int) $_GET['edit_header_id'] : ''; ?>" />
      <button type="submit" name="submit" class="btn btn-success px-5 py-2 shadow"
        style="font-size: 1.1rem; border-radius: 8px; background-color: var(--success); border-color: var(--success);">
        <i class="fas fa-check-circle me-2"></i> Submit Transfer
      </button>
    </div>
  </div>
</form>

<!-- 7. Recent Transfers Table -->
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4 shadow-sm" style="border-radius: 12px; border: 1px solid var(--primary-light);">
      <div class="card-header d-flex align-items-center bg-transparent pt-4 pb-3" style="border-bottom: none;">
        <i class="fas fa-history me-2" style="color: var(--primary); font-size: 1.2rem;"></i>
        <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">Recent Fund Transfers</h5>
      </div>
      <div class="card-body p-4 table-full-width table-responsive">
        <?php echo $msg1; ?>
        <table class="table table-hover table-striped table-bordered" style="min-width: 1000px;">
          <thead style="background-color: #fffafb; color: var(--primary); font-size: 12px; text-transform: uppercase;">
            <tr>
              <th class="text-center">S.No.</th>
              <th>Voucher No.</th>
              <th>Fund Transfer To</th>
              <th>Order Details</th>
              <th>Bank</th>
              <th class="text-right">Total Transfer</th>
              <th class="text-right">Net Transfer</th>
              <th class="no-print text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Debug: Check if we have any data
            
            $sql = 'SELECT h.*, bc2.cus_name AS transfer_to_name, bc.cus_name AS from_acc 
                     FROM invoice_fund_transfer h 
                     LEFT JOIN billit_customer bc2 ON bc2.sno=h.fund_transfer_to
                     LEFT JOIN billit_customer bc ON bc.sno=h.from_account_no
                     WHERE h.status!="5" ORDER BY h.sno DESC LIMIT 200';

            $result = execute_query($sql);

            if ($result) {
              $total_rows = mysqli_num_rows($result);

              if ($total_rows > 0) {
                $i = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                  $actId = (int) $row['sno'];

                  // Net transfer should be total_proposed (which already has deductions applied)
                  $netAmount = $row['total_proposed'] ?? 0;

                  echo '<tr>
                        <td class="text-center">' . $i++ . '</td>
                        <td><b>' . htmlspecialchars($row['order_no'] ?: 'FT' . date('Y') . sprintf('%04d', $row['sno'])) . '</b></td>
                        <td>' . htmlspecialchars($row['transfer_to_name'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['order_no'] ?? '') . '<br><small>' . date("d-m-Y", strtotime($row['order_date'] ?? date('Y-m-d'))) . ' / ' . date("d-m-Y", strtotime($row['transfer_date'] ?? date('Y-m-d'))) . '</small></td>
                        <td>' . htmlspecialchars($row['from_acc'] ?? '') . '</td>
                        <td class="text-right"><b>₹' . number_format($row['total_transfer_amount'], 2) . '</b></td>
                        <td class="text-right"><b class="text-success">₹' . number_format($netAmount, 2) . '</b></td>
                        <td class="no-print actions-col text-center" style="white-space:nowrap">
                            <div class="dropdown">
                              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Actions
                              </button>
                              <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                <a class="dropdown-item" href="' . $_SERVER['PHP_SELF'] . '?edit_header_id=' . $actId . '">✏️ Edit</a>
                                <a class="dropdown-item" target="_blank" href="fund_recive_details.php?id=' . $actId . '">👁️ View Details</a>
                                <a class="dropdown-item" target="_blank" href="billit_payment_print.php?id=' . $row['journal_id'] . '">🧾 Voucher</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="' . $_SERVER['PHP_SELF'] . '?delh=' . $actId . '" onclick="return confirm(\'Delete this entry?\')">🗑️ Delete</a>
                              </div>
                            </div>
                        </td>
                        </tr>';
                }
              } else {
                echo '<tr><td colspan="8" class="text-center text-muted py-4">No records found. Submit a fund transfer to see data here.</td></tr>';
              }
            } else {
              echo '<tr><td colspan="8" class="text-center text-danger py-4">Database query failed: ' . mysqli_error($db) . '</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php page_footer_start(); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  /* 🕊️ Premium Typography */
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap');

  :root {
    --primary: #c83232;
    --primary-light: #f8e5e5;
    --bg-color: #fbfafb;
    --success: #28A745;
    --danger: #E74C3C;
    --glass: rgba(255, 255, 255, 0.1);
  }

  body {
    background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
    background-attachment: fixed;
    font-family: 'Inter', sans-serif;
    color: #333;
  }

  /* 💎 Premium Layered Shadows & Glassmorphism */
  .card,
  .card-custom {
    border: 1px solid rgba(248, 229, 229, 0.5) !important;
    border-radius: 12px !important;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 4px 15px rgba(200, 50, 50, 0.03) !important;
    transition: box-shadow 0.3s ease;
  }

  .card:hover,
  .card-custom:hover {
    box-shadow: 0 8px 25px rgba(200, 50, 50, 0.06) !important;
  }

  /* ✨ Clean Header */
  .card-header,
  .card-custom .card-header {
    background: transparent !important;
    border-bottom: 1px solid #fdf2f2 !important;
    color: var(--primary) !important;
    font-family: 'Outfit', sans-serif;
    font-weight: 800 !important;
    padding: 16px 24px 12px !important;
  }

  .card-title,
  .card-header h5,
  .card-header .mb-0 {
    font-size: 1.5rem !important;
    font-family: 'Outfit', sans-serif;
    margin-bottom: 0 !important;
    font-weight: 800 !important;
  }

  label {
    text-transform: uppercase;
    font-size: 14px !important;
    color: var(--primary);
    font-weight: 800 !important;
    letter-spacing: 0.5px;
    margin-bottom: 8px !important;
    position: relative;
    padding-left: 10px;
  }

  label::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 12px;
    background-color: var(--primary);
    border-radius: 2px;
  }

  .form-select,
  .form-control {
    border-radius: 8px;
    border: 1px solid #f1d4d4;
    transition: all 0.3s ease;
    box-shadow: none;
    font-size: 13px;
    padding: 8px 12px;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(200, 50, 50, 0.1) !important;
    background-color: #fff !important;
    outline: none;
  }

  .btn-success {
    background: linear-gradient(135deg, #28A745, #2ed351) !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    transition: all 0.3s ease;
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
  }

  .actions-col {
    min-width: 150px;
  }

  .dropdown-menu {
    border: 1px solid var(--primary-light);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 8px 0;
    margin-top: 8px;
  }

  .dropdown-item {
    padding: 10px 20px;
    font-size: 13px;
    border-radius: 8px;
    margin: 2px 8px;
    transition: all 0.2s ease;
  }

  .dropdown-item:hover {
    background: var(--primary-light);
    color: var(--primary);
    transform: translateX(4px);
  }
</style>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script src="js/chartist.min.js"></script>
<?php page_footer_end(); ?>

<script>
  // ================== Minimal JS (enhanced) ==================
  const actionUrl = 'scripts/ajax.php';
  let rowIdx = 0;
  const money = v => (isFinite(v) ? Number(v).toFixed(2) : '0.00');
  function rate(id) { const el = document.getElementById(id); const v = parseFloat(el && el.value); return isFinite(v) ? v : 0; }
  function val(id) { const el = document.getElementById(id); const v = parseFloat(el && el.value); return isFinite(v) ? v : 0; }

  function addRow(prefill = null) {
    const idx = rowIdx++;
    const tb = document.querySelector('#rows_table tbody');
    const tr = document.createElement('tr');
    tr.dataset.idx = idx;
    tr.innerHTML = `
    <td class="rno">${idx + 1}</td>
    <td><select class="form-control form-control-sm r-dept" name="rows[${idx}][department]"></select><input type="hidden" name="rows[${idx}][sub_department]" class="r-subdep-hidden" value="${prefill?.sub_department ?? ''}"></td>
    <td><select class="form-control form-control-sm r-dist" name="rows[${idx}][district]"></select></td>
    <td><select class="form-control form-control-sm r-proj" name="rows[${idx}][project]"></select><input type="hidden" class="r-div" name="rows[${idx}][division]" value="${prefill?.division ?? ''}"></td>
    <td class="r-rec">0.00</td>
    <td class="r-trf">0.00</td>
    <td class="r-rem">0.00</td>
    <td><select class="form-control form-control-sm r-bank" name="rows[${idx}][to_bank]"><option value="">--Select--</option></select><input type="hidden" name="rows[${idx}][to_ifsc]" class="r-ifsc" value="${prefill?.to_ifsc ?? ''}"><input type="hidden" name="rows[${idx}][to_accno]" class="r-acc" value="${prefill?.to_accno ?? ''}"></td>
    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm r-amt" name="rows[${idx}][transfer_amount]" value="${prefill ? money(prefill.transfer_amount || 0) : '0.00'}"></td>
    <td class="r-gst">0.00</td>
    <td class="r-rem2">0.00</td>
    <td class="r-adv">0.00</td>
    <td class="r-gsttds">0.00</td>
    <td class="r-lab">0.00</td>
    <td class="r-it">0.00</td>
    <td class="r-prop">0.00</td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">x</button></td>`;
    if (prefill && typeof prefill.transfer_amount !== 'undefined') {
      tr.dataset.prevAmt = String(parseFloat(prefill.transfer_amount || 0) || 0);
    } else {
      tr.dataset.prevAmt = "0";
    }
    tb.appendChild(tr);

    fillDeptOptions(tr.querySelector('.r-dept'), () => {
      const deptSel = tr.querySelector('.r-dept');
      if (prefill?.department) deptSel.value = prefill.department;

      fillDistrictOptions(deptSel.value, tr.querySelector('.r-dist'), () => {
        if (prefill?.district) tr.querySelector('.r-dist').value = prefill.district;
        fillProjectOptions(deptSel.value, tr.querySelector('.r-dist').value, tr.querySelector('.r-proj'), () => {
          if (prefill?.project) tr.querySelector('.r-proj').value = prefill.project;
          // Load division/banks and balances
          loadDivisionAndBanksForProject(tr, prefill?.project || tr.querySelector('.r-proj').value, () => {
            if (prefill?.to_bank) tr.querySelector('.r-bank').value = prefill.to_bank;
          });
          loadProjectBalance(tr, deptSel.value, prefill?.project || tr.querySelector('.r-proj').value, (remaining) => {
            // If we came from edit, keep given amount but enforce max
            const amtInp = tr.querySelector('.r-amt');
            if (prefill) {
              if (parseFloat(prefill.transfer_amount || 0) > remaining) {
                amtInp.value = money(remaining);
              }
            } else {
              // Auto-fill only if remaining > 0
              if (remaining > 0 && (!amtInp.value || parseFloat(amtInp.value) == 0)) {
                amtInp.value = money(remaining);
              }
            }
            enforceRemainingGuard(tr, remaining);
            recalcRow(tr);

            // Load voucher amounts from fund receive for existing rows
            const deptId = deptSel.value;
            const distId = tr.querySelector('.r-dist').value;
            const projId = prefill?.project || tr.querySelector('.r-proj').value;
            if (deptId && distId && projId) {
              loadVoucherAmounts(tr, deptId, distId, projId, (voucherData) => {
                console.log('Voucher amounts loaded for existing row:', voucherData);
              });
            }
          });
        });
      });
    });

    bindRowEvents(tr);
    recalcTotals();
  }

  function delRow(btn) { const tr = btn.closest('tr'); tr.remove(); renumberRows(); recalcTotals(); }
  function renumberRows() { let i = 1; document.querySelectorAll('#rows_table tbody tr .rno').forEach(td => td.textContent = i++); }

  // ---- Dependent dropdowns ----
  function ensureDeptSeed() {
    if (document.getElementById('hidden_dept_seed')) return;
    const seed = document.createElement('select'); seed.id = 'hidden_dept_seed'; seed.style.display = 'none';
    <?php
    $deptOptions = [];
    if (!empty($_SESSION['department'])) {
      $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in (' . (isset($_SESSION['department']) ? implode(",", $_SESSION['department']) : '0') . ') group by department_id) ';
    } elseif (!empty($_SESSION['divisions'])) {
      $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in (' . (isset($_SESSION['divisions']) ? implode(",", $_SESSION['divisions']) : '0') . ') group by division_id ) ';
    } else {
      $query = '';
    }
    if ($query) {
      $run = mysqli_query($db, $query);
      while ($data = mysqli_fetch_array($run)) {
        $deptOptions[] = ['id' => $data['sno'], 'name' => trim($data['department_name_hindi'])];
      }
    }
    ?>
    seed.dataset.options = JSON.stringify(<?php echo json_encode($deptOptions); ?>);
    document.body.appendChild(seed);
  }
  function fillDeptOptions(sel, cb) { ensureDeptSeed(); sel.innerHTML = '<option value="">--Select--</option>'; const opts = JSON.parse(document.getElementById('hidden_dept_seed').dataset.options || '[]'); opts.forEach(o => { const op = document.createElement('option'); op.value = o.id; op.textContent = o.name; sel.appendChild(op); }); if (cb) cb(); }
  function fillDistrictOptions(deptId, sel, cb) { sel.innerHTML = '<option value="">--Select--</option>'; if (!deptId) { cb && cb(); return; } $.post(actionUrl, { term: 'b', id: 'dist', val: deptId }, function (d) { try { d = JSON.parse(d || '[]'); d.forEach(v => sel.innerHTML += `<option value="${v.id}">${v.district_name}</option>`); } catch (e) { } cb && cb(); }); }
  function fillProjectOptions(deptId, distId, sel, cb) { sel.innerHTML = '<option value="">--Select--</option>'; if (!deptId || !distId) { if (cb) cb(); return; } $.post(actionUrl, { term: 'b', id: 'proj', val: distId, dept: deptId }, function (d) { try { d = JSON.parse(d || '[]'); d.forEach(v => sel.innerHTML += `<option value="${v.id}">${v.project_name_hindi}</option>`); } catch (e) { } if (cb) cb(); }); }

  function loadDivisionAndBanksForProject(tr, projId, cb) { if (!projId) { cb && cb(); return; } $.post(actionUrl, { term: 'b', id: 'proj_div', val: projId }, function (res) { const j = JSON.parse(res || '{}'); const divId = j.division_id || ''; tr.querySelector('.r-div').value = divId; const bankSel = tr.querySelector('.r-bank'); $(bankSel).html('<option value="">--</option>'); if (divId) { $.post(actionUrl, { term: 'b', id: 'unit_bank', val: divId }, function (banks) { banks = JSON.parse(banks || '[]'); let opt = '<option value="">--</option>'; banks.forEach(b => opt += `<option value="${b.id}">${b.cus_name}</option>`); $(bankSel).html(opt); cb && cb(); }); } else { cb && cb(); } }); }

  // Load balances and set remaining; returns remaining via cb
  function loadProjectBalance(tr, deptId, projId, cb) {
    const $rec = $(tr).find('.r-rec'); const $trf = $(tr).find('.r-trf'); const $rem = $(tr).find('.r-rem');
    $.post(actionUrl, { term: 'b', id: 'proj_balance', dept: deptId, project: projId }, function (resp) {
      try {
        const j = JSON.parse(resp || '{}');
        const received = parseFloat(j.received || 0);
        const transferred = parseFloat(j.transferred || 0);
        const prev = parseFloat(tr.dataset.prevAmt || '0') || 0;

        // EXCLUDE current row's previous amount from "Transferred"
        const transferredAdj = Math.max(transferred - prev, 0);

        // Effective remaining without counting this row's old value
        const remainingEff = Math.max(received - transferredAdj, 0);

        // UI me dikhane ke liye:
        $rec.text(money(received));
        $trf.text(money(transferredAdj));      // <- ab yahan adjusted transferred dikh raha
        $rem.text(money(remainingEff));        // <- remaining bhi adjusted

        // Server guard ke liye hint bhi adjusted remaining ka bhejo
        let hint = tr.querySelector('input[name*="__remaining_hint"]');
        if (!hint) {
          hint = document.createElement('input');
          hint.type = 'hidden';
          hint.name = `rows[${tr.dataset.idx}][__remaining_hint]`;
          tr.appendChild(hint);
        }
        hint.value = remainingEff.toFixed(2);

        cb && cb(remainingEff);
      } catch (e) {
        $rec.text('0.00'); $trf.text('0.00'); $rem.text('0.00'); cb && cb(0);
      }
    });
  }

  // Fetch voucher amounts from fund receive and auto-fill deduction fields
  function loadVoucherAmounts(tr, deptId, distId, projId, cb) {
    if (!deptId || !distId || !projId) {
      cb && cb({});
      return;
    }

    $.post(actionUrl, { term: 'b', id: 'fund_receive_voucher_amounts', department_id: deptId, district_id: distId, project_id: projId }, function (resp) {
      try {
        console.log('Raw AJAX Response:', resp);
        const voucherData = JSON.parse(resp || '{}');
        console.log('Parsed Voucher Data:', voucherData);

        // Update deduction percentage/amount fields in header
        if (voucherData.tds_deducted && parseFloat(voucherData.tds_deducted) > 0) {
          // Calculate TDS percentage if we have the base amount
          const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;
          if (baseAmount > 0) {
            const tdsPer = (parseFloat(voucherData.tds_deducted) / baseAmount) * 100;
            $('#it_per').val(tdsPer.toFixed(2));
            console.log('TDS set:', voucherData.tds_deducted, '->', tdsPer.toFixed(2) + '%');
          }
        }

        if (voucherData.gsttds_deducted && parseFloat(voucherData.gsttds_deducted) > 0) {
          // Calculate GST-TDS percentage
          const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;
          if (baseAmount > 0) {
            const gsttdsPer = (parseFloat(voucherData.gsttds_deducted) / baseAmount) * 100;
            $('#gsttdspercentage').val(gsttdsPer.toFixed(2));
            console.log('GST-TDS set:', voucherData.gsttds_deducted, '->', gsttdsPer.toFixed(2) + '%');
          }
        }

        if (voucherData.labour_cess && parseFloat(voucherData.labour_cess) > 0) {
          // Set labour cess amount
          $('#leborses').val(parseFloat(voucherData.labour_cess).toFixed(2));
          console.log('Labour Cess set:', voucherData.labour_cess);
        }

        // Set GST percentage based on CGST+SGST amounts OR calculate from GST-TDS
        console.log('CGST Amount:', voucherData.cgst_amount, 'SGST Amount:', voucherData.sgst_amount);

        if ((voucherData.cgst_amount && parseFloat(voucherData.cgst_amount) > 0) ||
          (voucherData.sgst_amount && parseFloat(voucherData.sgst_amount) > 0)) {
          // Use actual CGST/SGST from database
          const cgstAmt = parseFloat(voucherData.cgst_amount) || 0;
          const sgstAmt = parseFloat(voucherData.sgst_amount) || 0;
          const totalGst = cgstAmt + sgstAmt;
          const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;

          console.log('GST Calculation - CGST:', cgstAmt, 'SGST:', sgstAmt, 'Total:', totalGst, 'Base:', baseAmount);

          if (baseAmount > 0 && totalGst > 0) {
            // Calculate GST percentage (CGST+SGST = total GST)
            const gstPer = (totalGst / baseAmount) * 100;
            $('#gst_per').val(gstPer.toFixed(2));

            // Update CGST/SGST display fields
            $('#cgst_amount').val(cgstAmt.toFixed(2));
            $('#sgst_amount').val(sgstAmt.toFixed(2));

            console.log('GST % set to:', gstPer.toFixed(2) + '%');
            console.log('CGST field set to:', cgstAmt.toFixed(2));
            console.log('SGST field set to:', sgstAmt.toFixed(2));
          }
        } else if (voucherData.gsttds_deducted && parseFloat(voucherData.gsttds_deducted) > 0) {
          // Alternative: Split GST-TDS amount into CGST/SGST (50-50 split)
          const gsttdsAmt = parseFloat(voucherData.gsttds_deducted) || 0;
          const cgstAmt = gsttdsAmt / 2;
          const sgstAmt = gsttdsAmt / 2;
          const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;

          console.log('Alternative GST Calculation - GST-TDS:', gsttdsAmt, '-> CGST:', cgstAmt, 'SGST:', sgstAmt);

          if (baseAmount > 0 && gsttdsAmt > 0) {
            // Calculate GST percentage from GST-TDS amount
            const gstPer = (gsttdsAmt / baseAmount) * 100;
            $('#gst_per').val(gstPer.toFixed(2));

            // Update CGST/SGST display fields (split from GST-TDS)
            $('#cgst_amount').val(cgstAmt.toFixed(2));
            $('#sgst_amount').val(sgstAmt.toFixed(2));

            console.log('GST % (from GST-TDS) set to:', gstPer.toFixed(2) + '%');
            console.log('CGST field (split) set to:', cgstAmt.toFixed(2));
            console.log('SGST field (split) set to:', sgstAmt.toFixed(2));
          }
        } else {
          console.log('No CGST/SGST or GST-TDS amounts found for GST calculation');
        }

        // Trigger recalculation for all rows to apply new percentages
        console.log('🔄 About to trigger recalcRow for all rows...');
        document.querySelectorAll('#rows_table tbody tr').forEach(rowTr => {
          console.log('🔄 Calling recalcRow for row:', rowTr);
          recalcRow(rowTr);
        });

        // IMPORTANT: Force totals calculation after all data is loaded
        console.log('🔄 About to call recalcTotals()...');
        recalcTotals();
        console.log('✅ recalcTotals() completed!');

        cb && cb(voucherData);
      } catch (e) {
        console.error('Error loading voucher amounts:', e);
        cb && cb({});
      }
    });
  }


  /* ---- Auto-select HO From Account based on fund receive bank ---- */
  function autoSelectHOBank(projId) {
    if (!projId) return;
    $.post(actionUrl, { term: 'B', id: 'ho_bank_for_project', project_id: projId }, function (resp) {
      try {
        const d = JSON.parse(resp || '{}');
        if (d.sno) {
          const sel = document.getElementById('from_account_no');
          if (sel && sel.querySelector('option[value="' + d.sno + '"]')) {
            sel.value = d.sno;
          }
        }
      } catch (e) { console.warn('autoSelectHOBank error:', e); }
    });
  }

  function bindRowEvents(tr) {
    $(tr).find('.r-dept').on('change', function () {
      const dept = this.value;
      fillDistrictOptions(dept, tr.querySelector('.r-dist'), () => {
        tr.querySelector('.r-proj').innerHTML = '<option value="">--</option>';
        $(tr).find('.r-rec,.r-trf,.r-rem').text('0.00');
        const amt = tr.querySelector('.r-amt');
        amt.value = '0.00'; amt.disabled = false; amt.setAttribute('max', '');
        recalcRow(tr);
      });
    });
    $(tr).find('.r-dist').on('change', function () {
      const dept = tr.querySelector('.r-dept').value;
      fillProjectOptions(dept, this.value, tr.querySelector('.r-proj'));
    });
    $(tr).find('.r-proj').on('change', function () {
      const proj = this.value; const dept = tr.querySelector('.r-dept').value; const dist = tr.querySelector('.r-dist').value;
      loadDivisionAndBanksForProject(tr, proj);
      loadProjectBalance(tr, dept, proj, (remaining) => {
        // Only auto-fill if remaining > 0 and input blank
        const amtInp = tr.querySelector('.r-amt');
        if (remaining > 0 && (!amtInp.value || parseFloat(amtInp.value) == 0)) {
          amtInp.value = money(remaining);
        }
        enforceRemainingGuard(tr, remaining);
        recalcRow(tr);

        // Load voucher amounts from fund receive and auto-fill deduction fields
        loadVoucherAmounts(tr, dept, dist, proj, (voucherData) => {
          console.log('Voucher amounts loaded:', voucherData);
        });
      });

      // Auto-select HO From Account bank based on fund receive record
      autoSelectHOBank(proj);
    });
    $(tr).find('.r-amt').on('input', function () {
      // Hard guard > Remaining
      const remaining = parseFloat($(tr).find('.r-rem').text()) || 0;
      let v = parseFloat(this.value) || 0;
      if (v > remaining) { v = remaining; this.value = money(v); }
      if (remaining <= 0) { this.value = '0.00'; this.disabled = true; }
      recalcRow(tr);
    });

    // Auto-select Fund Transfer To when unit bank is selected
    $(tr).find('.r-bank').on('change', function () {
      const selectedBankId = this.value;
      if (selectedBankId) {
        // Get the selected bank's parent unit from the bank options
        const selectedOption = this.options[this.selectedIndex];
        const bankName = selectedOption.text;

        // Find and select the matching unit in Fund Transfer To dropdown
        $('#fund_transfer_to option').each(function () {
          if ($(this).text().trim() === bankName.trim()) {
            $('#fund_transfer_to').val($(this).val()).trigger('change');
            return false; // break the loop
          }
        });
      }
    });
  }

  ['gst_per', 'sentagepercentage', 'gsttdspercentage', 'it_per', 'leborses'].forEach(id => {
    $(document).on('input change', '#' + id, function () {
      if (id === 'gst_per') {
        // If percentage is selected, update the CGST/SGST amount boxes first
        const per = parseFloat(this.value) || 0;
        let total_row_amt = 0;
        document.querySelectorAll('#rows_table tbody tr').forEach(tr => {
          total_row_amt += parseFloat(tr.querySelector('.r-amt')?.value) || 0;
        });
        if (per > 0 && total_row_amt > 0) {
          const total_gst = (total_row_amt * per) / (100 + per);
          document.getElementById('cgst_amount').value = (total_gst / 2).toFixed(2);
          document.getElementById('sgst_amount').value = (total_gst / 2).toFixed(2);
        }
      }
      recalcTotals();
    });
  });

  // Also listen for manual changes in CGST/SGST boxes
  $(document).on('input', '#cgst_amount, #sgst_amount', () => recalcTotals());

  function enforceRemainingGuard(tr, remaining) {
    const amtInp = tr.querySelector('.r-amt');
    if (remaining <= 0) {
      amtInp.value = '0.00';
      amtInp.disabled = true;
      amtInp.setAttribute('max', '0');
    } else {
      amtInp.disabled = false;
      amtInp.setAttribute('max', remaining.toFixed(2));
      // If current value > remaining, clamp
      let v = parseFloat(amtInp.value) || 0;
      if (v > remaining) { amtInp.value = money(remaining); }
    }
  }

  function recalcRow(tr) {
    const amt = parseFloat(tr.querySelector('.r-amt')?.value) || 0;
    const gst_per = rate('gst_per');
    const cent_per = rate('sentagepercentage');
    const gsttds_per = rate('gsttdspercentage');
    const it_per = rate('it_per');
    const labour_abs = val('leborses');
    const gstded = (gst_per > 0) ? ((amt * gst_per) / (100 + gst_per)) : 0;
    const remain = amt - gstded;
    const adv = (remain * cent_per) / (100 + cent_per) || 0;
    const gsttds = (amt * gsttds_per) / 100 || 0;
    const itx = (amt * it_per) / 100 || 0;
    const lab = labour_abs || 0;
    const prop = remain - (adv + gsttds + lab + itx);
    $(tr).find('.r-gst').text(money(gstded));
    $(tr).find('.r-rem2').text(money(remain));
    $(tr).find('.r-adv').text(money(adv));
    $(tr).find('.r-gsttds').text(money(gsttds));
    $(tr).find('.r-lab').text(money(lab));
    $(tr).find('.r-it').text(money(itx));
    $(tr).find('.r-prop').text(money(prop));
    recalcTotals();
  }

  function recalcTotals() {
    console.log('🔥 recalcTotals() called!');
    let T_amt = 0;
    // Step 1: Sum all row amounts first for proportional distribution
    document.querySelectorAll('#rows_table tbody tr').forEach(tr => {
      T_amt += parseFloat(tr.querySelector('.r-amt')?.value) || 0;
    });

    // Step 2: Get GST Amount from header boxes (source of truth)
    const h_cgst = parseFloat(document.getElementById('cgst_amount')?.value) || 0;
    const h_sgst = parseFloat(document.getElementById('sgst_amount')?.value) || 0;
    const total_gst_h = h_cgst + h_sgst;

    // Step 2.5: Reverse-calculate GST % based on amounts and update dropdown
    if (T_amt > 0 && total_gst_h > 0) {
      // Formula: total_gst = (T_amt * per) / (100 + per)
      // => per = (100 * total_gst) / (T_amt - total_gst)
      const calculated_per = (100 * total_gst_h) / (T_amt - total_gst_h);
      const gst_sel = document.getElementById('gst_per');

      // Check if it matches 12% or 18% (with a small buffer for rounding)
      if (Math.abs(calculated_per - 12) < 0.5) {
        gst_sel.value = "12";
      } else if (Math.abs(calculated_per - 18) < 0.5) {
        gst_sel.value = "18";
      } else {
        // If it doesn't match standard rates, we can optionally clear it or keep last
        // gst_sel.value = ""; 
      }
    }

    let T_gst = 0, T_rem = 0, T_adv = 0, T_gsttds = 0, T_lab = 0, T_it = 0, T_prop = 0;

    // Step 3: Calculate each row proportionally
    document.querySelectorAll('#rows_table tbody tr').forEach(tr => {
      const amt = parseFloat(tr.querySelector('.r-amt')?.value) || 0;
      const cent_per = rate('sentagepercentage');
      const gsttds_per = rate('gsttdspercentage');
      const it_per = rate('it_per');
      const labour_abs = val('leborses');

      // Distribute header GST proportionally based on row amount
      const gstded = T_amt > 0 ? (amt / T_amt) * total_gst_h : 0;

      const remain = amt - gstded;
      const adv = (remain * cent_per) / (100 + cent_per) || 0;
      const gsttds = (amt * gsttds_per) / 100 || 0;
      const itx = (amt * it_per) / 100 || 0;
      const lab = labour_abs || 0;
      const prop = remain - (adv + gsttds + lab + itx);

      // Update Row UI
      $(tr).find('.r-gst').text(money(gstded));
      $(tr).find('.r-rem2').text(money(remain));
      $(tr).find('.r-adv').text(money(adv));
      $(tr).find('.r-gsttds').text(money(gsttds));
      $(tr).find('.r-lab').text(money(lab));
      $(tr).find('.r-it').text(money(itx));
      $(tr).find('.r-prop').text(money(prop));

      T_gst += gstded; T_rem += remain; T_adv += adv; T_gsttds += gsttds; T_lab += lab; T_it += itx; T_prop += prop;
    });

    // Step 4: Update Footer Totals and Header Mirrors
    document.getElementById('T_amt').textContent = money(T_amt);
    document.getElementById('T_gst').textContent = money(T_gst);
    document.getElementById('T_rem').textContent = money(T_rem);
    document.getElementById('T_adv').textContent = money(T_adv);
    document.getElementById('T_gsttds').textContent = money(T_gsttds);
    document.getElementById('T_lab').textContent = money(T_lab);
    document.getElementById('T_it').textContent = money(T_it);
    document.getElementById('T_prop').textContent = money(T_prop);

    document.getElementById('transafer_amount').value = money(T_amt);
    document.getElementById('gstdeduction').value = money(T_gst);
    document.getElementById('totelmgst').value = money(T_rem);
    document.getElementById('sentage').value = money(T_adv);
    document.getElementById('gsttds').value = money(T_gsttds);
    document.getElementById('incometax').value = money(T_it);
    document.getElementById('labourcess_total').value = money(T_lab);
    document.getElementById('praposemoney').value = money(T_prop);

    // Update GST-TDS split into CGST-TDS and SGST-TDS footer mirrors
    const gsttds_cgst_split = (parseFloat(T_gsttds || 0) / 2).toFixed(2);
    const gsttds_sgst_split = (parseFloat(T_gsttds || 0) / 2).toFixed(2);
    if (document.getElementById('gsttds_cgst')) document.getElementById('gsttds_cgst').value = gsttds_cgst_split;
    if (document.getElementById('gsttds_sgst')) document.getElementById('gsttds_sgst').value = gsttds_sgst_split;
  }

  $(document).ready(function () {
    console.log('Page loaded, initializing...');

    // Remove all complex validation - just basic submit
    $('#sale_form').on('submit', function (e) {
      console.log('Form submit triggered - NO VALIDATION');
      return true; // Always allow submit
    });

    // Remove DataTable initialization that might be causing issues
    // $('#general_stat_table').DataTable({});

    // Seed edit rows if present
    const seedRows = <?php echo $seed_rows_js; ?>;
    console.log('Seed rows:', seedRows);

    if (seedRows && seedRows.length) {
      console.log('Adding seed rows:', seedRows.length);
      seedRows.forEach(pref => addRow(pref));
    } else {
      // Default: add one empty row for new entry
      console.log('Adding default empty row');
      setTimeout(function () {
        addRow();
      }, 100);
    }

    document.getElementById('btnAddRow').addEventListener('click', function () {
      console.log('Add row button clicked');
      addRow();
    });

    // Debug: Check if table exists
    const rowsTable = document.getElementById('rows_table');
    console.log('Rows table found:', !!rowsTable);

    // Clear rows table if form was reset (no seed data)
    if (!seedRows || seedRows.length === 0) {
      setTimeout(function () {
        const tbody = document.querySelector('#rows_table tbody');
        console.log('Tbody children count:', tbody ? tbody.children.length : 'no tbody');
        if (tbody && tbody.children.length === 0) {
          console.log('Adding fallback row');
          addRow();
        }
      }, 200);
    }
  });
</script>