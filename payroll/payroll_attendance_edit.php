<?php
include("scripts/settings.php");
$msg='';
$tab=1;
if(isset($_POST['submit'])){
	if($_POST['edit_sno'] != ''){
		$sql_update = 'UPDATE `attendance_details` SET 
			`working_days`="'.$_POST['working_days_edit'].'" ,
			`granted_leave`="'.$_POST['granted_leave_edit'].'" ,
			`presented_days`="'.$_POST['present_edit'].'" ,
			`edited_by`="'.$_SESSION['username'].'" ,
			`edition_time`="'.date('Y-m-d h:i:s').'"
			WHERE `sno`="'.$_POST['edit_sno'].'"';
		$res = execute_query($sql_update);
		if($res){
			$msg = '<div class="alert alert-success">Attendance Updated</div>';
		}
	}
}
if (isset($_GET['e_id'])) {
	$sql_edit = 'SELECT * FROM `attendance_details` WHERE `sno`="'.$_GET['e_id'].'"';
	$row_edit = mysqli_fetch_array(execute_query($sql_edit));
}	
page_header_start('Attendance Edit');
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
									<td>Select Company</td>
									<td>
										<select required  class="form-control"  name="company_id" id="company_id" disabled>
										<option value="">Select</option>
										<?php 
											$sql = "SELECT * FROM `company_details`";	
											$query = execute_query($sql);
											 while ($row = mysqli_fetch_array($query)){
											 	?>
										<option value="<?php echo $row['sno']; ?>" <?php if(isset($_GET['e_id'])){if($row_edit['company_id']==$row['sno']){echo 'selected';}} ?>><?php echo $row['company_name']; ?></option>
											 	<?php
											}?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Attendace Session</td>
									<td>
										<select name="attendance_session" class="form-control" disabled>
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
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_GET['e_id'])){if($row_edit['financial_year']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
													<?php
												}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td>For Month</td>
									<td>
										<select class="form-control"  name="for_month" id="title" disabled>
											<?php
											$month=1;
											$current_month = date('m');

											while($month<=12){
												echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
												if(isset($_GET['e_id'])){
													if($row_edit['salary_generation_month']==$month){
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
				</div>
			</div>
			<div class="row"><div class="col-sm-12">&nbsp;</div></div>
			<div class="row">
				<div class="col-sm-12">
					<div class="panel">
						<div class="panel-heading">Result</div>
						<div class="panel-body" id="total_calculation">
							<?php
								if (isset($_GET['e_id'])) {
									$n = 0;
									$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$row_edit['company_id'].'"';
									$result_company = mysqli_query($db_erp,$sql_company);
									$row_company = mysqli_fetch_array($result_company);
								?>
							<div class="" style="background-color: lightblue;font-size: 15px;display:flex;justify-content:space-between;align-items:center;padding-inline:2rem;">
								<div class="w-25" style="font-size: 16px;">
									<b><?php echo $row_company['division_name']; ?></b>
								</div>
								<div class="w-25"><b>Number Of Working List:</b>
									<input type="number" name="company_working_days" id="company_working_days" class="form-control" placeholder="Enter The Working Days" value="<?php if(isset($_GET['e_id'])){echo $row_edit['working_days'];} ?>">
								</div>
								<!--<div class="col-sm-5"><b>Number Of Granted Leave:</b>
									<input type="number" name="company_granted_days" id="company_granted_days" class="form-control" placeholder="Enter The Granted Leave Days" onblur="fill_info();" value="<?php //if(isset($_GET['e_id'])){echo $row_edit['granted_leave'];} ?>">
								</div>-->
							</div>
							<div class="row" style="font-size: 20px;text-align: center;">
								<div class="col-sm-1"><b>S.No.</b></div>
								<div class="col-sm-2"><b>Employee Name</b></div>
								<div class="col-sm-3"><b>Working Days</b></div>
								<div class="col-sm-3"><b>Presented Days</b></div>
								<div class="col-sm-3"><b>Granted Leave</b></div>
							</div>
							<hr/>
								<?php
									$sql_employee = 'SELECT * FROM `employee` WHERE `sno`="'.$row_edit['emp_id'].'"';
									$result_employee = execute_query($sql_employee);
									//echo $sql_employee;
									while($row_employee = mysqli_fetch_array($result_employee)){
										?>
							<div class="row">
								<div class="col-sm-1"><h4><b><?php echo ++$n; ?>.</b></h4></div>
								<div class="col-sm-2"><h4><?php echo $row_employee['employee_name']; ?></h4></div>
								<div class="col-sm-3"><input type="number" name="working_days_edit" id="working_days_edit" class="form-control" placeholder="Enter The Working Days" value="<?php if(isset($_GET['e_id'])){echo $row_edit['working_days'];} ?>"></div>
								<div class="col-sm-3"><input type="number" name="present_edit" oninput="fill_info();" id="present_edit" class="form-control" placeholder="Enter The Presented Days" value="<?php if(isset($_GET['e_id'])){echo $row_edit['presented_days'];} ?>"></div>
							
								<div class="col-sm-3"><input type="number" name="granted_leave_edit" id="granted_leave_edit" class="form-control" placeholder="Enter The Leave Days" value="<?php if(isset($_GET['e_id'])){echo $row_edit['granted_leave'];} ?>"></div>
								</div>
										<?php
									}
									?>
								<input type="hidden" id="nubmer_of_entry" name="nubmer_of_entry" value="<?php echo $n; ?>">
									<?php
								}
							?>
							
						</div>
						<input type="hidden" name="edit_sno" id="edit_sno" value="<?php if(isset($_GET['e_id'])){echo $_GET['e_id'];} ?>">
						<input type="submit" class="form-control" id="submit" name="submit" value="Submit">
					</div>
				</div>
			</div>
		</form>
	</div>
<?php
page_footer();
?>
<script>
	function fill_info() {
		var grantleave=parseInt(document.getElementById('granted_leave_edit').value);
		var workingday=parseInt(document.getElementById('working_days_edit').value);
		var present= parseInt(document.getElementById('present_edit').value);
		// console.log(present);
		document.getElementById('granted_leave_edit').value=document.getElementById('working_days_edit').value-document.getElementById('present_edit').value;
		if(present>workingday){
			alert("Present Days is greater than Working Days");
			document.getElementById('submit').style.display="none";
			
		}else{
			document.getElementById('submit').style.display="block";
		}
			
				
	
		
	}
	
			
</script>