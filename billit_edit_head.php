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
    $make_super = isset($_POST['make_super_parent']) ? true : false;

    if ($sno > 0) {
        $parent_update = $make_super ? ", parent = '0'" : "";
        $sql = "UPDATE billit_pl_heads SET 
                description = '$description', 
                fund_type = '$fund_type', 
                visibility = '$visibility'
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

$head = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM billit_pl_heads WHERE sno = $id";
    $res = execute_query($sql);
    $head = mysqli_fetch_assoc($res);
}

$subheads = [];
if ($head) {
    $sql_sub = "SELECT * FROM billit_pl_heads WHERE parent = '" . $head['sno'] . "' ORDER BY sort_no ASC, sno ASC";
    $res_sub = execute_query($sql_sub);
    while ($row = mysqli_fetch_assoc($res_sub)) {
        $subheads[] = $row;
    }
}

page_header_start();
page_header_end();
page_sidebar();
?>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title"><i class="fa fa-edit mr-2"></i>Edit Head / Group</h4>
            </div>
            <div class="card-body p-4">
                <?php echo $msg; ?>

                <?php if ($head): ?>
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $head['sno']; ?>">
                        <input type="hidden" name="edit_sno" value="<?php echo $head['sno']; ?>">

                        <div class="form-group">
                            <label>Description / Name</label>
                            <input type="text" name="description" class="form-control"
                                value="<?php echo htmlspecialchars($head['description']); ?>" required
                                tabindex="<?php echo $tab++; ?>">
                        </div>

                        <div class="form-group">
                            <label>Fund Type</label>
                            <select name="fund_type" class="form-control" tabindex="<?php echo $tab++; ?>">
                                <option value="" <?php echo $head['fund_type'] == '' ? 'selected' : ''; ?>>-- Select --</option>
                                <option value="source" <?php echo $head['fund_type'] == 'source' ? 'selected' : ''; ?>>SOURCE OF FUNDS</option>
                                <option value="application" <?php echo $head['fund_type'] == 'application' ? 'selected' : ''; ?>>APPLICATION OF FUNDS</option>
                            </select>
                        </div>

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

                        <?php if (intval($head['parent']) != 0): ?>
                        <div class="form-group mt-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="make_super_parent" value="1">
                                    <strong>⭐ Make Super Parent</strong>
                                    <small class="text-muted d-block">This will move the group to root level (parent = 0). Sub-heads will remain unchanged.</small>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <button type="submit" name="saveHead" class="btn btn-success">
                                <i class="fa fa-save mr-1"></i> Update Changes
                            </button>
                            <a href="billit_ledger_detail_report.php" class="btn btn-link">Back to Report</a>
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
                                                <th>#</th>
                                                <th>Description</th>
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
                                                    <td><?php echo $i + 1; ?></td>
                                                    <td><?php echo htmlspecialchars($sub['description']); ?></td>
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
                                                            <i class="fa fa-pencil"></i>
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
</script>

<?php
page_footer_start();
page_footer_end();
?>