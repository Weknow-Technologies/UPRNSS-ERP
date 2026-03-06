<?php
include ("scripts/settings.php");
$msg = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();

$errormsg = "";
$exten = array();//Array for extension storing 
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
//print_r($_POST);

if (isset($_POST['submit'])) {
	if ($_POST['edit_sno'] == '') {

		echo $checkifalready = "SELECT * FROM invoice_survey WHERE project_id='{$_POST['project_name']}'";
		$checkifalreadyres = execute_query($checkifalready);
		if (mysqli_num_rows($checkifalreadyres) > 0) {
			$msg .= "<p class='alert alert-danger'>This Project is Already Submitted</p>";
		} else {
			//testing files

			//creating file hirarkey 
			//file_upload => land_survey=> year=> district sno=> uprnss_project_temp sno=> invoice_survey sno => all the files are here

			$filepath = "file_upload/land_survey/" . $_POST['district'] . "/" . $_POST['project_name'] . "/";
			echo $filepath . "<br>";
			if (!file_exists($filepath)) {
				// Create the folder
				if (mkdir($filepath, 0777, true)) {
					echo 'Folder created successfully.<br>';
				}
			}

			if ($_FILES['soil_testiong_report_file']['name'] != "") {
				$newfileName = $_FILES["soil_testiong_report_file"]["name"];
				$newfileTmpName = $_FILES["soil_testiong_report_file"]["tmp_name"];
				$newfileSize = $_FILES["soil_testiong_report_file"]['size'];
				if ($newfileSize > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>Soil Testing Report File Size is too Large . Maximum file size allowed is 4MB.</p>";
				}
				// Allowed file extensions

				$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
				// Check if the file extension is allowed
				if (!in_array($newfileExtension, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>Wrong file type of Soil Testing Report File Only PDF, JPG, JPEG, PNG files are allowed</p>";
				}
				$exten[0] = $newfileExtension;
			}
			if ($_FILES['map_file']['name'] != "") {
				$newfileName = $_FILES["map_file"]["name"];
				$newfileTmpName = $_FILES["map_file"]["tmp_name"];
				$newfileSize = $_FILES["map_file"]['size'];
				if ($newfileSize > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>Map Report File Size is too Large . Maximum file size allowed is 4MB.</p>";
				}
				// Allowed file extensions

				$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));

				// Check if the file extension is allowed
				if (!in_array($newfileExtension, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>Wrong file type of Map Report File Only PDF, JPG, JPEG, PNG files are allowed</p>";
				}
				$exten[1] = $newfileExtension;
			}
			if ($_FILES['ts_file']['name'] != "") {
				$newfileName = $_FILES["ts_file"]["name"];
				$newfileTmpName = $_FILES["ts_file"]["tmp_name"];
				$newfileSize = $_FILES["ts_file"]['size'];
				if ($newfileSize > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>TECHINICAL SANCTION Report File Size is too Large . Maximum file size allowed is 4MB.</p>";
				}
				// Allowed file extensions

				$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));

				// Check if the file extension is allowed
				if (!in_array($newfileExtension, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>Wrong file type of TECHINICAL SANCTION Report File Only PDF, JPG, JPEG, PNG files are allowed</p>";
				}
				$exten[2] = $newfileExtension;
			}

			if ($_FILES['estiamate_file']['name'] != "") {
				$newfileName = $_FILES["estiamate_file"]["name"];
				$newfileTmpName = $_FILES["estiamate_file"]["tmp_name"];
				$newfileSize = $_FILES["estiamate_file"]['size'];
				if ($newfileSize > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>ESTIAMTE Report File Size is too Large . Maximum file size allowed is 4MB.</p>";
				}
				// Allowed file extensions

				$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));

				// Check if the file extension is allowed
				if (!in_array($newfileExtension, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>Wrong file type of ESTIAMTE Report File Only PDF, JPG, JPEG, PNG files are allowed</p>";
				}
				$exten[3] = $newfileExtension;
			}

			if ($_FILES['invoice_from_architect_file']['name'] != "") {
				$newfileName = $_FILES["invoice_from_architect_file"]["name"];
				$newfileTmpName = $_FILES["invoice_from_architect_file"]["tmp_name"];
				$newfileSize = $_FILES["invoice_from_architect_file"]['size'];
				if ($newfileSize > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>INVOICE FROM ARCHITECT Report File Size is too Large . Maximum file size allowed is 4MB.</p>";
				}
				// Allowed file extensions

				$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));

				// Check if the file extension is allowed
				if (!in_array($newfileExtension, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>Wrong file type of INVOICE FROM ARCHITECT Report File Only PDF, JPG, JPEG, PNG files are allowed</p>";
				}
				$exten[4] = $newfileExtension;
			}
			if ($errormsg == "") {
				// naming of files
				$soiltesting = $filepath . "soil_testiong." . $exten[0];
				$map = $filepath . "map_file." . $exten[1];
				$tsfile = $filepath . "ts_file." . $exten[2];
				$estimatefile = $filepath . "estiamate_file." . $exten[3];
				$invoice = $filepath . "invoice_from_architect_file." . $exten[4];



				$sql = 'insert into invoice_survey (`project_id` ,`land_type` ,`date_of_visit` ,`date_of_land_avaible` ,`date_of_land_work_start` ,`target_date_of_completion` ,`soil_testing_date` ,`architect` ,`structural_architect` ,`date_of_allotment` ,`date_of_order_handover` ,`date_of_site_visit` ,`date_of_map_sub` ,`date_of_ts_sub` ,
						`date_of_estiamat_sub` ,`date_of_invoice_architect_sub` ,`status` ,`created_by` ,`creation_time` ) 
					values 
					("' . $_POST['project_name'] . '", "' . $_POST['land_type'] . '", "' . $_POST['date_of_visit'] . '","' . $_POST['date_of_land_avaible'] . '", 
					 "' . $_POST['date_of_land_work_start'] . '", "' . $_POST['target_date_of_completion'] . '", "' . $_POST['soil_testing_date'] . '",  
					 "' . $_POST['architectid'] . '", "' . $_POST['structural_architectid'] . '", "' . $_POST['date_of_allotment'] . '", "' . $_POST['date_of_order_handover'] . '", "' . $_POST['date_of_site_visit'] . '", "' . $_POST['date_of_map_sub'] . '","' . $_POST['date_of_ts_sub'] . '", "' . $_POST['date_of_estiamat_sub'] . '", "' . $_POST['date_of_invoice_architect_sub'] . '", "0","' . $_SESSION['username'] . '","' . date("Y-m-d H:i:s") . '")';

				execute_query($sql);

				if (mysqli_error($db)) {
					$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
				}
				if ($msg == '') {
					$insertid = mysqli_insert_id($db);
					echo $insertid;


					//imgae file upload 

					if (move_uploaded_file($_FILES['soil_testiong_report_file']['tmp_name'], $soiltesting) && move_uploaded_file($_FILES['map_file']['tmp_name'], $map) && move_uploaded_file($_FILES['ts_file']['tmp_name'], $tsfile) && move_uploaded_file($_FILES['estiamate_file']['tmp_name'], $estimatefile) && move_uploaded_file($_FILES['invoice_from_architect_file']['tmp_name'], $invoice)) {


						//updating file name in the database
						$updateimgname = "UPDATE `invoice_survey` SET 
							`soil_testiong_report_file`='{$soiltesting}',
							`map_file`='{$map}',
							`ts_file`='$tsfile',
							`estiamate_file`='{$estimatefile}',
							`invoice_from_architect_file`='$invoice' WHERE sno={$insertid}";

						$updateres = mysqli_query($db, $updateimgname);
						if ($updateres) {
							$msg .= '<p class="alert alert-success">Data save</p>';
						}
					} else {
						$msg .= '<p class="alert alert-danger">Could not save files</p>';
					}
				}

			}
		}

	} else {

		$sql = 'update invoice_survey set

		   `project_id` ="' . $_POST['project_name'] . '",
		  `land_type` ="' . $_POST['land_type'] . '",
		  `date_of_visit` ="' . $_POST['date_of_visit'] . '",
		  `date_of_land_avaible` ="' . $_POST['date_of_land_avaible'] . '",
		  `date_of_land_work_start` ="' . $_POST['date_of_land_work_start'] . '",
		  `target_date_of_completion` ="' . $_POST['target_date_of_completion'] . '",
		  `soil_testing_date` ="' . $_POST['soil_testing_date'] . '",
		  `soil_testiong_report_file` ="' . $_POST['soil_testiong_report_file'] . '",
		  `architect` ="' . $_POST['architect'] . '",
		  `structural_architect` ="' . $_POST['structural_architect'] . '",
		  `date_of_allotment` ="' . $_POST['date_of_allotment'] . '",
		  `date_of_order_handover` ="' . $_POST['date_of_order_handover'] . '",
		  `date_of_site_visit` ="' . $_POST['date_of_site_visit'] . '",
		  `map_file` ="' . $_POST['map_file'] . '",
		  `date_of_map_sub` ="' . $_POST['date_of_map_sub'] . '",
		  `ts_file` ="' . $_POST['ts_file'] . '",
		  `date_of_ts_sub` ="' . $_POST['date_of_ts_sub'] . '",
		  `estiamate_file` ="' . $_POST['estiamate_file'] . '",
		  `date_of_estiamat_sub` ="' . $_POST['date_of_estiamat_sub'] . '",
		  `invoice_from_architect_file` ="' . $_POST['invoice_from_architect_file'] . '",
		  `date_of_invoice_architect_sub` ="' . $_POST['date_of_invoice_architect_sub'] . '",
			
			
			where sno="' . $_POST['edit_sno'] . '"';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		}
		if ($msg == '') {
			$msg .= '<p class="text text-success">Data Update</p>';

		}
	}
} else {
	$_POST['division_id'] = '';
	$_POST['project_name'] = '';
	$_POST['land_type'] = '';
	$_POST['date_of_visit'] = date("Y-m-d");
	$_POST['date_of_land_avaible'] = date("Y-m-d");
	$_POST['date_of_land_work_start'] = date("Y-m-d");
	$_POST['target_date_of_completion'] = date("Y-m-d");
	$_POST['soil_testing_date'] = date("Y-m-d");
	$_POST['soil_testiong_report_file'] = '';
	$_POST['architect'] = '';
	$_POST['structural_architect'] = '';
	$_POST['date_of_allotment'] = date("Y-m-d");
	$_POST['date_of_order_handover'] = date("Y-m-d");
	$_POST['date_of_site_visit'] = date("Y-m-d");
	$_POST['map_file'] = '';
	$_POST['date_of_map_sub'] = date("Y-m-d");
	$_POST['ts_file'] = '';
	$_POST['date_of_ts_sub'] = date("Y-m-d");
	$_POST['estiamate_file'] = '';
	$_POST['date_of_estiamat_sub'] = date("Y-m-d");
	$_POST['invoice_from_architect_file'] = '';
	$_POST['date_of_invoice_architect_sub'] = date("Y-m-d");
	$_POST['edit_sno'] = '';
}

?>


<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
	
	<div class="card">
	<div class="card-header ml-auto no-print">
		<a href="land_survey_report.php" class="text-right" target=""><u><i class="icon fas fa-plus" aria-hidden="true"></i>Land Survey Report</u></a>
	</div>
		<div class="card-header ">
			<?php echo $msg;
			echo "<br>";
			echo $errormsg;
			?>
			<h4 class="card-title text-center"></h4>
		</div>
		
		<div class="card-body">
			<div class="row">
				<input type="hidden" name="unit_name" id="unit_name" class="form-control" placeholder=" "
					value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
				<input type="hidden" name="division_id" id="division_id" class="form-control" placeholder=" "
					value="<?php echo $_POST['division_id']; ?>" readonly tabindex="<?php echo $tab++; ?>">
				<div class="col-md-3 ">
					<div class="form-group">
						<label>विभाग</label><br>
						<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>"
							onChange="fill_district(this.value), fill_sub_department(this.value)">
							<option value="">--- Select ---</option>
							<?php
							if (!empty($_SESSION['divisions'])) {
								$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in (' . implode(",", $_SESSION['divisions']) . ') group by department_id) ';
							} elseif (!empty($_SESSION['department'])) {
								$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in (' . implode(",", $_SESSION['department']) . ') group by department_id) ';
							}
							// echo $query;
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
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label>उप विभाग</label>
						<select class="form-control" name="sub_department_id" id="sub_department_id"
							value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>">
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label>आच्छादित जनपद</label>
						<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>"
							onChange="fill_project(this.value)">
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label>परियोजना का नाम</label><br>
						<select class="form-control" name="project_name" id="project_name"
							tabindex="<?php echo $tab++; ?>" onChange="fill_architect_details(this.value)">
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h5>Land Information </h5>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Type Of land</label>
								<select class="form-control" name="land_type" id="land_type">
									<option value="Select District-">--Select--</option>
									<option value="Normal">Normal </option>
									<option value="Low Leve">Low Level Land</option>
									<option value="Normal">Submerged</option>
									<option value="Normal">Disputed</option>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Date Of Visit</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_visit', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_visit']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Date of Land Avaible </label>

								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_land_avaible', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_land_avaible']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Date Of land Work Start</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_land_work_start', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_land_work_start']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Target Date of Completion </label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('target_date_of_completion', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['target_date_of_completion']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<h5>Soil Testing</h5>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Soil Testing Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('soil_testing_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['soil_testing_date']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Attach Report</label>
								<input class="form-control" type="file" required name="soil_testiong_report_file"
									id="soil_testiong_report_file">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h5>Architect Information</h5>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Architect</label>
								<input class="form-control" type="text" name="architect" id="architect" readonly>
								<input class="form-control" type="hidden" name="architectid" id="architectid">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Structural Architect</label>
								<input class="form-control" type="text" name="structural_architect"
									id="structural_architect" readonly>
								<input class="form-control" type="hidden" name="structural_architectid"
									id="structural_architectid">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Date of Allotment</label>
								<input type="date" name="date_of_allotment" id="date_of_allotment" class="form-control"
									readonly>
								<!-- <script  type="text/javascript" language="javascript">
											document.writeln(DateInput('date_of_allotment', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_allotment']; ?>', <?php echo $tab;
											   $tab += 4; ?>));
										</script> -->
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Date of Order Handover</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_order_handover', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_order_handover']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Date of Site Visit</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_site_visit', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_site_visit']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Attach Map</label>
								<input class="form-control" type="file" required name="map_file" id="map_file">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Map Submission Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_map_sub', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_map_sub']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Attach Techinical Sanction (TS)</label>
								<input class="form-control" type="file" required name="ts_file" id="ts_file">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>TS Submission Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_ts_sub', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_ts_sub']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Attach Estiamte</label>
								<input class="form-control" type="file" required name="estiamate_file"
									id="estiamate_file">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Estiamte Submission Date</label>>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_estiamat_sub', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_estiamat_sub']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Invoice from Architect</label>
								<input class="form-control" type="file" required name="invoice_from_architect_file"
									id="invoice_from_architect_file">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label> Invoice Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('date_of_invoice_architect_sub', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_invoice_architect_sub']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
					</div>
					<div class="col-md-12 pr-1" align="center">
						<div class="form-group">
							<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</form>


<?php
page_footer_start();
?>


<script>
	$('select[multiple]').multiselect();

	var actionUrl = 'scripts/ajax.php';

	function fill_sub_department(val, selected) {
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
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.sub_department_hindi + '</option>';

				});
				$("#sub_department_id").html(txt);
			}
		});
	}

	function fill_district(val, selected) {
		var data = { "term": "b", "id": "dist", "val": val };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.district_name + '</option>';

				});
				$("#district").html(txt);
			}
		});
	}

	function fill_project(val, selected) {
		var data = { "term": "b", "id": "proj", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.project_name_hindi + '</option>';

				});
				$("#project_name").html(txt);
			}
		});
	}

	function fill_architect_details(val) {
		var data = { "term": "b", "id": "fill_architect", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$("#architect").val(data.architect_name);
				$("#structural_architect").val(data.structural_architect_name);
				$("#date_of_allotment").val(data.alloteddate);
				$("#architectid").val(data.architect_id);
				$("#structural_architectid").val(data.structural_architect_id);

			}
		});
	}

	<?php
	if (isset($_GET['edit_sno'])) {
		?>
		$(document).ready(function () {
			fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
			fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
			fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['project_name']; ?>);

		});
		<?php
	}
	?>

</script>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

<?php
page_footer_end();
?>