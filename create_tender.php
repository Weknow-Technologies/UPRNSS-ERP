<?php
include ("scripts/settings.php");
$msg = '';
$tab = 1;

$errormsg = "";
$exten = array();//Array for extension storing 
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (isset($_POST['submit'])) {
    $task_names = $_POST['task_name'];
    $completion_days = $_POST['completion_days'];
    $weightages = $_POST['weightage'];
    $project_id = $_POST['project_name'];
    
    if (empty($_POST['edit_sno'])) {
        $checkifalready = "SELECT * FROM `tender_allotment` WHERE project_id = '" . $_POST['project_name'] . "' and status!='5'";
        $checkifalreadyres = execute_query($checkifalready);
        if (mysqli_num_rows($checkifalreadyres) > 0) {
            $msg .= "<p class='alert alert-danger'>This Project is Already Submitted</p>";
        } else {
            $filepath = "tender_upload/tender_allot/" . $_POST['district'] . "/" . $_POST['project_name'] . "/";
            if (!file_exists($filepath)) {
                mkdir($filepath, 0777, true);
            }

            // File validation
            $errormsg = "";
            $attach_file_path = "";
            $attach_file1_path  = "";
			if ($_FILES['attach_file']['name'] != "") {
				$file = $_FILES['attach_file'];
				$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
				if ($file['size'] > 4 * 1024 * 1024) {
					$errormsg .= "<p class='alert alert-danger'>attach_file size too large, max 4MB allowed.</p>";
				}
				if (!in_array($ext, $allowedExtensions)) {
					$errormsg .= "<p class='alert alert-danger'>attach_file: Invalid file type.</p>";
				}
				$attach_file_path = $filepath . "attach_file_" . $project_id . "." . $ext;
			} else {
				$errormsg .= "<p class='alert alert-danger'>attach_file is required.</p>";
			}

            if ($_FILES['attach_file1']['name'] != "" && $_FILES['attach_file12']['name'] != "") {
                $errormsg .= "<p class='alert alert-danger'>Only one of attach_file1 or attach_file12 can be uploaded at a time.</p>";
            } elseif ($_FILES['attach_file1']['name'] != "") {
                $file = $_FILES["attach_file1"];
                $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                if ($file['size'] > 4 * 1024 * 1024) {
                    $errormsg .= "<p class='alert alert-danger'>File too large. Max 4MB allowed.</p>";
                }
                if (!in_array($ext, $allowedExtensions)) {
                    $errormsg .= "<p class='alert alert-danger'>Invalid file type.</p>";
                }
                $attach_file1_path  = $filepath . "attach_file1_" . $project_id . "." . $ext;
            } elseif ($_FILES['attach_file12']['name'] != "") {
                $file = $_FILES["attach_file12"];
                $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                if ($file['size'] > 4 * 1024 * 1024) {
                    $errormsg .= "<p class='alert alert-danger'>File too large. Max 4MB allowed.</p>";
                }
                if (!in_array($ext, $allowedExtensions)) {
                    $errormsg .= "<p class='alert alert-danger'>Invalid file type.</p>";
                }
                $attach_file1_path  = $filepath . "attach_file1_" . $project_id . "." . $ext;
            }

            if ($errormsg == "") {
                // Insert main tender_allotment row
                $sql = "INSERT INTO tender_allotment (division_id, district_id, project_id, tender_status, tender_title, platform, tender_no, tender_fee,tender_cost, publish_date, end_date, technical_open_date, financial_open_date, vendor_l1, vendor_l2, vendor_l3, mou_date, project_awarded_to, emd_type, bank_name, issue_date, valid_upto, document_no, utr_no, deposited_date, deposited_bank_name, ifsc, created_by, creation_time, status)
                VALUES (
                    '{$_POST['division_id']}',
                    '{$_POST['district']}',
                    '{$_POST['project_name']}',
                    '{$_POST['tender_status']}',
                    '{$_POST['tender_title']}',
                    '{$_POST['platform']}',
                    '{$_POST['tender_no']}',
                    '{$_POST['tender_fee']}',
                    '{$_POST['tender_cost']}',
                    '{$_POST['publish_date']}',
                    '{$_POST['end_date']}',
                    '{$_POST['technical_open_date']}',
                    '{$_POST['financial_open_date']}',
                    '{$_POST['vendor_l1']}',
                    '{$_POST['vendor_l2']}',
                    '{$_POST['vendor_l3']}',
                    '{$_POST['mou_date']}',
                    '{$_POST['project_awarded_to']}',
                    '{$_POST['emd_type']}',
                    '{$_POST['bank_name']}',
                    '{$_POST['issue_date']}',
                    '{$_POST['valid_upto']}',
                    '{$_POST['document_no']}',
                    '{$_POST['utr_no']}',
                    '{$_POST['deposited_date']}',
                    '{$_POST['deposited_bank_name']}',
                    '{$_POST['ifsc']}',
                    '{$_SESSION['usersno']}',
                    '" . date("Y-m-d H:i:s") . "',
                    '0'
                )";
                execute_query($sql);

                if (!mysqli_error($db)) {
                    $insertid = mysqli_insert_id($db);

                    // Save tasks
                    for ($i = 0; $i < count($task_names); $i++) {
                        $task_name = mysqli_real_escape_string($db, $task_names[$i]);
                        $completion_day = (int)$completion_days[$i];
                        $weightage = (float)$weightages[$i];
                        $sql_task = "INSERT INTO tender_tasks (allotment_id, project_id, task_name, completion_days, weightage) VALUES
                            ('$insertid', '$project_id', '$task_name', '$completion_day', '$weightage')";
                        execute_query($sql_task);
                    }

                    // Move uploaded file
					 if (move_uploaded_file($_FILES['attach_file']['tmp_name'], $attach_file_path)) {
						$update = "UPDATE tender_allotment SET attach_file = '$attach_file_path' WHERE sno = $insertid";
						mysqli_query($db, $update);
					} else {
						$msg .= "<p class='alert alert-danger'>attach_file upload failed.</p>";
					}
					
                    if ($attach_file1_path  != "") {
                        if (move_uploaded_file($file["tmp_name"], $attach_file1_path )) {
                            $updateimgname = "UPDATE tender_allotment SET attach_file1 = '$attach_file1_path ' WHERE sno = $insertid";
                            mysqli_query($db, $updateimgname);
                            $msg .= "<p class='alert alert-success'>Tender saved with file.</p>";
                        } else {
                            $msg .= "<p class='alert alert-danger'>File upload failed.</p>";
                        }
                    } else {
                        $msg .= "<p class='alert alert-success'>Tender saved (No file uploaded).</p>";
                    }
                } else {
                    $msg .= '<p class="alert alert-danger">Error: ' . mysqli_error($db) . '</p>';
                }
            } else {
                $msg .= $errormsg;
            }
        }
    } else {
		$sql = 'update tender_allotment set
		
		division_id = "' . $_POST['division_id'] . '",  
		district_id = "' . $_POST['district'] . '",  
		project_id = "' . $_POST['project_name'] . '",
		tender_status = "' . $_POST['tender_status'] . '",
		platform = "' . $_POST['platform'] . '",
		tender_no = "' . $_POST['tender_no'] . '",
		tender_cost = "' . $_POST['tender_cost'] . '",
		tender_fee = "' . $_POST['tender_fee'] . '",
		publish_date = "' . $_POST['publish_date'] . '",
		end_date = "' . $_POST['end_date'] . '",
		attach_file = "' . $_POST['attach_file'] . '",
		technical_open_date = "' . $_POST['technical_open_date'] . '",
		financial_open_date = "' . $_POST['financial_open_date'] . '",
		vendor_l1 = "' . $_POST['vendor_l1'] . '",
		vendor_l2 = "' . $_POST['vendor_l2'] . '",
		vendor_l3 = "' . $_POST['vendor_l3'] . '",
		mou_date = "' . $_POST['mou_date'] . '",
		project_awarded_to = "' . $_POST['project_awarded_to'] . '",
		emd_type = "' . $_POST['emd_type'] . '",
		bank_name = "' . $_POST['bank_name'] . '",
		issue_date = "' . $_POST['issue_date'] . '",
		valid_upto = "' . $_POST['valid_upto'] . '",
		document_no = "' . $_POST['document_no'] . '",
		attach_file1 = "' . $_POST['attach_file1'] . '",
		utr_no = "' . $_POST['utr_no'] . '",
		deposited_date = "' . $_POST['deposited_date'] . '",
		deposited_bank_name = "' . $_POST['deposited_bank_name'] . '",
		ifsc = "' . $_POST['ifsc'] . '",
		
		
		edited_by = "' . $_SESSION['usersno'] . '", 
		edition_time = "' . date("Y-m-d H:i:s") . '" 
		
		where sno="' . $_POST['edit_sno'] . '"';
		execute_query($sql);
		if (mysqli_error($db)) {
			$msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
		}
		if ($msg == '') {
			$msg .= '<p class="text text-success">Data Update</p>';
			
			$edit_sno = $_POST['edit_sno'];
			// Delete old tasks
			execute_query("DELETE FROM tender_tasks WHERE allotment_id = '$edit_sno'");

			for ($i = 0; $i < count($task_names); $i++) {
				$task_name = mysqli_real_escape_string($db, $task_names[$i]);
				$completion_day = (int)$completion_days[$i];
				$weightage = (float)$weightages[$i];

				$sql_task = "INSERT INTO tender_tasks (allotment_id, project_id, task_name, completion_days, weightage) VALUES
                    ('$insertid', '$project_id', '$task_name', '$completion_day', '$weightage')";
				execute_query($sql_task);
				if (mysqli_error($db)) {
					$msg .= '<p class="alert alert-danger">Error updating task: ' . mysqli_error($db) . '</p>';
				}
			}
			

		}
	}
} else {

	$_POST['district'] = "";
	$_POST['project_id'] = "";
	$_POST['division_id'] = "";
	$_POST['platform'] = "";
	$_POST['tender_status'] = "";
	$_POST['tender_no'] = "";
	$_POST['tender_fee'] = "";
	$_POST['publish_date'] = date("Y-m-d");
	$_POST['end_date'] = date("Y-m-d");
	$_POST['attach_file'] = "";
	$_POST['technical_open_date'] = date("Y-m-d");
	$_POST['financial_open_date'] = date("Y-m-d");
	$_POST['vendor_l1'] = "";
	$_POST['vendor_l2'] = "";
	$_POST['vendor_l3'] = "";
	$_POST['mou_date'] = date("Y-m-d");
	$_POST['project_awarded_to'] = "";
	$_POST['emd_type'] = "";
	$_POST['bank_name'] = "";
	$_POST['issue_date'] = date("Y-m-d");
	$_POST['valid_upto'] = date("Y-m-d");
	$_POST['document_no'] = "";
	$_POST['attach_file1'] = "";
	$_POST['utr_no'] = "";
	$_POST['deposited_date'] = date("Y-m-d");
	$_POST['deposited_bank_name'] = "";
	$_POST['ifsc'] = "";

	$_POST['edit_sno'] = '';
}

if (isset($_GET['edit_sno'])) {
	$sql = 'select * from tender_allotment where sno="' . $_GET['edit_sno'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));

	$_POST['division_id'] = $data['division_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['project_name'] = $data['project_id'];
	$_POST['tender_status'] = $data['tender_status'];
	$_POST['platform'] = $data['platform'];
	$_POST['tender_no'] = $data['tender_no'];
	$_POST['tender_fee'] = $data['tender_fee'];
	$_POST['publish_date'] = $data['publish_date'];
	$_POST['end_date'] = $data['end_date'];
	$_POST['attach_file'] = $data['attach_file'];
	$_POST['technical_open_date'] = $data['technical_open_date'];
	$_POST['financial_open_date'] = $data['financial_open_date'];
	$_POST['vendor_l1'] = $data['vendor_l1'];
	$_POST['vendor_l2'] = $data['vendor_l2'];
	$_POST['vendor_l3'] = $data['vendor_l3'];
	$_POST['mou_date'] = $data['mou_date'];
	$_POST['project_awarded_to'] = $data['project_awarded_to'];
	$_POST['emd_type'] = $data['emd_type'];
	$_POST['bank_name'] = $data['bank_name'];
	$_POST['issue_date'] = $data['issue_date'];
	$_POST['valid_upto'] = $data['valid_upto'];
	$_POST['document_no'] = $data['document_no'];
	$_POST['attach_file1'] = $data['attach_file1'];
	$_POST['utr/ref_no'] = $data['utr_no'];
	$_POST['deposited_date'] = $data['deposited_date'];
	$_POST['deposited_bank_name'] = $data['deposited_bank_name'];
	$_POST['ifsc'] = $data['ifsc'];

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


<script>

	function onchangetype() {
		var type = document.getElementById("emd_type");

		if (type.value == "DD") {
			document.getElementById("type1").style.display = "block";
			document.getElementById("type2").style.display = "none";
		}
		else if (type.value == "FD") {
			document.getElementById("type1").style.display = "block";
			document.getElementById("type2").style.display = "none";
		}
		else if (type.value == "Check") {
			document.getElementById("type1").style.display = "block";
			document.getElementById("type2").style.display = "none";
		}
		else if (type.value == "RTGS") {
			document.getElementById("type2").style.display = "block";
			document.getElementById("type1").style.display = "none";
		}
		else if (type.value == "NEFT") {
			document.getElementById("type2").style.display = "block";
			document.getElementById("type1").style.display = "none";
		}
		else {
			document.getElementById("type1").style.display = "none";
			document.getElementById("type2").style.display = "none";
		}
	}

</script>


<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	<div class="card">
		<?php if(isset($_POST['submit'])){ 
			echo '<div class="card-header ml-auto no-print">
				<a href="tender_alloted_report.php" class="text-right" target=""><u><i class="icon fas fa-file-alt" aria-hidden="true"></i> Alloted Report</u></a></div>';
			}
				?>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 ">
							<div class="form-group">
								<label >प्रखण्ड का नाम </label><br>
								<select class="form-control" name="division_id" id="division_id" tabindex="<?php echo $tab++; ?>" readonly >
									<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from uprnss_division order by division_name ASC ) ';
									
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['s_no'].'" ';
										if(isset($_POST['division_id'])){
											if($_POST['division_id']==$data['s_no']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['division_name']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
					
						<div class="col-md-3">
							<div class="form-group">
								<label > जनपदका नाम </label>
								<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" readonly >
									<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from uprnss_district ) ';
									echo $query;
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['district'])){
											if($_POST['district']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['district_name_hindi']).'</option>';
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
			<div class="card">
				<div class="card-header">
					<h4 class="card-title"></h4>
					<?php echo $msg; ?>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Tender Status</label><br>
								<select class="form-control" name="tender_status" id="tender_status" tabindex="<?php echo $tab++; ?>">
								<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from master_tender_status) ';
									echo $query;
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['status'])){
											if($_POST['status']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['status']).'</option>';
									}
									?>
								</select>
							</div>
						</div>						
						
						<div class="col-md-3">
							<div class="form-group">
								<label>Tender Title</label>
								<input type="text" name="tender_title" id="tender_title" class="form-control"
									placeholder="">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>platform</label>
								<select name="platform" id="platform" class="form-control">
									<option value="Select">-select-</option>
									<option value="GEM">GEM</option>
									<option value="E-Tender">E-Tender</option>
								</select>
							</div>
						</div>
					
						<div class="col-md-3">
							<div class="form-group">
								<label>Tender No.</label>
								<input type="text" name="tender_no" id="tender_no" class="form-control" placeholder="">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Tender Fee</label>
								<input type="text" name="tender_fee" id="tender_fee" class="form-control" placeholder="">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Project Tender cost *</label>
								<input type="text" name="tender_cost" id="tender_cost" class="form-control" placeholder="" required>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Attach Tender doc.</label>
								<input type="file" name="attach_file" required id="attach_file" value="" class="form-control"
									placeholder="">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>MOU Date </label>
								<script type="text/javascript" language="javascript" name="mou_date" id="">
									document.writeln(DateInput('mou_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
								</script>
							</div>
						</div>
					
						<div class="col-md-3">
							<div class="form-group">
								<label>Publish Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('publish_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>End Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('end_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label> Technical tender oppening date</label>
								<script type="text/javascript" language="javascript" name="technical_open_date" id="">
									document.writeln(DateInput('technical_open_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Finanical tender oppening date </label>
								<script type="text/javascript" language="javascript" name="financial_open_date" id="">
									document.writeln(DateInput('financial_open_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
								</script>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label >Assign Vendor (L1)</label>
								<select class="form-control" name="vendor_l1" id="vendor_l1" tabindex="<?php echo $tab++; ?>" >
									<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from vendor order by firm_name ASC ) ';
									
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['firm_name'])){
											if($_POST['firm_name']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['firm_name']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >Assign Vendor (L2)</label>
								<select class="form-control" name="vendor_l2" id="vendor_l2" tabindex="<?php echo $tab++; ?>" >
									<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from vendor order by firm_name ASC ) ';
									
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['firm_name'])){
											if($_POST['firm_name']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['firm_name']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >Assign Vendor (L3)</label>
								<select class="form-control" name="vendor_l3" id="vendor_l3" tabindex="<?php echo $tab++; ?>" >
									<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from vendor order by firm_name ASC ) ';
									
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['firm_name'])){
											if($_POST['firm_name']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['firm_name']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Project Awarded TO</label>
								<select class="form-control" name="project_awarded_to" id="project_awarded_to" tabindex="<?php echo $tab++; ?>" >
									<option value="">--- Select ---</option>
									<?php
									
										$query = '(SELECT * from vendor order by firm_name ASC ) ';
									
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['firm_name'])){
											if($_POST['firm_name']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['firm_name']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						
						
					</div>
					<div class="row">
						
					</div>

					<div class="row">
						<div class="col-md-12">
							<h5>EMD Details</h5>
							<div class="col-md-4 pr-1">
								<div class="form-group">
									<label>EMD type :</label>
									<select name="emd_type" id="emd_type" class="form-control" onchange="onchangetype()"
										value="<?php if (isset($row['emd_type'])) {
											echo $row['emd_type'];
										} ?>">
										<option value="Select">Select-- </option>
										<option value="DD">DD</option>
										<option value="Check">Check</option>
										<option value="FD">FD</option>
										<option value="RTGS">RTGS</option>
										<option value="NEFT">NEFT</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div id="type1" style="display:none;">
						<div class="row">
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Issuer Bank Name</label>
									<input type="text" name="bank_name" id="bank_name" class="form-control"
										placeholder=" " value="">
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Issue Date</label>
									<script type="text/javascript" language="javascript">
										document.writeln(DateInput('issue_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
									</script>
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
								<label>Valid Upto</label>
									<script type="text/javascript" language="javascript">
										document.writeln(DateInput('valid_upto', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
									</script>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label>Document No:</label>
									<input type="text" name="document_no" id="document_no" class="form-control"
										placeholder="" value="">
									</input>
								</div>
							</div>
							<div class="col-md-4 ">
								<div class="form-group">
									<label>Attach File</label>
									<input type="file" name="attach_file1" id="attach_file1" class="form-control"
										placeholder="Please Select File" value="">
									</input>
								</div>
							</div>
						</div>
					</div>
					<div id="type2" style="display:none;">
						<div class="row">
							<div class="col-md-4 ">
								<div class="form-group">
									<label>UTR/Reference No:</label>
									<input type="text" name="utr_no" id="utr_no" class="form-control" placeholder=" "
										value="">
									</input>
								</div>
							</div>
							<div class="col-md-4 ">
								<div class="form-group">
									<label>Deposited Date</label>
									<script type="text/javascript" language="javascript">
										document.writeln(DateInput('deposited_date', 'user_form', true, 'YYYY-MM-DD', '2024-06-01', 1));
									</script>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Deposited Bank Name</label>
									<input type="text" name="deposited_bank_name" id="deposited_bank_name"
										class="form-control" placeholder=" " value="">
									</input>
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>IFSC</label>
									<input type="text" name="ifsc" id="ifsc" class="form-control" placeholder=" "
										value="">
									</input>
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Attach </label>
									<input type="file" name="attach_file12" id="attach_file12" class="form-control"
										placeholder=" " value="">
									</input>
								</div>
							</div>
						</div>
					</div>

				  <div class="card shadow-sm p-4">
					<!-- Section header with info and Add More button -->
					<div class="d-flex justify-content-between align-items-center mb-3">
					  <div>
						<h5 class="mb-0 text-primary">कृपया टेंडर में उल्लिखित परियोजना के कार्यों का विवरण नीचे प्रदान करें, साथ ही पूर्णता अवधि (दिनों में) और वेटेज (%) भरें।</h5>
					  </div>
					  <button type="button" class="btn btn-success btn-sm" onclick="addTaskRow()">+ Add More</button>
					</div>

					  <div id="taskContainer" class="border p-3 rounded bg-white">
						<!-- Dynamic task rows will appear here -->
					  </div>

					  <div class="mt-3">
						<strong>Total Weightage:</strong> <span id="totalWeight" class="text-primary fw-bold">0</span>/100
					  </div>

					  <div id="errorMsg" class="text-danger mt-2 d-none">
						Weightage का कुल योग 100 से अधिक नहीं होना चाहिए!
					  </div>
				  </div>

					<div class="col-md-11 pr-1" align="center">
						<div class="form-group">
							<button type="submit" name="submit"
								class="btn btn-info btn-fill pull-right">Submit
							</button>
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


    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
	<!--  Charts Plugin -->
	<script src="js/chartist.min.js"></script>
<script>
  function addTaskRow() {
    const row = document.createElement("div");
    row.className = "row g-3 align-items-end mb-3 border-bottom pb-3";

    row.innerHTML = `
      <div class="col-md-4">
        <label class="form-label">कार्य का नाम</label>
        <input type="text" name="task_name[]" class="form-control" placeholder="जैसे - सिवील वर्क " required>
      </div>
      <div class="col-md-4">
        <label class="form-label">पूर्णता (MOU के बाद दिन)</label>
        <input type="number" name="completion_days[]" class="form-control" placeholder="जैसे - 30" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Weightage (%)</label>
        <input type="number" name="weightage[]" class="form-control weightage-input" placeholder="जैसे - 20" required oninput="validateWeightage()">
      </div>
      <div class="col-md-1 text-end">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTaskRow(this)">X</button>
      </div>
    `;

    document.getElementById("taskContainer").appendChild(row);
  }

  function removeTaskRow(button) {
    button.closest(".row").remove();
    validateWeightage();
  }

  function validateWeightage() {
    const weights = document.querySelectorAll(".weightage-input");
    let total = 0;

    weights.forEach(input => {
      const val = parseFloat(input.value);
      if (!isNaN(val)) total += val;
    });

    if (total > 100) {
      const focused = document.activeElement;
      if (focused && focused.classList.contains("weightage-input")) {
        alert("Total Weightage cannot exceed 100!");
        focused.value = "";
        focused.focus();
      }
      // Recalculate total after clearing invalid input
      total = 0;
      weights.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) total += val;
      });
    }

    document.getElementById("totalWeight").textContent = total;
    document.getElementById("errorMsg").classList.toggle("d-none", total <= 100);
  }

  window.onload = addTaskRow;
</script>
	
<script>

$(document).ready( function () {
    $('#general_stat_table').DataTable({
			// paging: false,
			fixedHeader: true,
			colReorder: true,
			scrollX: true, // Enable horizontal scrolling if needed
	});
	
    
});

	$('select[multiple]').multiselect();	
	
var actionUrl = 'scripts/ajax.php';

function fill_sub_department(val, selected){
	var data = {"term":"b", "id":"sub_dep", "val":val};

	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(ajaxdata){
			console.log(ajaxdata);
			var txt = '<option value="">--Select--</option>';
			ajaxdata = JSON.parse(ajaxdata);
			$.each(ajaxdata, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(selected==value.id){
					txt += ' selected ';
				}
				txt += '>'+value.sub_department_hindi+'</option>';
				
			});
          	$("#sub_department_id").html(txt);
        }
    });
}

function fill_district(val, selected){
	var data = {"term":"b", "id":"dist", "val":val};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
			$.each(data, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(selected==value.id){
					txt += ' selected ';
				}
				txt += '>'+value.district_name+'</option>';
				
			});
          	$("#district").html(txt);
        }
    });
}
	
function fill_project(val, selected){
	var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department").val()};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
			$.each(data, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(selected==value.id){
					txt += ' selected ';
				}
				txt += '>'+value.project_name_hindi+'</option>';
				
			});
          	$("#project_name").html(txt);
        }
    });
}

<?php
	if(isset($_GET['edit_sno'])){
?>
	$(document).ready(function() {
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