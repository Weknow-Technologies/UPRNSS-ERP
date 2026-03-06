<?php
include ("scripts/settings.php");
$msg = '';
page_header_start();
page_header_end();
page_sidebar();


if (isset($_POST['search'])) {
    $employee_code = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    if(empty($employee_code) && empty($employee_name)){
        $msg = '<p class = "text text-danger">Please enter Employee ID or Employee Name to search.</p>';
    }else {

    $sql = "SELECT sno, division_id, old_employee_code, full_name, employee_type_id 
                FROM dp_personal_info 
                WHERE 1";

    if (!empty($employee_code)) {
        $sql .= " AND old_employee_code = '$employee_code'";
    }

    if (!empty($employee_name)) {
        $sql .= " AND full_name LIKE '%$employee_name%'";
    }

    $result = execute_query($sql);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $_POST['sno'] = $data['sno'];
        $_POST['division_id'] = $data['division_id'];
        $_POST['old_employee_code'] = $data['old_employee_code'];
        $_POST['full_name'] = $data['full_name'];
        $_POST['employee_type_id'] = $data['employee_type_id'];
    } else {
        $msg = '<p class="text text-danger">No records found.</p>';
    }
    }
}

if (isset($_POST['submit'])) {
    if (empty($_POST['edit_sno'])) {
        $sql = 'INSERT INTO apply_leave (employee_name, employee_type, leave_type, leave_days, start_date, end_date, leave_desc, location, created_by, creation_time, status)
                VALUES ("' . $_POST['employee_name'] . '", "' . $_POST['employee_type'] . '", "' . $_POST['leave_type'] . '", "' . $_POST['leave_days'] . '", "' . $_POST['start_date'] . '", "' . $_POST['end_date'] . '", "' . $_POST['leave_desc'] . '", "' . $_POST['location'] . '", "' . $_SESSION['usersno'] . '", "' . date("Y-m-d H:i:s") . '", "0")';

        execute_query($sql);
        if (mysqli_error($db)) {
            $msg .= '<p class="text text-danger">Error: ' . mysqli_error($db) . '</p>';
        } else {
            $msg .= '<p class="text text-success">Data Filled</p>';
            unset($_POST);
        }
    } else {
        $sql = 'UPDATE apply_leave SET 
                employee_name = "' . $_POST['employee_name'] . '",  
                employee_type = "' . $_POST['employee_type'] . '",
                leave_type = "' . $_POST['leave_type'] . '", 
                leave_days = "' . $_POST['leave_days'] . '", 
                start_date = "' . $_POST['start_date'] . '", 
                end_date = "' . $_POST['end_date'] . '", 
                leave_desc = "' . $_POST['leave_desc'] . '", 
                location = "' . $_POST['location'] . '", 
                edited_by = "' . $_SESSION['usersno'] . '", 
                edition_time = "' . date("Y-m-d H:i:s") . '" 
                WHERE sno="' . $_POST['edit_sno'] . '"';
        execute_query($sql);
        if (mysqli_error($db)) {
            $msg .= '<p class="text text-danger">Error: ' . mysqli_error($db) . '</p>';
        } else {
            $msg .= '<p class="text text-success">Data Updated</p>';
            unset($_POST);
        }
    }
}

if (isset($_GET['edit_sno'])) {
    $sql = 'SELECT * FROM apply_leave WHERE sno="' . $_GET['edit_sno'] . '"';
    $data = mysqli_fetch_assoc(execute_query($sql));
    $_POST['edit_sno'] = $data['sno'];
    $_POST['employee_name'] = $data['employee_name'];
    $_POST['employee_type'] = $data['employee_type'];
    $_POST['leave_type'] = $data['leave_type'];
    $_POST['leave_days'] = $data['leave_days'];
    $_POST['start_date'] = $data['start_date'];
    $_POST['end_date'] = $data['end_date'];
    $_POST['leave_desc'] = $data['leave_desc'];
    $_POST['location'] = $data['location'];
    $_POST['edition_time'] = date("Y-m-d H:i:s");
}

if (isset($_GET['delid'])) {
    $sql = 'DELETE FROM apply_leave WHERE sno="' . $_GET['delid'] . '"';
    execute_query($sql);
    $msg .= '<p class="text text-danger">Data Deleted.</p>';
}

?>

<style>
    #suggestions {
        border: 1px solid #ccc;
        max-height: 150px;
        overflow-y: auto;
    }

    .suggestion-item {
        padding: 8px;
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #f0f0f0;
    }
</style>

<form id="form" name="form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
    action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

    <div class="row no-print">
        <div class="col-md-12">
            <div class="card">
                <?php if (!isset($_POST['search'])) { ?>
                    <div class="card-header">
                        <h4 class="text-center card-title" style="font-weight: bold; background-color: #f5f8fa">Leave Form
                        </h4>
                        <h4 style="font-weight: bold; font-size:18px;">Search with Employee Code or Employee Name</h4>
                        <?php echo $msg; ?>
                    </div>
                    <div class="card-body">
                        <div class="row" id="employee_code_show">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Employee
                                        Code</label>
                                    <input type="text" name="employee_id" id="search" class="form-control"
                                        value="<?php echo isset($_POST['old_employee_code']) ? $_POST['old_employee_code'] : ''; ?>"
                                        tabindex="<?php echo $tab++; ?>" onchange="showFormSections()">
                                    <div id="suggestions"></div>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Employee
                                        Name</label>
                                    <input type="text" name="employee_name" id="employee_name" class="form-control"
                                        value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>"
                                        tabindex="<?php echo $tab++; ?>" onchange="showFormSections()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <button type="submit" name="search" class="btn btn-primary mt-4">Search</button>

                                    <input type="hidden" name="search" id="search" class="form-control" value="1">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                if (isset($_POST['search'])) {

                    ?>
                    <div class="card-header">
                        <h4 class="text-center card-title" style="font-weight: bold; background-color: #f5f8fa">Leave Form
                        </h4>
                        <h4 style="font-weight: bold; font-size:18px;"></h4>
                        <?php echo $msg; ?>
                    </div>
                    <div class="card-body" id="all_show" style="">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Division
                                        Name</label><br>
                                    <select class="form-control" name="location" id="location"
                                        tabindex="<?php echo $tab++; ?>" readonly>
                                        <option value="">--- Select ---</option>
                                        <?php
                                        $query = '(SELECT * from uprnss_division order by division_name ASC ) ';
                                        $run = mysqli_query($db, $query);
                                        while ($data = mysqli_fetch_array($run)) {
                                            echo '<option value="' . $data['s_no'] . '" ';
                                            if (isset($_POST['division_id'])) {
                                                if ($_POST['division_id'] == $data['s_no']) {
                                                    echo ' selected="Selected"';
                                                }
                                            }
                                            echo '>' . trim($data['division_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Employee
                                        Type</label>
                                    <select class="form-control" name="employee_type" id="employee_type" readonly
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
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Name
                                        of Employee</label>
                                    <input type="text" name="" id="" class="form-control"
                                        value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>"
                                        readonly tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Leave
                                        Type</label>
                                    <select class="form-control" name="leave_type" id="leave_type"
                                        tabindex="<?php echo $tab++; ?>">
                                        <option value="">--- Select ---</option>
                                        <?php
                                        $query = '(SELECT * FROM leave_type WHERE status!="5") ';
                                        echo $query;
                                        $run = mysqli_query($db, $query);
                                        while ($data = mysqli_fetch_array($run)) {
                                            echo '<option value="' . $data['leave_type'] . '" ';
                                            if (isset($_POST['leave_type'])) {
                                                if ($_POST['leave_type'] == $data['leave_type']) {
                                                    echo ' selected="Selected"';
                                                }
                                            }
                                            echo '>' . trim($data['leave_type']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Leave
                                        Days</label>
                                    <input type="text" name="leave_days" id="leave_days" class="form-control"
                                        value="<?php echo isset($_POST['leave_days']) ? $_POST['leave_days'] : ''; ?>"
                                        tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Start
                                        Date</label>
                                    <input type="date" id="start_date" name="start_date" class="form-control"
                                        value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">End
                                        Date</label>
                                    <input type="date" id="end_date" name="end_date" class="form-control"
                                        value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label
                                        style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;">Remark</label>
                                    <textarea type="text" name="leave_desc" id="leave_desc" class="form-control" <?php echo isset($_POST['leave_desc']) ? $_POST['leave_desc'] : ''; ?> > </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label
                                    style="color: #2a6496; font-weight: bold; font-size: 13px; padding: 3px 6px; border-radius: 4px;"></label>
                                <input type="hidden" name="employee_name" id="employee_name" class="form-control"
                                    value="<?php echo isset($_POST['sno']) ? $_POST['sno'] : ''; ?>" readonly
                                    tabindex="<?php echo $tab++; ?>">
                            </div>
                        </div>


                    </div>
                    <div class="col-md-11 pr-1" align="center">
                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
                            <input type="hidden" name="submit">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    </div>
</form>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<!--  Charts Plugin -->
<script src="js/chartist.min.js"></script>

<script>
    $(document).ready(function () {
        $('#general_stat_table').DataTable({
            // paging: false,
            fixedHeader: true,
            colReorder: true,
            scrollX: true, // Enable horizontal scrolling if needed
        });
    });

    $('select[multiple]').multiselect();

</script>
<script>
    document.getElementById('search').addEventListener('input', function () {
        var query = this.value;
        if (query.length > 1) {
            fetch('scripts/ajax1.php?query=' + query)
                .then(response => response.json())
                .then(data => {
                    var suggestionsBox = document.getElementById('suggestions');
                    suggestionsBox.innerHTML = '';
                    data.forEach(function (item) {
                        var div = document.createElement('div');
                        div.classList.add('suggestion-item');
                        div.textContent = item;
                        div.addEventListener('click', function () {
                            document.getElementById('search').value = item;
                            suggestionsBox.innerHTML = '';
                        });
                        suggestionsBox.appendChild(div);
                    });
                });
        } else {
            document.getElementById('suggestions').innerHTML = '';
        }
    });
</script>
<?php
page_footer_end();
?>