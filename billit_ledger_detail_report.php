<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");
set_time_limit(0);
$msg = '';

function get_ledger_filter($db, $selected_division)
{
    if ($selected_division === 'all') {
        return "1=1";
    } elseif ($selected_division !== '') {
        $sd_esc = mysqli_real_escape_string($db, $selected_division);
        return "(visibility='public' OR unit_id='$sd_esc')";
    } else {
        return "1=0";
    }
}

function get_head_visibility_filter($selected_division)
{
    if ($selected_division === 'all') {
        return "1=1";
    } elseif ($selected_division !== '') {
        return "(h.visibility IS NULL OR h.visibility = '' OR h.visibility = 'public')";
    } else {
        return "1=0";
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');

    $type = isset($_GET['type']) ? trim($_GET['type']) : 'head';
    $parentId = isset($_GET['parent']) ? trim($_GET['parent']) : '0';
    $headSno = isset($_GET['head']) ? trim($_GET['head']) : $parentId;
    $selected_division = isset($_GET['division_id']) ? trim($_GET['division_id']) : '';

    $ledger_filter = get_ledger_filter($db, $selected_division);
    $head_vis_filter = get_head_visibility_filter($selected_division);

    $records = [];

    if ($type === 'head') {
        $headSno = $parentId;

        $pidEsc = mysqli_real_escape_string($db, $parentId);
        $sqlSub = "
        SELECT h.sno, h.description
        FROM billit_pl_heads h
        WHERE h.parent = '$pidEsc'
          AND $head_vis_filter
        ORDER BY h.sort_no+0, h.sno ASC
    ";
        $resSub = execute_query($sqlSub);
        while ($sub = mysqli_fetch_assoc($resSub)) {
            $hid = (int) $sub['sno'];
            $records[] = [
                'entity_type' => 'pl_head',
                'sno' => $hid,
                'cus_name' => $sub['description'],
                'fname' => '',
                'mobile' => '',
                'parent_type' => 'Sub Parent',
                'opening_balance' => 0.0,
                'child_balance' => 0.0,
                'total_balance' => abs(get_head_total_balance($db, $hid, $selected_division)),
                'has_children' => node_has_children_under_head($db, $hid, $selected_division),
                'head_sno' => $hid
            ];
        }

        $sql = "SELECT sno, cus_name, fname, mobile, parent_ledger
                FROM billit_customer
                WHERE parent='" . mysqli_real_escape_string($db, $parentId) . "'
                  AND (parent_ledger IS NULL OR parent_ledger='' OR parent_ledger='0')
                  AND $ledger_filter
                ORDER BY cus_name ASC";
    } else {
        $sql = "SELECT sno, cus_name, fname, mobile, parent_ledger
                FROM billit_customer
                WHERE parent='" . mysqli_real_escape_string($db, $headSno) . "'
                  AND parent_ledger='" . mysqli_real_escape_string($db, $parentId) . "'
                  AND $ledger_filter
                ORDER BY cus_name ASC";
    }

    $res = execute_query($sql);

    while ($row = mysqli_fetch_assoc($res)) {
        $sno = $row['sno'];

        $ownBal = get_cust_balace('1970-01-01', date('Y-m-d'), $sno, '', $selected_division);

        $childBal = get_descendant_balance($db, $headSno, $sno, $selected_division);
        $parentLedger = isset($row['parent_ledger']) ? trim((string) $row['parent_ledger']) : '0';
        $parentType = ($parentLedger === '' || $parentLedger === '0') ? 'Super Parent' : 'Sub Parent';

        $records[] = [
            'entity_type' => 'ledger',
            'sno' => $sno,
            'cus_name' => $row['cus_name'],
            'fname' => $row['fname'],
            'mobile' => $row['mobile'],
            'parent_type' => $parentType,
            'opening_balance' => $ownBal,
            'child_balance' => $childBal,
            'total_balance' => ($ownBal + $childBal),
            'has_children' => has_ledger_children($db, $headSno, $sno, $selected_division),
            'head_sno' => $headSno
        ];
    }

    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($records, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function get_descendant_balance($db, $headSno, $parentLedgerSno, $selected_division)
{
    $total = 0;
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $p = mysqli_real_escape_string($db, (string) $parentLedgerSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);

    $res = mysqli_query($db, "SELECT sno FROM billit_customer
        WHERE parent='$h' AND parent_ledger='$p' AND $ledger_filter");

    while ($row = mysqli_fetch_assoc($res)) {
        $sno = intval($row['sno']);

        $total += get_cust_balace('1970-01-01', date('Y-m-d'), $sno, '', $selected_division);
        $total += get_descendant_balance($db, $headSno, $sno, $selected_division);
    }
    return $total;
}

function get_head_total_balance($db, $headSno, $selected_division)
{
    $total = 0;
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);
    $head_vis_filter = get_head_visibility_filter($selected_division);
    $head_vis_filter_bare = str_replace('h.', '', $head_vis_filter);

    $res = mysqli_query($db, "SELECT sno FROM billit_customer
        WHERE parent='$h'
          AND (parent_ledger IS NULL OR parent_ledger='' OR parent_ledger='0')
          AND $ledger_filter");

    while ($row = mysqli_fetch_assoc($res)) {
        $sno = intval($row['sno']);

        $total += get_cust_balace('1970-01-01', date('Y-m-d'), $sno, '', $selected_division);
        $total += get_descendant_balance($db, $headSno, $sno, $selected_division);
    }
    $resSub = mysqli_query($db, "SELECT h.sno FROM billit_pl_heads h WHERE h.parent='$h' AND $head_vis_filter");
    while ($row = mysqli_fetch_assoc($resSub)) {
        $total += get_head_total_balance($db, (int) $row['sno'], $selected_division);
    }

    return $total;
}

function has_ledger_children($db, $headSno, $customerSno, $selected_division)
{
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $c = mysqli_real_escape_string($db, (string) $customerSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);

    $res = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM billit_customer
        WHERE parent='$h' AND parent_ledger='$c' AND $ledger_filter");
    $row = mysqli_fetch_assoc($res);
    return ((int) $row['cnt']) > 0;
}

function node_has_children_under_head($db, $headSno, $selected_division)
{
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);
    $head_vis_filter = get_head_visibility_filter($selected_division);

    $r = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM billit_pl_heads h WHERE h.parent='$h' AND $head_vis_filter");
    $row = mysqli_fetch_assoc($r);
    if (((int) $row['cnt']) > 0) {
        return true;
    }

    $r = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM billit_customer
        WHERE parent='$h'
          AND (parent_ledger IS NULL OR parent_ledger='' OR parent_ledger='0')
          AND $ledger_filter");
    $row = mysqli_fetch_assoc($r);

    return ((int) $row['cnt']) > 0;
}

function fetch_heads_by_type($db, $fund_type, $selected_division, $only_public = false)
{
    $ft = mysqli_real_escape_string($db, $fund_type);

    if ($only_public) {
        $where = "h.fund_type = '$ft'
          AND h.sno NOT IN (4, 5, 6, 7, 12, 13)
          AND h.visibility = 'public'
          AND (h.parent IS NULL OR h.parent = '' OR h.parent = '0' OR h.parent = 0)";
    } else {
        $where = "h.fund_type = '$ft'
          AND h.sno NOT IN (4, 5, 6, 7, 12, 13)
          AND (h.parent IS NULL OR h.parent = '' OR h.parent = '0' OR h.parent = 0)";
    }

    $res = mysqli_query($db, "
        SELECT h.sno, h.description
        FROM billit_pl_heads h
        WHERE $where
        ORDER BY h.sort_no+0, h.sno ASC
    ");
    $heads = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $hid = $row['sno'];
        $heads[] = [
            'sno' => $hid,
            'description' => $row['description'],
            'total_balance' => get_head_total_balance($db, $hid, $selected_division)
        ];
    }
    return $heads;
}

$is_sadmin = isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'sadmin';
$user_divisions = (isset($_SESSION['divisions']) && is_array($_SESSION['divisions'])) ? $_SESSION['divisions'] : [];

$selected_division = isset($_GET['division_id']) ? $_GET['division_id'] : '';

if ($is_sadmin) {
    if ($selected_division === '')
        $selected_division = 'all';
} else {
    if (count($user_divisions) == 1) {
        $selected_division = $user_divisions[0];
    } else {
        if ($selected_division !== '' && !in_array($selected_division, $user_divisions)) {
            $selected_division = '';
        }
    }
}

if ($selected_division == '53' || $selected_division == 'all' || $selected_division == '') {
    $source_heads = fetch_heads_by_type($db, 'source', $selected_division);
    $application_heads = fetch_heads_by_type($db, 'application', $selected_division);
} else {
    $source_heads = fetch_heads_by_type($db, 'source', $selected_division, true);
    $application_heads = fetch_heads_by_type($db, 'application', $selected_division, true);
}

$pl_sno_list = [4, 5, 6, 7, 12, 13];
$net_pl_balance = 0;
foreach ($pl_sno_list as $pl_sno) {
    $net_pl_balance += get_head_total_balance($db, $pl_sno, $selected_division);
}

if (abs($net_pl_balance) > 0.001) {
    // If net_pl_balance is negative (Credit > Debit), it means PROFIT. 
    // Profit is a Source of Funds. We show it as positive on Source side.
    $pl_node = [
        'sno' => 'pl',
        'description' => '► (Sch-C) Profit & Loss',
        'total_balance' => $net_pl_balance,
        'is_pl_node' => true
    ];
    if ($net_pl_balance < 0) {
        $source_heads[] = $pl_node;
    } else {
        $application_heads[] = $pl_node;
    }
}

$source_total_abs = abs(array_sum(array_column($source_heads, 'total_balance')));
$application_total_abs = abs(array_sum(array_column($application_heads, 'total_balance')));

$source_total = $source_total_abs;
$application_total = $application_total_abs;

$diff_source = 0;
$diff_application = 0;

if ($source_total > $application_total) {
    $diff_application = $source_total - $application_total;
    $application_total_abs += $diff_application;
} elseif ($application_total > $source_total) {
    $diff_source = $application_total - $source_total;
    $source_total_abs += $diff_source;
}

$source_total = $source_total_abs;
$application_total = $application_total_abs;

function render_heads($heads)
{
    $html = '';
    foreach ($heads as $h) {
        $sno = $h['sno'];
        $desc = htmlspecialchars($h['description']);
        $total = number_format(abs((float) $h['total_balance']), 2);

        if (isset($h['is_pl_node']) && $h['is_pl_node']) {
            $div_param = isset($_GET['division_id']) ? '?division_id=' . urlencode($_GET['division_id']) : '';
            $html .= '
            <div class="ledger-node">
                <div class="ledger-card head-card clickable" onclick="window.location.href=\'billit_profit_loss_report.php' . $div_param . '\'">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>' . $desc . '</strong>
                        </div>
                        <div class="amt-col">
                            <div class="bal-lbl">Total</div>
                            <strong class="text-primary">' . $total . '</strong>
                        </div>
                    </div>
                </div>
                <div class="child-container" style="display:block; margin-left:20px; padding-left:12px; border-left:2px solid #dee2e6;">
                    <div class="ledger-node">
                        <div class="ledger-card clickable" onclick="window.location.href=\'billit_profit_loss_report.php' . $div_param . '\'">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="no-dot"></span>
                                    <strong>►Profit for the Year (Group)</strong>
                                </div>
                                <div class="amt-col">
                                    <div class="bal-lbl">Total</div>
                                    <strong class="text-primary">' . $total . '</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            continue;
        }

        $html .= '
        <div class="ledger-node">
            <div class="ledger-card head-card clickable" onclick="toggleNode(\'h-' . $sno . '\',' . $sno . ',\'head\',' . $sno . ')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="chev" id="chev-h-' . $sno . '">&#9658;</i>
                        <strong>' . $desc . '</strong>
                        <a href="billit_edit_head.php?id=' . $sno . '" class="edit-link ml-2" title="Edit Head" onclick="event.stopPropagation();"><i class="fa fa-edit text-muted"></i></a>
                    </div>
                    <div class="amt-col">
                        <div class="bal-lbl">Total</div>
                        <strong class="text-primary">' . $total . '</strong>
                    </div>
                </div>
            </div>
            <div class="child-container" id="cc-h-' . $sno . '"></div>
        </div>';
    }
    return $html;
}

page_header_start();
page_header_end();
page_sidebar();
?>

<?php if ($msg != ''): ?>
    <h5><?php echo alert($msg); ?></h5>
<?php endif; ?>

<style>
    * {
        box-sizing: border-box;
    }

    .bs-wrap {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .bs-header {
        background: #343a40;
        color: #fff;
        padding: 12px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .bs-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .bs-body {
        display: flex;
        align-items: stretch;
        min-height: 100px;
    }

    .bs-side {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .side-title {
        background: #495057;
        color: #fff;
        padding: 8px 14px;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: .3px;
        text-align: center;
        border-bottom: 2px solid #343a40;
    }

    .side-content {
        flex: 1;
        padding: 10px 12px;
    }

    .bs-divider {
        width: 2px;
        background: #dee2e6;
        flex-shrink: 0;
    }

    .side-total {
        background: #343a40;
        color: #fff;
        padding: 9px 14px;
        font-weight: 700;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ledger-card {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background: #fff;
        margin-bottom: 5px;
        padding: 8px 12px;
        transition: border-color .12s, background .12s;
    }

    .ledger-card:hover {
        border-color: #aac4f0;
        background: #f8faff;
    }

    .head-card {
        background: #e8f0fe;
        border-color: #b6cff5;
        font-weight: 700;
    }

    .head-card:hover {
        background: #dce8fd;
    }

    .child-container {
        margin-left: 20px;
        padding-left: 12px;
        border-left: 2px solid #dee2e6;
        margin-top: 3px;
        display: none;
    }

    .clickable {
        cursor: pointer;
    }

    .amt-col {
        text-align: right;
        min-width: 140px;
        font-family: monospace;
    }

    .bal-lbl {
        font-size: 10px;
        color: #6c757d;
    }

    .loader-txt {
        font-size: 11px;
        color: #888;
        padding: 5px 3px;
    }

    .chev {
        display: inline-block;
        font-size: 9px;
        color: #0d6efd;
        margin-right: 5px;
        transition: transform .15s;
    }

    .chev.open {
        transform: rotate(90deg);
    }

    .no-dot {
        display: inline-block;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #ccc;
        margin-right: 8px;
        vertical-align: middle;
    }

    .edit-link {
        text-decoration: none !important;
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .edit-link:hover {
        opacity: 1;
    }

    .edit-link i {
        font-size: 12px;
    }

    @media print {

        .bs-header .btn,
        .sidebar,
        .navbar {
            display: none !important;
        }

        .bs-wrap {
            box-shadow: none;
        }

        .child-container {
            display: block !important;
        }
    }
</style>

<div class="row">
    <div class="col-md-12">
        <?php if ($is_sadmin || count($user_divisions) > 1): ?>
            <form method="GET" class="form-inline mb-3 bg-white p-3 rounded shadow-sm border" id="divForm">
                <label class="mr-2 font-weight-bold"><i class="fa fa-building mr-2"></i>Select Division : </label>
                <select name="division_id" class="form-control mr-3" onchange="document.getElementById('divForm').submit()">
                    <?php
                    if ($is_sadmin) {
                        echo '<option value="all" ' . ($selected_division == 'all' ? 'selected' : '') . '>All Divisions</option>';
                        $sql_div = "SELECT s_no, division_name FROM uprnss_division ORDER BY division_name";
                        $res_div = execute_query($sql_div);
                        while ($div = mysqli_fetch_assoc($res_div)) {
                            echo '<option value="' . $div['s_no'] . '" ' . ($selected_division == $div['s_no'] ? 'selected' : '') . '>' . $div['division_name'] . '</option>';
                        }
                    } else {
                        echo '<option value="">-- Select Division --</option>';
                        if (count($user_divisions) > 0) {
                            $div_in = implode(',', $user_divisions);
                            $sql_div = "SELECT s_no, division_name FROM uprnss_division WHERE s_no IN ($div_in) ORDER BY division_name";
                            $res_div = execute_query($sql_div);
                            while ($div = mysqli_fetch_assoc($res_div)) {
                                echo '<option value="' . $div['s_no'] . '" ' . ($selected_division == $div['s_no'] ? 'selected' : '') . '>' . $div['division_name'] . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </form>
        <?php endif; ?>

        <?php if ($selected_division === '' && !$is_sadmin): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle mr-2"></i> Please select a division from the dropdown above to view the balance
                sheet.
            </div>
        <?php else: ?>
            <div class="bs-wrap">

                <div class="bs-header">
                    <h5><i class="fa fa-balance-scale mr-2"></i>Balance Sheet — Ledger Tree</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-light mr-1" onclick="expandAll()">
                            <i class="fa fa-expand mr-1"></i>Expand All
                        </button>
                        <button class="btn btn-sm btn-outline-light mr-1" onclick="collapseAll()">
                            <i class="fa fa-compress mr-1"></i>Collapse All
                        </button>
                        <button class="btn btn-sm btn-outline-light" onclick="window.print()">
                            <i class="fa fa-print mr-1"></i>Print
                        </button>
                    </div>
                </div>

                <div class="bs-body">

                    <div class="bs-side">
                        <div class="side-title">SOURCE OF FUNDS</div>
                        <div class="side-content" id="sourceTree">
                            <?php echo render_heads($source_heads); ?>
                            <?php if ($diff_source > 0): ?>
                                <div class="ledger-node mt-2">
                                    <div class="ledger-card head-card"
                                        style="background:#fff3f3; border-color:#f5c2c7; cursor:default;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-danger">
                                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                                <strong>Difference in Opening Balance</strong>
                                            </div>
                                            <div class="amt-col">
                                                <div class="bal-lbl text-danger">Total</div>
                                                <strong
                                                    class="text-danger"><?php echo number_format($diff_source, 2); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="side-total">
                            <span>TOTAL</span>
                            <span><?php echo number_format($source_total, 2); ?></span>
                        </div>
                    </div>

                    <div class="bs-divider"></div>

                    <div class="bs-side">
                        <div class="side-title">APPLICATION OF FUNDS</div>
                        <div class="side-content" id="applicationTree">
                            <?php echo render_heads($application_heads); ?>
                            <?php if ($diff_application > 0): ?>
                                <div class="ledger-node mt-2">
                                    <div class="ledger-card head-card"
                                        style="background:#fff3f3; border-color:#f5c2c7; cursor:default;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-danger">
                                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                                <strong>Difference in Opening Balance</strong>
                                            </div>
                                            <div class="amt-col">
                                                <div class="bal-lbl text-danger">Total</div>
                                                <strong
                                                    class="text-danger"><?php echo number_format($diff_application, 2); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="side-total">
                            <span>TOTAL</span>
                            <span><?php echo number_format($application_total, 2); ?></span>
                        </div>
                    </div>

                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    var _loaded = {};

    function toggleNode(nodeKey, parentId, type, headSno) {
        var container = document.getElementById('cc-' + nodeKey);
        var chev = document.getElementById('chev-' + nodeKey);
        if (!container) return;

        var isOpen = container.style.display === 'block';
        if (isOpen) {
            container.style.display = 'none';
            if (chev) chev.classList.remove('open');
            return;
        }

        container.style.display = 'block';
        if (chev) chev.classList.add('open');

        if (_loaded[nodeKey]) return;
        _loaded[nodeKey] = true;
        container.innerHTML = '<div class="loader-txt"><i class="fa fa-spinner fa-spin mr-1"></i>Loading...</div>';

        var url = '<?php echo $_SERVER['PHP_SELF']; ?>?ajax=1'
            + '&type=' + encodeURIComponent(type)
            + '&parent=' + encodeURIComponent(parentId)
            + '&head=' + encodeURIComponent(headSno)
            + '&division_id=<?php echo urlencode($selected_division); ?>';

        fetch(url)
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<div class="text-muted loader-txt">No records found.</div>';
                    return;
                }

                var html = '';
                data.forEach(function (row) {
                    var hSno = row.head_sno;
                    var hasCh = !!row.has_children;
                    var isPlHead = row.entity_type === 'pl_head';
                    var isLedger = row.entity_type === 'ledger' || !row.entity_type;
                    var nKey = isPlHead ? ('ph-' + row.sno) : ('c-' + row.sno);

                    html += '<div class="ledger-node">';
                    html += '<div class="ledger-card' + (hasCh ? ' clickable' : '') + '"' + (hasCh ? ' onclick="' + (isPlHead ? 'toggleNode(\'' + nKey + '\',' + row.sno + ',\'head\',' + row.sno + ')' : 'toggleNode(\'' + nKey + '\',' + row.sno + ',\'customer\',' + hSno + ')') + '"' : '') + '>';
                    html += '<div class="d-flex justify-content-between align-items-center">';
                    html += '<div>';
                    html += hasCh
                        ? '<i class="chev" id="chev-' + nKey + '">&#9658;</i>'
                        : '<span class="no-dot"></span>';
                    html += '<strong>' + esc(row.cus_name) + '</strong>';

                    if (isPlHead) {
                        html += ' <a href="billit_edit_head.php?id=' + row.sno + '" class="edit-link ml-1" title="Edit Group" onclick="event.stopPropagation();"><i class="fa fa-edit text-muted"></i></a>';
                    } else {
                        html += ' <a href="billit_ledgers.php?id=' + row.sno + '" class="edit-link ml-1" title="Edit Ledger" onclick="event.stopPropagation();"><i class="fa fa-edit text-muted"></i></a>';
                    }

                    if (isLedger && row.parent_type) {
                        html += ' <span style="font-size:11px;color:#6c757d;">(' + esc(row.parent_type) + ')</span>';
                    }
                    if (isPlHead) {
                        html += ' <span style="font-size:11px;color:#6c757d;">(Group)</span>';
                    }
                    if (row.fname) html += ' <span style="font-size:11px;color:#999;">| ' + esc(row.fname) + '</span>';
                    if (row.mobile) html += ' <span style="font-size:11px;color:#999;">| ' + esc(row.mobile) + '</span>';
                    html += '</div>';
                    html += '<div class="amt-col"><div class="bal-lbl">Total</div>';
                    html += '<strong class="text-primary">' + fmt(Math.abs(row.total_balance)) + '</strong></div>';
                    html += '</div></div>';
                    html += '<div class="child-container" id="cc-' + nKey + '"></div>';
                    html += '</div>';
                });

                container.innerHTML = html;
            })
            .catch(function (err) {
                container.innerHTML = '<div class="text-danger loader-txt"><i class="fa fa-times mr-1"></i>Error: ' + err.message + '</div>';
                console.error(err);
            });
    }

    function expandAll() {
        ['sourceTree', 'applicationTree'].forEach(function (id) {
            document.querySelectorAll('#' + id + ' .ledger-card.clickable').forEach(function (card) {
                var chev = card.querySelector('.chev');
                if (chev && !chev.classList.contains('open')) {
                    card.click();
                }
            });
        });
    }

    function collapseAll() {
        document.querySelectorAll('.child-container').forEach(function (c) { c.style.display = 'none'; });
        document.querySelectorAll('.chev').forEach(function (c) { c.classList.remove('open'); });
    }

    function fmt(v) {
        return parseFloat(v || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function esc(t) {
        return String(t || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>

<?php
page_footer_start();
page_footer_end();
?>