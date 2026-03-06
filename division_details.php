<?php
include("scripts/settings.php");

$division_id = isset($_GET['division_id']) ? intval($_GET['division_id']) : 0;
if ($division_id == 0) {
    die('Invalid division ID');
}

$sql = 'SELECT * FROM uprnss_division WHERE s_no = ' . $division_id;
$division_data = mysqli_fetch_assoc(execute_query($sql));

if (!$division_data) {
    die('Division not found');
}

page_header_start();
page_header_end();
page_sidebar();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="content">
                <div class="row">
                    <h4>Employees in <?php echo strtoupper($division_data['division_name']); ?></h4>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Employee Code</th>
                                <th>Date of Birth</th>
                                <th>Employee Name</th>
                                <th>Employee Designation</th>
                                <th>Employee Category</th>
                                <th>Mobile Number</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = 'SELECT * FROM dp_personal_info
                            LEFT JOIN dp_designation ON dp_designation.sno = dp_personal_info.employee_designation_id
                            LEFT JOIN dp_category ON dp_category.sno = dp_personal_info.employee_category_id
                            WHERE division_id = ' . $division_id . ' ORDER BY abs(sort_no)';
                            
                            $result_emp = execute_query($sql);
                            $i = 1;
                            
                            while ($row_emp = mysqli_fetch_assoc($result_emp)) {
                                echo '<tr>
                                    <td>' . $i++ . '</td>
                                    <td>' . $row_emp['old_employee_code'] . '</td>
                                    <td>' . $row_emp['date_of_birth'] . '</td>
                                    <td>' . $row_emp['full_name'] . '</td>
                                    <td>' . $row_emp['designation'] . '</td>
                                    <td>' . $row_emp['category_name'] . '</td>
                                    <td>' . $row_emp['c_number'] . '</td>
                                    <td>' . $row_emp['p_address1'] . ' <br> ' . $row_emp['p_district'] . '</td>
                                </tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <h4>Employee Designation in <?php echo strtoupper($division_data['division_name']); ?></h4>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Employee Designation</th>
                                <th>Number of Employees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = 'SELECT * FROM dp_designation ORDER BY ABS(sort_no)';
                            $result_div = execute_query($sql);
                            $i = 1;
                            $tot_employees = 0;

                            while ($row_des = mysqli_fetch_assoc($result_div)) {
                                $sql_employees = 'SELECT * FROM dp_personal_info WHERE employee_designation_id = "' . $row_des['sno'] . '" AND division_id = ' . $division_id;
                                $result_employees = execute_query($sql_employees);
                                $employees = mysqli_num_rows($result_employees);

                                if ($employees > 0) {
                                    $tot_employees += $employees;

                                    echo '<tr>
                                            <td>' . $i++ . '</td>
                                            <td>' . strtoupper($row_des['designation']) . '</td>
                                            <td>' . $employees . '</td>
                                        </tr>';
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Total</th>
                                <th><?php echo $tot_employees; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
    $('select[multiple]').multiselect();
</script>

<?php
page_footer_end();
?>
