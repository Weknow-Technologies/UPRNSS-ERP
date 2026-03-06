<?php
include("scripts/settings.php");
page_header_start('Bank List Report');
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
	th{
		font-size: 14px;
	}
	td{
		font-size: 14px;
	}
</style>
<?php
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
?>
<div class="container">
	<div class="row">
		
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
												$sql = "SELECT * FROM `uprnss_division`";	
												$query = execute_query($sql);
												 while ($row = mysqli_fetch_array($query)){ 
													echo '<option value="'.$row['s_no'].'" ';
													if(isset($_POST['company_name'])){
														if($row['s_no']==$_POST['company_name']){
															echo 'selected="selected"';
														}
													}
													echo '>'. $row['division_name'] . "</option>";
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
		<?php 
		
		if(isset($_POST['search'])){ 
		
		?>
		<table class="table table-hover table-responsive table-striped" border="2">	
			<thead>
				<tr style="border: 0px;"><td colspan="22" style="border: 0px;"><span class="print-only">
				<?php 
				if (isset($_POST['company_name'])) {
					
					if($_POST['company_name'] != ''){
					$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_name'].'"';
					$result_company = execute_query($sql_company);
					$row_company = mysqli_fetch_array($result_company);
					?>
						<table width="100%" border="0">
							<tr>
								<td width="35%" style="border-right:none;">
									<div class="row" style="margin-left: 10px;">
										<p><b><?php echo $row_company['division_name']; ?></b></p>
										<p><b><?php echo $row_company['address'].' , '.$row_company['state'].'-'.$row_company['pincode']; ?></b></p>
										<p><b>M. N. - <?php echo $row_company['mobile']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;E.Mail - <?php echo $row_company['email']; ?></b></p>
									</div>
								</td>
								<td width="30%" style="border-left:none;border-right:none;"><h1>SALARY SHEET</h1></td>
								<td width="35%" style="border-left:none;"><h1>FOR THE MONTH :<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?></h1></td>
							</tr>
						</table>
					<?php
					}
					else{
						?>
							<table width="100%" border="0">
								<tr>
									<td width="35%" style="border-right:none;"><h1>Payroll Bareilly</h1></td>
									<td width="30%" style="border-left:none;border-right:none;"><h1>SALARY SHEET</h1></td>
									<td width="35%" style="border-left:none;"><h1>FOR THE MONTH :<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?></h1></td>
								</tr>
							</table>
						<?php
					}
				}
					else{
				?>
						<table width="100%" border="0">
							<tr>
								<td width="35%" style="border-right:none;"><h1>Payroll Bareilly</h1></td>
								<td width="30%" style="border-left:none;border-right:none;"><h1>SALARY SHEET</h1></td>
								<td width="35%" style="border-left:none;">&nbsp;</td>
							</tr>
						</table>
				<?php } ?>
				</span></td></tr>
				<tr>
					<th rowspan="2" style='border:2px solid black;'>S.No.</th>
					
					<th rowspan="2" style='border:2px solid black;'>Division Name</th>
					<th rowspan="2" style='border:2px solid black;'>Employee Code</th>
					<th rowspan="2" style='border:2px solid black;'>Employee Name</th>
					<th rowspan="2" style='border:2px solid black;'>Designation</th>
					<th rowspan="2" style='border:2px solid black;'>Name Of Bank</th>
					<th rowspan="2" style='border:2px solid black;'>Account Number</th>
					<th rowspan="3" style='border:2px solid black;'>Net Payment </th>
					
					
				</tr>
				
			</thead>
			<tbody>
				<?php
				$sql ='SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as emp_id , `uprnss_division`.`s_no` as company_id , `employee`.`employee_name` , `employee`.`employee_designation` , `uprnss_division`.`division_name` , `payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`total_income` , `payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id` join `uprnss_division` on `uprnss_division`.`s_no` = `employee`.`company_id` WHERE 1=1 ';
					
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
				// echo $sql;
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
				$advance_amount = 0;
				$remainnung_amount = 0;
				while($row=mysqli_fetch_array($result)){ 
					if($row['total'] >= 0){
						$sql_amount_payable = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="salary_amount"';
						$result_amount_payable = execute_query($sql_amount_payable);
						$row_amount_payable = mysqli_fetch_array($result_amount_payable);
						// $total_payable_amount += $row_amount_payable['amount'];

						$sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="'.$row['emp_id'].'" AND `salary_generation_month`="'.$row['for_month'].'" AND `financial_year`="'.$row['financial_year'].'"';
						$result_attendance = execute_query($sql_attendance);
						$row_attendance = mysqli_fetch_array($result_attendance);
						echo"<tr>";
						echo "<td style='border:2px solid black;'>".$i++."</td>";
						// <td  style='border:2px solid black;'>".date('F',strtotime('01.'.$row["for_month"].'.2020'))."</td>";
						
						echo "<td  style='border:2px solid black;'>".$row["division_name"]."</td>
						<td  style='border:2px solid black;'>".$row["division_name"]."</td>
						<td style='border:2px solid black;'>".$row["employee_name"]."</td>
						<td style='border:2px solid black;'>".$row["employee_designation"]."</td>
						<td class='no-print' style='border:2px solid black;'>".$row["financial_year"]."</td>
						<td style='border:2px solid black;'>".$row_attendance['working_days']."</td>";
					
						
						
						$sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" and payslip_mode!="1"';
						$result_head_income = execute_query($sql_head_income);
						$allowances = 0;
						while($row_head_income = mysqli_fetch_array($result_head_income)){
							
							$sql_amount_income = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_income['sno'].'"';
							$result_amount_income = execute_query($sql_amount_income);
							$row_amount_income = mysqli_fetch_array($result_amount_income);
							
							// echo "<td style='border:2px solid black;'>".round($row_amount_income["amount"])."</td>";
							$allowances += $row_amount_income['amount'];
							$allo[$row_head_income['sno']] += $row_amount_income['amount'];
						}
						// echo "<td style='border:2px solid black;'>".round($allowances)."</td>";
						
						$sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
						$result_head_deduction = execute_query($sql_head_deduction);
						$deduction = 0;
						while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
							$sql_amount_deduction = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_deduction['sno'].'"';
							$result_amount_deduction = execute_query($sql_amount_deduction);
							$row_amount_deduction = mysqli_fetch_array($result_amount_deduction);
							// echo "<td style='border:2px solid black;'>".round($row_amount_deduction["amount"])."</td>";
							$deduction += $row_amount_deduction['amount'];
							$dedu[$row_head_deduction['sno']] += $row_amount_deduction['amount'];
						}
						
						$grand_total = $row_amount_payable['amount'] + $allowances - $deduction;
						
						echo '<td style="border:2px solid black;">'.round($grand_total).'</td>';
						
						
						echo "</tr>";
						$total_allowances += $allowances;
						$total_deduction += $deduction;
						$total_grand += $grand_total;
						
					}
				}
				?>
				<tr>
					
					<th colspan="7" style="text-align: right;border:2px solid black;">Total:</th>
					<th style='border:2px solid black;'><?php echo round($total_grand); ?></th>
					
				</tr>
			</tbody>
		</table>
		<?php } ?>
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