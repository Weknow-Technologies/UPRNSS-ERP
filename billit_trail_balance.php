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

function get_ledger_balance($db, $sno, $selected_division = '')
{
    $sno = (int) $sno;
    return get_cust_balace('1970-01-01', date('Y-m-d'), $sno, '', $selected_division);
}

function get_head_visibility_filter($selected_division)
{
    if ($selected_division === 'all') {
        return "1=1";
    } elseif ($selected_division !== '') {
        return "(visibility IS NULL OR visibility = '' OR visibility = 'public')";
    } else {
        return "1=0";
    }
}

function get_descendant_total_balance($db, $headSno, $parentLedgerSno, $selected_division)
{
    $total = 0.0;
    $children = [];
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $p = mysqli_real_escape_string($db, (string) $parentLedgerSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);

    $res = mysqli_query($db, "SELECT sno, cus_name FROM billit_customer WHERE parent='$h' AND parent_ledger='$p' AND $ledger_filter ORDER BY cus_name ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $sno = (int) $row['sno'];
        $bal = get_ledger_balance($db, $sno, $selected_division);
        $desc = get_descendant_total_balance($db, $headSno, $sno, $selected_division);

        $ledg_total = $bal + $desc['total'];
        if ($ledg_total != 0) {
            $total += $ledg_total;
            $children[] = [
                'type' => 'ledger',
                'name' => htmlspecialchars($row['cus_name']),
                'dr' => ($ledg_total > 0) ? abs($ledg_total) : 0,
                'cr' => ($ledg_total < 0) ? abs($ledg_total) : 0,
                'children' => $desc['children']
            ];
        }
    }
    return ['total' => $total, 'children' => $children];
}

function get_head_total_balance($db, $headSno, $selected_division)
{
    $total = 0.0;
    $children = [];
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);

    $head_vis = get_head_visibility_filter($selected_division);
    $resSub = mysqli_query(
        $db,
        "SELECT sno, description FROM billit_pl_heads h
   WHERE h.parent='$h'
     AND $head_vis
   ORDER BY h.sort_no+0, h.sno ASC"
    );
    while ($row = mysqli_fetch_assoc($resSub)) {
        $sub = get_head_total_balance($db, (int) $row['sno'], $selected_division);
        if ($sub['total'] != 0) {
            $total += $sub['total'];
            $children[] = [
                'type' => 'head',
                'name' => htmlspecialchars($row['description']),
                'dr' => ($sub['total'] > 0) ? abs($sub['total']) : 0,
                'cr' => ($sub['total'] < 0) ? abs($sub['total']) : 0,
                'children' => $sub['children']
            ];
        }
    }

    $res = mysqli_query($db, "SELECT sno, cus_name FROM billit_customer WHERE parent='$h' AND (parent_ledger IS NULL OR parent_ledger='' OR parent_ledger='0') AND $ledger_filter ORDER BY cus_name ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $sno = (int) $row['sno'];
        $bal = get_ledger_balance($db, $sno, $selected_division);
        $desc = get_descendant_total_balance($db, $headSno, $sno, $selected_division);

        $ledg_total = $bal + $desc['total'];
        if ($ledg_total != 0) {
            $total += $ledg_total;
            $children[] = [
                'type' => 'ledger',
                'name' => htmlspecialchars($row['cus_name']),
                'dr' => ($ledg_total > 0) ? abs($ledg_total) : 0,
                'cr' => ($ledg_total < 0) ? abs($ledg_total) : 0,
                'children' => $desc['children']
            ];
        }
    }

    return ['total' => $total, 'children' => $children];
}

function render_tb_rows($nodes, $parentId, $level, &$global_id, &$sno_counter)
{
    $html = '';
    foreach ($nodes as $node) {
        $myId = ++$global_id;
        $is_root = ($parentId === 0);
        $class_str = $is_root ? "tb-node root-node" : "tb-node child-node parent-$parentId";
        $display = $is_root ? "" : "style='display:none;'";

        $has_children = !empty($node['children']);

        $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level);
        $icon = '';
        $row_attr = '';

        if ($has_children) {
            $icon = "<i class='chev toggle-icon' id='chev-$myId'>&#9658;</i>";
            $row_attr = "style='cursor:pointer;' onclick='toggleRow($myId)'";
            $class_str .= " table-hover-row";
        } elseif ($node['type'] == 'ledger') {
            $icon = "<i class='fa fa-angle-double-right text-muted' style='font-size:11px; margin-right:8px;'></i>";
        } else {
            $icon = "<span style='display:inline-block; width:16px;'></span>";
        }

        $dr_text = $node['dr'] > 0 ? number_format($node['dr'], 2) : '-';
        $cr_text = $node['cr'] > 0 ? number_format($node['cr'], 2) : '-';
        $dr_class = $node['dr'] > 0 ? "text-primary" : "text-muted";
        $cr_class = $node['cr'] > 0 ? "text-success" : "text-muted";

        $name_style = ($node['type'] === 'Group' || $node['type'] === 'head') ? "font-weight:bold;" : "";

        $sno_display = $is_root ? $sno_counter++ : '';

        $html .= "<tr class='$class_str' data-id='$myId' $display $row_attr>";
        $html .= "<td class='text-center'>$sno_display</td>";
        $html .= "<td style='$name_style'>$indent $icon " . $node['name'] . "</td>";
        $html .= "<td class='amt-col $dr_class'>$dr_text</td>";
        $html .= "<td class='amt-col $cr_class'>$cr_text</td>";
        $html .= "</tr>";

        if ($has_children) {
            $html .= render_tb_rows($node['children'], $myId, $level + 1, $global_id, $sno_counter);
        }
    }
    return $html;
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

// Fetch Trial Balance Data
$trial_balance = [];
$total_debit = 0.0;
$total_credit = 0.0;

if ($selected_division !== '' || $is_sadmin) {
    $only_public = !($selected_division == 'all'
        || $selected_division == '53'
        || $selected_division == '');
    $pub_clause = $only_public
        ? "AND h.visibility = 'public'" : "";

    $sql = "SELECT h.sno, h.description, h.fund_type
        FROM billit_pl_heads h
        WHERE (h.parent IS NULL OR h.parent='' OR h.parent='0' OR h.parent=0)
          AND h.sno NOT IN (4,5,6,7,12,13)
          $pub_clause
        ORDER BY h.sort_no+0, h.sno ASC";
    $res = execute_query($sql);

    while ($row = mysqli_fetch_assoc($res)) {
        $headSno = (int) $row['sno'];

        $hier = get_head_total_balance($db, $headSno, $selected_division);
        $balance = $hier['total'];

        if ($balance == 0) {
            continue;
        }

        $dr = ($balance > 0) ? abs($balance) : 0;
        $cr = ($balance < 0) ? abs($balance) : 0;

        $trial_balance[] = [
            'name' => htmlspecialchars($row['description']),
            'type' => 'Group',
            'dr' => $dr,
            'cr' => $cr,
            'children' => $hier['children']
        ];

        $total_debit += $dr;
        $total_credit += $cr;
    }
}

// Check if Trial Balance matches
$diff = abs($total_debit - $total_credit);
$is_balanced = ($diff < 0.01);

page_header_start();
page_header_end();
page_sidebar();
?>

<style>
    .tb-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }

    .chev {
        display: inline-block;
        font-size: 10px;
        color: #0d6efd;
        margin-right: 8px;
        transition: transform .15s;
    }

    .chev.open {
        transform: rotate(90deg);
    }

    .tb-header {
        background: #2c3e50;
        color: #fff;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tb-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .tb-table th {
        background: #5e5555ff;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .tb-table td {
        vertical-align: middle;
        font-size: 14px;
    }

    .amt-col {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .tb-total-row {
        background: #eef2f5 !important;
        font-weight: 700;
    }

    .tb-total-row td {
        font-size: 15px;
        border-top: 2px solid #cbd3da !important;
        border-bottom: 2px solid #cbd3da !important;
    }

    .bal-status {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
    }

    .bal-match {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .bal-diff {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media print {

        .btn,
        .sidebar,
        .navbar,
        .form-inline {
            display: none !important;
        }

        .tb-card {
            box-shadow: none;
            border: none;
        }

        .tb-header {
            background: #fff !important;
            color: #000 !important;
            border-bottom: 2px solid #000;
        }
    }
</style>

<div class="row">
    <div class="col-md-12">

        <?php if ($is_sadmin || count($user_divisions) > 1): ?>
            <form method="GET" class="form-inline mb-4 bg-white p-3 rounded shadow-sm border" id="divForm">
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
            <div class="alert alert-info shadow-sm">
                <i class="fa fa-info-circle mr-2"></i> Please select a division from the dropdown above to view the Trial
                Balance.
            </div>
        <?php else: ?>

            <div class="tb-card mb-5">
                <div class="tb-header">
                    <h4><i class="fa fa-balance-scale mr-2"></i> Trial Balance</h4>
                    <div>
                        <?php if ($is_balanced): ?>
                            <span class="bal-status bal-match mr-3"><i class="fa fa-check-circle mr-1"></i> Balanced</span>
                        <?php else: ?>
                            <span class="bal-status bal-diff mr-3"><i class="fa fa-exclamation-triangle mr-1"></i> Diff:
                                <?php echo number_format($diff, 2); ?></span>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-light" onclick="window.print()">
                            <i class="fa fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 tb-table">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">S.No.</th>
                                <th width="65%">Account Group / Ledger Name</th>
                                <th width="15%" class="text-right">Debit (Dr) ₹</th>
                                <th width="15%" class="text-right">Credit (Cr) ₹</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($trial_balance)) {
                                echo '<tr><td colspan="4" class="text-center py-4 text-muted">No balances found for the selected division.</td></tr>';
                            } else {
                                $global_id = 0;
                                $sno_counter = 1;
                                echo render_tb_rows($trial_balance, 0, 0, $global_id, $sno_counter);
                            }
                            ?>

                            <?php if (!empty($trial_balance)): ?>
                                <tr class="tb-total-row">
                                    <td colspan="2" class="text-right pr-4">GRAND TOTAL</td>
                                    <td class="amt-col text-primary"><?php echo number_format($total_debit, 2); ?></td>
                                    <td class="amt-col text-success"><?php echo number_format($total_credit, 2); ?></td>
                                </tr>
                                <?php if (!$is_balanced): ?>
                                    <tr>
                                        <td colspan="2" class="text-right pr-4 text-danger"><strong>DIFFERENCE IN TRIAL
                                                BALANCE</strong></td>
                                        <?php if ($total_debit > $total_credit): ?>
                                            <td class="amt-col text-muted">-</td>
                                            <td class="amt-col text-danger"><strong><?php echo number_format($diff, 2); ?></strong></td>
                                        <?php else: ?>
                                            <td class="amt-col text-danger"><strong><?php echo number_format($diff, 2); ?></strong></td>
                                            <td class="amt-col text-muted">-</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr class="tb-total-row">
                                        <td colspan="2" class="text-right pr-4">ADJUSTED TOTAL</td>
                                        <?php $max_total = max($total_debit, $total_credit); ?>
                                        <td class="amt-col text-primary"><?php echo number_format($max_total, 2); ?></td>
                                        <td class="amt-col text-success"><?php echo number_format($max_total, 2); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
page_footer_start();
?>
<script>
    function toggleRow(parentId) {
        var iconEl = document.getElementById('chev-' + parentId);
        if (!iconEl) return;

        var isExpanded = iconEl.classList.contains('open');
        if (isExpanded) {
            // Collapse
            iconEl.classList.remove('open');
            collapseNode(parentId);
        } else {
            // Expand
            iconEl.classList.add('open');
            var children = document.querySelectorAll('.parent-' + parentId);
            children.forEach(function (child) {
                child.style.display = '';
            });
        }
    }

    function collapseNode(parentId) {
        var children = document.querySelectorAll('.parent-' + parentId);
        children.forEach(function (child) {
            child.style.display = 'none';
            var childId = child.getAttribute('data-id');
            var childIcon = document.getElementById('chev-' + childId);
            if (childIcon && childIcon.classList.contains('open')) {
                childIcon.classList.remove('open');
                collapseNode(childId);
            }
        });
    }
</script>
<?php
page_footer_end();
?>