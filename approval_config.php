<?php
include("scripts/settings.php");

$msg = '';
$tab = 1;

/* ================= SAVE / UPDATE ================= */
if (isset($_POST['submit'])) {

    if ($_POST['edit_id'] == '') {
        $sql = 'INSERT INTO approvals_config 
                (module_name, level, designation_id) 
                VALUES (
                    "' . $_POST['module_name'] . '",
                    "' . $_POST['level'] . '",
                    "' . $_POST['designation_id'] . '"
                )';
    } else {
        $sql = 'UPDATE approvals_config SET
                module_name = "' . $_POST['module_name'] . '",
                level = "' . $_POST['level'] . '",
                designation_id = "' . $_POST['designation_id'] . '"
                WHERE id = "' . $_POST['edit_id'] . '"';
    }

    execute_query($sql);

    if (mysqli_error($db)) {
        $msg .= '<p class="text-danger">Error : ' . mysqli_error($db) . '</p>';
    } else {
        $msg .= '<p class="text-success">Data Saved Successfully</p>';
        $_POST = [];
    }
}

/* ================= EDIT ================= */
if (isset($_GET['eid'])) {
    $sql = 'SELECT * FROM approvals_config WHERE id="' . $_GET['eid'] . '"';
    $data = mysqli_fetch_assoc(execute_query($sql));

    $_POST['edit_id'] = $data['id'];
    $_POST['module_name'] = $data['module_name'];
    $_POST['level'] = $data['level'];
    $_POST['designation_id'] = $data['designation_id'];
}

/* ================= DELETE ================= */
if (isset($_GET['delid'])) {
    $sql = 'DELETE FROM approvals_config WHERE id="' . $_GET['delid'] . '"';
    execute_query($sql);
    $msg .= '<p class="text-danger">Data Deleted</p>';
}

page_header_start();
page_header_end();
page_sidebar();
?>

<form method="post" action="">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-center">Approval Configuration</h4>
                </div>

                <?php echo $msg; ?>

                <div class="card-body">
                    <div class="row">

                        <!-- Module Name -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Module Name</label>
                                <input type="text" name="module_name" class="form-control"
                                       value="<?php echo $_POST['module_name'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <!-- Level -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Level</label>
                                <input type="number" name="level" class="form-control"
                                       value="<?php echo $_POST['level'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <!-- Designation -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>User Type (Designation)</label>
                                <select name="designation_id" class="form-control" required>
                                    <option value="">-- Select User Type --</option>
                                    <?php
                                    $sql = 'SELECT * FROM user_type ORDER BY user_type';
                                    $res = execute_query($sql);
                                    while ($row = mysqli_fetch_assoc($res)) {
                                        $sel = (!empty($_POST['designation_id']) && $_POST['designation_id'] == $row['sno']) ? 'selected' : '';
                                        echo '<option value="' . $row['sno'] . '" ' . $sel . '>' . $row['user_type'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <button type="submit" name="submit" class="btn btn-success col-12">
                                    Save
                                </button>
                                <input type="hidden" name="edit_id"
                                       value="<?php echo $_POST['edit_id'] ?? ''; ?>">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- ================= LISTING ================= -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title text-center">Approval Config List</h4>
            </div>
            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Module</th>
                        <th>Level</th>
                        <th>User Type</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    $i = 1;
                    $sql = 'SELECT ac.*, ut.user_type 
                            FROM approvals_config ac
                            LEFT JOIN user_type ut ON ut.sno = ac.designation_id
                            ORDER BY ac.module_name, ac.level';

                    $res = execute_query($sql);
                    while ($row = mysqli_fetch_assoc($res)) {
                        echo '<tr>
                            <td>' . $i++ . '</td>
                            <td>' . $row['module_name'] . '</td>
                            <td>' . $row['level'] . '</td>
                            <td>' . $row['user_type'] . '</td>
                            <td>
                                <a href="?eid=' . $row['id'] . '" onclick="return confirm(\'Edit this entry?\')">
                                    <i class="far fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <a href="?delid=' . $row['id'] . '" style="color:red"
                                   onclick="return confirm(\'Delete this entry?\')">
                                    <i class="far fa-trash-alt"></i>
                                </a>
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
page_footer_end();
?>
