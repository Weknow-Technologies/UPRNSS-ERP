<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	// $sql = 'select * from dp_personal_info where sno='.$_GET['id'];
	// //echo $sql;
	// $row = mysqli_fetch_assoc(execute_query($sql));
	// //echo mysqli_error($db).'>>'.$sql;
	
	
	$sql= 'select * from dp_personal_info where sno="'.$_GET['id'].'"';
	$row = mysqli_fetch_assoc(execute_query($sql));
	$_POST['old_employee_code'] = $row['old_employee_code'];
	$_POST['file_no'] = $row['file_no'];
	$_POST['service_book_no'] = $row['service_book_no'];
	$_POST['employee_category_id'] = $row['employee_category_id'];
	
	
	$_POST['date_of_birth'] = $row['date_of_birth'];
	$_POST['physical_handicap'] = $row['physical_handicap'];
	$_POST['freedom_fighter'] = $row['freedom_fighter'];
	$_POST['ex_army_man'] =$row['ex_army_man'];
	$_POST['ladies'] = $row['ladies'];
	$_POST['other'] = $row['other'];
	
	$sql = 'select * from dp_qulification where personal_info_id="'.$_GET['id'].'"';
	$result_qul = execute_query($sql);
	if(mysqli_num_rows($result_qul)!=0){
		$_POST['add_rows_id'] = mysqli_num_rows($result_qul);
		$i=1;
		while($row_qul = mysqli_fetch_assoc($result_qul)){
			$_POST['class_id_'.$i]= $row_qul['class_id'];
			$_POST['college_name_'.$i] = $row_qul['college_name'];
			$_POST['board_name_'.$i] = $row_qul['board_name'];
			$_POST['roll_number_'.$i] = $row_qul['roll_number'];
			$_POST['obtained_marks_'.$i]= $row_qul['obtained_marks'];
			$_POST['percentage_'.$i]= $row_qul['percentage'];
			$_POST['grade_'.$i]= $row_qul['grade'];
			$_POST['file_name_'.$i]= $row_qul['file_name'];
			$i++;
		}
	}

	$sql = 'select * from dp_nominee where personal_info_id="'.$_GET['id'].'"';
	$result_nomi = execute_query($sql);
	if(mysqli_num_rows($result_nomi)!=0){
		$_POST['add_rows_id_nomi'] = mysqli_num_rows($result_nomi);
		$i=1;
		while($row_nomi = mysqli_fetch_assoc($result_nomi)){
			$_POST['nominee_name_'.$i]= $row_nomi['nominee_name'];
			$_POST['nominee_dob_'.$i] = $row_nomi['nominee_dob'];
			$_POST['nominee_relation_'.$i] = $row_nomi['nominee_relation'];
			$_POST['nominee_share_'.$i] = $row_nomi['nominee_share'];
			$_POST['nominee_aadhar_'.$i]= $row_nomi['nominee_aadhar'];
			$i++;
		}
	}

	$sql = 'select * from dp_miscellaneous where personal_info_id="'.$_GET['id'].'"';
	$result_misc = execute_query($sql);
	if(mysqli_num_rows($result_misc)!=0){
		$_POST['add_rows_id1'] = mysqli_num_rows($result_misc);
		$i=1;
		while($row_misc = mysqli_fetch_assoc($result_misc)){
			$_POST['title_'.$i]= $row_misc['title'];
			// $_POST['attachment_'.$i]['name']= $row_misc['file_name'];
			$i++;
		}
	}

	$sql = 'select * from employee_appointment where employee_id="'.$_GET['id'].'" and (appo_id="1" or appo_id="4" or appo_id="5")';
	// echo $sql;
	$result = execute_query($sql);
	if (mysqli_num_rows($result) != 0) {
		$data = mysqli_fetch_assoc($result);
		if (isset($data['appo_id'])) {
			$_POST['appo_id'] = $data['appo_id'];
			$_POST['appointment_no'] = $data['appointment_no'];
			$_POST['appointment_date'] = $data['appointment_date'];
			$_POST['appoint_division'] = $data['appoint_division'];
			$_POST['appoint_post'] = $data['post'];
			$_POST['joinning_date'] = $data['joinning_date'];
			$_POST['pay_band_id'] = $data['pay_band_id'];
			$_POST['grade_pay_id'] = $data['grade_pay_id'];
			$_POST['pay_level_id'] = $data['pay_level_id'];
			
			$_POST['deputation_dep_name'] = $data['deputation_dep_name'];
			$_POST['deputation_from'] = $data['deputation_from'];
			$_POST['deputation_to'] = $data['deputation_to'];	
		}else{
			$_POST['appo_id'] = '';
			$_POST['appointment_no'] = '';
			$_POST['appointment_date'] = '';
			$_POST['appoint_division'] = '';
			$_POST['appoint_post'] = '';
			$_POST['joinning_date_1'] = '';
			$_POST['pay_band_id'] = '';
			$_POST['grade_pay_id'] = '';
			$_POST['pay_level_id']= '';
			
			$_POST['deputation_dep_name'] = '';
			$_POST['deputation_from'] = '';
			$_POST['deputation_to'] = '';
		}	
	}
	$sql = 'select * from employee_appointment where employee_id="'.$_GET['id'].'" and (appo_id="6" or appo_id="7" or appo_id="8")';
	$result_work = execute_query($sql);
	if(mysqli_num_rows($result_work)!=0){
		$_POST['add_rows_id_work'] = mysqli_num_rows($result_work);
		$i=1;
		while($row_work = mysqli_fetch_assoc($result_work)){
			$_POST['pramotaion_type_'.$i] = $row_work['appo_id'];
			$_POST['order_no_'.$i] = $row_work['appointment_no'];
			$_POST['order_date_'.$i] = $row_work['appointment_date'];
			$_POST['place_'.$i] = $row_work['appoint_division'];
			$_POST['post_'.$i] = $row_work['post'];
			$_POST['joinning_date_'.$i] = $row_work['joinning_date'];
			
			$_POST['pay_band_id_'.$i] = $row_work['pay_band_id'];
			$_POST['grade_pay_id_'.$i] = $row_work['grade_pay_id'];
			$_POST['pay_level_id_'.$i] = $row_work['pay_level_id'];
			
			$i++;
		}
	}	
	$_POST['id'] = $_GET['id'];
	
	
	
}

?>
	<script>

	function onchangetype()
	{
		var status = document.getElementById("appo_id");
		
		if(status.value == "5")
		{
			document.getElementById("hide").style.display="flex";
		}
		else{
			document.getElementById("hide").style.display="none";
			
			
		}
	}
	
	</script>
	
	<style>
		
		/* Mark input boxes that gets an error on validation: */
		input.invalid {
		  background-color: #ffdddd;
		}

		/* Hide all steps by default: */
		.tab {
		  display: none;
		}
		/* Make circles that indicate the steps of the form: */
		.step {
		  height: 20px;
		  width: 20px;
		  margin: 10 9 0px !important;
		  background-color: #bbbbbb;
		  border: 2px solid black;
		  border-radius: 50%;
		  display: inline-block;
		  opacity: 0.5;
		}

		.step.active {
		  opacity: 1;
		}

		/* Mark the steps that are finished and valid: */
		.step.finish {
		  background-color: yellow;
		}
	</style>

	<form id="employees_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
	
		<div class="container col-md-12 " >
			<div class="card ">
				<div class="card-body ">
						<?php echo $msg; ?>
						<!-- Circles which indicates the steps of the form: -->
						<div style="text-align:center;margin-top:10px; margin-bottom:10px; display:flex; border-radius: 5px;"
						 	class="bg-danger mx-auto text-white" >
							<h5 >Personal Detail &nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Address&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Bank Details&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Qualifcation Details&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Other Information&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Appointment Details&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Employeement Details&nbsp;<span class="step"></span></h5>
						</div>
						<div class="row d-flex my-auto">		
							<div class="col-md-12">
								<div class="card " >
									<h3 class="bg-primary text-white p-3">मानवसंपदा मानव संसाधन प्रबंधन प्रणाली के लिए कार्मिक विवरण</h5>
									<h3>कार्मिक पंजीकरण विवरण</h5>
									
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr style="background-color:whitesmoke;">
										<th width = "10%">Employee Group</th>
										<td width = "15%">
											<select  name="employee_category_id" id="employee_category_id" tabindex="<?php echo $tab++; ?>" class="form-control" disabled>
												<option value="">--Select--</option>
												<?php 
												$query = "select * from dp_category";
												$run = execute_query($query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['sno'].'" ';
													if(isset($_POST['employee_category_id'])){
													if($_POST['employee_category_id']==$data['sno']){
														echo ' selected="Selected"';
														}
													}
													echo '>'.trim($data['category_name']).'</option>';
												}
												?>
											</select>
										</td>
										<th width="22%">Old Employee Code:</th>
										<td><?php echo $_POST['old_employee_code']; ?></td>
										<th>File No:</th>
										<td><?php echo $_POST['file_no']; ?></td>
										<th>Service Book No:</th>
										<td><?php echo $_POST['service_book_no']; ?></td>
										
										</tr>
									</table>
									<table width="100%" class="table table-striped table-hover rounded">
										<tr>
											<td>Full Name </td>
											<td><?php if(isset($row['full_name'])){echo $row['full_name'];}?><td>
											<td>Father Name </td>
											<td><?php if(isset($row['father_name'])){echo $row['father_name'];}?></td>
											<td>Date of Birth</td>
											<td><?php echo $_POST['date_of_birth']; ?></td>
										
										</tr>
										<tr class="table-info">
											<td>Religion</td>
											<td width = "15%">
												<select name="religion" class="form-control" tabindex="<?php echo $tab++; ?>"  disabled>
													<option value="">--Select--</option>
													<option value="hinduism" <?php if(isset($row['religion']))if($row['religion']=='hinduism'){ echo ' selected="Selected"'; }?>>Hinduism</option>
													<option value="islam" <?php if(isset($row['religion']))if($row['religion']=='islam'){ echo ' selected="Selected"'; }?>>Islam</option>
													<option value="cristianity" <?php if(isset($row['religion']))if($row['religion']=='cristianity'){ echo ' selected="Selected"'; }?>">Cristianity</option>
													<option value="buddhism" <?php if(isset($row['religion']))if($row['religion']=='buddhism'){ echo ' selected="Selected"'; }?>>Buddhism</option>
													
												</select>
											</td>
											<td>Category</td>
											<td width = "15%">
												<select name="caste" class="form-control" tabindex="<?php echo $tab++; ?>" disabled>
													<option value=""> --Select--</option>
													<option value="Un-Reserved/General" <?php if(isset($row['caste']))if($row['caste']=='Un-Reserved/General'){ echo ' selected="Selected"'; }?>">Un-Reserved /General</option>
													<option value="OBC" <?php if(isset($row['caste']))if($row['caste']=='OBC'){ echo ' selected="Selected"'; }?>>OBC</option>
													<option value="SC" <?php if(isset($row['caste']))if($row['caste']=='SC'){ echo ' selected="Selected"'; }?>>SC</option>
													<option value="ST" <?php if(isset($row['caste']))if($row['caste']=='ST'){ echo ' selected="Selected"'; }?>>ST</option>
													<option value="Minority" <?php if(isset($row['caste']))if($row['caste']=='Minority'){ echo ' selected="Selected"'; }?>>Minority</option>
													<option value="EWS" <?php if(isset($row['caste']))if($row['caste']=='EWS'){ echo ' selected="Selected"'; }?>>EWS</option>
													
												</select>
											</td>
											<td>Caste</td>
											<td><?php if(isset($row['sub_caste'])){echo $row['sub_caste'];}?></td>
											<td>Gender</td>
											<td><?php if(isset($row['gender'])){echo $row['gender'];}?></td>
										</tr>
										<tr>
											
											<td>E-mail</td>
											<td><?php if(isset($row['email'])){echo $row['email'];}?></td>
											<td>Contact Number</td>
											<td><?php if(isset($row['c_number'])){echo $row['c_number'];}?></td>
											<td>Aadhar No.</td>
											<td><?php if(isset($row['aadhar_number'])){echo $row['aadhar_number'];}?></td>
											<td>Pan Number</td>
											<td><?php if(isset($row['pan_number'])){echo $row['pan_number'];}?></td>
											
										</tr>
										
									</table>
								</div>
								<div class="card " id="info_table" name="info_table" >
									<h4>Permanent Address</h4>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>Address Line 1</td>
											<td><?php if(isset($row['p_address1'])){echo $row['p_address1'];}?><td></td>					
											<td>Address Line 2</td>
											<td><?php if(isset($row['p_address2'])){echo $row['p_address2'];}?><td></td>
											<td>Post</td>
											<td><?php if(isset($row['p_post'])){echo $row['p_post'];}?><td></td>
										</tr>
										<tr class=" ">
											<td>Tehseel</td>
											<td><?php if(isset($row['p_tehseel'])){echo $row['p_tehseel'];}?><td></td>
											<td>Block</td>
											<td><?php if(isset($row['p_block'])){echo $row['p_block'];}?><td></td>
											<td>District</td>
											<td><?php if(isset($row['p_district'])){echo $row['p_district'];}?><td></td>
										</tr>
										<tr class="table-danger ">
											<td>PIN</td>
											<td><?php if(isset($row['p_pin'])){echo $row['p_pin'];}?><td></td>
											<td>State</td>
											<td><?php if(isset($row['p_state'])){echo $row['p_state'];}?><td></td>
											<td></td>
											<td></td>
										</tr>
									</table>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="bg-secondary text-white">
											<th colspan="6" class="h5" style="text-align:center;">Correspondence Address <a href="javascript:copy_adr()" class=" no-print btn btn-primary" >Click Here to Copy</a></th>
										</tr>
										<tr class="table-danger ">
										<td>Address Line 1</td>
											<td><?php if(isset($row['p_address1'])){echo $row['p_address1'];}?><td></td>					
											<td>Address Line 2</td>
											<td><?php if(isset($row['p_address2'])){echo $row['p_address2'];}?><td></td>
											<td>Post</td>
											<td><?php if(isset($row['p_post'])){echo $row['p_post'];}?><td></td>
										</tr>
										<tr class="">
										<td>Tehseel</td>
											<td><?php if(isset($row['p_tehseel'])){echo $row['p_tehseel'];}?><td></td>
											<td>Block</td>
											<td><?php if(isset($row['p_block'])){echo $row['p_block'];}?><td></td>
											<td>District</td>
											<td><?php if(isset($row['p_district'])){echo $row['p_district'];}?><td></td>
										</tr>
										</tr>
										<tr class="table-danger ">
										<td>PIN</td>
											<td><?php if(isset($row['p_pin'])){echo $row['p_pin'];}?><td></td>
											<td>State</td>
											<td><?php if(isset($row['p_state'])){echo $row['p_state'];}?><td></td>
											<td></td>
											<td></td>
										</tr>
									</table>
								</div>
								<div class="card "  >
									<h4>Bank Details</h4>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>A/C Number</td>
											<td><?php if(isset($row['ac_number'])){echo $row['ac_number'];}?><td></td>
											<td>IFSC Code</td>
											<td><?php if(isset($row['ifsc_code'])){echo $row['ifsc_code'];}?><td></td>
											<td>Attached Cancel Check/Passbook</td>
											<td><?php if(isset($row['attached_check'])){echo $row['attached_check'];}?><td></td>
										</tr>
										
									</table>
									<h4>Nomination Details</h4>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>Name of Nominee</td>
											<td>Date of Birth Nominee</td>
											<td>Relation with Nominee</td>
											<td>Share Percentage of the Nominee</td>
											<td>Aadhar Number of the Nominee</td>
											<td></td>
											
											
										</tr>
										<?php 
										for($i=1;$i<=$_POST['add_rows_id_nomi'];$i++){
										?>
										<tr id="add_rows_length_nomi">
											<td><input type="text" id="nominee_name_<?php echo $i; ?>" name="nominee_name_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_name_'.$i]; ?>"></td>
											
											<td><input type="date" id="nominee_dob_<?php echo $i; ?>" name="nominee_dob_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_dob_'.$i]; ?>"></td>
											
											<td><input type="text" id="nominee_relation_<?php echo $i; ?>" name="nominee_relation_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_relation_'.$i]; ?>"></td>
											
											<td><input type="text" id="nominee_share_<?php echo $i; ?>" name="nominee_share_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_share_'.$i]; ?>"></td>
											
											<td><input type="text" id="nominee_aadhar_<?php echo $i; ?>" name="nominee_aadhar_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_aadhar_'.$i]; ?>"></td>
											<td>
											<div class="col-md-1 d-flex justify-content- align-items-center">
												<button type="button" id="add_button_nomi" class="no-print btn btn-info pull-right" onClick="add_rows_nomi()">Add</button>
											</div>
											</td>
										</tr>
										
										<?php  } ?>
										<tr>
											<th colspan="6" id="nominee"></th>
										</tr>
										<input type="hidden" name="add_rows_id_nomi" id="add_rows_id_nomi" value="<?php echo $_POST['add_rows_id_nomi']; ?>">
									</table>
								</div>
								<div class="card " >
									<h3>Qualifcation Details</h3>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>Class</td>
											<td>College Name</td>
											<td>Board</td>
											<td>Roll No.</td>
											<td>Marks</td>
											<td>Percentage</td>
											<td>Grade</td>
											<td>Attach</td>
											<td></td>
											
										</tr>
										<?php 
										for($i=1;$i<=$_POST['add_rows_id'];$i++){
										?>
										<tr id="add_rows_length">
										
											<td> <?php echo $i; ?>.<select class="form-control" name="class_id_<?php echo $i; ?>" id="class_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
												<option value="">--- Select ---</option>
												<?php
												$query = "select * from dp_class";
												$run = execute_query($query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['sno'].'" ';
													if(isset($_POST['id'])){
														if($_POST['class_id_'.$i]==$data['sno']){
															echo ' selected="Selected"';
														}
													}
													echo '>'.trim($data['class_name']).'</option>';
												}
												?>
											</select></td>
											
											<td><input type="text" name="college_name_<?php echo $i; ?>" id="college_name_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['college_name_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="text" name="board_name_<?php echo $i; ?>" id="board_name_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['board_name_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											<td><input type="text" name="roll_number_<?php echo $i; ?>" id="roll_number_<?php echo $i; ?>" class="form-control" placeholder=""value="<?php echo $_POST['roll_number_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="text" name="obtained_marks_<?php echo $i; ?>" id="obtained_marks_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['obtained_marks_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="text" name="percentage_<?php echo $i; ?>" id="percentage_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['percentage_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											<td><input type="text" name="grade_<?php echo $i; ?>" id="grade_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['grade_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="file" name="file_name_<?php echo $i; ?>" id="file_name_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['file_name_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											<td>
											<div class="col-md-1 d-flex justify-content- align-items-center">
												<button type="button" id="add_button" class="no-print btn btn-info pull-right" onClick="add_rows()">Add</button>
											</div>
											</td>
											
										</tr>
										<?php  } ?>
										<tr>
											<th colspan="9" id="test"></th>
										</tr>
										<input type="hidden" name="add_rows_id" id="add_rows_id" value="<?php echo $_POST['add_rows_id']; ?>">
										
									</table>
								</div>
								<div class="card " >
									<h3>Other Attachemnts</h3>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">										
											<td>Title</td>
											<td>Upload file</td>
											<td></td>
										</tr>
										<?php 
										for($i=1;$i<=$_POST['add_rows_id1'];$i++){
										?>
										<tr id="add_rows_length1">
											<td><?php echo $i; ?>.<input type="text" name="title_<?php echo $i; ?>" id="title_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['title_'.$i]; ?>"  tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="file" accept=".jpg, .jpeg, .gif, .png, .bmp" class="form-control" id="attachment_<?php echo $i; ?>" name="attachment_<?php echo $i; ?>" value="<?php echo $_POST['attachment_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											<td>
											<div class="col-md-1 d-flex justify-content- align-items-center">
												<button type="button" id="add_button1" class="no-print btn btn-info pull-right" onClick="add_rows1()">Add</button>
											</div>
											</td>
										</tr>
										<?php  } ?>
										<tr>
											<th colspan="2" id="test1"></th>
										</tr>
										<input type="hidden" name="add_rows_id1" id="add_rows_id1" value="<?php echo $_POST['add_rows_id1']; ?>">
									</table>
								
									<h3>Other Information</h3>
									<tr class="border rounded m-2 p-2 border-secondary">
    <td class="col-md-3" style="width: 25%;">Physical handicap</td>
    <td class="col-md-3" style="width: 25%;">
        <input type="checkbox" name="physical_handicap" id="physical_handicap" class="" <?php if($_POST['physical_handicap']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
    </td>
    <td class="col-md-3" style="width: 25%;">Freedom fighter dependent</td>
    <td class="col-md-3" style="width: 25%;">
        <input type="checkbox" name="freedom_fighter" id="freedom_fighter" class="" <?php if($_POST['freedom_fighter']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
    </td>
</tr>
<tr class="border rounded m-2 p-2 border-secondary">
    <td class="col-md-3" style="width: 25%;">Ex-Army man</td>
    <td class="col-md-3" style="width: 25%;">
        <input type="checkbox" name="ex_army_man" id="ex_army_man" class="" <?php if($_POST['ex_army_man']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
    </td>
    <td class="col-md-3" style="width: 25%;">Female/ladies</td>
    <td class="col-md-3" style="width: 25%;">
        <input type="checkbox" name="ladies" id="ladies" class="" <?php if($_POST['ladies']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
    </td>
</tr>
<tr class="border rounded m-2 p-2 border-secondary">
    <td class="col-md-3" style="width: 25%;">Other</td>
    <td class="col-md-9" colspan="3" style="width: 75%;">
        <input type="checkbox" name="other" id="other" class="" <?php if($_POST['other']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
    </td>
</tr>

								</div>
								<div class="card ">
									<h3 class="bg-danger text-white mt-3">Appointment Detail</h3>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr>
											<td>Appointment Type</td>
											<td width = "20%"><select class="form-control" name="appo_id" id="appo_id" tabindex="<?php echo $tab++; ?>" onchange="onchangetype()" >
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_appointment_type where type=1 ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['appo_id'])){
															if($_POST['appo_id']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['appo_type']).'</option>';
													}
													?>
													</select></td>
											<td>Appointment No.</td>
											<td><?php echo $_POST['appointment_no']; ?></td>
											<td>Appointment Date</td>
											<td><?php echo $_POST['appointment_date']; ?></td>
											<td>Appoint Division</td>
											<td><select class="form-control" name="appoint_division" id="appoint_division" tabindex="<?php echo $tab++; ?>" disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from uprnss_division order by division_name ASC';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['s_no'].'" ';
														if(isset($_POST['appoint_division'])){
															if($_POST['appoint_division']==$data['s_no']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['division_name'].'</option>';
													}
													?>
												</select>
											</td>
										</tr>
										<tr id="hide" style="display:<?php echo $_POST['appo_id']==5?'flex':'none'; ?>;">
											<td>Department Name</td>
											<td><?php echo $_POST['deputation_dep_name']; ?></td>
											<td>Deputation From</td>
											<td><?php echo $_POST['deputation_from']; ?></td>
											<td>Deputation To</td>
											<td><?php echo $_POST['deputation_to']; ?></td>
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td>Post</td>
											<td><select class="form-control" name="appoint_post" id="appoint_post" tabindex="<?php echo $tab++; ?>" disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from dp_designation order by abs(sort_no)';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['appoint_post'])){
															if($_POST['appoint_post']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['designation'].'</option>';
													}
													?>
												</select></td>
											<td>Pay Band</td>
											<td><select class="form-control" name="pay_band_id" id="pay_band_id" tabindex="<?php echo $tab++; ?>" disabled>
														<option value="">--- Select ---</option>
														<?php
														$query = "select * from master_pay_band";
														$run = mysqli_query($db,$query);
														while($data = mysqli_fetch_array($run)){
															echo '<option value="'.$data['sno'].'" ';
															if(isset($_POST['pay_band_id'])){
																if($_POST['pay_band_id']==$data['sno']){
																	echo ' selected="Selected"';
																}
															}
															echo '>'.trim($data['band_name']).'</option>';
														}
														?>
														</select></td>
											<td>Pay Level</td>
											<td><select class="form-control" name="pay_level_id" id="pay_level_id" tabindex="<?php echo $tab++; ?>" disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_pay_level_grade ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['pay_level_id'])){
															if($_POST['pay_level_id']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['level_name']).'</option>';
													}
													?>
													</select></td>
											<td>Basic Pay</td>
											<td><select class="form-control" name="grade_pay_id" id="grade_pay_id" tabindex="<?php echo $tab++; ?>" onchange="onchangetype()" disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_pay_level_grade";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['grade_pay_id'])){
															if($_POST['grade_pay_id']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['basic_pay']).'</option>';
													}
													?>
													</select></td>
										</tr>
										<tr>
											<td>Joinning Date</td>
											<td><?php echo $_POST['joinning_date']; ?></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
										</tr>
									</table>
								</div>
								<div class="card ">
									<h3 class="bg-danger text-white mt-3">Employment details</h3>
									<?php
										for($i=1;$i<=$_POST['add_rows_id_work'];$i++){	
									?>
									<?php echo $i; ?>.
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr>
											<td>Promotion/ Type</td>
											<td width = "20%"><select class="form-control" name="pramotaion_type_<?php echo $i; ?>" id="pramotaion_type_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>"  disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_appointment_type  where type=2 ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['pramotaion_type_'.$i])){
															if($_POST['pramotaion_type_'.$i]==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['appo_type']).'</option>';
													}
													?>
												</select></td>
											<td>Order No.</td>
											<td><?php echo $_POST['order_no_'.$i]; ?></td>
										
											<td>Order Date</td>
											<td><?php echo $_POST['order_date_'.$i]; ?></td>
											<td>Appoint Division</td>
											<td><select class="form-control" name="place_<?php echo $i; ?>" id="place_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from uprnss_division order by division_name ASC';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['s_no'].'" ';
														if(isset($_POST['place_'.$i])){
															if($_POST['place_'.$i]==$data['s_no']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['division_name'].'</option>';
													}
													?>
												</select></td>
										</tr>
										<tr>
											<td>Post</td>
											<td><select class="form-control" name="post_<?php echo $i; ?>" id="post_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from dp_designation order by abs(sort_no)';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['post_'.$i])){
															if($_POST['post_'.$i]==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['designation'].'</option>';
													}
													?>
												</select></td>
											<td>Pay Band</td>
											<td><select class="form-control" name="pay_band_id_<?php echo $i; ?>" id="pay_band_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>"  disabled>
												<option value="">--- Select ---</option>
												<?php
												$query = "select * from master_pay_band";
												$run = mysqli_query($db,$query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['sno'].'" ';
													if(isset($_POST['pay_band_id_'.$i])){
														if($_POST['pay_band_id_'.$i]==$data['sno']){
															echo ' selected="Selected"';
														}
													}
													echo '>'.trim($data['band_name']).'</option>';
												}
												?>
												</select></td>
											<td>Pay Level</td>
											<td><select class="form-control" name="pay_level_id_<?php echo $i; ?>" id="pay_level_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>"  disabled>
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_pay_level_grade ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['pay_level_id_'.$i])){
															if($_POST['pay_level_id_'.$i]==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['level_name']).'</option>';
													}
													?>
													</select></td>
											<td>Basic Pay</td>
											<td><select class="form-control" name="grade_pay_id_<?php echo $i; ?>" id="grade_pay_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onchange="onchangetype()" disabled >
														<option value="">--- Select ---</option>
														<?php
														$query = "select * from master_pay_level_grade";
														$run = mysqli_query($db,$query);
														while($data = mysqli_fetch_array($run)){
															echo '<option value="'.$data['sno'].'" ';
															if(isset($_POST['grade_pay_id_'.$i])){
																if($_POST['grade_pay_id_'.$i]==$data['sno']){
																	echo ' selected="Selected"';
																}
															}
															echo '>'.trim($data['basic_pay']).'</option>';
														}
														?>
													</select></td>
										</tr>
										<tr>
										<td>Joinning Date</td>
										<td><?php echo $_POST['joinning_date_'.$i]; ?></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										</tr>
									</table>
									<?php }?>
									<div id="test_work"></div>
									<input type="hidden" name="add_rows_id_work" id="add_rows_id_work" value="<?php echo $_POST['add_rows_id_work']; ?>">
								</div>
							</div>
							<div style="overflow:auto;"  class="mx-auto">
								<div style="float:left;">
									<button type="button" class="btn btn-primary pull-right" id="prevBtn" onclick="nextPrev(-1)">&laquo; Previous</button>
									<button type="button" class="btn btn-primary pull-right" id="nextBtn" onclick="nextPrev(1)">Next &raquo;</button>
									<button type="submit" name="submit" id="submit_btn" class="btn btn-success pull-right" onclick="return confirm('Are you sure?');">Submit</button>
									<input type="hidden" id="id" name="id" value="<?php echo $_POST['id']; ?>">
								</div>
							</div>
						</div>
				</div>
			</div>
		</div>
	</form>
<script>
document.getElementById('page-select').addEventListener('change', function() {
    var selectedPage = this.value;

    // Use AJAX to fetch and replace the table data
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'your_data_fetching_script.php?page=' + selectedPage, true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('table-body').innerHTML = xhr.responseText;
        }
    };

    xhr.send();
});
</script>	


<!-- ... Your HTML code for displaying the table ... -->


	<script>
		var currentTab = 0; // Current tab is set to be the first tab (0)
		showTab(currentTab); // Display the current tab

		function showTab(n) {
		  // This function will display the specified tab of the form...
		  var x = document.getElementsByClassName("tab");
		  x[n].style.display = "block";
		  //... and fix the Previous/Next buttons:
		  if (n == 0) {
			document.getElementById("prevBtn").style.display = "none";
			document.getElementById("submit_btn").style.display = "none";
		  } else {
			document.getElementById("prevBtn").style.display = "inline";
		  }
		  if (n == (x.length - 1)) {
			// document.getElementById("nextBtn").innerHTML = "Submit";
			document.getElementById("nextBtn").style.display = "none";
			document.getElementById("submit_btn").style.display = "inline";
		  } else {
			document.getElementById("nextBtn").style.display = "inline";
			document.getElementById("submit_btn").style.display = "none";
		  }
		  //... and run a function that will display the correct step indicator:
		  fixStepIndicator(n)
		}

		function nextPrev(n) {
		  // This function will figure out which tab to display
		  var x = document.getElementsByClassName("tab");
		  // Exit the function if any field in the current tab is invalid:
		  if (n == 1 && !validateForm()) return false;
		  // Hide the current tab:
		  x[currentTab].style.display = "none";
		  // Increase or decrease the current tab by 1:
		  currentTab = currentTab + n;
		  // if you have reached the end of the form...
		  if (currentTab >= x.length) {
			// ... the form gets submitted:
			document.getElementById("employees_form").submit();
			return false;
		  }
		  // Otherwise, display the correct tab:
		  showTab(currentTab);
		}

		function validateForm() {
		  // This function deals with validation of the form fields
		  var x, y, i, valid = true;
		  x = document.getElementsByClassName("tab");
		  y = x[currentTab].getElementsByTagName("input");
		  if (valid) {
			document.getElementsByClassName("step")[currentTab].className += " finish";
		  }
		  return valid; // return the valid status
		}

		function fixStepIndicator(n) {
		  // This function removes the "active" class of all steps...
		  var i, x = document.getElementsByClassName("step");
		  for (i = 0; i < x.length; i++) {
			x[i].className = x[i].className.replace(" active", "");
		  }
		  //... and adds the "active" class on the current step:
		  x[n].className += " active";
		}
	</script>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

