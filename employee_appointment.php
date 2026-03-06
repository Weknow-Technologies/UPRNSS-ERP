<?php
include("scripts/settings.php");
 
$msg='';
$error_msg='';
$msg1='';
$tab=1;
// print_r($_POST);
if(isset($_POST['submit'])){
		$emp_id= $_POST['employee_id'];
		if($_POST['edit']==''){
			$emp_id= $_POST['employee_id'];
			
			$id = mysqli_insert_id($db);
			for($i=1; $i<=$_POST['add_rows_id_work'] &&$_POST['pramotaion_type_'.$i]!='';  $i++){
				$sql = 'insert into employee_appointment (employee_id, appo_id, appointment_no, appointment_date, appoint_division, post, pay_band_id, grade_pay_id, pay_level_id, joinning_date, status, created_by, creation_time, pay_scale) values ("'.$emp_id.'", "'.$_POST['pramotaion_type_'.$i].'", "'.$_POST['order_no_'.$i].'","'.$_POST['order_date_'.$i].'", "'.$_POST['place_'.$i].'","'.$_POST['post_'.$i].'", "'.$_POST['pay_band_id_'.$i].'", "'.$_POST['grade_pay_id_'.$i].'", "'.$_POST['pay_level_id_'.$i].'", "'.$_POST['joinning_date_'.$i].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'", "'.$_POST['pay_scale_'.$i].'")';
				//echo $sql;
				execute_query($sql);
				if(mysqli_error($db)){ 
					$error_msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}else{
					$msg .= '<div class="alert alert-danger">Promotion  Add </div>';
				}
			}
		}
		
		else{
			$sql = 'update employee_appointment set
			employee_id="'.$_POST['employee_id'].'", 
			appo_id="'.$_POST['pramotaion_type_1'].'", 
			appointment_no="'.$_POST['order_no_1'].'", 
			appointment_date="'.$_POST['order_date_1'].'", 
			appoint_division="'.$_POST['place_1'].'",  
			post="'.$_POST['post_1'].'",  
			pay_scale="'.$_POST['pay_scale_1'].'",
			pay_band_id="'.$_POST['pay_band_id_1'].'",
			pay_level_id="'.$_POST['pay_level_id_1'].'",
			grade_pay_id="'.$_POST['grade_pay_id_1'].'",
			joinning_date="'.$_POST['joinning_date_1'].'",
			deputation_dep_name="'.$_POST['deputation_dep_name_1'].'",
			deputation_from="'.$_POST['deputation_from_1'].'",
			deputation_to="'.$_POST['deputation_to_1'].'",
			
			edited_by="'.$_SESSION['usersno'].'", 
			edition_time="'.date("Y-m-d H:i:s").'"		
			where sno="'.$_POST['edit'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<div class="alert alert-danger">Update successfully</div>';
			}
		}
		
			$sql = 'select * from employee_appointment where employee_id="'.$_POST['employee_id'].'" order by joinning_date DESC limit 1';
			// echo $sql;
			$row_data = mysqli_fetch_assoc(execute_query($sql));
			
			if($row_data['post']!=''){
				$sql = 'update dp_personal_info set
					employee_designation_id="'.$row_data['post'].'" 		
					where sno="'.$emp_id.'"';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$error_msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
			}	
			if($row_data['appo_id']!='8'){
				$sql = 'update dp_personal_info set
				appointment_id="'.$row_data['appo_id'].'"
				where sno="'.$emp_id.'"';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$error_msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
			}
			if($error_msg==''){
				$msg .= '<div class="alert alert-danger">Update Post</div>';
			}
}
else{
		$_POST['grade_id'] = '';
		$_POST['designation_id'] = '';
		$_POST['employee_id'] = '';
		
		$_POST['appo_id'] = '';
		$_POST['appointment_no'] = '';
		$_POST['appointment_date'] = '';
		$_POST['appoint_division'] = '';
		$_POST['appoint_post'] = '';
		$_POST['joinning_date'] = '';
		
		$_POST['pay_scale_1'] = '';
		$_POST['pramotaion_type_1'] = '';
		$_POST['order_no_1'] = '';
		$_POST['order_date_1'] = '';
		$_POST['place_1'] = '';
		$_POST['post_1'] = '';
		$_POST['joinning_date_1'] = '';
		$_POST['add_rows_id'] = 1;
		
		$_POST['deputation_dep_name'] = '';
		$_POST['deputation_from'] = '';
		$_POST['deputation_to'] = '';
		$_POST['add_rows_id_work'] = 1;
		
		$_POST['edit'] = '';
		// $_GET['edit'] = '';
		
	}
if(isset($_GET['edit'])){
	if($_GET['edit']!=''){
		$sql = 'select * from employee_appointment where sno="'.$_GET['edit'].'"';
		$data = mysqli_fetch_assoc(execute_query($sql));
		$_POST['employee_id'] = $data['employee_id'];
		$_POST['pramotaion_type_1'] = $data['appo_id'];
		$_POST['order_no_1'] = $data['appointment_no'];
		$_POST['order_date_1'] = $data['appointment_date'];
		$_POST['place_1'] = $data['appoint_division'];
		$_POST['post_1'] = $data['post'];
		$_POST['pay_scale_1'] = $data['pay_scale'];
		$_POST['pay_band_id_1'] = $data['pay_band_id'];
		$_POST['pay_level_id_1'] = $data['pay_level_id'];
		$_POST['grade_pay_id_1'] = $data['grade_pay_id'];
		$_POST['joinning_date_1'] = $data['joinning_date'];
		
		$_POST['deputation_dep_name_1'] = $data['deputation_dep_name'];
		$_POST['deputation_from_1'] = $data['deputation_from'];
		$_POST['deputation_to_1'] = $data['deputation_to'];
		
		$_POST['edit'] = $data['sno'];
		}  
	else{
		$_POST['employee_id'] = '';
		$_POST['appo_id_1'] = '';
		$_POST['appointment_no_1'] = '';
		$_POST['appointment_date_1'] = '';
		$_POST['appoint_division_1'] = '';
		$_POST['post_1'] = '';
		$_POST['joinning_date_1'] = '';
		$_POST['edit'] = '';
	}
}

if(isset($_GET['del'])){
	$sql = 'delete from employee_appointment where sno="'.$_GET['del'].'"';
	execute_query($sql);
	// $msg1 .= '<p class="text text-danger">Data Deleted.</p>';
	$msg1 .= '<div class="alert alert-danger">Data Deleted</div>';
}


page_header_start();

?>
<script src="js/krutidev.js"></script>
<script src="js/unicode_keyboard.js"></script>
<style>
	#project_name_hindi{
		font-family: 'Kruti Dev 010';
		font-size: 20px;
	}
	textarea{
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
	}
</style>

<?php
page_header_end();
page_sidebar();

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

   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
   
        <div class="row " >
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
                    	<div class="row">
							<div class="col-md-3 ">
								<div class="form-group">
									<label >Grade</label><br>
									<select class="form-control" name="grade_id" id="grade_id" tabindex="<?php echo $tab++; ?>" onChange="fill_designation(this.value)">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from dp_type order by type_name ASC";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['grade_id'])){
												if($_POST['grade_id']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['type_name']).'</option>';
										}
										?>
									</select>
								</div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Designation</label>
                                    <select class="form-control" name="designation_id" id="designation_id" value="<?php echo $_POST['designation_id']; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_employee(this.value)">
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Employee Name</label>
                                    <select class="form-control" name="employee_id" id="employee_id" value="<?php echo $_POST['employee_id']; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_emp_details(this.value)">
									</select>
                                </div>
                            </div>
                    								
                        </div>
						
						<div class="card tab mt-1 pt-1" <?php if(isset($_GET['edit'])){ echo 'style="display:none"';} ?> >
							<h4 class="bg-danger text-white text-center mt-1">Last Details</h4>
							<div class="border rounded m-2 p-2 border-secondary" id="">
								<div id="add_rows_length" class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label>Appointment Type</label>
											<select class="form-control" name="appo_id" id="appo_id" tabindex="<?php echo $tab++; ?>" onchange="onchangetype()" >
											<option value="">--- Select ---</option>
											<?php
											$query = "select * from master_appointment_type";
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
											</select>
										</div>
									</div>
									<div id="hide" style="display:<?php echo $_POST['appo_id']==5?'flex':'none'; ?>;">
										<div class="col-md-4" >
											<div class="form-group">
												<label >Department Name</label>
												<input type="text" name="deputation_dep_name" id="deputation_dep_name" class="form-control" value="<?php echo $_POST['deputation_dep_name']; ?>" tabindex="<?php echo $tab++; ?>">
											</div>
										</div>
										<div class="col-md-4"  >
											<div class="form-group">
												<label >Deputation From </label>
											   <input type="date" name="deputation_from" id="deputation_from" class="form-control" value="<?php echo $_POST['deputation_from']; ?>" tabindex="<?php echo $tab++; ?>">
											</div>
										</div>
										<div class="col-md-4" >
											<div class="form-group">
												<label >Deputation To</label>
											   <input type="date" name="deputation_to" id="deputation_to" class="form-control" value="<?php echo $_POST['deputation_to']; ?>" tabindex="<?php echo $tab++; ?>">
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label >Appointment No.</label>
											<input type="text" name="appointment_no" id="appointment_no" class="form-control" placeholder="" value="<?php echo $_POST['appointment_no']; ?>" tabindex="<?php echo $tab++; ?>">
										</div>
									</div>
									<div class="col-3">
										<div class="form-group">
											<label >Appointment Date</label>
											<input type="date" name="appointment_date" id="appointment_date" class="form-control" placeholder="" value="<?php echo $_POST['appointment_date']; ?>" tabindex="<?php echo $tab++; ?>">
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label >Appoint Division</label>
											<select class="form-control" name="appoint_division" id="appoint_division" tabindex="<?php echo $tab++; ?>">
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
										</div>          
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label >Post </label>
											<select class="form-control" name="appoint_post" id="appoint_post" tabindex="<?php echo $tab++; ?>">
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
										</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Pay Scale</label>
											<select name="pay_scale" id="pay_scale" class="form-control" onChange="fill_payband(this.value, 'pay_band_id')" tabindex="<?php echo $tab++; ?>">
												<option value="">--- Select ---</option>
												<option value="1" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 1 ? 'selected' : ''); ?>>5th Pay</option>
												<option value="2" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 2 ? 'selected' : ''); ?>>6th Pay</option>
												<option value="3" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 3 ? 'selected' : ''); ?>>7th Pay</option>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label >Pay Band</label>
											<select class="form-control" name="pay_band_id" id="pay_band_id" onChange="fill_paylevel(this.value, 'pay_level_id')" tabindex="<?php echo $tab++; ?>" >
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
											</select>
										</div>          
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Pay Level</label>
											<select class="form-control" name="pay_level_id" id="pay_level_id" onChange="fill_basicpay(this.value, 'grade_pay_id')" tabindex="<?php echo $tab++; ?>" >
											<option value="">--- Select ---</option>
											<?php
											$query = "select * from master_pay_level ";
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
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Basic Pay</label>
											<select class="form-control" name="grade_pay_id" id="grade_pay_id" tabindex="<?php echo $tab++; ?>" >
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
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label >Joinning Date</label>
											<input type="date" name="joinning_date" id="joinning_date" class="form-control" placeholder="" value="<?php echo $_POST['joinning_date']; ?>" tabindex="<?php echo $tab++; ?>">
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php if(isset($_GET['edit'])){ 
							$sql = 'select * from employee_appointment where sno="'.$_GET['edit'].'"';
							$data = mysqli_fetch_assoc(execute_query($sql));
							// echo$data['employee_id'];
						
						echo '<input type="hidden" name="employee_id" id="employee_id" class="form-control" placeholder="" value="'.$data['employee_id'].'" tabindex="<?php echo $tab++; ?>">';
						
						} ?>
						
						<?php
									
						for($i=1;$i<=$_POST['add_rows_id_work'];$i++){
							
						?>
						<?php echo $i; ?>.
						<div class="border rounded m-2 p-2 border-secondary" id="">
							<div id="add_rows_length" class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label>Promotion/ Type</label>
										
										<select class="form-control" name="pramotaion_type_<?php echo $i; ?>" id="pramotaion_type_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" >
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
									</select>
									</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label >Order No.</label>
										<input type="text" name="order_no_<?php echo $i; ?>" id="order_no_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['order_no_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
									</div>
								</div>
								<div class="col-3">
									<div class="form-group">
										<label >Order Date</label>
										<input type="date" name="order_date_<?php echo $i; ?>" id="order_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['order_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label >Appoint Division</label>
										<select class="form-control" name="place_<?php echo $i; ?>" id="place_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
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
									</select>
									</div>          
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label >Post </label>
										<select class="form-control" name="post_<?php echo $i; ?>" id="post_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
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
									</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Pay Scale</label>
										<select name="pay_scale_<?php echo $i; ?>" id="pay_scale_<?php echo $i; ?>" class="form-control" onChange="fill_payband(this.value, 'pay_band_id_<?php echo $i; ?>')" tabindex="<?php echo $tab++; ?>">
											<option value="">--- Select ---</option>
											<option value="1" <?php echo (isset($_POST['pay_scale_'.$i]) && $_POST['pay_scale_'.$i] == 1 ? 'selected' : ''); ?>>5th Pay</option>
											<option value="2" <?php echo (isset($_POST['pay_scale_'.$i]) && $_POST['pay_scale_'.$i] == 2 ? 'selected' : ''); ?>>6th Pay</option>
											<option value="3" <?php echo (isset($_POST['pay_scale_'.$i]) && $_POST['pay_scale_'.$i] == 3 ? 'selected' : ''); ?>>7th Pay</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label >Pay Band</label>
										<select class="form-control" name="pay_band_id_<?php echo $i; ?>" id="pay_band_id_<?php echo $i; ?>" onChange="fill_paylevel(this.value, 'pay_level_id_<?php echo $i; ?>')" tabindex="<?php echo $tab++; ?>" >
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
											</select>
									</div>          
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Pay Level</label>
										<select class="form-control" name="pay_level_id_<?php echo $i; ?>" id="pay_level_id_<?php echo $i; ?>" onChange="fill_basicpay(this.value, 'grade_pay_id_<?php echo $i; ?>')" tabindex="<?php echo $tab++; ?>" >
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from master_pay_level ";
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
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Basic Pay</label>
										<select class="form-control" name="grade_pay_id_<?php echo $i; ?>" id="grade_pay_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" >
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
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label >Joinning Date</label>
										<input type="date" name="joinning_date_<?php echo $i; ?>" id="joinning_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['joinning_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
									</div>
								</div>
								<div class="col-md-1 d-flex justify-content- align-items-center" >
									<button type="button" id="add_button_work" class="btn btn-info pull-right" onClick="add_rows_work()">Add</button>
								</div>
							</div>
						</div>
						<?php }?>
						<div id="test_work"></div>
						<input type="hidden" name="add_rows_id_work" id="add_rows_id_work" value="<?php echo $_POST['add_rows_id_work']; ?>">
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
									<input type="hidden" id="id" name="id" value="1">
									<input type="hidden" id="edit" name="edit" value="<?php echo $_POST['edit']; ?>">
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </form>
	
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
					<?php echo $msg1; ?>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
						<tr>
						<th>S.No.</th>
						<th>Employee Name</th>
						<th>Appointment Type</th>
						<th>Appoint Post</th>
						<th>Appointment No.</th>
						<th>Appointment Date</th>
						<th>Appointment Division</th>
						<th class="no-print text-center">Edit</th>
						<th class="no-print text-center">Delete</th>
						</tr>
						<tr>
							<?php
							for($i=1;$i<=7;$i++){
								echo '<th>'.$i.'</th>';
							}
							?>
							<th class="no-print text-center">8</th>
							<th class="no-print text-center">9</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
							// if(isset($_POST['search'])){
								if($_SESSION['usertype']=="sadmin"){
									$sql ='select * from employee_appointment where 1=1';
								}else{
									$sql ='select * from employee_appointment where appo_id IN(6,7,8) and  created_by="'.$_SESSION['usersno'].'"';
								}
								
							
								// if($_POST['employee_id']!=''){
									// $sql .= ' and employee_id="'.$_POST['employee_id'].'"';
								// }
								
								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									$sql = 'select * from dp_personal_info where sno="'.$row['employee_id'].'"';
									$emp_name = execute_query($sql);
									if(mysqli_num_rows($emp_name)!=0){
										$emp_name = mysqli_fetch_assoc($emp_name);
									}
									else{
										unset($emp_name);
										$emp_name['full_name'] = '';
									}
									$sql = 'select * from uprnss_division where s_no="'.$row['appoint_division'].'"';
									$div = execute_query($sql);
									if(mysqli_num_rows($div)!=0){
										$div = mysqli_fetch_assoc($div);
									}
									else{
										unset($div);
										$div['division_name'] = '';
									}
									$sql = 'select * from master_appointment_type where sno="'.$row['appo_id'].'"';
									$type = execute_query($sql);
									if(mysqli_num_rows($type)!=0){
										$type = mysqli_fetch_assoc($type);
									}
									else{
										unset($type);
										$type['appo_type'] = '';
									}
									$sql = 'select * from dp_designation where sno="'.$row['post'].'"';
									$alloted_post = execute_query($sql);
									if(mysqli_num_rows($alloted_post)!=0){
										$alloted_post = mysqli_fetch_assoc($alloted_post);
									}
									else{
										unset($alloted_post);
										$alloted_post['designation'] = '';
									}

									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$emp_name['full_name'].'</td>
									<td>'.$type['appo_type'].'</td>
									<td>'.$alloted_post['designation'].'</td>
									<td>'.$row['appointment_no'].'</td>
									<td>'. date('d-m-Y', strtotime($row['appointment_date'])).'</td>
									<td>'.$div['division_name'].'</td>
									<td class="no-print"><a href="'.$_SERVER['PHP_SELF'].'?edit='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit"><span class="far fa-edit" aria-hidden="true"></span></a></td>
									<td class="no-print"><a href="employee_appointment.php?del='.$row['sno'].'" onClick="return confirm(\'Are you sure? \');" > <span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span> </a></td>
									</tr>'; 
								}
							// }				
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
	
	var actionUrl = 'scripts/ajax.php';
	function fill_payband(val, element_id, selected=''){
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"payband", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.band_name+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}
	function fill_paylevel(val, element_id, selected=''){
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"paylevel", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.level_name+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}	
	function fill_basicpay(val, element_id, selected=''){
		console.log(val);
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"basicpay", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.basic_pay+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}	
	</script>	
		<script>
	
		
			function add_rows_work(){
				var id = parseFloat($("#add_rows_id_work").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = id+'.<div class="border rounded m-2 p-2 border-secondary" id=""><div id="add_rows_length" class="row"><div class="col-md-3"><div class="form-group"><label>Appointment Type</label><select class="form-control" name="pramotaion_type_'+id+'" id="pramotaion_type_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from master_appointment_type where type=2";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['appo_type'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-3"><div class="form-group"><label >Order No.</label><input type="text" name="order_no_'+id+'" id="order_no_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-3"><div class="form-group"><label >Order Date</label><input type="date" name="order_date_'+id+'" id="order_date_'+id+'" class="form-control" placeholder="" value="<" tabindex=""></div></div><div class="col-md-3 "><div class="form-group"><label>Appointment Division</label><select class="form-control" name="place_'+id+'" id="place_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from uprnss_division order by division_name ASC';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['s_no'].'\'>'.$data['division_name'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3 "><div class="form-group"><label>post</label><select class="form-control" name="post_'+id+'" id="post_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from dp_designation order by abs(sort_no)';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['designation'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3"><div class="form-group"><label>Pay Scale</label><select name="pay_scale_'+id+'" id="pay_scale_'+id+'" class="form-control" onChange="fill_payband(this.value, \'pay_band_id_'+id+'\')" ><option value="">--- Select ---</option><option value="1" >5th Pay</option><option value="2" >6th Pay</option><option value="3" >7th Pay</option></select></div></div><div class="col-md-3 "><div class="form-group"><label>Pay Band</label><select class="form-control" name="pay_band_id_'+id+'" id="pay_band_id_'+id+'" onChange="fill_paylevel(this.value, \'pay_level_id_'+id+'\')" ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from master_pay_band ';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['band_name'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3 "><div class="form-group"><label>Pay Level</label><select class="form-control" name="pay_level_id_'+id+'" id="pay_level_id_'+id+'" onChange="fill_basicpay(this.value, \'grade_pay_id_'+id+'\')" ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from master_pay_level ';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['level_name'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3 "><div class="form-group"><label>Basic Pay</label><select class="form-control" name="grade_pay_id_'+id+'" id="grade_pay_id_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from master_pay_level_grade ';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['basic_pay'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3"><div class="form-group"><label >Joinning Date</label><input type="date" name="joinning_date_'+id+'" id="joinning_date_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button_work" class="btn btn-info pull-right" onClick="add_rows_work()">Add</button></div></div></div>';
				$("#test_work").append(txt);
				$("#add_rows_id_work").val(id);
			}
			
						
		</script>	
<script>

$('select[multiple]').multiselect({
	search: true
});
	
$(document).ready( function () {
    /*$('#general_stat_table').DataTable({
		paging: false,
		fixedHeader: true,
		colReorder: true
		});
	});	*/

	
	var t = $('#general_stat_table').DataTable({
		paging: false
    });
 
    
});

var actionUrl = 'scripts/ajax.php';
function fill_designation(val, selected=''){
	var data = {"term":"b", "id":"designation", "val":val};

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
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.designation+'</option>';
				
			});
          	$("#designation_id").html(txt);
        }
    });
}
function fill_employee(val, selected=''){
	var data = {"term":"b", "id":"employee", "val":val};

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
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.emp_code + ' ' + value.full_name + '</option>';
				
			});
          	$("#employee_id").html(txt);
        }
    });
}
function fill_emp_details(val){
	var data = {"term":"b", "id":"emp_detail", "val":val};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
          	$("#appo_id").val(data.appo_id);
          	$("#appointment_no").val(data.appointment_no);
          	$("#appointment_date").val(data.appointment_date);
          	$("#appoint_division").val(data.appoint_division);
          	$("#appoint_post").val(data.post);
          	$("#pay_scale").val(data.pay_scale);
          	$("#pay_band_id").val(data.pay_band_id);
          	$("#pay_level_id").val(data.pay_level_id);
          	$("#grade_pay_id").val(data.grade_pay_id);
          	$("#joinning_date").val(data.joinning_date);
          	$("#deputation_dep_name").val(data.deputation_dep_name);
          	$("#deputation_from").val(data.deputation_from);
          	$("#deputation_to").val(data.deputation_to);
          	
        }
    });
}

<?php
	if(isset($_GET['eid'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>  );
		
	});
	
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>