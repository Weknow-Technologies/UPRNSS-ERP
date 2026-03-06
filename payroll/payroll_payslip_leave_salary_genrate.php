<?php
include("scripts/settings.php");

navigation($_SERVER['PHP_SELF']);
$deduction=0;
$income=0;
$grand_total=0;
$var=1;
$emp_id='';
$msg='';
$msg12='';

	if(isset($_POST['confirm_submit'])){
		
	$sql_employee_attendance = 'SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as sno , `employee`.`employee_name` ,`employee`.`employee_code` ,`employee`.`ehrms_id`, `employee`.`company_id`, `employee`.`pay_level_id` ,`employee`.`pay_commission` , `employee`.`employee_designation` ,`employee`.`employee_category_id` ,`payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`main_basic`, sum(`payslip_report`.`presented_days`)as presented_days, `payslip_report`.`granted_leave` , `payslip_report`.`working_days` ,`payslip_report`.`total_income` , 			
	`payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`
	WHERE payslip_report.granted_leave!="0" and employee.company_id="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `for_month`="'.$_POST['for_month'].'"  AND `employee_category_id`="'.$_POST['employee_category_id'].'"  group by emp_id';
	
	
		$result_employee_attendance = execute_query($sql_employee_attendance);
		$n = 1;
		//echo $sql_employee_attendance;
		while ($row_employee_attendance = mysqli_fetch_array($result_employee_attendance)) {
			
			if ($_POST['leave_days_'.$row_employee_attendance['sno']] != "" && $_POST['leave_days_'.$row_employee_attendance['sno']] != "0") {
				
				
				$year = explode("-", $_POST['financial_session']);
				$year_to_store = '';
				$month=$_POST['for_month'];
				if($month>=1 and $month<=3){
					$year_to_store = $year[1];
				}
				else{
					$year_to_store = $year[0];
				}
				$date_to_store = $year_to_store.'-'.$month.'-01';
				
				$sql_insert = 'INSERT INTO `payslip_report`(`emp_id`, `financial_year`, `main_basic`, `total_income`, `total_deduction`, `total`, `for_month`, `created_by`, `creation_time`, salary_type, working_days, presented_days, granted_leave, company_id, salary_month) VALUES ("'.$row_employee_attendance['sno'].'" , "'.$_POST['financial_session'].'" , "'.$_POST['main_basic_value_'.$row_employee_attendance['sno'].''].'" , "'.$_POST['total_income_'.$row_employee_attendance['sno'].''].'" ,  "'.$_POST['tot_deduction_'.$row_employee_attendance['sno'].''].'" ,  "'.$_POST['net_pay_'.$row_employee_attendance['sno'].''].'" , "'.$_POST['for_month'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'",
				"Leave","'.$_POST['working_days_'.$row_employee_attendance['sno'].''].'", "'.$_POST['leave_days_'.$row_employee_attendance['sno']].'" , "'.$_POST['granted_leave_'.$row_employee_attendance['sno'].''].'","'.$_POST['company_id'].'", "'.$date_to_store.'" )';
				$res = execute_query($sql_insert);
				
				if(!mysqli_error($db)){
					$payslip_sno = mysqli_insert_id($db);
					
				}
				else{
					$msg .= "Error:1 " . $sql_insert . " >> ".mysqli_error($db);
				}
				$sql_salary = 'SELECT head_id, payslip_mode, head_name, head_type, value_type, percent_of, recurrence_period, head_value, payslip_mode FROM `salary_structure` left join head_type on head_type.sno = head_id WHERE `emp_id`="'.$row_employee_attendance['sno'].'" and payslip_mode !="1"';
				
				// echo $sql_salary.'<br>';
				$result_salary = execute_query($sql_salary);
				$data_array = array();	
				
				while($row_heads = mysqli_fetch_assoc($result_salary)){
					$data_array[$row_heads['head_id']] = $row_heads;
				}
				// foreach($data_array as $k=>$v){
					// echo $sql = 'insert into payslip_details (payslip_id, head_id, amount, base_amount, head_name, head_type, value_type, percent_of, recurrence_period) values("'.$payslip_sno.'", "'.$v['head_id'].'", "'.$_POST['head_value_'.$v['head_id'].'_'.$row_employee_attendance['sno'].''].'", "'.$_POST['head_value_orig_'.$v['head_id'].'_'.$row_employee_attendance['sno'].''].'", "'.$v['head_name'].'", "'.$v['head_type'].'", "'.$v['value_type'].'", "'.$v['percent_of'].'", "'.$v['recurrence_period'].'")';
					// execute_query($sql);
					// //echo $sql.'<br>';
					// if(mysqli_error($db)){
						// $msg .= 'Error # 09 : '.mysqli_error($db).' >> '.$sql;
					// }
				// }
				foreach($data_array as $k=>$v){
					$sql = 'insert into payslip_details (payslip_id, head_id, amount, 
					base_amount, base_value, head_name, head_type, value_type, percent_of, recurrence_period) values(
					
					"'.$payslip_sno.'", 
					"'.$v['head_id'].'", 
					"'.$_POST['head_value_'.$v['head_id'].'_'.$row_employee_attendance['sno'].''].'", 
					"'.$_POST['head_value_orig_'.$v['head_id'].'_'.$row_employee_attendance['sno'].''].'",
					"'.$v['head_value'].'", 
					"'.$v['head_name'].'", 
					"'.$v['head_type'].'", 
					"'.$v['value_type'].'", 
					"'.$v['percent_of'].'", 
					"'.$v['recurrence_period'].'")';
					execute_query($sql);
					//echo $sql.'<br>';
					if(mysqli_error($db)){
						$msg .= 'Error # 09 : '.mysqli_error($db).' >> '.$sql;
					}
				}
			}	
		}
		if($msg == ''){
			$msg .= '<div class="alert alert-success">Generated.</li>';	
					foreach ($_POST as $key => $value) {
					unset($_POST[$key]);
				}
		}
	}
	


//print_r($_POST);
page_header_start('Leave Salay Genrate');
page_header_end();
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
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
		<div class="container">
			<div class="row" style="padding:10px;">
			<div class="col-sm-12"><?php echo $msg; ?></div>
			<div class="col-sm-12"><?php echo $msg12; ?></div>
				<div class="col-md-12">
					<?php //if(isset($_POST['search'])){
						$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_id'].'"';
						$result_company = mysqli_query($db_erp, $sql_company);
						$row_company = mysqli_fetch_array($result_company);
					?>
					<div class="panel" style="margin-top:40px;">
						<div class="panel-heading">
						
							<h4 class="text-center" style="display: block; text-align: center;">Leave salary :</h4>

							<table border="0px" width="100%">
							<td>Division Name : <?php echo $row_company['division_name']; ?></td>
							<td>Financial Session</td>
								<td>
									<select name="financial_session1" class="form-control" disabled>
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
								<td>For Month</td>
								<td>
									<select class="form-control"  name="for_month1" id="title" disabled>
										<?php
										$month=1;
										$current_month = date('m');

										while($month<=12){
											echo '<option value="'.date('m',strtotime('01.'.$month.'.2020')).'"'; 
											if(isset($_POST['for_month'])){
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
							</table>
						</div>
						<div class="panel-body" style="">
							<table class="table table-hover table-responsive table-bordered table-striped text-center" id="payroll" style="">
								<thead>
									<tr>

										<!-- first row adding -->

										<th rowspan="2" style="text-align: center;">S.No.</th>
										<th rowspan="2" style="text-align: center;">Employee Code</th>
										<th rowspan="2" style="text-align: center;">Employee Name</th>
										<th rowspan="2" style="text-align: center;">Designation</th>
										<th rowspan="2" style="text-align: center;">salary Days</th>
										<th rowspan="2" style="text-align: center;">main Basic</th>
										

										<?php

											//printing INCOME and getting its total column number for colspan

											$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='income' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";

											// $sql = 'select * from head_type where head_type="income" and payslip_mode!="1"';
											$result_income = execute_query($sql);
											$income_count = mysqli_num_rows($result_income);
											echo '<th colspan="'.($income_count+1).'">Income</th>';
											


											//printing DEducation  and getting its total column number for colspan



											
											// echo $income_count+$i;

											$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='deduction' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";

											// $sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';

											$result_income = execute_query($sql);
											$income_count = mysqli_num_rows($result_income);
											echo '<th colspan="'.($income_count).'">Deduction</th>';
											

											//printing Other Deducation  and getting its total column number for colspan

											$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='oth_deduction' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}'";

											// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode!="1"';

											$i=1;//may be for totel deduction columns

											$result_oth_deduction = execute_query($sql);
											$income_oth_deduction = mysqli_num_rows($result_oth_deduction);
											echo '<th colspan="'.($income_oth_deduction+$i).'">Other Deduction</th>';
											
										?>
										<th rowspan="2" style="text-align: center;"> Net Pay</th>
									</tr>
									<tr>
										
										<?php

										$tot_heads = array();//here we store all heads sno 
										

										$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='income' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}' order by abs(sort_no)";

										// $sql = 'select * from head_type where head_type="income" and payslip_mode!="1" order by abs(sort_no)';

										$result_income = execute_query($sql);
										// print_r($result_income);
										while($row_income = mysqli_fetch_assoc($result_income)){
											$tot_heads[$row_income['sno']] = 0;//////////////////? storing sno of head_type
											echo '<th>'.$row_income['head_name'].'</th>';
				
										}

										//nhi dikhega

										$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='income' and head_type.payslip_mode='1' and cat_id='{$_POST['employee_category_id']}' order by abs(sort_no)";

										// $sql = 'select * from head_type where head_type="income" and payslip_mode="1" order by abs(sort_no)';
										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){
											$tot_heads[$row_income['sno']] = 0;///storing sno of head_type
											//echo '<th>'.$row_income['head_name'].'</th>';
										}
										
										echo'<th style="text-align: center;">Gross Pay</th>';
										

										$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='deduction' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}' order by abs(sort_no)";

										// $sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1" order by abs(sort_no)';

										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){
											$tot_heads[$row_income['sno']] = 0;
											echo '<th>'.$row_income['head_name'].'</th>';	
										}

										
										// nhi dikhega

										$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='deduction' and head_type.payslip_mode='1' and cat_id='{$_POST['employee_category_id']}' order by abs(sort_no)";

										// $sql = 'select * from head_type where head_type="deduction" and payslip_mode="1" order by abs(sort_no)';

										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){
											$tot_heads[$row_income['sno']] = 0;
											//echo '<th>'.$row_income['head_name'].'</th>';	
										}


										$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='oth_deduction' and head_type.payslip_mode!='1' and cat_id='{$_POST['employee_category_id']}' order by abs(sort_no)";

										// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode!="1" order by abs(sort_no)';
										$result_oth_deduction = execute_query($sql);
										while($row_oth_deduction = mysqli_fetch_assoc($result_oth_deduction)){
											$tot_heads[$row_oth_deduction['sno']] = 0;
											echo '<th>'.$row_oth_deduction['head_name'].'</th>';	
										}

										// nhi dikhega

										$sql ="SELECT * from payroll_head_access LEFT JOIN head_type on payroll_head_access.head_id=head_type.sno WHERE head_type.head_type='oth_deduction' and head_type.payslip_mode='1' and cat_id='{$_POST['employee_category_id']}' order by abs(sort_no)";

										// $sql = 'select * from head_type where head_type="oth_deduction" and payslip_mode="1" order by abs(sort_no)';
										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){
											$tot_heads[$row_income['sno']] = 0;
										}
										
										echo'<th style="text-align: center;">Total Deduction</th>';
				
										?>
									</tr>
								</thead>
								<tbody>
									<?php 
									
									$sql_employee_attendance = 'SELECT `payslip_report`.`sno` as payslip_id , `employee`.`sno` as sno , `employee`.`employee_name` ,`employee`.`employee_code` ,`employee`.`ehrms_id`, `employee`.`company_id`, `employee`.`pay_level_id` ,`employee`.`pay_commission` , `employee`.`employee_designation` ,`employee`.`employee_category_id` ,`payslip_report`.`financial_year` , `payslip_report`.`for_month`, `payslip_report`.`main_basic`, sum(`payslip_report`.`presented_days`)as presented_days, `payslip_report`.`granted_leave` , `payslip_report`.`working_days` ,`payslip_report`.`total_income` , 
					
									`payslip_report`.`advance_amount` , `payslip_report`.`total_deduction` , `payslip_report`.`total` FROM `payslip_report` join `employee` on `employee`.`sno` = `payslip_report`.`emp_id`
									WHERE payslip_report.granted_leave!="0" and employee.company_id="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `for_month`="'.$_POST['for_month'].'"  AND `employee_category_id`="'.$_POST['employee_category_id'].'"  group by emp_id'; 
									
									
									$result_employee_attendance = execute_query($sql_employee_attendance);
									$n = 0;
									$salary_days = 0;
									$deduction = 0;
									
									
									$total_payable = 0;
									$total_deduction = 0;
									$grand_total = 0;
									$grand_total_income = 0;
									$grand_total_deduction = 0;
									$grand_main_basic_value = 0;
												
										//echo $sql_employee_attendance;
									while ($row_employee = mysqli_fetch_array($result_employee_attendance)) {

											// $sql_employee = 'SELECT * FROM `employee` WHERE `sno`="'.$row_employee_attendance['emp_id'].'"';
											// $result_employee = execute_query($sql_employee);
											// $row_employee = mysqli_fetch_array($result_employee);
											
											$sql = 'select * from dp_designation  where sno="'.$row_employee['employee_designation'].'"';
											$des = mysqli_query($db_erp, $sql);
											if(mysqli_num_rows($des)!=0){
												$des = mysqli_fetch_assoc($des);
												}
											else{
												unset($des);
												$des['designation'] = '';
											}
											
											
										?>
										
										<tr>
										<?php if ($_POST['leave_days_'.$row_employee['sno']] != "" && $_POST['leave_days_'.$row_employee['sno']] != "0") { ?>
										
											<th width="2%"><?php echo ++$n; ?></th>
											<td width="4%"><?php echo $row_employee['employee_code']; ?></td>
											<td width="7%"><?php echo $row_employee['employee_name']; ?></td>
											<td width="3%"><?php echo $des['designation']; ?></td>
											<td width="2%"><?php echo $_POST['leave_days_'.$row_employee['sno']]; ?></td>
											
											<?php
											echo'<input type="hidden" name="company_id" value="'. $_POST["company_id"].'" id="" >
												<input type="hidden" name="financial_session" value="'. $_POST["financial_session"].'" id="" >
												<input type="hidden" name="employee_category_id" value="'. $_POST["employee_category_id"].'" id="" >
												<input type="hidden" name="for_month" value="'. $_POST["for_month"].'" id="leave_days" >
												<input type="hidden" name="leave_days_'.$row_employee['sno'].'" value="'. $_POST['leave_days_'.$row_employee['sno']].'" id="" >';
											?>
											<?php 
												$sql_salary = "SELECT *
											FROM salary_structure
											LEFT JOIN  head_type on head_type.sno = salary_structure.head_id
											LEFT JOIN payroll_head_access on payroll_head_access.head_id=head_type.sno
											WHERE cat_id='{$_POST['employee_category_id']}' and `emp_id`='".$row_employee['sno']."' order by abs(sort_no)";
											
											//echo $sql_salary.'<br>';
											$result_salary = execute_query($sql_salary);
											$data_array = array();
											$tot_income = 0; 
											$tot_deduction = 0; 
											$tot_oth_deduction = 0; 
											$total_income_col_count = 0;
											$main_basic_value = 0;
											
											while($row_heads = mysqli_fetch_assoc($result_salary)){
												$original_value = $row_heads['head_value'];
												if($row_heads['head_id'] == '1' ){
													$main_basic_value=$row_heads['head_value'];
													
													echo'<td width="3%">'. $row_heads['head_value'].'</td>
													<input type="hidden" name="main_basic_value_'.$row_employee['sno'].'"  id="main_basic_value_'.$row_employee['sno'].'" value="'.$row_heads['head_value'].'" class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">';
													//$row_heads['head_value'] = (float)$row_heads['head_value'];
													$grand_main_basic_value+=$main_basic_value;
												
												}
												//print ($row_heads['payslip_mode']);
												if($row_heads['head_type'] == 'income' ){
													$total_income_col_count++;
												}
												if ($row_heads['head_id'] == 1 || $row_heads['head_id'] == 6 || $row_heads['head_id'] == 7) {
												$basic_value = $row_heads['head_value']/$row_employee['working_days']*$_POST['leave_days_'.$row_employee['sno']];
													$row_heads['head_value']=$basic_value;
												}
												$data_array[$row_heads['head_id']] = $row_heads;
												$data_array[$row_heads['head_id']]['net_value'] = 0;
												$data_array[$row_heads['head_id']]['original_value'] = $original_value;
											}
												
											//print_r($data_array['27']);
											//die();
											//$total_income_col_count++;
											$count=0;
											foreach($data_array as $k=>$v){
												//print_r($v);
												if($v['value_type']=='value'){
													
													if($v['head_id']==17 || $v['head_id']==18){
														$v['head_value']=0;
														 $data_array[$v['head_id']]['net_value'] = round(floatval($v['head_value']));
													
														echo '<td   width="3%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($v['head_value'] ).'"class="form-control" placeholder="" tabindex="">
														<input type="hidden" name="head_value_orig_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.$v['original_value'].'"> 
														</td>';
														// echo '<td>'.$main_basic_print.'</td>';
														//echo $v['head_id']['original_value'];
													
													}else{
														
													$data_array[$v['head_id']]['net_value'] = round(floatval($v['head_value']));
													
														echo '<td   width="3%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($v['head_value'] ).'"class="form-control" placeholder="" tabindex="">
														<input type="hidden" name="head_value_orig_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.$v['original_value'].'"> 
														</td>';
														// echo '<td>'.$main_basic_print.'</td>';
														//echo $v['head_id']['original_value'];
													}
												}
												elseif($v['value_type']=='percent'){
													
													if($row_employee['pay_commission']=="1"){
														if($v['head_id']=="9" || $v['head_id']=="16" || $v['head_id']=="36"){
															$basic = $data_array['1']['net_value'];
															$da = $data_array['4']['net_value'];
															$grade_pay = $data_array['3']['net_value'];
															$val = ($basic+$da+$grade_pay)*$data_array[$v['head_id']]['head_value']/100;
															$data_array[$v['head_id']]['net_value'] = $val;
															
															
															echo '<td width="3%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($val).'" class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">';
															
															$basic = $data_array['1']['original_value'];
															$da = $data_array['4']['original_value'];
															$grade_pay = $data_array['3']['original_value'];
															$val = ($basic+$da+$grade_pay)*$data_array[$v['head_id']]['head_value']/100;
															
															$v['original_value'] = $val;
															
															echo '
															<input type="hidden" name="head_value_orig_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.$v['original_value'].'">
															
															</td>';
															// if($v['head_id']=='36'){
																
																// echo  $row_employee['sno'].'<br>';
																// echo  $val.'<br>';
																// // echo $v['percent_of'].'<br>';
																// print_r($data_array[$v['percent_of']]);
															// }
														}
														else{
															goto process;
														}
													}
													else{
														process:
														$val = (float)$data_array[$v['percent_of']]['net_value']*(float)$v['head_value']/100;
														$data_array[$v['head_id']]['net_value'] = round($val,3);
														$data_array[$v['head_id']]['head_value'] = round($val);
														
														echo '<td width="4%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($val).'"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">';
														
														
													// echo	$da = $data_array['4']['original_value'];
														
														$val = (float)$data_array[$v['percent_of']]['original_value']*(float)$v['original_value']/100;
														$v['original_value'] = $val;
														$data_array[$v['head_id']]['original_value'] = $val;
														
														
														
														echo '<input type="hidden" name="head_value_orig_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.$val.'">
														</td>';
														// echo '<td>'.$val.'</td>';
														if($v['head_id']=='36'){
															// echo $v['percent_of'].'<br>';
															// print_r($data_array[$v['percent_of']]);
														}
													}
												}
												elseif($v['value_type']=='formula'){
													//print_r($v);
													$sql = 'select * from head_type_formula where head_id="'.$v['head_id'].'"';
													//echo $sql;
													$result_formula = execute_query($sql);
													$step_result = 0;
													$tst = '';
													while($row_formula = mysqli_fetch_assoc($result_formula)){
														switch($row_formula['operator']){
															case '+' :{
																$tst = $data_array[$row_formula['var_a']]['net_value'].' + '.$data_array[$row_formula['var_b']]['net_value'];
																$step_result = $data_array[$row_formula['var_a']]['net_value'] + $data_array[$row_formula['var_b']]['net_value'];
																$step_result_original = $data_array[$row_formula['var_a']]['original_value'] + $data_array[$row_formula['var_b']]['original_value'];
																// echo '<h1>'.$step_result_original.' >> '.$data_array[$row_formula['var_b']]['original_value'].'</h1>';
																
																break;
															}
															case '-' :{
															
																break;
															}
															case '*' : {
															
																break;
															}
															case '/':{
															
																break;
															}
															
														}
														
														
													}
													// echo '<h1>'.$step_result.'</h1>';
													//$step_result = $step_result*(float)$v['head_value']/100;
													$data_array[$v['head_id']]['net_value'] = $step_result;
													$data_array[$v['head_id']]['original_value'] = $step_result_original;
													
													
												}
												elseif($v['value_type']=='other'){
													//print_r($v);
													$sql = 'select * from head_type_formula where head_id="'.$v['head_id'].'"';
													// echo $sql;
													$result_formula = execute_query($sql);
													$step_result_oth = 0;
													$tst = '';
													$row_formula = mysqli_fetch_assoc($result_formula);
													// while($row_formula = mysqli_fetch_assoc($result_formula)){
														switch($row_formula['operator']){
															case '+' :{
																$tst = $data_array[$row_formula['var_a']]['net_value'].' + '.$data_array[$row_formula['var_b']]['net_value'];
																$step_result_oth = $data_array[$row_formula['var_a']]['net_value'] + $row_formula['var_b'];
																$step_result_original_oth = $data_array[$row_formula['var_a']]['original_value'] + $data_array[$row_formula['var_b']]['original_value'];
																//echo '<h1>'.$step_result_oth.'</h1>';
																
																break;
															}
															case '-' :{
															
																break;
															}
															case '*' : {
																// echo $row_formula['var_a'];
																$tst = round($data_array[$row_formula['var_a']]['net_value']).' * '.$row_formula['var_b'];
																$step_result_oth = round($data_array[$row_formula['var_a']]['net_value']) * $row_formula['var_b'];
																$val = round($data_array[$row_formula['var_a']]['original_value']) * $row_formula['var_b'];
																// echo '<h1>'.$data_array[$row_formula['var_a']]['original_value'].'</h1>';
																break;
															}
															case '/':{
															
																break;
															}
															
														}
														
														
													// }
													
													$data_array[$v['head_id']]['net_value'] = $step_result_oth;
													$data_array[$v['head_id']]['original_value'] = $val;
													
													echo'<td width="4%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($data_array[$v['head_id']]['net_value']).'"class="form-control" placeholder="" tabindex="">
													
													<input type="hidden" name="head_value_orig_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.$data_array[$v['head_id']]['original_value'].'"></td>';
													
												}
												
												if($v['head_type']=='income'){
													
													if($v['head_id']!='8'  && $v['head_id'] != '27'){
														$tot_income += $data_array[$v['head_id']]['net_value'];										
													}
												}
												else{
													$tot_deduction += $data_array[$v['head_id']]['net_value'];
													
												}
												$tot_heads[$v['head_id']]+= $data_array[$v['head_id']]['net_value'];
												$count++;
												// echo $total_income_col_count.'@@@'.$count.'<br>';
												if($total_income_col_count==$count){
													echo '<td width="5%"><input type="text" name="total_income_'.$row_employee['sno'].'"  id="total_income_'.$row_employee['sno'].'" value="'.round($tot_income).'"class="form-control" placeholder=""></td>';
													// echo '<td>'.round($tot_income, ).'</td>';
												}
												
											}
											
											echo '<td width="4%"><input type="text" name="tot_deduction_'.$row_employee['sno'].'"  id="tot_deduction_'.$row_employee['sno'].'" value="'.round($tot_deduction, ).'"class="form-control" placeholder=""></td>';
											// echo '<td>'.round($tot_deduction, ).'</td>';
											
											echo '<td width="6%"><input type="text" name="net_pay_'.$row_employee['sno'].'"  id="net_pay_'.$row_employee['sno'].'" value="'.round($tot_income-$tot_deduction ).'"class="form-control" placeholder=""></td>';
											// echo '<td>'.round($tot_income-$tot_deduction ).'</td>';
											$grand_total += round($tot_income-$tot_deduction, );
											
											
										?>
									</tr>
								<?php
											
										}
									} 
									?>
									<input type="hidden" name="number_of_entries" id="number_of_entries" value="<?php echo $n; ?>">
									<tr>
										<th colspan="5" style="text-align: right;">Total:</th>
										
										<?php
										echo'<th style="text-align: right;">'.$grand_main_basic_value.'</th>';
										
										$sql = "select * from payroll_head_access LEFT JOIN  head_type on payroll_head_access.head_id=head_type.sno where cat_id='{$_POST['employee_category_id']}' and  head_type.head_type='income'   and payslip_mode='0' order by abs(sort_no)";
										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){ 
											
											echo '<th><input type="text" name=""  id="" value="'.round($tot_heads[$row_income['sno']]).'"class="form-control" placeholder=""></th>';
											// echo '<th>'.$tot_heads[$row_income['sno']].'</th>';
											$grand_total_income += $tot_heads[$row_income['sno']];								
										}
										echo '<th><input type="text" name="gross pay total"  id="" value="'.round($grand_total_income).'"class="form-control" placeholder=""></th>';	
										// echo '<th>'.round($grand_total_income).'</th>';
										
										$sql = "select *  from payroll_head_access LEFT JOIN  head_type on payroll_head_access.head_id=head_type.sno where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='deduction' and payslip_mode='0' order by abs(sort_no)";
										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){
											echo '<th><input type="text" name=""  id="" value="'.round($tot_heads[$row_income['sno']]).'"class="form-control" placeholder=""></th>';	
											// echo '<th>'.round($tot_heads[$row_income['sno']]).'</th>';	
											$grand_total_deduction += $tot_heads[$row_income['sno']];
										}
										$sql = "select *  from payroll_head_access LEFT JOIN  head_type on payroll_head_access.head_id=head_type.sno where cat_id='{$_POST['employee_category_id']}' and head_type.head_type='oth_deduction' and payslip_mode='0' order by abs(sort_no)";
										$result_income = execute_query($sql);
										while($row_income = mysqli_fetch_assoc($result_income)){
											echo '<th><input type="text" name=""  id="" value="'.round($tot_heads[$row_income['sno']]).'"class="form-control" placeholder=""></th>';	
											// echo '<th>'.round($tot_heads[$row_income['sno']]).'</th>';	
											$grand_total_deduction += $tot_heads[$row_income['sno']];
										}
										echo '<th><input type="text" name=""  id="" value="'.round($grand_total_deduction).'"class="form-control" placeholder=""> </th>';
										// echo '<th>'.round($grand_total_deduction).'</th>';
										
										echo '<th><input type="text" name=""  id="" value="'.round($grand_total).'"class="form-control" placeholder=""></th>';
										// echo '<th>'.round($grand_total).'</th>';
										?>
									</tr>
								</tbody>
							</table>
							<div class="row">
								<div class="col-sm-3">&nbsp;</div>
								<div class="col-sm-6">
									<input type="submit" name="confirm_submit" id="confirm_submit" value="SUBMIT" class="form-control btn btn-info">
								</div>
							</div>
						</div>
					</div>
					<?php //} ?>
				</div>
			</div>
		</div>
	</form>
<?php
page_footer();
?>
