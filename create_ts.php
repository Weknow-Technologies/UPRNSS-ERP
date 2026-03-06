<?php
include ("scripts/settings.php");
$msg = '';
$tab = 1;

$errormsg = "";
$exten = array();//Array for extension storing 
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (isset($_POST['submit'])) {
	if ($_POST['edit_sno'] == '') {

		$checkifalready = "SELECT * FROM technical_sanction WHERE project_id='{$_POST['project_name']}'";
		$checkifalreadyres = execute_query($checkifalready);
		if (mysqli_num_rows($checkifalreadyres) > 0) {
			$msg .= "<p class='alert alert-danger'>This Project is Already Submitted</p>";
		} else {
			//testing files

			//creating file hirarkey 
			//file_upload => land_survey=> year=> district sno=> uprnss_project_temp sno=> invoice_survey sno => all the files are here

			$filepath = "file_upload/ts_allot/" . $_POST['district'] . "/" . $_POST['project_name'] . "/";
			echo $filepath . "<br>";
			if (!file_exists($filepath)) {
				// Create the folder
				if (mkdir($filepath, 0777, true)) {
					echo 'Folder created successfully.<br>';
				}
			}

			if ($_FILES['ts_file']['name'] != "") {
				$newfileName = $_FILES["ts_file"]["name"];
				$newfileTmpName = $_FILES["ts_file"]["tmp_name"];
				$newfileSize = $_FILES["ts_file"]['size'];
				if ($newfileSize > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>Technical Sanction Report File Size is too Large . Maximum file size allowed is 4MB.</p>";
				}
				// Allowed file extensions

				$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
				// Check if the file extension is allowed
				if (!in_array($newfileExtension, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>Wrong file type of technical sanction Report File Only PDF, JPG, JPEG, PNG files are allowed</p>";
				}
				$exten[0] = $newfileExtension;
			}

			if ($errormsg == "") {
				// naming of files
				$ts_file = $filepath . "ts_file." . $exten[0];


				$sql = ' insert into technical_sanction (division_id, district_id, project_id, ts_date, ts_no, created_by, creation_time, status)
                
                values ("' . $_POST['division_id'] . '","' . $_POST['district'] . '", "' . $_POST['project_name'] . '", "' . $_POST['ts_date'] . '", "' . $_POST['ts_no'] . '", "' . $_SESSION['usersno'] . '", "' . date("Y-m-d H:i:s") . '", "0")';

				execute_query($sql);
				if (mysqli_error($db)) {
					$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
				}
				if ($msg == '') {
					$insertid = mysqli_insert_id($db);
					echo $insertid;


					//imgae file upload 

					if (move_uploaded_file($_FILES['ts_file']['tmp_name'], $ts_file)) {

						//updating file name in the database
						$updateimgname = "UPDATE `technical_sanction` SET 
                        `ts_file`='{$ts_file}' WHERE sno={$insertid}";

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
		$sql = 'update technical_sanction set
        
        division_id = "' . $_POST['division_id'] . '",  
        district_id = "' . $_POST['district'] . '",  
        project_id = "' . $_POST['project_name'] . '",
        ts_date = "' . $_POST['ts_date'] . '",
        ts_no = "' . $_POST['ts_no'] . '",
        ts_file ="' . $_POST['ts_file'] . '",

        `edited_by` = "' . $_SESSION['usersno'] . '", 
        `edition_time` = "' . date("Y-m-d H:i:s") . '" 
        
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
	$_POST['district_id'] = '';
	$_POST['project_id'] = '';
	$_POST['ts_date'] = date("Y-m-d");
	$_POST['ts_no'] = '';
	$_POST['ts_file'] = '';
}

if (isset($_GET['edit_sno'])) {
	$sql = 'select * from technical_sanction where sno="' . $_GET['edit_sno'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));

	$_POST['division_id'] = $data['division_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['project_name'] = $data['project_id'];
	$_POST['ts_date'] = $data['ts_date'];
	$_POST['ts_no'] = $data['ts_no'];
	$_POST['ts_file'] = $data['ts_file'];


	$_POST['edit_sno'] = $data['sno'];
}


if (isset($_GET['id'])) {

	$sql = 'select * from uprnss_project_temp where sno="' . $_GET['id'] . '"';
	// echo $sql;
	$data = mysqli_fetch_assoc(execute_query($sql));

	$_POST['division_id'] = $data['division_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['project_name'] = $data['sno'];

}

page_header_start();
page_header_end();
page_sidebar();

?>

<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div class="card">
		<div class="card-header ">
			<?php echo $msg;
			echo "<br>";
			echo $errormsg;
			?>
			<h4 class="card-title text-center"></h4>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 ">
					<div class="form-group">
						<label>प्रखण्ड का नाम </label><br>
						<select class="form-control" name="division_id" id="division_id"
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
						<label> जनपदका नाम </label>
						<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" readonly>
							<option value="">--- Select ---</option>
							<?php

							$query = '(SELECT * from uprnss_district ) ';
							echo $query;
							$run = mysqli_query($db, $query);
							while ($data = mysqli_fetch_array($run)) {
								echo '<option value="' . $data['sno'] . '" ';
								if (isset($_POST['district'])) {
									if ($_POST['district'] == $data['sno']) {
										echo ' selected="Selected"';
									}
								}
								echo '>' . trim($data['district_name_hindi']) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-5">
					<div class="form-group">
						<label>परियोजना का नाम</label><br>
						<select class="form-control" name="project_name" id="project_name"
							tabindex="<?php echo $tab++; ?>" readonly>
							<option value="">--- Select ---</option>
							<?php

							$query = '(SELECT * FROM `uprnss_project_temp` WHERE status!="5" and status!="2") ';
							echo $query;
							$run = mysqli_query($db, $query);
							while ($data = mysqli_fetch_array($run)) {
								echo '<option value="' . $data['sno'] . '" ';
								if (isset($_POST['project_name'])) {
									if ($_POST['project_name'] == $data['sno']) {
										echo ' selected="Selected"';
									}
								}
								echo '>' . trim($data['project_name_hindi']) . '</option>';
							}
							?>
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
					<h4 class="card-title"></h4>
					<!-- <?php echo $msg; ?> -->
				</div>
				<div class="card-body">
					<div class="row">
						<tr>
							<div class="col-md-4">
								<div class="form-group">
									<label>Technical Sanction Date</label>
									<script type="text/javascript" language="javascript">
										document.writeln(DateInput('ts_date', 'user_form', true, 'YYYY-MM-DD', '2023-12-01', 1));
									</script>
								</div>
							</div>
						</tr>
						<tr>
							<div class="col-md-4">
								<div class="form-group">
									<label>Technical Sanction Number</label>
									<input type="text" name="ts_no" id="ts_no" class="form-control" placeholder="">
								</div>
							</div>
						</tr>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Technical Sanction Doc.</label>
								<input type="file" name="ts_file" id="ts_file" value="" class="form-control"
									placeholder="">
							</div>
						</div>
					</div>

					<div class="col-md-12 d-flex justify-content-center align-items-center">
						<button type="submit" name="submit" class="btn btn-info btn-fill pull-right">Submit</button>
						<button type="edit_sno" name="edit_sno"
								class="btn btn-edit btn-fill pull-right">Edit
							</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>


<script>
	function add_rows() {
		var id = parseFloat($("#add_rows_id").val());
		if (!id) {
			id = 0;
		}
		for (var i = 0; i <= id; i++) {
			if ($("#deliverable_" + i).val() == '' || $("#complitation_days_" + i).val() == '') {
				alert("पंक्ति संख्या " + i + " खाली है");
				$("#deliverable_" + i).focus();
				return;
			}
		}
		id = id + 1;


		$("#add_button").remove();
		var txt = id + '<div class="col-md-12" id="add_rows_length"><div class="row"><div class="col-md-3"><div class="form-group"><label> DELiverable Title</label><input type="text" name="deliverable_' + id + '" id="deliverable_' + id + '" class="form-control" placeholder="" value=""></input></div></div><div class="col-md-3"><div class="form-group"><label>Deliverable Description </label><input type="text" name="description_' + id + '" id="description_' + id + '" class="form-control" placeholder=" " value=""></div></div><div class="col-md-4"><div class="form-group"><label>Completion form the detail mou (in Days)</label><input type="text" name="complitation_days_' + id + '" id="complitation_days_' + id + '" class="form-control" placeholder=" " value=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button><input type="hidden" name="add_rows_id" id="add_rows_id" value="1"></div></div></div>';
		$("#test").append(txt);
		$("#add_rows_id").val(id);

	}			
</script>


<?php
page_footer_start();
?>


<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
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

<?php
page_footer_end();
?>