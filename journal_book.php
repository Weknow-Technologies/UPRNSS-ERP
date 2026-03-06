<?php
// journal_book_grouped.php  (Bootstrap minimal)

// --- Errors (optional) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/scripts/settings.php'; // expects $db (mysqli)
mysqli_set_charset($db, 'utf8mb4');

/* ------------ Helpers ------------ */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function current_fy_string(): string {
    $y = (int)date('Y'); $m = (int)date('n');
    $start = ($m >= 4) ? $y : ($y - 1);
    return $start . '-' . ($start + 1);
}
function parse_fy_to_range(string $fy): array {
    $fy = trim($fy);
    if (preg_match('/^(\d{4})\s*-\s*(\d{4})$/', $fy, $m)) {
        $startY = (int)$m[1]; $endY = (int)$m[2];
    } elseif (preg_match('/^(\d{4})$/', $fy, $m)) {
        $startY = (int)$m[1]; $endY = $startY + 1;
    } else {
        [$startY, $endY] = array_map('intval', explode('-', current_fy_string()));
    }
    $start = sprintf('%04d-04-01 00:00:00', $startY);
    $end   = sprintf('%04d-03-31 23:59:59', $endY);
    return [$startY, $endY, $start, $end];
}
function month_options_apr_to_mar(): array {
    return [
        'all'=>'All Months',
        '04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August',
        '09'=>'September','10'=>'October','11'=>'November','12'=>'December',
        '01'=>'January','02'=>'February','03'=>'March',
    ];
}
function month_start_end_in_fy(int $fyStartYear, string $mm): array {
    $m = (int)$mm; $y = ($m>=4 && $m<=12) ? $fyStartYear : ($fyStartYear+1);
    $days = cal_days_in_month(CAL_GREGORIAN, $m, $y);
    return [sprintf('%04d-%02d-01 00:00:00',$y,$m), sprintf('%04d-%02d-%02d 23:59:59',$y,$m,$days)];
}
function fy_options_list(int $yearsBack=7, int $yearsForward=1): array {
    [$baseStart] = array_map('intval', explode('-', current_fy_string()));
    $opts = [];
    for ($y=$baseStart-$yearsBack; $y<=$baseStart+$yearsForward; $y++){
        $opts[] = sprintf('%d-%d', $y, $y+1);
    }
    return $opts;
}
function ordinal_day(string $ymd): string {
    $ts = strtotime($ymd); if(!$ts) return $ymd;
    $d = (int)date('j', $ts);
    $suf = 'th';
    if(!in_array(($d % 100), [11,12,13], true)){
        $last = $d % 10;
        if($last===1) $suf='st'; elseif($last===2) $suf='nd'; elseif($last===3) $suf='rd';
    }
    return date('j', $ts).$suf.' '.date('F Y', $ts);
}

// Map customer sno -> cus_name
$custMap = [];
$cr = mysqli_query($db, "SELECT sno, cus_name FROM billit_customer");
if($cr){
    while($c = mysqli_fetch_assoc($cr)){
        $custMap[(string)$c['sno']] = $c['cus_name'];
    }
}
function account_label($val, array $custMap): string {
    $t = trim((string)$val);
    if($t !== '' && ctype_digit($t) && isset($custMap[$t])) return $custMap[$t];
    return $t;
}

/* ----------- Filters ---------- */
$fyParam     = isset($_GET['fy']) ? trim($_GET['fy']) : current_fy_string();
$monthParam  = isset($_GET['m']) ? trim($_GET['m']) : 'all';

list($fyStartYear, $fyEndYear, $fyStartDt, $fyEndDt) = parse_fy_to_range($fyParam);
if ($monthParam !== 'all') {
    [$rangeStart, $rangeEnd] = month_start_end_in_fy($fyStartYear, $monthParam);
} else {
    $rangeStart = $fyStartDt; $rangeEnd = $fyEndDt;
}

/*
   Core logic (RECEIPTS + PAYMENTS):
   - Join on stock.journal_id = invoice.sno  ✅
   - No CONVERT/COLLATE in JOIN              ✅
   - Add source tag: 'receipt' / 'payment'
*/
$sql = "
SELECT * FROM (
  /* RECEIPTS */
  SELECT 
    DATE(i.`timestamp`)      AS dt,
    i.`voucher_no`           AS voucher_no,
    i.`remarks`              AS inv_remarks,
    s.`by`                   AS by_ac,
    s.`to`                   AS to_ac,
    IFNULL(s.`amount`,0.00)  AS amount,
    s.`remarks`              AS s_remarks,
    s.`sno`                  AS line_sno,
    'receipt'                AS src
  FROM `billit_invoice_erp_receipt` i
  LEFT JOIN `billit_stock_erp_receipt` s
    ON s.`journal_id` = i.`sno`
  WHERE i.`timestamp` BETWEEN ? AND ?

  UNION ALL

  /* PAYMENTS */
  SELECT 
    DATE(i.`timestamp`)      AS dt,
    i.`voucher_no`           AS voucher_no,
    i.`admin_remarks`        AS inv_remarks,
    s.`by`                   AS by_ac,
    s.`to`                   AS to_ac,
    IFNULL(s.`amount`,0.00)  AS amount,
    s.`remarks`              AS s_remarks,
    s.`sno`                  AS line_sno,
    'payment'                AS src
  FROM `billit_invoice_erp_payment` i
  LEFT JOIN `billit_stock_erp_payment` s
    ON s.`journal_id` = i.`sno`
  WHERE i.`timestamp` BETWEEN ? AND ?
) u
ORDER BY u.dt ASC, u.voucher_no ASC, u.line_sno ASC
";

$stmt = mysqli_prepare($db, $sql);
if(!$stmt){ die('SQL Prepare Error: '.h(mysqli_error($db))); }
mysqli_stmt_bind_param($stmt, 'ssss', $rangeStart, $rangeEnd, $rangeStart, $rangeEnd);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

/* Build structure */
$data = []; $order = []; $grandDr = 0.0; $grandCr = 0.0;
while($row = $res->fetch_assoc()){
    $vno = (string)$row['voucher_no'];
    $src = ($row['src'] === 'payment') ? 'payment' : 'receipt';
    // composite key so receipt & payment with same voucher don't collide
    $key = $src . '|' . $vno;

    if(!isset($data[$key])){
        $data[$key] = [
            'dt' => $row['dt'] ?: '',
            'inv_remarks' => (string)$row['inv_remarks'],
            'type' => ($src==='payment' ? 'Payment' : 'Receipt'),
            'voucher_no' => $vno,
            'dr' => [], 'cr' => [],
            'stock_remarks' => []
        ];
        $order[] = $key;
    }

    $amt = (float)$row['amount'];

    $byAcc = account_label($row['by_ac'], $custMap);
    $toAcc = account_label($row['to_ac'], $custMap);

    if($byAcc !== ''){
        if(!isset($data[$key]['dr'][$byAcc])) $data[$key]['dr'][$byAcc] = 0.0;
        $data[$key]['dr'][$byAcc] += $amt;
    }
    if($toAcc !== ''){
        if(!isset($data[$key]['cr'][$toAcc])) $data[$key]['cr'][$toAcc] = 0.0;
        $data[$key]['cr'][$toAcc] += $amt;
    }

    $srem = trim((string)$row['s_remarks']);
    if($srem!==''){ $data[$key]['stock_remarks'][$srem]=1; }
}
mysqli_stmt_close($stmt);

// Totals
foreach($data as $key=>$entry){
    $grandDr += array_sum($entry['dr']);
    $grandCr += array_sum($entry['cr']);
}

$monthNames = month_options_apr_to_mar();
$fyOptions  = fy_options_list();

/* ---------- Page Skeleton ---------- */
page_header_start();
?>
<style>
  :root{ --app-header-h: 50px; } /* adjust to your fixed header height */

  /* Thicker, high-contrast header */
  .journal-table thead th {
    padding: 0.9rem 0.75rem !important;
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
    background-color: #ec414a !important; /* your red */
    border-bottom: 2px solid #dee2e6 !important;
  }

  /* Sticky header inside the scroll area */
  .table-scroll {
    max-height: calc(100vh - var(--app-header-h) - 16px);
    overflow: auto;
  }
  .journal-table thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    box-shadow: 0 2px 0 rgba(0,0,0,.03);
    background-clip: padding-box;
  }

  /* Footer look */
  .journal-table tfoot td {
    background-color: #f8f9fa;
    border-top: 2px solid #dee2e6;
    font-weight: 700;
  }

  /* Cells */
  .journal-table td { padding: 0.55rem 0.75rem; }

  /* Right-align numbers */
  .text-end { text-align: right !important; }

  /* Top summary (table-like & sticky under app header) */
  .summary-total-row td {
    background-color: #f8f9fa;
    border-top: 2px solid #dee2e6;
    border-bottom: 2px solid #dee2e6;
    font-weight: 700;
  }
  
</style>
<?php
page_header_end();
page_sidebar();
?>
<div class="container-fluid my-3">
  <div class="card">
    <div class="card-body">
      <!--<h4 class="card-title mb-3">Journal Book</h4>-->
      <form method="get" class="row g-2">
        <div class="col-md-3">
          <label for="fy" class="form-label">Financial Year</label>
          <select id="fy" name="fy" class="form-control">
            <?php foreach($fyOptions as $fy): ?>
              <option value="<?=h($fy)?>" <?=($fy === $fyParam ? 'selected' : '')?>><?=h($fy)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label for="m" class="form-label">Month (Apr–Mar)</label>
          <select id="m" name="m" class="form-control">
            <?php foreach($monthNames as $k=>$label): ?>
              <option value="<?=h($k)?>" <?=($k === $monthParam ? 'selected' : '')?>><?=h($label)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary me-2">Apply</button>
          <a class="btn btn-secondary" href="?fy=<?=urlencode(current_fy_string())?>&m=all">Reset</a>
        </div>
        <div class="col-12">
          <small class="text-muted">
            Voucher period by invoice date:
            <strong><?=h($rangeStart)?></strong> → <strong><?=h($rangeEnd)?></strong> |
            Vouchers: <strong><?=count($data)?></strong>
          </small>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <!-- Top Grand Total as a table-like row (matches footer) -->
    <div class="summary-stick">
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle journal-table">
          <tbody>
            <tr class="table-light summary-total-row">
              <td style="width:220px;"></td>
              <td style="width:180px;"></td>
              <td style="width:110px;"></td>
              <td class="text-end"><strong>Grand Total</strong></td>
              <td class="text-end" style="width:140px;"><strong><?= number_format($grandDr, 2) ?></strong></td>
              <td class="text-end" style="width:140px;"><strong><?= number_format($grandCr, 2) ?></strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-body p-0">
      <!-- Scrollable area with sticky header -->
      <div class="table-responsive table-scroll">
        <table class="table table-sm table-striped table-hover mb-0 align-middle journal-table">
          <thead>
            <tr>
              <th style="width: 220px;">Date</th>
              <th style="width: 180px;">Voucher No</th>
             <!-- <th style="width: 110px;">Type</th>-->
              <th>Transaction</th>
              <th class="text-end" style="width: 140px;">Dr (Rs)</th>
              <th class="text-end" style="width: 140px;">Cr (Rs)</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($order)): ?>
              <tr><td colspan="6" class="text-muted">No vouchers found for the selected period.</td></tr>
            <?php else: ?>
              <?php foreach($order as $key): $entry = $data[$key]; ?>
                <?php
                  $lines = [];
                  foreach($entry['dr'] as $acc=>$amt){ $lines[] = ['acc'=>$acc, 'dr'=>$amt, 'cr'=>0.0]; }
                  foreach($entry['cr'] as $acc=>$amt){ $lines[] = ['acc'=>$acc, 'dr'=>0.0, 'cr'=>$amt]; }
                  if(!$lines) continue;
                  $dateDisp = $entry['dt'] ? ordinal_day($entry['dt']) : '';
                  $narr = trim($entry['inv_remarks']);
                  if($narr===''){ $narr = implode('; ', array_keys($entry['stock_remarks'])); }
                ?>
                <?php foreach($lines as $idx=>$r): ?>
                  <tr>
                    <td><?= $idx===0 ? h($dateDisp) : '' ?></td>
                    <td><?= $idx===0 ? h($entry['voucher_no']) : '' ?></td>
                    <!--<td><?= $idx===0 ? h($entry['type']) : '' ?></td>-->
                    <td<?= $idx>0 ? ' style="padding-left:0px;"' : '' ?>><?= h($r['acc']) ?></td>
                    <td class="text-end"><?= $r['dr']>0 ? number_format($r['dr'], 2) : '' ?></td>
                    <td class="text-end"><?= $r['cr']>0 ? number_format($r['cr'], 2) : '' ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if($narr!==''): ?>
                  <tr class="table-active">
                    <td></td><td></td><td></td>
                    <td colspan="3"><em>(<?= h($narr) ?>)</em></td>
                  </tr>
                <?php endif; ?>
                <tr><td colspan="6" class="p-1"></td></tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
              <td class="text-end"><strong><?= number_format($grandDr, 2) ?></strong></td>
              <td class="text-end"><strong><?= number_format($grandCr, 2) ?></strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
<?php
page_footer_start();
page_footer_end();
?>