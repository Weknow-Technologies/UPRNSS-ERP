<?php
include("scripts/settings.php");
include("scripts/alerts.php");
include("scripts/billit_settings.php"); // customRound() aur money() yahan se aate hain
$msg = '';
$tab = 1;

$auto_voucher_no = (!isset($_GET['edit_sno']))
    ? generateVoucherNumber('invoice_fund_receive', 'voucher_no', 'FR')
    : '';

page_header_start();
?>

<?php
page_header_end();
page_sidebar();

/* ---------- Helpers ---------- */
function nval($v)
{
    return is_numeric($v) ? floatval($v) : 0.0;
}
function get_general_setting($desc, $unit_id = 0)
{
    global $db;
    $unit_id = ($unit_id == "" || $unit_id == 0) ? 53 : $unit_id; // Default HO is 53
    $sql = "SELECT * FROM general_settings WHERE `desc`='" . mysqli_real_escape_string($db, $desc) . "' AND unit_id='" . mysqli_real_escape_string($db, $unit_id) . "' LIMIT 1";
    $res = execute_query($sql);
    if ($r = mysqli_fetch_assoc($res)) {
        return $r;
    }
    // Fallback to global (unit_id IS NULL or 0)
    $sql = "SELECT * FROM general_settings WHERE `desc`='" . mysqli_real_escape_string($db, $desc) . "' AND (unit_id IS NULL OR unit_id=0 OR unit_id='') LIMIT 1";
    $res = execute_query($sql);
    return mysqli_fetch_assoc($res);
}
function ensure_tfr_alloc_columns($db)
{
    $res = mysqli_query($db, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transaction_fund_receive'");
    $have = [];
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $have[$r['COLUMN_NAME']] = 1;
        }
    }

    $clauses = [];
    // existing allocation columns (as before)
    if (!isset($have['tds_alloc']))
        $clauses[] = "ADD COLUMN `tds_alloc` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `p_receive_amount`";
    if (!isset($have['gsttds_alloc']))
        $clauses[] = "ADD COLUMN `gsttds_alloc` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `tds_alloc`";
    if (!isset($have['labour_cess_alloc']))
        $clauses[] = "ADD COLUMN `labour_cess_alloc` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `gsttds_alloc`";

    // NEW: per-row GST split + net (stored as p_diff_amount)
    if (!isset($have['cgst_amount']))
        $clauses[] = "ADD COLUMN `cgst_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `labour_cess_alloc`";
    if (!isset($have['sgst_amount']))
        $clauses[] = "ADD COLUMN `sgst_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `cgst_amount`";

    if (!isset($have['p_diff_amount'])) {
        if (isset($have['amount'])) {
            // rename existing `amount` -> `p_diff_amount` (keeps data)
            $clauses[] = "CHANGE COLUMN `amount` `p_diff_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `sgst_amount`";
        } else {
            $clauses[] = "ADD COLUMN `p_diff_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `sgst_amount`";
        }
    }

    if (!empty($clauses)) {
        $sql = "ALTER TABLE `transaction_fund_receive` " . implode(", ", $clauses);
        mysqli_query($db, $sql);
    }
}



function ensure_ifr_ledger_columns($db)
{
    $res = mysqli_query($db, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='invoice_fund_receive'");
    $have = [];
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $have[$r['COLUMN_NAME']] = 1;
        }
    }
    $clauses = [];
    if (!isset($have['tds_ledger_id']))
        $clauses[] = "ADD COLUMN `tds_ledger_id` VARCHAR(50) DEFAULT NULL";
    if (!isset($have['gst_tds_ledger_id']))
        $clauses[] = "ADD COLUMN `gst_tds_ledger_id` VARCHAR(50) DEFAULT NULL";
    if (!isset($have['labour_cess_ledger_id']))
        $clauses[] = "ADD COLUMN `labour_cess_ledger_id` VARCHAR(50) DEFAULT NULL";
    if (!isset($have['other_charges_ledger_id']))
        $clauses[] = "ADD COLUMN `other_charges_ledger_id` VARCHAR(50) DEFAULT NULL";
    if (!isset($have['other_charges']))
        $clauses[] = "ADD COLUMN `other_charges` DECIMAL(15,2) DEFAULT '0.00'";

    if (!empty($clauses)) {
        $sql = "ALTER TABLE `invoice_fund_receive` " . implode(", ", $clauses);
        mysqli_query($db, $sql);
    }
}
// Run schema update globally to ensure columns exist before edit queries run
ensure_ifr_ledger_columns($db);

/* ---------- Submit (Insert/Update) ---------- */
if (isset($_POST['submit'])) {
    mysqli_begin_transaction($db);

    // Make sure allocation columns exist
    ensure_tfr_alloc_columns($db);
    ensure_ifr_ledger_columns($db);

    $tds_ledger_id = mysqli_real_escape_string($db, $_POST['tds_ledger_id'] ?? '');
    $gst_tds_ledger_id = mysqli_real_escape_string($db, $_POST['gst_tds_ledger_id'] ?? '');
    $labour_cess_ledger_id = mysqli_real_escape_string($db, $_POST['labour_cess_ledger_id'] ?? '');
    $other_charges_ledger_id = mysqli_real_escape_string($db, $_POST['other_charges_ledger_id'] ?? '');
    $other_charges = nval($_POST['other_charges'] ?? 0);

    $rowCount = intval($_POST['add_rows_id']);
    $perProjectNew = [];  // project_id => sum p_receive_amount (rupees)
    for ($i = 1; $i <= $rowCount; $i++) {
        $pid = trim($_POST['project_id_' . $i] ?? '');
        $amt = nval($_POST['p_receive_amount_' . $i] ?? 0);
        if ($pid !== '' && $amt > 0) {
            if (!isset($perProjectNew[$pid]))
                $perProjectNew[$pid] = 0.0;
            $perProjectNew[$pid] += $amt;
        }
    }

    // Through Project = header total equals sum of line items
    $fund_receive_type = $_POST['fund_receive_type'] ?? '1';
    if ($fund_receive_type == '1') {
        $calc_total = 0.0;
        foreach ($perProjectNew as $v)
            $calc_total += $v;
        $_POST['tot_receive_amount'] = number_format($calc_total, 2, '.', '');
    }

    $tot_receive_amount = nval($_POST['tot_receive_amount']);
    $tds_total = nval($_POST['tds_deducted']);
    $gsttds_total = nval($_POST['gsttds_deducted']);
    $labour_total = nval($_POST['labour_sess']);
    $other_charges = nval($_POST['other_charges'] ?? 0);
    $tot_credit_amount = max(0, $tot_receive_amount - $tds_total - $gsttds_total - $labour_total - $other_charges);
    $_POST['tot_credit_amount'] = number_format($tot_credit_amount, 2, '.', '');

    /* ---------- Server-side cap check (Sanction in Lakh → Rupees) ---------- */
    $violations = [];
    foreach ($perProjectNew as $projectId => $newAmtRupees) {
        $q1 = 'SELECT sanction_cost FROM uprnss_project_temp WHERE sno = "' . mysqli_real_escape_string($db, $projectId) . '" LIMIT 1';
        $rs1 = execute_query($q1);
        $row1 = $rs1 ? mysqli_fetch_assoc($rs1) : null;
        $sanction_lakh = $row1 ? nval($row1['sanction_cost']) : 0.0;
        $sanction_rupees = $sanction_lakh * 100000;

        $q2 = 'SELECT COALESCE(SUM(p_receive_amount),0) as rcvd FROM transaction_fund_receive WHERE project_id="' . mysqli_real_escape_string($db, $projectId) . '" AND invoice_id!="' . $_POST['edit_sno'] . '"';
        $rs2 = execute_query($q2);
        $row2 = $rs2 ? mysqli_fetch_assoc($rs2) : null;
        $received_rupees = $row2 ? nval($row2['rcvd']) : 0.0;

        $remaining_rupees = $sanction_rupees - $received_rupees;
        if ($sanction_rupees > 0 && $newAmtRupees > $remaining_rupees) {
            $violations[] = 'Project #' . $projectId . ' exceeds sanction. Remaining: ₹' . number_format($remaining_rupees, 2) . ', Tried: ₹' . number_format($newAmtRupees, 2) . ' (Sanction: ' . $sanction_lakh . ' Lakh)';
        }
    }

    if (!empty($violations)) {
        mysqli_rollback($db);
        $msg .= '<strong>Validation failed:</strong><br>' . implode('<br>', $violations);
    } else {

        /* ---------- Compute proportional allocations per line ---------- */
        $sumLines = 0.0;
        for ($i = 1; $i <= $rowCount; $i++) {
            $sumLines += nval($_POST['p_receive_amount_' . $i] ?? 0);
        }
        if ($sumLines <= 0)
            $sumLines = 1;

        $alloc = []; // row i => ['tds'=>..., 'gst'=>..., 'lab'=>...]
        $tds_left = $tds_total;
        $gst_left = $gsttds_total;
        $lab_left = $labour_total;

        for ($i = 1; $i <= $rowCount; $i++) {
            $lineAmt = nval($_POST['p_receive_amount_' . $i] ?? 0);
            if ($i < $rowCount) {
                $ratio = $lineAmt / $sumLines;
                $tds_a = round($tds_total * $ratio, 2);
                $gst_a = round($gsttds_total * $ratio, 2);
                $lab_a = round($labour_total * $ratio, 2);
                $tds_left -= $tds_a;
                $gst_left -= $gst_a;
                $lab_left -= $lab_a;
            } else {
                $tds_a = round($tds_left, 2);
                $gst_a = round($gst_left, 2);
                $lab_a = round($lab_left, 2);
            }
            $alloc[$i] = ['tds' => $tds_a, 'gst' => $gst_a, 'lab' => $lab_a];
        }

        /* ---------- Insert or Update ---------- */

        // Always set order date to current date
        $_POST['order_date'] = date("Y-m-d");

        if ($_POST['edit_sno'] == '') {
            // INSERT header
            $sql = 'INSERT INTO invoice_fund_receive 
            (`fund_receive_type`,`fund_receive_to`,`installment`,`order_no`,`order_date`,`receive_date`,
             `tot_receive_amount`, `tds_per`,`tds_deducted`,`gst_tds_per`,`gsttds_deducted`,`labour_sess`,
             `bank_name`,`remark`,`status`,`created_by`,`creation_time`, `voucher_no`, `tds_ledger_id`, `gst_tds_ledger_id`, `labour_cess_ledger_id`, `other_charges_ledger_id`, `other_charges` ) VALUES (
            "' . $_POST['fund_receive_type'] . '",
            "' . $_POST['fund_receive_to'] . '",
            "' . $_POST['installment'] . '",
            "' . $_POST['order_no'] . '",
            "' . $_POST['order_date'] . '",
            "' . $_POST['receive_date'] . '",
            "' . number_format($tot_receive_amount, 2, '.', '') . '",
            "' . $_POST['tds_per'] . '",
            "' . number_format($tds_total, 2, '.', '') . '",
            "' . $_POST['gst_tds_per'] . '",
            "' . number_format($gsttds_total, 2, '.', '') . '",
            "' . number_format($labour_total, 2, '.', '') . '",
            "' . mysqli_real_escape_string($db, $_POST['bank_name']) . '",
            "' . mysqli_real_escape_string($db, $_POST['remark']) . '",
            "0",
            "' . $_SESSION['usersno'] . '",
            "' . date("Y-m-d H:i:s") . '",
            "' . $_POST['voucher_no'] . '",
            "' . $tds_ledger_id . '",
            "' . $gst_tds_ledger_id . '",
            "' . $labour_cess_ledger_id . '",
            "' . $other_charges_ledger_id . '",
            "' . number_format($other_charges, 2, '.', '') . '"
            )';
            execute_query($sql);
            if (mysqli_error($db)) {
                mysqli_rollback($db);
                $msg .= 'Error # 1 : ' . mysqli_error($db) . ' >> ' . $sql;
            } else {
                $inv_id = mysqli_insert_id($db);

                // Details
                for ($i = 1; $i <= $rowCount; $i++) {
                    $deptId = $_POST['department_id_' . $i] ?? '';
                    $subDeptId = $_POST['sub_department_id_' . $i] ?? '';
                    $distId = $_POST['district_id_' . $i] ?? '';
                    $prjId = $_POST['project_id_' . $i] ?? '';
                    $amt = nval($_POST['p_receive_amount_' . $i] ?? 0);
                    if ($prjId != '' && $amt > 0) {
                        // take values exactly as displayed (from hidden inputs)
                        $cgst = nval($_POST['cgst_amount_' . $i] ?? 0);
                        $sgst = nval($_POST['sgst_amount_' . $i] ?? 0);
                        $net = nval($_POST['p_diff_amount_' . $i] ?? 0); // Net shown in UI

                        $sql = 'INSERT INTO transaction_fund_receive 
						(invoice_id, department_id, sub_department_id, district_id, project_id, p_receive_amount,
						 tds_alloc, gsttds_alloc, labour_cess_alloc,
						 cgst_amount, sgst_amount, p_diff_amount,
						 status, created_by, creation_time)
						VALUES (
						"' . $inv_id . '",
						"' . mysqli_real_escape_string($db, $deptId) . '",
						"' . mysqli_real_escape_string($db, $subDeptId) . '",
						"' . mysqli_real_escape_string($db, $distId) . '",
						"' . mysqli_real_escape_string($db, $prjId) . '",
						"' . number_format($amt, 2, '.', '') . '",
						"' . number_format($alloc[$i]['tds'], 2, '.', '') . '",
						"' . number_format($alloc[$i]['gst'], 2, '.', '') . '",
						"' . number_format($alloc[$i]['lab'], 2, '.', '') . '",
						"' . number_format($cgst, 2, '.', '') . '",
						"' . number_format($sgst, 2, '.', '') . '",
						"' . number_format($net, 2, '.', '') . '",
						"0",
						"' . $_SESSION['usersno'] . '",
						"' . date("Y-m-d H:i:s") . '"
						)';
                        execute_query($sql);
                        if (mysqli_error($db)) {
                            mysqli_rollback($db);
                            $msg .= 'Error # 1.1 : ' . mysqli_error($db) . ' >> ' . $sql;
                            break;
                        }
                    }
                }

                if ($msg === '') {
                    // Journal header (billit_invoice_erp_receipt)
                    $sql = 'SELECT * FROM `uprnss_department_name` WHERE sno="' . $_POST['department_id_1'] . '"';
                    $first_to = mysqli_fetch_assoc(execute_query($sql));

                    if (isset($_POST['unit_id']) && $_POST['unit_id'] != "") {
                        $sql = 'INSERT INTO billit_invoice_erp_receipt 
                        (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, remarks, unit_id, created_by, creation_time, table_name, table_id) VALUES (
                        "' . $_POST['receive_date'] . '",
                        "' . $_POST['bank_name'] . '",
                        "' . $first_to['ledger_id'] . '",
                        "' . number_format($tot_receive_amount, 2, '.', '') . '",
                        "' . number_format($tot_receive_amount, 2, '.', '') . '",
                        "",
                        "' . $_POST['voucher_no'] . '",
                        "' . $_POST['remark'] . '",
                        "' . $_POST['unit_id'] . '",
                        "' . $_SESSION['username'] . '",
                        "' . date("Y-m-d H:i:s") . '",
                        "invoice_fund_receive",
                        "' . $inv_id . '"
                        )';
                    } else {
                        $sql = 'INSERT INTO billit_invoice_erp_receipt 
                        (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, remarks, created_by, creation_time, table_name, table_id, unit_id) VALUES (
                        "' . $_POST['receive_date'] . '",
                        "' . $_POST['bank_name'] . '",
                        "' . $first_to['ledger_id'] . '",
                        "' . number_format($tot_receive_amount, 2, '.', '') . '",
                        "' . number_format($tot_receive_amount, 2, '.', '') . '",
                        "",
                        "' . $_POST['voucher_no'] . '",
                        "' . $_POST['remark'] . '",
                        "' . $_SESSION['username'] . '",
                        "' . date("Y-m-d H:i:s") . '",
                        "invoice_fund_receive",
                        "' . $inv_id . '",
                        "53"
                        )';
                    }
                    execute_query($sql);
                    $journal_id = mysqli_insert_id($db);

                    $sum = mysqli_fetch_assoc(execute_query('
						SELECT
						  COALESCE(SUM(cgst_amount),0)    AS cgst_total,
						  COALESCE(SUM(sgst_amount),0)    AS sgst_total,
						  COALESCE(SUM(p_diff_amount),0)  AS net_total
						FROM transaction_fund_receive
						WHERE invoice_id="' . $inv_id . '"'));
                    $cgst_total = nval($sum['cgst_total']);
                    $sgst_total = nval($sum['sgst_total']);
                    $net_total = nval($sum['net_total']);
                    $tot_credit_amount = $_POST['tot_credit_amount'];

                    /* Ledger lookups (unit-aware) */
                    $unit_id_for_settings = $_POST['unit_id'] ?? 53;

                    $tds_ledger = $tds_ledger_id;
                    $gst_tds_ledger = $gst_tds_ledger_id;
                    $labour_cess_ledger = $labour_cess_ledger_id;
                    $other_charges_ledger = $other_charges_ledger_id;
					$cgst_ledger == '';
					$sgst_ledger == '';

                    if ($tds_ledger == '') {
                        $s = get_general_setting("ITTDS", $unit_id_for_settings);
                        $tds_ledger = $s['rate'] ?? '';
                    }
                    if ($gst_tds_ledger == '') {
                        $s = get_general_setting("GSTTDS", $unit_id_for_settings);
                        $gst_tds_ledger = $s['rate'] ?? '';
                    }
                    if ($labour_cess_ledger == '') {
                        $s = get_general_setting("LABORCESS", $unit_id_for_settings);
                        $labour_cess_ledger = $s['rate'] ?? '';
                    }
                    if ($other_charges_ledger == '') {
                        $s = get_general_setting("OTHER_CHARGES", $unit_id_for_settings);
                        $other_charges_ledger = $s['rate'] ?? '';
                    }
                    if ($cgst_ledger == '') {
                        $s = get_general_setting("CGST", $unit_id_for_settings);
                        $cgst_ledger = $s['rate'] ?? '';
                    }
                    if ($sgst_ledger == '') {
                        $s = get_general_setting("SGST", $unit_id_for_settings);
                        $sgst_ledger = $s['rate'] ?? '';
                    }



                    /* By: Bank — DIFFERENCE/NET (sum of p_diff_amount) */
                    execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
								   VALUES ("' . $journal_id . '", "' . $_POST['bank_name'] . '", "' . number_format($tot_credit_amount, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');

                    /* By: TDS, GST-TDS, Labour (using selected ledgers) */
                    if (nval($tds_total) > 0) {
                        execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $tds_ledger . '",  "' . number_format($tds_total, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                    }
                    if (nval($gsttds_total) > 0) {
                        if ($gst_tds_ledger != '') {
                            execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $gst_tds_ledger . '",  "' . number_format($gsttds_total, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                        } else {
                            $cgst_tds_l = get_general_setting("CGSTTDS", $unit_id_for_settings)['rate'] ?? '';
                            $sgst_tds_l = get_general_setting("SGSTTDS", $unit_id_for_settings)['rate'] ?? '';
                            $half = $gsttds_total / 2;
                            execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $cgst_tds_l . '",  "' . number_format($half, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                            execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $sgst_tds_l . '",  "' . number_format($half, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                        }
                    }
                    if (nval($labour_total) > 0) {
                        execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $labour_cess_ledger . '", "' . number_format($labour_total, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                    }
                    if (nval($other_charges) > 0) {
                        execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $other_charges_ledger_id . '", "' . number_format($other_charges, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                    }


                    /* To: Project Ledgers (row-wise) */
                    for ($k = 0; $k <= $rowCount; $k++) {
                        if (!empty($_POST['project_id_' . $k]) && !empty($_POST['p_receive_amount_' . $k])) {
                            $net_amt = nval($_POST['p_diff_amount_' . $k] ?? 0);
                            $cgst_amt = nval($_POST['cgst_amount_' . $k] ?? 0);
                            $sgst_amt = nval($_POST['sgst_amount_' . $k] ?? 0);



                            if ($net_amt > 0) {
                                $proj_ledger = mysqli_real_escape_string($db, $_POST['project_ledger_id_' . $k] ?? '');
                                if (empty($proj_ledger)) {
                                    $proj_ledger = $first_to['ledger_id']; // fallback to department
                                }
                                execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `to`, amount, timestamp, unit_id, status)
                                               VALUES ("' . $journal_id . '", "' . $proj_ledger . '", "' . number_format($net_amt, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');

                                execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `to`, amount, timestamp, unit_id, status)
                                               VALUES ("' . $journal_id . '", "' . $cgst_ledger . '", "' . number_format($cgst_amt, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');

                                execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `to`, amount, timestamp, unit_id, status)
                                               VALUES ("' . $journal_id . '", "' . $sgst_ledger . '", "' . number_format($sgst_amt, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                            }
                        }
                    }


                    if (mysqli_error($db)) {
                        mysqli_rollback($db);
                        $msg .= 'Error # 1.369';
                    } else {
                        mysqli_commit($db);
                        $msg .= 'Successfully Added';

                        // Notification to HO Accountant
                        if ($_POST['fund_receive_to'] == 'Unit' && $_SESSION['usertype'] == '9') {
                            $u_id = mysqli_real_escape_string($db, $_POST['unit_id'] ?? ($_SESSION['divisions'][0] ?? '0'));
                            $n_msg = "Fund of ₹" . number_format($tot_receive_amount, 2) . " received successfully.";
                            $n_time = date("Y-m-d H:i:s");
                            execute_query("INSERT INTO ho_notifications (sender_unit_id, invoice_id, message, status, created_time) VALUES ('$u_id', '$inv_id', '$n_msg', '0', '$n_time')");
                        }

                        unset($_POST);
                        $auto_voucher_no = generateVoucherNumber('invoice_fund_receive', 'voucher_no', 'FR');
                        goto postblank;
                    }

                }
            }
        } else {
            // UPDATE full chain (header + details + journal header + journal lines)
            $inv_id = intval($_POST['edit_sno']);

            // Update header
            $sql = 'UPDATE invoice_fund_receive SET
                `fund_receive_type` ="' . $_POST['fund_receive_type'] . '",
                `fund_receive_to` ="' . $_POST['fund_receive_to'] . '",
                `installment` ="' . $_POST['installment'] . '",
                `order_no` ="' . $_POST['order_no'] . '",
                `order_date` ="' . $_POST['order_date'] . '",
                `receive_date` ="' . $_POST['receive_date'] . '",
                `tot_receive_amount` ="' . number_format($tot_receive_amount, 2, '.', '') . '",
                `tds_per` ="' . $_POST['tds_per'] . '",
                `tds_deducted` ="' . number_format($tds_total, 2, '.', '') . '",
                `gst_tds_per` ="' . $_POST['gst_tds_per'] . '",
                `gsttds_deducted` ="' . number_format($gsttds_total, 2, '.', '') . '",
                `labour_sess` ="' . number_format($labour_total, 2, '.', '') . '",
                `bank_name` ="' . mysqli_real_escape_string($db, $_POST['bank_name']) . '",
                `remark` ="' . mysqli_real_escape_string($db, $_POST['remark']) . '",
                `edited_by` ="' . $_SESSION['username'] . '",
                `edition_time` ="' . date("Y-m-d H:i:s") . '",
                `voucher_no` = "' . $_POST['voucher_no'] . '",
                `tds_ledger_id` = "' . $tds_ledger_id . '",
                `gst_tds_ledger_id` = "' . $gst_tds_ledger_id . '",
                `labour_cess_ledger_id` = "' . $labour_cess_ledger_id . '",
                `other_charges_ledger_id` = "' . $other_charges_ledger_id . '",
                `other_charges` = "' . number_format($other_charges, 2, '.', '') . '"
                WHERE sno="' . $inv_id . '"';
            execute_query($sql);
            if (mysqli_error($db)) {
                mysqli_rollback($db);
                $msg .= 'Error updating header: ' . mysqli_error($db);
            } else {
                // Replace details
                execute_query('DELETE FROM transaction_fund_receive WHERE invoice_id="' . $inv_id . '"');
                for ($i = 1; $i <= $rowCount; $i++) {
                    $deptId = $_POST['department_id_' . $i] ?? '';
                    $subDeptId = $_POST['sub_department_id_' . $i] ?? '';
                    $distId = $_POST['district_id_' . $i] ?? '';
                    $prjId = $_POST['project_id_' . $i] ?? '';
                    $amt = nval($_POST['p_receive_amount_' . $i] ?? 0);
                    if ($prjId != '' && $amt > 0) {
                        // take values exactly as displayed (from hidden inputs)
                        $cgst = nval($_POST['cgst_amount_' . $i] ?? 0);
                        $sgst = nval($_POST['sgst_amount_' . $i] ?? 0);
                        $net = nval($_POST['p_diff_amount_' . $i] ?? 0); // Net shown in UI

                        $sql = 'INSERT INTO transaction_fund_receive 
						(invoice_id, department_id, sub_department_id, district_id, project_id, p_receive_amount,
						 tds_alloc, gsttds_alloc, labour_cess_alloc,
						 cgst_amount, sgst_amount, p_diff_amount,
						 status, created_by, creation_time)
						VALUES (
						"' . $inv_id . '",
						"' . mysqli_real_escape_string($db, $deptId) . '",
						"' . mysqli_real_escape_string($db, $subDeptId) . '",
						"' . mysqli_real_escape_string($db, $distId) . '",
						"' . mysqli_real_escape_string($db, $prjId) . '",
						"' . number_format($amt, 2, '.', '') . '",
						"' . number_format($alloc[$i]['tds'], 2, '.', '') . '",
						"' . number_format($alloc[$i]['gst'], 2, '.', '') . '",
						"' . number_format($alloc[$i]['lab'], 2, '.', '') . '",
						"' . number_format($cgst, 2, '.', '') . '",
						"' . number_format($sgst, 2, '.', '') . '",
						"' . number_format($net, 2, '.', '') . '",
						"0",
						"' . $_SESSION['usersno'] . '",
						"' . date("Y-m-d H:i:s") . '"
						)';
                        execute_query($sql);
                    }

                }

                // Update journal header
                $sql = 'SELECT * FROM `uprnss_department_name` WHERE sno="' . $_POST['department_id_1'] . '"';
                $first_to = mysqli_fetch_assoc(execute_query($sql));

                // Fetch existing journal header id
                $jr = mysqli_fetch_assoc(execute_query('SELECT sno FROM billit_invoice_erp_receipt WHERE table_name="invoice_fund_receive" AND table_id="' . $inv_id . '" LIMIT 1'));
                $journal_id = $jr ? intval($jr['sno']) : 0;

                if ($journal_id) {
                    // Update receipt header
                    if (isset($_POST['unit_id']) && $_POST['unit_id'] != "") {
                        $sql = 'UPDATE billit_invoice_erp_receipt SET
                            timestamp="' . $_POST['receive_date'] . '",
                            first_by="' . $_POST['bank_name'] . '",
                            first_to="' . $first_to['ledger_id'] . '",
                            tot_debit="' . number_format($tot_receive_amount, 2, '.', '') . '",
                            tot_credit="' . number_format($tot_receive_amount, 2, '.', '') . '",
                            voucher_no="' . $_POST['voucher_no'] . '",
                            remarks="' . $_POST['remark'] . '",
                            unit_id="' . $_POST['unit_id'] . '"
                            WHERE sno="' . $journal_id . '"';
                    } else {
                        $sql = 'UPDATE billit_invoice_erp_receipt SET
                            timestamp="' . $_POST['receive_date'] . '",
                            first_by="' . $_POST['bank_name'] . '",
                            first_to="' . $first_to['ledger_id'] . '",
                            tot_debit="' . number_format($tot_receive_amount, 2, '.', '') . '",
                            tot_credit="' . number_format($tot_receive_amount, 2, '.', '') . '",
                            voucher_no="' . $_POST['voucher_no'] . '",
                            remarks="' . $_POST['remark'] . '",
                            unit_id="53"
                            WHERE sno="' . $journal_id . '"';
                    }
                    execute_query($sql);
                    execute_query('DELETE FROM billit_stock_erp_receipt WHERE journal_id="' . $journal_id . '"');
                    $sum = mysqli_fetch_assoc(execute_query('
						SELECT
						  COALESCE(SUM(cgst_amount),0)    AS cgst_total,
						  COALESCE(SUM(sgst_amount),0)    AS sgst_total,
						  COALESCE(SUM(p_diff_amount),0)  AS net_total
						FROM transaction_fund_receive
						WHERE invoice_id="' . $inv_id . '"'));
                    $cgst_total = nval($sum['cgst_total']);
                    $sgst_total = nval($sum['sgst_total']);
                    $net_total = nval($sum['net_total']);
                    $tot_credit_amount = $_POST['tot_credit_amount'];

                    /* Ledger lookups (unit-aware) */
                    $unit_id_for_settings = $_POST['unit_id'] ?? 53;

                    $tds_ledger = $tds_ledger_id;
                    $gst_tds_ledger = $gst_tds_ledger_id;
                    $labour_cess_ledger = $labour_cess_ledger_id;

                    if ($tds_ledger == '') {
                        $s = get_general_setting("ITTDS", $unit_id_for_settings);
                        $tds_ledger = $s['rate'] ?? '';
                    }
                    if ($gst_tds_ledger == '') {
                        $s = get_general_setting("GSTTDS", $unit_id_for_settings);
                        $gst_tds_ledger = $s['rate'] ?? '';
                    }
                    if ($labour_cess_ledger == '') {
                        $s = get_general_setting("LABORCESS", $unit_id_for_settings);
                        $labour_cess_ledger = $s['rate'] ?? '';
                    }
                    if ($other_charges_ledger_id == '') {
                        $s = get_general_setting("OTHER_CHARGES", $unit_id_for_settings);
                        $other_charges_ledger_id = $s['rate'] ?? '';
                    }



                    /* By: Bank — DIFFERENCE/NET (sum of p_diff_amount) */
                    execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
								   VALUES ("' . $journal_id . '", "' . $_POST['bank_name'] . '", "' . number_format($tot_credit_amount, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');

                    /* By: TDS, GST-TDS, Labour (using selected ledgers) */
                    if (nval($tds_total) > 0) {
                        execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $tds_ledger . '",  "' . number_format($tds_total, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                    }
                    if (nval($gsttds_total) > 0) {
                        if ($gst_tds_ledger != '') {
                            execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $gst_tds_ledger . '",  "' . number_format($gsttds_total, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                        } else {
                            $cgst_tds_l = get_general_setting("CGSTTDS", $unit_id_for_settings)['rate'] ?? '';
                            $sgst_tds_l = get_general_setting("SGSTTDS", $unit_id_for_settings)['rate'] ?? '';
                            $half = $gsttds_total / 2;
                            execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $cgst_tds_l . '",  "' . number_format($half, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                            execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $sgst_tds_l . '",  "' . number_format($half, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                        }
                    }
                    if (nval($labour_total) > 0) {
                        execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $labour_cess_ledger . '", "' . number_format($labour_total, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                    }
                    if (nval($other_charges) > 0) {
                        execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `by`, amount, timestamp, unit_id, status)
									 VALUES ("' . $journal_id . '", "' . $other_charges_ledger_id . '", "' . number_format($other_charges, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                    }


                    /* To: Project Ledgers (row-wise) */
                    for ($k = 0; $k <= $rowCount; $k++) {
                        if (!empty($_POST['project_id_' . $k]) && !empty($_POST['p_receive_amount_' . $k])) {
                            $net_amt = nval($_POST['p_receive_amount_' . $k] ?? 0);
                            if ($net_amt > 0) {
                                $proj_ledger = mysqli_real_escape_string($db, $_POST['project_ledger_id_' . $k] ?? '');
                                if (empty($proj_ledger)) {
                                    $proj_ledger = $first_to['ledger_id']; // fallback to department
                                }
                                execute_query('INSERT INTO billit_stock_erp_receipt (`journal_id`, `to`, amount, timestamp, unit_id, status)
                                               VALUES ("' . $journal_id . '", "' . $proj_ledger . '", "' . number_format($net_amt, 2, '.', '') . '", "' . $_POST['receive_date'] . '", "", "")');
                            }
                        }
                    }



                }

                mysqli_commit($db);
                $msg .= 'Successfully Updated';
                // keep $_POST so user stays on edit view
            }
        }
    }

} else {
    postblank:
    $_POST['fund_receive_type'] = '1';
    $_POST['installment'] = '';
    $_POST['order_no'] = '';
    $_POST['order_date'] = date("Y-m-d");  // Always set to current date
    $_POST['receive_date'] = date("Y-m-d");
    $_POST['request_date'] = date("Y-m-d");
    $_POST['tot_receive_amount'] = '0.00';
    $_POST['tds_per'] = '';
    $_POST['tds_deducted'] = '0.00';
    $_POST['gst_tds_per'] = '';
    $_POST['gsttds_deducted'] = '0.00';
    $_POST['bank_name'] = '';
    $_POST['account_no'] = '';
    $_POST['ifsc_code'] = '';
    $_POST['request_amount'] = '';
    $_POST['request_no'] = '';
    $_POST['remark'] = '';

    $_POST['labour_sess'] = '0.00';
    $_POST['tot_credit_amount'] = '0.00';
    $_POST['division_id'] = '';
    $_POST['department_id_1'] = '';
    $_POST['sub_department_id_1'] = '';
    $_POST['district_id_1'] = '';
    $_POST['project_id_1'] = '';
    $_POST['p_receive_amount_1'] = '';
    $_POST['add_rows_id'] = 1;
    $_POST['edit_sno'] = '';

    if ($_SESSION['usertype'] == "6" || $_SESSION['usertype'] == "sadmin" || $_SESSION['username'] == "headacc") {
        $_POST['fund_receive_to'] = 'HO';
    } elseif ($_SESSION['usertype'] == "9") {
        $_POST['fund_receive_to'] = 'Unit';
    } else {
        $_POST['fund_receive_to'] = '';
    }

    // Auto-select unit_id if user only has one division
    if (isset($_SESSION['divisions']) && count($_SESSION['divisions']) == 1) {
        $_POST['unit_id'] = $_SESSION['divisions'][0];
    } else {
        $_POST['unit_id'] = '';
    }
}

/* ---------- Load for Edit: header + detail rows ---------- */
if (isset($_GET['edit_sno']) && $_GET['edit_sno'] != '') {
    $editId = intval($_GET['edit_sno']);
    $sql = 'select * from invoice_fund_receive where sno="' . $editId . '"';
    $data = mysqli_fetch_assoc(execute_query($sql));

    $_POST['fund_receive_type'] = $data['fund_receive_type'];
    $_POST['fund_receive_to'] = $data['fund_receive_to'];
    $_POST['installment'] = $data['installment'];
    $_POST['order_no'] = $data['order_no'];
    $_POST['order_date'] = $data['order_date'];
    $_POST['receive_date'] = $data['receive_date'];
    $_POST['tot_receive_amount'] = $data['tot_receive_amount'];
    $_POST['tds_per'] = $data['tds_per'];
    $_POST['tds_deducted'] = $data['tds_deducted'];
    $_POST['gst_tds_per'] = $data['gst_tds_per'];
    $_POST['gsttds_deducted'] = $data['gsttds_deducted'];
    $_POST['labour_sess'] = $data['labour_sess'];
    $_POST['bank_name'] = $data['bank_name'];
    $_POST['voucher_no'] = $data['voucher_no'];
    $_POST['remark'] = $data['remark'];
    $_POST['edit_sno'] = $data['sno'];
    $_POST['tds_ledger_id'] = $data['tds_ledger_id'] ?? '';
    $_POST['gst_tds_ledger_id'] = $data['gst_tds_ledger_id'] ?? '';
    $_POST['labour_cess_ledger_id'] = $data['labour_cess_ledger_id'] ?? '';
    $_POST['other_charges_ledger_id'] = $data['other_charges_ledger_id'] ?? '';
    $_POST['other_charges'] = $data['other_charges'] ?? '';

    // Load unit_id from billit_invoice_erp_receipt
    $jr = mysqli_fetch_assoc(execute_query('SELECT unit_id FROM billit_invoice_erp_receipt WHERE table_name="invoice_fund_receive" AND table_id="' . $editId . '" LIMIT 1'));
    $_POST['unit_id'] = $jr ? $jr['unit_id'] : '';

    // Load details
    $details = [];
    $res = execute_query('SELECT * FROM transaction_fund_receive WHERE invoice_id="' . $editId . '" ORDER BY sno ASC');
    $idx = 0;
    while ($r = mysqli_fetch_assoc($res)) {
        $idx++;
        $_POST['department_id_' . $idx] = $r['department_id'];
        $_POST['sub_department_id_' . $idx] = $r['sub_department_id'];
        $_POST['district_id_' . $idx] = $r['district_id'];
        $_POST['project_id_' . $idx] = $r['project_id'];
        $_POST['p_receive_amount_' . $idx] = $r['p_receive_amount'];
    }
    if ($idx > 0) {
        $_POST['add_rows_id'] = $idx;
    }
}

/* ---------- Delete ---------- */
if (isset($_GET['delid'])) {
    $delId = intval($_GET['delid']);
    mysqli_begin_transaction($db);

    $ok = execute_query('delete from invoice_fund_receive where sno="' . $delId . '"');
    if ($ok) {
        $ok2 = execute_query('DELETE FROM transaction_fund_receive WHERE invoice_id="' . $delId . '"');
        if ($ok2) {
            $jr = mysqli_fetch_assoc(execute_query('SELECT sno FROM billit_invoice_erp_receipt WHERE table_name="invoice_fund_receive" AND table_id="' . $delId . '" LIMIT 1'));
            $journal_id = $jr ? intval($jr['sno']) : 0;

            $ok3 = execute_query('DELETE FROM billit_invoice_erp_receipt WHERE table_id="' . $delId . '" AND table_name="invoice_fund_receive"');
            if ($ok3) {
                $ok4 = true;
                if ($journal_id) {
                    $ok4 = execute_query('DELETE FROM billit_stock_erp_receipt WHERE journal_id="' . $journal_id . '"');
                }
                if ($ok4) {
                    mysqli_commit($db);
                    $msg .= 'Data Deleted from all tables.';
                } else {
                    mysqli_rollback($db);
                    $msg .= 'Failed to delete stock lines.';
                }
            } else {
                mysqli_rollback($db);
                $msg .= 'Failed to delete receipt header.';
            }
        } else {
            mysqli_rollback($db);
            $msg .= 'Failed to delete detail rows.';
        }
    } else {
        mysqli_rollback($db);
        $msg .= 'Failed to delete invoice header.';
    }
}

?>
<script src="js/custom_round.js"></script>
<script>
    function makeSearchable(selectId) {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            console.error('Select2 or jQuery not loaded');
            return;
        }
        var $select = $('#' + selectId);
        if (!$select.length) return;

        // Initialize Select2
        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '--- Select ---',
            allowClear: true
        });
    }

    $(function () {
        // Static dropdowns
        ['fund_receive_type', 'fund_receive_to', 'unit_id', 'installment', 'bank_name', 'bank_name_unit'].forEach(makeSearchable);

        // Initial project rows (if any)
        var rowCount = parseInt(document.getElementById('add_rows_id')?.value) || 1;
        for (var i = 1; i <= rowCount; i++) {
            ['department_id_' + i, 'sub_department_id_' + i, 'district_id_' + i, 'project_id_' + i].forEach(makeSearchable);
        }
    });

    /* ---------- UI Helpers ---------- */
    function readonly_input() {
        var type = document.getElementById("fund_receive_type");
        document.getElementById("tot_receive_amount").readOnly = (type.value === "1");
    }

    function onchangetype() {
        var type = document.getElementById("fund_receive_type");
        document.getElementById("hide_project_type").style.display = (type.value == "1" ? "flex" : "none");
        readonly_input();
    }

    function fund_receive_to() {
        var type = document.getElementById("fund_receive_to");
        var val = type ? type.value : "HO";

        if (val == "Unit") {
            document.getElementById("unit").style.display = "block";
            document.getElementById("bank_unit").style.display = "block";
            document.getElementById("bank_ho").style.display = "none";
            document.getElementById("bank_name_unit").disabled = false;
            document.getElementById("bank_name").disabled = true;
            if (typeof fill_unit_ledgers === "function") {
                var uId = document.getElementById("unit_id") ? document.getElementById("unit_id").value : '';
                if (uId) fill_unit_ledgers(uId);
            }
        } else {
            document.getElementById("unit").style.display = "none";
            document.getElementById("bank_unit").style.display = "none";
            document.getElementById("bank_ho").style.display = "block";
            document.getElementById("bank_name_unit").disabled = true;
            document.getElementById("bank_name").disabled = false;
            if (typeof fill_unit_ledgers === "function") {
                fill_unit_ledgers("53"); // 53 is HO
            }
        }
    }

    window.onload = function () {
        onchangetype();
        document.getElementById("fund_receive_type").onchange = onchangetype;
        fund_receive_to();
        document.getElementById("fund_receive_to").onchange = fund_receive_to;
        var preUnit = document.getElementById("unit_id")?.value;
        var frt = document.getElementById("fund_receive_to")?.value;
        var effectiveUnit = (frt === 'Unit') ? (preUnit || '') : '53';
        if (effectiveUnit) {
            fill_unit_ledgers(effectiveUnit);
        }
    };

    /* ---------- Totals / Percent ---------- */
    function setTodayDate() {
        // Set today's date in receive_date field when voucher number is entered
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayStr = yyyy + '-' + mm + '-' + dd;

        // Set the receive_date field to today's date
        const receiveDateField = document.getElementById('receive_date');
        if (receiveDateField) {
            receiveDateField.value = todayStr;

            // Also update the date picker components
            const monthField = document.getElementById('receive_date_Month_ID');
            const dayField = document.getElementById('receive_date_Day_ID');
            const yearField = document.getElementById('receive_date_Year_ID');

            if (monthField) monthField.value = today.getMonth();
            if (dayField) dayField.value = today.getDate();
            if (yearField) yearField.value = today.getFullYear();
        }
    }

    function percent_amt_calc(source = null) {
        const toFixed2 = (x) => (isNaN(x) ? '0.00' : (+x).toFixed(2));
        let totamount = parseFloat(document.getElementById('tot_receive_amount').value) || 0;

        let tds_per_el = document.getElementById('tds_per');
        let tdsdeducted = document.getElementById('tds_deducted');
        let gsttds_per_el = document.getElementById('gst_tds_per');
        let gsttdsdeducted = document.getElementById('gsttds_deducted');
        let laboursess = document.getElementById('labour_sess');
        let totcreditamount = document.getElementById('tot_credit_amount');

        // TDS
        if (source === 'tds_per') {
            let pct = parseFloat(tds_per_el.value) || 0;
            tdsdeducted.value = customRound((totamount * pct) / 100);
        } else if (source === 'tds_deducted') {
            let amt = parseFloat(tdsdeducted.value) || 0;
            if (totamount > 0) {
                tds_per_el.value = parseFloat(((amt / totamount) * 100).toFixed(4));
            } else {
                tds_per_el.value = '0';
            }
        }

        // GST TDS
        if (source === 'gst_tds_per') {
            let pct = parseFloat(gsttds_per_el.value) || 0;
            gsttdsdeducted.value = customRound((totamount * pct) / 100);
        } else if (source === 'gsttds_deducted') {
            let amt = parseFloat(gsttdsdeducted.value) || 0;
            if (totamount > 0) {
                gsttds_per_el.value = parseFloat(((amt / totamount) * 100).toFixed(4));
            } else {
                gsttds_per_el.value = '0';
            }
        }

        // Global recalculation (When base amount changes, or generic call)
        if (!source || source === true || source === false || typeof source !== 'string') {
            let activeId = document.activeElement ? document.activeElement.id : '';
            // Only force calc amount from percentage if we are not actively typing the amount
            if (activeId !== 'tds_deducted' && activeId !== 'gsttds_deducted') {
                let tdsper = parseFloat(tds_per_el.value) || 0;
                let gsttdsper = parseFloat(gsttds_per_el.value) || 0;
                tdsdeducted.value = customRound((totamount * tdsper) / 100);
                gsttdsdeducted.value = customRound((totamount * gsttdsper) / 100);
            }
        }

        // Always calculate credit in bank from the actual values in the deduction boxes
        let tds_ded = parseFloat(tdsdeducted.value) || 0;
        let gst_ded = parseFloat(gsttdsdeducted.value) || 0;
        let lab = parseFloat(laboursess.value) || 0;
        let other_chg = parseFloat(document.getElementById('other_charges')?.value) || 0;

        totcreditamount.value = customRound(totamount - tds_ded - gst_ded - lab - other_chg);

        // Update GST split
        let cgst_box = document.getElementById('cgst_split_box');
        let sgst_box = document.getElementById('sgst_split_box');
        if (cgst_box && sgst_box) {
            let half_gst = customRound((gst_ded / 2).toFixed(2));
            cgst_box.value = half_gst;
            sgst_box.value = half_gst;
        }
    }



    /* ---------- NEW: Project Row Calculation ---------- */
    function addCalc(rowId) {
        let max = parseInt($("#add_rows_id").val()) || 1;
        let sum = 0;
        for (let i = 1; i <= max; i++) {
            if ($("#row_wrap_" + i).length) {
                let val = parseFloat($("#p_receive_amount_" + i).val()) || 0;
                sum += val;
            }
        }

        let type = $("#fund_receive_type").val();
        if (type == "1") {
            $("#tot_receive_amount").val(sum.toFixed(2));
            percent_amt_calc(); // Update TDS and Net amounts
        }

        if (rowId) {
            renderTaxBreakdown(rowId);
        }
    }

    /* ---------- NEW: Per-row CGST/SGST/Net breakdown (display only) ---------- */
    // GST breakdown from inclusive OR exclusive amount
    function renderTaxBreakdown(rowId, opts = {}) {
        const elAmt = document.getElementById('p_receive_amount_' + rowId);
        const holder = document.getElementById('tax_br_' + rowId);
        if (!elAmt || !holder) return;

        const val = parseFloat(elAmt.value) || 0;

        // Options
        const gstRate = (typeof opts.gstRate === 'number' ? opts.gstRate : 0.18); // 18% by default
        const inclusive = (typeof opts.inclusive === 'boolean' ? opts.inclusive : true);
        const halfRate = gstRate / 2;

        let base, cgst, sgst, gross;

        if (inclusive) {
            // val is Gross (incl. GST) — sab customRound se round karo
            gross  = parseFloat(customRound(val));
            base   = parseFloat(customRound(val / (1 + gstRate)));
            cgst   = parseFloat(customRound(base * halfRate));
            // sgst = gross - base - cgst (to avoid rounding drift)
            sgst   = parseFloat(customRound(gross - base - cgst));
        } else {
            // val is Base (excl. GST)
            base   = parseFloat(customRound(val));
            cgst   = parseFloat(customRound(base * halfRate));
            sgst   = parseFloat(customRound(base * halfRate));
            gross  = parseFloat(customRound(base + cgst + sgst));
        }

        // UI text (display)
        const pct = (halfRate * 100).toFixed(0);
        holder.innerHTML =
            'CGST (' + pct + '%): <b>₹' + customRound(cgst) + '</b> | ' +
            'SGST (' + pct + '%): <b>₹' + customRound(sgst) + '</b> | ' +
            'Base: <b>₹' + customRound(base) + '</b>';

        // push ROUNDED values into hidden inputs (yahi DB me save hoga)
        const hc = document.getElementById('cgst_amount_' + rowId);
        const hs = document.getElementById('sgst_amount_' + rowId);
        const hn = document.getElementById('p_diff_amount_' + rowId); // storing base (net-of-tax)
        if (hc) hc.value = customRound(cgst);
        if (hs) hs.value = customRound(sgst);
        if (hn) hn.value = customRound(base);
    }

    function addCalc(line_serial) {
        var id = parseFloat($("#add_rows_id").val());
        var tot = 0;
        for (let i = 1; i <= id; i++) {
            if (!$('#p_receive_amount_' + i).length) continue;
            var amt = parseFloat($('#p_receive_amount_' + i).val());
            if (!amt) { amt = 0; }
            tot += amt;
        }
        $("#tot_receive_amount").val(customRound(tot));
        percent_amt_calc();
        renderTaxBreakdown(line_serial); // NEW
        enforcePerProjectLimit(line_serial);
    }

    /* ---------- New: top toolbar add, per-row remove ---------- */
    function remove_row(rowId) {
        $("#row_wrap_" + rowId).remove();
        $("#row_num_" + rowId).remove();
        addCalc(1); // recalc totals
        update_row_numbers();
    }

    function update_row_numbers() {
        $('.row-serial-number').each(function(index) {
            // First static row has no visible number, first dynamic row is #2
            $(this).text(index + 2);
        });
    }

    /* ---------- Lock & show department on new rows ---------- */
    function getLockedDepartment() {
        const val = $('#department_id_1').val();
        return val ? val : '';
    }

    $('select[multiple]').multiselect();

    function add_rows() {
        var id = parseFloat($("#add_rows_id").val());
        if (!id) { id = 0; }
        id = id + 1;

        var lockedDept = getLockedDepartment();

        var txt = '<div id="row_num_' + id + '" class="mt-3 font-weight-bold row-serial-number" style="font-size: 16px;">' + id + '</div>' +
            '<div id="row_wrap_' + id + '" class="row align-items-start border-top pt-2 mt-2 flex-nowrap overflow-auto">' +
            '<div class="col" style="min-width: 160px;">' +
            '<div class="form-group mb-1">' +
            '<label>विभाग</label>' +
            '<div class="d-flex gap-2 align-items-center">' +
            '<select class="form-control" name="department_id_' + id + '" id="department_id_' + id + '" onChange="fill_district(this.value, ' + id + '),fill_sub_department(this.value, ' + id + ')" ' + (lockedDept ? 'disabled' : '') + '>' +
            '<option value="">--- Select ---</option>';
        <?php
        if (!empty($_SESSION['department'])) {
            $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in (' . implode(",", $_SESSION['department']) . ') group by department_id) ';
        } elseif (!empty($_SESSION['divisions'])) {
            $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in (' . implode(",", $_SESSION['divisions']) . ') group by division_id ) ';
        }
        $run = mysqli_query($db, $query);
        while ($data = mysqli_fetch_array($run)) {
            echo 'txt += "<option value=\'' . $data['sno'] . '\'>' . trim(addslashes($data['department_name_hindi'])) . '</option>";' . "\n";
        }
        ?>
        txt += '</select>' +
            '<button type="button" class="btn btn-outline-danger btn-sm ms-2" title="Remove row" onclick="remove_row(' + id + ')">🗑️</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col" style="min-width: 160px;">' +
            '<div class="form-group mb-1">' +
            '<label>उप विभाग</label>' +
            '<select class="form-control" name="sub_department_id_' + id + '" id="sub_department_id_' + id + '" value=""></select>' +
            '</div>' +
            '</div>' +
            '<div class="col" style="min-width: 160px;">' +
            '<div class="form-group mb-1">' +
            '<label>आच्छादित जनपद</label>' +
            '<select class="form-control" name="district_id_' + id + '" id="district_id_' + id + '" onChange="fill_project(this.value, ' + id + ')"></select>' +
            '</div>' +
            '</div>' +
            '<div class="col" style="min-width: 160px; max-width: 350px;">' +
            '<div class="form-group mb-1">' +
            '<label>परियोजना का नाम <span id="ledger_link_' + id + '"></span></label>' +
            '<select class="form-control" name="project_id_' + id + '" id="project_id_' + id + '" onChange="view_ledger_row(' + id + '); loadProjectInfo(' + id + '); loadProjectMappingInfo(' + id + ');"></select>' +
            '<input type="hidden" id="erp_code_' + id + '">' +
            '<div class="mt-1 small text-muted" id="proj_info_' + id + '"></div>' +
            '</div>' +
            '</div>' +
            '<div class="col" id="ledger_map_col_' + id + '">' +
            '<div class="form-group mb-1">' +
            '<label>Ledger Map <span class="text-success small" id="map_status_' + id + '" style="display:none;">Saved ✓</span> <a href="billit_ledgers.php" target="_blank" title="Create Ledger" style="margin-left:5px; color:#16a34a;"><i class="fas fa-plus-circle"></i></a></label>' +
            '<select class="form-control ledger-select" name="project_ledger_id_' + id + '" id="project_ledger_id_' + id + '" onChange="save_project_ledger_mapping(' + id + ')"></select>' +
            '</div>' +
            '</div>' +
            '<div class="col" style="min-width: 160px;">' +
            '<div class="form-group mb-1">' +
            '<label>परियोजना पर प्राप्त राशि</label>' +
            '<input type="text" name="p_receive_amount_' + id + '" id="p_receive_amount_' + id + '" class="form-control" value="" onInput="addCalc(' + id + ')" onblur="roundInputOnBlur(this); addCalc(' + id + ');">' +
            '<div class="mt-1 small text-muted" id="tax_br_' + id + '"></div>' +
            '<input type="hidden" name="cgst_amount_' + id + '" id="cgst_amount_' + id + '">' +
            '<input type="hidden" name="sgst_amount_' + id + '" id="sgst_amount_' + id + '">' +
            '<input type="hidden" name="p_diff_amount_' + id + '" id="p_diff_amount_' + id + '">' +
            '</div>' +
            '</div>' +
            '</div>';

        $("#rows_container").append(txt);
        $("#add_rows_id").val(id);

        // auto-fill + lock department, while keeping it visible (disabled) and submitted (hidden)
        if (lockedDept) {
            $('#department_id_' + id).val(lockedDept).prop('disabled', true);
            if (!$('#department_id_hidden_' + id).length) {
                $('<input>').attr({ type: 'hidden', id: 'department_id_hidden_' + id, name: 'department_id_' + id, value: lockedDept }).appendTo('#rows_container');
            }
            fill_sub_department(lockedDept, id);
            fill_district(lockedDept, id);
        }

        // NEW: init tax breakdown on empty value
        renderTaxBreakdown(id);

        // Transform new dropdowns to searchable
        ['department_id_' + id, 'sub_department_id_' + id, 'district_id_' + id, 'project_id_' + id].forEach(makeSearchable);

        // Fetch ledgers and initialize Select2 for all ledger-select elements, including the new one
        var currentUnit = $('#unit_id').val() || 53;
        fill_unit_ledgers(currentUnit);

        update_row_numbers();
    }

    var actionUrl = "scripts/ajax_isolated.php";

    // Standard fills
    function fill_sub_department(val, rowId) {
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "sub_dep", "val": val },
            success: function (ajaxdata) {
                var txt = '<option value="">--Select--</option>';
                ajaxdata = JSON.parse(ajaxdata);
                $.each(ajaxdata, function (key, value) {
                    txt += '<option value="' + value.id + '">' + value.sub_department_hindi + '</option>';
                });
                $("#sub_department_id_" + rowId).html(txt).trigger('change');
                if (ajaxdata && ajaxdata.length > 0) {
                    $("#sub_department_id_" + rowId).closest('.col').show();
                } else {
                    $("#sub_department_id_" + rowId).closest('.col').hide();
                }
            }
        });
    }
    function fill_district(val, rowId) {
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "dist", "val": val },
            success: function (data) {
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function (key, value) {
                    txt += '<option value="' + value.id + '">' + value.district_name + '</option>';
                });
                $("#district_id_" + rowId).html(txt).trigger('change');
            }
        });
    }
    function fill_project(distVal, rowId) {
        var deptVal = $("#department_id_" + rowId).val() || getLockedDepartment();
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "proj", "val": distVal, "dept": deptVal },
            success: function (data) {
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function (key, value) {
                    txt += '<option value="' + value.id + '">' + value.project_name_hindi + '</option>';
                });
                $("#project_id_" + rowId).html(txt).trigger('change');
            }
        });
    }

    function loadProjectMappingInfo(rowId) {
        var prj = $("#project_id_" + rowId).val();
        $("#erp_code_" + rowId).val('');

        if (!prj) {
            $("#project_ledger_id_" + rowId).val('').trigger('change.select2');
            $("#ledger_map_col_" + rowId).show();
            return;
        }

        $.ajax({
            url: 'scripts/ajax.php',
            type: 'POST',
            data: { term: 'all', id: 'get_project_mapping_info', project_id: prj, unit_id: $('#unit_id').val() || 53 },
            dataType: 'json',
            success: function (res) {
                if (res.erp_code) {
                    $("#erp_code_" + rowId).val(res.erp_code);
                }
                if (res.ledger_sno) {
                    $("#project_ledger_id_" + rowId).val(res.ledger_sno).trigger('change.select2');
                    $("#ledger_map_col_" + rowId).hide();
                } else {
                    $("#project_ledger_id_" + rowId).val('').trigger('change.select2');
                    $("#ledger_map_col_" + rowId).show();
                }
            }
        });
    }

    function save_project_ledger_mapping(rowId) {
        var ledgerSno = $("#project_ledger_id_" + rowId).val();
        var erpCode = $("#erp_code_" + rowId).val();
        // Do not alert if we are just unselecting or if there's no ERP code on load
        if (!erpCode) {
            return;
        }
        $.ajax({
            url: 'scripts/project_ledger_mapping_ajax.php',
            type: 'POST',
            data: { action: 'save_mapping', erp_code: erpCode, ledger_sno: ledgerSno },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    $('#map_status_' + rowId).show().fadeOut(3000);
                }
            }
        });
    }

    // Prefill versions for edit
    function fill_sub_department_prefill(deptId, rowId, selectedVal) {
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "sub_dep", "val": deptId },
            success: function (ajaxdata) {
                var txt = '<option value="">--Select--</option>';
                ajaxdata = JSON.parse(ajaxdata);
                $.each(ajaxdata, function (key, value) {
                    txt += '<option value="' + value.id + '">' + value.sub_department_hindi + '</option>';
                });
                $("#sub_department_id_" + rowId).html(txt).val(selectedVal).trigger('change');
                if (ajaxdata && ajaxdata.length > 0) {
                    $("#sub_department_id_" + rowId).closest('.col').show();
                } else {
                    $("#sub_department_id_" + rowId).closest('.col').hide();
                }
            }
        });
    }
    function fill_district_prefill(deptId, rowId, selectedVal) {
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "dist", "val": deptId },
            success: function (data) {
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function (key, value) {
                    txt += '<option value="' + value.id + '">' + value.district_name + '</option>';
                });
                $("#district_id_" + rowId).html(txt).val(selectedVal).trigger('change');
            }
        });
    }
    function fill_project_prefill(distVal, rowId, deptVal, selectedVal) {
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "proj", "val": distVal, "dept": deptVal },
            success: function (data) {
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function (key, value) {
                    txt += '<option value="' + value.id + '">' + value.project_name_hindi + '</option>';
                });
                $("#project_id_" + rowId).html(txt).val(selectedVal).trigger('change');
                loadProjectInfo(rowId);
                loadProjectMappingInfo(rowId);
            }
        });
    }

    function view_ledger_row(rowId) {
        var val = $("#project_id_" + rowId).val();
        var txt = val ? '<a href="report_ledger_project.php?id=' + val + '" target="_blank"><small>View Ledger</small></a>' : '';
        $("#ledger_link_" + rowId).html(txt);
    }

    /* ---------- Project info (Lakh→₹) + installments ---------- */
    function loadProjectInfo(rowId) {
        var prj = $("#project_id_" + rowId).val();
        if (!prj) { $("#proj_info_" + rowId).html(''); return; }

        // NEW: when editing, exclude current invoice from Received/Installments; also hide Received in UI
        var exclude = parseInt($("#edit_sno").val() || '0') || 0;

        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { "term": "b", "id": "proj_info", "val": prj, "exclude_invoice": exclude },
            success: function (data) {
                try {
                    var o = JSON.parse(data);
                    var sanc_lakh = parseFloat(o.sanction_cost_lakh) || 0;
                    var sanc_rupees = sanc_lakh * 100000;
                    var rcvd = parseFloat(o.received_to_date) || 0;
                    var inst_cnt = parseInt(o.installments_count || 0);
                    var remain = sanc_rupees - rcvd - sumCurrentFormForProject(prj, rowId);
                    if (remain < 0) remain = 0;

                    var parts = [];
                    parts.push('Sanction: <b>' + customRound(sanc_lakh.toFixed(2)) + ' Lakh</b> (<b>₹' + customRound(sanc_rupees.toFixed(2)) + '</b>)');
                    if (exclude === 0) { // show Received only in create mode
                        parts.push('Received: <b>₹' + customRound(rcvd.toFixed(2)) + '</b>');
                    }
                    parts.push('Installments: <b>' + customRound(inst_cnt) + '</b>');
                    parts.push('Remaining: <b id="remain_' + rowId + '">₹' + customRound(remain.toFixed(2)) + '</b>');

                    $("#proj_info_" + rowId).html(parts.join(' | '));
                    view_ledger_row(rowId);
                    enforcePerProjectLimit(rowId);

                    // NEW: Fetch HO Transfer Details and auto-fill amount
                    if (exclude === 0) {
                        var orderNo = $("#order_no").val();
                        if (orderNo && orderNo.trim() !== '') {
                            $.ajax({
                                type: "POST",
                                url: "scripts/ajax_isolated.php",
                                data: { "id": "fetch_transfer_row", "order_no": orderNo, "project_id": prj },
                                success: function (res) {
                                    try {
                                        var transferData = JSON.parse(res);
                                        if (transferData.status === 'success') {
                                            var transferAmt = parseFloat(transferData.transafer_amount) || 0;
                                            if (transferAmt > 0 && !$("#p_receive_amount_" + rowId).val()) { // Only auto-fill if empty
                                                $("#p_receive_amount_" + rowId).val(transferAmt).trigger("input");
                                                $("#proj_info_" + rowId).append(' <br><span class="text-success"><i class="fas fa-check-circle"></i> HO Transfer Amount Auto-filled: ₹' + transferAmt.toFixed(2) + '</span>');

                                                // Accumulate deductions into the footer
                                                var tds = parseFloat(transferData.incometax) || 0;
                                                var gst = parseFloat(transferData.gsttds) || 0;
                                                var lab = parseFloat(transferData.leborses) || 0;

                                                if (tds > 0) $("#tds_deducted").val((parseFloat($("#tds_deducted").val()) || 0) + tds).trigger("input");
                                                if (gst > 0) $("#gsttds_deducted").val((parseFloat($("#gsttds_deducted").val()) || 0) + gst).trigger("input");
                                                if (lab > 0) $("#labour_sess").val((parseFloat($("#labour_sess").val()) || 0) + lab).trigger("input");
                                            }
                                        }
                                    } catch (e) { }
                                }
                            });
                        }
                    }

                } catch (e) {
                    console.log("test.................................");
                    console.log(e.toString());
                    $("#proj_info_" + rowId).html('<span class="text-danger">Unable to load project info</span>');
                }
            }
        });
    }

    function sumCurrentFormForProject(projectId, exceptRow) {
        var id = parseFloat($("#add_rows_id").val());
        var tot = 0;
        for (let i = 1; i <= id; i++) {
            if (!$("#row_wrap_" + i).length) continue; // row removed
            if (i === exceptRow) continue;
            var pid = $("#project_id_" + i).val();
            if (pid && pid == projectId) {
                var v = parseFloat($("#p_receive_amount_" + i).val()) || 0;
                tot += v;
            }
        }
        return tot;
    }

    function enforcePerProjectLimit(rowId) {
        var prj = $("#project_id_" + rowId).val();
        if (!prj) return;

        var remainEl = $("#remain_" + rowId);
        if (remainEl.length == 0) return;

        var remaining = parseFloat(remainEl.text().replace(/[^\d.-]/g, '')) || 0;
        var thisVal = parseFloat($("#p_receive_amount_" + rowId).val()) || 0;

        if (thisVal > remaining) {
            $("#p_receive_amount_" + rowId).val(remaining.toFixed(2));
            addCalc(rowId);
            alert("Amount exceeds remaining sanction on this project. Adjusted to ₹" + remaining.toFixed(2));
        }
    }

    // Prefill detail rows when editing
    <?php if (isset($_GET['edit_sno']) && $_GET['edit_sno'] != '') {
        $pre = [];
        for ($i = 1; $i <= $_POST['add_rows_id']; $i++) {
            $pre[$i] = [
                'dept' => $_POST['department_id_' . $i] ?? '',
                'sub' => $_POST['sub_department_id_' . $i] ?? '',
                'dist' => $_POST['district_id_' . $i] ?? '',
                'proj' => $_POST['project_id_' . $i] ?? ''
            ];
        }
        ?>
        $(function () {
            // ensure first row exists
            $('#add_rows_length').first().attr('id', 'row_wrap_1');

            // Prefill selects for each row
            var pre = <?php echo json_encode($pre); ?>;
            for (const k in pre) {
                const rowId = parseInt(k);
                const d = pre[k];
                if ($('#department_id_' + rowId).length) {
                    $('#department_id_' + rowId).val(d.dept);
                }
                fill_sub_department_prefill(d.dept, rowId, d.sub);
                fill_district_prefill(d.dept, rowId, d.dist);
                fill_project_prefill(d.dist, rowId, d.dept, d.proj);
            }

            // NEW: render tax breakdowns for prefilled amounts
            var max = parseInt($("#add_rows_id").val()) || 1;
            for (let i = 1; i <= max; i++) {
                if ($("#p_receive_amount_" + i).length) { renderTaxBreakdown(i); }
            }
        });
    <?php } ?>

    $(function () {
        // On load (create mode), render tax breakdowns too
        var max = parseInt($("#add_rows_id").val()) || 1;
        for (let i = 1; i <= max; i++) {
            if ($("#p_receive_amount_" + i).length) { renderTaxBreakdown(i); }
        }

        $("#sale_form").on("submit", function (e) {
            var id = parseFloat($("#add_rows_id").val()) || 1;

            // 1. Check Project Ledgers
            for (let i = 1; i <= id; i++) {
                let $projRow = $("#row_wrap_" + i);
                let $projId = $("#project_id_" + i);
                let $amt = $("#p_receive_amount_" + i);
                let $ledger = $("#project_ledger_id_" + i);

                if ($projRow.length && $projId.length && $projId.val() && $amt.length && parseFloat($amt.val()) > 0) {
                    if (!$ledger.val() || $ledger.val().trim() === '') {
                        alert("Kripya row " + i + " ke liye 'Project Ledger Map' select karein.");
                        e.preventDefault();
                        $ledger.focus();
                        return false;
                    }
                }
            }

            // Removed legacy checkFinancialLedger validation since unit settings now handles mappings and split logic.

            for (let i = 1; i <= id; i++) {
                if ($("#row_wrap_" + i).length && $("#project_id_" + i).length && $("#project_id_" + i).val()) {
                    enforcePerProjectLimit(i);
                }
            }
        });
    });

</script>


<form id="sale_form" name="sale_form" class="no-print" autocomplete="off" enctype="multipart/form-data" method="post"
    action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-danger text-white d-flex align-items-center justify-content-center">
                    <i class="fas fa-wallet me-2"></i>&nbsp;&nbsp;
                    <h5 class="card-title mb-0 text-white">Fund Receive</h5>
                </div>
                <div class="card-body py-4">
                    <?php
                    if ($msg != '') {
                        echo '<h5>' . alert($msg) . '</h5>';
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fund Received type</label>
                                <select name="fund_receive_type" id="fund_receive_type" class="form-control"
                                    onchange="onchangetype(), readonly_input()" tabindex="<?php echo $tab++; ?>">
                                    <option value="1" <?php echo ($_POST['fund_receive_type'] == '1' ? 'selected' : ''); ?>>Through Project</option>
                                    <option value="2" <?php echo ($_POST['fund_receive_type'] == '2' ? 'selected' : ''); ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label>Fund Received to</label>

                            <?php
                            $username = $_SESSION['username'];
                            $isHo = ($username == "headacc" || $_SESSION['usertype'] == "sadmin" || $_SESSION['usertype'] == "6") ? 1 : 0;
                            $frt = @$_POST['fund_receive_to'] ?: ($isHo ? 'HO' : 'Unit');
                            ?>
                            <select name="fund_receive_to" id="fund_receive_to" class="form-control form-select"
                                tabindex="<?php echo $tab++; ?>" onchange="fund_receive_to()" <?php echo $isHo ? '' : 'style="pointer-events: none; background-color: #e9ecef;"'; ?>>
                                <option value="HO" <?php echo ($frt == 'HO') ? 'selected' : ''; ?>>
                                    HO
                                </option>
                                <option value="Unit" <?php echo ($frt == 'Unit') ? 'selected' : ''; ?>>
                                    UNIT
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3" id="unit">
                            <label>Division</label>
                            <select name="unit_id" id="unit_id" class="form-control form-select"
                                onchange="fill_bank_details(this.value); fill_unit_ledgers(this.value);">
                                <option value="">-select-</option>
                                <?php
                                if (is_array($_SESSION['divisions'])) {
                                    foreach ($_SESSION['divisions'] as $k => $v) {
                                        // Also select if user only has 1 division
                                        $is_default = (count($_SESSION['divisions']) == 1 && $_SESSION['divisions'][0] == $v);
                                        $selected = (@$_POST['unit_id'] == $v || $is_default) ? 'selected' : '';
                                        echo '<option value="' . $v . '" ' . $selected . '>' . get_division($v) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Installment</label>
                            <select name="installment" id="installment" class="form-control form-select"
                                tabindex="<?php echo $tab++; ?>">
                                <option value="">Select--</option>
                                <?php
                                $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X'];
                                for ($k = 1; $k <= 10; $k++) {
                                    $sel = (@$_POST['installment'] == $k ? 'selected' : '');
                                    echo '<option value="' . $k . '" ' . $sel . '>' . $romans[$k] . ' Installment</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Order No.(GO)</label>
                            <input type="text" name="order_no" id="order_no" class="form-control"
                                onblur="getOrderData(this.value)" value="<?php echo @$_POST['order_no']; ?>"
                                tabindex="<?php echo $tab++; ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label>Voucher Number <small class="text-muted"></small></label>
                            <input type="text" name="voucher_no" id="voucher_no" class="form-control"
                                   value="<?php echo @$_POST['voucher_no'] ?: $auto_voucher_no; ?>" tabindex="<?php echo $tab++; ?>"
                                   placeholder="FR-2627-0001" oninput="setTodayDate()" readonly required>
                        </div>
                        <div class="col-md-3 mb-3" style="margin-bottom: 1rem;">
                            <label>Order Date</label>
                            <script type="text/javascript" language="javascript">
                                document.writeln(DateInput('order_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo @$_POST['order_date']; ?>', <?php echo $tab;
                                   $tab += 4; ?>));
                            </script>
                        </div>
                        <div class="col-md-3 mb-3" style="margin-bottom: 1rem;">
                            <label>Received Date</label>
                            <script type="text/javascript" language="javascript">
                                document.writeln(DateInput('receive_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo @$_POST['receive_date']; ?>', <?php echo $tab;
                                   $tab += 4; ?>));
                            </script>
                        </div>
                    </div>

                    <!-- 2. Project Details Card -->
                    <div class="card mb-4 " id="hide_project_type"
                        style="display:none;">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 text-danger font-weight-bold">Project-wise Allocation
                            </h5>
                            <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="add_rows()">+
                                Add
                                Row</button>
                        </div>
                        <div class="card-body p-4">
                            <input type="hidden" name="division_id" id="division_id" class="form-control"
                                value="<?php echo $_POST['division_id']; ?>" readonly tabindex="<?php echo $tab++; ?>">

                            <!-- First row (existing markup adapted with wrapper & remove icon) -->
                            <div id="rows_container">
                                <?php for ($i = 1; $i <= $_POST['add_rows_id']; $i++) { ?>
                                    <div id="row_wrap_<?php echo $i; ?>"
                                        class="row align-items-start flex-nowrap overflow-auto <?php echo ($i > 1 ? 'border-top pt-2 mt-2' : ''); ?>">
                                        <div class="col">
                                            <div class="form-group mb-1">
                                                <label>Department</label>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <select class="form-control" name="department_id_<?php echo $i; ?>"
                                                        id="department_id_<?php echo $i; ?>"
                                                        tabindex="<?php echo $tab++; ?>"
                                                        onChange="fill_district(this.value, <?php echo $i; ?>), fill_sub_department(this.value, <?php echo $i; ?>)">
                                                        <option value="">--- Select ---</option>
                                                        <?php
                                                        if (!empty($_SESSION['department'])) {
                                                            $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in (' . implode(",", $_SESSION['department']) . ') group by department_id) ';
                                                        } elseif (!empty($_SESSION['divisions'])) {
                                                            $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in (' . implode(",", $_SESSION['divisions']) . ') group by division_id ) ';
                                                        }
                                                        $run = mysqli_query($db, $query);
                                                        while ($data = mysqli_fetch_array($run)) {
                                                            echo '<option value="' . $data['sno'] . '" ';
                                                            if (isset($_POST['department_id_' . $i]) && $_POST['department_id_' . $i] == $data['sno']) {
                                                                echo ' selected="Selected"';
                                                            }
                                                            echo '>' . trim($data['department_name_hindi']) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <?php if ($i > 1) { ?>
                                                        <button type="button" class="btn btn-outline-danger btn-sm ms-2"
                                                            title="Remove row" onclick="remove_row(<?php echo $i; ?>)">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col" style="min-width: 160px;">
                                            <div class="form-group mb-1">
                                                <label>Sub Department</label>
                                                <select class="form-control" name="sub_department_id_<?php echo $i; ?>"
                                                    id="sub_department_id_<?php echo $i; ?>"
                                                    value="<?php echo $_POST['sub_department_id_' . $i] ?? ''; ?>"
                                                    tabindex="<?php echo $tab++; ?>"></select>
                                            </div>
                                        </div>

                                        <div class="col" style="min-width: 160px;">
                                            <div class="form-group mb-1">
                                                <label>District</label>
                                                <select class="form-control" name="district_id_<?php echo $i; ?>"
                                                    id="district_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>"
                                                    onChange="fill_project(this.value, <?php echo $i; ?>)"></select>
                                            </div>
                                        </div>

                                        <div class="col" style="min-width: 160px;">
                                            <div class="form-group mb-1">
                                                <label>Project<span id="ledger_link_<?php echo $i; ?>"></span></label>
                                                <select class="form-control" name="project_id_<?php echo $i; ?>"
                                                    id="project_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>"
                                                    onChange="view_ledger_row(<?php echo $i; ?>); loadProjectInfo(<?php echo $i; ?>); loadProjectMappingInfo(<?php echo $i; ?>);"></select>
                                                <input type="hidden" id="erp_code_<?php echo $i; ?>">
                                                <div class="mt-1 small text-muted" id="proj_info_<?php echo $i; ?>"></div>
                                            </div>
                                        </div>

                                        <div class="col" id="ledger_map_col_<?php echo $i; ?>">
                                            <div class="form-group mb-1">
                                                <label>Ledger Map <span class="text-success small"
                                                        id="map_status_<?php echo $i; ?>" style="display:none;">Saved
                                                        ✓</span> <a href="billit_ledgers.php" target="_blank"
                                                        title="Create Ledger" style="margin-left:5px; color:#16a34a;"><i
                                                            class="fas fa-plus-circle"></i></a></label>
                                                <select class="form-control ledger-select"
                                                    name="project_ledger_id_<?php echo $i; ?>"
                                                    id="project_ledger_id_<?php echo $i; ?>"
                                                    tabindex="<?php echo $tab++; ?>"
                                                    onChange="save_project_ledger_mapping(<?php echo $i; ?>)"></select>
                                            </div>
                                        </div>

                                        <div class="col" style="min-width: 160px;">
                                            <div class="form-group mb-1">
                                                <label>Project Received Amount</label>
                                                <input type="text" name="p_receive_amount_<?php echo $i; ?>"
                                                    id="p_receive_amount_<?php echo $i; ?>" class="form-control"
                                                    value="<?php echo customRound($_POST['p_receive_amount_' . $i] ?? 0); ?>"
                                                    tabindex="<?php echo $tab++; ?>" onInput="addCalc(<?php echo $i; ?>)"
                                                    onblur="roundInputOnBlur(this); addCalc(<?php echo $i; ?>);">
                                                <div class="mt-1 small text-muted" id="tax_br_<?php echo $i; ?>"></div>

                                                <!-- NEW: send displayed values via POST -->
                                                <input type="hidden" name="cgst_amount_<?php echo $i; ?>"
                                                    id="cgst_amount_<?php echo $i; ?>">
                                                <input type="hidden" name="sgst_amount_<?php echo $i; ?>"
                                                    id="sgst_amount_<?php echo $i; ?>">
                                                <input type="hidden" name="p_diff_amount_<?php echo $i; ?>"
                                                    id="p_diff_amount_<?php echo $i; ?>">

                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <input type="hidden" name="add_rows_id" id="add_rows_id"
                                value="<?php echo $_POST['add_rows_id']; ?>">
                        </div>
                    </div>


                    <!-- 3. Financial Settings Card -->
                    <div class="card mb-4 ">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 text-danger font-weight-bold">Deduction details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div style="display:none;">
                                <input type="hidden" name="tds_ledger_id" id="tds_ledger_id"
                                    value="<?php echo @$_POST['tds_ledger_id']; ?>">
                                <input type="hidden" name="gst_tds_ledger_id" id="gst_tds_ledger_id"
                                    value="<?php echo @$_POST['gst_tds_ledger_id']; ?>">
                                <input type="hidden" name="labour_cess_ledger_id" id="labour_cess_ledger_id"
                                    value="<?php echo @$_POST['labour_cess_ledger_id']; ?>">
                                <input type="hidden" name="other_charges_ledger_id" id="other_charges_ledger_id"
                                    value="<?php echo @$_POST['other_charges_ledger_id']; ?>">
                            </div>
                            <div class="row align-items-end">
                                <div class="col-md-1 mb-3" style="padding-right: 5px;">
                                    <label >TDS(%)</label>
                                    <input type="text" name="tds_per" id="tds_per" class="form-control px-1"
                                        value="<?php echo @$_POST['tds_per']; ?>" tabindex="<?php echo $tab++; ?>"
                                        onInput="percent_amt_calc('tds_per')">
                                </div>
                                <div class="col-md-1 mb-3" style="padding-left: 5px; padding-right: 5px;">
                                    <label >TDS Amount</label>
                                    <input type="text" name="tds_deducted" id="tds_deducted" class="form-control px-1"
                                        value="<?php echo customRound(@$_POST['tds_deducted']); ?>" tabindex="<?php echo $tab++; ?>"
                                        onInput="percent_amt_calc('tds_deducted')" onblur="roundInputOnBlur(this); percent_amt_calc();">
                                </div>
                                <div class="col-md-1 mb-3" style="padding-left: 5px; padding-right: 5px;">
                                    <label >GST TDS (%)</label>
                                    <input type="text" name="gst_tds_per" id="gst_tds_per" class="form-control px-1"
                                        value="<?php echo @$_POST['gst_tds_per']; ?>" tabindex="<?php echo $tab++; ?>"
                                        onInput="percent_amt_calc('gst_tds_per')">
                                </div>
                                <div class="col-md-2 mb-3" style="padding-left: 5px; padding-right: 5px;">
                                    <label >GST TDS Amount</label>
                                    <input type="text" name="gsttds_deducted" id="gsttds_deducted"
                                        class="form-control px-1" value="<?php echo customRound(@$_POST['gsttds_deducted']); ?>"
                                        tabindex="<?php echo $tab++; ?>" onInput="percent_amt_calc('gsttds_deducted')" onblur="roundInputOnBlur(this); percent_amt_calc();">
                                </div>
                                <div class="col-md-1 mb-3" style="padding-left: 5px; padding-right: 5px;">
                                    <label >CGST TDS</label>
                                    <input type="text" id="cgst_split_box" class="form-control px-1"
                                        style="background-color: #f0fdf4; color: #000; font-weight: bold; border-color: #bbf7d0;"
                                        readonly>
                                </div>
                                <div class="col-md-1 mb-3" style="padding-left: 5px; padding-right: 5px;">
                                    <label >SGST TDS</label>
                                    <input type="text" id="sgst_split_box" class="form-control px-1"
                                        style="background-color: #f0fdf4; color: #000; font-weight: bold; border-color: #bbf7d0;"
                                        readonly>
                                </div>
                                <div class="col-md-2 mb-3" style="padding-left: 5px; padding-right: 5px;">
                                    <label >Labour Cess</label>
                                    <input type="text" name="labour_sess" id="labour_sess" class="form-control px-1"
                                        value="<?php echo customRound(@$_POST['labour_sess']); ?>" tabindex="<?php echo $tab++; ?>"
                                        onInput="percent_amt_calc()" onblur="roundInputOnBlur(this); percent_amt_calc();">
                                </div>
                                <div class="col-md-2 mb-3" style="padding-left: 5px;">
                                    <label >Other Charges</label>
                                    <input type="text" name="other_charges" id="other_charges" class="form-control px-1"
                                        value="<?php echo customRound(@$_POST['other_charges']); ?>" tabindex="<?php echo $tab++; ?>"
                                        onInput="percent_amt_calc()" onblur="roundInputOnBlur(this); percent_amt_calc();">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Dashboard Totals Card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 text-danger font-weight-bold">Overview Totals</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-end">
                                <div class="col-md-2 mb-3">
                                    <label>Total Received Amount</label>
                                    <input onInput="percent_amt_calc()" type="text" name="tot_receive_amount"
                                        id="tot_receive_amount" class="form-control"
                                        value="<?php echo customRound(@$_POST['tot_receive_amount']); ?>"
                                        tabindex="<?php echo $tab++; ?>" style="background-color: #fdfaf9;"
                                        onblur="roundInputOnBlur(this); percent_amt_calc();">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label>Total Credit Amount In Bank</label>
                                    <input type="text" name="tot_credit_amount" id="tot_credit_amount"
                                        class="form-control" value="<?php echo customRound(@$_POST['tot_credit_amount']); ?>"
                                        tabindex="<?php echo $tab++; ?>" style="background-color: #f6fdf6;" readonly>
                                </div>

                                <div class="col-md-4 mb-3" id="bank_ho">
                                    <label>Bank Details (HO)</label>
                                    <select class="form-control form-select" name="bank_name" id="bank_name"
                                        tabindex="<?php echo $tab++; ?>">
                                        <option value="">--- Select ---</option>
                                        <?php
                                        $query = 'select * from billit_customer where unit_id="53" and (parent IN ("479", "61") or (account_no is not null and account_no != "")) order by cus_name';
                                        $run = mysqli_query($db, $query);
                                        while ($data = mysqli_fetch_array($run)) {
                                            echo '<option value="' . $data['sno'] . '" ' . ((@$_POST['bank_name'] == $data['sno']) ? 'selected' : '') . '>' . trim($data['cus_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3" id="bank_unit" style="display:none;">
                                    <label>Bank Details (Unit)</label>
                                    <select class="form-control form-select" name="bank_name" id="bank_name_unit"
                                        tabindex="<?php echo $tab++; ?>">
                                        <option value="">--Select--</option>
                                        <?php /* Options load via AJAX when Unit Name is selected — fill_bank_details() */ ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Remark (Review)</label>
                                    <textarea name="remark" id="remark" class="form-control"
                                        style="height: 40px; padding: 6px 12px; font-size: 14px;" rows="1"
                                        tabindex="<?php echo $tab++; ?>"><?php echo trim(@$_POST['remark']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end main outer card-body -->
				
				    <!-- Submit Section -->
					<div class="text-center mb-5">
						<button type="submit" name="submit" class="btn btn-success btn-lg px-5"
							>
							<?php echo !empty($_POST['edit_sno']) ? 'Update' : 'Submit'; ?>
						</button>
						<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo @$_POST['edit_sno']; ?>">
					</div>
			</div> <!-- end main outer card -->
		</div>
	</div>


</form>

<div class="row">
    <div class="col-md-12">
        <!-- 5. Recent Fund Receives Table -->
        <div class="card shadow mb-4">
            <div class="card-header bg-danger text-white d-flex align-items-center justify-content-start">
                <i class="fas fa-list-ul me-2"></i>&nbsp;&nbsp;
                <h5 class="card-title mb-0 text-white">List of Fund Received </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="fundReceiveTable" style="width:100%">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th class="text-center text-white">S.No.</th>
                                <th class="text-center text-white">Voucher No.</th>
                                <th class="text-center text-white">FundReceivedAt</th>
                                <th class="text-center text-white">OrderNo</th>
                                <th class="text-center text-white">OrderDate</th>
                                <th class="text-center text-white">Installment</th>
                                <th class="text-center text-white">TotalReceivedAmount</th>
                                <th class="text-center text-white">Remark</th>
                                <th class="no-print text-left text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Unit-wise filter: HO / sadmin see all; unit users see only their unit's records
                            $isAdmin = (isset($_SESSION['username']) && in_array($_SESSION['username'], ['sadmin', 'headacc']));
                            if ($isAdmin || empty($_SESSION['divisions'])) {
                                // HO/sadmin or no division restriction — show all
                                $sql = 'SELECT ifr.* FROM `invoice_fund_receive` ifr ORDER BY ifr.sno DESC LIMIT 200';
                            } else {
                                // Unit user — only show records whose journal entry matches this user's division(s)
                                $divIds = implode(',', array_map('intval', $_SESSION['divisions']));
                                $sql = 'SELECT ifr.* 
                                        FROM `invoice_fund_receive` ifr
                                        INNER JOIN `billit_invoice_erp_receipt` bier
                                            ON bier.table_name = "invoice_fund_receive"
                                           AND bier.table_id = ifr.sno
                                           AND bier.unit_id IN (' . $divIds . ')
                                        ORDER BY ifr.sno DESC
                                        LIMIT 200';
                            }
                            $result = execute_query($sql);
                            $i = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $actId = (int) $row['sno'];

                                // Resolve unit name for FundReceivedAt column
                                if ($row['fund_receive_to'] === 'Unit') {
                                    $jr2 = mysqli_fetch_assoc(execute_query(
                                        'SELECT unit_id FROM billit_invoice_erp_receipt 
                                         WHERE table_name="invoice_fund_receive" AND table_id="' . $actId . '" LIMIT 1'
                                    ));
                                    $receivedAtLabel = ($jr2 && $jr2['unit_id']) ? htmlspecialchars(get_division($jr2['unit_id'])) : 'Unit';
                                } else {
                                    $receivedAtLabel = htmlspecialchars($row['fund_receive_to']); // HO
                                }

                                echo '<tr>
                            <td class="text-right">' . $i++ . '</td>
                            <td class="text-left">' . htmlspecialchars($row['voucher_no']) . '</td>
                            <td class="text-left">' . $receivedAtLabel . '</td>
                            <td class="text-left">' . htmlspecialchars($row['order_no']) . '</td>
                            <td class="text-left">' . htmlspecialchars($row['order_date']) . '</td>
                            <td class="text-right">' . (isset($romans[$row['installment']]) ? $romans[$row['installment']] : htmlspecialchars($row['installment'])) . '</td>
                            <td class="text-right">' . htmlspecialchars($row['tot_receive_amount']) . '</td>
                            <td class="text-left">' . htmlspecialchars($row['remark']) . '</td>
                            <td class="no-print actions-col text-left" style="white-space:nowrap">
                                <div class="dropdown">
                                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                  </button>
                                  <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="' . $_SERVER['PHP_SELF'] . '?edit_sno=' . $actId . '">✏️ Edit</a>
                                    <a class="dropdown-item" target="_blank" href="fund_recive_details.php?id=' . $actId . '">👁️ View Details</a>
                                    <a class="dropdown-item" target="_blank" href="billit_recive_print.php?voucher=' . $actId . '">🧾 Voucher</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="' . $_SERVER['PHP_SELF'] . '?delid=' . $actId . '" onclick="return confirm(\'Delete this entry?\')">🗑️ Delete</a>
                                  </div>
                                </div>
                            </td>
                            </tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    page_footer_start();
    ?>

    <script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
    <script>
        function fill_bank_details(val, preselect) {
            if (!val) return;
            console.log('[Bank] fill_bank_details called: unit_id=', val, 'preselect=', preselect);
            $.ajax({
                type: "POST",
                url: "scripts/ajax.php",
                data: { "term": "b", "id": "unit_bank", "val": val },
                success: function (ajaxdata) {
                    console.log('[Bank] AJAX raw response:', ajaxdata);
                    var txt = '<option value="">--Select--</option>';
                    try {
                        var data = JSON.parse(ajaxdata);
                        if (data && data.length > 0) {
                            $.each(data, function (key, value) {
                                var sel = (preselect && String(value.id) === String(preselect)) ? ' selected' : '';
                                txt += '<option value="' + value.id + '"' + sel + '>' + value.cus_name + '</option>';
                            });
                        } else {
                            console.warn('[Bank] No bank accounts found for unit_id=' + val);
                        }
                    } catch (e) {
                        console.error('[Bank] JSON parse error:', e, 'Response was:', ajaxdata);
                    }
                    $("#bank_name_unit").html(txt).trigger('change');
                    makeSearchable('bank_name_unit');
                },
                error: function (xhr, status, err) {
                    console.error('[Bank] AJAX error:', status, err);
                }
            });
        }

        // Auto-load Unit bank on page load (for both create & edit modes)
        $(function () {
            var savedUnit = '<?php echo isset($_POST['unit_id']) ? intval($_POST['unit_id']) : ''; ?>';
            var savedBank = '<?php echo isset($_POST['bank_name']) ? intval($_POST['bank_name']) : ''; ?>';
            if (savedUnit) {
                fill_bank_details(savedUnit, savedBank);
            }
        });

        function fill_unit_ledgers(unit_id) {
            if (!unit_id) return;
            $.ajax({
                url: 'scripts/ajax.php?id=get_unit_ledgers&unit_id=' + unit_id + '&term=all',
                dataType: 'json',
                success: function (ledgers) {
                    var options = '<option value="">--- Select Ledger ---</option>';
                    if (ledgers && ledgers.length > 0) {
                        ledgers.forEach(function (l) {
                            options += '<option value="' + l.id + '">' + l.text + '</option>';
                        });
                    }

                    $('.ledger-select').each(function () {
                        var currentVal = $(this).val();
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }
                        $(this).html(options);
                        if (currentVal) $(this).val(currentVal); // retain value if already set
                        $(this).select2({
                            theme: 'bootstrap4',
                            width: '100%'
                        });
                    });

                    // Fetch unit default settings for TDS/GST/Labour if not prefilled
                    $.ajax({
                        url: 'scripts/ajax.php?id=get_unit_settings&unit_id=' + unit_id + '&term=all',
                        dataType: 'json',
                        success: function (data) {
                            if (data && !window.has_prefilled_ledgers) {
                                if ($('#tds_ledger_id').val() == '') $('#tds_ledger_id').val(data.ITTDS);
                                if ($('#gst_tds_ledger_id').val() == '') $('#gst_tds_ledger_id').val(data.GSTTDS);
                                if ($('#labour_cess_ledger_id').val() == '') $('#labour_cess_ledger_id').val(data.LABORCESS);
                                if ($('#other_charges_ledger_id').val() == '') $('#other_charges_ledger_id').val(data.OTHER_CHARGES);
                                window.has_prefilled_ledgers = true;
                            }
                        }
                    });
                }
            });
        }

        function getOrderData(orderNo) {
            console.log("getOrderData called with:", orderNo);

            if (!orderNo || orderNo.trim() === '') {
                console.log("Empty order number, skipping AJAX call");
                return;
            }

            $.ajax({
                type: "POST",
                url: "scripts/ajax.php",
                data: {
                    "term": "b",
                    "id": "order_no",
                    "val": orderNo
                },
                success: function (ajaxdata) {
                    console.log("Raw AJAX response:", ajaxdata);
                    try {
                        var data = JSON.parse(ajaxdata);
                        console.log("Parsed JSON:", data);

                        if (data.status == "success") {
                            console.log("Success! Order date:", data.order_date);
                            $("#voucher_no").val(data.voucher_no.trim());
                            updateDate("order_date", data.order_date);
                            updateDate("receive_date", "<?php echo date('Y-m-d'); ?>");
                        } else {
                            console.log("Error response:", data);
                            updateDate("order_date", data.order_date || "<?php echo date('Y-m-d'); ?>");
                            updateDate("receive_date", "<?php echo date('Y-m-d'); ?>");
                        }
                    } catch (e) {
                        console.error("JSON parsing error:", e);
                        console.error("Raw response:", ajaxdata);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    console.error("Response text:", xhr.responseText);
                }
            });
        }

        function updateDate(fieldName, dateValue) {
            console.log("updateDate called:", fieldName, dateValue);
            if (!dateValue) {
                console.log("No date value, returning");
                return;
            }
            var parts = dateValue.split("-");
            var year = parts[0];
            var month = parseInt(parts[1], 10) - 1;
            var day = parseInt(parts[2], 10);

            console.log("Date parts - Year:", year, "Month:", month, "Day:", day);

            $("#" + fieldName).val(dateValue);
            $("#" + fieldName + "_Month_ID").val(month);
            $("#" + fieldName + "_Day_ID").val(day);
            $("#" + fieldName + "_Year_ID").val(year);

            console.log("Updated values:");
            console.log(fieldName + " field:", $("#" + fieldName).val());
            console.log(fieldName + "_Month_ID:", $("#" + fieldName + "_Month_ID").val());
            console.log(fieldName + "_Day_ID:", $("#" + fieldName + "_Day_ID").val());
            console.log(fieldName + "_Year_ID:", $("#" + fieldName + "_Year_ID").val());
        }
    </script>
    <?php
    page_footer_end();
    ?>

    <!-- DataTables Buttons libs are now included globally in settings.php -->

    <script>
        $(document).ready(function () {
            if ($.fn.DataTable.isDataTable('#fundReceiveTable')) {
                $('#fundReceiveTable').DataTable().destroy();
            }
            $('#fundReceiveTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']],
                dom: '<"d-flex justify-content-between align-items-center mb-2"lB>frtip',
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columns',
                        className: 'btn btn-info btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Fund Receive Report',
                        exportOptions: { columns: ':visible:not(.no-print)' }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Fund Receive Report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: ':visible:not(.no-print)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-secondary btn-sm',
                        title: 'Fund Receive Report',
                        exportOptions: { columns: ':visible:not(.no-print)' }
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