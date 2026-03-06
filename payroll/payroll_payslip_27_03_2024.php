<?php
include("scripts/settings.php");
page_header_start('Payslip');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$deduction=0;
$income=0;
$grand_total=0;
$var=1;
$emp_id='';
$msg='';
$msg12='';

	if(isset($_POST['confirm_submit'])){
		
		$sql_employee_attendance = 'SELECT * FROM `attendance_details` WHERE `company_id`="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `salary_generation_month`="'.$_POST['for_month'].'"';
		$result_employee_attendance = execute_query($sql_employee_attendance);
		$n = 1;
		//echo $sql_employee_attendance;
		while ($row_employee_attendance = mysqli_fetch_array($result_employee_attendance)) {
			$sql_check = 'SELECT * FROM `payslip_report` WHERE `emp_id`="'.$row_employee_attendance['emp_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `for_month`="'.$_POST['for_month'].'"';
			$result_check = execute_query($sql_check);

			$num_check = mysqli_num_rows($result_check);

			//echo $sql_check;
			if($num_check == 0){
				$sql_insert = 'INSERT INTO `payslip_report`(`emp_id`, `financial_year`, `main_basic`, `total_income`, `total_deduction`, `total`, `for_month`, `created_by`, `creation_time`) VALUES ("'.$row_employee_attendance['emp_id'].'" , "'.$_POST['financial_session'].'" , "'.$_POST['main_basic_value_'.$row_employee_attendance['emp_id'].''].'" , "'.$_POST['total_income_'.$row_employee_attendance['emp_id'].''].'" ,  "'.$_POST['tot_deduction_'.$row_employee_attendance['emp_id'].''].'" ,  "'.$_POST['net_pay_'.$row_employee_attendance['emp_id'].''].'" ,  "'.$_POST['for_month'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
				$res = execute_query($sql_insert);
				if(!mysqli_error($db)){
					$payslip_sno = mysqli_insert_id($db);
				}
				else{
					$msg .= "Error:1 " . $sql_insert . " >> ".mysqli_error($db);
				}
				$sql_salary = 'SELECT head_id, payslip_mode, head_name, head_type, value_type, percent_of, recurrence_period, head_value, payslip_mode FROM `salary_structure` left join head_type on head_type.sno = head_id WHERE `emp_id`="'.$row_employee_attendance['emp_id'].'" and payslip_mode !="1"';
				
				// echo $sql_salary.'<br>';
				$result_salary = execute_query($sql_salary);
				$data_array = array();	
				while($row_heads = mysqli_fetch_assoc($result_salary)){
					$data_array[$row_heads['head_id']] = $row_heads;
				}
				foreach($data_array as $k=>$v){
					$sql = 'insert into payslip_details (payslip_id, head_id, amount) values("'.$payslip_sno.'", "'.$v['head_id'].'", "'.$_POST['head_value_'.$v['head_id'].'_'.$row_employee_attendance['emp_id'].''].'")';
					execute_query($sql);
					//echo $sql.'<br>';
					if(mysqli_error($db)){
						$msg .= 'Error # 09 : '.mysqli_error($db).' >> '.$sql;
					}
				}	
			}
			else{
				$msg12 .= '<div class="alert alert-danger">Already Generated.</li>';
			}	
		}
		if($msg12 != ''){
			$msg .= '<div class="alert alert-danger">Already Generated.</li>';
		}
		if($msg == ''){
			$msg .= '<div class="alert alert-success">Generated.</li>';	
		}
	}


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
			<div class="col-sm-12"><?php echo $msg; ?></div>
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
						<input type="submit" name="search" value="Search" id="search" class="form-control btn btn-info">
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	<div class="row" style="padding:10px;">
	<div class="col-md-12">
		<?php if(isset($_POST['search'])){
			$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_id'].'"';
			$result_company = mysqli_query($db_erp, $sql_company);
			$row_company = mysqli_fetch_array($result_company);
		?>
		<div class="panel" style="margin-top:40px;">
			<div class="panel-heading">
				<b>Division Name : <?php echo $row_company['division_name']; ?></b>
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
							<th colspan="3" style="text-align: center;">Days</th>
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
							<th style="text-align: center;">Working</th>
							<th style="text-align: center;">Leave</th>
							<th style="text-align: center;">Present</th>
							
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

						
						$sql_employee_attendance = 'SELECT * FROM `attendance_details` left JOIN employee on attendance_details.emp_id=employee.sno WHERE attendance_details.company_id="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `salary_generation_month`="'.$_POST['for_month'].'"';
						
						if(isset($_POST['employee_category_id'])){
							if($_POST['employee_category_id']!=''){
								$sql_employee_attendance .= ' and employee_category_id="'.$_POST['employee_category_id'].'"';
							}
						}
						
						// echo $sql_employee_attendance;
						
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
						while ($row_employee_attendance = mysqli_fetch_array($result_employee_attendance)) {

								$sql_employee = 'SELECT * FROM `employee` WHERE `sno`="'.$row_employee_attendance['emp_id'].'"';
								$result_employee = execute_query($sql_employee);
								$row_employee = mysqli_fetch_array($result_employee);
								
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
								<th width="2%"><?php echo ++$n; ?></th>
								<td width="4%"><?php echo $row_employee['employee_code']; ?></td>
								<td width="7%"><?php echo $row_employee['employee_name']; ?></td>
								<td width="3%"><?php echo $des['designation']; ?></td>
								<td width="2%"><?php echo $row_employee_attendance['working_days']; ?></td>
								<td width="2%"><?php echo $row_employee_attendance['granted_leave']; ?></td>
								<td width="2%"><?php echo $row_employee_attendance['presented_days']; ?></td>
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
										if($row_heads['head_id'] == '1' ){
											$main_basic_value=$row_heads['head_value'];
											
											echo'<td width="3%">'. $row_heads['head_value'].'</td>
											<input type="hidden" name="main_basic_value_'.$row_employee['sno'].'"  id="main_basic_value_'.$row_employee['sno'].'" value="'.$row_heads['head_value'].'"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>">';
											//$row_heads['head_value'] = (float)$row_heads['head_value'];
											$grand_main_basic_value+=$main_basic_value;
										
										}
										//print ($row_heads['payslip_mode']);
										if($row_heads['head_type'] == 'income' ){
											$total_income_col_count++;
										}
										if ($row_heads['head_id'] == 1 || $row_heads['head_id'] == 6 || $row_heads['head_id'] == 7) {
										$basic_value = $row_heads['head_value']/$row_employee_attendance['working_days']*$row_employee_attendance['presented_days'];
											$row_heads['head_value']=$basic_value;
										}
										$data_array[$row_heads['head_id']] = $row_heads;
										$data_array[$row_heads['head_id']]['net_value'] = 0;
									
										
									}
										
									
									//$total_income_col_count++;
									$count=0;
									foreach($data_array as $k=>$v){
										//print_r($v);
										if($v['value_type']=='value'){
											$data_array[$v['head_id']]['net_value'] = round(floatval($v['head_value']));
											
											echo '<td   width="3%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($v['head_value'] ).'"class="form-control" placeholder="" tabindex=""></td>';
											// echo '<td>'.$main_basic_print.'</td>';
										}
										elseif($v['value_type']=='percent'){
											
										if($row_employee['pay_commission']=="1"){
											if($v['head_id']=="9" || $v['head_id']=="16" || $v['head_id']=="36"){
												$basic = $data_array['1']['net_value'];
												$da = $data_array['4']['net_value'];
												$grade_pay = $data_array['3']['net_value'];
												$val = ($basic+$da+$grade_pay)*$data_array[$v['head_id']]['head_value']/100;
												$data_array[$v['head_id']]['net_value'] = $val;
												
												echo '<td width="3%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($val).'"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>"></td>';
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
												
												echo '<td width="4%"><input type="text" name="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'"  id="head_value_'.$v['head_id'].'_'.$row_employee['sno'].'" value="'.round($val).'"class="form-control" placeholder="" tabindex="<?php echo $tab++;?>"></td>';
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
														//echo '<h1>'.$step_result.'</h1>';
														
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
						?>
						<input type="hidden" name="number_of_entries" id="number_of_entries" value="<?php echo $n; ?>">
						<tr>
							<th colspan="7" style="text-align: right;">Total:</th>
							
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
		<?php } ?>
	</form>
</div>
<?php
page_footer();
?>
