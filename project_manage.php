<?php
include ("scripts/settings.php");
$msg = '';
$tab = 1;

if (!isset($_POST['go_type'])) {
	$_POST['go_type'] = 1;
	$_POST['date_from'] = date("2023-04-01");
	$_POST['date_to'] = date("Y-m-d");
	$_POST['department'] = '';
	$_POST['project_type'] = '';
}

page_header_start();
page_header_end();
page_sidebar();

?>
<div id="container" class="no-print">
	<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">
			<div class="row d-flex my-auto">
				<table width="100%" class="table table-striped table-hover rounded">
					<tr>
						<th width="16%">G.O.Type</th>
						<th width="18%"><select name="go_type" id="go_type" class="form-control">
								<option value="">--- Select ---</option>
								<option value="1" <?php echo ($_POST['go_type'] == 1 ? ' selected="selected"' : ''); ?>>
									Administrative</option>
								<option value="3" <?php echo ($_POST['go_type'] == 3 ? ' selected="selected"' : ''); ?>>
									Administrative+Financial </option>

							</select>
						</th>
						<th width="15%">Form</th>
						<th width="18%"><input type="date" name="date_from" id="date_from"
								value="<?php echo $_POST['date_from']; ?>" class="form-control"></th>
						<th width="15%">To</th>
						<th width="18%"><input type="date" name="date_to" id="date_to"
								value="<?php echo $_POST['date_to']; ?>" class="form-control"></th>
					</tr>

					<tr>
						<th>Project Type</th>
						<th width="18%">
							<select name="project_type" id="project_type" class="form-control"
								onchange="onchangetype()">
								<option value="">--- Select ---</option>
								<option value="1" <?php echo ($_POST['project_type'] == 1 ? 'selected' : ''); ?>>Ho Level
								</option>
								<option value="2" <?php echo ($_POST['project_type'] == 2 ? 'selected' : ''); ?>>Division
									Level</option>
							</select>
						</th>
						<th>Project Status</th>
						<th>
							<select class="form-control" name="project_status_1" id="project_status_1"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from master_projoect_status_1";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['project_status_1'])) {
										if ($_POST['project_status_1'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['status_1']) . '</option>';
								}
								?>
							</select>
						</th>
						<th>टेंडर की स्थिति</th>
						<th>
							<select class="form-control" name="tender_status" id="tender_status"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from master_tender_status";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['tender_status'])) {
										if ($_POST['tender_status'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['status']) . '</option>';
								}
								?>
							</select>
						</th>
					</tr>
				</table>
				<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
					<tr>
						<th>विभाग </th>
						<th width="15%">
							<select required class="form-control" name="department" id="department"
								tabindex="<?php echo $tab++; ?>"
								onChange="fill_sub_department(this.value), fill_scheme(this.value)">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from uprnss_department_name";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['department'])) {
										if ($_POST['department'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . trim($data['department_name_hindi']) . '</option>';
								}
								?>
							</select>
						</th>
						<th>उप-विभाग </th>
						<th width="10%">
							<!-- <select class="form-control" name="sub_department_id" id="sub_department_id"
								tabindex="<?php echo $tab++; ?>">
								<option value ="">-----Select-----</option>
								<?php
								$query = "select * from uprnss_sub_department";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value = "' . $data['sno'] . '" ';
									if (isset($_POST['sub_department'])) {
										if ($_POST['sub_department'] == $data['sno']) {
											echo ' selected = "Selected"';
										}
									}
									echo '>' . trim($data['sub_department_hindi']) . '</option>';
								}
								?>
							</select> -->
							<select class="form-control" name="sub_department_id" id="sub_department_id"
								tabindex="<?php echo $tab++; ?>">
						</th>

						</th>
						<th>योजना </th>
						<th width="15%">
							<select class="form-control" name="scheme" id="scheme" tabindex="<?php echo $tab++; ?>">
						</th>
						<th>उप-योजना </th>
						<th width="15%">
							<select class="form-control" name="scheme" id="scheme" tabindex="<?php echo $tab++; ?>">
						</th>
					</tr>
					<tr>
						<th>प्रखण्ड </th>
						<th width="15%"><select class="form-control" name="division_name" id="division_name"
								tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = 'select * from uprnss_division order by division_name ASC';
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['s_no'] . '" ';
									if (isset($_POST['division_name'])) {
										if ($_POST['division_name'] == $data['s_no']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . $data['division_name'] . '</option>';
								}
								?>
							</select>
						</th>
						<th>आच्छादित जनपद</th>
						<th width="15%">
							<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
								<?php
								$query = "select * from uprnss_district";
								$run = mysqli_query($db, $query);
								while ($data = mysqli_fetch_array($run)) {
									echo '<option value="' . $data['sno'] . '" ';
									if (isset($_POST['district'])) {
										if ($_POST['district'] == $data['sno']) {
											echo ' selected="Selected"';
										}
									}
									echo '>' . $data['district_name_hindi'] . '</option>';
								}
								?>
							</select>
						</th>
					</tr>
				</table>
				<div class="col-md-12 text-center mt-3">
					<button type="submit" name="search" class="btn btn-primary">Search</button>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="card strpied-tabled-with-hover">
			<div class="card-header ">
				<h4 class="card-title">Project list</h4>
			</div>
			<div class="card-body table-full-width table-responsive m-auto">
			<table class="table table-hover table-striped" id="general_stat_table">
					<thead>
						<tr>
							<th>S.No.</th>
							<th>Creation Time</th>
							<th>Department </br>Sub-department</th>
							<th>G.O.Type</th>
							<th>G.O.Number </br>G.O Date</th>
							<th>Division</th>
							<th>District</th>
							<th>Project Name</th>
							<th>Project Sub Name</th>
							<th>Project Sub Name Hindi</th>
							<th>Scheme</th>
							<!--<th>Sub scheme</th>-->
							<th>Civil Status</th>
							<th>Architect Allotment Status</th>
							<th>Tender Status</th>
							<th>Technical Sanction Status</th>
							<th>Contractor Selection</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$sql = 'SELECT *,
						transaction_new_project.sno as tsno,
						transaction_new_project.edition_time
						FROM `transaction_new_project`
						LEFT JOIN invoice_new_project 
						ON transaction_new_project.invoice_id = invoice_new_project.sno
						WHERE invoice_new_project.status != "5" ';

						if ($_POST['go_type'] != '') {
							$sql .= ' and go_type="' . $_POST['go_type'] . '"';
						}
						if ($_POST['go_type'] != '') {
							$sql .= ' and go_date >="' . $_POST['date_from'] . '" and go_date <"' . $_POST['date_to'] . '"';
						}
						if ($_POST['department'] != '') {
							$sql .= ' and department="' . $_POST['department'] . '"';
						}
						$sql .= 'order by invoice_new_project.sno DESC ';
						// echo $sql;
						$result_invoice_details = execute_query($sql);
						$i = 1;
						while ($row_invoice_details = mysqli_fetch_assoc($result_invoice_details)) {
							$sql = "select * from uprnss_project_temp where new_project_trans_id='" . $row_invoice_details['tsno'] . "' ";
							// echo $sql;
							$row_temp = mysqli_fetch_assoc(mysqli_query($db, $sql));

							$sql = 'select * from uprnss_department_name where sno="' . $row_invoice_details['department'] . '"';
							$department = mysqli_fetch_assoc(execute_query($sql));

							$sql = 'select * from uprnss_project_scheme where sno="' . $row_invoice_details['scheme'] . '"';
							// echo $sql;
							$scheme = mysqli_fetch_assoc(execute_query($sql));
							$scheme = execute_query($sql);
							if (mysqli_num_rows($scheme) != 0) {
								$scheme = mysqli_fetch_assoc($scheme);
							} else {
								unset($scheme);
								$scheme['scheme_name_hindi'] = '';
							}

							$sql = 'select * from uprnss_sub_department where sno="' . $row_invoice_details['sub_department_id'] . '"';
							// echo $sql;
							$sub_dep = mysqli_fetch_assoc(execute_query($sql));
							$sub_dep = execute_query($sql);
							if (mysqli_num_rows($sub_dep) != 0) {
								$sub_dep = mysqli_fetch_assoc($sub_dep);
							} else {
								unset($sub_dep);
								$sub_dep['sub_department_hindi'] = '';
							}

							$sql = 'select * from uprnss_division where s_no="' . $row_invoice_details['division_id'] . '"';
							// echo $sql;
							$div = execute_query($sql);
							if (mysqli_num_rows($div) != 0) {
								$div = mysqli_fetch_assoc($div);
							} else {
								unset($div);
								$div['division_name'] = '';
							}

							$sql = 'select * from uprnss_district where sno="' . $row_invoice_details['district_id'] . '"';
							$district = execute_query($sql);
							if (mysqli_num_rows($district) != 0) {
								$district = mysqli_fetch_assoc($district);
							} else {
								unset($district);
								$district['district_name_english'] = '';
							}

							$dateString = $row_invoice_details['creation_time'];
							$dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);
							if ($dateTime !== false) {
								$year = $dateTime->format("Y");
								// echo $year;
							} else {
								// echo 'Invalid date format';
							}
							echo '<tr>
								<td>' . $i++ . '</td>
								
								<td>' . date("d-m-Y H:i:s", strtotime($row_invoice_details['creation_time'])) . '</td>
								<td>' . $department['department_name_hindi'] . '</br>' . $sub_dep['sub_department_hindi'] . '</td>
								<td>';
							if ($row_invoice_details['go_type'] != '') {
								if ($row_invoice_details['go_type'] == '1') {
									echo '<span class="">Administrative</span>';
								} elseif ($row_invoice_details['go_type'] == '2') {
									echo '<span class="">Financial</span>';
								} elseif ($row_invoice_details['go_type'] == '3') {
									echo '<span class="">Administrative + Financial</span>';
								}
							}
							echo '
								</td>
								<td>' . $row_invoice_details['go_number'] . '</br>' . date("d-m-Y", strtotime($row_invoice_details['go_date'])) . '</td>
								<td>' . $div['division_name'] . '</td>
								<td>' . $district['district_name_hindi'] . '</td>
								<td>' . $row_invoice_details['project_name_hindi'] . '</td>
								<td>' . $row_invoice_details['sub_project_name_hindi'] . '</td>
								<td>' . $scheme['scheme_name_hindi'] . '</td>
								<td> </td>
								
								<td>';
							if (isset($row_temp['new_project_trans_id']) && $row_temp['new_project_trans_id'] != '') {
								if (isset($row_temp['status']) && $row_temp['status'] == "2") {
									echo '<div class="btn btn-danger btn-sm" style="background-color:red">Pending!</div>';
								} else {
									if (!empty($row_temp['edition_time'])) {
										$formattedDate = date("d-m-Y H:i:s", strtotime($row_temp['edition_time']));
									} else {
										$formattedDate = "Date not available";
									}
									echo '<div class="btn btn-info btn-sm">Approved!</div> <hr>' . $formattedDate;
								}
							} else {
								echo "Invalid data or missing information.";
							}
						
							echo '
								</td>
								<td>';
							// echo $row_invoice_details['tsno'];
							$sql = "SELECT * FROM `inovoice_architect_allotment` WHERE project_id = '" . $row_temp['sno'] . "'";
							$result = execute_query($sql);
							$rowcount = mysqli_num_rows($result);
							if ($rowcount > 0) {
								echo '<div class="btn btn-info btn-sm" style="background-color:lightgreen">Alloted !</div>';
							} else {
								echo '<div class="btn btn-danger btn-sm" style="background-color:red;"> Pending!</div>';
							}
							echo '
								</td>
								
								<td>';

							$sql = "SELECT * FROM `tender_allotment` WHERE project_id = '" . $row_temp['sno'] . "'";
							$result = execute_query($sql);
							$rowcount = mysqli_num_rows($result);
							if ($rowcount > 0) {
								echo '<div class="btn btn-info btn-sm" style="background-color:lightgreen">Alloted !</div>';
							} else {
								echo '<div class="btn btn-danger btn-sm" style="background-color:red;"> Pending!</div>';
							}

							echo '
								</td>
								
								<td>';

							$sql = "SELECT * FROM `technical_sanction` WHERE project_id = '" . $row_temp['sno'] . "'";
							$result = execute_query($sql);
							$rowcount = mysqli_num_rows($result);
							if ($rowcount > 0) {
								echo '<div class="btn btn-info btn-sm" style="background-color:lightgreen">Alloted !</div>';
							} else {
								echo '<div class="btn btn-danger btn-sm" style="background-color:red;"> Pending!</div>';
							}


							echo '
								</td>	
								<td>';											
							$sql = "SELECT * FROM `technical_sanction` WHERE project_id = '" . $row_temp['sno'] . "'";
							$result = execute_query($sql);
							$rowcount = mysqli_num_rows($result);
							if ($rowcount > 0) {
								echo '<div class="btn btn-info btn-sm" style="background-color:lightgreen">Alloted !</div>';
							} else {
								echo '<div class="btn btn-danger btn-sm" style="background-color:red;"> Pending!</div>';
							}


							echo '
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
?>


<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

<script>

	$(document).ready(function () {
		$('#general_stat_table').DataTable({
			// paging: false,
			fixedHeader: true,
			// colReorder: true,
			// scrollX: true, // Enable horizontal scrolling if needed
		});


	});

	$('select[multiple]').multiselect();

	var actionUrl = 'scripts/ajax.php';
	function fill_sub_department(val, selected = '') {
		var data = { "term": "b", "id": "sub_dep", "val": val };

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (value.id == selected) {
						txt += ' selected="selected" ';
					}
					txt += '>' + value.sub_department_hindi + '</option>';

				});
				$("#sub_department_id").html(txt);
			}
		});
	}

	function fill_scheme(val, selected = '') {
		var data = { "term": "b", "id": "scheme", "val": val };

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (value.id == selected) {
						txt += ' selected="selected" ';
					}
					txt += '>' + value.scheme_name_hindi + '</option>';

				});
				$("#scheme").html(txt);
			}
		});
	}

</script>
<!--  Charts Plugin -->
<script src="js/chartist.min.js"></script>

<?php
page_footer_end();
?>