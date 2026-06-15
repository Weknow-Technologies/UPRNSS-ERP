<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");
set_time_limit(0);
$msg = '';


function get_trial_balance_details($db, $sno, $from, $to, $division) {
    // We will do exact same sum as get_cust_balace but purely mathematical.
    $unit_filter = "";
    if ($division != '' && $division != 'all') {
        $unit_filter = " and unit_id='" . mysqli_real_escape_string($db, $division) . "'";
    }
    
    // Fetch initial opening balance from master
    $sql = 'select opening_balance, parent from billit_customer where sno=' . (int)$sno;
    $customer = mysqli_fetch_array(mysqli_query($db, $sql));
    $master_opening = (float)($customer['opening_balance'] ?? 0);
    
    $pl_heads = array("DirectIncome"=>11, "DirectExpense"=>10, "IndirectIncome"=>19, "IndirectExpense"=>18);
    
    // 1. Opening up to $from - 1 day
    $op_dr = 0.0;
    $op_cr = 0.0;
    
    if (!in_array($customer['parent'], $pl_heads)) {
        // Dr components
        $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type in ("PAYMENT", "sale", "purchase_revert") and cust_id='.(int)$sno;
        $t1 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
        $sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp<"'.$from.'" and `by`='.(int)$sno.' and status="1" '.$unit_filter;
        $t2 = mysqli_fetch_array(mysqli_query($db, $sql_journal));
        $sql_erp_receipt_dr = 'select sum(amount) as journal from billit_stock_erp_receipt where timestamp<"'.$from.'" and `by`='.(int)$sno;
        $t2r = mysqli_fetch_array(mysqli_query($db, $sql_erp_receipt_dr));
        $sql_erp_payment_dr = 'select sum(amount) as journal from billit_stock_erp_payment where timestamp<"'.$from.'" and `by`='.(int)$sno;
        $t2p = mysqli_fetch_array(mysqli_query($db, $sql_erp_payment_dr));
        $sql_cash = 'select sum(amount) as cash from billit_cash_voucher_journal where timestamp<"'.$from.'" and `by`='.(int)$sno.' and status="1" '.$unit_filter;
        $t3 = mysqli_fetch_array(mysqli_query($db, $sql_cash));
        $sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp<"'.$from.'" and `to`='.(int)$sno.$unit_filter;
        $t4 = mysqli_fetch_array(mysqli_query($db, $sql_contra));
        $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type="debit_note" and cust_id='.(int)$sno;
        $t5 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
        
        $op_dr = (float)$t1['trans'] + (float)$t2['journal'] + (float)$t2r['journal'] + (float)$t2p['journal'] + (float)$t3['cash'] + (float)$t4['contra'] + (float)$t5['trans'];
        
        // Cr components
        $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type="credit_note" and cust_id='.(int)$sno;
        $c1 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
        $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp<"'.$from.'" and type in ("RECIEPT","purchase", "RECEIPT", "sale_revert") and cust_id='.(int)$sno;
        $c2 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
        $sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp<"'.$from.'" and `to`='.(int)$sno.' and status="1" '.$unit_filter;
        $c3 = mysqli_fetch_array(mysqli_query($db, $sql_journal));
        $sql_erp_receipt_cr = 'select sum(amount) as journal from billit_stock_erp_receipt where timestamp<"'.$from.'" and `to`='.(int)$sno;
        $c3r = mysqli_fetch_array(mysqli_query($db, $sql_erp_receipt_cr));
        $sql_erp_payment_cr = 'select sum(amount) as journal from billit_stock_erp_payment where timestamp<"'.$from.'" and `to`='.(int)$sno;
        $c3p = mysqli_fetch_array(mysqli_query($db, $sql_erp_payment_cr));
        $sql_cash = 'select sum(amount) as cash from billit_cash_voucher_journal where timestamp<"'.$from.'" and `to`='.(int)$sno.' and status="1" '.$unit_filter;
        $c4 = mysqli_fetch_array(mysqli_query($db, $sql_cash));
        $sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp<"'.$from.'" and `by`='.(int)$sno.$unit_filter;
        $c5 = mysqli_fetch_array(mysqli_query($db, $sql_contra));
        
        $op_cr = (float)$c1['trans'] + (float)$c2['trans'] + (float)$c3['journal'] + (float)$c3r['journal'] + (float)$c3p['journal'] + (float)$c4['cash'] + (float)$c5['contra'];
        
        // Master opening balance is typically debit, adjust based on your logic
        // Original logic: $cust_opening = (float)$customer['opening_balance'] + ((float)$opening_tot_dr - (float)$opening_tot_cr);
        $op_dr += $master_opening;
    }
    
    // 2. Transactions during period
    $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type in ("PAYMENT","sale", "purchase_revert") and cust_id='.(int)$sno;
    $d1 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
    $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type="debit_note" and cust_id='.(int)$sno;
    $d2 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
    $sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.(int)$sno.$unit_filter;
    $d3 = mysqli_fetch_array(mysqli_query($db, $sql_contra));
    $sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.(int)$sno.' and status="1" '.$unit_filter;
    $d4 = mysqli_fetch_array(mysqli_query($db, $sql_journal));
    $sql_erp_receipt_txdr = 'select sum(amount) as journal from billit_stock_erp_receipt where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.(int)$sno;
    $d4r = mysqli_fetch_array(mysqli_query($db, $sql_erp_receipt_txdr));
    $sql_erp_payment_txdr = 'select sum(amount) as journal from billit_stock_erp_payment where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.(int)$sno;
    $d4p = mysqli_fetch_array(mysqli_query($db, $sql_erp_payment_txdr));
    $sql_cash = 'select sum(amount) as cash from billit_cash_voucher_journal where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.(int)$sno.' and status="1" '.$unit_filter;
    $d5 = mysqli_fetch_array(mysqli_query($db, $sql_cash));
    
    $txn_dr = (float)$d1['trans'] + (float)$d2['trans'] + (float)$d3['contra'] + (float)$d4['journal'] + (float)$d4r['journal'] + (float)$d4p['journal'] + (float)$d5['cash'];
    
    $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type in ("RECIEPT","purchase", "RECEIPT", "sale_revert") and cust_id='.(int)$sno;
    $x1 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
    $sql_trans = 'select sum(amount) as trans from billit_customer_transactions where timestamp>="'.$from.'" and timestamp<="'.$to.'" and type="credit_note" and cust_id='.(int)$sno;
    $x2 = mysqli_fetch_array(mysqli_query($db, $sql_trans));
    $sql_journal = 'select sum(amount) as journal from billit_stock_journal where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.(int)$sno.' and status="1" '.$unit_filter;
    $x3 = mysqli_fetch_array(mysqli_query($db, $sql_journal));
    $sql_erp_receipt_txcr = 'select sum(amount) as journal from billit_stock_erp_receipt where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.(int)$sno;
    $x3r = mysqli_fetch_array(mysqli_query($db, $sql_erp_receipt_txcr));
    $sql_erp_payment_txcr = 'select sum(amount) as journal from billit_stock_erp_payment where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.(int)$sno;
    $x3p = mysqli_fetch_array(mysqli_query($db, $sql_erp_payment_txcr));
    $sql_cash = 'select sum(amount) as cash from billit_cash_voucher_journal where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `to`='.(int)$sno.' and status="1" '.$unit_filter;
    $x4 = mysqli_fetch_array(mysqli_query($db, $sql_cash));
    $sql_contra = 'select sum(amount) as contra from billit_contra_entry where timestamp>="'.$from.'" and timestamp<="'.$to.'" and `by`='.(int)$sno.$unit_filter;
    $x5 = mysqli_fetch_array(mysqli_query($db, $sql_contra));
    
    $txn_cr = (float)$x1['trans'] + (float)$x2['trans'] + (float)$x3['journal'] + (float)$x3r['journal'] + (float)$x3p['journal'] + (float)$x4['cash'] + (float)$x5['contra'];
    
    // Resolve pure Opening Balance Dr/Cr
    $net_opening = $op_dr - $op_cr;
    $resolved_op_dr = $net_opening > 0 ? $net_opening : 0;
    $resolved_op_cr = $net_opening < 0 ? abs($net_opening) : 0;
    
    // Resolve pure Closing Balance Dr/Cr
    $net_closing = $net_opening + $txn_dr - $txn_cr;
    $resolved_cl_dr = $net_closing > 0 ? $net_closing : 0;
    $resolved_cl_cr = $net_closing < 0 ? abs($net_closing) : 0;

    return [
        'op_dr' => $resolved_op_dr,
        'op_cr' => $resolved_op_cr,
        'tx_dr' => $txn_dr,
        'tx_cr' => $txn_cr,
        'cl_dr' => $resolved_cl_dr,
        'cl_cr' => $resolved_cl_cr
    ];
}


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
        return "(visibility IS NULL OR visibility = '' OR visibility = 'public')";
    } else {
        return "1=0";
    }
}

function get_descendant_total_balance($db, $headSno, $parentLedgerSno, $selected_division, $from, $to)
{
    $tot_op_dr = 0.0; $tot_op_cr = 0.0;
    $tot_tx_dr = 0.0; $tot_tx_cr = 0.0;
    $tot_cl_dr = 0.0; $tot_cl_cr = 0.0;
    
    $children = [];
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $p = mysqli_real_escape_string($db, (string) $parentLedgerSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);

    $res = mysqli_query($db, "SELECT sno, cus_name FROM billit_customer WHERE parent='$h' AND parent_ledger='$p' AND $ledger_filter ORDER BY cus_name ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $sno = (int) $row['sno'];
        $bal = get_trial_balance_details($db, $sno, $from, $to, $selected_division);
        $desc = get_descendant_total_balance($db, $headSno, $sno, $selected_division, $from, $to);

        $op_dr = $bal['op_dr'] + $desc['op_dr'];
        $op_cr = $bal['op_cr'] + $desc['op_cr'];
        $tx_dr = $bal['tx_dr'] + $desc['tx_dr'];
        $tx_cr = $bal['tx_cr'] + $desc['tx_cr'];
        $cl_dr = $bal['cl_dr'] + $desc['cl_dr'];
        $cl_cr = $bal['cl_cr'] + $desc['cl_cr'];

        // Netting Opening and Closing for this node
        $net_op = $op_dr - $op_cr;
        $resolved_op_dr = $net_op > 0 ? $net_op : 0;
        $resolved_op_cr = $net_op < 0 ? abs($net_op) : 0;
        
        $net_cl = $cl_dr - $cl_cr;
        $resolved_cl_dr = $net_cl > 0 ? $net_cl : 0;
        $resolved_cl_cr = $net_cl < 0 ? abs($net_cl) : 0;

        if ($resolved_op_dr != 0 || $resolved_op_cr != 0 || $tx_dr != 0 || $tx_cr != 0 || $resolved_cl_dr != 0 || $resolved_cl_cr != 0) {
            $tot_op_dr += $resolved_op_dr; $tot_op_cr += $resolved_op_cr;
            $tot_tx_dr += $tx_dr; $tot_tx_cr += $tx_cr;
            $tot_cl_dr += $resolved_cl_dr; $tot_cl_cr += $resolved_cl_cr;
            
            $children[] = [
                'type' => 'ledger',
                'name' => htmlspecialchars($row['cus_name']),
                'op_dr' => $resolved_op_dr, 'op_cr' => $resolved_op_cr,
                'tx_dr' => $tx_dr, 'tx_cr' => $tx_cr,
                'cl_dr' => $resolved_cl_dr, 'cl_cr' => $resolved_cl_cr,
                'children' => $desc['children']
            ];
        }
    }
    
    // Netting for the group return
    $group_net_op = $tot_op_dr - $tot_op_cr;
    $group_net_cl = $tot_cl_dr - $tot_cl_cr;
    
    return [
        'op_dr' => $group_net_op > 0 ? $group_net_op : 0, 
        'op_cr' => $group_net_op < 0 ? abs($group_net_op) : 0,
        'tx_dr' => $tot_tx_dr, 
        'tx_cr' => $tot_tx_cr,
        'cl_dr' => $group_net_cl > 0 ? $group_net_cl : 0, 
        'cl_cr' => $group_net_cl < 0 ? abs($group_net_cl) : 0,
        'children' => $children
    ];
}

function get_head_total_balance($db, $headSno, $selected_division, $from, $to)
{
    $tot_op_dr = 0.0; $tot_op_cr = 0.0;
    $tot_tx_dr = 0.0; $tot_tx_cr = 0.0;
    $tot_cl_dr = 0.0; $tot_cl_cr = 0.0;
    
    $children = [];
    $h = mysqli_real_escape_string($db, (string) $headSno);
    $ledger_filter = get_ledger_filter($db, $selected_division);
    $head_vis = get_head_visibility_filter($selected_division);

    $resSub = mysqli_query($db, "SELECT sno, description FROM billit_pl_heads h WHERE h.parent='$h' AND $head_vis ORDER BY h.sort_no+0, h.sno ASC");
    while ($row = mysqli_fetch_assoc($resSub)) {
        $sub = get_head_total_balance($db, (int) $row['sno'], $selected_division, $from, $to);
        
        if ($sub['op_dr'] != 0 || $sub['op_cr'] != 0 || $sub['tx_dr'] != 0 || $sub['tx_cr'] != 0 || $sub['cl_dr'] != 0 || $sub['cl_cr'] != 0) {
            $tot_op_dr += $sub['op_dr']; $tot_op_cr += $sub['op_cr'];
            $tot_tx_dr += $sub['tx_dr']; $tot_tx_cr += $sub['tx_cr'];
            $tot_cl_dr += $sub['cl_dr']; $tot_cl_cr += $sub['cl_cr'];
            
            $children[] = [
                'type' => 'head',
                'name' => htmlspecialchars($row['description']),
                'op_dr' => $sub['op_dr'], 'op_cr' => $sub['op_cr'],
                'tx_dr' => $sub['tx_dr'], 'tx_cr' => $sub['tx_cr'],
                'cl_dr' => $sub['cl_dr'], 'cl_cr' => $sub['cl_cr'],
                'children' => $sub['children']
            ];
        }
    }

    $res = mysqli_query($db, "SELECT sno, cus_name FROM billit_customer WHERE parent='$h' AND (parent_ledger IS NULL OR parent_ledger='' OR parent_ledger='0') AND $ledger_filter ORDER BY cus_name ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $sno = (int) $row['sno'];
        $bal = get_trial_balance_details($db, $sno, $from, $to, $selected_division);
        $desc = get_descendant_total_balance($db, $headSno, $sno, $selected_division, $from, $to);

        $op_dr = $bal['op_dr'] + $desc['op_dr'];
        $op_cr = $bal['op_cr'] + $desc['op_cr'];
        $tx_dr = $bal['tx_dr'] + $desc['tx_dr'];
        $tx_cr = $bal['tx_cr'] + $desc['tx_cr'];
        $cl_dr = $bal['cl_dr'] + $desc['cl_dr'];
        $cl_cr = $bal['cl_cr'] + $desc['cl_cr'];

        $net_op = $op_dr - $op_cr;
        $resolved_op_dr = $net_op > 0 ? $net_op : 0;
        $resolved_op_cr = $net_op < 0 ? abs($net_op) : 0;
        
        $net_cl = $cl_dr - $cl_cr;
        $resolved_cl_dr = $net_cl > 0 ? $net_cl : 0;
        $resolved_cl_cr = $net_cl < 0 ? abs($net_cl) : 0;

        if ($resolved_op_dr != 0 || $resolved_op_cr != 0 || $tx_dr != 0 || $tx_cr != 0 || $resolved_cl_dr != 0 || $resolved_cl_cr != 0) {
            $tot_op_dr += $resolved_op_dr; $tot_op_cr += $resolved_op_cr;
            $tot_tx_dr += $tx_dr; $tot_tx_cr += $tx_cr;
            $tot_cl_dr += $resolved_cl_dr; $tot_cl_cr += $resolved_cl_cr;
            
            $children[] = [
                'type' => 'ledger',
                'name' => htmlspecialchars($row['cus_name']),
                'op_dr' => $resolved_op_dr, 'op_cr' => $resolved_op_cr,
                'tx_dr' => $tx_dr, 'tx_cr' => $tx_cr,
                'cl_dr' => $resolved_cl_dr, 'cl_cr' => $resolved_cl_cr,
                'children' => $desc['children']
            ];
        }
    }

    $group_net_op = $tot_op_dr - $tot_op_cr;
    $group_net_cl = $tot_cl_dr - $tot_cl_cr;
    
    return [
        'op_dr' => $group_net_op > 0 ? $group_net_op : 0, 
        'op_cr' => $group_net_op < 0 ? abs($group_net_op) : 0,
        'tx_dr' => $tot_tx_dr, 
        'tx_cr' => $tot_tx_cr,
        'cl_dr' => $group_net_cl > 0 ? $group_net_cl : 0, 
        'cl_cr' => $group_net_cl < 0 ? abs($group_net_cl) : 0,
        'children' => $children
    ];
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

        $f = function($v) { return $v > 0 ? number_format($v, 2) : '-'; };
        
        $op_dr = $f($node['op_dr']); $op_cr = $f($node['op_cr']);
        $tx_dr = $f($node['tx_dr']); $tx_cr = $f($node['tx_cr']);
        $cl_dr = $f($node['cl_dr']); $cl_cr = $f($node['cl_cr']);

        $name_style = ($node['type'] === 'Group' || $node['type'] === 'head') ? "font-weight:bold;" : "";
        $sno_display = $is_root ? $sno_counter++ : '';

        $html .= "<tr class='$class_str' data-id='$myId' $display $row_attr>";
        $html .= "<td class='text-center'>$sno_display</td>";
        $html .= "<td class='text-left' style='$name_style'>$indent $icon " . $node['name'] . "</td>";
        $html .= "<td class='amt-col text-dark'>$op_dr</td><td class='amt-col text-dark'>$op_cr</td>";
        $html .= "<td class='amt-col text-dark'>$tx_dr</td><td class='amt-col text-dark'>$tx_cr</td>";
        $html .= "<td class='amt-col text-dark'>$cl_dr</td><td class='amt-col text-dark'>$cl_cr</td>";
        $html .= "</tr>";

        if ($has_children) {
            $html .= render_tb_rows($node['children'], $myId, $level + 1, $global_id, $sno_counter);
        }
    }
    return $html;
}

$is_sadmin = isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'sadmin';
$user_divisions = (isset($_SESSION['divisions']) && is_array($_SESSION['divisions'])) ? $_SESSION['divisions'] : [];

$start_m = (int)date('n');
$start_y = (int)date('Y');
$fy_start = ($start_m >= 4) ? $start_y : ($start_y - 1);
$default_from = $fy_start . '-04-01';
$default_to = ($fy_start + 1) . '-03-31';

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : $default_from;
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : $default_to;
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
$gt_op_dr = 0.0; $gt_op_cr = 0.0;
$gt_tx_dr = 0.0; $gt_tx_cr = 0.0;
$gt_cl_dr = 0.0; $gt_cl_cr = 0.0;

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

        $hier = get_head_total_balance($db, $headSno, $selected_division, $from_date, $to_date);
        
        if ($hier['op_dr'] == 0 && $hier['op_cr'] == 0 && $hier['tx_dr'] == 0 && $hier['tx_cr'] == 0 && $hier['cl_dr'] == 0 && $hier['cl_cr'] == 0) {
            continue;
        }

        $trial_balance[] = [
            'name' => htmlspecialchars($row['description']),
            'type' => 'Group',
            'op_dr' => $hier['op_dr'], 'op_cr' => $hier['op_cr'],
            'tx_dr' => $hier['tx_dr'], 'tx_cr' => $hier['tx_cr'],
            'cl_dr' => $hier['cl_dr'], 'cl_cr' => $hier['cl_cr'],
            'children' => $hier['children']
        ];

        $gt_op_dr += $hier['op_dr']; $gt_op_cr += $hier['op_cr'];
        $gt_tx_dr += $hier['tx_dr']; $gt_tx_cr += $hier['tx_cr'];
        $gt_cl_dr += $hier['cl_dr']; $gt_cl_cr += $hier['cl_cr'];
    }
}

// Check if Trial Balance matches
$diff = abs($gt_cl_dr - $gt_cl_cr);
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
        background: #e0f7fa !important;
        color: #000000 !important;
        font-weight: bold !important;
        text-transform: uppercase;
        font-size: 15px !important;
        letter-spacing: 0.5px;
    }

    .tb-table td {
        vertical-align: middle;
        font-size: 14px;
    }

    .amt-col {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-weight: 900;
        letter-spacing: 0.5px;
        color: #000 !important;
        font-size: 20px;
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
                <label class="mr-2 font-weight-bold">From : </label>
                <input type="date" name="from_date" class="form-control mr-3" value="<?php echo htmlspecialchars($from_date); ?>" onchange="document.getElementById('divForm').submit()">
                
                <label class="mr-2 font-weight-bold">To : </label>
                <input type="date" name="to_date" class="form-control mr-3" value="<?php echo htmlspecialchars($to_date); ?>" onchange="document.getElementById('divForm').submit()">
                
                <label class="mr-2 font-weight-bold"><i class="fa fa-building mr-2"></i>Division : </label>
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
                                <th width="5%" rowspan="2" class="text-center" style="vertical-align: middle;">S.No.</th>
                                <th width="35%" rowspan="2" class="text-left" style="vertical-align: middle;">Account Group / Ledger Name</th>
                                <th width="20%" colspan="2" class="text-center">Opening Balance</th>
                                <th width="20%" colspan="2" class="text-center">Transactions</th>
                                <th width="20%" colspan="2" class="text-center">Closing Balance</th>
                            </tr>
                            <tr>
                                <th width="10%" class="text-right">Dr ₹</th>
                                <th width="10%" class="text-right">Cr ₹</th>
                                <th width="10%" class="text-right">Dr ₹</th>
                                <th width="10%" class="text-right">Cr ₹</th>
                                <th width="10%" class="text-right">Dr ₹</th>
                                <th width="10%" class="text-right">Cr ₹</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($trial_balance)) {
                                echo '<tr><td colspan="8" class="text-center py-4 text-muted">No balances found for the selected division.</td></tr>';
                            } else {
                                $global_id = 0;
                                $sno_counter = 1;
                                echo render_tb_rows($trial_balance, 0, 0, $global_id, $sno_counter);
                            }
                            ?>

                            <?php if (!empty($trial_balance)): ?>
                                <tr class="tb-total-row">
                                    <td colspan="2" class="text-right pr-4">GRAND TOTAL</td>
                                    <td class="amt-col text-dark"><?php echo number_format($gt_op_dr, 2); ?></td>
                                    <td class="amt-col text-dark"><?php echo number_format($gt_op_cr, 2); ?></td>
                                    <td class="amt-col text-dark"><?php echo number_format($gt_tx_dr, 2); ?></td>
                                    <td class="amt-col text-dark"><?php echo number_format($gt_tx_cr, 2); ?></td>
                                    <td class="amt-col text-dark"><?php echo number_format($gt_cl_dr, 2); ?></td>
                                    <td class="amt-col text-dark"><?php echo number_format($gt_cl_cr, 2); ?></td>
                                </tr>
                                <?php if (!$is_balanced): ?>
                                    <tr>
                                        <td colspan="6" class="text-right pr-4 text-danger"><strong>DIFFERENCE IN TRIAL
                                                BALANCE</strong></td>
                                        <?php if ($gt_cl_dr > $gt_cl_cr): ?>
                                            <td class="amt-col text-muted">-</td>
                                            <td class="amt-col text-danger"><strong><?php echo number_format($diff, 2); ?></strong></td>
                                        <?php else: ?>
                                            <td class="amt-col text-danger"><strong><?php echo number_format($diff, 2); ?></strong></td>
                                            <td class="amt-col text-muted">-</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr class="tb-total-row">
                                        <td colspan="6" class="text-right pr-4">ADJUSTED TOTAL</td>
                                        <?php $max_total = max($gt_cl_dr, $gt_cl_cr); ?>
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