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
	$salary_days = 0;
	$allowances = 0;
	$deduction = 0;
	$total_basic_salary = 0;
	$total_salary_amount = 0;
	$total_payable = 0;
	$total_deduction = 0;
	$total_allownces = 0;
	$grand_total = 0;
	//echo $sql_employee_attendance;
	while ($row_employee_attendance = mysqli_fetch_array($result_employee_attendance)) {
		$sql_check = 'SELECT * FROM `payslip_report` WHERE `emp_id`="'.$row_employee_attendance['emp_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `for_month`="'.$_POST['for_month'].'"';
		$result_check = execute_query($sql_check);
		$num_check = mysqli_num_rows($result_check);
		//echo $sql_check;
		if($num_check == 0){
			$sql_insert = 'INSERT INTO `payslip_report`(`emp_id`, `financial_year`, `for_month`, `created_by`, `creation_time`) VALUES ("'.$row_employee_attendance['emp_id'].'" , "'.$_POST['financial_session'].'" , "'.$_POST['for_month'].'" , "'.$_SESSION['username'].'" , "'.date('Y-m-d h:i:s').'")';
			$res = execute_query($sql_insert);
			if(!mysqli_error($db)){
				$payslip_sno = mysqli_insert_id($db);
			}
			else{
				$msg .= "Error:1 " . $sql_insert . " >> ".mysqli_error($db);
			}

			$sql_salary = 'SELECT head_id, head_name, head_type, value_type, percent_of, recurrence_period, head_value FROM `salary_structure` left join head_type on head_type.sno = head_id WHERE `emp_id`="'.$row_employee_attendance['emp_id'].'"';
			//echo $sql_salary.'<br>';
			$result_salary = execute_query($sql_salary);
			$data_array = array();
			$tot_income = 0; 
			$tot_deduction = 0; 
			while($row_heads = mysqli_fetch_assoc($result_salary)){
				$data_array[$row_heads['head_id']] = $row_heads;
				$data_array[$row_heads['head_id']]['net_value'] = 0;
			}
			// foreach($data_array as $k=>$v){
				// if($v['value_type']=='value'){
					// $data_array[$v['head_id']]['net_value'] = round($v['head_value'] , 3);
				// }
				// elseif($v['value_type']=='percent'){
					// $val = $data_array[$v['percent_of']]['head_value']*$v['head_value']/100;
					// $data_array[$v['head_id']]['net_value'] = round($val, 3);
				// }
				// elseif($v['value_type']=='formula'){
					// $sql = 'select * from head_type_formula where head_id="'.$v['head_id'].'"';
					// //echo $sql;
					// $result_formula = execute_query($sql);
					// $step_result = 0;
					// $tst = '';
					// while($row_formula = mysqli_fetch_assoc($result_formula)){
						// switch($row_formula['operator']){
							// case '+' :{
								// $tst = $data_array[$row_formula['var_a']]['net_value'].' + '.$data_array[$row_formula['var_b']]['net_value'];
								// $step_result = $data_array[$row_formula['var_a']]['net_value'] + $data_array[$row_formula['var_b']]['net_value'];
								// break;
							// }
							// case '-' :{

								// break;
							// }
							// case '*' : {

								// break;
							// }
							// case '/':{

								// break;
							// }
						// }
					// }
					// $step_result = $step_result*(float)$v['head_value']/100;
					// $data_array[$v['head_id']]['net_value'] = $step_result;
				// }

				// $sql = 'insert into payslip_details (payslip_id, head_id, amount) values("'.$payslip_sno.'", "'.$v['head_id'].'", "'.$data_array[$v['head_id']]['net_value'].'")';
				// execute_query($sql);
				// //echo $sql.'<br>';
				// if(mysqli_error($db)){
					// $msg .= 'Error # 09 : '.mysqli_error($db).' >> '.$sql;
				// }
			// }
			
			foreach($data_array as $k=>$v){
			//print_r($v);
			if($v['value_type']=='value'){
				$data_array[$v['head_id']]['net_value'] = round($v['head_value'] , 3);
				
				echo '<td>'.round($v['head_value'] , 3).'</td>';	
			}
			elseif($v['value_type']=='percent'){
				
				$val = (float)$data_array[$v['percent_of']]['net_value']*(float)$v['head_value']/100;
				$data_array[$v['head_id']]['net_value'] = round($val, 3);
				$data_array[$v['head_id']]['head_value'] = round($val, 3);
				
				echo '<td>'.$val.'</td>';
				if($v['head_id']=='9'){
					//echo $v['percent_of'].'<br>';
					//print_r($data_array[$v['percent_of']]);
				}
		
			
			}
			elseif($v['value_type']=='formula'){
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
					if($v['head_id']!='8'){
						$tot_income += $data_array[$v['head_id']]['net_value'];										
					}
				}
				else{
					$tot_deduction += $data_array[$v['head_id']]['net_value'];
					
				}
				$sql = 'insert into payslip_details (payslip_id, head_id, amount) values("'.$payslip_sno.'", "'.$v['head_id'].'", "'.$data_array[$v['head_id']]['net_value'].'")';
				execute_query($sql);
				//echo $sql.'<br>';
				if(mysqli_error($db)){
					$msg .= 'Error # 09 : '.mysqli_error($db).' >> '.$sql;
				}
			
			
				$tot_heads[$v['head_id']]+= $data_array[$v['head_id']]['net_value'];
				$count++;
					if($total_income_col_count==$count){
						echo '<td>'.round($tot_income, ).'</td>';
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
										$query = execute_query($sql);
										 while ($row = mysqli_fetch_array($query)){
										 	?>
									<option value="<?php echo $row['s_no']; ?>" <?php if(isset($_POST['search'])){if($_POST['company_id']==$row['s_no']){echo 'selected';}} ?>><?php echo $row['division_name']; ?></option>
										 	<?php
										}?>
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
		<?php if(isset($_POST['search'])){
			$sql_company = 'SELECT * FROM `uprnss_division` WHERE `s_no`="'.$_POST['company_id'].'"';
			$result_company = execute_query($sql_company);
			$row_company = mysqli_fetch_array($result_company);
		?>
		<div class="panel" style="margin-top:40px;">
			<div class="panel-heading">
				<b>Division Name : <?php echo $row_company['division_name']; ?></b>
			</div>
			<div class="panel-body">
				<table class="table table-hover table-responsive table-bordered table-striped">
					<thead>
						<tr>
							<th rowspan="2" style="text-align: center;">S.No.</th>
							<th rowspan="2" style="text-align: center;">Employee Name</th>
							<th rowspan="2" style="text-align: center;">Designation</th>
							<th colspan="3" style="text-align: center;">Days</th>
							<?php
							$sql = 'select * from head_type where head_type="income" and payslip_mode!="1"';
							$result_income = execute_query($sql);
							$income_count = mysqli_num_rows($result_income);
							echo '<th colspan="'.($income_count+1).'">Income</th>';
							
							
							$i=1;
							// echo $income_count+$i;
							$sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';
							$result_income = execute_query($sql);
							$income_count = mysqli_num_rows($result_income);
							echo '<th colspan="'.($income_count+$i).'">Deduction</th>';
							
							?>
							<th rowspan="2" style="text-align: center;"> Net Pay</th>
						</tr>
						<tr>
							<th style="text-align: center;">Working</th>
							<th style="text-align: center;">Leave</th>
							<th style="text-align: center;">Present</th>
							
							
							<?php
							$tot_heads = array();
							$sql = 'select * from head_type where head_type="income" and payslip_mode!="1"';
							$result_income = execute_query($sql);
							while($row_income = mysqli_fetch_assoc($result_income)){
								$tot_heads[$row_income['sno']] = 0;
								echo '<th>'.$row_income['head_name'].'</th>';
	
							}
							$sql = 'select * from head_type where head_type="income" and payslip_mode="1"';
							$result_income = execute_query($sql);
							while($row_income = mysqli_fetch_assoc($result_income)){
								$tot_heads[$row_income['sno']] = 0;
								//echo '<th>'.$row_income['head_name'].'</th>';
	
							}
							
							
							echo'<th style="text-align: center;">Gross Pay</th>';
							
							$sql = 'select * from head_type where head_type="deduction" and payslip_mode!="1"';
							$result_income = execute_query($sql);
							while($row_income = mysqli_fetch_assoc($result_income)){
								$tot_heads[$row_income['sno']] = 0;
								echo '<th>'.$row_income['head_name'].'</th>';	
							}
							
							$sql = 'select * from head_type where head_type="deduction" and payslip_mode="1"';
							$result_income = execute_query($sql);
							while($row_income = mysqli_fetch_assoc($result_income)){
								$tot_heads[$row_income['sno']] = 0;
								//echo '<th>'.$row_income['head_name'].'</th>';	
							}
							echo'<th style="text-align: center;">Total Deduction</th>';
	
							?>
						</tr>
					</thead>
					<tbody>
						<?php 
							$sql_employee_attendance = 'SELECT * FROM `attendance_details` WHERE `company_id`="'.$_POST['company_id'].'" AND `financial_year`="'.$_POST['financial_session'].'" AND `salary_generation_month`="'.$_POST['for_month'].'"';
							$result_employee_attendance = execute_query($sql_employee_attendance);
							$n = 0;
							$salary_days = 0;
							$allowances = 0;
							$deduction = 0;
							$total_basic_salary = 0;
							$total_salary_amount = 0;
							$total_payable = 0;
							$total_deduction = 0;
							$total_allownces = 0;
							$grand_total = 0;
							$grand_total_income = 0;
							$grand_total_deduction = 0;
							//echo $sql_employee_attendance;
							while ($row_employee_attendance = mysqli_fetch_array($result_employee_attendance)) {
								$sql_employee = 'SELECT * FROM `employee` WHERE `sno`="'.$row_employee_attendance['emp_id'].'"';
								$result_employee = execute_query($sql_employee);
								$row_employee = mysqli_fetch_array($result_employee);
							?>
							<tr>
								<th><?php echo ++$n; ?></th>
								<td><?php echo $row_employee['employee_name']; ?></td>
								<td><?php echo $row_employee['employee_designation']; ?></td>
								<td><?php echo $row_employee_attendance['working_days']; ?></td>
								<td><?php echo $row_employee_attendance['granted_leave']; ?></td>
								<td><?php echo $row_employee_attendance['presented_days']; ?></td>
								<?php 
									$sql_salary = 'SELECT head_id, head_name, head_type, value_type, percent_of, recurrence_period, head_value, payslip_mode FROM `salary_structure` left join head_type on head_type.sno = head_id WHERE `emp_id`="'.$row_employee['sno'].'"';
									
									//echo $sql_salary.'<br>';
									$result_salary = execute_query($sql_salary);
									$data_array = array();
									$tot_income = 0; 
									$tot_deduction = 0; 
									$total_income_col_count = 0;
									
										
										
									while($row_heads = mysqli_fetch_assoc($result_salary)){
										//print ($row_heads['payslip_mode']);
										if($row_heads['head_type'] == 'income' ){
											$total_income_col_count++;
										}
										$data_array[$row_heads['head_id']] = $row_heads;
										$data_array[$row_heads['head_id']]['net_value'] = 0;
										
									}
									//$total_income_col_count++;
									$count=0;
									foreach($data_array as $k=>$v){
										//print_r($v);
										if($v['value_type']=='value'){
											$data_array[$v['head_id']]['net_value'] = round($v['head_value'] , 3);
											
											echo '<td>'.round($v['head_value'] , 3).'</td>';	
										}
										elseif($v['value_type']=='percent'){
											
											$val = (float)$data_array[$v['percent_of']]['net_value']*(float)$v['head_value']/100;
											$data_array[$v['head_id']]['net_value'] = round($val, 3);
											$data_array[$v['head_id']]['head_value'] = round($val, 3);
											
											echo '<td>'.$val.'</td>';
											if($v['head_id']=='9'){
												//echo $v['percent_of'].'<br>';
												//print_r($data_array[$v['percent_of']]);
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
											if($v['head_id']!='8'){
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
											echo '<td>'.round($tot_income, ).'</td>';
										}
										
									}
									
									echo '<td>'.round($tot_deduction, ).'</td>';
									
									echo '<td>'.round($tot_income-$tot_deduction ).'</td>';
									$grand_total += round($tot_income-$tot_deduction, );
									
									/*
									//$total_basic_salary += $row_basic_salary['head_value'];
									$d1 = $row_employee_attendance['presented_days']+$row_employee_attendance['granted_leave'];
									$d2 = $row_employee_attendance['working_days'];
									if($d1 > $d2){
										$salary_days = $d2;
									}
									else{
										$salary_days = $d1;
									}
									//$salary_amount = ($row_basic_salary['head_value'] / $row_employee_attendance['working_days']) * $salary_days;
									//echo '<td>'.round($salary_amount).'</td>';
									$total_salary_amount += $salary_amount;
									$sql_income = 'SELECT * FROM `head_type` WHERE `head_type`="income" AND `sno`!="1"';
									$result_income = execute_query($sql_income);
									$allowances = 0;
									$deduction = 0;
									$pass_income = 0;
									while($row_income = mysqli_fetch_array($result_income)){
										if($row_income['recurrence_period'] == "1"){
											$pass_income = 1;
										}
										elseif($row_income['recurrence_period'] == "3"){
											if($_POST['for_month'] == "04" OR $_POST['for_month'] == "07" OR $_POST['for_month'] == "10" OR $_POST['for_month'] == "01"){
												$pass_income = 1;
											}
											else{
												$pass_income = 0;
											}
										}
										elseif($row_income['recurrence_period'] == "6"){
											if($_POST['for_month'] == "04" OR $_POST['for_month'] == "10"){
												$pass_income = 1;
											}
											else{
												$pass_income = 0;
											}
										}
										elseif($row_income['recurrence_period'] == "12"){
											if($_POST['for_month'] == "04"){
												$pass_income = 1;
											}
											else{
												$pass_income = 0;
											}
										}
										else{
											$pass_income = 0;
										}
										if($pass_income == 1){
											if($row_income['value_type'] == "value"){
												$sql_income_amount = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_income['sno'].'"';
												$result_income_amount = execute_query($sql_income_amount);
												$row_income_amount = mysqli_fetch_array($result_income_amount);
												$allowances += $row_income_amount['head_value'];
											}
											elseif($row_income['value_type'] == "percent"){
												$sql_income_amount_p = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_income['sno'].'"';
												$result_income_amount_p = execute_query($sql_income_amount_p);
												$row_income_amount_p = mysqli_fetch_array($result_income_amount_p);

												$sql_income_amount = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_income['percent_of'].'"';
												$result_income_amount = execute_query($sql_income_amount);
												$row_income_amount = mysqli_fetch_array($result_income_amount);

												$allowances += (($row_income_amount['head_value'] * $row_income_amount_p['head_value'])/100);
											}
										}
									}
									echo '<td>'.round($allowances).'</td>';
									$total_allownces += $allowances;
									$sql_deduction = 'SELECT * FROM `head_type` WHERE `head_type`="deduction"';
									$result_deduction = execute_query($sql_deduction);
									$pass_deduction = 0;
									while($row_deduction = mysqli_fetch_array($result_deduction)){
										if($row_deduction['recurrence_period'] == "1"){
											$pass_deduction = 1;
										}
										elseif($row_deduction['recurrence_period'] == "3"){
											if($_POST['for_month'] == "04" OR $_POST['for_month'] == "07" OR $_POST['for_month'] == "10" OR $_POST['for_month'] == "01"){
												$pass_deduction = 1;
											}
											else{
												$pass_deduction = 0;
											}
										}
										elseif($row_deduction['recurrence_period'] == "6"){
											if($_POST['for_month'] == "04" OR $_POST['for_month'] == "10"){
												$pass_deduction = 1;
											}
											else{
												$pass_deduction = 0;
											}
										}
										elseif($row_deduction['recurrence_period'] == "12"){
											if($_POST['for_month'] == "04"){
												$pass_deduction = 1;
											}
											else{
												$pass_deduction = 0;
											}
										}
										else{
											$pass_deduction = 0;
										}
										if($pass_deduction == 1){
											if($row_deduction['value_type'] == "value"){
												$sql_deduction_amount = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_deduction['sno'].'"';
												$result_deduction_amount = execute_query($sql_deduction_amount);
												$row_deduction_amount = mysqli_fetch_array($result_deduction_amount);
												$deduction += $row_deduction_amount['head_value'];
											}
											/**Only of payroll barielly**/
											/**elseif($row_deduction['sno'] == "9"){
												$sql_deduction_amount_p = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_deduction['sno'].'"';
												$result_deduction_amount_p = execute_query($sql_deduction_amount_p);
												$row_deduction_amount_p = mysqli_fetch_array($result_deduction_amount_p);

												$deduction += (($salary_amount * $row_deduction_amount_p['head_value'])/100);
												$c_14 = (($salary_amount * $row_deduction_amount_p['head_value'])/100);
												//echo $c_14.'<br/>';
											}
											elseif($row_deduction['sno'] == "11"){
												$sql_deduction_amount_p = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_deduction['sno'].'"';
												$result_deduction_amount_p = execute_query($sql_deduction_amount_p);
												$row_deduction_amount_p = mysqli_fetch_array($result_deduction_amount_p);

												$deduction += ((($salary_amount+$allowances) * $row_deduction_amount_p['head_value'])/100);
												$c_16 = ((($salary_amount+$allowances) * $row_deduction_amount_p['head_value'])/100);
												//echo $c_16.'<br/>';
											}*
											elseif($row_deduction['value_type'] == "percent"){
												$sql_deduction_amount_p = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_deduction['sno'].'"';
												$result_deduction_amount_p = execute_query($sql_deduction_amount_p);
												$row_deduction_amount_p = mysqli_fetch_array($result_deduction_amount_p);

												$sql_deduction_amount = 'SELECT * FROM `salary_structure` WHERE `emp_id`="'.$row_employee['sno'].'" AND `head_id`="'.$row_deduction['percent_of'].'"';
												$result_deduction_amount = execute_query($sql_deduction_amount);
												$row_deduction_amount = mysqli_fetch_array($result_deduction_amount);

												$deduction += (($row_deduction_amount['head_value'] * $row_deduction_amount_p['head_value'])/100);
											}
										}
									}*/
								?>
							</tr>
								<?php
							}
						?>
						<input type="hidden" name="number_of_entries" id="number_of_entries" value="<?php echo $n; ?>">
						<tr>
							<th colspan="6" style="text-align: right;">Total:</th>
							<?php
							$sql = 'select * from head_type where head_type="income" and payslip_mode="0"';
							$result_income = execute_query($sql);
							while($row_income = mysqli_fetch_assoc($result_income)){
								echo '<th>'.$tot_heads[$row_income['sno']].'</th>';
								$grand_total_income += $tot_heads[$row_income['sno']];								
							}
							echo '<th>'.round($grand_total_income).'</th>';	
							$sql = 'select * from head_type where head_type="deduction" and payslip_mode="0"';
							$result_income = execute_query($sql);
							while($row_income = mysqli_fetch_assoc($result_income)){
								echo '<th>'.round($tot_heads[$row_income['sno']]).'</th>';	
								$grand_total_deduction += $tot_heads[$row_income['sno']];
							}
							echo '<th>'.round($grand_total_deduction).'</th>';
							echo '<th>'.round($grand_total).'</th>';
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
<!--<script type="text/javascript">
	function advance_settelment(){
		var remainning_amount = 0;
		var advance_amount = 0;
		var number_of_entries = $('#number_of_entries').val();
		for(var i = 1 ; i <= number_of_entries ; i++){
			var amt = parseFloat($('#grand_amount_'+i).val());
			if(!amt){
				amt = 0;
			}
			var adv = parseFloat($('#advance_amount_'+i).val());
			if(!adv){
				adv = 0;
			}
			var rem = amt - adv;
			$('#remainning_amount_'+i).html(rem);
			advance_amount += adv;
			remainning_amount += rem;
		}
		$('#advance_amount').html(advance_amount);
		$('#remainning_amount').html(remainning_amount);
	}
</script>-->