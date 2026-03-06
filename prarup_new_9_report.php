<?php
include ("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i = 1;
$msg = '';

?>

<style>
    .bold-text {
        font-weight: bold;
    }
</style>

<?php //echo $_SERVER['PHP_SELF'];  ?>
<?php //echo $_SESSION['unit_name'];  ?>

<div id="container" class="no-print">
    <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
        action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
        <div class="card card-body">
            <div class="row d-flex my-auto">

                <table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
                    <tr>
                        <th width="5%"></th>
                        <th>परियोजना का नाम </th>
                        <th width="15%">
                            <select class="form-control" name="project_id" id="project_id"
                                tabindex="<?php echo $tab++; ?>">
                                <option value="">--- Select ---</option>
                                <?php
                                $query = '(SELECT sno, project_name_hindi, Project_name, status FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
                                // echo $query;
                                $run = mysqli_query($db, $query);
                                $a = 1;
                                while ($data = mysqli_fetch_array($run)) {
                                    echo '<option value="' . $data['sno'] . '" ';
                                    if (isset($_POST['project_id_' . $a])) {
                                        if ($_POST['project_id_' . $a] == $data['sno']) {
                                            echo ' selected="Selected"';
                                        }
                                    }
                                    echo '>' . trim($data['project_name_hindi']) . '</option>';
                                }
                                ?>
                            </select>
                        </th>
                        <th width="10%"></th>
                        <th>प्रखण्ड </th>
                        <th width="15%"><select class="form-control" name="division_id" id="division_id"
                                tabindex="<?php echo $tab++; ?>">
                                <option value="">--- Select ---</option>
                                <?php
                                $query = 'select * from uprnss_division order by division_name ASC';
                                $run = mysqli_query($db, $query);
                                while ($data = mysqli_fetch_array($run)) {
                                    echo '<option value="' . $data['s_no'] . '" ';
                                    if (isset($_POST['division_id'])) {
                                        if ($_POST['division_id'] == $data['s_no']) {
                                            echo ' selected="Selected"';
                                        }
                                    }
                                    echo '>' . $data['division_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </th>
                        <th width="10%"></th>
                        <th>माह</th>
                        <th width="15%">
                            <select name="month" id="month" class="form-control" value="">
                                <option value="">--Select--</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </th>
                        <th width="5%"></th>
                    </tr>
                </table>
                <div class="col-md-12 text-center mt-3">
                    <button type="submit" name="search" class="btn btn-primary">Search</button>
                    <input type="hidden" id="id" name="id" value="1">
                </div>
            </div>
        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title text-center">प्रखण्डों के अभिलेखों में डेबिट (अधिक भुगतान) परियोजनाओं का विवरण
                </h4></br>
            </div>
            <?php echo $msg; ?>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover text-right">
                <thead style="position:sticky;top:0; z-index:2;">
                        <tr>
                            <th>Sno.</th>
                            <th>प्रखण्ड का नाम </th>
                            <th>Entry Date</th>
                            <th>वित्तीय वर्ष </th>
                            <th>विभाग का नाम </th>
                            <th>परियोजना का नाम </th>
                            <th>स्वीकृत धनराशि</th>
                            <th>अवमुक्त धनराशि</th>
                            <th>व्यय धनराशि</th>
                            <th>अवशेष धनराशि</th>
                            <th>अभ्युक्ति</th>
                        </tr>
                        <tr>
                            <?php
                            for
                            ($i = 1; $i <= 11; $i++) {
                                echo '<th>' . $i . '</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($_SESSION['usertype'] == '1') {
                            $sql = 'SELECT `invoice_prarup_9`.`division_id`, `invoice_prarup_9`.`entry_date`, `trans_prarup_9`.`year`, `trans_prarup_9`.`vibhagname`, `trans_prarup_9`.`pariyojnaname`,`trans_prarup_9`.`swekritdhanrashi`,`trans_prarup_9`.`avmuktdhanrashi`, `trans_prarup_9`.`vyadhanrashi`, `trans_prarup_9`.`avsheshdhanrashi`, `trans_prarup_9`.`remark`  FROM `trans_prarup_9` LEFT JOIN `invoice_prarup_9` ON `invoice_prarup_9`.`sno` = `trans_prarup_9`.`invoice_id`where invoice_prarup_9.created_by = "' . $_SESSION['username'] . '"';
                            if (isset($_POST['search'])) {
                                if ($_POST['division_id'] != '') {
                                    $sql .= ' and invoice_prarup_9.division_id="' . $_POST['division_id'] . '"';
                                }
                                if ($_POST['month'] != '') {
                                    $sql .= ' and trans_prarup_9.month="' . $_POST['month'] . '"';
                                }
                            }

                            $i = 1;
                            $result_prarup_9 = execute_query($sql);
                            $total_entry_date = 0;
                            $total_year = 0;
                            $total_swekritdhanrashi = 0;
                            $total_avmuktdhanrashi = 0;
                            $total_vyadhanrashi = 0;
                            $total_avsheshdhanrashi = 0;
                            $total_remark = 0;
                            while ($row_prarup_9 = mysqli_fetch_assoc($result_prarup_9)) {
                                $sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_9['pariyojnaname'] . '")';
                                $row_project = mysqli_fetch_assoc(execute_query($sql));
                                $row_project = execute_query($sql);
                                if (mysqli_num_rows($row_project) != 0) {
                                    $row_project = mysqli_fetch_assoc($row_project);
                                } else {
                                    unset($row_project);
                                    $row_project['project_name_hindi'] = '';
                                }
                                $sql = 'select * from uprnss_department_name where sno="' . $row_prarup_9['vibhagname'] . '"';
                                $row_dep = mysqli_fetch_assoc(execute_query($sql));
                                $row_dep = execute_query($sql);
                                if (mysqli_num_rows($row_dep) != 0) {
                                    $row_dep = mysqli_fetch_assoc($row_dep);
                                } else {
                                    unset($row_dep);
                                    $row_dep['department_name_hindi'] = '';
                                }
                                $sql = 'select * from uprnss_division where s_no="' . $row_prarup_9['division_id'] . '"';
                                $row_div = mysqli_fetch_assoc(execute_query($sql));
                                $row_div = execute_query($sql);
                                if (mysqli_num_rows($row_div) != 0) {
                                    $row_div = mysqli_fetch_assoc($row_div);
                                } else {
                                    unset($row_div);
                                    $row_div['division_name'] = '';
                                }
                                $total_entry_date += floatval($row_prarup_9['entry_date']);
                                $total_year += floatval($row_prarup_9['year']);
                                $total_swekritdhanrashi += floatval($row_prarup_9['swekritdhanrashi']);
                                $total_avmuktdhanrashi += floatval($row_prarup_9['avmuktdhanrashi']);
                                $total_vyadhanrashi += floatval($row_prarup_9['vyadhanrashi']);
                                $total_avsheshdhanrashi += floatval($row_prarup_9['avsheshdhanrashi']);
                                // $total_remark += floatval($row_prarup_9['remark']);
                        
                                echo '<tr>
                                            <td>' . $i++ . '</td>
                                            <td>' . $row_div['division_name'] . '</td>
                                            <td>' . $row_prarup_9['entry_date'] . '</td> 
                                            <td>' . $row_prarup_9['year'] . '</td>
                                            <td>' . $row_dep['department_name_hindi'] . '</td>
                                            <td>' . $row_project['project_name_hindi'] . '</td>
                                            <td>' . $row_prarup_9['swekritdhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['avmuktdhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['vyadhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['avsheshdhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['remark'] . '</td>
                                        </tr>';
                            }
                        } else {
                            $sql = 'SELECT `invoice_prarup_9`.`division_id`, `invoice_prarup_9`.`entry_date`, `trans_prarup_9`.`year`, `trans_prarup_9`.`vibhagname`, `trans_prarup_9`.`pariyojnaname`,`trans_prarup_9`.`swekritdhanrashi`,`trans_prarup_9`.`avmuktdhanrashi`, `trans_prarup_9`.`vyadhanrashi`, `trans_prarup_9`.`avsheshdhanrashi`, `trans_prarup_9`.`remark`  FROM `trans_prarup_9` LEFT JOIN `invoice_prarup_9` ON `invoice_prarup_9`.`sno` = `trans_prarup_9`.`invoice_id`where 1=1';
                            if (isset($_POST['search'])) {
                                if ($_POST['division_id'] != '') {
                                    $sql .= ' and invoice_prarup_9.division_id="' . $_POST['division_id'] . '"';
                                }
                                if ($_POST['month'] != '') {
                                    $sql .= ' and trans_prarup_9.month="' . $_POST['month'] . '"';
                                }
                            }

                            $total_entry_date = 0;
                            $total_year = 0;
                            $total_swekritdhanrashi = 0;
                            $total_avmuktdhanrashi = 0;
                            $total_vyadhanrashi = 0;
                            $total_avsheshdhanrashi = 0;
                            $total_remark = 0;

                            $i = 1;
                            $result_prarup_9 = execute_query($sql);
                            while ($row_prarup_9 = mysqli_fetch_assoc($result_prarup_9)) {
                                $sql = '(SELECT sno,department_id, project_name_hindi, Project_name FROM uprnss_project_temp WHERE sno ="' . $row_prarup_9['pariyojnaname'] . '")';
                                $row_project = mysqli_fetch_assoc(execute_query($sql));
                                $row_project = execute_query($sql);
                                if (mysqli_num_rows($row_project) != 0) {
                                    $row_project = mysqli_fetch_assoc($row_project);
                                } else {
                                    unset($row_project);
                                    $row_project['project_name_hindi'] = '';
                                }
                                $sql = 'select * from uprnss_department_name where sno="' . $row_prarup_9['vibhagname'] . '"';
                                $row_dep = mysqli_fetch_assoc(execute_query($sql));
                                $row_dep = execute_query($sql);
                                if (mysqli_num_rows($row_dep) != 0) {
                                    $row_dep = mysqli_fetch_assoc($row_dep);
                                } else {
                                    unset($row_dep);
                                    $row_dep['department_name_hindi'] = '';
                                }
                                $sql = 'select * from uprnss_division where s_no="' . $row_prarup_9['division_id'] . '"';
                                $row_div = mysqli_fetch_assoc(execute_query($sql));
                                $row_div = execute_query($sql);
                                if (mysqli_num_rows($row_div) != 0) {
                                    $row_div = mysqli_fetch_assoc($row_div);
                                } else {
                                    unset($row_div);
                                    $row_div['division_name'] = '';
                                }

                                $total_entry_date += floatval($row_prarup_9['entry_date']);
                                $total_year += floatval($row_prarup_9['year']);
                                $total_swekritdhanrashi += floatval($row_prarup_9['swekritdhanrashi']);
                                $total_avmuktdhanrashi += floatval($row_prarup_9['avmuktdhanrashi']);
                                $total_vyadhanrashi += floatval($row_prarup_9['vyadhanrashi']);
                                $total_avsheshdhanrashi += floatval($row_prarup_9['avsheshdhanrashi']);
                                // $total_remark += floatval($row_prarup_9['remark']);
                        
                                echo '<tr>
                                            <td>' . $i++ . '</td>
                                            <td>' . $row_div['division_name'] . '</td>
                                            <td>' . $row_prarup_9['entry_date'] . '</td> 
                                            <td>' . $row_prarup_9['year'] . '</td>
                                            <td>' . $row_dep['department_name_hindi'] . '</td>
                                            <td>' . $row_project['project_name_hindi'] . '</td>
                                            <td>' . $row_prarup_9['swekritdhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['avmuktdhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['vyadhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['avsheshdhanrashi'] . '</td>
                                            <td>' . $row_prarup_9['remark'] . '</td>
                                        </tr>';

                            }
                        }
                        ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="bold-text" colspan="6"> योग </th>

                            <td class="bold-text"> <?php echo $total_swekritdhanrashi; ?> </td>
                            <td class="bold-text"> <?php echo $total_avmuktdhanrashi; ?> </td>
                            <td class="bold-text"> <?php echo $total_vyadhanrashi; ?> </td>
                            <td class="bold-text"> <?php echo $total_avsheshdhanrashi; ?> </td>

                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
</div>
</form>

<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

    $('select[multiple]').multiselect();
    $(document).ready(function () {
        /*$('#general_stat_table').DataTable({
            paging: false,
            fixedHeader: true,
            colReorder: true
            });
        });	*/


        var t = $('#general_stat_table').DataTable({
            // paging: false
        });


    });
</script>


<?php
page_footer_end();
?>