<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");

$msg = '';
$tab = 1;

if (isset($_POST['saveHead'])) {
    $sno = intval($_POST['edit_sno']);
    $description = mysqli_real_escape_string($db, strtoupper($_POST['description']));
    $fund_type = mysqli_real_escape_string($db, $_POST['fund_type']);
    $visibility = mysqli_real_escape_string($db, $_POST['visibility']);
    $pl_side = isset($_POST['pl_side']) ? mysqli_real_escape_string($db, $_POST['pl_side']) : '';
    $parent = isset($_POST['parent_group']) ? intval($_POST['parent_group']) : null;
    $make_super = isset($_POST['make_super_parent']) ? true : false;

    if ($sno > 0) {
        $parent_update = "";
        if ($make_super) {
            $parent_update = ", parent = '0'";
        } elseif ($parent !== null) {
            $parent_update = ", parent = '$parent'";
        }

        $sql = "UPDATE billit_pl_heads SET 
                description = '$description', 
                fund_type = '$fund_type', 
                visibility = '$visibility',
                pl_side = " . ($pl_side === '' ? "NULL" : "'$pl_side'") . "
                $parent_update,
                edited_by = '" . $_SESSION['username'] . "', 
                edition_time = '" . date("Y-m-d H:i:s") . "' 
                WHERE sno = $sno";
        execute_query($sql);
        if (mysqli_error($db)) {
            $msg = alert("Error: " . mysqli_error($db));
        } else {
            $msg = alert("Update Successful.", "success");
        }
    }
}

if (isset($_POST['saveSubHead'])) {
    $sub_sno = intval($_POST['sub_sno']);
    $sub_description = mysqli_real_escape_string($db, strtoupper($_POST['sub_description']));
    $sub_visibility = mysqli_real_escape_string($db, $_POST['sub_visibility']);

    if ($sub_sno > 0) {
        $sql = "UPDATE billit_pl_heads SET 
                description = '$sub_description', 
                visibility = '$sub_visibility',
                edited_by = '" . $_SESSION['username'] . "', 
                edition_time = '" . date("Y-m-d H:i:s") . "' 
                WHERE sno = $sub_sno";
        execute_query($sql);
        if (mysqli_error($db)) {
            $msg = alert("Sub-head Error: " . mysqli_error($db));
        } else {
            $msg = alert("Sub-head Updated Successfully.", "success");
        }
    }
}

if (isset($_POST['saveBulkSubHeads'])) {
    $bulk_snos = isset($_POST['bulk_snos']) ? $_POST['bulk_snos'] : [];
    $row_visibility = isset($_POST['row_visibility']) ? $_POST['row_visibility'] : [];

    if (!empty($bulk_snos)) {
        $updated = 0;
        foreach ($bulk_snos as $bsno) {
            $bsno = intval($bsno);
            $bvis = isset($row_visibility[$bsno]) ? mysqli_real_escape_string($db, $row_visibility[$bsno]) : 'private';
            if ($bvis !== 'public') $bvis = 'private';
            $sql = "UPDATE billit_pl_heads SET visibility='$bvis',
                    edited_by='" . $_SESSION['username'] . "',
                    edition_time='" . date("Y-m-d H:i:s") . "'
                    WHERE sno=$bsno";
            execute_query($sql);
            if (!mysqli_error($db)) $updated++;
        }
        $msg = alert("$updated sub-head(s) updated successfully.", "success");
    } else {
        $msg = alert("No rows selected.", "warning");
    }
}

if (isset($_POST['addSubGroup'])) {
    $new_parent = intval($_POST['new_parent_id']);
    $new_desc = mysqli_real_escape_string($db, strtoupper($_POST['new_sub_description']));
    $new_visibility = mysqli_real_escape_string($db, $_POST['new_sub_visibility']);

    if ($new_parent > 0 && $new_desc != '') {
        $sql = "INSERT INTO billit_pl_heads (description, parent, visibility, created_by, creation_time)
                VALUES ('$new_desc', '$new_parent', '$new_visibility', '" . $_SESSION['username'] . "', '" . date('Y-m-d H:i:s') . "')";
        execute_query($sql);
        if (mysqli_error($db)) {
            $msg = alert("Error: " . mysqli_error($db));
        } else {
            $msg = alert("New Sub-Group Added Successfully.", "success");
        }
    }
}

if (isset($_POST['saveBulkLedgers'])) {
    $bulk_ledgers = isset($_POST['bulk_ledgers']) ? $_POST['bulk_ledgers'] : [];
    $new_parent = isset($_POST['ledger_new_parent']) ? intval($_POST['ledger_new_parent']) : 0;
    
    if (!empty($bulk_ledgers) && $new_parent > 0) {
        $updated = 0;
        foreach ($bulk_ledgers as $lsno) {
            $lsno = intval($lsno);
            $sql = "UPDATE billit_customer SET parent='$new_parent',
                    edited_by='" . $_SESSION['username'] . "',
                    edition_time='" . date("Y-m-d H:i:s") . "'
                    WHERE sno=$lsno";
            execute_query($sql);
            if (!mysqli_error($db)) $updated++;
        }
        $msg = alert("$updated ledger(s) successfully shifted to new parent group.", "success");
    } elseif ($new_parent == 0 && isset($_POST['saveBulkLedgers'])) {
        $msg = alert("Please select a target parent group to shift to.", "warning");
    } elseif (isset($_POST['saveBulkLedgers'])) {
        $msg = alert("No ledgers selected.", "warning");
    }
}

$head = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM billit_pl_heads WHERE sno = $id";
    $res = execute_query($sql);
    $head = mysqli_fetch_assoc($res);
}

$subheads = [];
$group_ledgers = [];
if ($head) {
    $sql_sub = "SELECT * FROM billit_pl_heads WHERE parent = '" . $head['sno'] . "' ORDER BY sort_no ASC, sno ASC";
    $res_sub = execute_query($sql_sub);
    while ($row = mysqli_fetch_assoc($res_sub)) {
        $subheads[] = $row;
    }
    
    $sql_ledgers = "SELECT sno, cus_name, mobile, parent FROM billit_customer WHERE parent = '" . $head['sno'] . "' AND (parent_ledger IS NULL OR parent_ledger='' OR parent_ledger='0') ORDER BY cus_name ASC";
    $res_ledgers = execute_query($sql_ledgers);
    while ($row = mysqli_fetch_assoc($res_ledgers)) {
        $group_ledgers[] = $row;
    }
}

page_header_start();
page_header_end();
page_sidebar();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: none;">
            <div class="card-header" style="background: linear-gradient(45deg, #e53935, #b71c1c); color: white; border-bottom: none;">
                <h4 class="card-title" style="margin: 0; font-weight: bold; color: white;"><i class="fa fa-edit mr-2"></i>Edit Head / Group</h4>
            </div>
            <div class="card-body p-4">
                <?php echo $msg; ?>

                <?php if ($head): ?>
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $head['sno']; ?>">
                        <input type="hidden" name="edit_sno" value="<?php echo $head['sno']; ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Description / Name</label>
                                    <input type="text" name="description" class="form-control"
                                        value="<?php echo htmlspecialchars($head['description']); ?>" required
                                        tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fund Type</label>
                                    <select name="fund_type" class="form-control" tabindex="<?php echo $tab++; ?>">
                                        <option value="" <?php echo $head['fund_type'] == '' ? 'selected' : ''; ?>>-- Select --</option>
                                        <option value="source" <?php echo $head['fund_type'] == 'source' ? 'selected' : ''; ?>>SOURCE OF FUNDS</option>
                                        <option value="application" <?php echo $head['fund_type'] == 'application' ? 'selected' : ''; ?>>APPLICATION OF FUNDS</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-eye mr-1"></i>Visibility</label>
                                    <select name="visibility" class="form-control" tabindex="<?php echo $tab++; ?>">
                                        <option value="private" <?php echo ($head['visibility'] == 'private' || $head['visibility'] == '') ? 'selected' : ''; ?>>
                                            🔒 Private (Head Office Only)
                                        </option>
                                        <option value="public" <?php echo $head['visibility'] == 'public' ? 'selected' : ''; ?>>
                                            🌐 Public (Visible to Units)
                                        </option>
                                    </select>
                                    <small class="text-muted">Private: visible to Head Office only. Public: visible to all units.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-chart-bar mr-1"></i>Trading & P&L Side (For Root Groups)</label>
                                    <select name="pl_side" class="form-control" tabindex="<?php echo $tab++; ?>">
                                        <option value="" <?php echo (empty($head['pl_side'])) ? 'selected' : ''; ?>>-- None (Not in P&L) --</option>
                                        <option value="expense" <?php echo ($head['pl_side'] == 'expense') ? 'selected' : ''; ?>>Trading & P&L Expenses</option>
                                        <option value="income" <?php echo ($head['pl_side'] == 'income') ? 'selected' : ''; ?>>Trading & P&L Incomes</option>
                                    </select>
                                    <small class="text-muted">Select a side to shift this group directly into the Profit & Loss report.</small>
                                </div>
                            </div>
                        </div>

                        <?php if (intval($head['parent']) != 0): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Parent Group</label>
                                    <select name="parent_group" class="form-control" tabindex="<?php echo $tab++; ?>">
                                        <option value="0">-- Root Group --</option>
                                        <?php
                                        echo get_group_hierarchy_options($head['parent'], $head['sno'], false);
                                        ?>
                                    </select>
                                    <small class="text-muted">Shift this group under a different parent.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mt-3" style="padding-top: 15px;">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="make_super_parent" value="1">
                                            <strong>⭐ Make Super Parent</strong>
                                            <small class="text-muted d-block">This will move the group to root level (parent = 0). Sub-heads will remain unchanged.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <button type="submit" name="saveHead" class="btn btn-success">
                                <i class="fa fa-save mr-1"></i> Update Changes
                            </button>
                            <a href="billit_ledger_detail_report.php" class="btn btn-link" style="color: #666; font-weight: bold;">Back to Report</a>
                        </div>
                    </form>

                    <?php if ($head['parent'] == '0'): ?>
                        <hr>
                        <h5 class="mt-4 mb-3"><i class="fa fa-sitemap mr-1"></i>Sub-Heads under
                            "<?php echo htmlspecialchars($head['description']); ?>"</h5>

                        <?php if (!empty($subheads)): ?>
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $head['sno']; ?>">

                                <div class="mb-2 d-flex align-items-center flex-wrap" style="gap:8px;">
                                    <strong>Bulk Set:</strong>
                                    <button type="button" class="btn btn-xs btn-success" onclick="bulkSetVisibility('public')">
                                        <i class="fa fa-globe mr-1"></i> All Public
                                    </button>
                                    <button type="button" class="btn btn-xs btn-warning" onclick="bulkSetVisibility('private')">
                                        <i class="fa fa-lock mr-1"></i> All Private
                                    </button>
                                    <label class="ml-3 mb-0">
                                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"> Select All
                                    </label>
                                    <button type="submit" name="saveBulkSubHeads" class="btn btn-xs btn-primary ml-2">
                                        <i class="fa fa-save mr-1"></i> Save Selected
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width:36px;">
                                                    <input type="checkbox" id="selectAll2" onclick="toggleSelectAll(this)">
                                                </th>
                                                <th class="text-right">S.No</th>
                                                <th class="text-left">Description</th>
                                                <th>Visibility</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subheads as $i => $sub): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="bulk_snos[]" value="<?php echo $sub['sno']; ?>" class="row-check">
                                                    </td>
                                                    <td class="text-right"><?php echo $i + 1; ?></td>
                                                    <td class="text-left"><?php echo htmlspecialchars($sub['description']); ?></td>
                                                    <td>
                                                        <select name="row_visibility[<?php echo $sub['sno']; ?>]"
                                                                class="form-control form-control-sm vis-select"
                                                                data-sno="<?php echo $sub['sno']; ?>">
                                                            <option value="private" <?php echo ($sub['visibility'] == 'private' || $sub['visibility'] == '') ? 'selected' : ''; ?>>🔒 Private</option>
                                                            <option value="public" <?php echo $sub['visibility'] == 'public' ? 'selected' : ''; ?>>🌐 Public</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <a href="billit_edit_head.php?id=<?php echo $sub['sno']; ?>"
                                                            class="btn btn-xs btn-info" title="Edit full details">
                                                            <i class="fa fa-pencil"></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            </form>
                        <?php else: ?>
                            <p class="text-muted"><i class="fa fa-info-circle mr-1"></i>No sub-groups added yet.</p>
                        <?php endif; ?>

                        <div class="panel panel-default mt-3">
                            <div class="panel-heading" style="cursor:pointer;" onclick="toggleAddSubGroup()">
                                <strong><i class="fa fa-plus-circle mr-1 text-success"></i> Add New Sub-Group</strong>
                            </div>
                            <div class="panel-body" id="addSubGroupForm" style="display:none;">
                                <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $head['sno']; ?>">
                                    <input type="hidden" name="new_parent_id" value="<?php echo $head['sno']; ?>">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Group Name <span class="text-danger">*</span></label>
                                                <input type="text" name="new_sub_description" class="form-control"
                                                    placeholder="Enter group name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Visibility</label>
                                                <select name="new_sub_visibility" class="form-control">
                                                    <option value="private">🔒 Private (Head Office Only)</option>
                                                    <option value="public">🌐 Public (Visible to Units)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label><br>
                                                <button type="submit" name="addSubGroup" class="btn btn-success btn-block">
                                                    <i class="fa fa-plus mr-1"></i> Add Group
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php endif; ?>
                    
                    <hr>
                    <h5 class="mt-4 mb-3"><i class="fa fa-users mr-1"></i>Ledgers under "<?php echo htmlspecialchars($head['description']); ?>"</h5>
                    
                    <?php if (!empty($group_ledgers)): ?>
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $head['sno']; ?>">
                            <div class="mb-2 d-flex align-items-center flex-wrap" style="gap:8px;">
                                <strong>Shift To:</strong>
                                <select name="ledger_new_parent" class="form-control form-control-sm" style="width:250px; display:inline-block;">
                                    <option value="0">-- Select New Parent Group --</option>
                                    <?php echo get_group_hierarchy_options('', '', false); ?>
                                </select>
                                <label class="ml-3 mb-0">
                                    <input type="checkbox" id="selectAllLedgers" onclick="toggleSelectAllLedgers(this)"> Select All
                                </label>
                                <button type="submit" name="saveBulkLedgers" class="btn btn-xs btn-primary ml-2">
                                    <i class="fa fa-share mr-1"></i> Shift Selected
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width:36px;">
                                                <input type="checkbox" id="selectAllLedgers2" onclick="toggleSelectAllLedgers(this)">
                                            </th>
                                            <th class="text-right">S.No</th>
                                            <th class="text-left">Ledger Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($group_ledgers as $i => $lgr): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="bulk_ledgers[]" value="<?php echo $lgr['sno']; ?>" class="ledger-check">
                                                </td>
                                                <td class="text-right"><?php echo $i + 1; ?></td>
                                                <td class="text-left"><?php echo htmlspecialchars($lgr['cus_name']); ?></td>
                                                <td>
                                                    <a href="billit_ledgers.php?id=<?php echo $lgr['sno']; ?>"
                                                        class="btn btn-xs btn-info" title="Edit full details">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-muted"><i class="fa fa-info-circle mr-1"></i>No ledgers directly under this group.</p>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-warning">
                        No head selected or head not found. <a href="billit_ledger_detail_report.php">Go back to report</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAddSubGroup() {
        var form = document.getElementById('addSubGroupForm');
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }

    function bulkSetVisibility(val) {
        document.querySelectorAll('.vis-select').forEach(function(sel) {
            sel.value = val;
        });
        document.querySelectorAll('.row-check').forEach(function(chk) {
            chk.checked = true;
        });
        document.getElementById('selectAll').checked = true;
        document.getElementById('selectAll2').checked = true;
    }

    function toggleSelectAll(source) {
        var checked = source.checked;
        document.querySelectorAll('.row-check').forEach(function(chk) {
            chk.checked = checked;
        });
        document.getElementById('selectAll').checked = checked;
        document.getElementById('selectAll2').checked = checked;
    }

    function toggleSelectAllLedgers(source) {
        var checked = source.checked;
        document.querySelectorAll('.ledger-check').forEach(function(chk) {
            chk.checked = checked;
        });
        if(document.getElementById('selectAllLedgers')) document.getElementById('selectAllLedgers').checked = checked;
        if(document.getElementById('selectAllLedgers2')) document.getElementById('selectAllLedgers2').checked = checked;
    }
</script>

<?php
page_footer_start();
page_footer_end();
?>