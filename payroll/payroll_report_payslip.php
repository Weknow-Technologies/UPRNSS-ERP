<?php
include("scripts/settings.php");
page_header_start('Payslip Report');
$msg="";
if (isset($_GET['del_id'])) {
	$sql_delete = 'DELETE FROM `payslip_report` WHERE `sno`="'.$_GET['del_id'].'"';
	$res = execute_query($sql_delete);
	if($res){
		$sql_delete_detail = 'DELETE FROM `payslip_details` WHERE `payslip_id`="'.$_GET['del_id'].'"';
		execute_query($sql_delete_detail);
		$msg = '<div class="alert alert-success">Payslip Information Deleted...</div>';
	}
}

if(!isset ($_POST['search'])){
	$_POST['type']='';
}
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
.page-break { page-break-before: always; }
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
<script src="table2excel.js"></script>
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
						<?php echo $msg;?>
							<span style="float: right;">
								<a onclick="printPage()" class="no-print btn btn-info">Print this page</a>
							</span>
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
										<td>Salary Type</td>
										<td>
											<select name="type" id="type" class="form-control" onchange="onchangetype()">

												<option value="main" <?php echo ($_POST['type'] == 'main' ? 'selected' : ''); ?>>Monthly</option>
												<option value="Leave" <?php echo ($_POST['type'] == 'Leave' ? 'selected' : ''); ?>>Holiday</option>
												
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
				margin: 30rem!important;
			}

			/* .equal-width {
				flex-grow: 1;
				text-align: center;
			} */
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
									<div style="text-align: center;">
										<div class="title text-center"> 
											<div class="" style="font-size:2.5rem; justify-content:center">
												<!--उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)-->
													UTTAR PRADESH RAJYA NIRMAN SAHKARI SANGH Ltd.
											</div>
											<div class="" style="font-size:1.8rem;">
												Pay Bil off <?php echo $row['category_name'];?> Employee:<?php echo date('F',strtotime('01.'.$_POST['for_month'].'.2020')).'&nbsp;'.$_POST['financial_year']; ?>
											</div>	
											<div class="" style="font-size:1.8rem;">Construction Division:<?php echo $row_company	['division_name']; ?>
											</div>
										</div>
									</div>
								</div>
								<div style="display:flex; justify-content:space-between;margin-bottom:0rem!important;margin-inline:0rem!important;">
									<div style="font-size:1.8rem;">
										Bill No:
									</div>
									<div class="" style="font-size:1.8rem;">File No: &nbsp; <?php echo  $row_company['file_no']; ?>
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
										<div class="" style="font-size:1.8rem;">File No:<?php echo $row_company	['file_no']; ?>
										</div>
										<div>
											Date:<?php echo date("d-m-y"); ?>
										</div>
										<div>
											File No.
										</div>
									</div>
								</div>
							<?php
						}
					}
			?>
		
			
			<table class="table table-hover table-responsive table-striped tablepad" border="2">	
				<thead>	
					<tr class="tablepad" style='border-bottom:1px solid black!important;'>
						<th rowspan="2" style='border:1px solid black;'>S.No.</th>
						<th rowspan="2" style='border:1px solid black;'>Employee code</th>
						<th rowspan="2" style='border:1px solid black;'>ehrms code</th>
						<th rowspan="2" style='border:1px solid black;'> Employee Name </th>
						<th rowspan="2" style='border:1px solid black;'>Designation</th>
						<th rowspan="2" class="no-print" style='border:1px solid black;'>Company Name</th>
						<th rowspan="2" class="no-print" style='border:1px solid black;'>Financial Year</th>
						<th rowspan="2" class="no-print" style='border:1px solid black;'>For Month</th>
						<th colspan="3" style='border:1px solid black;'>Attendance (Days)</th>
						<th rowspan="2" style='border:1px solid black;'>level</th>
						<th rowspan="2" style='border:1px solid black;'>@ BAsic</th>
						<?php


						// income sql 


						$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='income' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";
						// echo $sql = 'select * from head_type where head_type="income" and payslip_mode!="1"';
						$result_income = execute_query($sql);
						$income_count = mysqli_num_rows($result_income);
						$income_count += 1;
						echo '<th style="border:1px solid black;" colspan="'.$income_count.'">Income</th>';
						

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
						echo '<th style="border:1px solid black;" colspan="'.$deduction_count.'">Other Deduction</th>';
						

						?>
						
						
						<th  rowspan="2"  style='border:1px solid black;'>Net Pay</th>
						<th  rowspan="2"  style='border:1px solid black; ' class="no-print">DELETE</th>
					</tr>
					<tr class="tablepad">
						<!--<th>Income</th>
						<th>Deduction</th>
						<th>Total</th>-->
						<th style='border:1px solid black;'>Working</th>
						<th style='border:1px solid black;'>Leave</th>
						<th style='border:1px solid black;'>Present</th>
						<?php
						$tot_heads = array();
						
						$sql = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='income' and payslip_mode!='1'";
						$result_income = execute_query($sql);
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
							echo '<th style="border:1px solid black;">'.$row_deduction['head_name'].'</th>';	
						}
						echo'<th style="border:1px solid black;">Total Deduction</th>';
						$tot_heads['total_deduction'] = 0;

						?>
						
					</tr>
				</thead>
				<tbody>
					<?php
					$sql ='SELECT `payslip_report`.`sno` as payslip_id , `cloudice_uprnss_payroll`.`employee`.`sno` as emp_id , `cloudice_uprnss_payroll`.`employee`.`employee_name` ,`cloudice_uprnss_payroll`.`employee`.`employee_code` ,`cloudice_uprnss_payroll`.`employee`.`ehrms_id`, `cloudice_uprnss_payroll`.`payslip_report`.`company_id`, `cloudice_uprnss_payroll`.`employee`.`pay_level_id` , `cloudice_uprnss_payroll`.`employee`.`employee_designation` ,`cloudice_uprnss_payroll`.`employee`.`employee_category_id` ,`payslip_report`.`financial_year` , `cloudice_uprnss_payroll`.`payslip_report`.`for_month`, `cloudice_uprnss_payroll`.`payslip_report`.`main_basic`, `payslip_report`.`presented_days` , `payslip_report`.`granted_leave` , `cloudice_uprnss_payroll`.`payslip_report`.`salary_type` , `cloudice_uprnss_payroll`.`payslip_report`.`working_days` ,`payslip_report`.`total_income` ,  `cloudice_uprnss`.`dp_designation`.`designation`, `cloudice_uprnss`.`dp_designation`.`sort_no`,
					
					`payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`  
					
					left join `cloudice_uprnss`.`dp_designation` on `cloudice_uprnss`.`dp_designation`.`sno` = `cloudice_uprnss_payroll`.`employee`.`employee_designation`
					
					WHERE 1=1 ';
						
					if(isset($_POST['company_name'])){
						if($_POST['company_name'] != ''){
							$sql .= ' AND `payslip_report`.`company_id`="'.$_POST['company_name'].'" ';
						}
					}
					if(isset($_POST['type'])){
						if($_POST['type'] != ''){
							$sql .= ' AND `cloudice_uprnss_payroll`.`payslip_report`.`salary_type`="'.$_POST['type'].'" ';
						}
					}
					if(isset($_POST['emp_name'])){
						if($_POST['emp_name'] != ''){
							$sql .= ' AND `payslip_report`.`emp_id`="'.$_POST['emp_name'].'" ';
						}
					}
					if(isset($_POST['employee_category_id'])){
						if($_POST['employee_category_id']!=''){
							$sql .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
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
					$sql .= 'ORDER BY abs(`dp_designation`.`sort_no`)ASC , `payslip_report`.`main_basic` DESC';
					// echo $sql;
					$result = execute_query($sql);
					$i=1;
					$pb=1;
					$tot_main_basic=0; 
					$tot_net_pay=0; 
					$tot_net_totoal_deduction=0; 
					$tot_total_income=0; 
					while($row=mysqli_fetch_array($result)){ 
						$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$row['company_id'].'"';
							$result_company = mysqli_query($db_erp, $sql_company);
							$row_company = mysqli_fetch_array($result_company);
					
						$sql_attendance = 'SELECT * FROM `attendance_details` WHERE `emp_id`="'.$row['emp_id'].'" AND `salary_generation_month`="'.$row['for_month'].'" AND `financial_year`="'.$row['financial_year'].'"';
						$result_attendance = execute_query($sql_attendance);
						$row_attendance = mysqli_fetch_array($result_attendance);
							
							$sql = 'select * from dp_designation where sno="'.$row['employee_designation'].'"';
							$des = mysqli_query($db_erp, $sql);
							if(mysqli_num_rows($des)!=0){
								$des = mysqli_fetch_assoc($des);
								}
							else{
								unset($des);
								$des['designation'] = '';
							}
									
							
						if ($row['total'] > 0){
						
							echo "<tr";
							if ($pb >= 16 && ($pb - 16) % 18 === 0) {
								echo ' class="page-break"';
							}
							$pb++;
							echo ">";
							echo "<td style='border:1px solid black;'>".$i++."</td>";
							
							echo "<td style='border:1px solid black;'>".$row["employee_code"]."</td>
							<td style='border:1px solid black;'>".$row["ehrms_id"]."</td>
							<td style='border:1px solid black;'>".$row["employee_name"]."</td>
							<td style='border:1px solid black;'>".$des['designation']."</td>
							<td class='no-print' style='border:1px solid black;'>".$row_company["division_name"]."</td>
							<td class='no-print' style='border:1px solid black;'>".$row["financial_year"]."</td>
							<td class='no-print' style='border:1px solid black;'>".date('F',strtotime('01.'.$row["for_month"].'.2020'))."</td>
							<td style='border:1px solid black;'>".$row['working_days']."</td>
							<td style='border:1px solid black;'>".$row['granted_leave']."</td>
							<td style='border:1px solid black;'>".$row['presented_days']."</td>
							<td style='border:1px solid black;'>".$row['pay_level_id']."</td>
							<td style='border:1px solid black;'>".$row['main_basic']."</td>";
							$tot_main_basic+=$row['main_basic'];
							
							$sql_head_income = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='income' and payslip_mode!='1'";
						
							// $sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" and payslip_mode!="1"';
							$result_head_income = execute_query($sql_head_income);
							while($row_head_income = mysqli_fetch_array($result_head_income)){
								
								$sql_amount_income = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_income['sno'].'"';
								
								$result_income = execute_query($sql_amount_income);
								$row_amount_income = mysqli_fetch_array($result_income);
								
								echo "<td style='border:1px solid black;'>".$row_amount_income["amount"]."</td>";
								$tot_heads[$row_head_income['sno']] += $row_amount_income["amount"];
								
							}
							echo "<td style='border:1px solid black;'><b>".$row['total_income']."</b></td>";
							$tot_total_income += $row['total_income'];
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction" and payslip_mode!="1"';
							$result_head_deduction = execute_query($sql_head_deduction);
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								
								$sql_amount_deduction = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_deduction['sno'].'"';
								
								$result_deduction = execute_query($sql_amount_deduction);
								$row_amount_deduction = mysqli_fetch_array($result_deduction);
								
								echo "<td style='border:1px solid black;'>".round($row_amount_deduction["amount"])."</td>";
								$tot_heads[$row_head_deduction['sno']] += $row_amount_deduction["amount"];
								
							}
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='oth_deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="oth_deduction" and payslip_mode!="1"';
							$result_head_deduction = execute_query($sql_head_deduction);
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								
								$sql_amount_deduction = 'SELECT * FROM `payslip_details` WHERE `payslip_id`="'.$row['payslip_id'].'" AND `head_id`="'.$row_head_deduction['sno'].'"';
								
								$result_deduction = execute_query($sql_amount_deduction);
								$row_amount_deduction = mysqli_fetch_array($result_deduction);
								
								echo "<td style='border:1px solid black;'>".round($row_amount_deduction["amount"])."</td>";
								$tot_heads[$row_head_deduction['sno']] += $row_amount_deduction["amount"];
								
							}
							$tot_heads['total_deduction'] += $row['total_deduction'];
							echo "<td style='border:1px solid black;'>".$row['total_deduction']."</td>";
							$tot_net_totoal_deduction+=$row['total_deduction'];
							echo "<td style='border:1px solid black;'><b>".$row['total']."</b></td>";
							$tot_net_pay+=$row['total'];
							echo '<td class="no-print" ><a href="payroll_report_payslip.php?del_id='.$row['payslip_id'].'" '; echo 'onclick="alert(\'Are you sure?\')"'; echo '>Delete</a></td>
							</tr>';
							
							
						}
						
					} 
					?>
					<tr>
						
						<th colspan="3" class="no-print">&nbsp;</th>
						<th colspan="9" style="text-align: right;border:1px solid black;">Total:</th>
						<?php 
						
							echo "<th style='border:1px solid black;'>".$tot_main_basic."</th>";
							
							$sql_head_income = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='income' and payslip_mode!='1'";
						
							// $sql_head_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" and payslip_mode!="1"';
							
							$result_head_income = execute_query($sql_head_income);
							$total_income =0;
							while($row_head_income = mysqli_fetch_array($result_head_income)){
								echo "<th style='border:1px solid black;'>".$tot_heads[$row_head_income["sno"]]."</th>";
								
							}
							echo "<th style='border:1px solid black;'>".$tot_total_income."</th>";
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction" and payslip_mode!="1"';
							
							$result_head_deduction = execute_query($sql_head_deduction);
							
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								echo "<th style='border:1px solid black;'>".$tot_heads[$row_head_deduction["sno"]]."</th>";
								
							}
							
							$sql_head_deduction = "select * FROM payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno  where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='oth_deduction' and payslip_mode!='1'";
						
							// $sql_head_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="oth_deduction" and payslip_mode!="1"';
							$result_head_deduction = execute_query($sql_head_deduction);
							while($row_head_deduction = mysqli_fetch_array($result_head_deduction)){
								echo "<th style='border:1px solid black;'>".$tot_heads[$row_head_deduction["sno"]]."</th>";
								
							}
							echo "<th style='border:1px solid black;'>".$tot_net_totoal_deduction."</th>";
							echo "<th style='border:1px solid black;'>".$tot_net_pay."</th>";
						?>
						
						<th style='border:1px solid black;' class="no-print">&nbsp;</th>
					</tr>
				</tbody>
			</table>

			<!-- <div class="payroll-foot">
				mentioned and passed for payment of Rs. <?php //echo $tot_heads['gorss_pay']; ?> 
				<?php
					//$abc="123123";
				 //echo int_to_words($abc);?>
				
			</div> -->
			<div class="payroll-foot" style="font-size:1.5rem;font-weight:bold;">
				Sanctioned and passed for payment of Rs. <?php echo $tot_total_income; ?> 
				<?php
					// Assuming $tot_heads['gorss_pay'] is an array
					echo '( '.strtoupper(int_to_words($tot_total_income)).' )';

				?>
			</div>

			<div class="payroll-footer" style="display: flex; justify-content: space-between; margin: 100px !important;">
				<div class="equal-width">Junior Assistant</div>
				<div class="equal-width">Accountant</div>
				<div class="equal-width">DY Manager</div>
				<div class="equal-width">Manager</div>
				<div class="equal-width">DGM(F)</div>
			</div>

			<?php  } ?>
		</div>
	</div>
	<!-- <button  id="btn" class="no-print">Export Table Data To Excel File</button> -->
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

<script>
        document.getElementById("btn").addEventListener('click', function(){
            var table2excel = new Table2Excel();
            table2excel.export(document.querySelectorAll("#tblData"),"Table File");
                //in the second parameter we can declare the file name that we want to store
            });
    </script>