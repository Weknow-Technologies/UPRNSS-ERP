<?php
include("scripts/settings.php");
include("scripts/alerts.php");

$is_sadmin = isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'sadmin';
$user_divisions = (isset($_SESSION['divisions']) && is_array($_SESSION['divisions'])) ? $_SESSION['divisions'] : [];

// --- AJAX ENDPOINT ---
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    
    $type = $_GET['type'] ?? '';
    $selected_division = $_GET['division_id'] ?? '';
    $from_date = $_GET['from_date'] ?? '';
    $to_date = $_GET['to_date'] ?? '';
    
    $div_cond_cust = ($selected_division == 'all') ? "" : " AND unit_id='$selected_division' ";
    $div_cond_proj = ($selected_division == 'all') ? "" : " AND p.division_id='$selected_division' ";
    
    $records = [];
    
    if ($type === 'expense') {
        $keyword = mysqli_real_escape_string($db, $_GET['keyword'] ?? '');
        $q = "SELECT c.sno, c.cus_name, SUM(CASE WHEN t.type='DR' THEN CAST(t.amount AS DECIMAL(14,2)) ELSE -CAST(t.amount AS DECIMAL(14,2)) END) as bal 
              FROM billit_customer_transactions t 
              JOIN billit_customer c ON t.cust_id = c.sno 
              WHERE c.cus_name LIKE '%$keyword%' $div_cond_cust
              AND SUBSTRING(t.timestamp,1,10) >= '$from_date' AND SUBSTRING(t.timestamp,1,10) <= '$to_date'
              GROUP BY c.sno, c.cus_name
              HAVING bal != 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'name' => $r['cus_name'],
                'amount' => (float)$r['bal'],
                'has_children' => false
            ];
        }
    } 
    elseif ($type === 'receive_divs') {
        // Total amount received per division
        $receive_to = $_GET['receive_to'] ?? 'Unit';
        $q = "SELECT d.s_no, d.division_name, SUM(tfr.p_receive_amount) as tot 
              FROM transaction_fund_receive tfr 
              JOIN invoice_fund_receive ifr ON tfr.invoice_id = ifr.sno 
              JOIN uprnss_project_temp p ON p.sno = tfr.project_id 
              JOIN uprnss_division d ON p.division_id = d.s_no
              WHERE ifr.fund_receive_to = '$receive_to'
              AND ifr.receive_date >= '$from_date' AND ifr.receive_date <= '$to_date'
              GROUP BY d.s_no, d.division_name
              HAVING tot > 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'id' => $r['s_no'],
                'name' => $r['division_name'],
                'amount' => (float)$r['tot'],
                'has_children' => true
            ];
        }
    }
    elseif ($type === 'receive_projs') {
        $receive_to = $_GET['receive_to'] ?? 'Unit';
        $div_id = mysqli_real_escape_string($db, $_GET['div_id'] ?? '');
        $q = "SELECT p.sno, p.project_name, SUM(tfr.p_receive_amount) as tot 
              FROM transaction_fund_receive tfr 
              JOIN invoice_fund_receive ifr ON tfr.invoice_id = ifr.sno 
              JOIN uprnss_project_temp p ON p.sno = tfr.project_id 
              WHERE p.division_id = '$div_id' AND ifr.fund_receive_to = '$receive_to'
              AND ifr.receive_date >= '$from_date' AND ifr.receive_date <= '$to_date'
              GROUP BY p.sno, p.project_name
              HAVING tot > 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'name' => $r['project_name'],
                'amount' => (float)$r['tot'],
                'has_children' => false
            ];
        }
    }
    elseif ($type === 'opening_divs') {
        // Show divisions and their opening balance of mapped ledgers
        $q = "SELECT d.s_no, d.division_name, 
                     SUM(CAST(c.opening_balance AS DECIMAL(14,2))) + COALESCE(SUM(tr.ledger_sum), 0) as op
              FROM billit_customer c
              JOIN uprnss_division d ON c.unit_id = d.s_no
              LEFT JOIN (
                  SELECT cust_id, SUM(CASE WHEN type='DR' THEN CAST(amount AS DECIMAL(14,2)) ELSE -CAST(amount AS DECIMAL(14,2)) END) as ledger_sum
                  FROM billit_customer_transactions
                  WHERE SUBSTRING(timestamp,1,10) < '$from_date'
                  GROUP BY cust_id
              ) tr ON tr.cust_id = c.sno
              WHERE c.erp_code IS NOT NULL AND c.erp_code != ''
              GROUP BY d.s_no, d.division_name
              HAVING op != 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'id' => $r['s_no'],
                'name' => $r['division_name'],
                'amount' => -(float)$r['op'], // Credit is positive for this side
                'has_children' => true
            ];
        }
    }
    elseif ($type === 'opening_ledgers') {
        // Show ledgers for a specific division
        $div_id = mysqli_real_escape_string($db, $_GET['div_id'] ?? '');
        $q = "SELECT c.sno, c.cus_name, 
                     CAST(c.opening_balance AS DECIMAL(14,2)) + 
                     COALESCE((SELECT SUM(CASE WHEN t.type='DR' THEN CAST(t.amount AS DECIMAL(14,2)) ELSE -CAST(t.amount AS DECIMAL(14,2)) END) 
                               FROM billit_customer_transactions t 
                               WHERE t.cust_id = c.sno AND SUBSTRING(t.timestamp,1,10) < '$from_date'), 0) as op
              FROM billit_customer c
              WHERE c.erp_code IS NOT NULL AND c.erp_code != '' AND c.unit_id = '$div_id'
              HAVING op != 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'name' => $r['cus_name'],
                'amount' => -(float)$r['op'], // Credit is positive
                'has_children' => false
            ];
        }
    }
    elseif ($type === 'closing_divs') {
        // Show divisions and their closing balance of mapped ledgers
        $q = "SELECT d.s_no, d.division_name, 
                     SUM(CAST(c.opening_balance AS DECIMAL(14,2))) + COALESCE(SUM(tr.ledger_sum), 0) as op
              FROM billit_customer c
              JOIN uprnss_division d ON c.unit_id = d.s_no
              LEFT JOIN (
                  SELECT cust_id, SUM(CASE WHEN type='DR' THEN CAST(amount AS DECIMAL(14,2)) ELSE -CAST(amount AS DECIMAL(14,2)) END) as ledger_sum
                  FROM billit_customer_transactions
                  WHERE SUBSTRING(timestamp,1,10) <= '$to_date'
                  GROUP BY cust_id
              ) tr ON tr.cust_id = c.sno
              WHERE c.erp_code IS NOT NULL AND c.erp_code != ''
              GROUP BY d.s_no, d.division_name
              HAVING op != 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'id' => $r['s_no'],
                'name' => $r['division_name'],
                'amount' => (float)$r['op'], // Debit is positive for closing balance on DR side
                'has_children' => true
            ];
        }
    }
    elseif ($type === 'closing_ledgers') {
        // Show ledgers for a specific division
        $div_id = mysqli_real_escape_string($db, $_GET['div_id'] ?? '');
        $q = "SELECT c.sno, c.cus_name, 
                     CAST(c.opening_balance AS DECIMAL(14,2)) + 
                     COALESCE((SELECT SUM(CASE WHEN t.type='DR' THEN CAST(t.amount AS DECIMAL(14,2)) ELSE -CAST(t.amount AS DECIMAL(14,2)) END) 
                               FROM billit_customer_transactions t 
                               WHERE t.cust_id = c.sno AND SUBSTRING(t.timestamp,1,10) <= '$to_date'), 0) as op
              FROM billit_customer c
              WHERE c.erp_code IS NOT NULL AND c.erp_code != '' AND c.unit_id = '$div_id'
              HAVING op != 0";
        $res = execute_query($q);
        while ($r = mysqli_fetch_assoc($res)) {
            $records[] = [
                'name' => $r['cus_name'],
                'amount' => (float)$r['op'], // Debit is positive
                'has_children' => false
            ];
        }
    }
    
    echo json_encode($records);
    exit;
}
// --- END AJAX ENDPOINT ---

$msg = '';

$selected_division = isset($_GET['division_id']) ? $_GET['division_id'] : ($is_sadmin ? 'all' : (count($user_divisions) > 0 ? $user_divisions[0] : ''));
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-04-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-03-31', strtotime('+1 year', strtotime($from_date)));

$div_cond_cust = ($selected_division == 'all') ? "" : " AND unit_id='$selected_division' ";
$div_cond_proj = ($selected_division == 'all') ? "" : " AND p.division_id='$selected_division' ";

// 1. Amount Received
function get_amount_received($receive_to, $div_cond_proj, $from_date, $to_date) {
    $q = "SELECT SUM(tfr.p_receive_amount) as tot 
          FROM transaction_fund_receive tfr 
          JOIN invoice_fund_receive ifr ON tfr.invoice_id = ifr.sno 
          JOIN uprnss_project_temp p ON p.sno = tfr.project_id 
          WHERE ifr.fund_receive_to = '$receive_to'
          AND ifr.receive_date >= '$from_date' AND ifr.receive_date <= '$to_date' $div_cond_proj";

    $res = execute_query($q);
    if($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        return (float)($row['tot']);
    }
    return 0;
}

$amt_from_ho = get_amount_received('HO', $div_cond_proj, $from_date, $to_date);
$amt_from_div = get_amount_received('Unit', $div_cond_proj, $from_date, $to_date);

function get_expense_total_by_keyword($keyword, $div_cond_cust, $from_date, $to_date) {
    global $db;
    $keyword = mysqli_real_escape_string($db, $keyword);
    $q = "SELECT SUM(CASE WHEN t.type='DR' THEN CAST(t.amount AS DECIMAL(14,2)) ELSE -CAST(t.amount AS DECIMAL(14,2)) END) as bal 
          FROM billit_customer_transactions t 
          JOIN billit_customer c ON t.cust_id = c.sno 
          WHERE c.cus_name LIKE '%$keyword%' $div_cond_cust
          AND SUBSTRING(t.timestamp,1,10) >= '$from_date' AND SUBSTRING(t.timestamp,1,10) <= '$to_date'";
    $res = execute_query($q);
    if($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        return (float)($row['bal']);
    }
    return 0;
}

$amt_from_security = get_expense_total_by_keyword('Security', $div_cond_cust, $from_date, $to_date); 
$amt_from_security = -$amt_from_security; 

// 2. Work A/c Expenses
$construction_exp = get_expense_total_by_keyword('Construction', $div_cond_cust, $from_date, $to_date);
$centage_paid = get_expense_total_by_keyword('Centage', $div_cond_cust, $from_date, $to_date);
$labour_cess = get_expense_total_by_keyword('Labour Cess', $div_cond_cust, $from_date, $to_date);
$advance_labour = get_expense_total_by_keyword('Advance Labour', $div_cond_cust, $from_date, $to_date);
$electric_conn = get_expense_total_by_keyword('Electric', $div_cond_cust, $from_date, $to_date);

$total_work_ac = $construction_exp + $centage_paid + $labour_cess + $advance_labour + $electric_conn;

// 3. Opening Balance of Nirman Work
function get_opening_nirman_work($div_cond_cust, $from_date) {
    $q = "SELECT SUM(CAST(opening_balance AS DECIMAL(14,2))) as op FROM billit_customer WHERE erp_code IS NOT NULL AND erp_code != '' $div_cond_cust";
    $res = execute_query($q);
    $base_op = 0;
    if($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $base_op = (float)($row['op']);
    }
    
    $q2 = "SELECT SUM(CASE WHEN t.type='DR' THEN CAST(t.amount AS DECIMAL(14,2)) ELSE -CAST(t.amount AS DECIMAL(14,2)) END) as tr_bal 
           FROM billit_customer_transactions t
           JOIN billit_customer c ON t.cust_id = c.sno
           WHERE c.erp_code IS NOT NULL AND c.erp_code != '' $div_cond_cust
           AND SUBSTRING(t.timestamp,1,10) < '$from_date'";
    $res2 = execute_query($q2);
    $tr_bal = 0;
    if($res2 && mysqli_num_rows($res2) > 0) {
        $row2 = mysqli_fetch_assoc($res2);
        $tr_bal = (float)($row2['tr_bal']);
    }
    
    return $base_op + $tr_bal;
}

$opening_nirman = get_opening_nirman_work($div_cond_cust, $from_date);
$opening_nirman_cr = -$opening_nirman;

$total_amount_received = $amt_from_ho + $amt_from_div + $amt_from_security;
$total_cr = $opening_nirman_cr + $total_amount_received;

// 4. Closing Balance of Nirman Work
$closing_nirman = $total_cr - $total_work_ac;
$total_dr = $total_work_ac + $closing_nirman;

page_header_start();
page_header_end();
page_sidebar();
?>
<style>
    .trading-table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .trading-table th, .trading-table td { border: 1px solid #dee2e6; padding: 10px; vertical-align: top; }
    .trading-table thead th { background: #343a40; color: #fff; text-align: center; }
    .trading-header { text-align: center; margin-bottom: 20px; }
    .trading-header h3 { margin: 0; font-weight: 700; color: #333; }
    .trading-header h5 { margin: 5px 0 0 0; color: #555; }
    .amt-col { width: 150px; text-align: right; }
    .total-row { background: #f8f9fa; font-weight: 700; }
    .grand-total { background: #e9ecef; font-weight: bold; }
    
    .tree-row { background: #fdfdfd; display: none; }
    .tree-item { padding-left: 30px; font-size: 13px; color: #555; border-bottom: 1px solid #eee; padding-top: 5px; padding-bottom: 5px;}
    .tree-item-div { padding-left: 20px; font-weight: bold; font-size: 13px; color: #333; cursor: pointer; border-bottom: 1px solid #eee; padding-top: 5px; padding-bottom: 5px; background: #f8f9fa;}
    
    .expandable { cursor: pointer; color: #0d6efd; transition: color 0.2s; }
    .expandable:hover { color: #0043a8; text-decoration: underline; }
    .chev { display: inline-block; font-size: 10px; transition: transform 0.2s; margin-right: 5px; }
    .chev.open { transform: rotate(90deg); }
    
    .loader-txt { font-size: 12px; color: #888; margin: 10px 0; text-align: center; }
    
    @media print {
        .btn, .sidebar, .navbar, .form-inline { display: none !important; }
        body, .main-panel { margin: 0; padding: 0; width: 100%; }
        .trading-table { box-shadow: none; border: 2px solid #000; }
        .trading-table th, .trading-table td { border: 1px solid #000 !important; }
        .trading-table thead th { background: #ddd !important; color: #000 !important; -webkit-print-color-adjust: exact; }
        .tree-row { display: table-row !important; }
    }
</style>

<div class="row">
    <div class="col-md-12">
        <form method="GET" class="form-inline mb-4 bg-white p-3 rounded shadow-sm border" id="invForm">
            <?php if ($is_sadmin || count($user_divisions) > 1): ?>
                <label class="mr-2 font-weight-bold">Division:</label>
                <select name="division_id" class="form-control mr-3" onchange="document.getElementById('invForm').submit()">
                    <?php if ($is_sadmin): ?>
                        <option value="all" <?php if($selected_division=='all') echo 'selected'; ?>>All Divisions</option>
                    <?php else: ?>
                        <option value="">Select Division</option>
                    <?php endif; ?>
                    <?php
                    $dRes = execute_query("SELECT s_no, division_name FROM uprnss_division ORDER BY division_name");
                    while($dRow = mysqli_fetch_assoc($dRes)) {
                        if ($is_sadmin || in_array($dRow['s_no'], $user_divisions)) {
                            $sel = ($selected_division == $dRow['s_no']) ? 'selected' : '';
                            echo '<option value="'.$dRow['s_no'].'" '.$sel.'>'.$dRow['division_name'].'</option>';
                        }
                    }
                    ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="division_id" value="<?php echo htmlspecialchars($selected_division); ?>">
            <?php endif; ?>

            <label class="mr-2 font-weight-bold">From:</label>
            <input type="date" name="from_date" class="form-control mr-3" value="<?php echo htmlspecialchars($from_date); ?>" onchange="document.getElementById('invForm').submit()">
            
            <label class="mr-2 font-weight-bold">To:</label>
            <input type="date" name="to_date" class="form-control mr-3" value="<?php echo htmlspecialchars($to_date); ?>" onchange="document.getElementById('invForm').submit()">

            <div class="ml-auto">
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fa fa-print mr-1"></i> Print Report
                </button>
            </div>
        </form>

        <div class="bg-white p-4 rounded shadow-sm border mb-4">
            <div class="trading-header">
                <h3>U.P. Rajya Nirman Sahkari Sangh Ltd</h3>
                <?php
                if ($selected_division != 'all') {
                    $divName = mysqli_fetch_assoc(execute_query("SELECT division_name FROM uprnss_division WHERE s_no='$selected_division'"));
                    echo '<h5>UPRNSS ' . strtoupper($divName['division_name']) . '</h5>';
                }
                ?>
                <h5>Trading A/c For the Year <?php echo date('Y', strtotime($from_date)) . '-' . date('y', strtotime($to_date)); ?></h5>
                <p class="mb-0"><?php echo date('d-m-Y', strtotime($from_date)); ?> TO <?php echo date('d-m-Y', strtotime($to_date)); ?></p>
            </div>

            <table class="trading-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Sl. N.</th>
                        <th>Particulars (Dr)</th>
                        <th class="amt-col">Amount</th>
                        <th style="width: 50px;">S. N.</th>
                        <th>Particulars (Cr)</th>
                        <th class="amt-col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <b>Work A/c Exp खर्चे</b><br>
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleExpense('Construction', this)"><i class="chev">▶</i>(i) Construction Work Exp</span>
                            <div id="cc-Construction" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                            
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleExpense('Centage', this)"><i class="chev">▶</i>(ii) Centage Paid</span>
                            <div id="cc-Centage" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                            
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleExpense('Labour Cess', this)"><i class="chev">▶</i>(iii) Labour Cess Paid</span>
                            <div id="cc-LabourCess" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                            
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleExpense('Advance Labour', this)"><i class="chev">▶</i>(iv) Advance Labour Cess Dr to Project</span>
                            <div id="cc-AdvanceLabour" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                            
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleExpense('Electric', this)"><i class="chev">▶</i>(v) Electric Conection</span>
                            <div id="cc-Electric" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                        </td>
                        <td class="text-right">
                            <br>
                            <?= number_format($construction_exp, 2) ?><br>
                            <?= number_format($centage_paid, 2) ?><br>
                            <?= number_format($labour_cess, 2) ?><br>
                            <?= number_format($advance_labour, 2) ?><br>
                            <?= number_format($electric_conn, 2) ?>
                        </td>
                        
                        <td class="text-center">1</td>
                        <td>
                            <span class="expandable" onclick="toggleOpening(this)"><i class="chev">▶</i><b>Opening Balance of Nirman Work</b><br>as on <?= date('d-m-y', strtotime($from_date)) ?></span>
                            <div id="cc-opening" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                        </td>
                        <td class="text-right"><br><?= number_format($opening_nirman_cr, 2) ?></td>
                    </tr>
                    
                    <tr class="total-row">
                        <td></td>
                        <td class="text-right">Total Work A/c</td>
                        <td class="text-right"><?= number_format($total_work_ac, 2) ?></td>
                        
                        <td class="text-center">2</td>
                        <td>
                            <b>Amount Received for Nirman Work during <?= date('Y', strtotime($from_date)) ?>-<?= date('y', strtotime($to_date)) ?></b><br>
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleReceived('HO', this)"><i class="chev">▶</i>From HO</span>
                            <div id="cc-rcv-HO" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                            
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleReceived('Unit', this)"><i class="chev">▶</i>From Division</span>
                            <div id="cc-rcv-Unit" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                            
                            &nbsp;&nbsp;&nbsp;<span class="expandable" onclick="toggleReceived('Security', this)"><i class="chev">▶</i>From Security</span>
                            <div id="cc-rcv-Security" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                        </td>
                        <td class="text-right">
                            <br>
                            <?= number_format($amt_from_ho, 2) ?><br>
                            <?= number_format($amt_from_div, 2) ?><br>
                            <?= number_format($amt_from_security, 2) ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="text-center">3</td>
                        <td>
                            <span class="expandable" onclick="toggleClosing(this)"><i class="chev">▶</i><b>Balance of Nirman Work (with details) on</b><br>dt <?= date('d-m-y', strtotime($to_date)) ?></span>
                            <div id="cc-closing" style="display:none; margin-top:5px; margin-bottom:10px;"></div>
                        </td>
                        <td class="text-right"><br><?= number_format($closing_nirman, 2) ?></td>
                        
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    
                    <tr class="grand-total">
                        <td></td>
                        <td class="text-center">Total</td>
                        <td class="text-right"><?= number_format($total_dr, 2) ?></td>
                        <td></td>
                        <td class="text-center">Total</td>
                        <td class="text-right"><?= number_format($total_cr, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="row mt-5">
                <div class="col-md-6 text-center">
                    <br><br>
                    <strong>ACCOUNTANT</strong>
                </div>
                <div class="col-md-6 text-center">
                    <br><br>
                    <strong>E.E</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var current_div = '<?php echo $selected_division; ?>';
    var f_date = '<?php echo $from_date; ?>';
    var t_date = '<?php echo $to_date; ?>';
    var _cache = {};

    function toggleExpense(keyword, el) {
        var safekey = keyword.replace(/\s+/g, '');
        var container = document.getElementById('cc-' + safekey);
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['exp_' + safekey]) return;
        _cache['exp_' + safekey] = true;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';
        
        fetch('?ajax=1&type=expense&keyword=' + encodeURIComponent(keyword) + '&division_id=' + encodeURIComponent(current_div) + '&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No records found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item d-flex justify-content-between">';
                    html += '<span>' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading data.</div>';
            });
    }

    function toggleReceived(receive_to, el) {
        var container = document.getElementById('cc-rcv-' + receive_to);
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['rcv_' + receive_to]) return;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';
        
        // If receive_to is HO or Security, it's not projects, but for 'Unit' we handle projects.
        // Wait, for Unit, if current_div == 'all', we show divisions. Else projects.
        if (receive_to === 'Unit' && current_div === 'all') {
            loadReceiveDivisions(receive_to, container);
        } else if (receive_to === 'Unit' && current_div !== 'all') {
            loadReceiveProjects(receive_to, current_div, container);
        } else {
            // Placeholder for HO / Security
            container.innerHTML = '<div class="tree-item text-muted">Detailed view not supported yet.</div>';
        }
    }
    
    function loadReceiveDivisions(receive_to, container) {
        _cache['rcv_' + receive_to] = true;
        fetch('?ajax=1&type=receive_divs&receive_to=' + encodeURIComponent(receive_to) + '&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No divisions found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item-div d-flex justify-content-between" onclick="toggleDivisionProjects(' + item.id + ', this)">';
                    html += '<span><i class="chev">▶</i> ' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                    html += '<div id="cc-div-projs-' + item.id + '" style="display:none; padding-left:15px;"></div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading divisions.</div>';
            });
    }

    function toggleDivisionProjects(div_id, el) {
        var container = document.getElementById('cc-div-projs-' + div_id);
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['div_proj_' + div_id]) return;
        _cache['div_proj_' + div_id] = true;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading projects...</div>';
        loadReceiveProjects('Unit', div_id, container);
    }
    
    function loadReceiveProjects(receive_to, div_id, container) {
        fetch('?ajax=1&type=receive_projs&receive_to=' + encodeURIComponent(receive_to) + '&div_id=' + encodeURIComponent(div_id) + '&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No projects found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item d-flex justify-content-between">';
                    html += '<span>' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading projects.</div>';
            });
    }

    function toggleOpening(el) {
        var container = document.getElementById('cc-opening');
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['opening']) return;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';
        
        if (current_div === 'all') {
            loadOpeningDivisions(container);
        } else {
            loadOpeningLedgers(current_div, container);
        }
    }
    
    function loadOpeningDivisions(container) {
        _cache['opening'] = true;
        fetch('?ajax=1&type=opening_divs&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No divisions found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item-div d-flex justify-content-between" onclick="toggleOpeningLedgers(' + item.id + ', this)">';
                    html += '<span><i class="chev">▶</i> ' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                    html += '<div id="cc-op-div-' + item.id + '" style="display:none; padding-left:15px;"></div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading divisions.</div>';
            });
    }
    
    function toggleOpeningLedgers(div_id, el) {
        var container = document.getElementById('cc-op-div-' + div_id);
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['op_div_' + div_id]) return;
        _cache['op_div_' + div_id] = true;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';
        loadOpeningLedgers(div_id, container);
    }
    
    function loadOpeningLedgers(div_id, container) {
        fetch('?ajax=1&type=opening_ledgers&div_id=' + encodeURIComponent(div_id) + '&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No mapped project ledgers found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item d-flex justify-content-between">';
                    html += '<span>' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading ledgers.</div>';
            });
    }

    function toggleClosing(el) {
        var container = document.getElementById('cc-closing');
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['closing']) return;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';
        
        if (current_div === 'all') {
            loadClosingDivisions(container);
        } else {
            loadClosingLedgers(current_div, container);
        }
    }
    
    function loadClosingDivisions(container) {
        _cache['closing'] = true;
        fetch('?ajax=1&type=closing_divs&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No divisions found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item-div d-flex justify-content-between" onclick="toggleClosingLedgers(' + item.id + ', this)">';
                    html += '<span><i class="chev">▶</i> ' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                    html += '<div id="cc-cl-div-' + item.id + '" style="display:none; padding-left:15px;"></div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading divisions.</div>';
            });
    }
    
    function toggleClosingLedgers(div_id, el) {
        var container = document.getElementById('cc-cl-div-' + div_id);
        var chev = el.querySelector('.chev');
        
        if (container.style.display === 'block') {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }
        
        container.style.display = 'block';
        if (chev) chev.classList.add('open');
        
        if (_cache['cl_div_' + div_id]) return;
        _cache['cl_div_' + div_id] = true;
        
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';
        loadClosingLedgers(div_id, container);
    }
    
    function loadClosingLedgers(div_id, container) {
        fetch('?ajax=1&type=closing_ledgers&div_id=' + encodeURIComponent(div_id) + '&from_date=' + f_date + '&to_date=' + t_date)
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="tree-item text-muted">No mapped project ledgers found.</div>';
                    return;
                }
                var html = '';
                data.forEach(item => {
                    html += '<div class="tree-item d-flex justify-content-between">';
                    html += '<span>' + esc(item.name) + '</span>';
                    html += '<span>' + fmt(item.amount) + '</span>';
                    html += '</div>';
                });
                container.innerHTML = html;
            }).catch(e => {
                container.innerHTML = '<div class="tree-item text-danger">Error loading ledgers.</div>';
            });
    }

    function fmt(v) {
        return parseFloat(v || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }
    function esc(t) {
        return String(t || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>

<?php
page_footer_start();
page_footer_end();
?>
