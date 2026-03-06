<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

	if(isset($_POST['submit'])){
	$_POST['physical_handicap'] = isset($_POST['physical_handicap'])?$_POST['physical_handicap']:'';
	$_POST['freedom_fighter'] = isset($_POST['freedom_fighter'])?$_POST['freedom_fighter']:'';
	$_POST['ex_army_man'] = isset($_POST['ex_army_man'])?$_POST['ex_army_man']:'';
	$_POST['ladies'] = isset($_POST['ladies'])?$_POST['ladies']:'';
	$_POST['other'] = isset($_POST['other'])?$_POST['other']:'';
	
		$sql = 'insert into dp_personal_info (employee_designation_id, employee_type_id, employee_category_id, employee_subgroup, full_name, father_name, 
		date_of_birth, religion, 
		caste, sub_caste, gender, 
		email, 
		c_number, 
		employee_image, 
		aadhar_number,
		aadhar_file, 
		pan_number, 
		pan_file, 
		p_address1, 
		p_address2, 
		p_post, 
		p_tehseel, 
		p_block, 
		p_district, 
		p_pin, 
		P_state, 
		c_address1, 
		c_address2, 
		c_post, 
		c_tehseel, 
		c_block, 
		c_district, 
		c_pin, 
		c_state, 
		ac_number, 
		ifsc_code,
		attached_check, 
		nominee_name, 
		nominee_dob, 
		nominee_relation, 
		nominee_mobile_no,
		nominee_aadhar,
		physical_handicap,
		freedom_fighter,
		ex_army_man,
		ladies,
		other
		) 
		
		values (
		"'.$_POST['employee_designation'].'", 
		"'.$_POST['employee_type'].'",
		"'.$_POST['employee_category'].'",
		"'.$_POST['employee_subgroup'].'",
		"'.$_POST['full_name'].'", 
		"'.$_POST['father_name'].'",
		"'.$_POST['date_of_birth'].'",
		"'.$_POST['religion'].'","'.$_POST['caste'].'","'.$_POST['sub_caste'].'","'.$_POST['gender'].'","'.$_POST['email'].'","'.$_POST['c_number'].'","'.$_FILES['employee_image']['name'].'","'.$_POST['aadhar_number'].'", "'.$_FILES['aadhar_file']['name'].'","'.$_POST['pan_number'].'", "'.$_FILES['pan_file']['name'].'", "'.$_POST['p_address1'].'", "'.$_POST['p_address2'].'","'.$_POST['p_post'].'","'.$_POST['p_tehseel'].'", "'.$_POST['p_block'].'","'.$_POST['p_district'].'","'.$_POST['p_pin'].'","'.$_POST['P_state'].'", "'.$_POST['c_address1'].'", "'.$_POST['c_address2'].'","'.$_POST['c_post'].'","'.$_POST['c_tehseel'].'", "'.$_POST['c_block'].'","'.$_POST['c_district'].'","'.$_POST['c_pin'].'","'.$_POST['c_state'].'",  "'.$_POST['ac_number'].'","'.$_POST['ifsc_code'].'","'.$_FILES['attached_check']['name'].'", "'.$_POST['nominee_name'].'","'.$_POST['nominee_dob'].'","'.$_POST['nominee_relation'].'","'.$_POST['nominee_mobile_no'].'","'.$_POST['nominee_aadhar'].'","'.$_POST['physical_handicap'].'","'.$_POST['freedom_fighter'].'","'.$_POST['ex_army_man'].'","'.$_POST['ladies'].'","'.$_POST['other'].'")'; 
		
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$id = mysqli_insert_id($db);
			
			move_uploaded_file ($_FILES['employee_image']['tmp_name'], "master_employees_img/employee_image/".$_FILES['employee_image']['name']);
			move_uploaded_file ($_FILES['aadhar_file']['tmp_name'], "master_employees_img/aadhar/".$_FILES['aadhar_file']['name']);
			move_uploaded_file ($_FILES['pan_file']['tmp_name'], "master_employees_img/pan/".$_FILES['pan_file']['name']);
			move_uploaded_file ($_FILES['attached_check']['tmp_name'], "master_employees_img/pan/".$_FILES['attached_check']['name']);
		
		for($i=1; $i<=$_POST['add_rows_id']; $i++){
			$sql = 'insert into dp_qulification (personal_info_id, class_id, college_name, board_name, roll_number, obtained_marks, percentage, grade, file_name) values ("'.$id.'", "'.$_POST['class_id_'.$i].'","'.$_POST['college_name_'.$i].'", "'.$_POST['board_name_'.$i].'","'.$_POST['roll_number_'.$i].'","'.$_POST['obtained_marks_'.$i].'","'.$_POST['percentage_'.$i].'","'.$_POST['grade_'.$i].'","'.$_FILES['file_name_'.$i]['name'].'")';
			execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					
					move_uploaded_file ($_FILES['file_name_'.$i]['tmp_name'], "master_employees_img/result/".$_FILES['file_name_'.$i]['name']);
				}
		}
		
		for($i=1; $i<=$_POST['add_rows_id1']; $i++){
			$sql = 'insert into dp_miscellaneous (personal_info_id, title, file_name) values ("'.$id.'","'.$_POST['title_'.$i].'", "'.$_FILES['attachment_'.$i]['name'].'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';
			
			move_uploaded_file ($_FILES['attachment_'.$i]['tmp_name'], "master_employees_img/misc/".$_FILES['attachment_'.$i]['name']);
			}
		}
		}	
		
	}
	else{
	$_POST['physical_handicap'] = '';
	$_POST['freedom_fighter'] = '';
	$_POST['ex_army_man'] = '';
	$_POST['ladies'] = '';
	$_POST['other'] = '';
	
	};
	
	if(isset($_GET['del'])){
			$sql = 'delete from dp_personal_info where sno="'.$_GET['del'].'"';
			mysqli_query($db, $sql);
			if(mysqli_error($db)){
				$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
			}
			else{
			$sql = 'delete from dp_qulification where personal_info_id="'.$_GET['del'].'"';
			mysqli_query($db, $sql);
			if(mysqli_error($db)){
				$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
			}
			else{
			$sql = 'delete from dp_miscellaneous where personal_info_id="'.$_GET['del'].'"';
			mysqli_query($db, $sql);
			if(mysqli_error($db)){
				$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
			}
			else{
				$msg1 .= '<p class="text text-denger">Data Deleted</p>';
				}
			}
		} 
	}	 
		
		if(isset($_GET['edit'])){
		$sql= 'select * from dp_personal_info where sno="'.$_GET['edit'].'"';
		$row = mysqli_fetch_assoc(mysqli_query($db, $sql));	
		}





?>


<?php

if(isset($_GET['id'])){
	$sql = 'select * from dp_personal_info where sno='.$_GET['id'];
	//echo $sql;
	$row = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>

	<div class="container col-md-10">
		<div class="card mx-auto border-primary">
			<div class="card-body  ">
				<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
					<?php echo $msg; ?>
					<div class="row d-flex my-auto">		
						<div class="col-md-12">							
							<h3>Personal Details</h3>						
							<div class="row">
								<div class="col-md-3">							
									<label>Employee Designation</label>
									<select readonly class="form-control" name="employee_designation" id="employee_designation" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from dp_designation";
										$run = execute_query($query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_GET['id'])){
												if($row['employee_designation_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['designation']).'</option>';
										}
										?>
									</select>
								</div>
								<div class="col-md-3">							
									<label>Employee Type</label>
									<select readonly name="employee_type" id="employee_type" tabindex="<?php echo $tab++; ?>" class="form-control">
										<option value="">--Select--</option>
										<?php 
										$query = "select * from dp_type";
										$run = execute_query($query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_GET['id'])){
												if($row['employee_type_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['type_name']).'</option>';
										}
										?>
									</select>
								</div>
								<div class="col-md-3">							
									<label>Employee Group</label>
									<select readonly name="employee_category" id="employee_category" tabindex="<?php echo $tab++; ?>" class="form-control">
										<option value="">--Select--</option>
										<?php 
										$query = "select * from dp_category";
										$run = execute_query($query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_GET['id'])){
												if($row['employee_category_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['category_name']).'</option>';
										}
										?>
									</select>
								</div>
								<div class="col-md-3">							
									<label>Employee Sub-Group</label>
									<select readonly name="employee_subgroup" id="employee_subgroup" tabindex="<?php echo $tab++; ?>" class="form-control">
										<option value="">--Select--</option>
										<?php 
										$query = "select * from dp_subgroup";
										$run = execute_query($query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_GET['id'])){
												if($row['employee_subgroup']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['subgroup_name']).'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="row">							
								<div class="col-md-3">							
									<label>Full Name </label>
									<input readonly type="text" name="full_name" id="full_name" tabindex="<?php echo $tab++; ?>" class="form-control" value="<?php if(isset($row['full_name'])){echo $row['full_name'];}?>">
								</div>
								<div class="col-md-3">							
									<label>Father Name </label>
									<input readonly type="text" name="father_name" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['father_name'])){echo $row['father_name'];}?>">
								</div>
								<div class="col-md-3">							
									<label>Date of Birth</label>
									
									<input readonly type="text" name="date_of_birth" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['date_of_birth'])){echo $row['date_of_birth'];}?>">
								</div>
								<div class="col-md-3">							
									<label>Religion</label>
									<input readonly type="text" name="religion" class="form-control" value="<?php if(isset($row['religion'])){echo $row['religion'];}?>">
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-3">							
									<label>Category</label>
									<input readonly type="text" name="caste"id="caste" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['caste'])){echo $row['caste'];}?>">
								</div>
								<div class="col-md-3">							
									<label>Caste</label>
									<input readonly type="text" name="sub_caste" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['sub_caste'])){echo $row['sub_caste'];}?>">
								</div>
								<div class="col-md-3">							
									<label>Gender</label>
									<input readonly type="gender" name="gender" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['gender'])){echo $row['gender'];}?>">
									
									
								</div>
								<div class="col-md-3">							
									<label>E-mail</label>
									<input readonly type="email" name="email" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['email'])){echo $row['email'];}?>">
								</div>
								<div class="col-md-3">							
									<label>Contact Number</label>
									<input readonly type="text" name="c_number" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_number'])){echo $row['c_number'];}?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">							
									<label>Upload Photo</label>
									
								</div>
								<div class="col-md-4">	
									<label>Aadhar Number</label>
									<input readonly type="text" name="aadhar_number" class="form-control" value="<?php if(isset($row['aadhar_number'])){echo $row['aadhar_number'];}?>">
								</div>
								<div class="col-md-4">							
									<label>Pan Number</label>
									<input readonly type="text" name="pan_number" class="form-control" value="<?php if(isset($row['pan_number'])){echo $row['pan_number'];}?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									
									<img src="master_employees_img/employee_image/<?php echo $row['employee_image'];?>" style="width:80px; height:80px;"/>
								</div>
								<div class="col-md-4">
									<label>Aadhar File</label>
									<div>
										<img src="master_employees_img/aadhar/<?php echo $row['aadhar_file'];?>" style="width:80px; height:80px;"/>
									</div>
								</div>
								<div class="col-md-4">
									<label>Pan File</label>
									<div >
										<img src="master_employees_img/pan/<?php echo $row['pan_file'];?>" style="width:80px; height:80px;"/>
									</div>
								</div>
							</div>								
							<h4>Permanent Address</h4>
							<div class="row">
								<div class="col-md-3">
									<label>Address Line 1</label>
									<input readonly type="text" name="p_address1" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_address1'])){echo $row['p_address1'];}?>">
								</div>
								<div class="col-md-3">
									<label>Address Line 2</label>
									<input readonly type="text" name="p_address2" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_address2'])){echo $row['p_address2'];}?>">
								</div>
								<div class="col-md-3">
									<label>Post</label>
									<input readonly type="text" name="p_post" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_post'])){echo $row['p_post'];}?>">
								</div>
								<div class="col-md-3">
									<label>Tehseel</label>
									<input readonly type="text" name="p_tehseel" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_tehseel'])){echo $row['p_tehseel'];}?>">
								</div>
							</div>
							<div class="row">								
								<div class="col-md-3">
									<label>Block</label>
									<input readonly type="text" name="p_block" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_block'])){echo $row['p_block'];}?>">
								</div>
								<div class="col-md-3">
									<label>District</label>
									<input readonly type="text" name="p_district" class="form-control" value="<?php if(isset($row['p_district'])){echo $row['p_district'];}?>">
								</div>
								<div class="col-md-3">
									<label>PIN</label>
									<input readonly type="text" name="p_pin" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_pin'])){echo $row['p_pin'];}?>">
								</div>
								<div class="col-md-3">
									<label>State</label>
									<input readonly type="text" name="P_state" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['P_state'])){echo $row['P_state'];}?>">
								</div>
							</div>
							
							<h4>Correpondence Address</h4>
							<div class="row">
								<div class="col-md-3">
									<label>Address Line 1</label>
									<input readonly type="text" name="c_address1" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_address1'])){echo $row['c_address1'];}?>">
								</div>
								<div class="col-md-3">
									<label>Address Line 2</label>
									<input readonly type="text" name="c_address2" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_address2'])){echo $row['c_address2'];}?>">
								</div>
								<div class="col-md-3">
									<label>Post</label>
									<input readonly type="text" name="c_post" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_post'])){echo $row['c_post'];}?>">
								</div>
								<div class="col-md-3">
									<label>Tehseel</label>
									<input readonly type="text" name="c_tehseel" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_tehseel'])){echo $row['c_tehseel'];}?>">
								</div>
							</div>
							<div class="row">								
								<div class="col-md-3">
									<label>Block</label>
									<input readonly type="text" name="c_block" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_block'])){echo $row['c_block'];}?>">
								</div>
								<div class="col-md-3">
									<label>District</label>
									<input readonly type="text" name="c_district" class="form-control" value="<?php if(isset($row['c_district'])){echo $row['c_district'];}?>">
								</div>
								<div class="col-md-3">
									<label>PIN</label>
									<input readonly type="text" name="c_pin" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_pin'])){echo $row['c_pin'];}?>">
								</div>
								<div class="col-md-3">
									<label>State</label>
									<input readonly type="text" name="c_state" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_state'])){echo $row['c_state'];}?>">
								</div>
							</div>
							
							<h4>Bank Details</h4>
							<div class="row">
								<div class="col-md-4">
									<label>A/C Number</label>
									<input readonly type="text" name="ac_number" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['ac_number'])){echo $row['ac_number'];}?>">
								</div>
								<div class="col-md-4">
									<label>IFSC Code</label>
									<input readonly type="text" name="ifsc_code" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['ifsc_code'])){echo $row['ifsc_code'];}?>">
								</div>
								<div class="col-md-4">
									<label>Attached Cancel Check/Passbook</label>
									<input readonly type="text" name="attached_check" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['attached_check'])){echo $row['attached_check'];}?>">
								</div>
							</div>
							<h4>Nomination Details</h4>
							<?php
							if(isset($_GET['id'])){
							$sql = 'select * from dp_nominee where personal_info_id='.$_GET['id'];
							//echo $sql;
							$result = execute_query($sql);
							while($row = mysqli_fetch_assoc($result)){
							echo '
							<div class="row">
								<div class="col-md-2">
									<label>Name of Nominee</label>
									<input readonly type="text" name="nominee_name" class="form-control" tabindex="<?php echo $tab++; ?>" value="';
									if(isset($row['nominee_name'])){echo $row['nominee_name'];}
									
									echo '">
								</div>
								<div class="col-md-2">
									<label>Date of Birth Nominee</label>
									<input readonly type="text" name="nominee_dob" class="form-control" tabindex="<?php echo $tab++; ?>" value="';
									if(isset($row['nominee_dob'])){echo $row['nominee_dob'];}
									
									echo '">
								</div>
								<div class="col-md-2">
									<label>Relation with Nominee</label>
									<input readonly type="text" name="nominee_relation" class="form-control" tabindex="<?php echo $tab++; ?>" value="';
									if(isset($row['nominee_relation'])){echo $row['nominee_relation'];}
									
									echo '">
								</div>
								<div class="col-md-3">
									<label>Share Percentage of the Nominee</label>
									<input readonly type="text" name="nominee_name" class="form-control" tabindex="<?php echo $tab++; ?>" value="';
									if(isset($row['nominee_share'])){echo $row['nominee_share'];}
									
									echo '">
								</div>
								<div class="col-md-3">
									<label>Aadhar Number of the Nominee</label>
									<input readonly type="text" name="nominee_name" class="form-control" tabindex="<?php echo $tab++; ?>" value="';
									if(isset($row['nominee_aadhar'])){echo $row['nominee_aadhar'];}
									
									echo '">
								</div>
							</div>';
							}}
							?>
							<hr>						
							<h4>Qualifcation Details</h4>
						
							<?php
							if(isset($_GET['id'])){
							$sql = 'select * from dp_qulification where personal_info_id='.$_GET['id'];
							//echo $sql;
							$result = execute_query($sql);
							while($row = mysqli_fetch_assoc($result)){
							echo '
							<div class="row">
								<div class="col-md-2">
									<label>Class </label>
									<select readonly class="form-control" name="class_id" id="class_id" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>';
										$query = "select * from dp_class";
										$run = execute_query($query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_GET['id'])){
												if($row['class_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['class_name']).'</option>';
										}
										echo '
									</select>
								</div> 
								<div class="col-md-2">
									<label >College Name</label>
									<input readonly type="text" name="college_name" id="college_name" class="form-control" placeholder="" tabindex="<?php echo $tab++; ?>" value="';
									if(isset($row['college_name'])){echo $row['college_name'];}
									
									echo '">
								</div>
								<div class="col-md-2">
									<label >Board</label>
									<input readonly type="text" name="board_name" id="board_name" class="form-control" placeholder="" tabindex="<?php echo $tab++; ?>" value="'; 
									if(isset($row['board_name'])){echo $row['board_name'];}
									echo '">
								</div>

								<div class="col-md-2">
									<label>Roll No.</label>
									<input readonly type="text" name="roll_number" id="roll_number" class="form-control" placeholder=""
									value="'; 
									if(isset($row['roll_number'])){echo $row['roll_number'];}
									echo '">
								</div>

								<div class="col-md-1">
									<label>Marks</label>
									<input readonly type="text" name="obtained_marks" id="obtained_marks" class="form-control" placeholder="" tabindex="<?php echo $tab++; ?>"
									value="'; 
									if(isset($row['obtained_marks'])){echo $row['obtained_marks'];}
									echo '">
								</div> 
								<div class="col-md-1">
									<label>Percentage</label>
									<input readonly type="text" name="percentage" id="percentage" class="form-control" placeholder="" tabindex="<?php echo $tab++; ?>" 
									value="'; 
									if(isset($row['percentage'])){echo $row['percentage'];}
									echo '">
								</div>
								<div class="col-md-1">
									<label >Grade</label>
									<input readonly type="text" name="grade" id="grade" class="form-control" placeholder="" tabindex="<?php echo $tab++; ?>" 
									value="'; 
									if(isset($row['grade'])){echo $row['grade'];}
									echo '">
								</div>

								<div class="col-md-1">
									<label> Result</label>
									<div >
										<img src="master_employees_img/result/'.$row['file_name'].'" style="width:80px; height:80px;"
										  >
										</div>
								</div>
							</div>
							';
						}}
						?>
							<div id="test"></div>
							<input type="hidden" name="add_rows_id" id="add_rows_id" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['add_rows_id']; ?>">
					    				
							<h4>Files and Attachemnts</h4>
							
							<?php
								if(isset($_GET['id'])){
								$sql = 'select * from dp_miscellaneous where personal_info_id='.$_GET['id'];
								//echo $sql;
							$result = execute_query($sql);
							while($row = mysqli_fetch_assoc($result)){
							echo '
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label > Title</label>
										<input readonly type="text" name="title" id="title" class="form-control" placeholder="" tabindex="<?php echo $tab++; ?>" value="';
										if(isset($row['title'])){echo $row['title'];}
										echo '">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label>Upload file</label>
										<div >
										<img src="master_employees_img/misc/'.$row['file_name'].'" style="width:80px; height:80px;"
										  >
										</div>
									</div>	
								</div>
							</div>
								';
						}}
						?>
						<?php

						if(isset($_GET['id'])){
							$sql = 'select * from dp_personal_info where sno='.$_GET['id'];
							//echo $sql;
							$row = mysqli_fetch_assoc(execute_query($sql));
							//echo mysqli_error($db).'>>'.$sql;
						}

						?>
						<div class="card">
							<h3>Other Information</h3>
							<div  class="row border rounded m-2 p-2 border-secondary">
								<div class="col-md-3">							
									<label>Physical handicap</label>
									<input readonly type="checkbox" name="physical_handicap" id="physical_handicap" class="" <?php if($row['physical_handicap']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
								</div>
								<div class="col-md-3">							
									<label>Freedom fighter dependent</label>
									<input readonly type="checkbox" name="freedom_fighter" id="freedom_fighter" class="" <?php if($row['freedom_fighter']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
								</div>
								<div class="col-md-2">							
									<label>Ex-Army man</label>
									<input readonly type="checkbox" name="ex_army_man" id="ex_army_man" class="" <?php if($row['ex_army_man']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
								</div>
								<div class="col-md-2">							
									<label>Female/ladies</label>
									<input readonly type="checkbox" name="ladies" id="ladies" class="" <?php if($row['ladies']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
								</div>
								<div class="col-md-2">							
									<label>Other</label>
									<input readonly type="checkbox" name="other" id="other" class="" <?php if($row['other']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
								</div>
							</div>
						</div>
						
						</div>
					</div>
				</form>	
			</div>
		</div>
	</div>
	

<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
   
<?php		
page_footer_end();
?>