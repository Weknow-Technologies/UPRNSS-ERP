<?php
include("scripts/settings.php");

$msg = '';
$tab = 1;

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
                                <th>Employee Division</th>
                                <th>Number of Employees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Fetch division details
                                $sql_division = 'SELECT s_no, division_name FROM `uprnss_division` ORDER BY `division_name` ASC'; 
                                $result_division = execute_query($sql_division);
                                
                                $i = 1;
                                $tot_employees = 0;

                                while ($row_des = mysqli_fetch_assoc($result_division)) {
                                    // Fetch employees in the current division
                                    $sql_employees = 'SELECT * FROM `dp_personal_info` WHERE division_id = ' . $row_des['s_no'];
                                    $result_employees = execute_query($sql_employees);
                                    $num_employees = mysqli_num_rows($result_employees);

                                    $tot_employees += $num_employees;

                                    echo '<tr>
                                        <td>' . $i++ . '</td>
                                        <td><a href="division_details.php?division_id=' . $row_des['s_no'] . '">' . strtoupper($row_des['division_name']) . '</a></td>
                                        <td>' . $num_employees . '</td>
                                    </tr>';
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
