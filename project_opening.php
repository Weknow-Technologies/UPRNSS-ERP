<?php
include("scripts/settings.php");
// error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function lookup($db, $table, $id, $field) {
    $id = intval($id);
    if ($id <= 0) return '';

    $table_esc = mysqli_real_escape_string($db, $table);
    $field_esc = mysqli_real_escape_string($db, $field);
    $id_fields = ['sno', 's_no', 'id'];

    $columns = [];
    $res = mysqli_query($db, "SHOW COLUMNS FROM `{$table_esc}`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $columns[] = $row['Field'];
        }
    }
    foreach ($id_fields as $id_field) {
        if (in_array($id_field, $columns)) {
            $sql = "SELECT `{$field_esc}` AS v FROM `{$table_esc}` WHERE `{$id_field}` = {$id} LIMIT 1";
            $q = mysqli_query($db, $sql);
            if ($q && $r = mysqli_fetch_assoc($q)) {
                return $r['v'];
            }
        }
    }
    return '';
}

$msg = '';

// Actions: del, view, edit load
if (isset($_GET['del']) && intval($_GET['del']) > 0) {
    $del_id = intval($_GET['del']);
    if (!$del_id) {
        $msg = '<div class="alert alert-danger">Not allowed to delete this batch.</div>';
    } else {
        mysqli_query($db, "DELETE FROM `invoice_project_opening_items` WHERE opening_id = {$del_id}");
        mysqli_query($db, "DELETE FROM `invoice_project_opening_summary` WHERE id = {$del_id} LIMIT 1");
        $msg = '<div class="alert alert-success">Batch and related items deleted.</div>';
    }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// view
$view_mode = false;
$view_data = null;
if (isset($_GET['view']) && intval($_GET['view']) > 0) {
    $vid = intval($_GET['view']);
    $res = mysqli_query($db, "SELECT * FROM `invoice_project_opening_summary` WHERE id = {$vid} LIMIT 1");
    if ($res && $summary = mysqli_fetch_assoc($res)) {
        $items = [];
        $iq = mysqli_query($db, "SELECT * FROM `invoice_project_opening_items` WHERE opening_id = {$vid} ORDER BY id");
        if ($iq) while ($ir = mysqli_fetch_assoc($iq)) $items[] = $ir;
        $view_mode = true;
        $view_data = ['summary' => $summary, 'items' => $items];
    } else {
        $msg = '<div class="alert alert-danger">Batch not found.</div>';
    }
}

// edit load
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $eid = intval($_GET['edit']);
    $res = mysqli_query($db, "SELECT * FROM `invoice_project_opening_summary` WHERE id = {$eid} LIMIT 1");
    if ($res && $summary = mysqli_fetch_assoc($res)) {
        $items = [];
        $iq = mysqli_query($db, "SELECT * FROM `invoice_project_opening_items` WHERE opening_id = {$eid} ORDER BY id");
        if ($iq) while ($ir = mysqli_fetch_assoc($iq)) $items[] = $ir;
        $edit_mode = true;
        $edit_data = ['summary' => $summary, 'items' => $items];
    } else {
        $msg = '<div class="alert alert-danger">Batch not found.</div>';
    }
}

// POST handler
if (isset($_POST['submit'])) {
    $entry_type = (isset($_POST['entry_type']) && $_POST['entry_type'] === 'other') ? 'other' : 'project';
    $department = intval($_POST['department'] ?? 0);
    $sub_department_id = intval($_POST['sub_department_id'] ?? 0);
    $district = intval($_POST['district'] ?? 0);
    $division_id = intval($_POST['division_id'] ?? 0);
    $project_id = intval($_POST['project_name'] ?? 0);
    $other_ledger_sno = intval($_POST['account_header_sno'] ?? 0);
    $user_id = intval($_SESSION['user_id'] ?? 0);

    // Validation
    // Division now REQUIRED for both types
    if ($division_id <= 0) {
        $msg = '<div class="alert alert-danger">Please select Division (Division अनिवार्य है)।</div>';
    } elseif ($entry_type === 'project') {
        if ($department <= 0 || $district <= 0 || $project_id <= 0) {
            $msg = '<div class="alert alert-danger">Please select विभाग, आच्छादित जनपद और परियोजना का नाम (Department, District and Project).</div>';
        }
    } else {
        if ($other_ledger_sno <= 0) {
            $msg = '<div class="alert alert-danger">Please select Ledger for Other type.</div>';
        }
    }

    if ($msg === '') {
        $max_rows = intval($_POST['max_rows'] ?? 1);
        if ($max_rows < 1) $max_rows = 1;

        // collect items
        $items = [];
        $errors = [];
        for ($i = 1; $i <= $max_rows; $i++) {
            $ledger_sno = trim($_POST["account_{$i}_sno"] ?? '');
            $opening_date_raw = trim($_POST["opening_date_{$i}"] ?? $_POST['opening_date'] ?? '');
            $opening_amount_raw = trim($_POST["opening_amount_{$i}"] ?? '');
            $opening_remark = trim($_POST["opening_remark_{$i}"] ?? '');

            if ($ledger_sno === '' && $opening_amount_raw === '') continue;
            if ($ledger_sno === '' || $opening_amount_raw === '') {
                $errors[] = "Row {$i}: Ledger और Opening Amount आवश्यक हैं।";
                continue;
            }

            $ledger_head_id = intval($ledger_sno);
            $ts = strtotime($opening_date_raw);
            $opening_date = $ts ? date('Y-m-d', $ts) : date('Y').'-04-01';
            $opening_amount = floatval(str_replace(',', '', $opening_amount_raw));

            $items[] = [
                'ledger_head_id' => $ledger_head_id,
                'opening_date' => $opening_date,
                'opening_amount' => $opening_amount,
                'remark' => $opening_remark
            ];
        }

        if (empty($items)) $errors[] = 'No valid item rows provided.';

        if (!empty($errors)) {
            $msg = '<div class="alert alert-warning">' . implode('<br>', $errors) . '</div>';
        } else {
            // Update
            if (!empty($_POST['edit_id'])) {
                $edit_id = intval($_POST['edit_id']);
                if (!$edit_id) {
                    $msg = '<div class="alert alert-danger">Not allowed to update this batch.</div>';
                } else {
                    mysqli_begin_transaction($db);
                    $ok = true;

                    $dpt = ($entry_type === 'project') ? intval($department) : 'NULL';
                    $subd = ($entry_type === 'project') ? intval($sub_department_id) : 'NULL';
                    $dist = ($entry_type === 'project') ? intval($district) : 'NULL';
                    $div = ($division_id > 0) ? intval($division_id) : 'NULL';
                    $proj = ($entry_type === 'project') ? intval($project_id) : 'NULL';
                    $otherLedgerVal = ($entry_type === 'other') ? intval($other_ledger_sno) : 'NULL';
                    $creator = intval($user_id);

                    $sql_up = "UPDATE `invoice_project_opening_summary` SET entry_type = '". mysqli_real_escape_string($db, $entry_type) ."', department = " . ($dpt === 'NULL' ? 'NULL' : $dpt) . ", sub_department_id = " . ($subd === 'NULL' ? 'NULL' : $subd) . ", district = " . ($dist === 'NULL' ? 'NULL' : $dist) . ", division_id = " . ($div === 'NULL' ? 'NULL' : $div) . ", project_id = " . ($proj === 'NULL' ? 'NULL' : $proj) . ", other_ledger_sno = " . ($otherLedgerVal === 'NULL' ? 'NULL' : $otherLedgerVal) . ", created_by = {$creator} WHERE id = {$edit_id} LIMIT 1";

                    if (!mysqli_query($db, $sql_up)) { $ok = false; $msg = '<div class="alert alert-danger">Update summary failed: ' . h(mysqli_error($db)) . '</div>'; }

                    if ($ok) {
                        if (!mysqli_query($db, "DELETE FROM `invoice_project_opening_items` WHERE opening_id = {$edit_id}")) {
                            $ok = false; $msg = '<div class="alert alert-danger">Delete old items failed: ' . h(mysqli_error($db)) . '</div>';
                        }
                    }
                    if ($ok) {
                        foreach ($items as $it) {
                            $ldb = intval($it['ledger_head_id']);
                            $op_date = mysqli_real_escape_string($db, $it['opening_date']);
                            $op_amt = number_format(floatval($it['opening_amount']), 2, '.', '');
                            $rmk = mysqli_real_escape_string($db, $it['remark']);
                            $sql_i = "INSERT INTO `invoice_project_opening_items` (opening_id, ledger_head_id, opening_date, opening_amount, remark) VALUES ({$edit_id}, {$ldb}, '{$op_date}', {$op_amt}, '{$rmk}')";
                            if (!mysqli_query($db, $sql_i)) { $ok = false; $msg = '<div class="alert alert-danger">Insert item failed: ' . h(mysqli_error($db)) . '</div>'; break; }
                        }
                    }
                    if ($ok) {
                        mysqli_commit($db);
                        $msg = '<div class="alert alert-success">Batch updated successfully.</div>';
                        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                        exit;
                    } else {
                        mysqli_rollback($db);
                    }
                }
            } else {
                // Insert
                mysqli_begin_transaction($db);
                $dpt = ($entry_type === 'project') ? intval($department) : 'NULL';
                $subd = ($entry_type === 'project') ? intval($sub_department_id) : 'NULL';
                $dist = ($entry_type === 'project') ? intval($district) : 'NULL';
                $div = ($division_id > 0) ? intval($division_id) : 'NULL';
                $proj = ($entry_type === 'project') ? intval($project_id) : 'NULL';
                $otherLedgerVal = ($entry_type === 'other') ? intval($other_ledger_sno) : 'NULL';
                $creator = intval($user_id);

                $sql_sum = "INSERT INTO `invoice_project_opening_summary` (entry_type, department, sub_department_id, district, division_id, project_id, other_ledger_sno, created_by) VALUES ('". mysqli_real_escape_string($db, $entry_type) ."', " . ($dpt === 'NULL' ? 'NULL' : $dpt) . ", " . ($subd === 'NULL' ? 'NULL' : $subd) . ", " . ($dist === 'NULL' ? 'NULL' : $dist) . ", " . ($div === 'NULL' ? 'NULL' : $div) . ", " . ($proj === 'NULL' ? 'NULL' : $proj) . ", " . ($otherLedgerVal === 'NULL' ? 'NULL' : $otherLedgerVal) . ", {$creator})";
                if (!mysqli_query($db, $sql_sum)) {
                    mysqli_rollback($db);
                    $msg = '<div class="alert alert-danger">Insert summary failed: ' . h(mysqli_error($db)) . '</div>';
                } else {
                    $opening_id = intval(mysqli_insert_id($db));
                    $ok = true;
                    foreach ($items as $it) {
                        $ldb = intval($it['ledger_head_id']);
                        $op_date = mysqli_real_escape_string($db, $it['opening_date']);
                        $op_amt = number_format(floatval($it['opening_amount']), 2, '.', '');
                        $rmk = mysqli_real_escape_string($db, $it['remark']);
                        $sql_i = "INSERT INTO `invoice_project_opening_items` (opening_id, ledger_head_id, opening_date, opening_amount, remark) VALUES ({$opening_id}, {$ldb}, '{$op_date}', {$op_amt}, '{$rmk}')";
                        if (!mysqli_query($db, $sql_i)) { $ok = false; $msg = '<div class="alert alert-danger">Insert item failed: ' . h(mysqli_error($db)) . '</div>'; break; }
                    }
                    if ($ok) {
                        mysqli_commit($db);
                        $msg = '<div class="alert alert-success">Saved ' . count($items) . ' item(s) under Opening #' . intval($opening_id) . '.</div>';
                        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                        exit;
                    } else {
                        mysqli_rollback($db);
                    }
                }
            }
        }
    }
}

page_header_start();
?>

<style>
.card-like{background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:15px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:15px;}
.bill-inner{border:1px dashed #ddd; padding:10px; margin-bottom:8px; border-radius:6px;}
.form-row {margin-bottom:8px;}
.table-actions { text-align:center; }
.small-muted{font-size:0.9em;color:#666}
.actions-dropdown .btn { padding: 3px 8px; line-height:1; }
.view-card .card-body { padding: 12px; }

</style>
<?php
page_header_end();
page_sidebar();
?>

<div class="container-fluid my-3">

  <!-- FORM CARD -->
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="mb-3">Project-wise / Other Opening Entries</h5>
      <?php echo $msg; ?>

 <form method="post" enctype="multipart/form-data" id="openingForm" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
    <div class="container-fluid">

        <!-- Entry Type -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Type</label>
                <select class="form-control" name="entry_type" id="entry_type" required>
                    <option value="project" <?php if (($edit_mode && $edit_data['summary']['entry_type'] === 'project') || (!empty($_POST['entry_type']) && $_POST['entry_type'] === 'project')) echo 'selected'; ?>>Project</option>
                    <option value="other" <?php if (($edit_mode && $edit_data['summary']['entry_type'] === 'other') || (!empty($_POST['entry_type']) && $_POST['entry_type'] === 'other')) echo 'selected'; ?>>Other (Ledger)</option>
                </select>
            </div>
        </div>

        <!-- Project Related Section -->
        <div class="row mb-3" id="location_and_project_row">
            <div class="col-md-3">
                <label>विभाग</label>
                <select class="form-control" name="department" id="department">
                    <option value="">--Select--</option>
                    <?php
                    $q = mysqli_query($db, "SELECT sno, department_name_hindi FROM uprnss_department_name ORDER BY department_name_hindi");
                    while ($r = mysqli_fetch_assoc($q)) {
                        $sel = '';
                        if ($edit_mode) $sel = (intval($edit_data['summary']['department']) === intval($r['sno'])) ? 'selected' : '';
                        else $sel = (isset($_POST['department']) && $_POST['department'] == $r['sno']) ? 'selected' : '';
                        echo '<option value="'.h($r['sno']).'" '.$sel.'>'.h($r['department_name_hindi']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>उप विभाग</label>
                <select class="form-control" name="sub_department_id" id="sub_department_id">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>आच्छादित जनपद</label>
                <select class="form-control" name="district" id="district">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>परियोजना का नाम</label>
                <select class="form-control" name="project_name" id="project_name">
                    <option value="">--Select--</option>
                </select>
            </div>
        </div>

        <!-- Division -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Division</label>
                <?php
                $sessDiv = $_SESSION['divisions'] ?? '';
                $userType = intval($_SESSION['usertype'] ?? 0);
                if (!empty($sessDiv) && $userType == 9) {
                    if (is_array($_SESSION['divisions'])) {
                        $ids = implode(',', array_map('intval', $_SESSION['divisions']));
                        $dq = mysqli_query($db, "SELECT division_name FROM uprnss_division WHERE s_no IN ({$ids}) LIMIT 1");
                    } else {
                        $dq = mysqli_query($db, "SELECT division_name FROM uprnss_division WHERE s_no = " . intval($sessDiv) . " LIMIT 1");
                    }
                    $drow = mysqli_fetch_assoc($dq);
                    echo '<input type="hidden" name="division_id" value="'.intval($sessDiv).'">';
                    echo '<input type="text" class="form-control" value="'.h($drow['division_name']).'" readonly>';
                } else {
                    echo '<select name="division_id" id="division_id" class="form-control">';
                    echo '<option value="">--Select Division--</option>';
                    $dq = mysqli_query($db, "SELECT s_no, division_name FROM uprnss_division ORDER BY division_name");
                    while($d = mysqli_fetch_assoc($dq)) {
                        $sel = '';
                        if ($edit_mode) $sel = (intval($edit_data['summary']['division_id']) === intval($d['s_no'])) ? 'selected' : '';
                        else $sel = (isset($_POST['division_id']) && $_POST['division_id'] == $d['s_no']) ? 'selected' : '';
                        echo '<option value="'.h($d['s_no']).'" '.$sel.'>'.h($d['division_name']).'</option>';
                    }
                    echo '</select>';
                }
                ?>
            </div>
        </div>
        </div>
          

          <div class="col-4" id="other_ledger_block" style="display:none; margin-top:10px;">
            <label>Ledger for Other</label>
            <input type="text" name="account_header" id="account_header" class="form-control account-input-header" placeholder="Start typing ledger..." value="<?php
                if ($edit_mode && $edit_data['summary']['entry_type'] === 'other') echo h(lookup($db,'billit_customer',$edit_data['summary']['other_ledger_sno'] ?? 0,'cus_name'));
                else echo h($_POST['account_header'] ?? '');
            ?>">
            <input type="hidden" name="account_header_sno" id="account_header_sno" value="<?php
                if ($edit_mode && $edit_data['summary']['entry_type'] === 'other') echo h(intval($edit_data['summary']['other_ledger_sno'] ?? 0));
                else echo h($_POST['account_header_sno'] ?? '');
            ?>">
          </div>

        <div style="margin-top:12px;">
            <div class="card-like">
                <button type="button" id="addOpeningRow" class="btn btn-primary" style="float:right;">
                    <i class="fa fa-plus"></i> Add Item Row
                </button>
                <div style="clear:both;"></div>
                <input type="hidden" id="current" value="1">

                <!-- Base row index = 1 -->
                <div class="bill-inner" data-row="1" id="opening_row_1">
                    <div class="row">
                        <div class="col-md-3 form-row">
                            <label>Ledger (Head name)</label>
                            <input type="text" name="account_1" id="account_1" class="form-control account-input" placeholder="Start typing ledger..." onFocus="set_current(1)"
                               value="<?php
                                  if ($edit_mode) echo h(lookup($db,'billit_customer',$edit_data['items'][0]['ledger_head_id'] ?? 0,'cus_name'));
                                  else echo h($_POST['account_1'] ?? '');
                               ?>">
                            <input type="hidden" name="account_1_sno" id="account_1_sno" value="<?php
                                  if ($edit_mode) echo h(intval($edit_data['items'][0]['ledger_head_id'] ?? 0));
                                  else echo h($_POST['account_1_sno'] ?? '');
                               ?>">
                            <input type="hidden" name="ledger_type_1" id="ledger_type_1" value="<?php echo h($_POST['ledger_type_1'] ?? ''); ?>">
                        </div>

                        <div class="col-md-3 form-row">
                            <label>Opening Date</label>
                            <input type="date" name="opening_date_1" id="opening_date_1" class="form-control" value="<?php
                                if ($edit_mode) echo h($edit_data['items'][0]['opening_date'] ?? ($_POST['opening_date'] ?? date('Y').'-04-01'));
                                else echo h($_POST['opening_date'] ?? date('Y').'-04-01');
                            ?>">
                        </div>

                        <div class="col-md-3 form-row">
                            <label>Opening Amount</label>
                            <input type="number" step="0.01" name="opening_amount_1" id="opening_amount_1" class="form-control" value="<?php
                                if ($edit_mode) echo h($edit_data['items'][0]['opening_amount'] ?? ($_POST['opening_amount_1'] ?? ''));
                                else echo h($_POST['opening_amount_1'] ?? '');
                            ?>">
                        </div>

                        <div class="col-md-3 form-row">
                            <label>Remark</label>
                            <input type="text" name="opening_remark_1" id="opening_remark_1" class="form-control" value="<?php
                                if ($edit_mode) echo h($edit_data['items'][0]['remark'] ?? ($_POST['opening_remark_1'] ?? ''));
                                else echo h($_POST['opening_remark_1'] ?? '');
                            ?>">
                        </div>
                    </div>
                </div>

                <div id="additional_opening_blocks">
                    <?php
                    // Render additional edit rows (if editing)
                    if ($edit_mode && !empty($edit_data['items'])) {
                        $start = 1;
                        $count = count($edit_data['items']);
                        for ($i = $start; $i < $count; $i++) {
                            $idx = $i+1;
                            $it = $edit_data['items'][$i];
                            echo '<div class="bill-inner" data-row="'. $idx .'" id="opening_row_'.$idx.'">';
                            echo '<div class="row">';
                            echo '<div class="col-md-3 form-row"><label>Ledger (Head name)</label><input type="text" name="account_'.$idx.'" id="account_'.$idx.'" class="form-control account-input" placeholder="Start typing ledger..." onFocus="set_current('.$idx.')" value="'.h(lookup($db,'billit_customer',$it['ledger_head_id'],'cus_name')).'"><input type="hidden" name="account_'.$idx.'_sno" id="account_'.$idx.'_sno" value="'.h(intval($it['ledger_head_id'])).'"><input type="hidden" name="ledger_type_'.$idx.'" id="ledger_type_'.$idx.'"></div>';
                            echo '<div class="col-md-3 form-row"><label>Opening Date</label><input type="date" name="opening_date_'.$idx.'" id="opening_date_'.$idx.'" class="form-control" value="'.h($it['opening_date']).'"></div>';
                            echo '<div class="col-md-3 form-row"><label>Opening Amount</label><input type="number" step="0.01" name="opening_amount_'.$idx.'" id="opening_amount_'.$idx.'" class="form-control" value="'.h($it['opening_amount']).'"></div>';
                            echo '<div class="col-md-3 form-row"><label>Remark</label><input type="text" name="opening_remark_'.$idx.'" id="opening_remark_'.$idx.'" class="form-control" value="'.h($it['remark']).'"></div>';
                            echo '</div>';
                            echo '<div class="row"><div class="col-12 text-end" style="margin-top:8px;"><button type="button" class="btn btn-danger btn-sm removeRow" data-row="'.$idx.'">Remove</button></div></div>';
                            echo '</div>';
                        }
                        echo '<script>window.onload = function(){ $("#max_rows").val('.$count.'); }</script>';
                    }
                    ?>
                </div>

                <input type="hidden" name="max_rows" id="max_rows" value="<?php echo ($edit_mode ? count($edit_data['items']) : 1); ?>">
                <?php if ($edit_mode): ?><input type="hidden" name="edit_id" value="<?php echo intval($edit_data['summary']['id']); ?>"><?php endif; ?>

                <div style="margin-top:12px;">
                    <div class="row">
                        <div class="col-md-4 text-end">
                            <button type="submit" name="submit" class="btn btn-success" style="margin-top:30px;"><?php echo $edit_mode ? 'Update Opening(s) ' : 'Save Opening(s)'; ?></button>
                            <?php if ($edit_mode): ?><a class="btn btn-secondary" style="margin-top:30px;" href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>">Cancel</a><?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
      </form>

    </div>
  </div>

  <!-- REPORT CARD -->
  <div class="card">
    <div class="card-body">

      <?php if ($view_mode && $view_data): ?>
          <div class="view-card">
            <h6>Batch Details — #<?php echo intval($view_data['summary']['id']); ?></h6>
            <div class="small-muted mb-2">
              Type: <?php echo h($view_data['summary']['entry_type']); ?> |
              <?php if ($view_data['summary']['entry_type'] === 'project'): ?>
                Department: <?php echo h(lookup($db,'uprnss_department_name',$view_data['summary']['department'],'department_name_hindi')); ?> |
                Project: <?php echo h(lookup($db,'uprnss_project_temp',$view_data['summary']['project_id'],'project_name_hindi')); ?> |
              <?php else: ?>
                Ledger: <?php echo h(lookup($db,'billit_customer',$view_data['summary']['other_ledger_sno'],'cus_name')); ?> |
              <?php endif; ?>
              Division: <?php echo h(lookup($db,'uprnss_division',$view_data['summary']['division_id'],'division_name')); ?>
            </div>
            <hr>
            <table class="table table-sm table-bordered">
              <thead><tr><th>#</th><th>Ledger (Head name)</th><th>Opening Date</th><th>Amount</th><th>Remark</th></tr></thead>
              <tbody>
              <?php $si=1; foreach ($view_data['items'] as $it){ echo '<tr>'; echo '<td>'.intval($si++).'</td>'; echo '<td>'.h(lookup($db,'billit_customer',$it['ledger_head_id'],'cus_name')).'</td>'; echo '<td>'.h($it['opening_date']).'</td>'; echo '<td>'.number_format($it['opening_amount'],2).'</td>'; echo '<td>'.h($it['remark']).'</td>'; echo '</tr>'; } if (empty($view_data['items'])) echo '<tr><td colspan="5">No items</td></tr>'; ?>
              </tbody>
            </table>
            <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn btn-sm btn-secondary">Back</a>
          </div>

      <?php else: ?>
        <h6>Openings Report</h6>
        <?php
            $where = " WHERE 1=1 ";
            $filter_department = intval($_POST['filter_department'] ?? 0);
            $filter_division   = intval($_POST['filter_division'] ?? 0);
            $filter_type       = ($_POST['filter_type'] ?? '');

            if ($filter_department > 0) $where .= " AND s.department = {$filter_department}";
            if ($filter_division > 0)   $where .= " AND s.division_id = {$filter_division}";
            if ($filter_type === 'project') $where .= " AND s.entry_type = 'project'";
            if ($filter_type === 'other')   $where .= " AND s.entry_type = 'other'";

            $page = max(1, intval($_GET['p'] ?? 1));
            $perpage = 25;
            $offset = ($page - 1) * $perpage;

            $total_q = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM `invoice_project_opening_summary` s {$where}");
            $total_row = mysqli_fetch_assoc($total_q);
            $total = intval($total_row['cnt']);

            $grand_q = mysqli_query($db, "
                SELECT COALESCE(SUM(i.opening_amount),0) AS grand_total_amount, COUNT(i.id) AS grand_item_count
                FROM `invoice_project_opening_summary` s
                LEFT JOIN `invoice_project_opening_items` i ON i.opening_id = s.id
                {$where}
            ");
            $grand_row = mysqli_fetch_assoc($grand_q);
            $grand_total_amount = floatval($grand_row['grand_total_amount'] ?? 0);
            $grand_item_count = intval($grand_row['grand_item_count'] ?? 0);

            $data_sql = "
                SELECT s.*, 
                       COALESCE(SUM(i.opening_amount),0) AS total_amount,
                       COUNT(i.id) AS item_count
                FROM `invoice_project_opening_summary` s
                LEFT JOIN `invoice_project_opening_items` i ON i.opening_id = s.id
                {$where}
                GROUP BY s.id
                ORDER BY s.created_at DESC
                LIMIT {$offset}, {$perpage}
            ";
            $data_q = mysqli_query($db, $data_sql);
        ?>

        <div class="row mb-3">
          <div class="col-md-9">
            <form method="post" class="row g-2">
              <div class="col-md-3">
                <select name="filter_department" class="form-control">
                  <option value="">--All Departments--</option>
                  <?php
                    $dq = mysqli_query($db, "SELECT sno, department_name_hindi FROM uprnss_department_name ORDER BY department_name_hindi");
                    while($d = mysqli_fetch_assoc($dq)) {
                        $sel = ($filter_department === intval($d['sno'])) ? 'selected' : '';
                        echo '<option value="'.intval($d['sno']).'" '.$sel.'>'.h($d['department_name_hindi']).'</option>';
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-3">
                <select name="filter_division" class="form-control">
                  <option value="">--All Divisions--</option>
                  <?php
                    $vq = mysqli_query($db, "SELECT s_no, division_name FROM uprnss_division ORDER BY division_name");
                    while($v = mysqli_fetch_assoc($vq)) {
                        $selv = ($filter_division === intval($v['s_no'])) ? 'selected' : '';
                        echo '<option value="'.intval($v['s_no']).'" '.$selv.'>'.h($v['division_name']).'</option>';
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-3">
                <select name="filter_type" class="form-control">
                  <option value="">--All Types--</option>
                  <option value="project" <?php if ($filter_type === 'project') echo 'selected'; ?>>Project</option>
                  <option value="other" <?php if ($filter_type === 'other') echo 'selected'; ?>>Other (Ledger)</option>
                </select>
              </div>

              <div class="col-md-1">
                <button class="btn btn-secondary">Filter</button>
              </div>
            </form>
          </div>

          <div class="col-md-3 text-end small-muted">
            Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $perpage, $total); ?> of <?php echo $total; ?>
          </div>
        </div>

        <div class="mb-2">
          <strong>Grand totals (filtered data):</strong>
          Total Amount: <strong><?php echo number_format($grand_total_amount, 2); ?></strong>
        </div>

        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th>Type</th>
              <th>Department</th>
              <th>Division</th>
              <th>Project / Ledger</th>
              <th style="width:80px">Heads</th>
              <th style="width:140px">Total Amount</th>
              <th style="width:160px">Created At</th>
              <th style="width:80px;text-align:center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sr = $offset + 1;
            $page_sum_items = 0;
            $page_sum_amount = 0.0;
            if ($data_q && mysqli_num_rows($data_q) > 0) {
                while ($row = mysqli_fetch_assoc($data_q)) {
                    echo '<tr>';
                    echo '<td>'.intval($sr++).'</td>';
                    echo '<td>'.h($row['entry_type']).'</td>';
                    echo '<td>'.h(lookup($db,'uprnss_department_name',$row['department'],'department_name_hindi')).'</td>';
                    echo '<td>'.h(lookup($db,'uprnss_division',$row['division_id'],'division_name')).'</td>';
                    if ($row['entry_type'] === 'project') {
                        echo '<td>'.h(lookup($db,'uprnss_project_temp',$row['project_id'],'project_name_hindi')).'</td>';
                    } else {
                        echo '<td>'.h(lookup($db,'billit_customer',$row['other_ledger_sno'],'cus_name')).'</td>';
                    }
                    echo '<td>'.intval($row['item_count']).'</td>';
                    echo '<td>'.number_format($row['total_amount'],2).'</td>';
                    echo '<td>'.h($row['created_at']).'</td>';
                    echo '<td class="no-print actions-col" style="white-space:nowrap">
                              <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  Actions
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                  <a class="dropdown-item" href="'.$_SERVER['PHP_SELF'].'?edit='.intval($row['id']).'">✏️ Edit</a>
                                  <a class="dropdown-item" href="'.$_SERVER['PHP_SELF'].'?view='.intval($row['id']).'">👁️ View Details</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item text-danger" href="'.$_SERVER['PHP_SELF'].'?del='.intval($row['id']).'" onclick="return confirm(\'Delete this entry?\')">🗑️ Delete</a>
                                </div>
                              </div>
                          </td>';
                    echo '</tr>';

                    $page_sum_items += intval($row['item_count']);
                    $page_sum_amount += floatval($row['total_amount']);
                }
            } else {
                echo '<tr><td colspan="9">No records</td></tr>';
            }
            ?>
          </tbody>

          <?php if ($page_sum_items > 0): ?>
          <tfoot>
            <tr>
              <th colspan="5" class="text-end">Page totals:</th>
              <th><?php echo intval($page_sum_items); ?></th>
              <th><?php echo number_format($page_sum_amount,2); ?></th>
              <th colspan="2"></th>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>

        <!-- Pagination -->
        <?php
        $pages = max(1, ceil($total / $perpage));
        if ($pages > 1) {
            echo '<nav><ul class="pagination">';
            for ($i=1;$i<=$pages;$i++) {
                $cls = ($i == $page) ? 'active' : '';
                echo '<li class="page-item '.$cls.'"><a class="page-link" href="?p='.$i.'">'.$i.'</a></li>';
            }
            echo '</ul></nav>';
        }
        ?>

      <?php endif; ?>

    </div>
  </div>

</div>

<?php
page_footer_start();
page_footer_end();
?>

<script>
var currentAccountRow = 1;
function set_current(idx){
    currentAccountRow = idx;
    $('#current').val(idx);
}

$(document).ready(function(){
    var actionUrl = 'scripts/ajax.php';

    function fill_sub_department(val, selected){
        var data = {"term":"b", "id":"sub_dep", "val":val};
        $.post(actionUrl, data, function(resp){
            var txt = '<option value="">--Select--</option>';
            try { resp = JSON.parse(resp); } catch(e){ resp = []; }
            $.each(resp, function(k,v){ txt += '<option value="'+v.id+'" '+(selected==v.id ? 'selected' : '')+'>'+v.sub_department_hindi+'</option>'; });
            $('#sub_department_id').html(txt);
        });
    }

    function fill_district(val, selected){
        var data = {"term":"b", "id":"dist", "val":val};
        $.post(actionUrl, data, function(resp){
            var txt = '<option value="">--Select--</option>';
            try { resp = JSON.parse(resp); } catch(e){ resp = []; }
            $.each(resp, function(k,v){ txt += '<option value="'+v.id+'" '+(selected==v.id ? 'selected' : '')+'>'+v.district_name+'</option>'; });
            $('#district').html(txt);
        });
    }

    function fill_project(val, selected){
        var data = {"term":"b", "id":"proj", "val":val, "dept":$('#department').val()};
        $.post(actionUrl, data, function(resp){
            var txt = '<option value="">--Select--</option>';
            try { resp = JSON.parse(resp); } catch(e){ resp = []; }
            $.each(resp, function(k,v){ txt += '<option value="'+v.id+'" '+(selected==v.id ? 'selected' : '')+'>'+v.project_name_hindi+'</option>'; });
            $('#project_name').html(txt);
        });
    }

    // toggle blocks: project-related fields shown only when project; division always visible
    function toggleTypeBlocks(selected){
        if (selected === 'project') {
            $('#location_and_project_row').show();
            $('#project_block_inline').show();
            $('#other_ledger_block').hide();
        } else {
            // hide the location row (dept, sub-dept, district, project) but keep Division visible (it's outside)
            $('#location_and_project_row').hide();
            $('#project_block_inline').hide();
            $('#other_ledger_block').show();
        }
    }

    <?php if ($edit_mode): ?>
        fill_sub_department(<?php echo intval($edit_data['summary']['department']); ?>, <?php echo intval($edit_data['summary']['sub_department_id'] ?? 0); ?>);
        fill_district(<?php echo intval($edit_data['summary']['department']); ?>, <?php echo intval($edit_data['summary']['district'] ?? 0); ?>);
        setTimeout(function(){
            fill_project(<?php echo intval($edit_data['summary']['district']); ?>, <?php echo intval($edit_data['summary']['project_id']); ?>);
        }, 300);
        toggleTypeBlocks('<?php echo h($edit_data['summary']['entry_type']); ?>');
    <?php elseif (!empty($_POST['department'])): ?>
        fill_sub_department(<?php echo intval($_POST['department']); ?>, <?php echo intval($_POST['sub_department_id'] ?? 0); ?>);
        fill_district(<?php echo intval($_POST['department']); ?>, <?php echo intval($_POST['district'] ?? 0); ?>);
        toggleTypeBlocks('<?php echo h($_POST['entry_type'] ?? 'project'); ?>');
    <?php else: ?>
        toggleTypeBlocks($('#entry_type').val());
    <?php endif; ?>

    $('#department').on('change', function(){
        var dept = $(this).val();
        $('#sub_department_id').html('<option value="">--Select--</option>');
        $('#district').html('<option value="">--Select--</option>');
        $('#project_name').html('<option value="">--Select--</option>');
        if(dept) { fill_sub_department(dept); fill_district(dept); }
    });
    $('#sub_department_id').on('change', function(){ var dept = $('#department').val(); if(dept) fill_district(dept); });
    $('#district').on('change', function(){ var did = $(this).val(); if(did) fill_project(did); });

    $('#entry_type').on('change', function(){
        toggleTypeBlocks($(this).val());
    });

    // Autocomplete for item ledger inputs
    var autoOptions = {
        source: function(request, response) {
            $.getJSON("scripts/billit_ajax.php?id=cust_name&cust=1", request, response);
        },
        minLength: 1,
        select: function(event, ui) {
            var rowIdx = currentAccountRow || 1;
            $('#account_' + rowIdx).val(ui.item.label || ui.item.value);
            $('#account_' + rowIdx + '_sno').val(ui.item.id || '');
            $('#ledger_type_' + rowIdx).val(ui.item.type || '');
            return false;
        }
    };

    // Autocomplete for header ledger (other type)
    var headerAuto = {
        source: function(request,response) {
            $.getJSON("scripts/billit_ajax.php?id=cust_name&cust=1", request, response);
        },
        minLength: 1,
        select: function(event, ui) {
            $('#account_header').val(ui.item.label || ui.item.value);
            $('#account_header_sno').val(ui.item.id || '');
            return false;
        }
    };

    $(document).on('focus', '.account-input', function(){
        var id = $(this).attr('id');
        var parts = id.split('_');
        var idx = parts[1] || 1;
        currentAccountRow = parseInt(idx, 10);
        $('#current').val(currentAccountRow);
        $(this).autocomplete(autoOptions);
    });

    $(document).on('focus', '.account-input-header', function(){
        $(this).autocomplete(headerAuto);
    });

    var counter = parseInt($('#max_rows').val() || '1', 10) + 1;
    $('#addOpeningRow').on('click', function(e){
        e.preventDefault();
        var idx = counter;
        var html = '<div class="bill-inner" data-row="'+idx+'" id="opening_row_'+idx+'">'+
            '<div class="row">'+
            '<div class="col-md-3 form-row"><label>Ledger (Head name)</label><input type="text" name="account_'+idx+'" id="account_'+idx+'" class="form-control account-input" placeholder="Start typing ledger..." onFocus="set_current('+idx+')"><input type="hidden" name="account_'+idx+'_sno" id="account_'+idx+'_sno"><input type="hidden" name="ledger_type_'+idx+'" id="ledger_type_'+idx+'"></div>' +
            '<div class="col-md-3 form-row"><label>Opening Date</label><input type="date" name="opening_date_'+idx+'" id="opening_date_'+idx+'" class="form-control" value="<?php echo h($_POST['opening_date'] ?? date('Y').'-04-01'); ?>"></div>' +
            '<div class="col-md-3 form-row"><label>Opening Amount</label><input type="number" step="0.01" name="opening_amount_'+idx+'" id="opening_amount_'+idx+'" class="form-control"></div>' +
            '<div class="col-md-3 form-row"><label>Remark</label><input type="text" name="opening_remark_'+idx+'" id="opening_remark_'+idx+'" class="form-control"></div>' +
            '</div>' +
            '<div class="row"><div class="col-12 text-end" style="margin-top:8px;"><button type="button" class="btn btn-danger btn-sm removeRow" data-row="'+idx+'">Remove</button></div></div>' +
            '</div>';

        $('#additional_opening_blocks').append(html);
        $('#max_rows').val(idx);
        counter++;
    });

    $(document).on('click', '.removeRow', function(){
        var idx = $(this).data('row');
        $('#opening_row_' + idx).remove();
        var highest = 1;
        $('[id^=opening_row_]').each(function(){
            var id = $(this).attr('id');
            var parts = id.split('_');
            var r = parseInt(parts[2] || '1', 10);
            if (r > highest) highest = r;
        });
        $('#max_rows').val(highest);
    });

});
</script>
