<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;

list($start_year, $end_year) = explode('-', $_POST['attendance_session']);
$month = $_POST['for_month'];
if((int)$month >= 4 && (int)$month <= 12){
    $attendance_year = $start_year;
}else{
    $attendance_year = $end_year;
}
$attendance_date = date("Y-m-t", strtotime($attendance_year . '-' . $month . '-01'));

if(isset($_POST['submit'])){
		$qryemp='SELECT * FROM `employee` WHERE `company_id`="'.$_POST['company_id'].'" AND `working_status`!="1"';
			if($_POST['employee_category_id'] != ''){
    
                if($_POST['employee_category_id'] == '2'){
                    
                    $qryemp .= ' AND employee_category_id="'.$_POST['employee_category_id'].'"';
                    
                    $qryemp .= " AND agmt_ending_date IS NOT NULL 
                                 AND agmt_ending_date >= '$attendance_date' ";
                
                }else{
                    
                    $qryemp .= ' AND employee_category_id="'.$_POST['employee_category_id'].'"';
                    
                }    
            }
	//echo $qryemp;
		$resemp=execute_query($qryemp);
		$emp_sno = array();
		while($rowemp=mysqli_fetch_array($resemp)){
			 $emp_sno[] = $rowemp['sno'];
		}
		$sql_validate = 'SELECT * FROM `attendance_details` WHERE `company_id`="'.$_POST['company_id'].'" and   `emp_id` IN (' . implode(',', $emp_sno) . ') AND `financial_year`="'.$_POST['attendance_session'].'" AND `salary_generation_month`="'.$_POST['for_month'].'"';
// echo	$sql_validate;
		$result_validate = execute_query($sql_validate);
		$num_validate = mysqli_num_rows($result_validate);
	if($num_validate == 0){
		$sql='INSERT INTO `attendance_invoice`(`company_id`, `working_days`, `financial_year`, `salary_generation_month`, `granted_leave`, `created_by`, `creation_time`) VALUES ("'.$_POST['company_id'].'" , "'.$_POST['company_working_days'].'" , "'.$_POST['attendance_session'].'" , "'.$_POST['for_month'].'" , "'.$_POST['company_granted_days'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
		execute_query($sql);
		if(!mysqli_error($db)) {
			$msg = '<div class="alert alert-success">Data Saved</div>';
			$attendance_id = mysqli_insert_id($db);

			$qryemp='SELECT * FROM `employee` WHERE `company_id`="'.$_POST['company_id'].'" AND `working_status`!="1"';
			if($_POST['employee_category_id']!=''){
				$qryemp .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
			}
			$resemp=execute_query($qryemp);
			while($rowemp=mysqli_fetch_array($resemp)){
				
				$sql_insert = 'INSERT INTO `attendance_details`(`financial_year`, `salary_generation_month`, `attendance_id`, `company_id`, `emp_id`, `working_days`, `granted_leave`, `presented_days`, `created_by`, `creation_time`) VALUES ("'.$_POST['attendance_session'].'" , "'.$_POST['for_month'].'" , "'.$attendance_id.'" , "'.$_POST['company_id'].'" , "'.$rowemp['sno'].'" , "'.$_POST['working_days_'.$rowemp['sno']].'" , "'.$_POST['granted_leave_'.$rowemp['sno']].'" , "'.$_POST['present_'.$rowemp['sno']].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
				execute_query($sql_insert);
				//echo $sql_insert.'<br/>';
				if(mysqli_error($db)){
					$msg .= '<div class="alert alert-danger">Error # AE002 : '.mysqli_error($db).' >> '.$sql.'</div>';
				}
				else{
					$msg1 = '<div class="alert alert-success">Data Saved</div>';
				}
			}
			if($msg1!=""){
				$msg = '<div class="alert alert-success">Data Saved</div>';
			}
			
		} else {
			$msg = '<div class="alert alert-danger">Error # AH_001. '.mysqli_error($db).' >> '.$sql.'</div>';
		}
	}
	else{
		$msg = '<div class="alert alert-success">Attendance Alredy Submited</div>';
	}
}	
page_header_start('Attendance');
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>
	<div class="container">
		<?php echo $msg; ?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" >
			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-6">
					<div class="panel">
						<div class="panel-heading">Attendance</div>
						<div class="panel-body">
							<table class="table table-hover table-responsive table-bordered table-striped">

								<tr>
									<td>Select Division</td>
									<td>
										<select required  class="form-control"  name="company_id" id="company_id">
										<option value="">Select</option>
										<?php 
											$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
												$query = mysqli_query($db_erp, $sql);
											 while ($row = mysqli_fetch_array($query)){
											 	?>
										<option value="<?php echo $row['s_no']; ?>" <?php if(isset($_POST['calculate'])){if($_POST['company_id']==$row['s_no']){echo 'selected';}} ?>><?php echo $row['division_name']; ?></option>
											 	<?php
											}?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Employee Type</td>
									<td>
										<select required name="employee_category_id" id="employee_category_id" class="form-control" tabindex="<?php echo $tab++; ?>" >
										<?php 
											$sql_details = 'SELECT * FROM `dp_category`';
											$result_details = mysqli_query($db_erp, $sql_details);
											while($row_details = mysqli_fetch_array($result_details)){
												?>
										
										
										<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_POST['calculate'])){if($_POST['employee_category_id']==$row_details['sno']){echo 'selected';}} ?>><?php echo $row_details['category_name']; ?></option>
												<?php
											}
										?>
									</select>
									</td>
								</tr>
								<tr>
									<td>Attendace Session</td>
									<td>
										<select name="attendance_session" class="form-control">
											<?php  
												if(date('m')>3){
													$select_start_session = date('Y');
												}
												else{
													$select_start_session = date('Y')-1;
												}
												$session_start = date('Y')-50;
												for($i = $session_start; $i<=$session_start+100;$i++){
													$end_session = $i+1;
													?>
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['attendance_session'])){if($_POST['attendance_session']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
													<?php
												}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td>For Month</td>
									<td>
										<select class="form-control"  name="for_month" id="title">
											<?php
											$month=1;
											$current_month = date('m');

											while($month<=12){
												echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
												if(isset($_POST['for_month'])){
													if($_POST['for_month']==$month){
														echo 'selected="selected"';
													}
												}
												else{
													if($month == $current_month){
														echo 'selected="selected"';
													}
												}
												echo '>'.date('F',strtotime('01.'.$month.'.2020')).'</option>';
												$month++;
											}
											?>
										</select>
									</td>
								</tr>
								
							</table>
						</div>
					</div>
					<?php if(!isset($_POST['calculate'])){ ?>
					<input type="submit" class="form-control btn btn-info" id="calculate" name="calculate" value="Search">	
				<?php } ?>
				</div>
			</div>
			<div class="row"><div class="col-sm-12">&nbsp;</div></div>
			<div class="row">
				<div class="col-sm-12">
					<div class="panel">
						<div class="panel-heading">Result</div>
						<div class="panel-body" id="total_calculation">
							<?php
								if (isset($_POST['calculate'])) {
									$n = 0;
									$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_id'].'"';
									$result_company = mysqli_query($db_erp, $sql_company);
									$row_company = mysqli_fetch_array($result_company);
								?>
							<div class="row" style="background-color: lightblue;font-size: 15px;">
								<div class="col-sm-7" style="font-size: 25px;">
									<b><?php echo $row_company['division_name']; ?></b>
								</div>	
								<div class="col-sm-5"><b>Number Of Working List:</b>
									<input type="number" name="company_working_days" id="company_working_days" class="form-control" placeholder="Enter The Working Days" oninput="fill_info();">
								</div>
								<!-- <div class="col-sm-5"><b>Number Of Granted Leave:</b>
									<input type="number" name="company_granted_days" id="company_granted_days" class="form-control" placeholder="Enter The Granted Leave Days" onblur="fill_info();">
								</div> --->
							</div>
							<div class="row" style="font-size: 20px;text-align: center;">
								<div class="col-sm-1"><b>S.No.</b></div>
								<div class="col-sm-2"><b>Employee code</b></div>
								<div class="col-sm-3"><b>Employee Name</b></div>
								<div class="col-sm-2"><b>Working Days</b></div>
								
								<div class="col-sm-2"><b>Presented Days</b></div>
								<div class="col-sm-2"><b>Granted Leave</b></div>
							</div>
							<hr/>
								<?php
									$sql_employee = 'SELECT * FROM `employee` WHERE `company_id`="'.$_POST['company_id'].'" AND `working_status`!="1"';
									
									if($_POST['employee_category_id']!=''){
										if($_POST['employee_category_id'] == '2'){
											$sql_employee .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
											$sql_employee .= ' AND agmt_ending_date IS NOT NULL 
                                         AND agmt_ending_date >= "'.$attendance_date.'" ';
										}else{
											$sql_employee .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
											
										}	
									}
									
									$result_employee = execute_query($sql_employee);
								// 	echo $sql_employee;
									while($row_employee = mysqli_fetch_array($result_employee)){
										?>
										<div class="row">
											<div class="col-sm-1">
											<h4><b><?php echo ++$n; ?>.</b></h4></div>
											<div class="col-sm-2">
												<h4><?php echo $row_employee['employee_code']; ?></h4>
											</div>
											<div class="col-sm-3">
											
												<h4><?php echo $row_employee['employee_name']; ?></h4>
											</div>
											<div class="col-sm-2">
												<input  type="number" name="working_days_<?php echo $row_employee['sno']; ?>" id="working_days_<?php echo $n; ?>" class="form-control" placeholder="Enter The Working Days" readonly></div>
											<div class="col-sm-2">
												<input  type="number" oninput="fill_info2();" name="present_<?php echo $row_employee['sno']; ?>" id="present_<?php echo $n; ?>" class="form-control" placeholder="Enter The Presented Days"></div>
											<div class="col-sm-2"><input  type="number" name="granted_leave_<?php echo $row_employee['sno']; ?>" id="granted_leave_<?php echo $n; ?>" class="form-control" placeholder="Enter The Leave Days"></div>
											
										</div>
										<?php
									}
									?>
								<input type="hidden" id="nubmer_of_entry" name="nubmer_of_entry" value="<?php echo $n; ?>">
									<?php
								}
							?>
							
						</div>
						
						<input type="submit" class="form-control" id="submit" name="submit" value="Submit">
					</div>
				</div>
			</div>
		</form>
	</div>
	
	<script type="text/javascript">
	function fill_info() {
		var nubmer_of_entry = document.getElementById('nubmer_of_entry').value;
		var working_days = document.getElementById('company_working_days').value;

		if (working_days > 0) {
			for (var i = 1; i <= nubmer_of_entry; i++) {
				$('#working_days_'+i).val(working_days);
				$('#present_'+i).val(working_days);
			}
		}
		// if (granted_leave > 0) {
			// for (var i = 1; i <= nubmer_of_entry; i++) {
				// $('#granted_leave_'+i).val(granted_leave);
			// }
		// }
		if (working_days > 0) {
			for (var i = 1; i <= nubmer_of_entry; i++) {
				var leave_day = document.getElementById('working_days_' + i).value - document.getElementById('present_' + i).value; 
				console.log(leave_day);

				$('#granted_leave_' + i).val(leave_day);
			}
		}
	}
	
	function fill_info2(){
		var nubmer_of_entry = document.getElementById('nubmer_of_entry').value;
		// var working_days = document.getElementById('company_working_days').value;
		
		// var grantleave=parseInt(document.getElementById('granted_leave_edit').value);
		
			for (var i = 1; i <= nubmer_of_entry; i++) {
				// var workingday=parseInt(document.getElementById('working_days_'+i).value);
				// var present= parseInt(document.getElementById('present_'+i).value);
			
				if(parseInt(document.getElementById('present_'+i).value)>parseInt(document.getElementById('working_days_'+i).value)){
					document.getElementById('submit').style.height="0";
					alert("Present Days is greater than Working Days");
					
					
				}else{
					document.getElementById('submit').style.height="fit-content";
				}
				document.getElementById('granted_leave_'+i).value=document.getElementById('working_days_'+i).value-document.getElementById('present_'+i).value;
				
				
				
			}
		
	}
	
</script>
<?php
page_footer();
?>
