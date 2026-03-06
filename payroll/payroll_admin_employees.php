 <?php
include("scripts/settings.php");	 
$msg='';
$msg1='';
$tab=1;

if(isset($_POST['submit'])) {
	if($_POST['edit_sno'] != ''){
		$emp_id = $_POST['edit_sno'];
		if($_POST['working_status']==1){
			$working_condition = 'working_status= "1", exit_date="'.$_POST['exit_date'].'", remark = "'.$_POST['remark'].'", ';
		}elseif($_POST['working_status']==0){
			$working_condition = 'working_status= "0", exit_date="", remark = "", ';
		}else{
			$working_condition='';
		}
		
		$sql='UPDATE `employee` SET 
		`dp_name_id`="'.$_POST['dp_name_id'].'",
		`company_id`="'.$_POST['company_id'].'" , 
		`employee_category_id`="'.$_POST['employee_category_id'].'",
		`ehrms_id`="'.$_POST['ehrms_id'].'",
		`joining_date`="'.$_POST['joining_date'].'",
		`employee_code`="'.$_POST['employee_code'].'",
		`employee_name`="'.$_POST['employee_name'].'",
		`pay_level_id`="'.$_POST['pay_level_id'].'",
		`pan`="'.$_POST['pan'].'",
		`esic_no`="'.$_POST['esic_no'].'",
		`employee_designation`="'.$_POST['employee_des'].'",
		`aadhar`="'.$_POST['aadhar'].'",
		`acount_number`="'.$_POST['acount_number'].'",
		`bank_name`="'.$_POST['bank_name'].'",
		`branch`="'.$_POST['branch'].'",
		`ifsc_code`="'.$_POST['ifsc_code'].'",
		`agmt_started_date`="'.$_POST['agmt_started_date'].'",
		`agmt_ending_date`="'.$_POST['agmt_ending_date'].'",
		`agmt_order_no`="'.$_POST['agmt_order_no'].'",
		`salary_per`="'.$_POST['salary_per'].'",
		
		'.$working_condition.'
		`edition_time` = "'.date('Y-m-d h:i:s').'",
		`edited_by` = "'.$_SESSION['username'].'" 
		WHERE `sno`="'.$_POST['edit_sno'].'"';
		$res=execute_query($sql);

		if($res){
			$msg='<div class="alert alert-danger">Data Updated</div>';
			$sql_del= "DELETE FROM salary_structure WHERE `salary_structure`.`emp_id` = '".$_POST['edit_sno']."'";
			$result = execute_query($sql_del);
			
			// $sql="SELECT * FROM `head_type`";
			$sql="select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}'";
			$result=execute_query($sql);
			$rowcount=mysqli_num_rows($result);
			if($rowcount>0){
				while($row = mysqli_fetch_array($result)){
					$sno=$row['sno'];
					$head_val=$_POST['head_'.$sno];
					$sql="INSERT INTO `salary_structure` (`emp_id`, `head_id`, `head_value`) VALUES ('".$_POST['edit_sno']."', '$sno','$head_val')";
					//echo $sql.'<br>';
					execute_query($sql);
					if(mysqli_error($db)){
						$msg .= '<div class="alert alert-danger">Error # AE003 : '.mysqli_error($db).' >> '.$sql.'</div>';
					}
					else{
						$msg='<div class="alert alert-success">Data Successfuly Update</div>';
					}
				}
				if($_POST['dp_name_id']!="Not Applicable" && !empty($_POST['dp_name_id'])){
					$sql = ' update dp_personal_info set 
					division_id = "'.$_POST['company_id'].'",
					old_employee_code = "'.$_POST['employee_code'].'",
					employee_designation_id = "'.$_POST['employee_des'].'",
					aadhar_number = "'.$_POST['aadhar'].'",
					pan_number = "'.$_POST['pan'].'",
					bank_name = "'.$_POST['bank_name'].'",
					branch_name = "'.$_POST['branch'].'",
					ac_number = "'.$_POST['acount_number'].'",
					ifsc_code =  "'.$_POST['ifsc_code'].'",
					uan_no =  "'.$_POST['esic_no'].'"
				
					where sno="'.$_POST['dp_name_id'].'"';
					mysqli_query($db_erp, $sql);
					if(mysqli_error($db_erp)){
						$msg1 .= '<div class="alert alert-danger">Error # AE003 : '.mysqli_error($db_erp).' >> '.$sql.'</div>';
					}
					else{
						$msg1='<div class="alert alert-success">Update dp</div>';
					}
				}
			}
			
			else{
				// echo 'tsetsetest';
				goto newinsert;
			}
		}
		else{
			$msg .= '<div class="alert alert-danger">Error # AE001 '.mysqli_error($db).' >> '.$sql.'</div>';
		}
			
	}
	else{

		$sql = 'INSERT INTO `employee` (dp_name_id, employee_category_id, employee_code, ehrms_id, joining_date, `employee_name`, `pay_level_id`, `pan`, `esic_no`, `employee_designation`, `aadhar`, `acount_number`, `bank_name`,`branch`, `ifsc_code` , `company_id`,`agmt_started_date` , `agmt_ending_date` , `agmt_order_no` ,`salary_per` ,`exit_date` , `remark` ,`created_by` , `creation_time`, `working_status`) 
			VALUES ("'.$_POST['dp_name_id'].'", "'.$_POST['employee_category_id'].'", "'.$_POST['employee_code'].'", "'.$_POST['ehrms_id'].'", "'.$_POST['joining_date'].'", "'.$_POST['employee_name'].'", "'.$_POST['pay_level_id'].'","'.$_POST['pan'].'",  "'.$_POST['esic_no'].'",  "'.$_POST['employee_des'].'", "'.$_POST['aadhar'].'", "'.$_POST['acount_number'].'", "'.$_POST['bank_name'].'", "'.$_POST['branch'].'", "'.$_POST['ifsc_code'].'" , "'.$_POST['company_id'].'" , "'.$_POST['agmt_started_date'].'" , "'.$_POST['agmt_ending_date'].'" ,"'.$_POST['agmt_order_no'].'" ,"'.$_POST['salary_per'].'" ,"'.$_POST['exit_date'].'" , "'.$_POST['remark'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'" , "'.$_POST['working_status'].'")';
			execute_query($sql);
			if(mysqli_error($db)){
				$msg .= '<div class="alert alert-danger">Error # AE002 : '.mysqli_error($db).' >> '.$sql.'</div>';
			}
			else{
				$emp_id = mysqli_insert_id($db);
			}
			
			newinsert:
			// $sql="SELECT * FROM `head_type`";
			$sql="select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}'";
			$result=execute_query($sql);
			$rowcount=mysqli_num_rows($result);
			if($rowcount>0){
				while($row = mysqli_fetch_array($result)){
					$sno=$row['sno'];
					$head_val = isset($_POST['head']) ? $_POST['head'] : null;
					$sql="INSERT INTO `salary_structure` (`emp_id`, `head_id`, `head_value`) VALUES ('$emp_id', '$sno','$head_val')";
					//echo $sql.'<br>';
					execute_query($sql);
					if(mysqli_error($db)){
						$msg .= '<div class="alert alert-danger">Error # AE003 : '.mysqli_error($db).' >> '.$sql.'</div>';
					}
					else{
						$msg='<div class="alert alert-success">Data Successfuly Saved</div>';
					}
				}
				if($_POST['dp_name_id']!="Not Applicable" && !empty($_POST['dp_name_id'])){
					$sql = ' update dp_personal_info set 
						division_id = "'.$_POST['company_id'].'",
						old_employee_code = "'.$_POST['employee_code'].'",
						employee_designation_id = "'.$_POST['employee_des'].'",
						aadhar_number = "'.$_POST['aadhar'].'",
						pan_number = "'.$_POST['pan'].'",
						bank_name = "'.$_POST['bank_name'].'",
						branch_name = "'.$_POST['branch'].'",
						ac_number = "'.$_POST['acount_number'].'",
						ifsc_code =  "'.$_POST['ifsc_code'].'",
						uan_no =  "'.$_POST['esic_no'].'"
					
						where sno="'.$_POST['dp_name_id'].'"';
						mysqli_query($db_erp, $sql);
						if(mysqli_error($db_erp)){
							$msg1 .= '<div class="alert alert-danger">Error # AE003 : '.mysqli_error($db_erp).' >> '.$sql.'</div>';
						}
						else{
							$msg1 ='<div class="alert alert-success">Update dp</div>';
						}	
				}
		    }	
		//}
	}
}else{
	unset($_POST);
}
		 
// if(isset($_GET['edit_id'])){
	// $sql ="select * from `employee` where sno='".$_GET['edit_id']."'";
	// $result = execute_query($sql);
	// $editrow = mysqli_fetch_array($result);
	// $cat_ids=$editrow['employee_category_id'];
	// // echo $cat_ids;
	// $sql1="SELECT * FROM `salary_structure` WHERE `emp_id`='".$_GET['edit_id']."'";
	// $result1= execute_query($sql1);
	// $numrowcount=mysqli_num_rows($result1);
	// if($numrowcount != 0){
		// while($row1 = mysqli_fetch_array($result1)){			
			// $_POST['head_'.$row1['head_id']] = $row1['head_value'];
			// // echo print_r($row1);
		// }
	// }
// }


if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $sql = "SELECT * FROM `employee` WHERE sno='$edit_id'";
    $result = execute_query($sql);
    $editrow = mysqli_fetch_assoc($result);
	$cat_ids=$editrow['employee_category_id'];
    if ($editrow) {
        $working_status_value = isset($editrow['working_status']) ? $editrow['working_status'] : '0'; 
        $exit_date_value      = !empty($editrow['exit_date']) ? $editrow['exit_date'] : ''; 
        $remark_value         = isset($editrow['remark']) ? $editrow['remark'] : '';

        $_POST['working_status'] = $working_status_value;
        $_POST['exit_date']      = $exit_date_value;
        $_POST['remark']         = $remark_value;

        // Load salary_structure values into $_POST so head fields prefill if your form echoes $_POST
        $sql1 = "SELECT * FROM `salary_structure` WHERE `emp_id`='$edit_id'";
        $result1 = execute_query($sql1);
        $numrowcount = mysqli_num_rows($result1);
        if ($numrowcount != 0) {
            while ($row1 = mysqli_fetch_assoc($result1)) {
                $_POST['head_'.$row1['head_id']] = $row1['head_value'];
            }
        }
    } else {
        
        $_POST['working_status'] = '0';
        $_POST['exit_date'] = '';
        $_POST['remark'] = '';
    }
}

if(isset($_GET['del'])){
	$var=$_GET['del'];
	//echo $var;
	//0 for working and 1 for not-working.... 
	$sql ="UPDATE `employee` SET `working_status`='1' WHERE `sno`='$var'";
	$result = execute_query($sql);
	if ($result == true) {
		$msg='<div class="alert alert-success">Data Deleted !</div>';
	}
	else{
			$msg='<div class="alert alert-danger">Sorry !</div>';
	}
}

page_header_start('Employee Master');
page_header_end();
navigation($_SERVER['PHP_SELF']);


?>
<script>
function open_info(val){
	$(".modal-body").html(''); 
	$.ajax({
        url: "ajax.php?id=salary&term="+val,
        dataType:"json"
    })
    .done(function( data ) {
		var txt='<table class="stripped table col-sm-3">';
		$.each(data, function(key, value){
			console.log(value);
			txt += '<tr><td>'+value.label+'</td><td>'+value.head_value+'</td></tr>';
		});
		$(".modal-body").html(txt);             
    }); 
	$("#myModal").modal()
}

function working_condition(val){
	if(val=='0'){
		$("#exit_date_row").css("display", "none");
		$("#remark_row").css("display", "none");
	}
	else{
		$("#exit_date_row").css("display", "block");
		$("#remark_row").css("display", "block");
	}
}
</script>

	<script>
		 function onchangetype() {
            var type = document.getElementById("employee_category_id").value;

            if (type == "2") {
                document.getElementById("agrement_show").style.display = "flex";
                document.getElementById("extra_show").style.display = "none";
            } else if (type == "4") {
                document.getElementById("agrement_show").style.display = "none";
                document.getElementById("extra_show").style.display = "block";
            } else {
                document.getElementById("agrement_show").style.display = "none";
                document.getElementById("extra_show").style.display = "none";
            }
        }

	  window.onload = function() {
		onchangetype(); 
		document.getElementById("go_type").onchange = onchangetype;
	  };
	</script>
	
<style type="text/css">
	.col-sm-4{
		margin-top:8px;
	}
	#head_input_box{
		/* display:none; */
	}
</style>
	<div class="container">
		<form method="post" name="admin_employee_from" id="admin_employee_from" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="row">
				<?php echo $msg; ?>
				<?php echo $msg1; ?>
				<div class="panel">
					<div class="panel-heading">Employee Details</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-sm-4">
								Working Status:
								<select name="working_status" id="working_status" class="form-control" tabindex="<?php echo $tab++; ?>" onChange="working_condition(this.value);">
									<option value="0" <?php echo ((isset($_POST['working_status']) && (string)$_POST['working_status'] === '0') ? 'selected' : ''); ?>>Working</option>
									<option value="1" <?php echo ((isset($_POST['working_status']) && (string)$_POST['working_status'] === '1') ? 'selected' : ''); ?>>Left</option>
								</select>
							</div>

							<?php
							// Decide whether to show the exit_date and remark rows on page load (if editing)
							$show_exit = (isset($_POST['working_status']) && (string)$_POST['working_status'] === '1') ? 'block' : 'none';
							$exit_date_value = isset($_POST['exit_date']) && $_POST['exit_date'] !== '' ? $_POST['exit_date'] : date("Y-m-d");
							?>

							<div class="col-sm-4" id="exit_date_row" style="display: <?php echo $show_exit; ?>;">
								Leaving Date:
								<script type="text/javascript" language="javascript">
									// pass the saved exit date when editing, otherwise fallback to today
									document.writeln(DateInput('exit_date', 'admin_employee_form', true, 'YYYY-MM-DD', '<?php echo $exit_date_value; ?>', <?php echo $tab++; $tab+=3; ?>));
								</script>
							</div>

							<div class="col-sm-4" id="remark_row" style="display: <?php echo $show_exit; ?>;">
								<label>Reason</label>
								<select name="remark" id="remark" class="form-control">
									<option value="Select" <?php echo (isset($_POST['remark']) && $_POST['remark'] === 'Select') ? 'selected' : ''; ?>>-select-</option>
									<option value="Retired" <?php echo (isset($_POST['remark']) && $_POST['remark'] === 'Retired') ? 'selected' : ''; ?>>Retired</option>
									<option value="Death" <?php echo (isset($_POST['remark']) && $_POST['remark'] === 'Death') ? 'selected' : ''; ?>>Death</option>
									<option value="Deputation" <?php echo (isset($_POST['remark']) && $_POST['remark'] === 'Deputation') ? 'selected' : ''; ?>>Deputation</option>
									<option value="Left From Service" <?php echo (isset($_POST['remark']) && $_POST['remark'] === 'Left From Service') ? 'selected' : ''; ?>>Left From Service</option>
									<option value="Left Without Any Information" <?php echo (isset($_POST['remark']) && $_POST['remark'] === 'Left Without Any Information') ? 'selected' : ''; ?>>Left Without Any Information</option>
								</select>
							</div>

							<div class="col-sm-4">
								Digital profile Name:
								<select name="dp_name_id" id="dp_name_id" class="form-control" tabindex="<?php echo $tab++; ?>"  onChange="fill_emp_details(this.value)">
									<option value="">-SELECT-</option>
									<option value="Not Applicable">Not Applicable</option>
									<?php 
										$sql_details = 'SELECT * FROM `dp_personal_info` order by full_name ASC';
										$result_details = mysqli_query($db_erp, $sql_details);
										while($row_details = mysqli_fetch_array($result_details)){
									?>
										<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_GET['edit_id'])){if($row_details['sno']==$editrow['dp_name_id']){echo 'selected';}} ?>><?php echo $row_details['full_name']; echo $row_details['old_employee_code']; ?></option>
									<?php
									}
									?>
								</select>
							</div>
							<div class="col-sm-4">
								Employee code:   
								<input type="text" name="employee_code"  id="employee_code" value="<?php if(isset($_GET['edit_id'])){ echo  $editrow['employee_code']; }?>" class="form-control" id="usr"height="10px"  placeholder="" tabindex="<?php echo $tab++;?>" > 
							</div>
							<div class="col-sm-4">
								EHRMS ID:   
								<input type="text" name="ehrms_id"  id="ehrms_id" value="<?php if(isset($_GET['edit_id'])){ echo  $editrow['ehrms_id']; }?>" class="form-control" id="usr"height="10px"  placeholder="" tabindex="<?php echo $tab++;?>" > 
							</div>
							<div class="col-sm-4">
								Joining Date:   
								<input type="date" name="joining_date"  id="joining_date" value="<?php if(isset($_GET['edit_id'])){ echo  $editrow['joining_date']; }?>" class="form-control" id="usr"height="10px"  placeholder="" tabindex="<?php echo $tab++;?>" > 
							</div>
							<div class="col-sm-4">
								Division Name: 
								<select name="company_id" id="company_id" class="form-control" tabindex="<?php echo $tab++; ?>" onChange="fill_hra(document.getElementById('pay_level_id').value)">
									<option value="">-SELECT ANY ONE-</option>
									<?php 
										$sql_details = 'SELECT * FROM `uprnss_division`order by division_name ASC';
										$result_details = mysqli_query($db_erp, $sql_details);
										while($row_details = mysqli_fetch_array($result_details)){
											?>
									<option value="<?php echo $row_details['s_no']; ?>" <?php if(isset($_GET['edit_id'])){if($row_details['s_no']==$editrow['company_id']){echo 'selected';}} ?>><?php echo $row_details['division_name']; ?></option>
											<?php
										}
									?>
								</select>
							</div>
							<div class="col-sm-4">
								Employee Type:   
							
								<select name="employee_category_id" id="employee_category_id" class="form-control" tabindex="<?php echo $tab++; ?>" onchange="dynamicHead(this.value), onchangetype()" >
									<option value="">-SELECT-</option>
									<?php 
										$sql_details = 'SELECT * FROM `payroll_emp_category`';
										$result_details = mysqli_query($db,$sql_details);
										while($row_details = mysqli_fetch_array($result_details)){
											?>
									<option value="<?php echo $row_details['sno']; ?>"
									 <?php if(isset($_GET['edit_id'])){if($row_details['sno']==$editrow['employee_category_id']){echo 'selected';}} ?> >
									 <?php echo $row_details['category_name']; ?></option>
											<?php
										}
									?>
								</select>
							</div>
							
							<div class="col-sm-4">
								Employee Designation:   
								<select name="employee_des" id="employee_des" class="form-control" tabindex="<?php echo $tab++; ?>" >
									<option value="">-SELECT-</option>
									<?php 
										// $sql_details = 'SELECT * FROM `employee_designation`';
										$sql_details = 'select * from dp_designation order by abs(sort_no)';
										$result_details = mysqli_query($db_erp, $sql_details);
										while($row_details = mysqli_fetch_array($result_details)){
											?>
									<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_GET['edit_id'])){if($row_details['sno']==$editrow['employee_designation']){echo 'selected';}} ?>><?php echo $row_details['designation']; ?></option>
											<?php
										}
									?>
								</select>
							</div>
							<div class="col-sm-4">
								Employee Name:   
								<input type="text" name="employee_name"  id="employee_name"     value="<?php if(isset($_GET['edit_id'])){ echo  $editrow['employee_name']; }?>" class="form-control" id="usr"height="10px"  placeholder="" tabindex="<?php echo $tab++;?>" > 
							</div>
							<div class="col-sm-4">
								Pay Level:  
								<select  id="pay_level_id" name="pay_level_id" class="form-control" tabindex="<?php echo $tab++; ?>"  onChange="fill_hra(this.value)">
									<option value="">-SELECT-</option>
									<?php 
										$sql_details = 'SELECT * FROM `master_pay_level`';
										$result_details = mysqli_query($db_erp, $sql_details);
										while($row_details = mysqli_fetch_array($result_details)){
											?>
									<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_GET['edit_id'])){if($row_details['sno']==$editrow['pay_level_id']){echo 'selected';}} ?>><?php echo $row_details['level_name']; ?></option>
											<?php
										}
									?>
								</select>
							</div>
							
							<div class="col-sm-4">
								Aadhar No:
								<input type="text" name="aadhar" id="aadhar" value="<?php if(isset($_GET['edit_id'])){echo $editrow['aadhar']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								PAN No: 
								<input type="text" name="pan" id="pan"  value="<?php if(isset($_GET['edit_id'])){echo $editrow['pan']; }?>"class="form-control"  placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								UAN No:
								<input type="text" name="esic_no" id="esic_no" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['esic_no']; } ?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								Bank Name :   
								<input type="text" name="bank_name" id="bank_name"   id="" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['bank_name']; } ?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								Branch Name :
								<input type="text" name="branch" id="branch" value="<?php if(isset($_GET['edit_id'])){echo $editrow['branch']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								Ifsc code:  
								<input type="text" name="ifsc_code"  id="ifsc_code" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['ifsc_code']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								Account number:   
								<input type="text" name="acount_number"  id="acount_number" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['acount_number']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
						</div>
						
						<div class="row" id="agrement_show">
							<div class="col-sm-4">
								Agreement Started On:  
								<input type="date" name="agmt_started_date"  id="agmt_started_date" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['agmt_started_date']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								Agreement Ending On:   
								<input type="date" name="agmt_ending_date"  id="agmt_ending_date" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['agmt_ending_date']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
							<div class="col-sm-4">
								Order No:   
								<input type="text" name="agmt_order_no"  id="agmt_order_no" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['agmt_order_no']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>					
						</div>
						<div class="row" id="extra_show">
							<div class="col-sm-4">
								Salary Percentage:
								<input type="number" name="salary_per"  id="salary_per" value="<?php if(isset($_GET['edit_id'])){ echo $editrow['salary_per']; }?>"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">
							</div>
						</div>		
					</div>
				</div>
			</div>
			
			
			<div class="" id="head_input_box">
				<!-- <input type="text" name="new_cat_id" id="new_cat_id"> -->
				
			</div>
			
			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-6 ">
					<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['edit_id'])){echo $_GET['edit_id'];}?>">
					<input type="submit" id="submit" name="submit" class="form-control" value="Submit" tabindex="<?php echo $tab++;?>">
				</div>
			</div>
		</form>
		
	</div>

	<script>
				
		function dynamicHead(val,selected){

			// var cat_id=$("#employee_category_id").val();
			console.log(val);
			$.ajax({
				url:"payroll_admin_employees_ajax.php",
				type:"POST",
				data:{cat_id:val,edit_id:selected},
				success:function(data){
					$("#head_input_box").html(data);
				}
			});

		}
		
	
	<?php
	
		if(isset($_GET['edit_id'])){
	?>
			$(document).ready(function() {
				dynamicHead(<?php if(isset($_GET['edit_id'])){echo $cat_ids; }?>,<?php if(isset($_GET['edit_id'])){echo $_GET['edit_id']; }?>);
				
			}); 
	<?php
		}
	?>
	</script>

	<?php 
		if(isset($_POST['submit'])) {
	?>
			<div class="container-fluid">
			<div class="row">
				<div class="panel" style="margin-top:40px; overflow: visible;">
					<table class="table table-hover table-responsive table-bordered table-striped">
						<thead>
							<tr class="panel-heading">
								<th>Sno</th>
								<th>Emp Code</th>
								<th>Emp Name</th>
								<th>Emp Division</th>
								<th>Emp Designation</th>
								<th>Pay Level</th>
								<th>Aadhar</th>
								<th>PAN</th>
								<th>Acount no</th>
								<th>Bank name</th>
								<th>Branch Name</th>
								<th>ifsc code</th>
								<th>UAN No</th>
								<th>Edit</th>
								<th>Delete</th>

							</tr>
						</thead>
						<tbody>
							<?php 
								$sql="SELECT * FROM `employee` WHERE `working_status`!='1' AND `company_id`='".$_POST['company_id']."'";
								$result = execute_query($sql);
								//echo $sql;
								$i=1;
								 while($row=mysqli_fetch_array($result)){
									$sql = 'select * from uprnss_division where s_no="'.$row['company_id'].'"';
									$row_company = mysqli_query($db_erp, $sql);
									if(mysqli_num_rows($row_company)!=0){
										$row_company = mysqli_fetch_assoc($row_company);
									}
									else{
										unset($row_company);
										$row_company['division_name'] = '';
									}
									$sql_details = 'select * from dp_designation where sno="'.$row['employee_designation'].'"  order by abs(sort_no)';
									$row_designation = mysqli_query($db_erp, $sql_details);
									// $row_designation = execute_query($sql);
									if(mysqli_num_rows($row_designation)!=0){
										$row_designation = mysqli_fetch_assoc($row_designation);
									}
									else{
										unset($row_designation);
										$row_designation['designation'] = '';
									}
									$sql = 'select * from master_pay_level where sno="'.$row['pay_level_id'].'"';
									$row_level = mysqli_query( $db_erp ,$sql);
									if(mysqli_num_rows($row_level)!=0){
										$row_level = mysqli_fetch_assoc($row_level);
									}
									else{
										unset($row_level);
										$row_level['level_name'] = '';
									}
									 
									 echo '<tr>
									<th>'. $i++.'</th>
									<td>'. $row['ehrms_id'].'</td>
									<td>'. $row['employee_code'].'</td>
									<td>'. $row['employee_name'].'</td>
									<td>'. $row_company['division_name'].'</td>
									<td>'. $row_designation['designation'].'</td>
									<td>'. $row_level['level_name'].'</td>
									<td>'. $row["aadhar"].'</td>
									<td>'. $row["pan"].'</td>
									<td>'. $row["acount_number"].'</td>
									<td>'. $row["bank_name"].'</td>
									<td>'. $row["branch"].'</td>
									<td>'. $row["ifsc_code"].'</td>
									<td>'. $row["esic_no"].'</td>
								 
									<td><a href="payroll_admin_employees.php?edit_id='.$row["sno"].'"><span><i class="glyphicon glyphicon-pencil"></i></span></a></td>
									<td><a href="payroll_admin_employees.php?del='.$row["sno"].'"> <i class="glyphicon glyphicon-remove"></i></a></td>';
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php }?>
	
<?php
page_footer();
?>
<script type="text/javascript">
	
	
	var actionUrl = 'scripts/ajax.php';
	function fill_emp_details(val){
		var data = {"term":"b", "id":"emp_detail", "val":val,};
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(data){
				//data = data[0];
				// console.log(data);
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				data = data[0];
				console.log(data);
				$("#employee_name").val(data.full_name);
				$("#employee_code").val(data.old_employee_code);
				$("#company_id").val(data.division_id);
				$("#employee_category_id").val(data.employee_category_id);
				$("#employee_des").val(data.employee_designation_id);
				$("#aadhar").val(data.aadhar_number);
				$("#pan").val(data.pan_number);
				$("#acount_number").val(data.ac_number);
				$("#ifsc_code").val(data.ifsc_code);
				$("#esic_no").val(data.uan_no);
				$("#branch").val(data.branch_name);
				$("#bank_name").val(data.bank_name);
				
				
			}
		});
	}
	
	function fill_hra(val){
		var data = {"term":"b", "id":"hra", "val":val, "div":$("#company_id").val()};
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(data){
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				data = data[0];
				$("#head_6").val(data.hra);
				
			}
		});
	}
	
	
</script>