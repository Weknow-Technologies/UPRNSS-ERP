<?php
include ("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();

if (isset($_GET['delid'])) {
    $sql = 'select * from inovoice_architect_allotment where sno="' . $_GET['delid'] . '"';
    $data = mysqli_fetch_assoc(execute_query($sql));
    $data['project_id'];
    // status 5= deleted

    $sql = 'update inovoice_architect_allotment set
        status = "5" where sno="' . $_GET['delid'] . '"';
    execute_query($sql);
    if (mysqli_error($db)) {
        $msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
    } else {
        $sql = 'update uprnss_project_temp set 
            architect_id = "",
            structural_architect_id = ""   
            where sno="' . $data['project_id'] . '"';
        execute_query($sql);
        $msg .= '<p class="alert alert-success">Deleted</p>';
    }
}

if (isset($_GET['approvesno'])) {
    $current_date = date('Y-m-d');
    $sql = 'update inovoice_architect_allotment set 
        approval_status = "approved", 
        approval_date = "' . $current_date . '" 
        where sno="' . $_GET['approvesno'] . '"';
    execute_query($sql);
    if (mysqli_error($db)) {
        $msg .= '<p class="text text-danger">Error # 2 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
    } else {
        $msg .= '<p class="alert alert-success">Approved</p>';
    }
}
?>

<style>
    .fixed-small-font {
        font-size: 13px;
        padding: 2px 7px;
    }
</style>
<form name="test" action="architect_allotment_report_print.php" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">
            <div class="card strpied-tabled-with-hover">
                <?php if ($_SESSION['usertype'] != '1') {
                    echo '
                    <div class="card-header ml-auto no-print">
                        <a href="architect_allotment.php" class="text-right" target=""><u><i class="icon fas fa-plus" aria-hidden="true"></i> नामित के लिए शेष प्रियोजनाये</u></a>
                    </div>';
                }
                echo $msg;
                ?>
                <div class="col-md-12 text-center">
                    <button formtarget="_blank" type="submit" name="student_ledger"
                        class="btn btn-primary">Print</button>
                </div>
                <div class="card-body table-full-width table-responsive">
                    <table class="table table-hover table-striped" id="general_stat_table">
                        <thead>
                            <tr>
                                <?php if ($_SESSION['usertype'] != '1') {
                                    echo '
                                    <th class="no-print"></th>';
                                }
                                ?>
                                <th>क्रं सं.</th>
                                <th>कार्य का नाम </th>
                                <th>धनराशि (लाख मे )</th>
                                <th>स्तर </th>
                                <th>शासनादेश संख्या/ दिनांक</th>
                                <th>जनपद का नाम </th>
                                <th>प्रखण्ड का नाम </th>
                                <th>आर्किटेक्ट</th>
                                <th>स्ट्रक्चरल आर्किटेक्ट </th>
                                <th>Allotment date</th>
                                <?php if ($_SESSION['usertype'] != '1') {
                                    echo '
                                            <th>Edit</th>
                                            <th>Delete</th>
                                            <th>Approve</th>';
                                }
                                ?>
                                <th>Work Order</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($_SESSION['usertype'] != '1') {
                                $sql = 'SELECT * FROM `inovoice_architect_allotment` WHERE status!="5" order by sno DESC';
                                $result = execute_query($sql);
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $sql = 'select * from uprnss_division where s_no="' . $row['division_id'] . '"';
                                    $divisions = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_district where sno="' . $row['district_id'] . '"';
                                    $district = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_project_temp where sno="' . $row['project_id'] . '"';
                                    $row_project = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_architect where sno="' . $row['architect_id'] . '"';
                                    $architect = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_architect where sno="' . $row['structural_architect_id'] . '"';
                                    $starch = mysqli_fetch_assoc(execute_query($sql));

                                    echo '<tr>
                                        <td><input type="checkbox" name="print_sno_' . $row['sno'] . '" id="date_of_acceptance_revised_cost" class="form-control" value="' . $row['sno'] . '" tabindex=""></td>
                                        <td>' . $i++ . '</td>
                                        <td>' . $row_project['project_name_hindi'] . '</td>
                                        <td>' . $row_project['sanction_cost'] . '</td>
                                        <td>';
                                    if ($row_project['project_type'] != '') {
                                        if ($row_project['project_type'] == '1') {
                                            echo '<span class="">शासन स्तर </span>';
                                        } elseif ($row_project['project_type'] == '2') {
                                            echo '<span class="">जिला स्तर </span>';
                                        }
                                    }
                                    echo '
                                    </td>
                                    <td>' . $row_project['admin_go_no'] . '<br>';
                                    if ($row_project['admin_go_date'] == "") {
                                        echo "-";
                                    } else {
                                        echo date("d-m-Y", strtotime($row_project['admin_go_date']));
                                    }
                                    echo '</td>
                                    <td>' . $district['district_name_hindi'] . '</td>
                                    <td>' . $divisions['division_name'] . '</td>
                                    <td>' . (isset($architect['full_name_english']) ? $architect['full_name_english'] : 'N/A') . '</td>
                                    <td>' . (isset($starch['full_name_english']) ? $starch['full_name_english'] : 'N/A') . '</td>
                                    <td>';
                                    if ($row['allotment_date'] == "") {
                                        echo "-";
                                    } else {
                                        echo date("d-m-Y", strtotime($row['allotment_date']));
                                    }
                                    echo '</td>
                                    <td class="no-print text-center">
                                        <a href="architect_namit.php?edit_sno=' . $row['sno'] . '" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title=""></span></a>
                                    </td>
                                    <td class="no-print text-center">
                                        <a href="architect_alloted_report.php?delid=' . $row['sno'] . '" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
                                    </td>
                                    <td class="no-print text-center">
                                        <a href="architect_alloted_report.php?approvesno=' . $row['sno'] . '" onClick="return confirm(\'Are you sure?\');" style="color:#0a0"><span class="far fa-check-circle" aria-hidden="true" data-toggle="tooltip" title="Approve"></span></a>
                                    </td>';

                                    if ($row['approval_status'] == 'approved') {
                                        if ($row['alloted_type'] == 'both') {
                                            echo '<td class="no-print text-center" style="font-size: smaller;">
                                                <a href="arch_and_str_allotment_print.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font" >Office</a>
                                                <a href="arch_and_str_allotment_print2.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font" >Main</a>
                                                <a href="arch_and_str_allotment_print3.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font" >General</a>
                                            </td>';
                                        } else {
                                            echo '<td class="no-print text-center" style="font-size: smaller;">
                                                <a href="arch_and_str_allotment_print.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">Office</a>
                                                <a href="arch_and_str_allotment_print2.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">Main</a>
                                                <a href="arch_and_str_allotment_print3.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">General</a>
                                            </td>';
                                        }
                                    } else {
                                        echo '<td class="no-print text-center">
                                            <span class="btn btn-secondary fixed-small-font" disabled>Not Approved</span>
                                        </td>';
                                    }

                                    echo '</tr>';
                                }
                            } else {
                                $sql = 'SELECT * FROM `inovoice_architect_allotment` WHERE status!="5" AND architect_id="' . $_SESSION['user_id'] . '" order by sno DESC';
                                $result = execute_query($sql);
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $sql = 'select * from uprnss_division where s_no="' . $row['division_id'] . '"';
                                    $divisions = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_district where sno="' . $row['district_id'] . '"';
                                    $district = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_project_temp where sno="' . $row['project_id'] . '"';
                                    $row_project = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_architect where sno="' . $row['architect_id'] . '"';
                                    $architect = mysqli_fetch_assoc(execute_query($sql));

                                    $sql = 'select * from uprnss_architect where sno="' . $row['structural_architect_id'] . '"';
                                    $starch = mysqli_fetch_assoc(execute_query($sql));

                                    echo '<tr>
                                    <td>' . $i++ . '</td>
                                    <td>' . $row_project['project_name_hindi'] . '</td>
                                    <td>' . $row_project['sanction_cost'] . '</td>
                                    <td>';
                                    if ($row_project['project_type'] != '') {
                                        if ($row_project['project_type'] == '1') {
                                            echo '<span class="">शासन स्तर </span>';
                                        } elseif ($row_project['project_type'] == '2') {
                                            echo '<span class="">जिला स्तर </span>';
                                        }
                                    }
                                    echo '
                                    </td>
                                    <td>' . $row_project['admin_go_no'] . '<br>';
                                    if ($row_project['admin_go_date'] == "") {
                                        echo "-";
                                    } else {
                                        echo date("d-m-Y", strtotime($row_project['admin_go_date']));
                                    }
                                    echo '</td>
                                    <td>' . $district['district_name_hindi'] . '</td>
                                    <td>' . $divisions['division_name'] . '</td>
                                    <td>' . (isset($architect['full_name_english']) ? $architect['full_name_english'] : 'N/A') . '</td>
                                    <td>' . (isset($starch['full_name_english']) ? $starch['full_name_english'] : 'N/A') . '</td>
                                    <td>';
                                    if ($row['allotment_date'] == "") {
                                        echo "-";
                                    } else {
                                        echo date("d-m-Y", strtotime($row['allotment_date']));
                                    }
                                    echo '</td>';

                                    if ($row['approval_status'] == 'approved') {
                                        if ($row['alloted_type'] == 'both') {
                                            echo '<td class="no-print text-center" style="font-size: smaller;">
                                                <a href="arch_and_str_allotment_print.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">Office</a>
                                                <a href="arch_and_str_allotment_print2.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">Main</a>
                                                <a href="arch_and_str_allotment_print3.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">General</a>
                                            </td>';
                                        } else {
                                            echo '<td class="no-print text-center" style="font-size: smaller;">
                                                <a href="arch_and_str_allotment_print.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">Office</a>
                                                <a href="arch_and_str_allotment_print2.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">Main</a>
                                                <a href="arch_and_str_allotment_print3.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-success fixed-small-font">General</a>
                                            </td>';
                                        }
                                    } else {
                                        echo '<td class="no-print text-center">
                                            <span class="btn btn-secondary fixed-small-font" disabled>Not Approved</span>
                                        </td>';
                                    }

                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#general_stat_table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            ordering: false
        });
    });
</script>