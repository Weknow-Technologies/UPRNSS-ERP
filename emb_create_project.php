<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("scripts/settings.php");         // expects $db  (main DB with uprnss_*)
include("scripts/setting_dbase_emb.php"); // expects $db_emb (EMB DB with master/parent/projects etc.)

/* ============================================================
   Helpers
============================================================ */
function q_main($s){ global $db; return mysqli_real_escape_string($db, (string)$s); }
function q_emb($s){ global $db_emb; return mysqli_real_escape_string($db_emb, (string)$s); }
function is_num($v){ return isset($v) && is_numeric($v); }
function post($k,$d=''){ return isset($_POST[$k]) ? $_POST[$k] : $d; }

/**
 * Ensure uprnss_project_temp has emb_project_id (INT NULL) without touching erp_code.
 */
function ensure_emb_project_id_column(){
    global $db;
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'uprnss_project_temp' AND COLUMN_NAME = 'emb_project_id'";
    $res = mysqli_query($db, $sql);
    if (!$res || mysqli_num_rows($res) === 0) {
        @mysqli_query($db, "ALTER TABLE `uprnss_project_temp` ADD COLUMN `emb_project_id` INT NULL DEFAULT NULL AFTER `erp_code`");
    }
}

/**
 * AJAX: real-time uniqueness check for job_code against master_projects.master_job_code
 * Usage: ?ajax=check_job_code&job_code=VALUE
 */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_job_code') {
    header('Content-Type: application/json; charset=utf-8');
    $job = isset($_GET['job_code']) ? trim($_GET['job_code']) : '';
    $exists = false;
    if ($job !== '') {
        $j = q_emb($job);
        $rs = mysqli_query($db_emb, "SELECT 1 FROM master_projects WHERE master_job_code = '$j' LIMIT 1");
        $exists = ($rs && mysqli_num_rows($rs) > 0);
    }
    echo json_encode(['ok'=>true,'exists'=>$exists], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ============================================================
   Initials
============================================================ */
$msg = '';
$tab = 1;
$parent_project_id = is_num(post('parent_project_id')) ? (int) post('parent_project_id') : null;
$master_project_id = is_num(post('master_project_id')) ? (int) post('master_project_id') : null;
$client_id = (post('client_id') !== '' && is_num(post('client_id'))) ? (int) post('client_id') : null;
$errormsg = "";

// normalize project_type (do not change user input values overall)
$project_type_input = post('project_type', '');
$project_type = ($project_type_input === 'EMB') ? 'E-MB' : $project_type_input;

// Next Project ID (from EMB projects.id) for first time prefill only
$row = mysqli_fetch_assoc(mysqli_query($db_emb, "SELECT MAX(id) AS last_id FROM projects"));
$nextProjectId = isset($row['last_id']) ? ((int)$row['last_id']) + 1 : 1;

// Ensure new column exists (no impact to erp_code)
ensure_emb_project_id_column();

/* ============================================================
   Handle Submit
============================================================ */
if (isset($_POST['submit'])) {
    // ---- COMMON VALIDATION ----
    $job_code_input = trim((string)post('job_code'));
    if ($job_code_input === '') {
        $errormsg .= '<div class="alert alert-danger">Job Code is required.</div>';
    } else {
        // server-side uniqueness recheck (race-safe)
        $jc = q_emb($job_code_input);
        $chk = mysqli_query($db_emb, "SELECT 1 FROM master_projects WHERE master_job_code = '$jc' LIMIT 1");
        if ($chk && mysqli_num_rows($chk) > 0) {
            $errormsg .= '<div class="alert alert-danger">This Job Code already exists in Master Projects. Please enter a unique one.</div>';
        }
    }

    // other required fields (as requested: everything we store should be present)
    $required_fields = [
        'client_id'              => 'Client Name',
        'project_name'           => 'Project Name',
        'project_type'           => 'Project Type',
        'work_type'              => 'Work Type',
        'project_description'    => 'Project Description',
        'project_requirement'    => 'Project Requirement',
        'project_cost'           => 'Administrative Sanction Cost',
        'budget_head_id'         => 'Budget Head',
        // optional but recommended – uncomment to strictly require:
        // 'government_order'    => 'G.O. Number',
        'government_order_date' => 'G.O. Date',
        'budget_approved_date'  => 'Budget Approved Date',
        'project_estimated_cost'=> 'Financial Sanction Cost',
        'financial_sanction_date'=> 'Financial Sanction Date',
    ];
    foreach ($required_fields as $k=>$label) {
        if (trim((string)post($k)) === '') {
            $errormsg .= '<div class="alert alert-danger">'.htmlspecialchars($label).' is required.</div>';
        }
    }

    if (empty($_POST['edit_sno'])) {
        // -------- CREATE FLOW --------
        if ($errormsg === '') {
            $created_by = isset($_SESSION['usersno']) ? (int) $_SESSION['usersno'] : 0;
            $updated_by = $created_by;

            // Resolve selected project (by sno) Sub use project_name_hindi; keep GET id usage as-is
            $project_name = '';
            $selected_project_sno = null;
            if (isset($_POST['project_name']) && $_POST['project_name'] !== '') {
                $selected_project_sno = (int) $_POST['project_name'];
                $nameQuery = "SELECT project_name_hindi FROM uprnss_project_temp WHERE sno = '" . $selected_project_sno . "' LIMIT 1";
                $res = mysqli_query($db, $nameQuery);
                if ($res && mysqli_num_rows($res) > 0) {
                    $r = mysqli_fetch_assoc($res);
                    $project_name = q_emb($r['project_name_hindi']);
                } else {
                    // fallback to raw (kept same as your original)
                    $project_name = q_emb($_POST['project_name']);
                }
            }

            // Insert Master Project (job code strictly from POST)
            $master_sql = "
                INSERT INTO master_projects (
                    master_project_name, master_job_code, master_project_description,
                    master_sanction_cost, created_by, updated_by, created_at, updated_at
                ) VALUES (
                    '$project_name',
                    '" . q_emb($job_code_input) . "',
                    '" . q_emb(post('project_description')) . "',
                    '" . q_emb(post('project_cost')) . "',
                    '$created_by', '$updated_by', NOW(), NOW()
                )";
            if (!mysqli_query($db_emb, $master_sql)) {
                $msg .= "<p class='alert alert-danger'>Master insert failed: " . htmlspecialchars(mysqli_error($db_emb)) . "</p>";
            } else {
                $master_project_id = mysqli_insert_id($db_emb);

                // Insert Parent Project (link to MASTER via master_project_id)
                $parent_sql = "
                    INSERT INTO parent_projects (
                        project_name, project_description, project_cost,
                        parent_job_code, master_project_id, created_by, updated_by,
                        created_at, updated_at
                    ) VALUES (
                        '$project_name',
                        '" . q_emb(post('project_description')) . "',
                        '" . q_emb(post('project_cost')) . "',
                        '" . q_emb($job_code_input) . "',
                        '$master_project_id',
                        '$created_by', '$updated_by', NOW(), NOW()
                    )";
                if (!mysqli_query($db_emb, $parent_sql)) {
                    $msg .= "<p class='alert alert-danger'>Parent insert failed: " . htmlspecialchars(mysqli_error($db_emb)) . "</p>";
                } else {
                    $parent_project_id = mysqli_insert_id($db_emb);

                    // Insert Project (EMB) — parent_project_id must be from PARENT row
                    echo $project_sql = "
                        INSERT INTO projects (
                            client_id, project_name, parent_project_id, project_cost,
                            project_estimated_cost, agreed_project_cost, project_description,
                            project_requirement, job_code, work_type, project_type,
                            budget_approved_date, financial_sanction_date, government_order_date,
                            budget_head_id, government_order, created_by, updated_by,
                            created_at, updated_at
                        ) VALUES (
                            " . ($client_id !== null ? "'$client_id'" : "NULL") . ",
                            '$project_name',
                            '$parent_project_id',
                            '" . q_emb(post('project_cost')) . "',
                            '" . q_emb(post('project_estimated_cost')) . "',
                            '" . q_emb(post('agreed_project_cost')) . "',
                            '" . q_emb(post('project_description')) . "',
                            '" . q_emb(post('project_requirement')) . "',
                            '" . q_emb($job_code_input) . "',
                            '" . q_emb(post('work_type')) . "',
                            '" . q_emb($project_type) . "',
                            '" . q_emb(post('budget_approved_date')) . "',
                            '" . q_emb(post('financial_sanction_date')) . "',
                            '" . q_emb(post('government_order_date')) . "',
                            '" . q_emb(post('budget_head_id')) . "',
                            '" . q_emb(post('government_order')) . "',
                            '$created_by', '$updated_by', NOW(), NOW()
                        )";
                    mysqli_query($db_emb, $project_sql);

                    if (mysqli_error($db_emb)) {
                        $msg .= '<p class="alert alert-danger">Error: ' . htmlspecialchars(mysqli_error($db_emb)) . '</p>';
                    } else {
                        $inserted_id = mysqli_insert_id($db_emb);

                        // Generate ERP code and update in EMB projects table
                        if (function_exists('generate_erp_code')) {
                            $erp_code = generate_erp_code(post('project_name'), $db);
                        } else {
                            $erp_code = '';
                        }
                        if ($erp_code) {
                            $update_sql = "UPDATE projects SET erp_code = '" . q_emb($erp_code) . "' WHERE id = '$inserted_id'";
                            mysqli_query($db_emb, $update_sql);
                            $msg .= '<p class="alert alert-success">ERP Code Added: <strong>' . htmlspecialchars($erp_code) . '</strong></p>';
                        } else {
                            $msg .= '<p class="alert alert-warning">ERP Code could not be generated.</p>';
                        }

                        // Map Zone + Zonal Units (kept as-is)
                        if (!empty($_POST['zone_id']) && !empty($_POST['zonal_unit_id'])) {
                            $zoneIds = $_POST['zone_id'];
                            $zonalUnitIds = $_POST['zonal_unit_id'];

                            foreach ($zoneIds as $zoneId) {
                                foreach ($zonalUnitIds as $zonalUnitId) {
                                    $insertParentSql = "
                                        INSERT INTO parent_project_zone_units (parent_project_id, zone_master_id, zone_unit_id, created_by, created_at)
                                        VALUES ('" . $parent_project_id . "', '" . q_emb($zoneId) . "', '" . q_emb($zonalUnitId) . "', '" . $created_by . "', NOW())";
                                    if (!mysqli_query($db_emb, $insertParentSql)) {
                                        echo "<p class='alert alert-danger'> Error inserting into parent_project_zone_unit: " . htmlspecialchars(mysqli_error($db_emb)) . "</p>";
                                    }

                                    $insertMasterSql = "
                                        INSERT INTO master_project_zone_units (master_project_id, zone_master_id, zone_unit_id, created_by, created_at)
                                        VALUES ('" . $master_project_id . "', '" . q_emb($zoneId) . "', '" . q_emb($zonalUnitId) . "', '" . $created_by . "', NOW())";
                                    if (!mysqli_query($db_emb, $insertMasterSql)) {
                                        echo "<p class='alert alert-danger'> Error inserting into master_project_zone_unit: " . htmlspecialchars(mysqli_error($db_emb)) . "</p>";
                                    }
                                }
                            }
                        }

                        // Update uprnss_project_temp.emb_project_id with projects.id (do NOT touch erp_code)
                        if ($selected_project_sno !== null) {
                            $up = "UPDATE `uprnss_project_temp` SET `emb_project_id` = '" . $inserted_id . "' WHERE `sno` = '" . (int)$selected_project_sno . "'";
                            mysqli_query($db, $up);
                        }

                        $msg .= '<p class="alert alert-success">Data saved successfully</p>';
                        // echo "<script>
                        // alert('Your project has been added to EMB.');
                        // window.location.href = 'erp_to_emb_project_add.php';
                        // </script>";
                        // exit;
                    }
                }
            }
        }
    } else {
        // -------- EDIT FLOW --------
        if ($errormsg === '') {
            $edit_sno = (int) $_POST['edit_sno'];
            $sql = "
                UPDATE projects SET
                    project_name = '" . q_emb(post('project_name')) . "',
                    client_id = " . ($client_id !== null ? "'$client_id'" : "NULL") . ",
                    parent_project_id = " . ($parent_project_id !== null ? "'$parent_project_id'" : "NULL") . ",
                    project_type = '" . q_emb(post('project_type')) . "',
                    work_type = '" . q_emb(post('work_type')) . "',
                    project_description = '" . q_emb(post('project_description')) . "',
                    project_requirement = '" . q_emb(post('project_requirement')) . "',
                    job_code = '" . q_emb($job_code_input) . "',
                    government_order = '" . q_emb(post('government_order')) . "',
                    government_order_date = '" . q_emb(post('government_order_date')) . "',
                    project_cost = '" . q_emb(post('project_cost')) . "',
                    budget_approved_date = '" . q_emb(post('budget_approved_date')) . "',
                    financial_sanction_date = '" . q_emb(post('financial_sanction_date')) . "',
                    project_estimated_cost = '" . q_emb(post('project_estimated_cost')) . "',
                    budget_head_id = '" . q_emb(post('budget_head_id')) . "',
                    agreed_project_cost = '" . q_emb(post('agreed_project_cost')) . "',
                    document_description = '" . q_emb(post('document_description')) . "',
                    document_type_id = '" . q_emb(post('document_type_id')) . "',
                    created_by = '" . (isset($_SESSION['usersno']) ? (int) $_SESSION['usersno'] : 0) . "',
                    updated_by = '" . (isset($_SESSION['usersno']) ? (int) $_SESSION['usersno'] : 0) . "',
                    updated_at = '" . date("Y-m-d H:i:s") . "'
                WHERE sno = '" . $edit_sno . "'"; // kept your original target column name
            $result = mysqli_query($db_emb, $sql);

            if (!$result) {
                $msg .= '<p class="alert alert-danger">Error: ' . htmlspecialchars(mysqli_error($db_emb)) . '</p>';
            } else {
                $msg .= '<p class="alert alert-success">Data updated successfully</p>';
            }
        }
    }
}

/* ============================================================
   Prefills: Edit & GET id — keep behavior
============================================================ */
if (isset($_GET['edit_sno'])) {
    $es = (int) $_GET['edit_sno'];
    $sql = "SELECT * FROM projects WHERE sno = '" . $es . "'"; // kept as-is per your code
    $result = mysqli_query($db_emb, $sql);
    if ($result && $data = mysqli_fetch_assoc($result)) {
        $_POST['division_id'] = $data['division_id'] ?? '';
        $_POST['district'] = $data['district'] ?? '';
        $_POST['project_name'] = $data['project_name'];
        $_POST['client_id'] = $data['client_id'];
        $_POST['parent_project_id'] = $data['parent_project_id'];
        $_POST['project_type'] = $data['project_type'];
        $_POST['work_type'] = $data['work_type'];
        $_POST['project_description'] = $data['project_description'];
        $_POST['project_requirement'] = $data['project_requirement'];
        $_POST['job_code'] = $data['job_code'];
        $_POST['government_order'] = $data['government_order'];
        $_POST['government_order_date'] = $data['government_order_date'];
        $_POST['project_cost'] = $data['project_cost'];
        $_POST['budget_approved_date'] = $data['budget_approved_date'];
        $_POST['financial_sanction_date'] = $data['financial_sanction_date'];
        $_POST['project_estimated_cost'] = $data['project_estimated_cost'];
        $_POST['budget_head_id'] = $data['budget_head_id'];
        $_POST['agreed_project_cost'] = $data['agreed_project_cost'];
        $_POST['document_type_id'] = $data['document_type_id'] ?? '';
        $_POST['document_description'] = $data['document_description'] ?? '';
        $_POST['edit_sno'] = $data['sno'];
    }
}

if (isset($_GET['id'])) {
    $gid = (int) $_GET['id'];
    $sql = "SELECT * FROM uprnss_project_temp WHERE sno = '" . $gid . "'";
    $result = mysqli_query($db, $sql);
    if ($result && $data = mysqli_fetch_assoc($result)) {
        $_POST['division_id'] = $data['division_id'];
        $_POST['district'] = $data['district_id'];
        $_POST['project_name'] = $data['sno'];  // keep dropdown selection by GET id
        $_POST['client_id'] = $data['client_id']; 
    }
}

/* ============================================================
   Layout
============================================================ */
page_header_start();
page_header_end();
page_sidebar();
?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
    action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

    <div class="card">
        <?php if (isset($_POST['submit'])) {
            echo '<div class="card-header ml-auto no-print">
				<a href="emb_report.php" class="text-right" target=""><u><i class="icon fas fa-file-alt" aria-hidden="true"></i> Alloted Report</u></a></div>';
        } ?>
        <div class="card-body">
            <?php echo $errormsg ?: ''; ?>
            <div class="row">
                <div class="col-md-4 ">
                    <div class="form-group">
                        <label>प्रखण्ड का नाम</label><br>
                        <select class="form-control" name="division_id" id="division_id"
                            tabindex="<?php echo $tab++; ?>" readonly>
                            <option value="">--- Select ---</option>
                            <?php
                            $query = '(SELECT * from uprnss_division order by division_name ASC ) ';
                            $run = mysqli_query($db, $query);
                            while ($data = mysqli_fetch_array($run)) {
                                echo '<option value="' . htmlspecialchars($data['s_no']) . '"';
                                if (isset($_POST['division_id']) && $_POST['division_id'] == $data['s_no'])
                                    echo ' selected';
                                echo '>' . htmlspecialchars(trim($data['division_name'])) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>जनपद का नाम</label>
                        <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>"
                            readonly>
                            <option value="">--- Select ---</option>
                            <?php
                            $query = '(SELECT * from uprnss_district ) ';
                            $run = mysqli_query($db, $query);
                            while ($data = mysqli_fetch_array($run)) {
                                echo '<option value="' . htmlspecialchars($data['sno']) . '"';
                                if (isset($_POST['district']) && $_POST['district'] == $data['sno'])
                                    echo ' selected';
                                echo '>' . htmlspecialchars(trim($data['district_name_hindi'])) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label>परियोजना का नाम</label><br>
                        <select class="form-control" name="project_name" id="project_name_readonly"
                            tabindex="<?php echo $tab++; ?>" readonly>
                            <option value="">--- Select ---</option>
                            <?php
                            $query = '(SELECT * FROM `uprnss_project_temp` WHERE status!="5") ';
                            $run = mysqli_query($db, $query);
                            while ($data = mysqli_fetch_array($run)) {
                                echo '<option value="' . htmlspecialchars($data['sno']) . '"';
                                if (isset($_POST['project_name']) && $_POST['project_name'] == $data['sno'])
                                    echo ' selected';
                                echo '>' . htmlspecialchars(trim($data['project_name_hindi'])) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                    <?php echo $msg; ?>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>projects Job Code / Work Order No. / TS No.</label>
                                <input type="text" name="job_code" id="job_code" class="form-control"
                                    placeholder="Sub projects Job Code / Work Order No. / TS No."
                                    value="<?php
                                        $job_prefill = isset($_POST['job_code']) ? trim((string)$_POST['job_code']) : '';
                                        echo htmlspecialchars($job_prefill !== '' ? $job_prefill : (string)$nextProjectId);
                                    ?>"
                                    required>
                                <small id="job_code_help" class="form-text"></small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Client Name</label>
                                <select class="form-control" name="client_id" id="client_id"
                                    tabindex="<?php echo $tab++; ?>" required>
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $run = mysqli_query($db_emb, "SELECT * FROM clients");
                                    while ($data = mysqli_fetch_array($run)) {
                                        echo '<option value="' . htmlspecialchars($data['id']) . '"';
                                        if (isset($_POST['client_id']) && $_POST['client_id'] == $data['id'])
                                            echo ' selected';
                                        echo '>' . htmlspecialchars(trim($data['name'])) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>projects Name</label>
                                <select class="form-control" name="project_name" id="project_name"
                                    tabindex="<?php echo $tab++; ?>" required>
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $run = mysqli_query($db, '(SELECT * FROM `uprnss_project_temp` WHERE status!="5") ');
                                    while ($data = mysqli_fetch_array($run)) {
                                        echo '<option value="' . htmlspecialchars($data['sno']) . '"';
                                        if (isset($_POST['project_name']) && $_POST['project_name'] == $data['sno'])
                                            echo ' selected';
                                        echo '>' . htmlspecialchars(trim($data['project_name_hindi'])) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Project Type</label>
                                <select name="project_type" id="project_type" class="form-control" required>
                                    <option value="">--- Select ---</option>
                                    <option value="E-MB" <?php if (isset($_POST['project_type']) && $_POST['project_type'] == 'E-MB') echo 'selected'; ?>>Tender(e-MB)</option>
                                    <option value="EPC"  <?php if (isset($_POST['project_type']) && $_POST['project_type'] == 'EPC')  echo 'selected'; ?>>EPC</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Work Type</label>
                                <select class="form-control" name="work_type" id="work_type"
                                    tabindex="<?php echo $tab++; ?>" required>
                                    <option value="">--- Select ---</option>
                                    <option value="Standard"     <?php if (isset($_POST['work_type']) && $_POST['work_type'] == 'Standard')     echo 'selected'; ?>>Standard</option>
                                    <option value="Non_Standard" <?php if (isset($_POST['work_type']) && $_POST['work_type'] == 'Non_Standard') echo 'selected'; ?>>Non-Standard</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>projects Description</label>
                                <input type="text" name="project_description" id="project_description"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['project_description']) ? htmlspecialchars($_POST['project_description']) : ''; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>projects Requirement</label>
                                <input type="text" name="project_requirement" id="project_requirement"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['project_requirement']) ? htmlspecialchars($_POST['project_requirement']) : ''; ?>"
                                    required>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mt-2">Add Budget Sanction Details</h5>
                            <div class="mt-2 mb-2">
                                <div class="dashbed-border-bottom"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>G. O. Number</label>
                                <input type="text" name="government_order" id="government_order" class="form-control"
                                    placeholder=""
                                    value="<?php echo isset($_POST['government_order']) ? htmlspecialchars($_POST['government_order']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>G. O. Date</label>
                                <input type="date" name="government_order_date" id="government_order_date"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['government_order_date']) ? htmlspecialchars($_POST['government_order_date']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Administrative Sanction Cost</label>
                                <input type="number" name="project_cost" required id="project_cost" class="form-control"
                                    placeholder=""
                                    value="<?php echo isset($_POST['project_cost']) ? htmlspecialchars($_POST['project_cost']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Budget Approved Date</label>
                                <input type="date" name="budget_approved_date" id="budget_approved_date"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['budget_approved_date']) ? htmlspecialchars($_POST['budget_approved_date']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Financial Sanction Cost(Fund Released)</label>
                                <input type="text" name="project_estimated_cost" id="project_estimated_cost"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['project_estimated_cost']) ? htmlspecialchars($_POST['project_estimated_cost']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Financial Sanction Cost(Fund Released) Date</label>
                                <input type="date" name="financial_sanction_date" id="financial_sanction_date"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['financial_sanction_date']) ? htmlspecialchars($_POST['financial_sanction_date']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Select Budget Head</label>
                                <select class="form-control" name="budget_head_id" id="budget_head_id"
                                    tabindex="<?php echo $tab++; ?>" required>
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $run = mysqli_query($db_emb, 'SELECT * FROM budget_heads');
                                    while ($data = mysqli_fetch_array($run)) {
                                        echo '<option value="' . htmlspecialchars($data['id']) . '"';
                                        if (isset($_POST['budget_head_id']) && $_POST['budget_head_id'] == $data['id'])
                                            echo ' selected';
                                        echo '>' . htmlspecialchars(trim($data['title'])) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group" id="agreed_project_cost_sec">
                                <label>Agreed Poject Cost</label>
                                <input type="number" name="agreed_project_cost" id="agreed_project_cost"
                                    class="form-control" placeholder=""
                                    value="<?php echo isset($_POST['agreed_project_cost']) ? htmlspecialchars($_POST['agreed_project_cost']) : ''; ?>">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mt-2">Add Zone & Zonal Unit</h5>
                            <div class="mt-2 mb-2">
                                <div class="dashbed-border-bottom"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group"><label for="zone_list">
                                    <input type="checkbox" id="zone_list" name="selected_all_zone" value="1"> Select All
                                    Zones
                                </label></div>
                        </div>

                        <div class="col-md-6 zonal-id">
                            <div class="form-group">
                                <label>Zone <span class="text-danger">*</span></label>
                                <select name="zone_id[]" class="form-control" id="select2Zone" multiple>
                                    <?php
                                    $zoneResult = mysqli_query($db_emb, "SELECT * FROM zone_masters ORDER BY zone_code ASC");
                                    while ($zone = mysqli_fetch_assoc($zoneResult)) {
                                        echo '<option value="' . htmlspecialchars($zone['id']) . '">' . htmlspecialchars($zone['zone_code']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 zonal-unit">
                            <div class="form-group">
                                <label>Zonal Unit <span class="text-danger">*</span></label>
                                <select class="form-control" name="zonal_unit_id[]" id="select2ZonalUnits" multiple>
                                    <option>Select Zonal Unit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments block kept commented as in your original -->
                </div>
                <br><br><br>
                <div class="col-md-11 pr-1" align="center">
                    <div class="form-group">
                        <button type="submit" name="submit" id="submitBtn" class="btn btn-info btn-fill pull-right" disabled>Submit</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

<?php page_footer_start(); ?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script src="js/chartist.min.js"></script>
<script>
    $('select[multiple]').multiselect && $('select[multiple]').multiselect();
</script>
<script>
    $(document).ready(function () {
        $('#select2Zone').select2({ placeholder: "Select Zone", allowClear: true, width: '100%' });
        $('#select2ZonalUnits').select2({ placeholder: "Select Zonal Unit", allowClear: true, width: '100%' });

        // On zone change, fetch zonal units
        $('#select2Zone').on('change', getZonalUnit);

        // If 'Select All Zones' is checked
        $('#zone_list').on('change', function () {
            let allOptions = $('#select2Zone option');
            if (this.checked) { allOptions.prop('selected', true); }
            else { allOptions.prop('selected', false); }
            $('#select2Zone').trigger('change'); // refresh + load units
        });
    });

    function getZonalUnit() {
        let selectedZones = $('#select2Zone').val();
        if (!selectedZones || selectedZones.length === 0) {
            $('#select2ZonalUnits').html('<option value="">Select Zone first</option>').trigger('change');
            return;
        }
        $.ajax({
            url: 'get_zonal_units.php',
            type: 'POST',
            data: { zone_ids: selectedZones },
            success: function (response) {
                $('#select2ZonalUnits').html(response).trigger('change');
            },
            error: function () {
                $('#select2ZonalUnits').html('<option value="">Error loading units</option>').trigger('change');
            }
        });
    }
</script>

<script>
    // Real-time required validation + job code uniqueness
    const $form = $('#sale_form');
    const $submit = $('#submitBtn');
    const $job = $('#job_code');
    const $jobHelp = $('#job_code_help');

    const requiredSelectors = [
        '#job_code',
        '#client_id',
        '#project_name',
        '#project_type',
        '#work_type',
        '#project_description',
        '#project_requirement',
        '#project_cost',
        '#budget_head_id'
    ];

    let jobOk = false;
    let fieldsOk = false;

    function checkRequiredFields(){
        let ok = true;
        requiredSelectors.forEach(sel => {
            const $el = $(sel);
            const val = ($el.val() || '').toString().trim();
            if (!val) {
                ok = false;
                $el.addClass('is-invalid');
            } else {
                $el.removeClass('is-invalid');
            }
        });
        fieldsOk = ok;
        toggleSubmit();
    }

    function checkJobCode(){
        const v = $job.val().trim();
        if (!v) {
            $jobHelp.text('Job Code is required.').removeClass('text-success').addClass('text-danger');
            $job.addClass('is-invalid');
            jobOk = false; toggleSubmit(); return;
        }
        $.getJSON('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', { ajax: 'check_job_code', job_code: v })
            .done(function(resp){
                if (resp && resp.ok) {
                    if (resp.exists) {
                        $jobHelp.text('This Job Code already exists. Please enter a unique value.')
                                .removeClass('text-success').addClass('text-danger');
                        $job.addClass('is-invalid');
                        jobOk = false;
                    } else {
                        $jobHelp.text('Job Code is available.').removeClass('text-danger').addClass('text-success');
                        $job.removeClass('is-invalid').addClass('is-valid');
                        jobOk = true;
                    }
                } else {
                    $jobHelp.text('Could not validate Job Code right now.').removeClass('text-success').addClass('text-danger');
                    jobOk = false;
                }
                toggleSubmit();
            })
            .fail(function(){
                $jobHelp.text('Validation error.').removeClass('text-success').addClass('text-danger');
                jobOk = false; toggleSubmit();
            });
    }

    function toggleSubmit(){
        $submit.prop('disabled', !(jobOk && fieldsOk));
    }

    // bind events
    requiredSelectors.forEach(sel => {
        $(document).on('input change', sel, checkRequiredFields);
    });
    $job.on('input blur', function(){
        checkRequiredFields();
        checkJobCode();
    });

    // initial run
    $(function(){
        checkRequiredFields();
        if ($job.val().trim() !== '') { checkJobCode(); }
    });
</script>

<script>
    // Optional: clone/remove attachment groups — kept same as original (commented block)
    let attachmentIndex = 1;

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-more')) {
            e.preventDefault();

            const wrapper = document.getElementById('document-attachments');
            const base = wrapper.querySelector('.attachment-group');
            if(!base){ return; }
            const newGroup = base.cloneNode(true);

            // Update names with new index
            newGroup.querySelectorAll('input, select').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, `[${attachmentIndex}]`);
                if (input.type !== 'file') input.value = '';
                if (input.tagName === 'SELECT') { for (const opt of input.options) opt.selected = false; }
            });

            // Change + button to - for removal
            const btn = newGroup.querySelector('.add-more');
            btn.textContent = '-';
            btn.classList.remove('add-more', 'btn-primary');
            btn.classList.add('remove-row', 'btn-danger');

            wrapper.appendChild(newGroup);
            attachmentIndex++;
        }

        if (e.target.classList.contains('remove-row')) {
            e.preventDefault();
            e.target.closest('.attachment-group').remove();
        }
    });
</script>

<script>
    $(document).ready(function () {
        if ($.fn.DataTable) {
            $('#general_stat_table').DataTable({
                fixedHeader: true,
                colReorder: true,
                scrollX: true
            });
        }

        var actionUrl = 'scripts/ajax.php';

        function fill_sub_department(val, selected) {
            var data = { "term": "b", "id": "sub_dep", "val": val };
            $.post(actionUrl, data, function (ajaxdata) {
                var txt = '<option value="">--Select--</option>';
                ajaxdata = JSON.parse(ajaxdata);
                $.each(ajaxdata, function (key, value) {
                    txt += '<option value="' + value.id + '"' + (selected == value.id ? ' selected' : '') + '>' + value.sub_department_hindi + '</option>';
                });
                $("#sub_department_id").html(txt);
            });
        }

        function fill_district(val, selected) {
            var data = { "term": "b", "id": "dist", "val": val };
            $.post(actionUrl, data, function (data) {
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function (key, value) {
                    txt += '<option value="' + value.id + '"' + (selected == value.id ? ' selected' : '') + '>' + value.district_name + '</option>';
                });
                $("#district").html(txt);
            });
        }

        function fill_project(val, selected) {
            var data = { "term": "b", "id": "proj", "val": val, "dept": $("#department").val() };
            $.post(actionUrl, data, function (data) {
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function (key, value) {
                    txt += '<option value="' + value.id + '"' + (selected == value.id ? ' selected' : '') + '>' + value.project_name_hindi + '</option>';
                });
                $("#project_name").html(txt);
            });
        }

        <?php if (isset($_GET['edit_sno'])) { ?>
            fill_sub_department(<?php echo json_encode(post('department')); ?>, <?php echo json_encode(post('sub_department_id')); ?>);
            fill_district(<?php echo json_encode(post('department')); ?>, <?php echo json_encode(post('district')); ?>);
            fill_project(<?php echo json_encode(post('district')); ?>, <?php echo json_encode(post('project_name')); ?>);
        <?php } ?>
    });
</script>

<script>
    if (window.history && window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
<?php page_footer_end(); ?>
