<?php
include("scripts/settings.php");
page_header_start('Leave Payslip');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$deduction=0;
$income=0;
$grand_total=0;
$var=1;
$emp_id='';
$msg='';
$msg12='';



?>
<style>
#payroll input{
	margin:0px !important;
	padding:0px !important;
	text-align:center !important;
	font-size:11px !important;
	
}
#payroll td, #payroll th{
	margin:0px!important;
	padding:0px!important;
	font-size:11px;
	/* border: 1px solid black!important;*/
}

</style>
	<div class="container">
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
			<div class="row">
				<div class="col-sm-12"><?php echo $msg;?></div>
			</div>
			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-6">
					<div class="panel">
						<div class="panel-heading">PaySlip</div>
						<div class="panel-body">
							<table class="table table-hover table-responsive table-bordered table-striped">
								<tr>
									<td>Select Division</td>
									<td>
										<select required  class="form-control"  name="company_id" id="company_id">
										<option value="">--Select--</option>
										<?php 
											$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
											$query = mysqli_query($db_erp, $sql);
											 while ($row = mysqli_fetch_array($query)){
												?>
										<option value="<?php echo $row['s_no']; ?>" <?php if(isset($_POST['search'])){if($_POST['company_id']==$row['s_no']){echo 'selected';}} ?>><?php echo $row['division_name']; ?></option>
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
												$sql_details = 'SELECT * FROM `payroll_emp_category`';
												$result_details = mysqli_query($db,$sql_details);
												while($row_details = mysqli_fetch_array($result_details)){
													?>
														<option value="<?php echo $row_details['sno']; ?>" <?php if(isset($_POST['search'])){if($_POST['employee_category_id']==$row_details['sno']){echo 'selected';}} ?>><?php echo $row_details['category_name']; ?></option>
													<?php
												}
											?>
										</select>
										</td>
									</tr>
								<tr>
									<td>Financial Session</td>
									<td>
										<select name="financial_session" class="form-control">
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
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['financial_session'])){if($_POST['financial_session']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
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
												if(isset($_POST['search'])){
													if($month==$_POST['for_month']){
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
							<?php //if(!isset($_POST['search'])){ ?>
							
							<input type="submit" name="search" value="Search" id="search" class="form-control btn btn-info">
							<?php //} ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
		<?php 
			if(isset($_POST['search'])){
		?>
	<form method="POST" action="payroll_payslip_leave_salary_genrate.php" enctype="multipart/form-data">
		<div class="container">	
			<div class="panel" style="margin-top:40px; margin-left:40px; margin-right:40px; ">
				<div class="panel-body">
					<table class="table table-hover table-responsive table-bordered table-striped text-center" id="" style="">
						<thead>
							<tr class="panel-heading">
								<th>Sno.</th>
								<th>Employee code</th>
								<th>Employee Division</th>
								<th>Employee Name</th>
								<th>Granted Leave</th>
								<th>Number of Days create Salary</th>
								<?php
								echo'<input type="hidden" name="company_id" value="'. $_POST["company_id"].'" id="" >
									<input type="hidden" name="financial_session" value="'. $_POST["financial_session"].'" id="leave_days" >
									<input type="hidden" name="employee_category_id" value="'. $_POST["employee_category_id"].'" id="leave_days" >
									<input type="hidden" name="for_month" value="'. $_POST["for_month"].'" id="leave_days" >';
								?>
							</tr>
						</thead>
						<tbody>
						<?php 
						$sql='SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as sno , `employee`.`employee_name` ,`employee`.`employee_code` ,`employee`.`ehrms_id`, `employee`.`company_id`, `employee`.`pay_level_id` , `employee`.`employee_designation` ,`employee`.`employee_category_id` ,`payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`main_basic`, sum(`payslip_report`.`presented_days`)as presented_days, `payslip_report`.`granted_leave` , `payslip_report`.`working_days` ,`payslip_report`.`total_income` , 
					
						`payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`
								WHERE payslip_report.granted_leave!="0" and employee.company_id="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `for_month`="'.$_POST['for_month'].'"  AND `employee_category_id`="'.$_POST['employee_category_id'].'" group by emp_id';
						$result = execute_query($sql);
						$i=1;
						while($row=mysqli_fetch_array($result)){ 
						$working_days = intval($row["working_days"]);
						$presented_days = intval($row["presented_days"]);

						// Perform subtraction
						$remain_days = $working_days - $presented_days;
						// echo $remain_days;
						if($remain_days!=0){
							
						
							echo '<tr>
							<td>'. ($i++).'</td>
							<td>'. $row["employee_code"].'</td>
							<td>'. $row["company_id"].'</td>
							<td>'. $row["employee_name"].'</td>
							<td>'. $remain_days.'</td>';
							
							echo'<td><input type="hidden" name="remain_days_'. $row["sno"].'" value="'. $remain_days.'" id="remain_days_'. $row["sno"].'" >';?>
							<input type="text" name="leave_days_<?php echo $row["sno"]; ?>" value="" id="leave_days_<?php echo $row["sno"]; ?>"  oninput="validateLeaveDays(<?php echo $row["sno"]; ?>)"></td>
							</tr>
						<?php }	
						}
						
						?>
						</tbody>
					</table>
							<input type="submit" name="payslip" value="Submit" id="payslip" class="form-control btn btn-info">
				</div>
			</div>
		</div>
		<?php 
			}
		?>
	</form>
	
	
	<script>
		function validateLeaveDays(rowNumber) {
			// console.log(rowNumber);
			// Get the entered value
			var enteredValue = document.getElementById('leave_days_' + rowNumber).value;
			
			// Get the remaining leave days from the hidden field
			var remainingLeaveDays = document.getElementById('remain_days_' + rowNumber).value;
			
			// Convert enteredValue to a number
			var enteredNumber = parseInt(enteredValue);
			
			// Convert remainingLeaveDays to a number
			var remainingNumber = parseInt(remainingLeaveDays);
			
			// Check if enteredValue is a valid number and not greater than remainingLeaveDays
			if (isNaN(enteredNumber) || enteredNumber > remainingNumber) {
				alert('Please enter a valid number not greater than ' + remainingNumber);
				// Clear the input field
				document.getElementById('leave_days_' + rowNumber).value = '';
			}
		}
	</script>

<?php
page_footer();
?>
