<?php
include ("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();

if (isset($_POST['submit'])) {
	if (empty($_POST['edit_sno'])) {
		// status 5= deleted
		$sql = "SELECT * FROM `inovoice_architect_allotment` WHERE project_id = '" . $_POST['project_name'] . "' and status!='5'";
		$result = execute_query($sql);
		$rowcount = mysqli_num_rows($result);
		if ($rowcount > 0) {
			$msg = '<div class="alert alert-danger">Alredy Allot !</div>';
		} else {
			$date = date("Y-m-d");
			$time = strtotime($date);
			$month = date("m", $time);
			$year = date("Y", $time);
			if ($month >= 1 && $month <= 3) {
				$year = $year - 1;
			}

			$sql = 'select * from inovoice_architect_allotment where financial_year="' . $year . '" order by abs(invoice_no) desc limit 1';
			if ('alloted_type' == '3'){
				$result_invoice_no = execute_query($sql);
				if (mysqli_num_rows($result_invoice_no) == 0) {
					$invoice_no = 1;
				} else {
					$row_invoice_no = mysqli_fetch_assoc($result_invoice_no);
					$invoice_no = (float) $row_invoice_no['invoice_no'] + 6;
				}
			} else {
				$result_invoice_no = execute_query($sql);
				if (mysqli_num_rows($result_invoice_no) == 0) {
					$invoice_no = 1;
				} else {
					$row_invoice_no = mysqli_fetch_assoc($result_invoice_no);
					$invoice_no = (float) $row_invoice_no['invoice_no'] + 5;
				}
			} 

			$sql = ' insert into inovoice_architect_allotment (division_id, district_id, project_id, architect_id, structural_architect_id, alloted_type,  allotment_date, financial_year, invoice_no, created_by, creation_time, status)
			 
			values ("' . $_POST['division_id'] . '","' . $_POST['district'] . '", "' . $_POST['project_name'] . '", "' . $_POST['architect'] . '", "' . $_POST['structural_architect'] . '", "' . $_POST['alloted_type'] . '" , "' . date("Y-m-d") . '",
			"' . $year . '" , "' . $invoice_no . '" , "' . $_SESSION['usersno'] . '", "' . date("Y-m-d H:i:s") . '", "0")';

			execute_query($sql);
			if (mysqli_error($db)) {
				$msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
			} else {
				$sql = 'update uprnss_project_temp set 
				architect_id = "' . $_POST['architect'] . '",
				structural_architect_id = "' . $_POST['structural_architect'] . '"
				where sno="' . $_POST['project_name'] . '"';
				// echo $sql;
				execute_query($sql);

				$msg .= '<p class="alert alert-success">Data Saved</p>';

			}
		}
	} else {
		$sql = 'update inovoice_architect_allotment set
		
		division_id = "' . $_POST['division_id'] . '",  
		district_id = "' . $_POST['district'] . '",  
		project_id = "' . $_POST['project_name'] . '",
		architect_id = "' . $_POST['architect'] . '", 
		structural_architect_id = "' . $_POST['structural_architect'] . '", 
		alloted_type = "' . $_POST['alloted_type'] . '", 
		 
		edited_by = "' . $_SESSION['usersno'] . '", 
		edition_time = "' . date("Y-m-d H:i:s") . '" 
		
		where sno="' . $_POST['edit_sno'] . '"';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		} else {
			$sql = 'update uprnss_project_temp  set 
					architect_id = "' . $_POST['architect'] . '",
					structural_architect_id = "' . $_POST['structural_architect'] . '"   
					where sno="' . $_POST['project_name'] . '"';
			// echo $sql;
			execute_query($sql);
			$msg .= '<p class="alert alert-success">Data Updates</p>';

		}
	}
} else {

	$_POST['district'] = "";
	$_POST['project_id'] = "";
	$_POST['architect'] = "";
	$_POST['structural_architect'] = "";
	$_POST['alloted_type'] = "";
	$_POST['allotment_date'] = "";
	$_POST['invoice_no'] = "";
	$_POST['financial_year'] = "";
	$_POST['division_id'] = "";
	$_POST['edit_sno'] = '';
}

if (isset($_GET['edit_sno'])) {
	$sql = 'select * from inovoice_architect_allotment where sno="' . $_GET['edit_sno'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));

	$_POST['division_id'] = $data['division_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['project_name'] = $data['project_id'];
	$_POST['architect'] = $data['architect_id'];
	$_POST['structural_architect'] = $data['structural_architect_id'];
	$_POST['alloted_type'] = $data['alloted_type'];


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

?>

<style>
    /* General form styling */
    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        border-radius: 4px;
        padding: 10px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: #5cb85c;
        box-shadow: 0 0 8px rgba(92, 184, 92, 0.2);
    }

    /* Card styling */
    .card {
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #5cb85c;
        color: white;
        padding: 10px 15px;
        border-bottom: 1px solid #e3e3e3;
        border-radius: 4px 4px 0 0;
    }

    .card-body {
        padding: 15px;
    }

    /* Button styling */
    .btn-success {
        background-color: #5cb85c;
        border-color: #5cb85c;
        padding: 10px 20px;
        font-size: 16px;
    }

    .btn-success:hover {
        background-color: #4cae4c;
        border-color: #4cae4c;
    }

    /* Alert styling */
    .alert {
        margin-top: 15px;
    }

    /* Responsive styling */
    @media (max-width: 768px) {
        .col-md-4, .col-md-3, .col-md-5, .col-md-8, .col-md-12 {
            padding: 0 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
    }

    /* Custom select styling */
    select.form-control {
        height: auto;
        padding-right: 30px;
    }

    /* Flexbox alignment for buttons */
    .d-flex {
        display: flex;
        align-items: center;
    }

    .justify-content-center {
        justify-content: center;
    }

    .align-items-center {
        align-items: center;
    }
</style>



<form id="form" name="form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div class="card">
		<?php if (isset($_POST['submit'])) {
			echo '<div class="card-header ml-auto no-print">
			<a href="architect_alloted_report.php" class="text-right" target=""><u><i class="icon fas fa-file-alt" aria-hidden="true"></i> Alloted Report</u></a></div>';
		}
		echo $msg; ?>
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
						<label> जनपद का नाम </label>
						<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>"
							readonly>
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

							$query = '(SELECT * FROM `uprnss_project_temp` WHERE status!="5") ';
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
			<div class="card strpied-tabled-with-hover">
				<div class="card-header ">
					<h4 class="card-title">Allot Architect</h4>
				</div>
				<div class="card-body">
					<div class="row">

						<div class="col-md-4">
							<div class="form-group">
								<label>Alloted Type</label>
								<select name="alloted_type" id="alloted_type" class="form-control"
									onchange="onchangetype()" value="<?php if (isset($row['alloted_type'])) {
										echo $row['alloted_type'];
									} ?>">
									<option value="">--- Select ---</option>
									<option value="1"<?php echo ($_POST['alloted_type'] == 1 ? 'selected' : ''); ?> >Architect</option>
									<option value="2" <?php echo ($_POST['alloted_type'] == 2 ? 'selected' : ''); ?> >Structural Architect</option>
									<option value="3" <?php echo ($_POST['alloted_type'] == 3 ? 'selected' : ''); ?> >Both</option>
									<option value="4" <?php echo ($_POST['alloted_type'] == 4 ? 'selected' : ''); ?> >Not Applicable</option>
								</select>
							</div>
						</div>

						<div id="type1" style="display:none;">
							<div class="col-md-8">
								<div class="form-group">
									<label>Architect</label>
									<select class="form-control" name="architect" id="architect"
										tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$var_date = date('Y-m-d'); // Correct date format for MySQL
										$query = "SELECT sno, full_name_english FROM uprnss_architect 
                          				WHERE `uprnss_architect`.`type` = 1 AND valid_to <= '$var_date'";
										$run = mysqli_query($db, $query);
										while ($data = mysqli_fetch_array($run)) {
											echo '<option value="' . $data['sno'] . '" ';
											if (isset($_POST['architect']) && $_POST['architect'] == $data['sno']) {
												echo ' selected="selected"';
											}
											echo '>' . trim($data['full_name_english']) . '</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>

						<div id="type2" style="display:none;">
							<div class="col-md-8">
								<div class="form-group">
									<label>Structural Architect</label>
									<select class="form-control" name="structural_architect" id="structural_architect"
										tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$var_date = date('Y-m-d'); // Correct date format for MySQL
										$query = "SELECT sno, full_name_english FROM uprnss_architect WHERE `uprnss_architect`.`type` = 2 AND valid_to >= '$var_date'";
										$run = mysqli_query($db, $query);
										while ($data = mysqli_fetch_array($run)) {
											echo '<option value="' . $data['sno'] . '" ';
											if (isset($_POST['structural_architect']) && $_POST['structural_architect'] == $data['sno']) {
												echo ' selected="selected"';
											}
											echo '>' . trim($data['full_name_english']) . '</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>

						<div id="type3" style="display:none;"></div>
						<div id="type4" style="display:none;"></div> <!-- Added missing type4 element -->
					</div>
					<div class="col-md-12 d-flex justify-content-center align-items-center">
						<button type="submit" name="submit" class="btn btn-success pull-right">Submit</button>
						<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
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
<!--  Charts Plugin -->
<script src="js/chartist.min.js"></script>

<script>
	function onchangetype() {
		var type = document.getElementById("alloted_type").value;

		// Hide all sections first
		document.getElementById("type1").style.display = "none";
		document.getElementById("type2").style.display = "none";
		document.getElementById("type3").style.display = "none";
		document.getElementById("type4").style.display = "none";

		// Show relevant sections based on the selected type
		if (type == "1") {
			document.getElementById("type1").style.display = "block";
		} else if (type == "2") {
			document.getElementById("type2").style.display = "block";
		} else if (type == "3") {
			document.getElementById("type1").style.display = "block";
			document.getElementById("type2").style.display = "block";
		} else if (type == "4") {
			
		}
	}
</script>

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