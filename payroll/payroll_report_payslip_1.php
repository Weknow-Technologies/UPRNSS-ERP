<?php
	include("scripts/settings.php");
	page_header_start('Payslip Report');
	if (isset($_GET['del_id'])) {
		$sql_delete = 'DELETE FROM `payslip_report` WHERE `sno`="'.$_GET['del_id'].'"';
		$res = execute_query($sql_delete);
		if($res){
			$sql_delete_detail = 'DELETE FROM `payslip_details` WHERE `payslip_id`="'.$_GET['del_id'].'"';
			execute_query($sql_delete_detail);
			$msg = '<div class="alert alert-success">Payslip Information Deleted...</div>';
		}
	}
?>
<style type="text/css">
	table thead tr th{
		text-align: center;
		border: 2px solid black;
	}
	table tbody tr td{
		border: 2px solid black;
	}
</style>
<?php
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
?>
<div class="container">
	<?php echo $msg; ?>
	<div class="row">
		<?php 
			if (isset($_POST['company_name'])) {
				if($_POST['company_name'] != ''){
				$sql_company = 'SELECT * FROM `company_details` WHERE `sno`="'.$_POST['company_name'].'"';
				$result_company = execute_query($sql_company);
				$row_company = mysqli_fetch_array($result_company);
				?>
				<div class="print-only">
					<table width="100%" border="0">
						<tr>
							<td width="35%">
								<div class="row" style="margin-left: 10px;">
									<p><b><?php echo $row_company['company_name']; ?></b></p>
									<p><b><?php echo $row_company['address'].' , '.$row_company['state'].'-'.$row_company['pincode']; ?></b></p>
									<p><b>M. N. - <?php echo $row_company['mobile']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;E.Mail - <?php echo $row_company['email']; ?></b></p>
								</div>
							</td>
							<td width="30%"><h1>SALARY SHEET</h1></td>
							<td width="35%"><h1>FOR THE MONTH :<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?></h1></td>
						</tr>
					</table>
				</div>
				<?php
			}
			else{
				?>
				<div class="print-only">
					<table width="100%" border="0">
						<tr>
							<td width="35%"><h1>Payroll Bareilly</h1></td>
							<td width="30%"><h1>SALARY SHEET</h1></td>
							<td width="35%"><h1>FOR THE MONTH :<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?></h1></td>
						</tr>
					</table>
				</div>
				<?php
			}
		}
			else{
		?>
			<div class="print-only">
				<table width="100%" border="0">
					<tr>
						<td width="35%"><h1>Payroll Bareilly</h1></td>
						<td width="30%"><h1>SALARY SHEET</h1></td>
						<td width="35%">&nbsp;</td>
					</tr>
				</table>
			</div>
		<?php } ?>
		<div class="no-print">
			<div class="panel">
				<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data">
					<div class="panel-heading">Search</div>
					<div class="panel-body">
						<span style="float: right;">
							<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
						</span>
						<table class="table table-hover table-responsive table-bordered table-striped">	
							<thead>
								<tr>
									<td>Company Name</td>
									<td>
										<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
											<option value="">-Select All-</option>
											<?php 
												$sql = "SELECT * FROM `company_details`";	
												$query = execute_query($sql);
												 while ($row = mysqli_fetch_array($query)){ 
													echo '<option value="'.$row['sno'].'" ';
													if(isset($_POST['company_name'])){
														if($row['sno']==$_POST['company_name']){
															echo 'selected="selected"';
														}
													}
													echo '>'. $row['company_name'] . "</option>";
												}?>
											</select>
									</td>
									<td>Employee Name</td>
									<td><select class="form-control"  name="emp_name" id="emp_name">
										<option value="">-Select All-</option>

										<?php 
										$sql = 'SELECT * FROM `employee`';	
										$result = execute_query($sql);
										while ($row = mysqli_fetch_array($result)){ 
											echo '<option value="'.$row['sno'].'" ';
											if(isset($_POST['emp_name'])){
												if($row['sno']==$_POST['emp_name']){
													echo 'selected="selected"';
												}
											}
											echo '>' . $row['employee_name'] . "</option>";

										}?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Financial Year</td>
									<td>
										<select name="financial_year" class="form-control">
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
													<option value="<?php echo $i.'-'.$end_session; ?>" <?php if(isset($_POST['financial_year'])){if($_POST['financial_year']==$i.'-'.$end_session){echo 'selected';}}elseif($i == $select_start_session){echo 'selected';} ?>><?php echo $i.'-'.$end_session; ?></option>
													<?php
												}
											?>
										</select>
									</td>
									<td>For Month</td>
									<td>
										<select class="form-control"  name="for_month" id="title">
											<?php
											$month=1;
											$current_month = date('m');

											while($month<=12){
												echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
												if(isset($_POST['for_month'])){
													if($_POST['for_month']==$month or $month == $current_month){
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
								
							</thead>
						</table>
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" name="search" id="search" value="SEARCH" class="form-control btn btn-info">
							</div>
							<div class="col-sm-6">
								<a href="<?php echo $_SERVER['PHP_SELF'];?>" class="form-control btn btn-info">Reset</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<table class="table table-hover table-responsive table-striped" border="2">	
			<thead>
				<?php 
					$sql_head_basic = 'SELECT * FROM `head_type` WHERE `sno`="1"';
					$result_head_basic = execute_query($sql_head_basic);
					$row_head_basic = mysqli_fetch_array($result_head_basic);

					$sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" AND `sno`!="1"';
					$result_head_income = execute_query($sql_head_income);
					$num_head_income = mysqli_num_rows($result_head_income);

					$sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
					$result_head_deduction = execute_query($sql_head_deduction);
					$num_head_deduction = mysqli_num_rows($result_head_deduction);
				?>
				<tr>
					<th rowspan="2" style='border:2px solid black;'>S.No.</th>
					<!--<th colspan="3">Check</th>-->
					<th rowspan="2" class="no-print" style='border:2px solid black;'>Company Name</th>
					<th rowspan="2" style='border:2px solid black;'>Employee Name</th>
					<th rowspan="2" style='border:2px solid black;'>Designation</th>
					<th rowspan="2" class="no-print" style='border:2px solid black;'>Financial Year</th>
					<th rowspan="2" class="no-print" style='border:2px solid black;'>For Month</th>
					<th colspan="3" style='border:2px solid black;'>Attendance (Days)</th>
					<th rowspan="2" style='border:2px solid black;'><?php echo $row_head_basic['head_name']; ?></th>
					<th rowspan="2" style='border:2px solid black;'>Payable Amount</th>
					<th colspan="<?php echo $num_head_income+1; ?>" style='border:2px solid black;'>Allowances</th>
					<th rowspan="2" style='border:2px solid black;'>Total Earning</th>
					<th colspan="<?php echo $num_head_deduction+1; ?>" style='border:2px solid black;'>Deduction</th>
					<th rowspan="2" style='border:2px solid black;'>Net Payable Amount</th>
					<th rowspan="2" style='border:2px solid black;'>Signature</th>
					<th rowspan="2" style='border:2px solid black;' class="no-print">&nbsp;</th>
				</tr>
				<tr>
					<!--<th>Income</th>
					<th>Deduction</th>
					<th>Total</th>-->
					<th style='border:2px solid black;'>Working</th>
					<th style='border:2px solid black;'>Leave</th>
					<th style='border:2px solid black;'>Present</th>
					<?php 
						while($row_head_income = mysqli_fetch_array($result_head_income)){
							?>
					<th style='border:2px solid black;'><?php echo $row_head_income['head_name']; ?></th>
							<?php
						}
					?>
					<th style='border:2px solid black;'>Total</th>
					<?php 
						while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
							?>
					<th style='border:2px solid black;'><?php echo $row_head_deduction['head_name']; ?></th>
							<?php
						}
					?>
					<th style='border:2px solid black;'>Total</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$sql ='SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as emp_id , `company_details`.`sno` as company_id , `employee`.`employee_name` , `employee`.`employee_designation` , `company_details`.`company_name` , `payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`total_income` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id` join `company_details` on `company_details`.`sno` = `employee`.`company_id` WHERE 1=1 ';
					
				if(isset($_POST['company_name'])){
					if($_POST['company_name'] != ''){
						$sql .= ' AND `employee`.`company_id`="'.$_POST['company_name'].'" ';
					}
				}
				if(isset($_POST['emp_name'])){
					if($_POST['emp_name'] != ''){
						$sql .= ' AND `payslip_report`.`emp_id`="'.$_POST['emp_name'].'" ';
					}
				}
				if(isset($_POST['financial_year'])){
					if($_POST['financial_year'] != ''){
						$sql .= ' AND `payslip_report`.`financial_year`="'.$_POST['financial_year'].'" ';
					}
				}
				if(isset($_POST['for_month'])){
					if($_POST['for_month'] != ''){
						$sql .= ' AND `payslip_report`.`for_month`="'.$_POST['for_month'].'" ';
					}
				}
				$sql .= ' ORDER BY ABS(`payslip_report`.`financial_year`) DESC , ABS(`payslip_report`.`for_month`) DESC , ABS(`employee`.`company_id`) DESC , ABS(`payslip_report`.`total`) DESC';
				//echo $sql;
				$result = execute_query($sql);
				$i=1;
				$total_basic_salary = 0;
				$total_payable_amount = 0;
				$total_allowances = 0;
				$total_deduction = 0;
				$total_grand = 0;
				$allo[] = 0;
				$allo = array();
				$dedu = array();
				while($row=mysqli_fetch_array($result)){ 
					if($row['total'] > 0){
						$sql_amount_basic = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="1"';
						$result_amount_basic = execute_query($sql_amount_basic);
						$row_amount_basic = mysqli_fetch_array($result_amount_basic);
						$total_basic_salary += $row_amount_basic['amount'];

						$sql_amount_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="salary_amount"';
						$result_amount_payable = execute_query($sql_amount_payable);
						$row_amount_payable = mysqli_fetch_array($result_amount_payable);
						$total_payable_amount += $row_amount_payable['amount'];

						$sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="'.$row['emp_id'].'" AND `salary_generation_month`="'.$row['for_month'].'" AND `financial_year`="'.$row['financial_year'].'"';
						$result_attendance = execute_query($sql_attendance);
						$row_attendance = mysqli_fetch_array($result_attendance);
						echo"<tr>";
						echo "<td style='border:2px solid black;'>".$i++."</td>";
						/**echo "<td>".$row["total_income"]."</td>";
						echo "<td>".$row["total_deduction"]."</td>";
						echo "<td>".$row['total']."</td>";**/
						echo "<td class='no-print' style='border:2px solid black;'>".$row["company_name"]."</td>";
						echo "<td style='border:2px solid black;'>".$row["employee_name"]."</td>";
						echo "<td style='border:2px solid black;'>".$row["employee_designation"]."</td>";
						echo "<td class='no-print' style='border:2px solid black;'>".$row["financial_year"]."</td>";
						echo "<td class='no-print' style='border:2px solid black;'>".date('F',strtotime('01.'.$row["for_month"].'.2020'))."</td>";
						echo '<td style="border:2px solid black;">'.$row_attendance['working_days'].'</td><td style="border:2px solid black;">'.$row_attendance['granted_leave'].'</td><td style="border:2px solid black;">'.$row_attendance['presented_days'].'</td>';
						echo '<td style="border:2px solid black;">'.round($row_amount_basic['amount']).'</td><td  style="border:2px solid black;">'.round($row_amount_payable['amount']).'</td>';
						$sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" AND `sno`!="1"';
						$result_head_income = execute_query($sql_head_income);
						$allowances = 0;
						while($row_head_income = mysqli_fetch_array($result_head_income)){
							$sql_amount_income = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_income['sno'].'"';
							$result_amount_income = execute_query($sql_amount_income);
							$row_amount_income = mysqli_fetch_array($result_amount_income);
						echo "<td style='border:2px solid black;'>".round($row_amount_income["amount"])."</td>";
						$allowances += $row_amount_income['amount'];
						$allo[$row_head_income['sno']] += $row_amount_income['amount'];
						}
						echo '<td style="border:2px solid black;">'.round($allowances).'</td>';
						echo '<td style="border:2px solid black;">'.round($allowances+$row_amount_payable['amount']).'</td>';
						$sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
						$result_head_deduction = execute_query($sql_head_deduction);
						$deduction = 0;
						while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
							$sql_amount_deduction = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_deduction['sno'].'"';
							$result_amount_deduction = execute_query($sql_amount_deduction);
							$row_amount_deduction = mysqli_fetch_array($result_amount_deduction);
						echo "<td style='border:2px solid black;'>".round($row_amount_deduction["amount"])."</td>";
						$deduction += $row_amount_deduction['amount'];
						$dedu[$row_head_deduction['sno']] += $row_amount_deduction['amount'];
						}
						echo '<td style="border:2px solid black;">'.round($deduction).'</td>';
						$grand_total = $row_amount_payable['amount'] + $allowances - $deduction;
						echo '<td style="border:2px solid black;">'.round($grand_total).'</td>';
						echo"<td>&nbsp;<br/>&nbsp;</td>";
						echo '<td class="no-print"><a href="report_payslip.php?del_id='.$row['payslip_id'].'" '; echo 'onclick="alert(\'Are you sure?\')"'; echo '>Delete</a></td>';
						echo "</tr>";
						$total_allowances += $allowances;
						$total_deduction += $deduction;
						$total_grand += $grand_total;
					}
				}
				?>
				<tr>
					<th colspan="3" class="no-print" style='border:2px solid black;'>&nbsp;</th>
					<th colspan="6" style="text-align: right;border:2px solid black;">Total:</th>
					<th style='border:2px solid black;'><?php echo round($total_basic_salary); ?></th>
					<th style='border:2px solid black;'><?php echo round($total_payable_amount); ?></th>
				<?php 
					$sql_income_count = 'SELECT * FROM `head_type` WHERE `head_type`="income" AND `sno`!="1"';
					$result_income_count = execute_query($sql_income_count);
					while($row_income_count = mysqli_fetch_array($result_income_count)){
						?>
					<th style="border:2px solid black;"><?php echo round($allo[$row_income_count['sno']]); ?></th>
						<?php
					}
				?>
					<th style='border:2px solid black;'><?php echo round($total_allowances); ?></th>
					<th style='border:2px solid black;'><?php echo round($total_allowances+$total_payable_amount); ?></th>
				<?php 
					$sql_deduction_count = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
					$result_deduction_count = execute_query($sql_deduction_count);
					while($row_deduction_count = mysqli_fetch_array($result_deduction_count)){
						?>
					<th style="border:2px solid black;"><?php echo round($dedu[$row_deduction_count['sno']]); ?></th>
						<?php
					}
				?>
					<!--<th style="border:2px solid black;"><?php echo $dedu[2]; ?></th>
					<?php if($num_head_deduction > 0){ ?>
					<th colspan="<?php echo $num_head_deduction-1; ?>" style='border:2px solid black;'></th>
					<?php } ?>-->
					<th style='border:2px solid black;'><?php echo round($total_deduction); ?></th>
					<th style='border:2px solid black;'><?php echo round($total_grand); ?></th>
					<th style='border:2px solid black;'>&nbsp;</th>
					<th style='border:2px solid black;' class="no-print">&nbsp;</th>
				</tr>
			</tbody>
		</table>
	</div>
<?php
page_footer();
?>
<script type="text/javascript">
	function select_employee(){
		var d_i = document.getElementById("company_name").value;
		if (d_i != "") {
				$.ajax({
				url: "new_ajax.php?company="+d_i,
				data: "data",
				type: "post",
				success: function(resposedata){
				$('#emp_name').html(resposedata);
			}
			});
		}
	}
</script>