<?php
include("scripts/settings.php");
$msg='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();

if(isset($_GET['id'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['id'].'"';
	$division = mysqli_fetch_assoc(execute_query($sql));
}
?>
<style>
    .status-badge {
        font-size: 1.2rem;
        padding: 10px 15px;
        border-radius: 8px;
    }
    .bg-success {
        background-color: #28a745 !important;
        color: white !important;
    }
    .bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
</style>
<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header " style="background-color: #fb5c64;">
                    <h3 class="card-title text-center text-white"><?php echo $division['division_name']; ?> प्रखण्ड परियोजना की स्तिथि </h3><br>
                </div>
                <?php echo $msg; ?>
                <div class="card-body">
					<?php
					$closing_status_labels = [
						'0' => 'Running Projects',
						'1' => 'Closed Projects'
					];

					foreach ($closing_status_labels as $status_val => $status_label) {
						echo '<h4 class="text-center font-weight-bold my-4">' . $status_label . '</h4>';

						echo '<table class="table table-striped table-bordered align-middle">';
						echo '<thead class="table-primary text-center">
								<tr>
									<th rowspan="2">S.No.</th>
									<th rowspan="2">Department Name</th>
									<th rowspan="2">District Name</th>
									<th rowspan="2">Project Name</th>
									<th rowspan="2">Civil Status</th>
									<th rowspan="2">Architect Allotment</th>
									<th rowspan="2">Land Survey Status</th>
									<th rowspan="2">Tender Status</th>
									<th rowspan="2">Technical Sanction</th>
									<th colspan="2">Reports as On</th>
								</tr>
								<tr>
									<th>4th</th>
									<th>16th</th>
								</tr>
							</thead><tbody>';

						$sql = 'SELECT uprnss_project_temp.sno as sno, new_project_trans_id,edition_time, status, reporting_status, project_name_hindi, master_freeze, department_name_hindi, district_name_hindi, admin_go_no  
								FROM `uprnss_project_temp`
								LEFT JOIN uprnss_district ON uprnss_district.sno = district_id
								LEFT JOIN uprnss_department_name ON uprnss_department_name.sno = department_id
					WHERE uprnss_project_temp.division_id IN (' . $_GET['id'] . ') AND uprnss_project_temp.status != "5" AND uprnss_project_temp.reporting_status = '.$status_val.'
								ORDER BY `uprnss_department_name`.`department_name_hindi` ASC';

						$result = execute_query($sql);
						$i = 1;

						while ($row = mysqli_fetch_assoc($result)) {
							// Architect Status
							$sql_arch = 'SELECT * FROM `inovoice_architect_allotment` WHERE project_id="' . $row['sno'] . '" AND status != "5"';
							$result_arch = execute_query($sql_arch);
							if (mysqli_num_rows($result_arch) > 0) {
								$architect_data = mysqli_fetch_assoc($result_arch);
								$formatted_date = !empty($architect_data['allotment_date']) ? date("d M Y", strtotime($architect_data['allotment_date'])) : "Invalid Date";
								$architect_status = '<span class="badge bg-success">Allotted</span> <hr>' . $formatted_date;
							} else {
								$architect_status = '<span class="badge bg-danger">Pending</span>';
							}

							// Land Survey Status
							$survey_status = (mysqli_num_rows(execute_query('SELECT * FROM `invoice_survey` WHERE project_id="' . $row['sno'] . '"')) > 0)
								? '<span class="badge bg-success">Allotted</span>'
								: '<span class="badge bg-danger">Pending</span>';

							// Tender Status
							$tender_status = (mysqli_num_rows(execute_query('SELECT * FROM `tender_allotment` WHERE project_id="' . $row['sno'] . '"')) > 0)
								? '<span class="badge bg-success">Allotted</span>'
								: '<span class="badge bg-danger">Pending</span>';

							// Technical Status
							$technical_status = (mysqli_num_rows(execute_query('SELECT * FROM `technical_sanction` WHERE project_id="' . $row['sno'] . '"')) > 0)
								? '<span class="badge bg-success">Allotted</span>'
								: '<span class="badge bg-danger">Pending</span>';

							// Report on 4th and 16th
							$today = date("Y-m-d");
							$sql_report_4 = 'SELECT * FROM `invoice_civil` WHERE project_name="' . $row['sno'] . '" AND last_update >= "' . date("Y-m-01") . '" AND last_update <= "' . date("Y-m-14") . '"';
							$report_4 = (mysqli_num_rows(execute_query($sql_report_4)) > 0)
								? '<span class="text-success">Yes</span>'
								: '<span class="text-danger">No</span>';

							$sql_report_16 = 'SELECT * FROM `invoice_civil` WHERE project_name="' . $row['sno'] . '" AND last_update >= "' . date("Y-m-15") . '" AND last_update <= "' . date("Y-m-t") . '"';
							$report_16 = (mysqli_num_rows(execute_query($sql_report_16)) > 0)
								? '<span class="text-success">Yes</span>'
								: '<span class="text-danger">No</span>';

							echo '<tr>
									<td>' . $i++ . '</td>
									<td>' . $row['department_name_hindi'] . '</td>
									<td>' . $row['district_name_hindi'] . '</td>
									<td><strong>' . $row['project_name_hindi'] . '</strong><br><div class="text-muted small">' . $row['admin_go_no'] . '</div></td>
									<td class="text-center">';
							if ($row['status'] == "2") {
								echo '<span class="badge bg-danger">Pending</span>';
							} else {
								$formattedDate = !empty($row['edition_time']) ? date("d M Y H:i:s", strtotime($row['edition_time'])) : "Date Invalid";
								echo '<span class="badge bg-success">Approved!</span><hr>' . $formattedDate;
							}
							echo '</td>
									<td class="text-center">' . $architect_status . '</td>
									<td class="text-center">' . $survey_status . '</td>
									<td class="text-center">' . $tender_status . '</td>
									<td class="text-center">' . $technical_status . '</td>
									<td class="text-center">' . $report_4 . '</td>
									<td class="text-center">' . $report_16 . '</td>
								</tr>';
						}
						echo '</tbody></table>';
					}
					?>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
$('select[multiple]').multiselect();
</script>
<?php
page_footer_end();
?>
