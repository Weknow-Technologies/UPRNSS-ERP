<?php
include("scripts/settings.php");
page_header_start('Bonus');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$deduction=0;
$income=0;
$grand_total=0;
$var=1;
$emp_id='';
$msg='';
if(isset($_POST['confirm_submit'])){
	$sql_check = 'SELECT * FROM `bonus_invoice` WHERE `financial_year`="'.$_POST['financial_session'].'" AND `company_id`="'.$_POST['company_id'].'"';
	$res_check = execute_query($sql_check);
	$num_check = mysqli_num_rows($res_check);
	if($num_check == 0){
		$sql = 'INSERT INTO `bonus_invoice`(`company_id`, `financial_year`, `from_month`, `to_month`, `maximum_salary`, `minimum_working_days`, `bonus_rate`, `minimum_wages`, `maximum_applicable_wages`, `declaration`, `invoice_amount`, `created_by`, `creation_time` , `total_working_days`) VALUES ("'.$_POST['company_id'].'" , "'.$_POST['financial_session'].'" , "04" , "03" , "'.$_POST['maximum_salary'].'" , "'.$_POST['minimum_working_days'].'" , "'.$_POST['bonus_rate'].'" , "'.$_POST['minimum_wages'].'" , "'.$_POST['maximum_applicable_bonus_wages'].'" , "'.$_POST['valid'].'" , "'.$_POST['grand_total'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'" , "'.$_POST['company_working_days'].'")';
		$res = execute_query($sql);
		if($res){
			$bonus_id = mysqli_insert_id($db);
		}
		for($i = 1; $i <= $_POST['number_of_entries'] ; $i++){
			$sql_insert = 'INSERT INTO `bonus_details`(`bonus_id`, `emp_id`, `bonus_amount`, `salary_amount`, `working_days`, `granted_leave`, `presented_days`, `payable_salary_amount`, `amount_for_bonus`, `bonus_rate`, `created_by`, `creation_time`) VALUES ("'.$bonus_id.'" , "'.$_POST['employee_sno'.$i].'" , "'.$_POST['employee_bonus_amount'.$i].'" , "'.$_POST['employee_total_salary_amount'.$i].'" , "'.$_POST['employee_working_days'.$i].'" , "'.$_POST['employee_leave'.$i].'" , "'.$_POST['employee_present_days'.$i].'" , "'.$_POST['employee_total_payable_salary'.$i].'" , "'.$_POST['employee_bonus_on_amount'.$i].'" , "'.$_POST['employee_bonus_rate'.$i].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
			execute_query($sql_insert);
		}
		$msg .= '<div class="alert-success">Generated.</li>';
	}
	else{
		$msg .= '<div class="alert-success">Already Generated.</li>';
	}
}


?>
<div class="container">
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
		<div class="row">
			<div class="col-sm-12"><?php echo $msg; ?></div>
		</div>
		<div class="row">
			<div class="col-sm-2"></div>
			<div class="col-sm-8">
				<div class="panel">
					<div class="panel-heading">Bonus</div>
					<div class="panel-body">
						<table class="table table-hover table-responsive table-bordered table-striped">
							<tr>
								<td>Select Company</td>
								<td>
									<select required  class="form-control"  name="company_id" id="company_id">
									<option value="">Select</option>
									<?php 
										$sql = "SELECT * FROM `company_details`";	
										$query = execute_query($sql);
										 while ($row = mysqli_fetch_array($query)){
										 	?>
									<option value="<?php echo $row['sno']; ?>" <?php if(isset($_POST['search'])){if($_POST['company_id']==$row['sno']){echo 'selected';}} ?>><?php echo $row['company_name']; ?></option>
										 	<?php
										}?>
									</select>
								</td>
								<td>Financial Session</td>
								<td>
									<select name="financial_session" class="form-control" required>
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
												<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['search'])){if($_POST['financial_session']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
												<?php
											}
										?>
									</select>
								</td>
							</tr>
							<!--<tr>
								<td>From Month</td>
								<td>
									<select class="form-control"  name="from_month" id="title" required>
										<?php
										$month=1;
										$current_month = date('m');

										while($month<=12){
											echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
											if(isset($_POST['search'])){
												if($month==$_POST['from_month']){
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
								<td>To Month</td>
								<td>
									<select class="form-control"  name="to_month" id="title" required>
										<?php
										$month=1;
										$current_month = date('m');

										while($month<=12){
											echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
											if(isset($_POST['search'])){
												if($month==$_POST['to_month']){
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
							<tr>
								<td>Minimum Working Days</td>
								<td>
									<input type="number" name="minimum_working_days" id="minimum_working_days" class="form-control" value="<?php if(isset($_POST['search'])){echo $_POST['minimum_working_days'];} ?>" required>
								</td>
								<td>Maximum Salary (Per Month)</td>
								<td>
									<input type="number" name="maximum_salary" id="maximum_salary" class="form-control" value="<?php if(isset($_POST['search'])){echo $_POST['maximum_salary'];} ?>" required>
								</td>
							</tr>
							<tr>
								<td>Bonus Rate(in %)</td>
								<td>
									<input type="number" name="bonus_rate" id="bonus_rate" class="form-control" value="<?php if(isset($_POST['search'])){echo $_POST['bonus_rate'];} ?>" required>
								</td>
							</tr>-->
						</table>
						<input type="submit" name="search" value="Search" id="search" class="form-control btn btn-info">
					</div>
				</div>
			</div>
			<div class="col-sm-2"></div>
		</div>
		<?php 
		if(isset($_POST['search'])){
			$company_working_days = 0;
			$sql_admin = 'SELECT * FROM `admin_bonus` WHERE `company_id`="0"';
			$result_admin = execute_query($sql_admin);
			$row_admin = mysqli_fetch_array($result_admin);
			$sql_company_attendance = 'SELECT * FROM `attendance_invoice` WHERE `company_id`="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'"';
			$result_company_attendance = execute_query($sql_company_attendance);
			while ($row_company_attendance = mysqli_fetch_array($result_company_attendance)) {
				$company_working_days += $row_company_attendance['working_days'];
			}
			$sql_company = 'SELECT * FROM `company_details` WHERE `sno`="'.$_POST['company_id'].'"';
			$result_company = execute_query($sql_company);
			$row_company = mysqli_fetch_array($result_company);
		?>
		<div class="panel" style="margin-top:40px;">
			<div class="panel-heading">
				<b>Company Name : <?php echo $row_company['company_name']; ?></b>
				<input type="hidden" name="company_working_days" value="<?php echo $company_working_days; ?>">
			</div>
			<div class="panel-body">
				<!--<table class="table table-hover table-responsive table-bordered table-striped">
					<tr>
						<th>Bonus Amount For Every Person:</th>
						<th colspan="5">
							<input type="number" name="bonus_amount" id="bonus_amount" class="form-control" onblur="fill_bonus_amount();" placeholder="Enter The Amount" required>
						</th>
					</tr>
				</table>-->
				<table class="table table-hover table-responsive table-bordered table-striped">
					<tr>
						<th>Maximum Salary(Per Month)</th>
						<td><input type="text" name="maximum_salary" value="<?php echo $row_admin['maximum_salary']; ?>" readonly></td>
						<th>Minimum Working Days(Per Year)</th>
						<td><input type="text" name="minimum_working_days" value="<?php echo $row_admin['minimum_working_days']; ?>" readonly></td>
					</tr>
					<tr>
						<th>Minimum Wages(Prefferd)</th>
						<td><input type="text" name="minimum_wages" value="<?php echo $row_admin['minimum_wages']; ?>" readonly></td>
						<th>Maximum Applicable Bonus Wages</th>
						<td><input type="text" name="maximum_applicable_bonus_wages" value="<?php echo $row_admin['maximum_applicable_bonus_wages']; ?>" readonly></td>
					</tr>
					<tr>
						<th>Bonus Rate(%)</th>
						<td><input type="text" name="bonus_rate" value="<?php echo $row_admin['bonus_rate']; ?>" readonly></td>
						<th>Bonus Applicable On These Aspects.</th><td><input type="checkbox" name="valid" required class="form-control"></td>
					</tr>
				</table>
				<table class="table table-hover table-responsive table-bordered table-striped">
					<thead>
						<tr>
							<th>S.No.</th>
							<th>Employee Name</th>
							<th>Salary Amount</th>
							<th>Working Days</th>
							<th>Granted Leave</th>
							<th>Present Days</th>
							<th>Payable Salary Amount</th>
							<th>Amount For Bonus</th>
							<th>Bonus Rate(%)</th>
							<th>Bonus Amount</th>
						</tr>
					</thead>
					<tbody>
		<?php 
				$sql_employee = 'SELECT * FROM `employee` WHERE `company_id`="'.$row_company['sno'].'" AND `working_status`!="1"';
				$result_employee = execute_query($sql_employee);
				$n = 0;
				$minimum_wages = 0;
				if ($row_admin['minimum_wages'] > $row_admin['maximum_applicable_bonus_wages']) {
					$minimum_wages = $row_admin['minimum_wages'];
				}
				else{
					$minimum_wages = $row_admin['maximum_applicable_bonus_wages'];
				}
				while($row_employee = mysqli_fetch_array($result_employee)){
					$working = 0;
					$leave = 0;
					$present = 0;
					$pre = 0;
					$sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="'.$row_employee['sno'].'" AND `financial_year`="'.$_POST['financial_session'].'"';
					$result_attendance = execute_query($sql_attendance);
					while ($row_attendance = mysqli_fetch_array($result_attendance)) {
						$pre = 1;
						$working += $row_attendance['working_days'];
						$leave += $row_attendance['granted_leave'];
						$present += $row_attendance['presented_days'];
					}

					$sql_salary = 'SELECT * FROM `payslip_report` WHERE `emp_id`="'.$row_employee['sno'].'" AND `financial_year`="'.$_POST['financial_session'].'"';
					$result_salary = execute_query($sql_salary);
					$maximum_salary = 0;
					$bonus_per_person = 0;
					$bonus_on_amount = 0;
					$total_employee_salary = 0;
					$total_payable_salary = 0;
					while ($row_salary = mysqli_fetch_array($result_salary)) {
						$sql_salary_amount = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row_salary['sno'].'" AND `head_id`="1"';
						$result_salary_amount = execute_query($sql_salary_amount);
						$row_salary_amount = mysqli_fetch_array($result_salary_amount);
						if($row_salary_amount['amount'] <= $row_admin['maximum_salary']){
							$maximum_salary = 1;
							$sql_salary_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row_salary['sno'].'" AND `head_id`="salary_amount"';
							$result_salary_payable = execute_query($sql_salary_payable);
							$row_salary_payable = mysqli_fetch_array($result_salary_payable);
							if ($row_salary_payable['amount'] < $minimum_wages) {
								$bonus_on_amount += $row_salary_payable['amount'];
								$bonus_per_person +=  (($row_salary_payable['amount'] * $row_admin['bonus_rate']) / 100);
							}
							else{
								$bonus_on_amount += $minimum_wages;
								$bonus_per_person +=  (($minimum_wages * $row_admin['bonus_rate']) / 100);
							}
							$total_employee_salary += $row_salary_amount['amount'];
							$total_payable_salary += $row_salary_payable['amount'];
						}
					}
					//echo $maximum_salary.'a<br/>';
					if ($maximum_salary != 0 AND $row_admin['minimum_working_days'] <= $present AND $pre == 1 ) {
						$grand_total += $bonus_per_person;
					?>						
						<tr>
							<tr>
								<th><?php echo ++$n; ?></th>
								<td>
									<?php echo $row_employee['employee_name']; ?>
									<input type="hidden" name="employee_sno<?php echo $n; ?>" id="employee_sno<?php echo $n; ?>" value="<?php echo $row_employee['sno']; ?>" class="form-control">
								</td>
								<td>
									<input type="number" name="employee_total_salary_amount<?php echo $n; ?>" id="employee_total_salary_amount<?php echo $n; ?>" value="<?php echo $total_employee_salary; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_working_days<?php echo $n; ?>" id="employee_working_days<?php echo $n; ?>" value="<?php echo $working; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_leave<?php echo $n; ?>" id="employee_leave<?php echo $n; ?>" value="<?php echo $leave; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_present_days<?php echo $n; ?>" id="employee_present_days<?php echo $n; ?>" value="<?php echo $present; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_total_payable_salary<?php echo $n; ?>" id="employee_total_payable_salary<?php echo $n; ?>" value="<?php echo $total_payable_salary; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_bonus_on_amount<?php echo $n; ?>" id="employee_bonus_on_amount<?php echo $n; ?>" value="<?php echo $bonus_on_amount; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_bonus_rate<?php echo $n; ?>" id="employee_bonus_rate<?php echo $n; ?>" value="<?php echo $row_admin['bonus_rate']; ?>" class="form-control" readonly>
								</td>
								<td>
									<input type="number" name="employee_bonus_amount<?php echo $n; ?>" id="employee_bonus_amount<?php echo $n; ?>" class="form-control" onblur="cal_amount();" value="<?php echo round($bonus_per_person , 2); ?>" readonly>
								</td>		
							</tr>
						</tr>						
		<?php 
					}
				}
				?>
						<tr>
							<th colspan="9" style="text-align: right;">Total:</th>
							<th>
								<input type="text" name="grand_total" id="grand_total" class="form-control" value="<?php echo round($grand_total , 2); ?>" readonly>
							</th>
						</tr>
					<input type="hidden" name="number_of_entries" id="number_of_entries" value="<?php echo $n; ?>">
					</tbody>
				</table>
				<div class="row">
					<div class="col-sm-3">&nbsp;</div>
					<div class="col-sm-6">
						<input type="submit" name="confirm_submit" id="confirm_submit" value="SUBMIT" class="form-control btn btn-info">
					</div>
					<div class="col-sm-3">&nbsp;</div>
				</div>
			</div>
		</div>	
				<?php
			} 
		?>
	</form>
</div>
<?php
page_footer();
?>
<script type="text/javascript">
	function fill_bonus_amount(){
		var grand_total = 0;
		var number_of_entries = document.getElementById('number_of_entries').value;
		var bonus_amount = document.getElementById('bonus_amount').value;
		for(var i = 1 ; i <= number_of_entries ; i++){
			$('#employee_bonus_amount'+i).val(bonus_amount);
			grand_total += parseFloat(document.getElementById('employee_bonus_amount'+i).value);
		}
		$('#grand_total').val(grand_total);
	}

	function cal_amount(){
		var grand_total = 0;
		var number_of_entries = document.getElementById('number_of_entries').value;
		for(var i = 1 ; i <= number_of_entries ; i++){
			var bonus_amount = parseFloat(document.getElementById('employee_bonus_amount'+i).value);
			if(!bonus_amount){
				bonus_amount = 0;
			}
			grand_total += bonus_amount;
		}
		$('#grand_total').val(grand_total);
	}
</script>