<?php
include("scripts/settings.php");
include("scripts/alerts.php");
include("scripts/billit_settings.php");
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

$auto_voucher_no = (!isset($_GET['edit_header_id']))
  ? generateVoucherNumber(
    'invoice_fund_transfer',
    'order_no',
    'FT'
  )
  : '';

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
  $msg1 .= 'Successfully deleted.';
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
    $msg .= 'Header not found or deleted.';
  }
}

/* ---------------- Submit (create OR full-edit replace) ---------------- */
if (isset($_POST['btn_submit'])) {
  // determine if editing existing header
  $is_edit = !empty($_POST['edit_header_id']);
  $edit_header_id = $is_edit ? (int) $_POST['edit_header_id'] : 0;
  // Auto-generate voucher number if empty (new entry only)
  if (!$is_edit && empty(trim($_POST['order_no'] ?? ''))) {
    $_POST['order_no'] = generateVoucherNumber(
      'invoice_fund_transfer',
      'order_no',
      'FT'
    );
  }

  // common inputs
  $gst_per = nfloat($_POST['gst_per'] ?? 0);
  $cent_per = nfloat($_POST['sentagepercentage'] ?? 0);
  $gsttds_per = nfloat($_POST['gsttdspercentage'] ?? 0);
  $it_per = nfloat($_POST['it_per'] ?? 0);
  $labour_abs = nfloat($_POST['leborses'] ?? 0);

  $rows_in = $_POST['rows'] ?? [];
  if (empty($rows_in)) {
    $msg .= 'Please add at least one row.';
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
      $msg .= 'No valid rows (all have zero Remaining).';
    }
  }

  if ($clamped_any) {
    $msg .= 'Some rows exceeded Remaining; amounts were clamped to Remaining.';
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
          (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, unit_id, created_by, creation_time, table_name, table_id, remarks)
          VALUES
          ("' . q($_POST['transafer_date']) . '", "' . q($_POST['from_account_no']) . '", "' . q($prepared[0]['to_bank'] ?? '') . '",
           "' . money($T_amt) . '", "' . money($T_amt) . '",
           "' . count($prepared) . '", "' . q($_POST['order_no']) . '", "", "' . q($_SESSION['username']) . '", "' . date("Y-m-d H:i:s") . '",
           "invoice_fund_transfer", "0", "' . q($_POST['remark'] ?? '') . '")';
  execute_query($sql);
  if (mysqli_error($db)) {
    $msg .= 'Voucher error: ' . mysqli_error($db) . ' >> ' . $sql;
    goto postblank;
  } else {
    $journal_id = mysqli_insert_id($db);
  }

  $total_debits = 0.0;
  $debit_inserts = [];
  foreach ($prepared as $r) {
    if (!empty($r['proposed']) && $r['proposed'] > 0) {
      $amt_str = money($r['proposed']);
      $total_debits += (float) $amt_str;
      // 'to' populated means DEBIT
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "", "' . q($r['to_bank']) . '", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($r['division']) . '", "")';
    }
  }

  function get_bill_setting_w($db, $desc, $unit_id)
  {
    $res = mysqli_query($db, "SELECT * FROM general_settings WHERE `desc`='$desc' AND unit_id='$unit_id'");
    if (mysqli_num_rows($res) > 0)
      return mysqli_fetch_assoc($res);
    $res = mysqli_query($db, "SELECT * FROM general_settings WHERE `desc`='$desc' AND unit_id='53'");
    return mysqli_fetch_assoc($res);
  }

  foreach ($prepared as $r) {
    $division_id = $r['division'];

    $gstw = get_bill_setting_w($db, 'GSTW', $division_id);
    $cgst = get_bill_setting_w($db, 'CGSTW', $division_id);
    $sgst = get_bill_setting_w($db, 'SGSTW', $division_id);
    $advcen = get_bill_setting_w($db, 'ADVCEN', $division_id);
    $gsttds_setting = get_bill_setting_w($db, 'GSTTDSW', $division_id);
    $cgsttds_setting = get_bill_setting_w($db, 'CGSTTDSW', $division_id);
    $sgsttds_setting = get_bill_setting_w($db, 'SGSTTDSW', $division_id);
    $labourw = get_bill_setting_w($db, 'LABOURCESSW', $division_id);
    $ittds = get_bill_setting_w($db, 'ITTDSW', $division_id);

    if (!empty($r['gstded']) && $r['gstded'] > 0) {
      $cgst_amt = money($r['gstded'] / 2);
      $sgst_amt = money($r['gstded'] - (float) $cgst_amt);
      $total_debits += (float) $cgst_amt + (float) $sgst_amt;

      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "", "' . q($cgst['rate'] ?? '') . '", "' . $cgst_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "", "' . q($sgst['rate'] ?? '') . '", "' . $sgst_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // Advance Centage Deduction
    if (!empty($r['advcen']) && $r['advcen'] > 0) {
      $amt_str = money($r['advcen']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "", "' . q($advcen['rate'] ?? '') . '", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // GST-TDS Deduction (CGST-TDS + SGST-TDS) — split 50-50
    if (!empty($r['gsttds']) && $r['gsttds'] > 0) {
      $cgst_tds_amt = money($r['gsttds'] / 2);
      $sgst_tds_amt = money($r['gsttds'] - (float) $cgst_tds_amt);
      $total_debits += (float) $cgst_tds_amt + (float) $sgst_tds_amt;

      $cgsttds_ledger = !empty($cgsttds_setting['rate']) ? $cgsttds_setting['rate'] : ($gsttds_setting['rate'] ?? '');
      $sgsttds_ledger = !empty($sgsttds_setting['rate']) ? $sgsttds_setting['rate'] : ($gsttds_setting['rate'] ?? '');

      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                   VALUES ("' . q($journal_id) . '", "", "' . q($cgsttds_ledger) . '", "' . $cgst_tds_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                   VALUES ("' . q($journal_id) . '", "", "' . q($sgsttds_ledger) . '", "' . $sgst_tds_amt . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // Labour Cess Deduction
    if (!empty($r['labour']) && $r['labour'] > 0) {
      $amt_str = money($r['labour']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "", "' . q($labourw['rate'] ?? '') . '", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }

    // Income Tax Deduction
    if (!empty($r['itax']) && $r['itax'] > 0) {
      $amt_str = money($r['itax']);
      $total_debits += (float) $amt_str;
      $debit_inserts[] = 'INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "", "' . q($ittds['rate'] ?? '') . '", "' . $amt_str . '", "' . q($_POST['transafer_date']) . '", "' . q($division_id) . '", "")';
    }
  }

  // 1) DEBIT: Project Ledger(s) — one entry per row with FULL transfer amount
  //    (includes net + CGST + SGST + all deductions = r['amount'])
  //    Each row's project sno → erp_code → billit_customer ledger id
  //    Falls back to from_account_no if no project ledger mapped.

  foreach ($prepared as $r) {
    if ($r['amount'] > 0) {
      // Lookup project ledger via erp_code
      $proj_ledger_id = '';
      if (!empty($r['project'])) {
        $proj_row = mysqli_fetch_assoc(execute_query(
          'SELECT erp_code FROM uprnss_project_temp WHERE sno="' . q($r['project']) . '" LIMIT 1'
        ));
        if (!empty($proj_row['erp_code'])) {
          $cust_row = mysqli_fetch_assoc(execute_query(
            'SELECT sno FROM billit_customer WHERE erp_code="' . q($proj_row['erp_code']) . '" LIMIT 1'
          ));
          if (!empty($cust_row['sno'])) {
            $proj_ledger_id = $cust_row['sno'];
          }
        }
      }
      // Fallback: use HO bank if no project ledger mapped
      if (empty($proj_ledger_id)) {
        $proj_ledger_id = $_POST['from_account_no'];
      }

      execute_query('INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                     VALUES ("' . q($journal_id) . '", "' . q($proj_ledger_id) . '", "", "' . money($r['remain']) . '", "' . q($_POST['transafer_date']) . '", "53", "")');

      // Debit 2: CGST
      if (!empty($r['gstded']) && $r['gstded'] > 0) {
        $cgst_d = money($r['gstded'] / 2);
        $sgst_d = money($r['gstded'] - (float) $cgst_d);
        $cgst_s = get_bill_setting_w($db, 'CGSTW', $r['division']);
        $sgst_s = get_bill_setting_w($db, 'SGSTW', $r['division']);
        execute_query('INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                       VALUES ("' . q($journal_id) . '", "' . q($cgst_s['rate'] ?? '') . '", "", "' . $cgst_d . '", "' . q($_POST['transafer_date']) . '", "53", "")');
        execute_query('INSERT INTO billit_stock_erp_payment (`journal_id`, `by`, `to`, amount, timestamp, unit_id, status)
                       VALUES ("' . q($journal_id) . '", "' . q($sgst_s['rate'] ?? '') . '", "", "' . $sgst_d . '", "' . q($_POST['transafer_date']) . '", "53", "")');
      }
    }
  }

  // Now insert all debit entries
  foreach ($debit_inserts as $sql_d) {
    execute_query($sql_d);
  }

  // check for DB errors after voucher lines
  if (mysqli_error($db)) {
    $msg .= 'Voucher lines error >> ' . mysqli_error($db);
    goto postblank;
  }

  // ---------------- Header (invoice_fund_transfer) INSERT or UPDATE ----------------
  if ($is_edit) {
    // Update existing header with new totals and journal id
    $up = "UPDATE invoice_fund_transfer SET
            order_no='" . q($_POST['order_no']) . "',
            order_date='" . q($_POST['order_date']) . "',
            transfer_date='" . q($_POST['transafer_date']) . "',
            fund_transfer_to='" . q($prepared[0]['to_bank'] ?? '') . "',
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
      $msg .= 'Header update error: ' . mysqli_error($db) . ' >> ' . $up;
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
      ('" . q($_POST['order_no']) . "', '" . q($_POST['order_date']) . "', '" . q($_POST['transafer_date']) . "', '" . q($prepared[0]['to_bank'] ?? '') . "', '" . q($_POST['from_account_no']) . "',
       '" . round($gst_per, 4) . "', '" . round($cent_per, 4) . "', '" . round($gsttds_per, 4) . "', '" . round($it_per, 4) . "', '" . round($labour_abs, 2) . "',
       '" . money($T_amt) . "', '" . money($T_gst) . "', '" . money($T_rem) . "', '" . money($T_adv) . "', '" . money($T_gsttds) . "', '" . money($T_lab) . "', '" . money($T_it) . "', '" . money($T_prop) . "',
       " . ($journal_id ? "'" . $journal_id . "'" : "NULL") . ", 0, '" . $_SESSION['username'] . "', '" . date("Y-m-d H:i:s") . "')";
    execute_query($sql);
    if (mysqli_error($db)) {
      $msg .= 'Header insert error: ' . mysqli_error($db) . ' >> ' . $sql;
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
      department='" . q($r['department']) . "',
      district='" . q($r['district']) . "',
      sub_department_id='" . q($r['subdep']) . "',
      project_name='" . q($r['project']) . "',
      transafer_amount='" . q($r['amount']) . "',
      sentage='" . q($r['advcen']) . "',
      gsttds='" . q($r['gsttds']) . "',
      leborses='" . q($r['labour']) . "',
      incometax='" . q($r['itax']) . "',
      created_by='" . q($_SESSION['username']) . "', 
      creation_time=NOW()";
      execute_query($sql);
      if (mysqli_error($db)) {
        $msg .= 'Line insert error: ' . mysqli_error($db) . ' >> ' . $sql;
        break;
      }
    }
  }

  if ($msg == '') {
    $msg .= ($is_edit ? 'Successfully edited' : 'Successfully added');
    $_POST = [];
    $_POST['order_no'] = generateVoucherNumber(
      'invoice_fund_transfer',
      'order_no',
      'FT'
    );
    $_POST['order_date'] = date('Y-m-d');
    $_POST['transafer_date'] = date('Y-m-d');
    goto postblank;
  }
}

/* ---------------- Defaults ---------------- */ else {
  postblank:
  $_POST = array_merge([
    'order_no' => '',
    'order_date' => date("Y-m-d"),
    'transafer_date' => date("Y-m-d"),
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
    <div class="px-3 pt-2">
      <?php
      if ($msg != '') {
        echo '<h5>' . alert($msg) . '</h5>';
      }
      ?>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body p-3">
        <!-- 1. Basic Information Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <div class="col-md-3 mb-3">
                <label><i class="fas fa-university me-1"></i> HO From Account</label>
                <select class="form-control form-select" name="from_account_no" id="from_account_no"
                  tabindex="<?php echo $tab++; ?>" required>
                  <option value="">--- Select ---</option>
                  <?php
                  $run = mysqli_query($db, 'select * from billit_customer where unit_id="53" and (parent ="1" or (account_no is not null and account_no != ""))');
                  while ($d = mysqli_fetch_array($run)) {
                    echo '<option value="' . $d['sno'] . '" ' . ((@$_POST['from_account_no'] == $d['sno']) ? 'selected' : '') . '>' . trim($d['cus_name']) . '</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-2 mb-3">
                <label><i class="fas fa-hashtag me-1"></i> Voucher No.</label>
                <input class="form-control" name="order_no" id="order_no" placeholder="FT-XXXX" readonly
                  value="<?php echo @$_POST['order_no'] ?: $auto_voucher_no; ?>">
              </div>
              <div class="col-md-2 mb-3">
                <label><i class="fas fa-calendar-alt me-1"></i> Order Date</label>
                <script>document.writeln(DateInput('order_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo @$_POST['order_date']; ?>', <?php echo $tab;
                   $tab += 4; ?>));</script>
              </div>
              <div class="col-md-2 mb-3">
                <label><i class="fas fa-calendar-check me-1"></i> Transfer Date</label>
                <script>document.writeln(DateInput('transafer_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo @$_POST['transafer_date']; ?>', <?php echo $tab;
                   $tab += 4; ?>));</script>
              </div>
            </div>
          </div>
        </div>
        <!-- 2. Project Details Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i> Project Details</h5>
            <button type="button" class="btn btn-primary btn-sm" id="btnAddRow">
              <i class="fas fa-plus me-1"></i> Add Row
            </button>
          </div>
          <div class="card-body p-0">
            <div class="table-scroll-container">
              <table class="table table-hover" id="rows_table" style="min-width: 1800px;">
                <thead>
                  <tr>
                    <th width="50">#</th>
                    <th width="200">Department</th>
                    <th width="150">District</th>
                    <th width="220">Project</th>
                    <th width="120" class="text-right">Received</th>
                    <th width="120" class="text-right">Transferred</th>
                    <th width="120" class="text-right">Remaining</th>
                    <th width="200">To Bank (Unit)</th>
                    <th width="150" class="text-right">Transfer Amt.</th>
                    <th width="100" class="text-right">GST</th>
                    <th width="100" class="text-right">Remain</th>
                    <th width="100" class="text-right">Adv. Cent.</th>
                    <th width="100" class="text-right">GST-TDS</th>
                    <th width="100" class="text-right">Labour</th>
                    <th width="100" class="text-right">IT</th>
                    <th width="150" class="text-right">Net</th>
                    <th width="50" class="text-center">#</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Rows added via JS -->
                </tbody>
                <tfoot class="bg-light">
                  <tr class="font-weight-bold">
                    <td colspan="8" class="text-right">Total Summary →</td>
                    <td id="T_amt" class="text-right text-primary">0.00</td>
                    <td id="T_gst" class="text-right">0.00</td>
                    <td id="T_rem" class="text-right">0.00</td>
                    <td id="T_adv" class="text-right">0.00</td>
                    <td id="T_gsttds" class="text-right">0.00</td>
                    <td id="T_lab" class="text-right">0.00</td>
                    <td id="T_it" class="text-right">0.00</td>
                    <td id="T_prop" class="text-right text-success" style="font-size: 1.2rem;">0.00</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
        <!-- 3. Financial Settings Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-coins"></i> Financial Calculation</h5>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <div class="col-md-3 mb-3">
                <label>GST %</label>
                <select class="form-control form-select" name="gst_per" id="gst_per">
                  <option value="">--Select--</option>
                  <option value="12" <?php echo (@$_POST['gst_per'] == '12') ? 'selected' : ''; ?>>12%</option>
                  <option value="18" <?php echo (@$_POST['gst_per'] == '18') ? 'selected' : ''; ?>>18%</option>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label>GST Breakdown (CGST/SGST)</label>
                <input type="hidden" name="gstdeduction" id="gstdeduction_hidden"
                  value="<?php echo @$_POST['gstdeduction']; ?>">
                <div class="input-group">
                  <input type="text" name="cgst_amount" id="cgst_amount" class="form-control" placeholder="CGST"
                    value="<?php echo @$_POST['cgst_amount']; ?>">
                  <div class="input-group-append"><span class="input-group-text">+</span></div>
                  <input type="text" name="sgst_amount" id="sgst_amount" class="form-control" placeholder="SGST"
                    value="<?php echo @$_POST['sgst_amount']; ?>">
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label>Adv. Centage %</label>
                <div class="input-group">
                  <input class="form-control" name="sentagepercentage" id="sentagepercentage"
                    value="<?php echo @$_POST['sentagepercentage']; ?>">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label>GST-TDS %</label>
                <div class="input-group">
                  <input class="form-control" name="gsttdspercentage" id="gsttdspercentage"
                    value="<?php echo @$_POST['gsttdspercentage']; ?>">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label>GST-TDS Breakdown</label>
                <input type="hidden" name="gsttds" id="gsttds_hidden" value="<?php echo @$_POST['gsttds']; ?>">
                <div class="input-group">
                  <input type="text" name="gsttds_cgst" id="gsttds_cgst" class="form-control" placeholder="CGST"
                    value="<?php echo @$_POST['gsttds_cgst']; ?>" readonly>
                  <input type="text" name="gsttds_sgst" id="gsttds_sgst" class="form-control" placeholder="SGST"
                    value="<?php echo @$_POST['gsttds_sgst']; ?>" readonly>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label>Income Tax %</label>
                <div class="input-group">
                  <input class="form-control" name="it_per" id="it_per" value="<?php echo @$_POST['it_per']; ?>">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label>Labour Cess (Amt)</label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                  <input class="form-control" name="leborses" id="leborses" value="<?php echo @$_POST['leborses']; ?>">
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label>Remark</label>
                <input class="form-control" name="remark" placeholder="Optional notes..."
                  value="<?php echo @$_POST['remark']; ?>">
              </div>
            </div>
          </div>
        </div>
        <!-- 4. Totals Dashboard -->
        <div class="dashboard-totals mb-4">
          <div class="row">
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-hand-holding-usd me-1"></i> Total Transfer</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="transafer_amount"
                  name="transafer_amount" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-file-invoice-dollar me-1"></i> GST Total</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="gstdeduction"
                  name="gstdeduction" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-calculator me-1"></i> Remain Total</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="totelmgst"
                  name="totelmgst" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-percentage me-1"></i> Adv. Centage</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="sentage"
                  name="sentage" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-shield-alt me-1"></i> GST-TDS Total</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="gsttds"
                  name="gsttds" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-user-shield me-1"></i> IT Total</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="incometax"
                  name="incometax" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card">
                <label><i class="fas fa-hard-hat me-1"></i> Labour Cess</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="labourcess_total"
                  name="labourcess_total" readonly>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="metric-card proposed-card">
                <label><i class="fas fa-check-double me-1"></i> Proposed Total</label>
                <input class="form-control metric-value border-0 p-0 bg-transparent shadow-none" id="praposemoney"
                  name="praposemoney" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. Submit Section -->
    <div class="row mt-5 mb-5">
      <div class="col-12 text-center">
        <input type="hidden" name="edit_header_id"
          value="<?php echo @$_GET['edit_header_id'] ? (int) $_GET['edit_header_id'] : ''; ?>" />
        <button type="submit" name="btn_submit" id="btn_submit" class="btn btn-success shadow-lg">
          <i class="fas fa-check-circle me-2"></i> Confirm & Process Transfer
        </button>
      </div>
    </div>
  </div>
</form>

<!-- 7. Recent Transfers Table -->
<div class="row">
  <div class="col-md-12">
    <div class="card shadow-sm border-0">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Fund Transfers</h5>
        <span class="badge-premium">Last 200 Records</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover" id="fundTransferTable">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Voucher Details</th>
                <th>Fund Transfer To</th>
                <th>Bank Account</th>
                <th class="text-right">Total Transfer</th>
                <th class="text-right">Net Amount</th>
                <th class="text-center">Confirmation</th>
                <th>Remark</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Debug: Check if we have any data
              
              $isAdmin = (isset($_SESSION['username']) && in_array(strtolower((string) $_SESSION['username']), ['sadmin', 'headacc']));
              $divFilter = "";
              if (!$isAdmin && !empty($_SESSION['divisions'])) {
                $divIds = implode(',', array_map('intval', $_SESSION['divisions']));
                $divFilter = " AND bc2.unit_id IN ($divIds)";
              }

              $sql = 'SELECT h.*, bc2.cus_name AS transfer_to_name, bc.cus_name AS from_acc 
                     FROM invoice_fund_transfer h 
                     LEFT JOIN billit_customer bc2 ON bc2.sno=h.fund_transfer_to
                     LEFT JOIN billit_customer bc ON bc.sno=h.from_account_no
                     WHERE h.status!="5" ' . $divFilter . ' ORDER BY h.sno ASC LIMIT 200';

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
                        <td>
                          <div class="font-weight-bold text-dark">' . htmlspecialchars($row['order_no'] ?: 'FT-' . $row['sno']) . '</div>
                          <div class="text-muted small"><i class="far fa-calendar-alt me-1"></i>' . date("d-m-Y", strtotime($row['transfer_date'])) . '</div>
                        </td>
                        <td>
                          <div class="font-weight-600">' . htmlspecialchars($row['transfer_to_name'] ?? '') . '</div>
                        </td>
                        <td>
                          <div class="text-muted small">' . htmlspecialchars($row['from_acc'] ?? '') . '</div>
                        </td>
                        <td class="text-right font-weight-bold">₹' . number_format($row['total_transfer_amount'], 2) . '</td>
                        <td class="text-right">
                          <span class="badge badge-success px-3 py-2" style="border-radius: 6px; font-size: 0.9rem;">₹' . number_format($netAmount, 2) . '</span>
                        </td>
                        <td class="text-center">
                          ';
                    $conf_status = $row['unit_confirmation_status'];
                    if ($conf_status == 1) {
                      echo '<span class="badge badge-success" title="' . htmlspecialchars($row['unit_confirmation_remark']) . '">Accepted</span>';
                    } elseif ($conf_status == 2) {
                      echo '<span class="badge badge-danger" title="' . htmlspecialchars($row['unit_confirmation_remark']) . '">Rejected</span>';
                    } else {
                      echo '<span class="badge badge-secondary">Pending</span>';
                    }
                    echo '
                        </td>
                        <td>' . htmlspecialchars($row['unit_confirmation_remark'] ?? '') . '</td>
                        <td class="text-center">
                            <div class="dropdown">
                              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                Options
                              </button>
                              <div class="dropdown-menu dropdown-menu-right shadow-lg">
                                <a class="dropdown-item" href="' . $_SERVER['PHP_SELF'] . '?edit_header_id=' . $actId . '"><i class="fas fa-edit me-2"></i> Edit</a>
                                <a class="dropdown-item" target="_blank" href="fund_transfer_view_details.php?id=' . $actId . '"><i class="fas fa-eye me-2"></i> View Details</a>
                                <a class="dropdown-item" target="_blank" href="billit_payment_print.php?id=' . $row['journal_id'] . '"><i class="fas fa-file-invoice me-2"></i> Voucher</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="' . $_SERVER['PHP_SELF'] . '?delh=' . $actId . '" onclick="return confirm(\'Delete this entry?\')"><i class="fas fa-trash-alt me-2"></i> Delete</a>
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
      --primary-dark: #a52828;
      --primary-light: #fdf2f2;
      --primary-gradient: linear-gradient(135deg, #c83232 0%, #a52828 100%);
      --bg-color: #f8fafc;
      --card-bg: rgba(255, 255, 255, 0.95);
      --text-main: #2c3e50;
      --text-muted: #64748b;
      --success: #10b981;
      --info: #3b82f6;
      --border-color: #e2e8f0;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
      background-color: var(--bg-color);
      font-family: 'Inter', sans-serif;
      color: var(--text-main);
      overflow-x: hidden;
    }

    /* 💎 Premium Cards & Glassmorphism */
    .card {
      border: none !important;
      border-radius: 16px !important;
      background: var(--card-bg) !important;
      backdrop-filter: blur(8px);
      box-shadow: var(--shadow-md) !important;
      margin-bottom: 2rem !important;
      overflow: hidden;
    }

    .card:hover {
      box-shadow: var(--shadow-lg) !important;
    }

    .card-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
      border-bottom: none !important;
      padding: 1rem 1.5rem !important;
      border-radius: 16px 16px 0 0 !important;
    }

    .card-header h5 {
      font-family: 'Outfit', sans-serif;
      font-weight: 700 !important;
      color: #fff !important;
      font-size: 1.1rem !important;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      letter-spacing: 0.3px;
    }

    .card-header h5 i {
      font-size: 1rem;
      opacity: 0.85;
    }

    .card-header .btn-primary {
      background: rgba(255, 255, 255, 0.2) !important;
      border: 1px solid rgba(255, 255, 255, 0.4) !important;
      color: #fff !important;
      box-shadow: none !important;
    }

    .card-header .btn-primary:hover {
      background: rgba(255, 255, 255, 0.35) !important;
      transform: none !important;
    }

    .card-header .badge-premium {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 20px;
      padding: 4px 12px;
      font-size: 0.78rem;
      font-weight: 600;
    }

    /* ✨ Form Elements */
    label {
      font-family: 'Outfit', sans-serif;
      text-transform: uppercase;
      font-size: 1rem !important;
      font-weight: 700 !important;
      color: var(--text-muted);
      letter-spacing: 0.05em;
      margin-bottom: 0.5rem !important;
      display: block;
    }

    .form-control,
    .form-select,
    .select2-container--bootstrap4 .select2-selection {
      border: 1px solid var(--border-color) !important;
      border-radius: 10px !important;
      font-size: 1rem !important;
      transition: all 0.2s ease;
      background-color: #fff !important;
      height: auto !important;
    }

    .form-control:focus,
    .form-select:focus,
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
      border-color: var(--primary) !important;
      box-shadow: 0 0 0 4px rgba(200, 50, 50, 0.1) !important;
      outline: none !important;
    }

    /* 📊 Totals Dashboard */
    .dashboard-totals .metric-card {
      background: #fff;
      padding: 1.25rem;
      border-radius: 12px;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }

    .dashboard-totals .metric-card:hover {
      border-color: var(--primary);
      background: var(--primary-light);
    }

    .dashboard-totals label {
      margin-bottom: 0.25rem !important;
      color: var(--text-muted);
    }

    .dashboard-totals .metric-value {
      font-family: 'Outfit', sans-serif;
      font-weight: 800;
      font-size: 1.6rem;
      color: var(--text-main);
    }

    .proposed-card {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
      color: #fff !important;
      border: none !important;
    }

    .proposed-card label {
      color: rgba(255, 255, 255, 0.8) !important;
    }

    /* 📋 Tables */
    .table {
      margin-bottom: 0;
    }

    .table-scroll-container {
      overflow-x: auto;
      border-radius: 8px;
      margin-bottom: 1rem;
      scrollbar-width: thin;
      scrollbar-color: var(--primary-light) transparent;
    }

    .table-scroll-container::-webkit-scrollbar {
      height: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-track {
      background: transparent;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
      background-color: var(--primary-light);
      border-radius: 10px;
    }

    .table thead th {
      background: #f8fafc !important;
      border-bottom: 2px solid var(--border-color) !important;
      color: var(--text-muted) !important;
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.9rem !important;
      letter-spacing: 0.05em;
      padding: 1.25rem 1rem !important;
      white-space: nowrap;
    }

    .table tbody td {
      padding: 1rem 1.25rem !important;
      vertical-align: middle !important;
      border-color: var(--border-color) !important;
      font-size: 1rem;
    }

    .table tfoot td {
      padding: 1.25rem 1rem !important;
      vertical-align: middle !important;
      border-top: 2px solid var(--primary-light) !important;
      font-size: 1rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .table .form-control {
      font-size: 0.95rem !important;
      padding: 0.5rem 0.75rem !important;
    }

    .table-hover tbody tr:hover {
      background-color: var(--primary-light) !important;
    }

    /* 🚀 Functional Classes */
    .btn-primary {
      background: var(--primary-gradient) !important;
      border: none !important;
      border-radius: 10px !important;
      padding: 0.6rem 1.5rem !important;
      font-weight: 600 !important;
      box-shadow: 0 4px 12px rgba(200, 50, 50, 0.2);
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      box-shadow: 0 6px 16px rgba(200, 50, 50, 0.3);
    }

    .btn-success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
      border: none !important;
      border-radius: 10px !important;
      padding: 0.75rem 2.5rem !important;
      font-weight: 700 !important;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .btn-success:hover {
      box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }

    .badge-premium {
      background: var(--primary-light);
      color: var(--primary);
      padding: 0.5rem 1rem;
      border-radius: 30px;
      font-weight: 700;
      font-size: 0.7rem;
      text-transform: uppercase;
    }

    /* 📱 Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* Action Buttons in Table */
    .btn-table-action {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .btn-delete-row {
      color: #ef4444;
      background: #fef2f2;
      border: 1px solid #fee2e2;
    }

    .btn-delete-row:hover {
      background: #ef4444;
      color: #fff;
    }

    /* 🔍 Select2 Premium Styling */
    .select2-container--bootstrap4 .select2-dropdown {
      border: 1px solid var(--border-color) !important;
      border-radius: 12px !important;
      box-shadow: var(--shadow-lg) !important;
      overflow: hidden;
      z-index: 9999;
    }

    .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
      border: 1px solid var(--border-color) !important;
      border-radius: 8px !important;
      padding: 10px 14px !important;
      margin: 8px !important;
      width: calc(100% - 16px) !important;
      font-size: 1rem !important;
    }

    .select2-container--bootstrap4 .select2-results__option {
      padding: 10px 18px !important;
      font-size: 1rem !important;
      transition: all 0.2s ease;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
      background-color: var(--primary-light) !important;
      color: var(--primary) !important;
    }

    .select2-container--bootstrap4 .select2-selection--single {
      height: auto !important;
      padding: 0.5rem 1rem !important;
    }

    .select2-container--bootstrap4 .select2-selection__rendered {
      line-height: inherit !important;
      padding-left: 0 !important;
      color: var(--text-main) !important;
    }

    /* ── DataTable Wrapper Fix ── */
    .dataTables_wrapper {
      padding: 16px 20px !important;
    }

    .dataTables_wrapper .dataTables_length {
      float: none !important;
      display: inline-block;
    }

    .dataTables_wrapper .dataTables_filter {
      float: none !important;
      display: inline-block;
    }

    /* Show entries + Buttons row */
    .dataTables_wrapper .d-flex {
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      margin-bottom: 12px !important;
      padding: 0 4px;
    }

    .dataTables_wrapper .dataTables_length label {
      font-family: 'Inter', sans-serif !important;
      font-size: 0.85rem !important;
      font-weight: 500 !important;
      color: var(--text-muted) !important;
      text-transform: none !important;
      letter-spacing: 0 !important;
      margin: 0 !important;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .dataTables_wrapper .dataTables_length select {
      border: 1.5px solid var(--border-color) !important;
      border-radius: 8px !important;
      padding: 4px 8px !important;
      font-size: 0.85rem !important;
      height: auto !important;
      width: auto !important;
      display: inline-block;
    }

    /* Search bar */
    .dataTables_wrapper .dataTables_filter {
      margin-left: auto !important;
    }

    .dataTables_wrapper .dataTables_filter label {
      font-family: 'Inter', sans-serif !important;
      font-size: 0.85rem !important;
      font-weight: 500 !important;
      color: var(--text-muted) !important;
      text-transform: none !important;
      letter-spacing: 0 !important;
      margin: 0 !important;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .dataTables_wrapper .dataTables_filter input {
      border: 1.5px solid var(--border-color) !important;
      border-radius: 8px !important;
      padding: 6px 12px !important;
      font-size: 0.85rem !important;
      width: 200px !important;
      height: auto !important;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: var(--primary) !important;
      box-shadow: 0 0 0 3px rgba(200, 50, 50, 0.1) !important;
      outline: none !important;
    }

    /* Info text */
    .dataTables_wrapper .dataTables_info {
      font-size: 0.82rem !important;
      color: var(--text-muted) !important;
      padding: 8px 0 4px !important;
      font-family: 'Inter', sans-serif !important;
      font-weight: 400 !important;
    }

    /* Pagination */
    .dataTables_wrapper .dataTables_paginate {
      padding: 8px 0 4px !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border-radius: 8px !important;
      padding: 4px 10px !important;
      font-size: 0.82rem !important;
      font-weight: 500 !important;
      border: 1px solid var(--border-color) !important;
      margin: 0 2px !important;
      color: var(--text-main) !important;
      background: #fff !important;
      transition: all 0.2s ease !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: var(--primary-light) !important;
      border-color: var(--primary) !important;
      color: var(--primary) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: var(--primary-gradient) !important;
      border-color: var(--primary) !important;
      color: #fff !important;
      font-weight: 700 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
      opacity: 0.4 !important;
      cursor: not-allowed !important;
      background: #fff !important;
      color: var(--text-muted) !important;
    }

    /* Hide column filter X icons */
    .dataTables_wrapper thead .dt-column-order,
    table.dataTable thead .sorting::after,
    table.dataTable thead .sorting_asc::after,
    table.dataTable thead .sorting_desc::after,
    table.dataTable thead .sorting::before,
    table.dataTable thead .sorting_asc::before,
    table.dataTable thead .sorting_desc::before {
      opacity: 0.3 !important;
      font-size: 0.65rem !important;
    }

    /* Export Buttons */
    .dt-buttons {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }

    .dt-buttons .btn {
      border-radius: 8px !important;
      font-size: 0.8rem !important;
      font-weight: 600 !important;
      padding: 6px 14px !important;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      box-shadow: none !important;
      transition: all 0.2s ease !important;
    }

    .dt-buttons .btn:hover {
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15) !important;
    }

    /* Info + Pagination row alignment */
    .dataTables_wrapper .row:last-child {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 4px 4px 0;
    }

    /* fundTransferTable thead — sort icons fix */
    #fundTransferTable thead th {
      background: #e8f3ff !important;
      color: #090a0a !important;
      position: relative !important;
      padding-right: 28px !important;
    }

    /* dt-column-order span hide karo */
    #fundTransferTable thead th span.dt-column-order {
      display: none !important;
    }

    /* FontAwesome se custom sort arrows */
    #fundTransferTable thead th.sorting::after,
    #fundTransferTable thead th.sorting_asc::after,
    #fundTransferTable thead th.sorting_desc::after {
      font-family: "Font Awesome 6 Free" !important;
      font-weight: 900 !important;
      position: absolute !important;
      right: 8px !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
      font-size: 11px !important;
    }

    #fundTransferTable thead th.sorting::after {
      content: "\f0dc" !important;
      color: #000000 !important;
    }

    #fundTransferTable thead th.sorting_asc::after {
      content: "\f0de" !important;
      color: #c83232 !important;
    }

    #fundTransferTable thead th.sorting_desc::after {
      content: "\f0dd" !important;
      color: #c83232 !important;
    }

    #fundTransferTable thead th:last-child::after {
      display: none !important;
    }

    #fundTransferTable thead th,
    #fundTransferTable tbody td {
      border-right: 1px solid #e2e8f0 !important;
      border-left: 1px solid #e2e8f0 !important;
    }

    #fundTransferTable thead th:last-child,
    #fundTransferTable tbody td:last-child {
      border-right: none !important;
    }

    #fundTransferTable thead th:first-child,
    #fundTransferTable tbody td:first-child {
      border-left: none !important;
    }

    #fundTransferTable {
      border-collapse: collapse !important;
    }

    .card:has(#fundTransferTable) {
      overflow: visible !important;
    }

    .card {
      overflow: visible !important;
    }

    #rows_table .r-proj+.select2-container {
      max-width: 220px !important;
      min-width: 180px !important;
    }

    #rows_table .select2-container .select2-selection__rendered {
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      white-space: nowrap !important;
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

    /* ---------- 50 paise rounding rule ---------- */
    function customRound(number) {
      number = Number(number);
      if (!isFinite(number) || number === 0) return '0.00';
      var int = Math.floor(number);
      var decimal = number - int;
      if (decimal === 0) return int.toFixed(2);
      return (decimal < 0.50) ? (int + 0.50).toFixed(2) : (int + 1).toFixed(2);
    }


    /* ---- Select2 Helpers ---- */
    function makeSearchable(selectId) {
      if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') return;
      var $sel = $('#' + selectId);
      if (!$sel.length) return;
      if ($sel.data('select2')) $sel.select2('destroy');
      $sel.select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select ---', allowClear: true });
    }

    function initSelect2Row(tr) {
      if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') return;
      $(tr).find('select').each(function () {
        if ($(this).data('select2')) $(this).select2('destroy');
        $(this).select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select ---', allowClear: true });
      });
    }

    function addRow(prefill = null) {
      const idx = rowIdx++;
      const tb = document.querySelector('#rows_table tbody');
      const tr = document.createElement('tr');
      tr.dataset.idx = idx;
      tr.innerHTML = `
    <td class="rno text-center font-weight-bold font-size-sm">${idx + 1}</td>
    <td><select class="form-control form-control-sm r-dept" name="rows[${idx}][department]"></select><input type="hidden" name="rows[${idx}][sub_department]" class="r-subdep-hidden" value="${prefill?.sub_department ?? ''}"></td>
    <td><select class="form-control form-control-sm r-dist" name="rows[${idx}][district]"></select></td>
    <td style="max-width:220px; overflow:hidden;"><select class="form-control form-control-sm r-proj" name="rows[${idx}][project]"></select><input type="hidden" class="r-div" name="rows[${idx}][division]" value="${prefill?.division ?? ''}"></td>
    <td class="r-rec text-right font-weight-500">0.00</td>
    <td class="r-trf text-right text-muted font-size-sm">0.00</td>
    <td class="r-rem text-right font-weight-bold text-dark">0.00</td>
    <td><select class="form-control form-control-sm r-bank" name="rows[${idx}][to_bank]"><option value="">--Select--</option></select><input type="hidden" name="rows[${idx}][to_ifsc]" class="r-ifsc" value="${prefill?.to_ifsc ?? ''}"><input type="hidden" name="rows[${idx}][to_accno]" class="r-acc" value="${prefill?.to_accno ?? ''}"></td>
    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm r-amt font-weight-bold text-primary" name="rows[${idx}][transfer_amount]" value="${prefill ? money(prefill.transfer_amount || 0) : '0.00'}"></td>
    <td class="r-gst text-right text-danger font-size-sm">0.00</td>
    <td class="r-rem2 text-right text-muted font-size-sm">0.00</td>
    <td class="r-adv text-right text-info font-size-sm">0.00</td>
    <td class="r-gsttds text-right text-warning font-size-sm">0.00</td>
    <td class="r-lab text-right text-secondary font-size-sm">0.00</td>
    <td class="r-it text-right text-warning font-size-sm">0.00</td>
    <td class="r-prop text-right font-weight-bold text-success" style="font-size: 1.1rem;">0.00</td>
    <td class="text-center"><button type="button" class="btn btn-table-action btn-delete-row shadow-none" onclick="delRow(this)"><i class="fas fa-times"></i></button></td>`;
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

      // Apply Select2 to all selects in this row after all options are loaded
      setTimeout(function () { initSelect2Row(tr); }, 400);

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
    function fillDeptOptions(sel, cb) { ensureDeptSeed(); sel.innerHTML = '<option value="">--Select--</option>'; const opts = JSON.parse(document.getElementById('hidden_dept_seed').dataset.options || '[]'); opts.forEach(o => { const op = document.createElement('option'); op.value = o.id; op.textContent = o.name; sel.appendChild(op); }); if ($(sel).data('select2')) { $(sel).select2('destroy'); } $(sel).select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select ---', allowClear: true }); if (cb) cb(); }
    function fillDistrictOptions(deptId, sel, cb) { sel.innerHTML = '<option value="">--Select--</option>'; if (!deptId) { cb && cb(); return; } $.post(actionUrl, { term: 'b', id: 'dist', val: deptId }, function (d) { try { d = JSON.parse(d || '[]'); d.forEach(v => sel.innerHTML += `<option value="${v.id}">${v.district_name}</option>`); } catch (e) { } if ($(sel).data('select2')) { $(sel).select2('destroy'); } $(sel).select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select ---', allowClear: true }); cb && cb(); }); }
    function fillProjectOptions(deptId, distId, sel, cb) { sel.innerHTML = '<option value="">--Select--</option>'; if (!deptId || !distId) { if (cb) cb(); return; } $.post(actionUrl, { term: 'b', id: 'proj', val: distId, dept: deptId }, function (d) { try { d = JSON.parse(d || '[]'); d.forEach(v => sel.innerHTML += `<option value="${v.id}">${v.project_name_hindi}</option>`); } catch (e) { } if ($(sel).data('select2')) { $(sel).select2('destroy'); } $(sel).select2({ theme: 'bootstrap4', width: 'resolve', placeholder: '--- Select ---', allowClear: true }); if (cb) cb(); }); }

    function loadDivisionAndBanksForProject(tr, projId, cb) { if (!projId) { cb && cb(); return; } $.post(actionUrl, { term: 'b', id: 'proj_div', val: projId }, function (res) { const j = JSON.parse(res || '{}'); const divId = j.division_id || ''; tr.querySelector('.r-div').value = divId; const bankSel = tr.querySelector('.r-bank'); if ($(bankSel).data('select2')) $(bankSel).select2('destroy'); $(bankSel).html('<option value="">--</option>'); if (divId) { $.post(actionUrl, { term: 'b', id: 'unit_bank', val: divId }, function (banks) { banks = JSON.parse(banks || '[]'); let opt = '<option value="">--</option>'; banks.forEach(b => opt += `<option value="${b.id}">${b.cus_name}</option>`); $(bankSel).html(opt); $(bankSel).select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select ---', allowClear: true }); cb && cb(); }); } else { $(bankSel).select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select ---', allowClear: true }); cb && cb(); } }); }

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
          const voucherData = JSON.parse(resp || '{}');
          if (voucherData.tds_deducted && parseFloat(voucherData.tds_deducted) > 0) {
            // Calculate TDS percentage if we have the base amount
            const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;
            if (baseAmount > 0) {
              const tdsPer = (parseFloat(voucherData.tds_deducted) / baseAmount) * 100;
              $('#it_per').val(tdsPer.toFixed(2));
            }
          }

          if (voucherData.gsttds_deducted && parseFloat(voucherData.gsttds_deducted) > 0) {
            // Calculate GST-TDS percentage
            const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;
            if (baseAmount > 0) {
              const gsttdsPer = (parseFloat(voucherData.gsttds_deducted) / baseAmount) * 100;
              $('#gsttdspercentage').val(gsttdsPer.toFixed(2));
            }
          }

          if (voucherData.labour_cess && parseFloat(voucherData.labour_cess) > 0) {
            // Set labour cess amount
            $('#leborses').val(parseFloat(voucherData.labour_cess).toFixed(2));
          }

          if ((voucherData.cgst_amount && parseFloat(voucherData.cgst_amount) > 0) ||
            (voucherData.sgst_amount && parseFloat(voucherData.sgst_amount) > 0)) {
            // Use actual CGST/SGST from database
            const cgstAmt = parseFloat(voucherData.cgst_amount) || 0;
            const sgstAmt = parseFloat(voucherData.sgst_amount) || 0;
            const totalGst = cgstAmt + sgstAmt;
            const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;
            if (baseAmount > 0 && totalGst > 0) {
              // Calculate GST percentage (CGST+SGST = total GST)
              const gstPer = (totalGst / baseAmount) * 100;
              $('#gst_per').val(gstPer.toFixed(2));

              // Update CGST/SGST display fields
              $('#cgst_amount').val(customRound(cgstAmt));
              $('#sgst_amount').val(customRound(sgstAmt));
            }
          } else if (voucherData.gsttds_deducted && parseFloat(voucherData.gsttds_deducted) > 0) {
            // Alternative: Split GST-TDS amount into CGST/SGST (50-50 split)
            const gsttdsAmt = parseFloat(voucherData.gsttds_deducted) || 0;
            const cgstAmt = gsttdsAmt / 2;
            const sgstAmt = gsttdsAmt / 2;
            const baseAmount = parseFloat($(tr).find('.r-amt').val()) || 0;

            if (baseAmount > 0 && gsttdsAmt > 0) {
              // Calculate GST percentage from GST-TDS amount
              const gstPer = (gsttdsAmt / baseAmount) * 100;
              $('#gst_per').val(gstPer.toFixed(2));

              // Update CGST/SGST display fields (split from GST-TDS)
              $('#cgst_amount').val(customRound(cgstAmt));
              $('#sgst_amount').val(customRound(sgstAmt));
            }
          } else {
            console.log('No CGST/SGST or GST-TDS amounts found for GST calculation');
          }

          document.querySelectorAll('#rows_table tbody tr').forEach(rowTr => {
            recalcRow(rowTr);
          });
          recalcTotals();
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
          });
        });

        // Auto-select HO From Account bank based on fund receive record
        autoSelectHOBank(proj);
      });
      $(tr).find('.r-amt').on('input', function () {
        const remaining = parseFloat($(tr).find('.r-rem').text()) || 0;
        let v = parseFloat(this.value) || 0;
        if (v > remaining) { v = remaining; this.value = money(v); }
        if (remaining <= 0) { this.value = '0.00'; this.disabled = true; }

        // Recalculate total amount across all rows
        let total_row_amt = 0;
        document.querySelectorAll('#rows_table tbody tr').forEach(t => {
          total_row_amt += parseFloat(t.querySelector('.r-amt')?.value) || 0;
        });

        // Update CGST/SGST boxes based on current GST %
        const gst_per = rate('gst_per');
        if (gst_per > 0 && total_row_amt > 0) {
          const total_gst = (total_row_amt * gst_per) / (100 + gst_per);
          document.getElementById('cgst_amount').value = customRound(total_gst / 2);
          document.getElementById('sgst_amount').value = customRound(total_gst / 2);
        } else {
          document.getElementById('cgst_amount').value = '0.00';
          document.getElementById('sgst_amount').value = '0.00';
        }

        // Update GST-TDS breakdown
        const gsttds_per = rate('gsttdspercentage');
        if (gsttds_per > 0 && total_row_amt > 0) {
          const total_gsttds = (total_row_amt * gsttds_per) / 100;
          document.getElementById('gsttds_cgst').value = money(total_gsttds / 2);
          document.getElementById('gsttds_sgst').value = money(total_gsttds / 2);
        }

        recalcRow(tr);
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
            document.getElementById('cgst_amount').value = customRound(total_gst / 2);
            document.getElementById('sgst_amount').value = customRound(total_gst / 2);
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
      $(tr).find('.r-prop').text(customRound(prop));
      recalcTotals();
    }

    function recalcTotals() {
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
          gst_sel.value = "12"; if ($(gst_sel).data('select2')) $(gst_sel).trigger('change.select2');
        } else if (Math.abs(calculated_per - 18) < 0.5) {
          gst_sel.value = "18"; if ($(gst_sel).data('select2')) $(gst_sel).trigger('change.select2');
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
      document.getElementById('T_prop').textContent = customRound(T_prop);

      document.getElementById('transafer_amount').value = money(T_amt);
      document.getElementById('gstdeduction').value = money(T_gst);
      document.getElementById('totelmgst').value = money(T_rem);
      document.getElementById('sentage').value = money(T_adv);
      document.getElementById('gsttds').value = money(T_gsttds);
      document.getElementById('incometax').value = money(T_it);
      document.getElementById('labourcess_total').value = money(T_lab);
      document.getElementById('praposemoney').value = customRound(T_prop);

      // Update GST-TDS split into CGST-TDS and SGST-TDS footer mirrors
      const gsttds_cgst_split = (parseFloat(T_gsttds || 0) / 2).toFixed(2);
      const gsttds_sgst_split = (parseFloat(T_gsttds || 0) / 2).toFixed(2);
      if (document.getElementById('gsttds_cgst')) document.getElementById('gsttds_cgst').value = gsttds_cgst_split;
      if (document.getElementById('gsttds_sgst')) document.getElementById('gsttds_sgst').value = gsttds_sgst_split;
    }

    $(document).ready(function () {
      ['from_account_no', 'gst_per'].forEach(makeSearchable);
      $('#gst_per').on('change', function () { recalcTotals(); });
      $('#sale_form').on('submit', function (e) {
        try {
          var bankMissing = false;
          jQuery('#rows_table tbody tr').each(function () {
            var $tr = jQuery(this);
            var $bankSel = $tr.find('.r-bank');
            if ($bankSel.length && !$bankSel.val()) {
              bankMissing = true;
            }
          });

          if (bankMissing) {
            alert('Please select To Bank (Unit) for all rows.');
            e.preventDefault();
            return false;
          }
          var $btn = jQuery('#btn_submit');
          if ($btn.data('is-submitting')) {
            e.preventDefault();
            return false;
          }

          $btn.data('is-submitting', true);
          $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
          setTimeout(function () {
            $btn.prop('disabled', true);
          }, 50);
          return true;
        } catch (err) {
          console.error('Form submission error:', err);
          return true;
        }
      });

      const seedRows = <?php echo $seed_rows_js; ?>;
      if (seedRows && seedRows.length) {
        seedRows.forEach(pref => addRow(pref));
      } else {
        setTimeout(function () {
          addRow();
        }, 100);
      }

      document.getElementById('btnAddRow').addEventListener('click', function () {
        addRow();
      });

      const rowsTable = document.getElementById('rows_table');
      if (!seedRows || seedRows.length === 0) {
        setTimeout(function () {
          const tbody = document.querySelector('#rows_table tbody');
          if (tbody && tbody.children.length === 0) {
            addRow();
          }
        }, 200);
      }
    });
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="dataTables/Buttons-1.6.1/js/dataTables.buttons.min.js"></script>
  <script src="dataTables/Buttons-1.6.1/js/buttons.bootstrap4.min.js"></script>
  <script src="dataTables/Buttons-1.6.1/js/buttons.html5.min.js"></script>
  <script src="dataTables/Buttons-1.6.1/js/buttons.print.min.js"></script>

  <script>
    $(document).ready(function () {
      if ($.fn.DataTable.isDataTable('#fundTransferTable')) {
        $('#fundTransferTable').DataTable().destroy();
      }
      $('#fundTransferTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"lf<"ms-auto"B>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel"></i> Excel',
            className: 'btn btn-success btn-sm',
            title: 'Fund Transfer Report',
            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
          },
          {
            extend: 'pdfHtml5',
            text: '<i class="fas fa-file-pdf"></i> PDF',
            className: 'btn btn-danger btn-sm',
            title: 'Fund Transfer Report',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
          },
          {
            extend: 'print',
            text: '<i class="fas fa-print"></i> Print',
            className: 'btn btn-secondary btn-sm',
            title: 'Fund Transfer Report',
            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
          }
        ],
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
          search: 'Search:',
          lengthMenu: 'Show _MENU_ entries',
          info: 'Showing _START_ to _END_ of _TOTAL_ records',
          paginate: { first: 'First', last: 'Last', next: '›', previous: '‹' }
        }
      });
    });
  </script>