<?php
include("scripts/settings.php");
$msg='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();// print_r($_POST);if(!isset($_POST['generate_report'])){		$_POST['department'] = '';		$_POST['division'] = '';		$_POST['date_type'] ='';		$_POST['date_from'] = '';		$_POST['date_to'] = date("Y-m-d");	}
?>
	<div id="container" class="no-print">		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">		<div class="card card-body">				<div class="card-header">                        <h4 class="card-title text-center"></h4></br>                    </div>        	<div class="row d-flex my-auto">    						<table width="100%" class="table table-striped table-hover rounded">							<tr >							<th width="16%">Date Type</th>							<th width="18%"><select name="date_type" id="date_type" class="form-control" >									                                    <option value="invoice_civil.last_update" <?php echo ($_POST['date_type']=='last_update'?' selected="selected"':''); ?>>Entry Date</option> 							    </select>							</th>							<th  width="15%">Form</th>							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>							<th  width="15%">To</th>							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>						</tr>						<tr >							<th>Select Report View Mode</th>							<th width="18%">								<select name="report_view" id="report_view" class="form-control">                    				<option value="1">Unit View</option>                    				<option value="2">Department View</option>                    			</select>							</th>							<th>प्रखण्ड </th>							<th width="18%"><select class="form-control" name="division" id="division" tabindex="<?php echo $tab++; ?>">									<option value="">--- Select ---</option>									<?php									$query = 'select * from uprnss_division where s_no in ('.implode(",", $_SESSION['divisions']).') ';								echo $query;									$run = mysqli_query($db,$query);									while($data = mysqli_fetch_array($run)){										echo '<option value="'.$data['s_no'].'" ';										if(isset($_POST['division'])){											if($_POST['division']==$data['s_no']){												echo ' selected="Selected"';											}										}										echo '>'.$data['division_name'].'</option>';									}									?>								</select></th>													<th>विभाग</th>							<th width="18%">								<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">									<option value="">--- Select ---</option>									<?php									$query = "select * from uprnss_department_name";									$run = mysqli_query($db,$query);									while($data = mysqli_fetch_array($run)){										echo '<option value="'.$data['sno'].'" ';										if(isset($_POST['department'])){											if($_POST['department']==$data['sno']){												echo ' selected="Selected"';											}										}										echo '>'.trim($data['department_name_hindi']).'</option>';									}									?>								</select>							</th>						</tr>					</table>					<div class="mx-auto">						<button type="submit" class="btn btn-primary" name="generate_report" id="generate_report">Generate Report</button>											</div>			</div>		</div>	</div>							        <div class="row">            <div class="col-md-12">			<!-- <button onclick="printTable()">Print Table</button> -->                <div class="card">                    <div class="card-header">                        <h4 class="card-title text-center">Monthly Progress Report </h4></br>                    </div>                    <div class="card-body" id="print">	
						<?php 
						if(isset($_POST['report_view'])){
							switch($_POST['report_view']){
								case '1':{
									$sql = 'select 
									invoice_civil.sno as sno, 
									division_name,
									district_name_hindi, 
									department_name_hindi,
									uprnss_project_temp.project_name_hindi as project_name_hindi, 
									invoice_civil.district_id as district_id, 
									uprnss_project_temp.division_id as division_id, 
									invoice_civil.department_id as department_id, 
									invoice_civil.unit_id as unit_id, 
									invoice_civil.project_name as project_name, 
									invoice_civil.sanction_date as sanction_date, 
									invoice_civil.sanction_cost as sanction_cost, 
									invoice_civil.revised_date as revised_date, 
									invoice_civil.revised_cost as revised_cost, 
									invoice_civil.land_receive_date as land_receive_date, 
									invoice_civil.work_start_date as work_start_date, 
									invoice_civil.work_completion_date as work_completion_date, 
									invoice_civil.technical_sanction_date as technical_sanction_date, 
									invoice_civil.technical_sanction_no as technical_sanction_no, 
									invoice_civil.tender_status as tender_status, 
									invoice_civil.admin_go_no as admin_go_no, 
									invoice_civil.admin_go_date as admin_go_date, 
									invoice_civil.financial_go_no as financial_go_no, 
									invoice_civil.financial_go_date as financial_go_date, 
									invoice_civil.financial_go_amount as financial_go_amount, 
									invoice_civil.last_fy_tot_exp as last_fy_tot_exp, 
									invoice_civil.current_fy_last_month_exp as current_fy_last_month_exp, 
									invoice_civil.current_month_exp as current_month_exp, 
									invoice_civil.current_fy_tot_exp as current_fy_tot_exp, 
									invoice_civil.tot_exp_project as tot_exp_project, 
									invoice_civil.balance_amt_cost as balance_amt_cost, 
									invoice_civil.balance_amt_project as balance_amt_project, 
									invoice_civil.last_update as last_update, 
									uprnss_project_temp.project_status as project_status, 
									invoice_civil.status as status
									from invoice_civil 
									left join uprnss_project_temp on uprnss_project_temp.sno = invoice_civil.project_name
									left join uprnss_district on uprnss_district.sno = invoice_civil.district_id
									left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
									left join uprnss_department_name on uprnss_department_name.sno = invoice_civil.department_id
									where (uprnss_project_temp.status="0" or uprnss_project_temp.status is null)';
																	if(isset($_POST['generate_report'])){
										if($_POST['division']!=''){
											$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division'].'"';
										}
										if($_POST['department']!=''){
											$sql .= ' and department_id="'.$_POST['department'].'"';
										}										if($_POST['date_type']!=''){										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';										}
									}
									$sql .= '
									order by abs(uprnss_project_temp.division_id)';
									// echo $sql;
									$result_div = execute_query($sql);
									//echo '<br><br>'.mysqli_error($db);
									$div = '';
									$final_txt = '';
									$a=1;
									while($row_project = mysqli_fetch_assoc($result_div)){
										if($div == ''){
											echo '<h4>'.$row_project['division_name'].'</h4>';
											echo '<table class="table table-striped table-bordered table-hover">
											<thead>
												<tr>
													<th rowspan="2">क्रम.</th>
													<th rowspan="2">जनपद का नाम</th>
													<th rowspan="2">विभाग का नाम</th>
													<th rowspan="2">परियोजना का नाम</th>
													<th rowspan="2">स्वीकृति की तिथि/शासनादेश संख्या </th>
													<th rowspan="2">भूमि प्राप्त एवं कार्य प्रारम्भ की तिथि</th>
													<th rowspan="2">योजना की मूल लागत</th>
													<th rowspan="2">तकनीकी स्वीकृति का क्रमांक एवं दिनांक </th>
													<th rowspan="2">पुनरीक्षित आगणन की लागत </th>
													<th rowspan="2">माह प्राप्त धनराशि व दिनांक </th>
													<th rowspan="2">कुल प्राप्त धनराशि</th>
													<th rowspan="2">अवशेष धनराशि (परियोजना लागत के सापेक्ष)</th>
													<th colspan="3">व्यय धनराशि </th>
													<th rowspan="2">परियोजना पर शेष धनराशि (अवमुक्त के सापेक्ष)</th>
													<th colspan="2">भौतिक प्रगति </th>
													<th rowspan="2">कार्य पूर्णतः की तिथि </th>
													<th rowspan="2">भौतिक प्रगति प्रतिशत में</th>
													<th rowspan="2">उपयोगिता प्रमाण पत्र की अद्यतन स्थिति दिनांक सहित </th>
													<th rowspan="2">पुनरीक्षित की स्थिति धनराशि दिनांक सहित </th>
													<th rowspan="2">अभ्युक्ति </th>
												</tr>
												<tr>
													<th>परियोजना पर माह मे व्यय </th>
													<th>परियोजना पर कुल व्यय </th>
													<th>वित्तीय वर्ष मे व्यय </th>
													<th>भवन की इकाई (विस्तृत)</th>
													<th>कार्य का स्तर</th>
												</tr><tr>';
											for($i=1; $i<=23; $i++){
												echo '<td>'.$i.'</td>';
											}
											echo '</tr>
											</thead>
											<tbody>';
											$div = $row_project['division_id'];
										}
										if($div != $row_project['division_id']){
											$div = $row_project['division_id'];
											echo $final_txt.'</tbody>
											</table>';
											echo '<h4>'.$row_project['division_name'].'</h4>';
											echo '<table class="table table-striped table-bordered table-hover">
											<thead>
												<tr>
													<th rowspan="2">क्रम.</th>
													<th rowspan="2">जनपद का नाम</th>
													<th rowspan="2">विभाग का नाम</th>
													<th rowspan="2">परियोजना का नाम</th>
													<th rowspan="2">स्वीकृति की तिथि/शासनादेश संख्या </th>
													<th rowspan="2">भूमि प्राप्त एवं कार्य प्रारम्भ की तिथि</th>
													<th rowspan="2">योजना की मूल लागत</th>
													<th rowspan="2">तकनीकी स्वीकृति का क्रमांक एवं दिनांक </th>
													<th rowspan="2">पुनरीक्षित आगणन की लागत </th>
													<th rowspan="2">माह प्राप्त धनराशि व दिनांक </th>
													<th rowspan="2">कुल प्राप्त धनराशि</th>
													<th rowspan="2">अवशेष धनराशि (परियोजना लागत के सापेक्ष)</th>
													<th colspan="3">व्यय धनराशि </th>
													<th rowspan="2">परियोजना पर शेष धनराशि (अवमुक्त के सापेक्ष)</th>
													<th colspan="2">भौतिक प्रगति </th>
													<th rowspan="2">कार्य पूर्णतः की तिथि </th>
													<th rowspan="2">भौतिक प्रगति प्रतिशत में</th>
													<th rowspan="2">उपयोगिता प्रमाण पत्र की अद्यतन स्थिति दिनांक सहित </th>
													<th rowspan="2">पुनरीक्षित की स्थिति धनराशि दिनांक सहित </th>
													<th rowspan="2">अभ्युक्ति </th>
												</tr>
												<tr>
													<th>परियोजना पर माह मे व्यय </th>
													<th>परियोजना पर कुल व्यय </th>
													<th>वित्तीय वर्ष मे व्यय </th>
													<th>भवन की इकाई (विस्तृत)</th>
													<th>कार्य का स्तर</th>
												</tr><tr>';
											for($i=1; $i<=23; $i++){
												echo '<td>'.$i.'</td>';
											}
											echo '</tr>
											</thead>
											<tbody>';
											$final_txt = '';
										}
										$tot_rcpt = 0;
										$tot_exp = 0;
										$row_project['sanction_cost'] = (float)($row_project['sanction_cost']!=''?$row_project['sanction_cost'].' (लाख)':'');
										$sql = 'select * from transaction_civil_receipts where invoice_id="'.$row_project['sno'].'"';
										//echo $sql.'<br>';
										$result_rcpt = execute_query($sql);
										$rcpt_txt = '<td><table>';
										if(mysqli_num_rows($result_rcpt)!=0){
											$count_rcpt = mysqli_num_rows($result_rcpt);
											while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
												$rcpt_txt .= '<tr><td>'.$row_rcpt['transaction_date'].'</td><td>'.$row_rcpt['transaction_amount'].'</td></tr>';
												$tot_rcpt += (float)$row_rcpt['transaction_amount'];
											}
										}
										else{
											$count_rcpt=0;
										}
										$rcpt_txt .= '</table></td>
										<td>'.$tot_rcpt.'</td>
										<td>'.$row_project['balance_amt_cost'].'</td>';
										$sql = 'select * from transaction_civil_activities where invoice_id="'.$row_project['sno'].'"';
										$result_act = execute_query($sql);
										$act_txt = '<td colspan="2"><table>';
										if(mysqli_num_rows($result_act)!=0){
											$count_act = mysqli_num_rows($result_act);
											while($row_act = mysqli_fetch_assoc($result_act)){
												$act_txt .= '<tr><td>'.$row_act['activity'].'</td><td>'.$row_act['current_month_physical_progress'].'</td></tr>';
											}
										}
										else{
											$count_act = 0;
										}
										$act_txt .= '</table></td>';
										$final_txt .= '<tr>
										<td>'.$a++.'</td>
										<td>'.$row_project['district_name_hindi'].'</td>
										<td>'.$row_project['department_name_hindi'].'</td>
										<td>'.$row_project['project_name_hindi'].'</td>
										<td>'.$row_project['admin_go_date'].' '.$row_project['admin_go_no'].'</td>
										<td>'.$row_project['land_receive_date'].'</td>
										<td>'.$row_project['sanction_cost'].'</td>
										<td>'.$row_project['technical_sanction_date'].' '.$row_project['technical_sanction_no'].'</td>
										<td>'.$row_project['revised_date'].' '.$row_project['revised_cost'].'</td>';
										$final_txt .= $rcpt_txt;
										$final_txt .= '<td>'.$row_project['current_month_exp'].'</td>';
										$final_txt .= '<td>'.$row_project['tot_exp_project'].'</td>';
										$final_txt .= '<td>'.$row_project['current_fy_tot_exp'].'</td>';
										$final_txt .= '<td>'.$row_project['balance_amt_project'].'</td>';
										$final_txt .= $act_txt;
										$final_txt .= '<td>'.$row_project['work_completion_date'].'</td>';
										$final_txt .= '<td>&nbsp;</td>';
										$final_txt .= '<td>&nbsp;</td>';
										$final_txt .= '<td>&nbsp;</td>';
										$final_txt .= '<td>&nbsp;</td>';
										$final_txt .= '</tr>';
									}
								echo $final_txt.'</tbody>
											</table>';
								}
								break;								case '2':{									$sql = 'select 									invoice_civil.sno as sno, 									division_name,									district_name_hindi, 									department_name_hindi,									uprnss_project_temp.project_name_hindi as project_name_hindi, 									invoice_civil.district_id as district_id, 									uprnss_project_temp.division_id as division_id, 									invoice_civil.department_id as department_id, 									invoice_civil.unit_id as unit_id, 									invoice_civil.project_name as project_name, 									invoice_civil.sanction_date as sanction_date, 									invoice_civil.sanction_cost as sanction_cost, 									invoice_civil.revised_date as revised_date, 									invoice_civil.revised_cost as revised_cost, 									invoice_civil.land_receive_date as land_receive_date, 									invoice_civil.work_start_date as work_start_date, 									invoice_civil.work_completion_date as work_completion_date, 									invoice_civil.technical_sanction_date as technical_sanction_date, 									invoice_civil.technical_sanction_no as technical_sanction_no, 									invoice_civil.tender_status as tender_status, 									invoice_civil.admin_go_no as admin_go_no, 									invoice_civil.admin_go_date as admin_go_date, 									invoice_civil.financial_go_no as financial_go_no, 									invoice_civil.financial_go_date as financial_go_date, 									invoice_civil.financial_go_amount as financial_go_amount, 									invoice_civil.last_fy_tot_exp as last_fy_tot_exp, 									invoice_civil.current_fy_last_month_exp as current_fy_last_month_exp, 									invoice_civil.current_month_exp as current_month_exp, 									invoice_civil.current_fy_tot_exp as current_fy_tot_exp, 									invoice_civil.tot_exp_project as tot_exp_project, 									invoice_civil.balance_amt_cost as balance_amt_cost, 									invoice_civil.balance_amt_project as balance_amt_project, 									invoice_civil.last_update as last_update, 									uprnss_project_temp.project_status as project_status, 									invoice_civil.status as status									from invoice_civil 									left join uprnss_project_temp on uprnss_project_temp.sno = invoice_civil.project_name									left join uprnss_district on uprnss_district.sno = invoice_civil.district_id									left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id									left join uprnss_department_name on uprnss_department_name.sno = invoice_civil.department_id									where 1=1';																		if(isset($_POST['generate_report'])){										if($_POST['division']!=''){											$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division'].'"';										}										if($_POST['department']!=''){											$sql .= ' and department_id="'.$_POST['department'].'"';										}										if($_POST['date_type']!=''){										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';										}									}																										$sql .= '									and (uprnss_project_temp.status="0" or uprnss_project_temp.status is null or uprnss_project_temp.status="1")									order by (department_id)';									//echo $sql;									$result_depart = execute_query($sql);									// echo '<br><br>'.mysqli_error($db);									$dep = '';									$final_txt = '';									$a=1;									while($row_project = mysqli_fetch_assoc($result_depart)){										if($dep == ''){											echo '<h4>'.$row_project['department_name_hindi'].'</h4>';											echo '<table class="table table-striped table-bordered table-hover">											<thead>												<tr>													<th rowspan="2">क्रम.</th>													<th rowspan="2">प्रखण्ड का नाम</th>													<th rowspan="2">जनपद का नाम</th>																										<th rowspan="2">परियोजना का नाम</th>													<th rowspan="2">स्वीकृति की तिथि/शासनादेश संख्या </th>													<th rowspan="2">भूमि प्राप्त एवं कार्य प्रारम्भ की तिथि</th>													<th rowspan="2">योजना की मूल लागत</th>													<th rowspan="2">तकनीकी स्वीकृति का क्रमांक एवं दिनांक </th>													<th rowspan="2">पुनरीक्षित आगणन की लागत </th>													<th rowspan="2">माह प्राप्त धनराशि व दिनांक </th>													<th rowspan="2">कुल प्राप्त धनराशि</th>													<th rowspan="2">अवशेष धनराशि (परियोजना लागत के सापेक्ष)</th>													<th colspan="3">व्यय धनराशि </th>													<th rowspan="2">परियोजना पर शेष धनराशि (अवमुक्त के सापेक्ष)</th>													<th colspan="2">भौतिक प्रगति </th>													<th rowspan="2">कार्य पूर्णतः की तिथि </th>													<th rowspan="2">भौतिक प्रगति प्रतिशत में</th>													<th rowspan="2">उपयोगिता प्रमाण पत्र की अद्यतन स्थिति दिनांक सहित </th>													<th rowspan="2">पुनरीक्षित की स्थिति धनराशि दिनांक सहित </th>													<th rowspan="2">अभ्युक्ति </th>												</tr>												<tr>													<th>परियोजना पर माह मे व्यय </th>													<th>परियोजना पर कुल व्यय </th>													<th>वित्तीय वर्ष मे व्यय </th>													<th>भवन की इकाई (विस्तृत)</th>													<th>कार्य का स्तर</th>												</tr><tr>';											for($i=1; $i<=23; $i++){												echo '<td>'.$i.'</td>';											}											echo '</tr>											</thead>											<tbody>';											$dep = $row_project['department_id'];										}										if($dep != $row_project['department_id']){											$dep = $row_project['department_id'];											echo $final_txt.'</tbody>											</table>';											echo '<h4>'.$row_project['department_name_hindi'].'</h4>';											echo '<table class="table table-striped table-bordered table-hover">											<thead>												<tr>													<th rowspan="2">क्रम.</th>													<th rowspan="2">प्रखण्ड का नाम</th>													<th rowspan="2">जनपद का नाम</th>																										<th rowspan="2">परियोजना का नाम</th>													<th rowspan="2">स्वीकृति की तिथि/शासनादेश संख्या </th>													<th rowspan="2">भूमि प्राप्त एवं कार्य प्रारम्भ की तिथि</th>													<th rowspan="2">योजना की मूल लागत</th>													<th rowspan="2">तकनीकी स्वीकृति का क्रमांक एवं दिनांक </th>													<th rowspan="2">पुनरीक्षित आगणन की लागत </th>													<th rowspan="2">माह प्राप्त धनराशि व दिनांक </th>													<th rowspan="2">कुल प्राप्त धनराशि</th>													<th rowspan="2">अवशेष धनराशि (परियोजना लागत के सापेक्ष)</th>													<th colspan="3">व्यय धनराशि </th>													<th rowspan="2">परियोजना पर शेष धनराशि (अवमुक्त के सापेक्ष)</th>													<th colspan="2">भौतिक प्रगति </th>													<th rowspan="2">कार्य पूर्णतः की तिथि </th>													<th rowspan="2">भौतिक प्रगति प्रतिशत में</th>													<th rowspan="2">उपयोगिता प्रमाण पत्र की अद्यतन स्थिति दिनांक सहित </th>													<th rowspan="2">पुनरीक्षित की स्थिति धनराशि दिनांक सहित </th>													<th rowspan="2">अभ्युक्ति </th>												</tr>												<tr>													<th>परियोजना पर माह मे व्यय </th>													<th>परियोजना पर कुल व्यय </th>													<th>वित्तीय वर्ष मे व्यय </th>													<th>भवन की इकाई (विस्तृत)</th>													<th>कार्य का स्तर</th>												</tr><tr>';											for($i=1; $i<=23; $i++){												echo '<td>'.$i.'</td>';											}											echo '</tr>											</thead>											<tbody>';											$final_txt = '';										}										$tot_rcpt = 0;										$tot_exp = 0;										$row_project['sanction_cost'] = (float)($row_project['sanction_cost']!=''?$row_project['sanction_cost'].' (लाख)':'');										$sql = 'select * from transaction_civil_receipts where invoice_id="'.$row_project['sno'].'"';										//echo $sql.'<br>';										$result_rcpt = execute_query($sql);										$rcpt_txt = '<td><table>';										if(mysqli_num_rows($result_rcpt)!=0){											$count_rcpt = mysqli_num_rows($result_rcpt);											while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){												$rcpt_txt .= '<tr><td>'.$row_rcpt['transaction_date'].'</td><td>'.$row_rcpt['transaction_amount'].'</td></tr>';												$tot_rcpt += (float)$row_rcpt['transaction_amount'];											}										}										else{											$count_rcpt=0;										}										$rcpt_txt .= '</table></td>										<td>'.$tot_rcpt.'</td>										<td>'.$row_project['balance_amt_cost'].'</td>';										$sql = 'select * from transaction_civil_activities where invoice_id="'.$row_project['sno'].'"';										$result_act = execute_query($sql);										$act_txt = '<td colspan="2"><table>';										if(mysqli_num_rows($result_act)!=0){											$count_act = mysqli_num_rows($result_act);											while($row_act = mysqli_fetch_assoc($result_act)){												$act_txt .= '<tr><td>'.$row_act['activity'].'</td><td>'.$row_act['current_month_physical_progress'].'</td></tr>';											}										}										else{											$count_act = 0;										}										$act_txt .= '</table></td>';										$final_txt .= '<tr>										<td>'.$a++.'</td>										<td>'.$row_project['division_name'].'</td>										<td>'.$row_project['district_name_hindi'].'</td>																				<td>'.$row_project['project_name_hindi'].'</td>										<td>'.$row_project['admin_go_date'].' '.$row_project['admin_go_no'].'</td>										<td>'.$row_project['land_receive_date'].'</td>										<td>'.$row_project['sanction_cost'].'</td>										<td>'.$row_project['technical_sanction_date'].' '.$row_project['technical_sanction_no'].'</td>										<td>'.$row_project['revised_date'].' '.$row_project['revised_cost'].'</td>';										$final_txt .= $rcpt_txt;										$final_txt .= '<td>'.$row_project['current_month_exp'].'</td>';										$final_txt .= '<td>'.$row_project['tot_exp_project'].'</td>';										$final_txt .= '<td>'.$row_project['current_fy_tot_exp'].'</td>';										$final_txt .= '<td>'.$row_project['balance_amt_project'].'</td>';										$final_txt .= $act_txt;										$final_txt .= '<td>'.$row_project['work_completion_date'].'</td>';										$final_txt .= '<td>&nbsp;</td>';										$final_txt .= '<td>&nbsp;</td>';										$final_txt .= '<td>&nbsp;</td>';										$final_txt .= '<td>&nbsp;</td>';										$final_txt .= '</tr>';									}								echo $final_txt.'</tbody>											</table>';									}									break;
							}
						}
						?>
					</div>
                </div>
            </div>
        </div>
    </form>
<?php
page_footer_start();
?>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>


$('select[multiple]').multiselect();
</script>

<?php		
page_footer_end();
?>