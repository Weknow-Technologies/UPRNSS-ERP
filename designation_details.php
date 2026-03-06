<?php
include("scripts/settings.php");

page_header_start();
page_header_end();
page_sidebar();


    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="content">
                    <div class="row">
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
								$division_id = isset($_GET['division_id']) ? intval($_GET['division_id']) : 0;

								if ($division_id > 0) {
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
                                                <td>&nbsp;</td>
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
                                    <th>&nbsp;</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    echo '<div class="row"><div class="col-md-12"><div class="card"><div class="content"><div class="alert alert-warning">No division selected. Please select a division.</div></div></div></div></div>';
}

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
