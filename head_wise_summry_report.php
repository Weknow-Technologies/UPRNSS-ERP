<?php
/**
 * head_wise_report.php
 * Ledger-style drilldown (green header + DataTables)
 * Heads → Divisions → Projects → Bills (+ Adviser Debits)
 */

include("scripts/settings.php");
include("scripts/approval_system_functions.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

page_header_start();
page_header_end();
page_sidebar();

/* ---------- DB handle ---------- */
$mysqli = isset($db) ? $db : (isset($conn) ? $conn : null);
if (!$mysqli) { die('DB connection ($db or $conn) not found.'); }
@$mysqli->set_charset('utf8mb4');

/* ---------- CONFIG ---------- */
$CREDITS_TABLE           = 'invoice_account_fund_transafer'; // credits per head
$CREDITS_PK              = 'sno';
$CREDITS_PROJKEY         = 'project_name';                   // FK to projects table (int id)

$PROJECTS_TABLE          = 'uprnss_project_temp';
$PROJECTS_PK             = 'sno';
$PROJECTS_DIVISION_FIELD = 'division_id';
$PROJECTS_NAME_EN_FIELD  = 'project_name_hindi';
$PROJECTS_NAME_HI_FIELD  = 'project_name_hindi';

$DR_RELEASE_TABLE        = 'deduction_release';              // debits (released_amount)
$DR_RELEASE_AMOUNT_COL   = 'released_amount';
$DR_RELEASE_HEAD_COL     = 'deduction_type';
$DR_RELEASE_BILL_COL     = 'bill_id';
$DR_RELEASE_PROJ_COL     = 'project_id';

$ADVISER_TABLE           = 'project_bill_adviser';           // debits (type_of_payment)
$ADVISER_AMOUNT_COL      = 'total_net_pay';                  // or 'total_transfer'
$ADVISER_HEAD_COL        = 'type_of_payment';
$ADVISER_PROJ_COL        = 'project_id';

$HEADS = [
  'gsttds'       => ['label' => 'GST TDS',      'credit_col' => 'gsttds'],
  'leborses'     => ['label' => 'Labour Cess',  'credit_col' => 'leborses'],
  'incometax'    => ['label' => 'Income Tax',   'credit_col' => 'incometax'],
  'security'     => ['label' => 'Security',     'credit_col' => 'security'],
  'royalty'      => ['label' => 'Royalty',      'credit_col' => 'royalty'],
  'gstdeduction' => ['label' => 'GST',          'credit_col' => 'gstdeduction'],
  'sentage' => ['label' => 'Centage',          'credit_col' => 'sentage'],
  'other_amount' => ['label' => 'Hold Amount',          'credit_col' => 'other_amount'],
  'contingency' => ['label' => 'Contingency ',          'credit_col' => 'contingency'],
];

/* --- Division master table (for labels) --- */
$DIV_TABLE     = 'uprnss_division';
$DIV_PK        = 's_no';
$DIV_NAME_HI   = 'division_name';
$DIV_NAME_EN   = 'division_name_english';

/* ---------- Helpers ---------- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function toInt($v){ return (int)$v; }

function format_inr($num){
  if ($num === null || $num === '') return '0.00';
  $isNeg = $num < 0; $num = abs((float)$num);
  $dec  = number_format($num, 2, '.', '');
  [$int, $frac] = explode('.', $dec);
  $n = strlen($int);
  if ($n <= 3) { $res = $int; }
  else {
    $last3 = substr($int, -3);
    $rest  = substr($int, 0, $n - 3);
    $rest  = preg_replace('/\B(?=(?:\d{2})+(?!\d))/', ',', $rest);
    $res   = $rest . ',' . $last3;
  }
  return ($isNeg ? '-' : '') . $res . '.' . $frac;
}
function amt_html($amt){
  $cls = ($amt < 0) ? 'text-danger fw-semibold' : 'text-body';
  return '<span class="'.$cls.'">'.h(format_inr($amt)).'</span>';
}

function fetch_keyed_sum($mysqli, $sql, $params, $key_col, $sum_col){
  $out = [];
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) { return $out; }
  if (!empty($params)) {
    $types = '';
    $bind = [];
    foreach ($params as $p) {
      if (is_int($p)) $types .= 'i';
      elseif (is_float($p)) $types .= 'd';
      else $types .= 's';
      $bind[] = $p;
    }
    $stmt->bind_param($types, ...$bind);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $out[$row[$key_col]] = (float)$row[$sum_col];
  }
  $stmt->close();
  return $out;
}

/* ---------- Division map + label ---------- */
$DIVMAP = []; // [id => ['hi' => '...', 'en' => '...']]

function load_division_map(mysqli $mysqli){
  global $DIVMAP, $DIV_TABLE, $DIV_PK, $DIV_NAME_HI, $DIV_NAME_EN;
  $DIVMAP = [];
  $sql = "SELECT `$DIV_PK` AS id, `$DIV_NAME_HI` AS hi, `$DIV_NAME_EN` AS en FROM `$DIV_TABLE` WHERE 1";
  if ($res = $mysqli->query($sql)) {
    while ($r = $res->fetch_assoc()) {
      $id = (int)$r['id'];
      $DIVMAP[$id] = ['hi' => trim((string)$r['hi']), 'en' => trim((string)$r['en'])];
    }
    $res->free();
  }
}
function division_label($div_id, $lang = 'hi'){
  global $DIVMAP;
  $div_id = (int)$div_id;
  if (!isset($DIVMAP[$div_id])) return 'Division '.$div_id;
  $hi = $DIVMAP[$div_id]['hi'] ?? '';
  $en = $DIVMAP[$div_id]['en'] ?? '';
  if ($lang === 'en' && $en !== '') return $en;
  return $hi !== '' ? $hi : ($en !== '' ? $en : 'Division '.$div_id);
}
load_division_map($mysqli);
function get_project($mysqli, $project_id){
  global $PROJECTS_TABLE, $PROJECTS_PK, $PROJECTS_NAME_EN_FIELD, $PROJECTS_NAME_HI_FIELD, $PROJECTS_DIVISION_FIELD;
  $sql = "SELECT `$PROJECTS_PK` AS pid, `$PROJECTS_NAME_EN_FIELD` AS name_en, `$PROJECTS_NAME_HI_FIELD` AS name_hi, `$PROJECTS_DIVISION_FIELD` AS div_id
          FROM `$PROJECTS_TABLE` WHERE `$PROJECTS_PK`=?";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $project_id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res ?: null;
}
/* ---------- Optional division filter from SESSION ---------- */
function ints_in_clause(array $arr){
  $arr = array_values(array_filter(array_map('intval', $arr), fn($v)=>$v>0));
  return $arr ? implode(',', $arr) : '';
}
$ALLOWED_DIVS   = (!empty($_SESSION['divisions']) && is_array($_SESSION['divisions'])) ? $_SESSION['divisions'] : [];
$IN_DIVS        = ints_in_clause($ALLOWED_DIVS);
$HAS_DIV_FILTER = ($IN_DIVS !== '');

/* ---------- Input (drill path) ---------- */
$headKey     = isset($_GET['head']) ? strtolower(trim($_GET['head'])) : '';
$division_id = isset($_GET['division']) ? toInt($_GET['division']) : 0;
$project_id  = isset($_GET['project']) ? toInt($_GET['project']) : 0;

$level = 'head';
if ($headKey && isset($HEADS[$headKey])) {
  $level = 'division';
  if ($division_id > 0) $level = 'project';
  if ($division_id > 0 && $project_id > 0) $level = 'bill';
}
?>

<div class="content">
  <div class="container-fluid">

    <!-- Font Awesome (icon in NAME col) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <style>
      /* Ledger-green header */
      .ledger-grid thead th{
        background: linear-gradient(180deg,#1b6f5a 0%, #145845 100%) !important;
        color:#fff !important;
        font-weight:700;
        border-bottom:1px solid #124c3c !important;
        white-space: nowrap;
      }
      .ledger-grid tbody td{ border-top:1px solid #e9f6f2 !important; }
      .ledger-grid tbody tr:hover{ background:#f2fbf8 !important; }
      .ledger-name a{ color:#0a3d2f; text-decoration:none; }
      .ledger-name a:hover{ text-decoration:underline; }
      .name-icon{ color:#6b7280; margin-right:.4rem; }
      .table td, .table th{ vertical-align: middle; }
      .compact td{ padding:.45rem .6rem !important; }
      .compact th{ padding:.55rem .6rem !important; }
      .badge-soft { background:#e6f7f2; color:#146b54; }
      .dt-container .row{ align-items:center; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="m-0"></h4>
      <div><a class="btn btn-sm btn-outline-secondary" href="?">Reset</a></div>
    </div>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?">Heads</a></li>
        <?php if ($level !== 'head'): ?>
          <li class="breadcrumb-item"><a href="?head=<?=h($headKey)?>"><?=h($HEADS[$headKey]['label'])?></a></li>
        <?php endif; ?>
        <?php if ($level === 'project' || $level === 'bill'): ?>
          <li class="breadcrumb-item"><a href="?head=<?=h($headKey)?>&division=<?=$division_id?>"><?=h(division_label($division_id))?></a></li>
        <?php endif; ?>
        <?php if ($level === 'bill'):
          $p = get_project($mysqli, $project_id);
        ?>
          <li class="breadcrumb-item active" aria-current="page"><?=h($p['name_en'] ?? ('Project '.$project_id))?></li>
        <?php endif; ?>
      </ol>
    </nav>

    <?php
    /* ====== LEVEL 1: HEAD SUMMARY ====== */
    if ($level === 'head') {
      ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>ALL HEADS</strong>
         <!-- <span class="badge rounded-pill badge-soft">Click a row to drill down</span>-->
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table id="tbl-heads" class="table table-striped table-hover m-0 ledger-grid compact">
              <thead>
                <tr>
                  <th style="width:70px;">S. NO</th>
                  <th>NAME</th>
                  <th class="text-end">CREDIT (₹)</th>
                  <th class="text-end">DEBIT (₹)</th>
                  <th class="text-end">CLOSING (₹)</th>
                </tr>
              </thead>
              <tbody>
              <?php
              $sr=1;
              foreach ($HEADS as $key => $meta):
                $col = $meta['credit_col'];

                // Credits
                if ($HAS_DIV_FILTER) {
                  $sqlC = "SELECT SUM(COALESCE(i.`$col`,0)) AS s
                           FROM `$CREDITS_TABLE` i
                           JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = i.`$CREDITS_PROJKEY`
                           WHERE p.`$PROJECTS_DIVISION_FIELD` IN ($IN_DIVS)";
                } else {
                  $sqlC = "SELECT SUM(COALESCE(`$col`,0)) AS s FROM `$CREDITS_TABLE`";
                }
                $credit = (float)($mysqli->query($sqlC)->fetch_assoc()['s'] ?? 0);

                // Debits: deduction_release
                $sqlD1 = "SELECT SUM(COALESCE(d.`$DR_RELEASE_AMOUNT_COL`,0)) AS s FROM `$DR_RELEASE_TABLE` d";
                if ($HAS_DIV_FILTER) {
                  $sqlD1 .= " JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = d.`$DR_RELEASE_PROJ_COL`
                              WHERE d.`$DR_RELEASE_HEAD_COL`=? AND p.`$PROJECTS_DIVISION_FIELD` IN ($IN_DIVS)";
                } else {
                  $sqlD1 .= " WHERE d.`$DR_RELEASE_HEAD_COL`=?";
                }
                $stmtD1 = $mysqli->prepare($sqlD1);
                $stmtD1->bind_param('s', $key);
                $stmtD1->execute();
                $debit1 = (float)($stmtD1->get_result()->fetch_assoc()['s'] ?? 0);
                $stmtD1->close();

                // Debits: adviser
                $sqlD2 = "SELECT SUM(COALESCE(a.`$ADVISER_AMOUNT_COL`,0)) AS s FROM `$ADVISER_TABLE` a";
                if ($HAS_DIV_FILTER) {
                  $sqlD2 .= " JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = a.`$ADVISER_PROJ_COL`
                              WHERE a.`$ADVISER_HEAD_COL`=? AND p.`$PROJECTS_DIVISION_FIELD` IN ($IN_DIVS)";
                } else {
                  $sqlD2 .= " WHERE a.`$ADVISER_HEAD_COL`=?";
                }
                $stmtD2 = $mysqli->prepare($sqlD2);
                $stmtD2->bind_param('s', $key);
                $stmtD2->execute();
                $debit2 = (float)($stmtD2->get_result()->fetch_assoc()['s'] ?? 0);
                $stmtD2->close();

                $debit = $debit1 + $debit2;
                $bal   = $credit - $debit;
                ?>
                <tr>
                  <td class="text-center"><?=$sr++?></td>
                  <td class="ledger-name">
                    <a href="?head=<?=h($key)?>">
                      <i class="fa-regular fa-pen-to-square name-icon no-print"></i><?=h($meta['label'])?>
                      <!--<div class="small text-muted">Key: <code><?=h($key)?></code></div>-->
                    </a>
                  </td>
                  <td class="text-end"><?=amt_html($credit)?></td>
                  <td class="text-end"><?=amt_html($debit)?></td>
                  <td class="text-end fw-semibold"><?=amt_html($bal)?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php
    }

    /* ====== LEVEL 2: DIVISION SUMMARY FOR A HEAD ====== */
    if ($level === 'division') {
      $meta = $HEADS[$headKey];
      $col  = $meta['credit_col'];

      echo '<div class="card mb-3"><div class="card-header"><strong>DIVISION-WISE — '.h($meta['label']).'</strong></div><div class="card-body p-0">';

      // Credits by division
      $sqlC = "SELECT p.`$PROJECTS_DIVISION_FIELD` AS k, SUM(COALESCE(i.`$col`,0)) AS s
               FROM `$CREDITS_TABLE` i
               JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = i.`$CREDITS_PROJKEY`";
      if ($HAS_DIV_FILTER) $sqlC .= " WHERE p.`$PROJECTS_DIVISION_FIELD` IN ($IN_DIVS)";
      $sqlC .= " GROUP BY p.`$PROJECTS_DIVISION_FIELD`";
      $credits = fetch_keyed_sum($mysqli, $sqlC, [], 'k', 's');

      // Debits (DR) by division
      $sqlD1 = "SELECT p.`$PROJECTS_DIVISION_FIELD` AS k, SUM(COALESCE(d.`$DR_RELEASE_AMOUNT_COL`,0)) AS s
                FROM `$DR_RELEASE_TABLE` d
                JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = d.`$DR_RELEASE_PROJ_COL`
                WHERE d.`$DR_RELEASE_HEAD_COL`=?";
      if ($HAS_DIV_FILTER) $sqlD1 .= " AND p.`$PROJECTS_DIVISION_FIELD` IN ($IN_DIVS)";
      $sqlD1 .= " GROUP BY p.`$PROJECTS_DIVISION_FIELD`";
      $debits1 = fetch_keyed_sum($mysqli, $sqlD1, [$headKey], 'k', 's');

      // Debits (Adviser) by division
      $sqlD2 = "SELECT p.`$PROJECTS_DIVISION_FIELD` AS k, SUM(COALESCE(a.`$ADVISER_AMOUNT_COL`,0)) AS s
                FROM `$ADVISER_TABLE` a
                JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = a.`$ADVISER_PROJ_COL`
                WHERE a.`$ADVISER_HEAD_COL`=?";
      if ($HAS_DIV_FILTER) $sqlD2 .= " AND p.`$PROJECTS_DIVISION_FIELD` IN ($IN_DIVS)";
      $sqlD2 .= " GROUP BY p.`$PROJECTS_DIVISION_FIELD`";
      $debits2 = fetch_keyed_sum($mysqli, $sqlD2, [$headKey], 'k', 's');

      $keys = array_unique(array_merge(array_keys($credits), array_keys($debits1), array_keys($debits2)));
      sort($keys);

      echo '<div class="table-responsive"><table id="tbl-divisions" class="table table-striped table-hover m-0 ledger-grid compact"><thead><tr>
            <th style="width:70px;">S. NO</th><th>NAME</th><th class="text-end">CREDIT (₹)</th><th class="text-end">DEBIT (₹)</th><th class="text-end">CLOSING (₹)</th></tr></thead><tbody>';
      $sr=1;
      foreach ($keys as $k) {
        $c = $credits[$k] ?? 0; $d = ($debits1[$k] ?? 0) + ($debits2[$k] ?? 0); $b = $c - $d;
        $dn_hi = division_label($k, 'hi');
        $dn_en = division_label($k, 'en');
        echo '<tr>';
        echo '<td class="text-center">'.$sr++.'</td>';
        echo '<td class="ledger-name"><a href="?head='.h($headKey).'&division='.(int)$k.'"><i class="fa-regular fa-pen-to-square name-icon"></i>'
           . h($dn_hi)
           . ($dn_en && $dn_en !== $dn_hi ? '<div class="small text-muted">'.h($dn_en).'</div>' : '')
           . '</a></td>';
        echo '<td class="text-end">'.amt_html($c).'</td>';
        echo '<td class="text-end">'.amt_html($d).'</td>';
        echo '<td class="text-end fw-semibold">'.amt_html($b).'</td>';
        echo '</tr>';
      }
      if (!$keys) echo '<tr><td colspan="5" class="text-center text-muted">No data</td></tr>';
      echo '</tbody></table></div></div></div>';
    }

    /* ====== LEVEL 3: PROJECT SUMMARY WITHIN DIVISION FOR A HEAD ====== */
    if ($level === 'project') {
      $meta = $HEADS[$headKey];
      $col  = $meta['credit_col'];

      echo '<div class="card mb-3"><div class="card-header"><strong>PROJECT-WISE — '.h($meta['label']).' · '.h(division_label($division_id)).'</strong></div><div class="card-body p-0">';

      // Credits by project in division
      $sqlC = "SELECT i.`$CREDITS_PROJKEY` AS k, SUM(COALESCE(i.`$col`,0)) AS s
               FROM `$CREDITS_TABLE` i
               JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = i.`$CREDITS_PROJKEY`
               WHERE p.`$PROJECTS_DIVISION_FIELD`=?
               GROUP BY i.`$CREDITS_PROJKEY`";
      $credits = fetch_keyed_sum($mysqli, $sqlC, [$division_id], 'k', 's');

      // Debits (DR) by project in division
      $sqlD1 = "SELECT d.`$DR_RELEASE_PROJ_COL` AS k, SUM(COALESCE(d.`$DR_RELEASE_AMOUNT_COL`,0)) AS s
                FROM `$DR_RELEASE_TABLE` d
                JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = d.`$DR_RELEASE_PROJ_COL`
                WHERE d.`$DR_RELEASE_HEAD_COL`=? AND p.`$PROJECTS_DIVISION_FIELD`=?
                GROUP BY d.`$DR_RELEASE_PROJ_COL`";
      $debits1 = fetch_keyed_sum($mysqli, $sqlD1, [$headKey, $division_id], 'k', 's');

      // Debits (Adviser) by project in division
      $sqlD2 = "SELECT a.`$ADVISER_PROJ_COL` AS k, SUM(COALESCE(a.`$ADVISER_AMOUNT_COL`,0)) AS s
                FROM `$ADVISER_TABLE` a
                JOIN `$PROJECTS_TABLE` p ON p.`$PROJECTS_PK` = a.`$ADVISER_PROJ_COL`
                WHERE a.`$ADVISER_HEAD_COL`=? AND p.`$PROJECTS_DIVISION_FIELD`=?
                GROUP BY a.`$ADVISER_PROJ_COL`";
      $debits2 = fetch_keyed_sum($mysqli, $sqlD2, [$headKey, $division_id], 'k', 's');

      $keys = array_unique(array_merge(array_keys($credits), array_keys($debits1), array_keys($debits2)));
      sort($keys);

      echo '<div class="table-responsive"><table id="tbl-projects" class="table table-striped table-hover m-0 ledger-grid compact"><thead><tr>
            <th style="width:70px;">S. NO</th><th>NAME</th><th class="text-end">CREDIT (₹)</th><th class="text-end">DEBIT (₹)</th><th class="text-end">CLOSING (₹)</th></tr></thead><tbody>';
      $sr=1;
      foreach ($keys as $pid) {
        $c = $credits[$pid] ?? 0; $d = ($debits1[$pid] ?? 0) + ($debits2[$pid] ?? 0); $b = $c - $d;
        $p = get_project($mysqli, (int)$pid);
        $pname = $p['name_en'] ?? ('Project '.$pid);
        echo '<tr>';
        echo '<td class="text-center">'.$sr++.'</td>';
        echo '<td class="ledger-name"><a href="?head='.h($headKey).'&division='.$division_id.'&project='.(int)$pid.'"><i class="fa-regular fa-pen-to-square name-icon no-print no_print"></i>'.h($pname).'</a></td>';
        echo '<td class="text-end">'.amt_html($c).'</td>';
        echo '<td class="text-end">'.amt_html($d).'</td>';
        echo '<td class="text-end fw-semibold">'.amt_html($b).'</td>';
        echo '</tr>';
      }
      if (!$keys) echo '<tr><td colspan="5" class="text-center text-muted">No data</td></tr>';
      echo '</tbody></table></div></div></div>';
    }

    /* ====== LEVEL 4: BILL-WISE FOR A SPECIFIC PROJECT & HEAD ====== */
    if ($level === 'bill') {
      $meta = $HEADS[$headKey];
      $col  = $meta['credit_col'];
      $proj = get_project($mysqli, $project_id);

      echo '<div class="card mb-3"><div class="card-header"><strong>BILL-WISE — '.h($meta['label']).' · '.h($proj['name_en'] ?? ('Project '.$project_id)).'</strong></div>';

      // Bills for this project
      $sqlBills = "SELECT i.`$CREDITS_PK` AS bill_id, i.`order_no`, i.`order_date`, i.`bill_no`, i.`bill_date`, COALESCE(i.`$col`,0) AS credit
                   FROM `$CREDITS_TABLE` i
                   WHERE i.`$CREDITS_PROJKEY`=?
                   ORDER BY i.`$CREDITS_PK` DESC";
      $stmtB = $mysqli->prepare($sqlBills);
      $stmtB->bind_param('i', $project_id);
      $stmtB->execute();
      $resB = $stmtB->get_result();

      echo '<div class="card-body p-0">';
      echo '<div class="table-responsive"><table id="tbl-bills" class="table table-striped table-hover m-0 ledger-grid compact"><thead><tr>
            <th style="width:70px;">S. NO</th>
            <th>BILL No.</th>
            <th>DATE</th>
            <th class="text-end">CREDIT (₹)</th>
            <th class="text-end">DEBIT via DR (₹)</th>
            <th class="text-end">CLOSING (₹)</th>
            <th>ACTIONS</th>
            </tr></thead><tbody>';

      // DR per bill for this project & head
      $sqlDrPerBill = "SELECT `$DR_RELEASE_BILL_COL` AS k, SUM(COALESCE(`$DR_RELEASE_AMOUNT_COL`,0)) AS s
                       FROM `$DR_RELEASE_TABLE`
                       WHERE `$DR_RELEASE_HEAD_COL`=? AND `$DR_RELEASE_PROJ_COL`=?
                       GROUP BY `$DR_RELEASE_BILL_COL`";
      $drPerBill = fetch_keyed_sum($mysqli, $sqlDrPerBill, [$headKey, $project_id], 'k', 's');

      $grandC = $grandD = 0;
      $sr=1;
      while ($row = $resB->fetch_assoc()) {
        $bid   = (int)$row['bill_id'];
        $cAmt  = (float)$row['credit'];
        $dAmt  = (float)($drPerBill[$bid] ?? 0);
        $bal   = $cAmt - $dAmt; // Adviser debits shown below (project-level)

        $grandC += $cAmt; $grandD += $dAmt;

        echo '<tr>';
        echo '<td class="text-center">'.$sr++.'</td>';
        echo '<td class="ledger-name"></i>'.h($row['bill_no'] ?: ('#'.$bid)).'</td>';
        echo '<td>'.h($row['bill_date']).'</td>';
        echo '<td class="text-end">'.amt_html($cAmt).'</td>';
        echo '<td class="text-end">'.amt_html($dAmt).'</td>';
        echo '<td class="text-end fw-semibold">'.amt_html($bal).'</td>';
        echo '<td><a class="btn btn-sm btn-outline-primary" href="#.php?id='.$bid.'" target="">Open Bill</a></td>';
        echo '</tr>';
      }
      $stmtB->close();

      echo '<tr class="table-light fw-semibold">'
         .'<td colspan="3" class="text-end">TOTAL</td>'
         .'<td class="text-end">'.amt_html($grandC).'</td>'
         .'<td class="text-end">'.amt_html($grandD).'</td>'
         .'<td class="text-end">'.amt_html($grandC - $grandD).'</td>'
         .'<td></td>'
         .'</tr>';

      echo '</tbody></table></div>';
      echo '</div></div>';

      // Adviser debits (project-level)
      // echo '<div class="card"><div class="card-header"><strong>ADVISER DEBITS (PROJECT-LEVEL) — '.h($meta['label']).'</strong>
            // <span class="ms-2 badge badge-soft">Note: adviser amounts are at project-level.</span></div>';

      $sqlAdv = "SELECT id, `$ADVISER_AMOUNT_COL` AS amt, `payment_date`, `note_sheet_no`
                 FROM `$ADVISER_TABLE`
                 WHERE `$ADVISER_PROJ_COL`=? AND `$ADVISER_HEAD_COL`=?
                 ORDER BY id DESC";
      $stmtA = $mysqli->prepare($sqlAdv);
      $stmtA->bind_param('is', $project_id, $headKey);
      $stmtA->execute();
      $resA = $stmtA->get_result();

      // echo '<div class="card-body p-0"><div class="table-responsive"><table id="tbl-adviser" class="table table-striped table-hover m-0 ledger-grid compact"><thead><tr>
            // <th style="width:70px;">S. NO</th><th>NOTE SHEET NO</th><th>PAYMENT DATE</th><th class="text-end">AMOUNT (₹)</th><th>ACTION</th>
            // </tr></thead><tbody>';
      // $advTot = 0; $sr=1;
      // while ($a = $resA->fetch_assoc()) {
        // $advTot += (float)$a['amt'];
        // echo '<tr>'
           // .'<td class="text-center">'.$sr++.'</td>'
           // .'<td>'.h($a['note_sheet_no']).'</td>'
           // .'<td>'.h($a['payment_date']).'</td>'
           // .'<td class="text-end">'.amt_html($a['amt']).'</td>'
           // .'<td><a class="btn btn-sm btn-outline-secondary" target="_blank" href="view_adviser.php?id='.(int)$a['id'].'">Open</a></td>'
           // .'</tr>';
      // }
      // if ($advTot === 0) {
        // echo '<tr><td colspan="5" class="text-center text-muted">No adviser debits</td></tr>';
      // } else {
        // echo '<tr class="table-light fw-semibold"><td colspan="3" class="text-end">TOTAL</td><td class="text-end">'.amt_html($advTot).'</td><td></td></tr>';
      // }

      // $stmtA->close();
      // echo '</tbody></table></div></div></div>';
    }
    ?>

  <!--  <div class="mt-3 small text-muted">
      <strong>Tips:</strong> <code>$HEADS</code> में heads जोड़/हटाएँ (key = <code>deduction_release.deduction_type</code> और <code>project_bill_adviser.type_of_payment</code>).
      Adviser amount switch: <code>$ADVISER_AMOUNT_COL</code> → <code>total_transfer</code>.
      <?= $HAS_DIV_FILTER ? '<span class="ms-2 badge badge-soft">Division filter active (session).</span>' : '' ?>
    </div>
-->
    <!-- jQuery + DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
      (function($){
        function initDT(sel){
          var $t = $(sel);
          if (!$t.length) return;
          $t.DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            order: [],
            pageLength: 25,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
            autoWidth: false
          });
        }
        $(function(){
          initDT('#tbl-heads');
          initDT('#tbl-divisions');
          initDT('#tbl-projects');
          initDT('#tbl-bills');
          initDT('#tbl-adviser');
        });
      })(jQuery);
    </script>

  </div>
</div>

<?php
page_footer_start();
page_footer_end();
