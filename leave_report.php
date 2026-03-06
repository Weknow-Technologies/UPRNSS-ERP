<?php
include("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();

if (isset($_POST['edit_sno'], $_POST['employee_id'], $_POST['employee_name'], $_POST['employee_type'], $_POST['contact_no'], $_POST['leave_type'], $_POST['leave_days'], $_POST['start_date'], $_POST['end_date'], $_POST['leave_desc'], $_POST['location'])) {
    $edit_sno = $_POST['edit_sno'];
    
    $sql = "UPDATE apply_leave SET 
            employee_id='{$_POST['employee_id']}',  employee_name='{$_POST['employee_name']}',  employee_type='{$_POST['employee_type']}',  contact_no='{$_POST['contact_no']}',  leave_type='{$_POST['leave_type']}',  leave_days='{$_POST['leave_days']}',  start_date='{$_POST['start_date']}',  end_date='{$_POST['end_date']}',  leave_desc='{$_POST['leave_desc']}',  location='{$_POST['location']}' 
            WHERE sno='$edit_sno'";
    
    execute_query($sql);
    $msg .= '<p class="text text-success">Data Updated Successfully.</p>';
}

if (isset($_GET['edit_sno'])) {
    $edit_sno = $_GET['edit_sno'];
    $sql = "SELECT * FROM apply_leave WHERE sno='$edit_sno'";
    $result = execute_query($sql);
    $data = mysqli_fetch_assoc($result);
}

if (isset($_GET['delid'])) {
    $sql = "DELETE FROM apply_leave WHERE sno='". $_GET['delid'] ."'";
    execute_query($sql);
    $msg .= '<p class="text text-danger">Data Deleted.</p>';
}
// Handle search parameters (if needed)
$search_employee_id = isset($_GET['search_employee_id']) ? $_GET['search_employee_id'] : '';
$search_employee_name = isset($_GET['search_employee_name']) ? $_GET['search_employee_name'] : '';
$search_employee_type = isset($_GET['search_employee_type']) ? $_GET['search_employee_type'] : '';
$search_leave_type = isset($_GET['search_leave_type']) ? $_GET['search_leave_type'] : '';

?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Leave Application Report</h4><br>

                <!-- Search Form -->
                <form method="get" action="<?php echo ($_SERVER['PHP_SELF']); ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search_employee_id">Search by Employee ID</label>
                                <input type="text" class="form-control" id="search_employee_id" name="search_employee_id" value="<?php echo ($search_employee_id); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search_employee_name">Search by Employee Name</label>
                                <input type="text" class="form-control" id="search_employee_name" name="search_employee_name" value="<?php echo ($search_employee_name); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label
                                    style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Employee
                                    Type</label>
                                <select class="form-control" name="search_employee_type" id="search_employee_type"
                                    tabindex="<?php echo $tab++; ?>">
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $query = 'SELECT * from dp_category order by category_name ASC';
                                    $run = mysqli_query($db, $query);
                                    while ($data = mysqli_fetch_array($run)) {
                                        echo '<option value="' . $data['sno'] . '" ';
                                        if (isset($_POST['employee_type_id'])) {
                                            if ($_POST['employee_type_id'] == $data['sno']) {
                                                echo ' selected="Selected"';
                                            }
                                        }
                                        echo '>' . trim($data['category_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search_leave_type">Search by Leave Type</label>
                                <select class="form-control" name="search_leave_type" id="search_leave_type">
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $query = "SELECT * FROM `leave_type` WHERE status != '5'";
                                    $run = mysqli_query($db, $query);
                                    while ($data = mysqli_fetch_array($run)) {
                                        echo '<option value="' . ($data['leave_type']) . '" ';
                                        if ($search_leave_type == $data['leave_type']) {
                                            echo ' selected="selected"';
                                        }
                                        echo '>' . (trim($data['leave_type'])) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary" style="margin-top: 30px;">Search</button>
                        </div>
                    </div>
                </form>
                <br>
                <?php echo $msg; ?>

                <table class="table table-hover table-striped">
                    <thead>
                        <tr>                        
                            <th>S.No.</th>
                            <th>Division</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Employee Type</th>
                            <th>Contact No.</th>
                            <th>Leave Type</th>
                            <th>Leave Days</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Description</th>
                            <th>Edit</th>
                            <th>Delete</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        $sql = "SELECT * FROM apply_leave WHERE status != '5'";
                        if (!empty($search_employee_id)) {
                            $sql .= " AND employee_id LIKE '%" . $search_employee_id . "%'";
                        }
                        if (!empty($search_employee_name)) {
                            $sql .= " AND employee_name LIKE '%" . $search_employee_name . "%'";
                        }
                        if (!empty($search_employee_type)) {
                            $sql .= " AND employee_type LIKE '%" . $search_employee_type . "%'";
                        }
                        if (!empty($search_leave_type)) {
                            $sql .= " AND leave_type LIKE '%" . $search_leave_type . "%'";
                        }
                        $result = execute_query($sql);
                        $i = 1;
                        while ($res = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo ($res['location']); ?></td>
                                <td><?php echo ($res['employee_id']); ?></td>
                                <td><?php echo ($res['employee_name']); ?></td>
                                <td><?php echo ($res['employee_type']); ?></td>
                                <td><?php echo ($res['contact_no']); ?></td>
                                <td><?php echo ($res['leave_type']); ?></td>
                                <td><?php echo ($res['leave_days']); ?></td>
                                <td><?php echo ($res['start_date']); ?></td>
                                <td><?php echo ($res['end_date']); ?></td>
                                <td><?php echo ($res['leave_desc']); ?></td>
                                <td>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?edit_sno=<?php echo ($res['sno']); ?>" onClick="return confirm('Are you sure you want to edit?');" data-toggle="tooltip" title="Edit Details">
                                        <span class="far fa-edit" aria-hidden="true"></span>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?delid=<?php echo ($res['sno']); ?>" onclick="return confirm('Are you sure you want to delete?');" style="color:#f00" data-toggle="tooltip" title="Delete Entry">
                                    <span class="far fa-trash-alt" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        }                        
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
page_footer_end();
?>

