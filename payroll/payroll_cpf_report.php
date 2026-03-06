<?php
include("scripts/settings.php");
set_time_limit(0);
page_header_start('CPF Report');
if (isset($_GET['del_id'])) {
	$sql_delete = 'DELETE FROM `payslip_report` WHERE `sno`="'.$_GET['del_id'].'"';
	$res = execute_query($sql_delete);
	if($res){
		$sql_delete_detail = 'DELETE FROM `payslip_details` WHERE `payslip_id`="'.$_GET['del_id'].'"';
		execute_query($sql_delete_detail);
		$msg = '<div class="alert alert-success">Payslip Information Deleted...</div>';
	}
}
$_SESSION['payroll_post'] = $_POST ;

?>
<style type="text/css">
.tablepad{
          margin: 0 !important;
          padding: 0 !important;
          box-sizing: border-box !important;
        }
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
	
	@page {
	  size: A4 landscape;
	  margin:0.2in;
	}
	
@media print {
        *{
          margin: 0 !important;
          padding: 0 !important;
          box-sizing: border-box !important;
        }
       body{
        padding:1rem!important;
       }
        td{
          padding: 8px !important;
          /* margin: 10px !important; */
        }
        .no-print{
          display:none !important;
        }
        
        .btn-print{
          display: none;
        }
        
       
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
						<div style="text-align: right; width: 100%;">
							<a onclick="printPage()" class="no-print btn btn-success"
								style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print
								this page</a>
							<a href="payroll_cpf_report_export.php" target="_blank" class="no-print btn btn-info"
								style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export
								to Excel</a>
						</div>
							<table class="table table-hover table-responsive table-bordered table-striped">	
								<thead>
									<tr>
										<td>Division Name</td>
										<td>
											<select class="form-control"  name="company_name" id="company_name" onchange="select_employee();">
												<option value="">-Select All-</option>
												<?php 
													$sql = "SELECT * FROM `uprnss_division` order by division_name ASC";	
													$query = mysqli_query($db_erp, $sql);
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
										<!-- <td>Employee Type</td>
										<td>
											<select class="form-control"  name="employee_category_id" id="employee_category_id" onchange="select_employee();">
												
												<?php 
													// $sql = "SELECT * FROM `dp_category`";	
													// $query = mysqli_query($db_erp, $sql);
													//  while ($row = mysqli_fetch_array($query)){ 
													// 	echo '<option value="'.$row['sno'].'" ';
													// 	if(isset($_POST['employee_category_id'])){
													// 		if($row['sno']==$_POST['employee_category_id']){
													// 			echo 'selected="selected"';
													// 		}
													// 	}
													// 	echo '>'. $row['category_name'] . "</option>";
													// }?>
												</select>
										</td> -->
										<td>Employee Type</td>
										<td>
											<select class="form-control"  name="employee_category_id" id="employee_category_id" onchange="select_employee();">
												
												<?php 
													$sql = "SELECT * FROM `payroll_emp_category`";	
													$query = execute_query($sql);
													 while ($row = mysqli_fetch_array($query)){ 
														echo '<option value="'.$row['sno'].'" ';
														if(isset($_POST['employee_category_id'])){
															if($row['sno']==$_POST['employee_category_id']){
																echo 'selected="selected"';
															}
														}
														echo '>'. $row['category_name'] . "</option>";
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
		</div>
	</div>

	<style>
		.printonly{
			display:none!important;
		}
		/* .watermarkz{
			width:100%;
			height:100%;
			position:fixed;
			right: 0;
			left: 0;
			top: 0;
			bottom: 0;
			margin: auto;
			background-color:red;
			background-image:url('images/icon.png');
			background-repeat:no-repeat;
			background-size:40%;
			z-index: -10;
		} */
		#overlays{
			z-index:0;
			opacity:0.15;
			position: fixed;
			top: 50%;
			left: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%); 
			display:none;
		}
		.payroll-footer{
			display:flex;
			justify-content:space-between;
			margin:5rem;
		}
		@media print{
			.printonly{
				display:block!important;
			}
			#overlays{
				display:block;
				width:35%!important;
				top: 50%!important;
				-ms-transform: translate(-50%, -50%);
				transform: translate(-50%, -50%);}
			}
			.payroll-footer{
				font-size:2rem;
				display:flex;
				justify-content:space-between;
				margin: 10rem!important;
			}
	</style>
	
	<div class="container">
		<div class="row">
			<?php 
			
				if(isset($_POST['search'])){ 
					if (isset($_POST['company_name'])) {
						if($_POST['company_name'] != ''){
							$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_name'].'"';
							$result_company = mysqli_query($db_erp, $sql_company);
							$row_company = mysqli_fetch_array($result_company);
						
									// emp type php code
									$query = execute_query("SELECT * FROM `payroll_emp_category` WHERE sno= '".$_POST['employee_category_id']."' ");
									$row = mysqli_fetch_array($query);
									
								?>
							<div class="">
								<img src="images/icon.png"  id="overlays" style=" " alt="overlay image" >
							</div>
							<div class="printonly ">
								<div style="display:flex; justify-content:center;position:relative;margin-bottom:3rem!important;">
									<div style="position:absolute;left:1rem;">
										<img src="images/icon.png" height="75">
									</div>
									<div>
										<div class="title text-center"> 
											<div class="" style="font-size:2.5rem;">
												<!--उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)-->
													UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH Ltd.
											</div>
											<div class="" style="font-size:1.8rem;">
												Pay Bil off <?php echo $row['category_name'];?> Employee:<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?>
											</div>	
											<div class="" style="font-size:1.8rem;">Construction Division:<?php echo $row_company['division_name']; ?></div>
										</div>
									</div>
								</div>
								<div style="display:flex; justify-content:space-between;margin-bottom:2rem!important;margin-inline:10rem!important;">
									<div style="font-size:1.8rem;">
										Bill No:
									</div>
									<div style="font-size:1.8rem;">
										Date:<?php echo date("d-m-y"); ?>
									</div>
								</div>
							</div>
						<?php
						}
						else{
							$query = execute_query("SELECT * FROM `payroll_emp_category` WHERE sno= '".$_POST['employee_category_id']."' ");
							$row = mysqli_fetch_array($query);
							?>
								<div class="printonly">
									<div style="display:flex; justify-content:center;position:relative;margin-bottom:3rem!important;">
										<div style="position:absolute;left:1rem;">
											<img src="images/icon.png" height="75">
										</div>
										<div>
											<div class="title text-center"> 
												<div class="" style="font-size:2.5rem;">
													<!--उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)-->
													UTTER PRADESH RAJYA NIRMAN SAHKARI SANGH Ltd.
												</div>
												<div class="" style="font-size:1.8rem;">
													Pay Bil off <?php echo $row['category_name'];?> Employee:<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?>
												</div>	
												<div class="" style="font-size:1.8rem;">Construction Division:<?php echo $row_company['division_name']; ?></div>
											</div>
										</div>
									</div>
									<div style="display:flex; justify-content:space-between;margin-bottom:2rem!important;margin-inline:4rem!important;">
										<div>
											Bill No:
										</div>
										<div>
											Date:<?php echo date("d-m-y"); ?>
										</div>
									</div>
								</div>
							<?php
						}
					}
				
			?>
		
			
			<table class="table table-hover table-responsive table-striped tablepad" border="2">	
					
					<tr class="tablepad" style='border-bottom:1px solid black!important;'>
						<th rowspan="2" style='border:1px solid black;'>S.No.</th>
						
						<th rowspan="2" style='border:1px solid black;'>Employee Name</th>
						<th rowspan="2" style='border:1px solid black;'>Employee Name</th>
						<th rowspan="2" style='border:1px solid black;'>UAN NO.</th>
						<th rowspan="2" style='border:1px solid black;'>DOJ</th>
						<th rowspan="2" style='border:1px solid black;'>EPS</th>
						<th rowspan="2" style='border:1px solid black;'>Employee code</th>
						<th rowspan="2" style='border:1px solid black;'>Division Name</th>
						<th colspan="8" style='border:1px solid black;'>Rate Of Salary</th>
						
						
						<th colspan="3" style='border:1px solid black;'>Pay Days</th>
						
						
						
						<?php


						// income sql 


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='income' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";
						// echo $sql = 'select * from head_type where head_type="income" and payslip_mode!="1"';
						$result_income = execute_query($sql);
						$income_count = mysqli_num_rows($result_income);
						$income_count += 1;
						echo '<th style="border:1px solid black; text-align:center;" colspan="'.$income_count.'">Earned Salary</th>';
						

						// Deducaiton sql


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='deduction' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";

						// $sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';
						$result_deduction = execute_query($sql);
						$deduction_count = mysqli_num_rows($result_deduction);
						// $deduction_count += 1;
						echo '<th style="border:1px solid black;" colspan="'.$deduction_count.'">Deduction</th>';
						

						// other deduction sql


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='oth_deduction' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";
						
						// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode!="1"';

						$result_deduction = execute_query($sql);
						$deduction_count = mysqli_num_rows($result_deduction);
						$deduction_count += 1;
						//echo '<th style="border:1px solid black;" colspan="'.$deduction_count.'">Other Deduction</th>';
						echo '<th style="border:1px solid black;" rowspan="2">Total Deduction</th>';
						

						?>
						
						
						<th  rowspan="2"  style='border:1px solid black;'>Net Pay</th>
						
					</tr>
					<tr class="tablepad">
						<!--<th>Income</th>
						<th>Deduction</th>
						<th>Total</th>-->
						<?php
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='income' and payslip_mode!='1'";
						$result_income = execute_query($sql);
						while($row_income = mysqli_fetch_assoc($result_income)){
							$tot_heads[$row_income['sno']] = 0;
							echo '<th style="border:1px solid black;">'.$row_income['head_name'].'</th>';	
						}
						?>
						
						
						<th style='border:1px solid black;'>Month Days</th>
						<th style='border:1px solid black;'>Paybill Days</th>
						<th style='border:1px solid black;'>NCP Days</th>
						<?php
						$tot_heads = array();
						
						mysqli_data_seek($result_income,0);
						while($row_income = mysqli_fetch_assoc($result_income)){
							$tot_heads[$row_income['sno']] = 0;
							echo '<th style="border:1px solid black;">'.$row_income['head_name'].'</th>';	
						}
						$tot_heads['gorss_pay'] = 0;
						echo'<th  style="border:1px solid black;" >Gross Pay</th>';
						
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='deduction' and payslip_mode!='1'";
						
						// $sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';
						$result_deduction = execute_query($sql);
						while($row_deduction = mysqli_fetch_assoc($result_deduction)){
							$tot_heads[$row_deduction['sno']] = 0;
							echo '<th style="border:1px solid black;">'.$row_deduction['head_name'].'</th>';	
						}
						
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='oth_deduction' and payslip_mode!='1'";
						
						
						// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode!="1"';
						$result_deduction = execute_query($sql);
						while($row_deduction = mysqli_fetch_assoc($result_deduction)){
							$tot_heads[$row_deduction['sno']] = 0;
							// echo '<th style="border:1px solid black;">'.$row_deduction['head_name'].'</th>';	
						}
						// echo'<th style="border:1px solid black;">Total Deduction</th>';
						$tot_heads['total_deduction'] = 0;

						?>
						
					</tr>
				
				<tbody>
					<?php
					// Establish database connection
					// Assume $db_erp is your database connection

					// Function to execute queries and fetch results
					function fetch_results($sql, $db) {
						return mysqli_query($db, $sql);
					}

					// Prepare base SQL query with JOIN
					$sql = 'SELECT p.sno as payslip_id, e.sno as emp_id, e.employee_name, e.employee_code, p.company_id, e.pay_level_id, 
							e.employee_designation, e.employee_category_id, e.esic_no, e.joining_date, p.financial_year, p.for_month, 
							p.main_basic, p.total_income, p.advance_amount, p.total_deduction, p.total, p.presented_days, p.working_days 
							FROM payslip_report p
							JOIN employee e ON e.sno = p.emp_id 
							WHERE 1=1';

					// Add dynamic filters
					$filters = [
						'company_name' => 'p.company_id',
						'emp_name' => 'p.emp_id',
						'employee_category_id' => 'e.employee_category_id',
						'financial_year' => 'p.financial_year',
						'for_month' => 'p.for_month'
					];

					foreach ($filters as $key => $column) {
						if (isset($_POST[$key]) && $_POST[$key] != '') {
							$value = mysqli_real_escape_string($db, $_POST[$key]);
							$sql .= " AND $column='$value'";
						}
					}

					// Add ordering
					$sql .= ' ORDER BY ABS(p.financial_year) DESC, ABS(p.for_month) DESC, ABS(p.company_id) DESC, ABS(p.total) DESC';

					$result = fetch_results($sql, $db);

					$i = 1;
					$totals = [
						'main_basic' => 0,
						'net_pay' => 0,
						'total_deduction' => 0,
						'total_income' => 0
					];

					$tot_heads = [];

					// Fetch all required head types in advance
					$head_types = ['income', 'deduction', 'oth_deduction'];
					$head_data = [];

					foreach ($head_types as $type) {
						$sql_head = "SELECT h.sno, h.head_type FROM payroll_head_access pha 
									 JOIN head_type h ON pha.head_id = h.sno 
									 WHERE pha.cat_id='{$_POST['employee_category_id']}' 
									 AND h.head_type='$type' 
									 AND h.payslip_mode != '1'";
						$result_head = fetch_results($sql_head, $db);
						while ($row_head = mysqli_fetch_assoc($result_head)) {
							$head_data[$type][$row_head['sno']] = $row_head;
						}
					}

					while ($row = mysqli_fetch_array($result)) {
						$company_query = 'SELECT division_name FROM uprnss_division WHERE s_no="' . mysqli_real_escape_string($db_erp, $row['company_id']) . '"';
						$company_result = fetch_results($company_query, $db_erp);
						$company_row = mysqli_fetch_array($company_result);
						
						// Display row data
						if ($row['total'] > 0) {
							echo "<tr>";
							echo "<td style='border:1px solid black;'>$i</td>";
							echo "<td style='border:1px solid black;'>{$row['employee_name']}</td>";
							echo "<td style='border:1px solid black;'>{$row['employee_name']}</td>";
							echo "<td style='border:1px solid black;'>{$row['esic_no']}</td>";
							echo "<td style='border:1px solid black;'>{$row['joining_date']}</td>";
							echo "<td style='border:1px solid black;'></td>";
							echo "<td style='border:1px solid black;'>{$row['employee_code']}</td>";
							echo "<td style='border:1px solid black;'>{$company_row['division_name']}</td>";
							
							$row_amount_income_array = array();
							$sql_head_income = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='income' and payslip_mode!='1'";
						
							// $sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" and payslip_mode!="1"';
							$result_head_income = execute_query($sql_head_income);
							while($row_head_income = mysqli_fetch_array($result_head_income)){
								
								$sql_amount_income = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_income['sno'].'"';
								
								$result_income = execute_query($sql_amount_income);
								$row_amount_income_array[] = $row_amount_income = mysqli_fetch_array($result_income);
								
								echo "<td style='border:1px solid black;'>".$row_amount_income["base_amount"]."</td>";
							}
							
							// Display working and presented days
							echo "<td style='border:1px solid black;'>{$row['working_days']}</td>";
							echo "<td style='border:1px solid black;'>{$row['presented_days']}</td>";
							echo "<td style='border:1px solid black;'></td>";
							
							
							// Calculate income
							$row_amount_income_array = [];
							foreach ($head_data['income'] as $head_id => $head_info) {
								$income_query = 'SELECT amount FROM payslip_details WHERE payslip_id="' . mysqli_real_escape_string($db, $row['payslip_id']) . '" AND head_id="' . $head_id . '"';
								$income_result = fetch_results($income_query, $db);
								$income_row = mysqli_fetch_assoc($income_result);
								$amount = $income_row['amount'] ?? 0;
								$row_amount_income_array[$head_id] = $amount;
								echo "<td style='border:1px solid black;'>$amount</td>";
							}
							
							// Calculate totals
							$totals['main_basic'] += $row['main_basic'];
							$totals['total_income'] += $row['total_income'];
							$totals['total_deduction'] += $row['total_deduction'];
							$totals['net_pay'] += $row['total'];
							
							// Accumulate head totals
							foreach ($row_amount_income_array as $head_id => $amount) {
								if (!isset($tot_heads[$head_id])) {
									$tot_heads[$head_id] = 0;
								}
								$tot_heads[$head_id] += $amount;
							}
							echo "<td style='border:1px solid black;'>".$row['total_income']."</td>";
							// $tot_total_income += $row['total_income'];
							
							// Deduction handling
							foreach ($head_data['deduction'] as $head_id => $head_info) {
								$deduction_query = 'SELECT amount FROM payslip_details WHERE payslip_id="' . mysqli_real_escape_string($db, $row['payslip_id']) . '" AND head_id="' . $head_id . '"';
								$deduction_result = fetch_results($deduction_query, $db);
								$deduction_row = mysqli_fetch_assoc($deduction_result);
								$amount = $deduction_row['amount'] ?? 0;
								if (!isset($tot_heads[$head_id])) {
									$tot_heads[$head_id] = 0;
								}
								$tot_heads[$head_id] += $amount;
								echo "<td style='border:1px solid black;'>$amount</td>";
							}
							
							// Other deductions
							foreach ($head_data['oth_deduction'] as $head_id => $head_info) {
								$oth_deduction_query = 'SELECT amount FROM payslip_details WHERE payslip_id="' . mysqli_real_escape_string($db, $row['payslip_id']) . '" AND head_id="' . $head_id . '"';
								$oth_deduction_result = fetch_results($oth_deduction_query, $db);
								$oth_deduction_row = mysqli_fetch_assoc($oth_deduction_result);
								$amount = $oth_deduction_row['amount'] ?? 0;
								if (!isset($tot_heads[$head_id])) {
									$tot_heads[$head_id] = 0;
								}
								$tot_heads[$head_id] += $amount;
							}
							
							echo "<td style='border:1px solid black;'>{$row['total_deduction']}</td>";
							echo "<td style='border:1px solid black;'>{$row['total']}</td>";
							echo "</tr>";
							$i++;
						}
					}

					// Display totals row
					echo "<tr>";
					echo "<th colspan='8' style='text-align: right;border:1px solid black;'>Total:</th>";
					echo "<td style='border:1px solid black;' colspan='8'>&nbsp;</td>";
					echo "<th colspan='3' style='text-align: right;border:1px solid black;'></th>";

					// Display totals for income and deductions
					foreach ($head_data['income'] as $head_id => $head_info) {
						echo "<td style='border:1px solid black;'>{$tot_heads[$head_id]}</td>";
					}
					echo "<td style='border:1px solid black;'>{$totals['total_income']}</td>";

					foreach ($head_data['deduction'] as $head_id => $head_info) {
						echo "<td style='border:1px solid black;'>{$tot_heads[$head_id]}</td>";
					}
					echo "<td style='border:1px solid black;'>{$totals['total_deduction']}</td>";
					echo "<td style='border:1px solid black;'>{$totals['net_pay']}</td>";
					echo "</tr>";
					?>
				</tbody>

			</table>

			<!-- <div class="payroll-foot">
				mentioned and passed for payment of Rs. <?php //echo $tot_heads['gorss_pay']; ?> 
				<?php
					//$abc="123123";
				 //echo int_to_words($abc);?>
				
			</div> -->
			

			

			<div class="payroll-footer" style="">
				<div>Comp Oprator</div>
				<div>Accontant</div>
				<div>DGM(F)</div>
			</div>
			<?php  } ?>
		</div>
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