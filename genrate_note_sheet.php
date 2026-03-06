<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");

$msg = '';
$tab = 1;

$module_name = "project_note_sheet";

$preview_bills = [];
$total_received_amount = 0.0;
$past_approved_expense = 0.0;
$project_id = 0;

// CSRF token for form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

// Filters (GET)
$filter_division   = isset($_GET['filter_division']) ? intval($_GET['filter_division']) : 0;
$filter_district   = isset($_GET['filter_district']) ? intval($_GET['filter_district']) : 0;
$filter_department = isset($_GET['filter_department']) ? intval($_GET['filter_department']) : 0;
$filter_erp        = isset($_GET['filter_erp']) ? trim($_GET['filter_erp']) : '';

// ======================= HELPERS =========================
function as_int_list(array $arr): string {
    return implode(',', array_map('intval', $arr));
}

/**
 * Recalculate note sheet amounts excluding forwarded/rejected bills
 * Returns array with updated amounts and bill count
 */
function recalculateNoteSheetAmounts($note_sheet_id, $db) {
    global $moduleName;
    
    // Get note sheet details
    $ns_query = "SELECT related_bill_snos, project_id FROM project_note_sheet WHERE id = $note_sheet_id";
    $ns_result = $db->query($ns_query);
    
    if (!$ns_result || $ns_result->num_rows === 0) {
        return null;
    }
    
    $ns_row = $ns_result->fetch_assoc();
    $all_bill_snos = $ns_row['related_bill_snos'];
    $project_id = $ns_row['project_id'];
    
    if (empty($all_bill_snos)) {
        return [
            'total_transfer' => 0,
            'total_net_pay' => 0,
            'bill_count' => 0
        ];
    }
    
    // Get approval request ID for this note sheet
    $ar_query = "SELECT id FROM approval_requests WHERE row_id = $note_sheet_id AND module_name = 'project_note_sheet'";
    $ar_result = $db->query($ar_query);
    
    $forwarded_bills = [];
    // if ($ar_result && $ar_result->num_rows > 0) {
        // $ar_row = $ar_result->fetch_assoc();
        // $request_id = $ar_row['id'];
        
        // // Get all forwarded bills for this request
        // $fwd_query = "SELECT DISTINCT bill_sno 
                     // FROM bill_approval_selections 
                     // WHERE request_id = $request_id 
                     // AND action = 'forward'";
        // $fwd_result = $db->query($fwd_query);
        
        // if ($fwd_result) {
            // while ($fwd_row = $fwd_result->fetch_assoc()) {
                // $forwarded_bills[] = $fwd_row['bill_sno'];
            // }
        // }
    // }
    
    // Calculate amounts for non-forwarded bills only
    $bill_snos_array = explode(',', $all_bill_snos);
    $active_bills = array_diff($bill_snos_array, $forwarded_bills);
    
    if (empty($active_bills)) {
        return [
            'total_transfer' => 0,
            'total_net_pay' => 0,
            'bill_count' => 0
        ];
    }
    
    $active_bills_str = implode(',', array_map('intval', $active_bills));
    
    // Calculate sums for active bills
    $sum_query = "SELECT 
                    COALESCE(SUM(transafer_amount), 0) AS total_transfer,
                    COALESCE(SUM(praposemoney), 0) AS total_net_pay
                  FROM invoice_account_fund_transafer 
                  WHERE sno IN ($active_bills_str)";
    
    $sum_result = $db->query($sum_query);
    
    if ($sum_result) {
        $sums = $sum_result->fetch_assoc();
        return [
            'total_transfer' => $sums['total_transfer'],
            'total_net_pay' => $sums['total_net_pay'],
            'bill_count' => count($active_bills)
        ];
    }
    
    return null;
}

// ======================= PREVIEW (GET) ===================
if (isset($_GET['preview_project_id'])) {
    $project_id = intval($_GET['preview_project_id']);

    // Check if project is allocated to a tender/firm
    $tender_check_sql = "
        SELECT project_awarded_to 
        FROM tender_allotment 
        WHERE project_id = {$project_id} 
          AND (project_awarded_to IS NOT NULL AND project_awarded_to != '' AND project_awarded_to != '0')
          AND status != '5'
        LIMIT 1
    ";
    $tender_check_res = $db->query($tender_check_sql);
    $is_allocated = false;
    if ($tender_check_res && $tender_check_res->num_rows > 0) {
        $tender_row = $tender_check_res->fetch_assoc();
        if (!empty($tender_row['project_awarded_to'])) {
            $is_allocated = true;
        }
    }

    // Store allocation status for JavaScript alert
    $show_tender_alert = !$is_allocated;
    
    if (!$is_allocated) {
        // Don't show bills if not allocated
        $preview_bills = [];
    } else {
        // Bills not yet included in any notesheet
        $bill_sql = "
            SELECT sno, praposemoney, total_expen, bill_no, transafer_amount
            FROM invoice_account_fund_transafer
            WHERE project_name = {$project_id} AND genrate_note_sheet = 0
        ";
        if ($bills_res = $db->query($bill_sql)) {
            while ($bill = $bills_res->fetch_assoc()) {
                $preview_bills[] = $bill;
            }
        }
    }

    // Total received funds for project
    $received_sql = "
        SELECT COALESCE(SUM(p_receive_amount),0) AS total_received
        FROM transaction_fund_receive
        WHERE project_id = {$project_id}
    ";
    if ($received_res = $db->query($received_sql)) {
        if ($row = $received_res->fetch_assoc()) {
            $total_received_amount = (float)$row['total_received'];
        }
    }

    // Past approved (already notesheeted) expense
    $past_sql = "
        SELECT COALESCE(SUM(total_expen),0) AS past_expense
        FROM invoice_account_fund_transafer
        WHERE project_name = {$project_id} AND genrate_note_sheet = 1 ";
    if ($past_res = $db->query($past_sql)) {
        if ($row = $past_res->fetch_assoc()) {
            $past_approved_expense = (float)$row['past_expense'];
        }
    }
}

// ======================= CREATE (POST) ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_note_sheet'])) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $msg .= "<p style='color:red;'>Invalid/expired form token. Please retry.</p>";
    } else {
        $project_id = (int)($_POST['project_id'] ?? 0);
        $bill_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['selected_bills'] ?? []))));
        $note_sheet_no = trim($_POST['note_sheet_no'] ?? '');

        if (!$project_id || empty($bill_ids)) {
            $msg .= "<p>No bills selected for note sheet generation.</p>";
        } else {
            // Check if project is allocated to a tender/firm
            $tender_check_sql = "
                SELECT project_awarded_to 
                FROM tender_allotment 
                WHERE project_id = {$project_id} 
                  AND (project_awarded_to IS NOT NULL AND project_awarded_to != '' AND project_awarded_to != '0')
                  AND status != '5'
                LIMIT 1
            ";
            $tender_check_res = $db->query($tender_check_sql);
            $is_allocated = false;
            if ($tender_check_res && $tender_check_res->num_rows > 0) {
                $tender_row = $tender_check_res->fetch_assoc();
                if (!empty($tender_row['project_awarded_to'])) {
                    $is_allocated = true;
                }
            }

            if (!$is_allocated) {
                $_SESSION['msg'] = "Tender not received — please submit it first.";
                header('Location: ' . $_SERVER['PHP_SELF'] . '?preview_project_id=' . $project_id);
                exit;
            } else {
                $ids_sql = as_int_list($bill_ids);

                // Recompute server-side sums for selected bills
            $sum_q = $db->query("
                SELECT 
                    COALESCE(SUM(transafer_amount),0) AS sum_transfer,
                    COALESCE(SUM(praposemoney),0)    AS sum_prapose,
                    COALESCE(SUM(total_expen),0)     AS sum_expense
                FROM invoice_account_fund_transafer
                WHERE project_name = {$project_id} AND sno IN ($ids_sql)
            ");
            $sums = $sum_q ? $sum_q->fetch_assoc() : ['sum_transfer'=>0,'sum_prapose'=>0,'sum_expense'=>0];
            $sum_transfer = (float)$sums['sum_transfer'];
            $sum_prapose  = (float)$sums['sum_prapose'];
            $sum_expense  = (float)$sums['sum_expense'];

            // Total received for project
            $rcv_q = $db->query("
                SELECT COALESCE(SUM(p_receive_amount),0) AS total_received
                FROM transaction_fund_receive WHERE project_id = {$project_id}
            ");
            $total_received_amount_post = (float)($rcv_q->fetch_assoc()['total_received'] ?? 0);

            // Past approved expense (already notesheeted)
            $past_q = $db->query("
                SELECT COALESCE(SUM(total_expen),0) AS past_expense
                FROM invoice_account_fund_transafer
                WHERE project_name = {$project_id} AND genrate_note_sheet = 1
            ");
            $past_expense_post = (float)($past_q->fetch_assoc()['past_expense'] ?? 0);

            $grand_expense = $sum_expense + $past_expense_post;

            // Validate funds vs expenses
            if ($grand_expense > $total_received_amount_post) {
                $msg .= "<p style='color:red;'>Note sheet cannot be generated: Grand Expenses (₹".number_format($grand_expense,2).") exceed Total Received (₹".number_format($total_received_amount_post,2).").</p>";
            } else {
                // Duplicate guard: see if any selected bill already used
                $dup_q = $db->query("
                    SELECT COUNT(*) AS used_count
                    FROM invoice_account_fund_transafer
                    WHERE sno IN ($ids_sql) AND genrate_note_sheet = 1
                ");
                $used_count = (int)($dup_q->fetch_assoc()['used_count'] ?? 0);

                if ($used_count > 0) {
                    $msg .= "<p style='color:red;'>Some selected bills are already linked to another note sheet.</p>";
                } else {
                    // Transaction block
                    $db->begin_transaction();
                    try {
                        $bill_count = count($bill_ids);
                        $bill_snos_str = implode(',', $bill_ids);

                        $stmt = $db->prepare("
                            INSERT INTO project_note_sheet
                            (project_id, related_bill_snos, bill_count, note_sheet_no, 
                             total_transfer, total_net_pay, total_expense, 
                             total_rcv_amt, total_privious_expense, grand_total_expense)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        if (!$stmt) { throw new Exception($db->error); }

                        $stmt->bind_param(
                            "isisssssss",
                            $project_id, $bill_snos_str, $bill_count, $note_sheet_no,
                            $sum_transfer, $sum_prapose, $sum_expense,
                            $total_received_amount_post, $past_expense_post, $grand_expense
                        );

                        if (!$stmt->execute()) { throw new Exception($stmt->error); }
                        $insert_id = $db->insert_id;
                        $stmt->close();

                        // Concurrency-safe update: only flip flags that are currently 0
                        $db->query("
                            UPDATE invoice_account_fund_transafer 
                            SET genrate_note_sheet = 1 
                            WHERE project_name = {$project_id} 
                              AND sno IN ($ids_sql) 
                              AND genrate_note_sheet = 0
                        ");
                        if ($db->affected_rows !== $bill_count) {
                            throw new Exception("One or more selected bills were already used.");
                        }

                        $db->commit();

                        // genrate reqquest approval flow
                        createApprovalRequest($module_name, $insert_id);

                        // Flash + PRG
                        $_SESSION['msg'] = "✅ Note sheet #".htmlspecialchars($note_sheet_no)." created (ID {$insert_id}).";
                        // Rotate token so back/refresh cannot re-submit
                        unset($_SESSION['csrf_token']);

                        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                        exit;
                    } catch (Exception $ex) {
                        $db->rollback();
                        $msg .= "<p style='color:red;'>Error: ".htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8')."</p>";
                    }
                }
            }
            }
        }
    }
}

// ======================= DELETE (GET) ====================
if (isset($_GET['delid']) && ($_SESSION['usertype'] ?? '') == "1") {
    $delid = intval($_GET['delid']);

    // Check if used in adviser
    $check = $db->query("SELECT id FROM project_bill_adviser WHERE notesheet_id = '{$delid}'");
    if ($check && $check->num_rows > 0) {
        $_SESSION['msg'] = "❌ पहले आप इस नोटशीट से संबंधित Advice को डिलीट करें।";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Fetch related bill ids
    $res = $db->query("SELECT related_bill_snos FROM project_note_sheet WHERE id = {$delid}");
    $row = $res ? $res->fetch_assoc() : null;

    if ($row) {
        $snos_raw = $row['related_bill_snos'];
        $sno_arr = array_map('intval', explode(",", $snos_raw));
        $sno_list = implode(",", $sno_arr);

        // Reset bills
        if ($sno_list !== '') {
            $db->query("UPDATE invoice_account_fund_transafer SET genrate_note_sheet = 0 WHERE sno IN ({$sno_list})");
        }

        // Soft delete notesheet
        $db->query("UPDATE project_note_sheet SET delete_status = 1 WHERE id = {$delid}");

        $_SESSION['msg'] = "✅ नोटशीट सफलतापूर्वक डिलीट कर दी गई।";
    } else {
        $_SESSION['msg'] = "❌ नोटशीट नहीं मिली।";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ======================= PAGE LAYOUT =====================
page_header_start();
page_header_end();
page_sidebar();
?>

<?php
// Build filter options from master tables (scoped by user divisions present in projects)
// Filters should be available to all users who can view the note sheets list
$divisionsForScope = isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) ? $_SESSION['divisions'] : [0];
$scopeDivisionSql = "
    SELECT DISTINCT upt.division_id AS id, divn.division_name AS name
    FROM uprnss_project_temp upt
    LEFT JOIN uprnss_division divn ON divn.s_no = upt.division_id
    WHERE upt.division_id IN (".as_int_list($divisionsForScope).")
    ORDER BY name
";
$scopeDistrictSql = "
    SELECT DISTINCT upt.district_id AS id, dist.district_name_hindi AS name
    FROM uprnss_project_temp upt
    LEFT JOIN uprnss_district dist ON dist.sno = upt.district_id
    WHERE upt.division_id IN (".as_int_list($divisionsForScope).")
    ORDER BY name
";
$scopeDeptSql     = "
    SELECT DISTINCT upt.department_id AS id, dept.department_name_hindi AS name
    FROM uprnss_project_temp upt
    LEFT JOIN uprnss_department_name dept ON dept.sno = upt.department_id
    WHERE upt.division_id IN (".as_int_list($divisionsForScope).")
    ORDER BY name
";
$scopeErpSql      = "
    SELECT DISTINCT erp_code FROM uprnss_project_temp 
    WHERE division_id IN (".as_int_list($divisionsForScope).") 
    ORDER BY erp_code
";

$divisionOptions = $db->query($scopeDivisionSql);
$districtOptions = $db->query($scopeDistrictSql);
$departmentOptions = $db->query($scopeDeptSql);
$erpOptions = $db->query($scopeErpSql);
?>


        <!-- Project Selection Section (Form 2) -->
        <?php if (($_SESSION['usertype'] ?? '') == "9") { ?>
        <div class="card" style="margin-top: 20px;">
            <div class="card-header text-center">
                <h4 style="margin-bottom:0;">Select Project to Preview Note Sheet</h4>
            </div>
            <div class="card-body">
                <form method="get" action="" class="no-print">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-8">
                                    <select name="preview_project_id" class="form-control" required>
                                        <option value="">-- Select Project --</option>
                                        <?php
                                        $divisions = isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) ? $_SESSION['divisions'] : [0];
                                        // Filter conditions removed to show all projects unless scoped by division
                                        $projects_sql = "
                                            SELECT DISTINCT iat.project_name AS project_sno, project_name_hindi, erp_code 
                                            FROM invoice_account_fund_transafer iat
                                            JOIN uprnss_project_temp upt ON iat.project_name = upt.sno
                                            WHERE iat.genrate_note_sheet = 0 
                                              AND upt.division_id IN (".as_int_list($divisions).")
                                            ORDER BY project_name_hindi ASC
                                        ";
                                        if ($projects_result = $db->query($projects_sql)) {
                                            while ($project = $projects_result->fetch_assoc()) {
                                                $selected = ((int)$project['project_sno'] === (int)$project_id) ? 'selected' : '';
                                                echo "<option value='{$project['project_sno']}' {$selected}>".htmlspecialchars($project['project_name_hindi'])." (".htmlspecialchars($project['erp_code']).")</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-info" style="width: 100%;">Preview</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php } ?>
        
    <div class="col-md-12">

<style>
.unstyled-input {
    border: none;
    background: transparent;
    outline: none;
    font-weight: bold;
    color: #000;
    pointer-events: none; /* makes it visually read-only */
}
</style>
<style>
  /* 🎨 UPRNSS Sidebar Matching Theme */
  :root {
    --red-light: #e53935;
    --red-dark: #b71c1c;
    --yellow: #f6b800;
    --green: #008b00;
    --soft-bg: #fff8f6;
  }

  body {

    background: var(--soft-bg);
    font-family: 'Segoe UI', sans-serif;
  }

  /* Cards */
  .card-custom {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    background: #fff;
    margin-bottom: 25px;
  }

  .card-custom .card-header {
    background: linear-gradient(45deg, var(--red-light), var(--red-dark));
    color: white;
    font-weight: 600;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    padding: 12px 18px;
    letter-spacing: 0.5px;
  }

  /* Form controls */
  .form-select, .form-control {
    border-radius: 8px;
  }

  /* Primary button */
  .btn-primary {
    background: linear-gradient(45deg, var(--red-light), var(--red-dark));
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 8px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    background: linear-gradient(45deg, var(--red-dark), var(--yellow));
    transform: scale(1.03);
  }

  /* Success (Print) button */
  .btn-success {
    background: linear-gradient(45deg, var(--yellow), var(--red-light));
    border: none;
    color: #fff;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.3s ease;
  }

  .btn-success:hover {
    background: linear-gradient(45deg, var(--red-dark), var(--yellow));
  }

  /* Table Styling */
  .table-custom {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  }

  table thead th {
    background: linear-gradient(45deg, var(--red-light), var(--red-dark));
    text-align: center;
    vertical-align: middle;
    font-size: 13px;
    white-space: nowrap;
	color:white !important;
  }

  table tbody td {
    font-size: 12px;
    text-align: center;
    vertical-align: middle;
  }

  table tr:nth-child(even) {
    background-color: #fff5f5;
  }

  table tr:hover {
    background-color: #ffeaea;
  }

  .sticky-header thead th {
    position: sticky;
    top: 0;
    z-index: 2;
  }
/* GLOBAL PERFECT CENTER ALIGN FOR ALL INPUTS & SELECTS */
input.form-control,
select.form-select,
input[type="text"],
input[type="date"],
input[type="number"],
select {
    height: 40px !important;
    /* text-align: center !important; */
    padding: 5px 10px !important;
}


/* For placeholder center */
input::placeholder,
select::placeholder {
    text-align: center !important;
}
/* Sidebar always on top */
.sidebar {
    position: fixed;
    z-index: 9999 !important;
}

/* Your fixed TH behind sidebar */
table th {
    position: sticky;
    top: 0;
    z-index: 10 !important;
}



  /* Print mode */
 
</style>
<?php 
// Show alert if project is not allocated to tender (before form)
if (isset($show_tender_alert) && $show_tender_alert) {
    echo "<script>alert('Tender not received — please submit it first.');</script>";
}

if (!empty($preview_bills)) { 
    // Check if project is allocated for JavaScript validation
    $js_tender_check_sql = "
        SELECT project_awarded_to 
        FROM tender_allotment 
        WHERE project_id = {$project_id} 
          AND (project_awarded_to IS NOT NULL AND project_awarded_to != '' AND project_awarded_to != '0')
          AND status != '5'
        LIMIT 1
    ";
    $js_tender_check_res = $db->query($js_tender_check_sql);
    $js_is_allocated = false;
    if ($js_tender_check_res && $js_tender_check_res->num_rows > 0) {
        $js_tender_row = $js_tender_check_res->fetch_assoc();
        if (!empty($js_tender_row['project_awarded_to'])) {
            $js_is_allocated = true;
        }
    }
?>
<form method="post" action="" id="noteSheetForm" onsubmit="return validateTenderAllocation();">
    <input type="hidden" name="project_id" value="<?php echo (int)$project_id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
    <input type="hidden" id="is_project_allocated" value="<?php echo $js_is_allocated ? '1' : '0'; ?>">

    <div class="card">
        <div class="card-header">
            <h5 class="text-center">Select Bills for Note Sheet</h5>
        </div>
        <div class="card-body">

            <div class="row mx-auto">
                <div class="col-md-5 text-center">
                    <div class="form-group">
                        <label><b>Note Sheet number</b></label>
                        <input type="text" name="note_sheet_no" id="note_sheet_no" class="form-control" value="" tabindex="<?php echo $tab++; ?>">
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover" id="billTable" style="margin-bottom: 0;">
                <thead>
                    <tr>
                            <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap;">
                                <label style="margin:0; cursor: pointer;">
                                    <input type="checkbox" id="selectAllBills" style="margin-right: 5px; cursor: pointer;">
                                Select
                            </label>
                        </th>
                            <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">S.No</th>
                            <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap;color:black !important;">Bill No.</th>
                            <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap;color:black !important;">Transfer Amount</th>
                            <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap;color:black !important;">Net Payment</th>
                            <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap;color:black !important;">Total Expenses</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($preview_bills as $bill) { ?>
                    <tr>
                            <td style="text-align: center; vertical-align: middle; padding: 10px;">
                            <input type="checkbox"
                                   name="selected_bills[]"
                                   class="bill-checkbox"
                                   data-transfer="<?php echo (float)$bill['transafer_amount']; ?>"
                                   data-expense="<?php echo (float)$bill['total_expen']; ?>"
                                   data-prapose="<?php echo (float)$bill['praposemoney']; ?>"
                                   value="<?php echo (int)$bill['sno']; ?>"
                                       checked
                                       style="cursor: pointer;">
                        </td>
                            <td style="text-align: center; vertical-align: middle; padding: 10px; font-weight: 500;"><?php echo $i++; ?></td>
                            <td style="text-align: center; vertical-align: middle; padding: 10px;"><?php echo htmlspecialchars($bill['bill_no']); ?></td>
                            <td style="text-align: right; vertical-align: middle; padding: 10px; font-weight: 500;"><?php echo number_format((float)$bill['transafer_amount'], 2); ?></td>
                            <td style="text-align: right; vertical-align: middle; padding: 10px; font-weight: 500;"><?php echo number_format((float)$bill['praposemoney'], 2); ?></td>
                            <td style="text-align: right; vertical-align: middle; padding: 10px; font-weight: 500;"><?php echo number_format((float)$bill['total_expen'], 2); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                            <th colspan="3" class="text-right" style="padding: 12px; font-weight: 600;">Total (Checked Bills):</th>
                            <th style="background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%); text-align: right; padding: 12px; font-weight: 600;">₹<input type="text" id="totalTransfer" name="totalTransfer" value="0.00" class="unstyled-input" style="font-weight: 600;"></th>
                            <th style="background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%); text-align: right; padding: 12px; font-weight: 600;">₹<input type="text" id="totalPrapose"  name="totalPrapose"  value="0.00" class="unstyled-input" style="font-weight: 600;"></th>
                            <th style="background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%); text-align: right; padding: 12px; font-weight: 600;">₹<input type="text" id="totalExpense"  name="totalExpense"  value="0.00" class="unstyled-input" style="font-weight: 600;"></th>
                    </tr>
                </tfoot>
            </table>
            </div>

            <p>
                <strong>Total Received Amount:</strong>
                ₹ <input type="text" id="totalrcvamt" name="totalrcvamt"
                         value="<?php echo number_format($total_received_amount,2,'.',''); ?>" class="unstyled-input">
                &nbsp; &nbsp;
                <strong>Previous Approved Expenses:</strong>
                ₹ <input type="text" id="total_priv_Expense" name="total_priv_Expense"
                         value="<?php echo number_format($past_approved_expense,2,'.',''); ?>" class="unstyled-input">
                <br>
                <strong>Grand Total Expenses (Previous + Checked):</strong>
                ₹ <input type="text" id="grandExpense" name="grandExpense" value="0.00" class="unstyled-input">
            </p>

            <div class="text-center">
                <button type="submit" name="generate_note_sheet" class="btn btn-success" id="submitBtn" disabled>
                    Generate Note Sheet
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Enhanced table styling with software matching colors
    const style = document.createElement('style');
    style.innerHTML = `
        /* Table Header Styling - Subtle gradient matching software */
        .table-header-row {
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%) !important;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        .table-header-row th {
            background: transparent !important;
            border: 1px solid #dee2e6;
            color: #212529;
        }
        
        /* Sticky header for bill table */
        #billTable thead tr {
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%) !important;
        }
        #billTable thead th {
            position: sticky;
            top: 0;
            background: transparent !important;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }
        #billTable tfoot tr {
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%) !important;
        }
        #billTable tfoot th {
            position: sticky;
            bottom: 0;
            background: transparent !important;
            z-index: 9;
            box-shadow: 0 -2px 2px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }
        
        /* Table row hover effects - subtle */
        .table-data-row {
            transition: background-color 0.2s ease;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #ffffff;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
        
        /* Table responsive wrapper */
        .table-responsive {
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        /* Table borders */
        .table-bordered {
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
        }
        
        /* Better spacing for table cells */
        .table td, .table th {
            padding: 10px 12px;
        }
        
        /* Pagination styling */
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
        }
        .pagination .page-item {
            margin: 0 2px;
        }
        .pagination .page-link {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #0056b3;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            font-weight: 600;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }
    `;
    document.head.appendChild(style);

    const checkboxes    = document.querySelectorAll(".bill-checkbox");
    const totalTransfer = document.getElementById("totalTransfer");
    const totalPrapose  = document.getElementById("totalPrapose");
    const totalExpense  = document.getElementById("totalExpense");
    const grandExpense  = document.getElementById("grandExpense");
    const submitBtn     = document.getElementById("submitBtn");
    const selectAll     = document.getElementById("selectAllBills");

    const totalReceived = parseFloat("<?php echo (float)$total_received_amount; ?>");
    const pastApproved  = parseFloat("<?php echo (float)$past_approved_expense; ?>");

    function updateTotals() {
        let transferSum = 0, praposeSum = 0, expenseSum = 0;
        let anyChecked = false;

        checkboxes.forEach(chk => {
            if (chk.checked) {
                transferSum += parseFloat(chk.dataset.transfer || "0");
                praposeSum  += parseFloat(chk.dataset.prapose  || "0");
                expenseSum  += parseFloat(chk.dataset.expense  || "0");
                anyChecked = true;
            }
        });

        totalTransfer.value = transferSum.toFixed(2);
        totalPrapose.value  = praposeSum.toFixed(2);
        totalExpense.value  = expenseSum.toFixed(2);

        const totalGrandExpense = expenseSum + pastApproved;
        grandExpense.value = totalGrandExpense.toFixed(2);

        // Enable only when valid
        submitBtn.disabled = !(anyChecked && totalGrandExpense <= totalReceived);
    }

    checkboxes.forEach(chk => chk.addEventListener("change", updateTotals));
    if (selectAll) {
        selectAll.addEventListener("change", function () {
            checkboxes.forEach(chk => { chk.checked = selectAll.checked; });
            updateTotals();
        });
    }
    updateTotals();
});

// Validate tender allocation before form submission
function validateTenderAllocation() {
    const isAllocated = document.getElementById('is_project_allocated');
    if (isAllocated && isAllocated.value === '0') {
        alert('Tender not received — please submit it first.');
        return false;
    }
    return true;
}
</script>
<?php } ?>

<style>
/* Fix overlapping issue by ensuring sidebar is always on top */
.sidebar, .main-panel {
    z-index: 1000 !important; 
    /* Note: main-panel usually shouldn't need high z-index, but sidebar definitely does. 
       However, if we lift sidebar, we must ensure it stays above the sticky header. */
}
.sidebar {
    z-index: 1050 !important; /* Bootstrap modals are usually 1050, so sidebar should be high */
}

/* Sticky header for Generated Note Sheets table */
.generated-note-sheets .table-responsive {
    /* Ensure scroll container is defined */
    position: relative;
    overflow-y: auto; /* explicit scroll */
}
.generated-note-sheets table thead th {
    position: sticky;
    top: 0;
    /* Reduced z-index to be well below the sidebar (1050) but above table content */
    z-index: 10;
    /* Match the green color from the screenshot */
    background-color: #1a6f60;
    color: white;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
</style>

<div class="card generated-note-sheets" style="margin-top:15px;">
    <div class="card-header text-center">
        <h4>Generated Note Sheets</h4>
    </div>
    <div class="card-body">
        <!-- Moved Filters Section -->
        <div style="margin-bottom: 20px; border-bottom: 1px solid #dee2e6; padding-bottom: 20px;">
            <form method="get" action="" class="no-print">
                <div class="row" style="align-items:flex-end;">
                    <div class="col-md-3">
                        <label><small>Division</small></label>
                        <select name="filter_division" class="form-control" title="Filter by Division">
                            <option value="0">All</option>
                            <?php if ($divisionOptions) { 
                                $divisionOptions->data_seek(0);
                                while ($opt = $divisionOptions->fetch_assoc()) { 
                                    $val = (int)$opt['id'];
                                    $text = $opt['name'] !== null && $opt['name'] !== '' ? $opt['name'] : (string)$val;
                                    $sel = $val === $filter_division ? 'selected' : '';
                                    echo "<option value=\"{$val}\" {$sel}>".htmlspecialchars($text)."</option>";
                                } 
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><small>District</small></label>
                        <select name="filter_district" class="form-control" title="Filter by District">
                            <option value="0">All</option>
                            <?php if ($districtOptions) { 
                                $districtOptions->data_seek(0);
                                while ($opt = $districtOptions->fetch_assoc()) { 
                                    $val = (int)$opt['id'];
                                    $text = $opt['name'] !== null && $opt['name'] !== '' ? $opt['name'] : (string)$val;
                                    $sel = $val === $filter_district ? 'selected' : '';
                                    echo "<option value=\"{$val}\" {$sel}>".htmlspecialchars($text)."</option>";
                                } 
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><small>Department</small></label>
                        <select name="filter_department" class="form-control" title="Filter by Department">
                            <option value="0">All</option>
                            <?php if ($departmentOptions) { 
                                $departmentOptions->data_seek(0);
                                while ($opt = $departmentOptions->fetch_assoc()) { 
                                    $val = (int)$opt['id'];
                                    $text = $opt['name'] !== null && $opt['name'] !== '' ? $opt['name'] : (string)$val;
                                    $sel = $val === $filter_department ? 'selected' : '';
                                    echo "<option value=\"{$val}\" {$sel}>".htmlspecialchars($text)."</option>";
                                } 
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><small>ERP Code</small></label>
                        <?php 
                        $erp_js_arr = "[]";
                        if ($erpOptions && $erpOptions->num_rows > 0) {
                            $e_codes = [];
                            $erpOptions->data_seek(0);
                            while ($row = $erpOptions->fetch_assoc()) {
                                if(!empty($row['erp_code'])) {
                                    $e_codes[] = $row['erp_code'];
                                }
                            }
                            $erp_js_arr = json_encode($e_codes);
                        }
                        ?>
                        <input type="text" name="filter_erp" id="filter_erp" class="form-control" value="<?php echo htmlspecialchars($filter_erp); ?>" placeholder="Type ERP Code" autocomplete="off">
                        <script>
                        $(function() {
                            var availableErpCodes = <?php echo $erp_js_arr; ?>;
                            $("#filter_erp").autocomplete({
                                source: availableErpCodes,
                                minLength: 1
                            });
                        });
                        </script>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-md-8">
                        <?php
                        $activeBadges = [];
                        if ($filter_division) { $activeBadges[] = '<span class="badge badge-info" style="margin-right:6px;">Division</span>'; }
                        if ($filter_district) { $activeBadges[] = '<span class="badge badge-info" style="margin-right:6px;">District</span>'; }
                        if ($filter_department) { $activeBadges[] = '<span class="badge badge-info" style="margin-right:6px;">Department</span>'; }
                        if ($filter_erp !== '') { $activeBadges[] = '<span class="badge badge-info" style="margin-right:6px;">ERP: '.htmlspecialchars($filter_erp).'</span>'; }
                        if (!empty($activeBadges)) {
                            echo '<div>'.implode('', $activeBadges).'</div>';
                        } else {
                            echo '<div><small>No filters applied</small></div>';
                        }
                        ?>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="color: black;"  class="btn btn-default btn-sm">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <?php
        if (isset($_SESSION['msg'])) {
            echo "<script>alert('".addslashes($_SESSION['msg'])."');</script>";
            unset($_SESSION['msg']);
        }
		// Flash after PRG
		if (isset($_GET['success']) && !empty($_SESSION['msg'])) {
			echo '<div class="alert alert-success" style="margin:10px 15px;">'.$_SESSION['msg'].'</div>';
			unset($_SESSION['msg']);
		}
		// Inline $msg errors
		if (!empty($msg)) {
			echo '<div class="alert alert-warning" style="margin:10px 15px;">'.$msg.'</div>';
		}
        ?>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
            <thead>
                    <tr class="table-header-row">
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Sr. No</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Division</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">District</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Department</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">ERP Code</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Project Name</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Total Received</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Total Bill</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Total Net Payment</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Bill Count</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Created At</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Current With</th>
                        <th style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Status</th>
                        <th class="no-print" style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Action</th>
                        <th class="no-print" style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap; color:black !important;">Note Sheet</th>
                    <?php if (($_SESSION['usertype'] ?? '') == "1") { ?>
                            <th class="no-print" style="text-align: center; vertical-align: middle; padding: 12px; font-weight: 600; white-space: nowrap;">Delete</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $divisions = isset($_SESSION['divisions']) && is_array($_SESSION['divisions']) ? $_SESSION['divisions'] : [0];

                // Pagination setup
                $per_page = 12;
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $offset = ($page - 1) * $per_page;

                // Build WHERE clause for filters
                $where_clause = "WHERE upt.division_id IN (".as_int_list($divisions).") 
                      AND pns.delete_status = 0
                      ".($filter_division ? " AND upt.division_id = {$filter_division}" : "")."
                      ".($filter_district ? " AND upt.district_id = {$filter_district}" : "")."
                      ".($filter_department ? " AND upt.department_id = {$filter_department}" : "")."
                      ".($filter_erp !== '' ? " AND upt.erp_code = '".$db->real_escape_string($filter_erp)."'" : "");

                // Get total count
                $count_sql = "
                    SELECT COUNT(*) AS total
                    FROM project_note_sheet pns
                    JOIN uprnss_project_temp upt ON pns.project_id = upt.sno
                    {$where_clause}
                ";
                $count_res = $db->query($count_sql);
                $count_row = $count_res ? $count_res->fetch_assoc() : ['total' => 0];
                $total_records = (int)$count_row['total'];
                $total_pages = ceil($total_records / $per_page);

                // Main query with pagination
                $note_sheet_sql = "
                    SELECT pns.*, upt.project_name_hindi
                         , upt.division_id, upt.district_id, upt.department_id, upt.erp_code
                         , divn.division_name AS division_name
                         , dist.district_name_hindi AS district_name
                         , dept.department_name_hindi AS department_name
                    FROM project_note_sheet pns
                    JOIN uprnss_project_temp upt ON pns.project_id = upt.sno
                    LEFT JOIN uprnss_division divn ON divn.s_no = upt.division_id
                    LEFT JOIN uprnss_district dist ON dist.sno = upt.district_id
                    LEFT JOIN uprnss_department_name dept ON dept.sno = upt.department_id
                    {$where_clause}
                    ORDER BY pns.id DESC
                    LIMIT {$per_page} OFFSET {$offset}
                ";

                if ($note_sheet_res = $db->query($note_sheet_sql)) {
                    if ($note_sheet_res->num_rows > 0) {
                        $d = $offset + 1;
                        while ($row = $note_sheet_res->fetch_assoc()) {
                            echo "<tr class='table-data-row'>";
                            echo "<td style='text-align: center; vertical-align: middle; padding: 10px; font-weight: 500;'>".($d++)."</td>";
                            $divText = $row['division_name'] !== null && $row['division_name'] !== '' ? $row['division_name'] : $row['division_id'];
                            $distText = $row['district_name'] !== null && $row['district_name'] !== '' ? $row['district_name'] : $row['district_id'];
                            $deptText = $row['department_name'] !== null && $row['department_name'] !== '' ? $row['department_name'] : $row['department_id'];
                            echo "<td style='text-align: left; vertical-align: middle; padding: 10px;'>".htmlspecialchars($divText)."</td>";
                            echo "<td style='text-align: left; vertical-align: middle; padding: 10px;'>".htmlspecialchars($distText)."</td>";
                            echo "<td style='text-align: left; vertical-align: middle; padding: 10px;'>".htmlspecialchars($deptText)."</td>";
                            echo "<td style='text-align: center; vertical-align: middle; padding: 10px; font-weight: 500;'>".htmlspecialchars($row['erp_code'])."</td>";
                            echo "<td style='text-align: left; vertical-align: middle; padding: 10px;'>".htmlspecialchars($row['project_name_hindi'])."</td>";
                            echo "<td style='text-align: right; vertical-align: middle; padding: 10px; font-weight: 500;'>".htmlspecialchars($row['total_rcv_amt'])."</td>";
                            
                            // Recalculate amounts excluding forwarded bills
                            $recalc = recalculateNoteSheetAmounts($row['id'], $db);
                            $display_total_transfer = $recalc ? $recalc['total_transfer'] : $row['total_transfer'];
                            $display_total_net_pay = $recalc ? $recalc['total_net_pay'] : $row['total_net_pay'];
                            $display_bill_count = $recalc ? $recalc['bill_count'] : $row['bill_count'];
                            
                            echo "<td style='text-align: right; vertical-align: middle; padding: 10px; font-weight: 500;'>".htmlspecialchars($display_total_transfer)."</td>";
                            echo "<td style='text-align: right; vertical-align: middle; padding: 10px; font-weight: 500;'>".htmlspecialchars($display_total_net_pay)."</td>";
                            echo "<td style='text-align: center; vertical-align: middle; padding: 10px; font-weight: 500;'>".htmlspecialchars($display_bill_count)."</td>";
                            echo "<td style='text-align: left; vertical-align: middle; padding: 10px; white-space: nowrap;'>".htmlspecialchars($row['created_at'])."</td>";

                            // Approval trail: prints "Current With", "Status"
                            echo getApprovalTrailRow($module_name, $row['id']);

                            // Print link only if approved
                            if ((int)$row['status'] === 1) {
                                echo '<td class="no-print text-center" style="vertical-align: middle; padding: 10px;">';
                                echo '<a target="_blank" href="bill_note_sheet.php?id='.(int)$row['id'].'" onClick="return confirm(\'Are you sure you?\');" style="color: #007bff; text-decoration: none;"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Note sheet"></span> Print</a>';
                                echo '</td>';
                            } else {
                                echo '<td class="no-print text-center" style="vertical-align: middle; padding: 10px;">Pending</td>';
                            }

                            if (($_SESSION['usertype'] ?? '') == "1") {
                                echo '<td class="no-print text-center" style="vertical-align: middle; padding: 10px;">
                                    <a href="?delid='.(int)$row['id'].'"
                                       onclick="return confirm(\'क्या आप वाकई इसे हटाना चाहते हैं?\');"
                                       class="btn btn-sm btn-danger" 
                                       data-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>';
                            }

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='16' style='text-align: center; padding: 20px; color: #6c757d;'>No Note Sheets Generated</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='16' style='text-align: center; padding: 20px; color: #dc3545;'>Error loading note sheets.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-3 mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <small class="text-muted">
                    Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?> records
                </small>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0" style="margin: 0;">
                    <?php
                    $current_url = $_SERVER['PHP_SELF'];
                    $query_params = $_GET;
                    
                    // Previous button
                    if ($page > 1) {
                        $query_params['page'] = $page - 1;
                        $prev_url = $current_url . '?' . http_build_query($query_params);
                        echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars($prev_url).'">&laquo; Previous</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>';
                    }

                    // Page numbers
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1) {
                        $query_params['page'] = 1;
                        $first_url = $current_url . '?' . http_build_query($query_params);
                        echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars($first_url).'">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $query_params['page'] = $i;
                        $page_url = $current_url . '?' . http_build_query($query_params);
                        if ($i == $page) {
                            echo '<li class="page-item active"><span class="page-link">'.$i.'</span></li>';
                        } else {
                            echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars($page_url).'">'.$i.'</a></li>';
                        }
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        $query_params['page'] = $total_pages;
                        $last_url = $current_url . '?' . http_build_query($query_params);
                        echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars($last_url).'">'.$total_pages.'</a></li>';
                    }

                    // Next button
                    if ($page < $total_pages) {
                        $query_params['page'] = $page + 1;
                        $next_url = $current_url . '?' . http_build_query($query_params);
                        echo '<li class="page-item"><a class="page-link" href="'.htmlspecialchars($next_url).'">Next &raquo;</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<?php
page_footer_end();
