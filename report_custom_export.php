<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

if(isset($_SESSION['post_custom_report'])){
	$_POST = $_SESSION['post_custom_report'];
}
else{
	die('Invalid Request');
}

		$select_array = array(
					"chk_project_type" => array("परियोजना का प्रकार", "1"), 
					"chk_division_name" => array("यूनिट का नाम ", "1"),
					"chk_district_name_hindi" => array("जनपद","1"), 
					"chk_department_name_hindi" => array("विभाग", "1"), 
					"chk_project_name_hindi" => array("परियोजना का नाम (हिन्दी )", "1"),
					"chk_sacntion_cost" => array("मूल परियोजना लागत ( लाख में ) ","1"), 
					"chk_revised_date" => array("पुनरीक्षित लागत की स्वीकृति का दिनांक", "1"),
					"chk_revised_cost" => array("पुनरीक्षित लागत ( लाख में )", "1"),
					"chk_land_receive_date" => array("भूमि प्राप्त की तिथि", "1"),
					"chk_work_start_date" => array("कार्य प्रारंभ तिथि", "1"),
					"chk_work_completion_date" => array("कार्य पूर्ण तिथि", "1"),
					"chk_admin_go_no" => array("प्रशासनिक शासनादेश संख्या तिथि", "1"),
					"chk_financial_go_no" => array("वित्तीय शासनादेश संख्या तिथ धनराशि ( लाख में )", "1"),
					"chk_technical_sacntion_date" => array("तकनीकी स्वीकृति  दिनांक/तकनीकी स्वीकृति क्रमांक", "1"),
					"chk_tender_status" => array("टेंडर की स्थिति","1"), 
					"chk_payable_gst" => array("योजना पर देय जी . एस. टी .","1"), 
					"chk_architect_name" => array("आर्किटेक्ट", "1"),
					"chk_structural_architect" => array("स्ट्राक्चरल आर्किटेक्ट","1"), 
					"chk_soli_testing" => array("मृदा परीक्षण / Survey","1"), 
					"chk_estimation_status" => array("आगणन गठन की स्थिति", "1"),
					"chk_je_name" => array("परियोजना  पर तैनात  अवर  अभियंता का नाम", "1"),
					"chk_ae_name" => array("परियोजना  पर तैनात  सहायक  अभियंता का नाम", "1"),
					"chk_excn_name" => array("परियोजना  के  अधिशासी  अभियंता का नाम कब से/कब तक", "1"),
					"chk_se_name" => array("परियोजना  के  अधीक्षण  अभियंता का नाम कब से/कब तक", "1"),
					"chk_revised_amount" => array("पुनरीक्षित आगण की लागत ( लाख में )", "1"),
					"chk_revision_reason" => array("पुनरीक्षित आगणन का  कारण", "1"),
					"chk_revision_date" => array("पुनरीक्षित आगण की तिथि", "1"),
					"chk_revision_sent_status" => array("पुनरीक्षित प्रेषण की स्थिति /धनराशि ( लाख में )", "1"),
					"chk_revision_sent_date" => array("पुनरीक्षित प्रेषण की तिथि", "1"),
					
					"chk_total_received_amount_date" => array("कुल प्राप्त धनराशि व दिनांक", "2"),
					"chk_total_received_amount" => array("कुल प्राप्त धनराशि", "1"),
					
					"chk_expense_current_fy" => array("गत वित्तीय वर्ष में कुल व्यय", "1"),
					"chk_expense_current_fy_month" => array("वर्तमान वित्तीय वर्ष में गत माह तक व्यय ( लाख में )", "1"),
					"chk_expense_current_month" => array("वर्तमान माह में व्यय ( लाख में )", "1"),
					"chk_expense_current_fy_total" => array("वर्तमान वित्तीय वर्ष में कुल व्यय ( लाख में )", "1"),
					"chk_expense_project" => array("परियोजना में अब तक का कुल व्यय ( लाख में )", "1"),
					"chk_balance_amount" => array("शेष धनराशि परियोजना लागत के सापेक्ष ( लाख में )", "1"),
					"chk_balance_amount_refer" =>array( "परियोजना पर शेष धनराशि अवमुक्त के सापेक्ष ( लाख में )","1"),

					"chk_percent_avmukt" => array("वित्तीय प्रगति %(अवमुक्त के सापेक्ष)","1"),
					"chk_percent_physical_progress" => array("भौतिक प्रगति","2"),
					"chk_percent_physical_progress_per" => array("भौतिक प्रगति प्रतिशत में","1"),

					"chk_project_status_1" => array("परियोजना की स्थिति ", "1"),
					"chk_remarks" => array("अभियुक्ति",  "1"),
					"chk_last_update" => array("Last Update", "1") );



			$html = '<table border="1">
						<thead>
						<tr>
							<th>क्र० स०</th>';
						foreach($select_array as $k=>$v){
							if(isset($_POST[$k])){
								$html .= '<th colspan="'.$v[1].'">'.$v[0].'</th>';
							}
						}
						
						$html .= '</tr>
						<tr>
							<th>1</th>';
							$i=2;
							foreach($select_array as $k=>$v){
								if(isset($_POST[$k])){
									$html .= '<th>'.$i++.'</th>';
								}
							}
						$html .= '</tr>
						
						</thead>
						 
						<tbody>';
	
						$i = 1;
						//if($_SESSION['usertype']=='sadmin'){
						$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, land_receive_date, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,
							financial_go_amount,
							technical_sanction_date,
							technical_sanction_no,
							tender_status,
							project_type,
							
							payable_gst_on_project,
							architect_id,
							structural_architect_id,
							soil_testing_status,
							estimation_formation_status,
							project_status_1,
							project_status_2,
							remark,
							junior_engineer_name,
							assistant_engineer_name,
							executive_engineer_name,
							executive_engineer_from,
							executive_engineer_to,
							superintendent_engineer_name,
							superintendent_engineer_from,
							superintendent_engineer_to,
							revised_estimate_amount,
							revised_estimate_remark,
							revised_estimate_date,
							revised_dispatch_status,
							revised_remittance_amount,
							revised_remittance_date, 
							last_fy_tot_exp,
							current_fy_last_month_exp,
							current_month_exp,
							current_fy_tot_exp,
							tot_exp_project,
							balance_amt_cost,
							balance_amt_project,
							last_update,
							reporting_status,
							status, master_freeze 
							from uprnss_project_temp 
							left join uprnss_district on uprnss_district.sno = district_id
							left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
							left join uprnss_department_name on uprnss_department_name.sno = department_id
							where 1=1 and (status!="5" or status="0" or status is null or status="1") and reporting_status="0"';
						//print_r($_POST);
						if (isset($_POST['search'])) {
							if ($_POST['department'] != '') {
								$sql .= ' and department_id="' . $_POST['department'] . '"';
							}
							if ($_POST['district'] != '') {
								$sql .= ' and district_id="' . $_POST['district'] . '"';
							}
							if ($_POST['division_name'] != '') {
								$sql .= ' and uprnss_project_temp.division_id="' . $_POST['division_name'] . '"';
							}
							if ($_POST['project_status_1'] != '') {
								$sql .= ' and project_status_1="' . $_POST['project_status_1'] . '"';
							}
							if ($_POST['project_type'] != '') {
								$sql .= ' and project_type="' . $_POST['project_type'] . '"';
							}
							if ($_POST['sanction_cost1'] == '') {
								if ($_POST['sanction_cost'] != '') {
									$sanction_cost = $_POST['sanction_cost'];
									$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
								}
							}
							if ($_POST['sanction_cost1'] != '') {
								$sanction_cost = abs($_POST['sanction_cost']);
								$sanction_cost1 = abs($_POST['sanction_cost1']);
								$sql .= ' and abs(sanction_cost)' . $_POST['type'] . '"' . $sanction_cost . '"';
								$sql .= ' and abs(sanction_cost)' . $_POST['type2'] . '"' . $sanction_cost1 . '"';
							}
							if ($_POST['tot_exp_project'] != '') {
								$sql .= ' and abs(tot_exp_project)' . $_POST['type1'] . '"' . $_POST['tot_exp_project'] . '"';
							}
							if ($_POST['tender_status'] != '') {
								$sql .= ' and tender_status="' . $_POST['tender_status'] . '"';
							}
							if ($_POST['date_type'] != '') {
								$sql .= ' and ' . $_POST['date_type'] . '>="' . $_POST['date_from'] . '" and ' . $_POST['date_type'] . '<"' . date('Y-m-d', strtotime($_POST['date_to'] . '+1 day')) . '"';
							}
							// $sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
							//if($_POST['work_start_date_from']!=''){
							// $sql .= ' and work_start_date>="'.$_POST['work_start_date_from'].'" and `work_start_date`<"'.$_POST['work_start_date_to'].'"';
							//}
						} else {
							$sql .= ' and "a"="b"';
						}
						$sql .= ' ORDER BY uprnss_project_temp.division_id, uprnss_project_temp.department_id';
						// echo $sql;
							$result = execute_query($sql);
							while($row = mysqli_fetch_assoc($result)){
								
								
								$sql = 'select * from master_projoect_status_2 where sno="'.$row['project_status_2'].'"';
								// echo $sql;
								// $status2 = mysqli_fetch_assoc(execute_query($sql));
								$status2 = execute_query($sql);
								if(mysqli_num_rows($status2)!=0){
									$status2 = mysqli_fetch_assoc($status2);
								}
								else{
									unset($status2);
									$status2['status_2'] = '';
								}
								
								$sql = 'select * from uprnss_architect where sno="'.$row['architect_id'].'"';
								// echo $sql;
								// $architect = mysqli_fetch_assoc(execute_query($sql));
								$architect = execute_query($sql);
								if(mysqli_num_rows($architect)!=0){
									$architect = mysqli_fetch_assoc($architect);
								}
								else{
									unset($architect);
									$architect['full_name_english'] = '';
								}
								
								$sql = 'select * from uprnss_architect where sno="'.$row['structural_architect_id'].'"';
								// echo $sql;
								// $starch = mysqli_fetch_assoc(execute_query($sql));
								$starch = execute_query($sql);
								if(mysqli_num_rows($starch)!=0){
									$starch = mysqli_fetch_assoc($starch);
								}
								else{
									unset($starch);
									$starch['full_name_english'] = '';
								}
								
								$sql = 'select * from dp_personal_info where sno="'.$row['junior_engineer_name'].'"';
								// echo $sql;
								// $je = mysqli_fetch_assoc(execute_query($sql));
								$je = execute_query($sql);
								if(mysqli_num_rows($je)!=0){
									$je = mysqli_fetch_assoc($je);
								}
								else{
									unset($je);
									$je['full_name'] = '';
								}
								
								$sql = 'select * from dp_personal_info where sno="'.$row['assistant_engineer_name'].'"';
								// echo $sql;
								// $ae = mysqli_fetch_assoc(execute_query($sql));
								$ae = execute_query($sql);
								if(mysqli_num_rows($ae)!=0){
									$ae = mysqli_fetch_assoc($ae);
								}
								else{
									unset($ae);
									$ae['full_name'] = '';
								}
								
								$sql = 'select * from dp_personal_info where sno="'.$row['executive_engineer_name'].'"';
								// echo $sql;
								$exe = execute_query($sql);
								if(mysqli_num_rows($exe)!=0){
									$exe = mysqli_fetch_assoc($exe);
								}
								else{
									unset($exe);
									$exe['full_name'] = '';
								}
								
								$sql = 'select * from dp_personal_info where sno="'.$row['superintendent_engineer_name'].'"';
								// echo $sql;
								// $se = mysqli_fetch_assoc(execute_query($sql));
								$se = execute_query($sql);
								if(mysqli_num_rows($se)!=0){
									$se = mysqli_fetch_assoc($se);
								}
								else{
									unset($se);
									$se['full_name'] = '';
								}
								
								$sql = 'select * from master_tender_status where sno="'.$row['tender_status'].'"';
								// echo $sql;
								// $tender = mysqli_fetch_assoc(execute_query($sql));
								$tender = execute_query($sql);
								if(mysqli_num_rows($tender)!=0){
									$tender = mysqli_fetch_assoc($tender);
								}
								else{
									unset($tender);
									$tender['status'] = '';
								}
								
								$sql = 'select * from invoice_civil where project_name="'.$row['sno'].'"order by sno desc limit 1';
									// echo $sql;
									// $res = execute_query($sql);
									$res = execute_query($sql);
									if(mysqli_num_rows($res)!=0){
										$row_invoice = mysqli_fetch_assoc($res);
										$sql = 'select * from transaction_civil_receipts where invoice_id="'.$row_invoice['sno'].'"';
										//echo $sql.'<br>';
										$result_rcpt = execute_query($sql);
										$rcpt_txt = '<td colspan="2"><table class="" style="border:none!important;" width="100%">';
										$tot_rcpt = 0;
										$tot_exp = 0;
												
										if(mysqli_num_rows($result_rcpt)!=0){
											$count_rcpt = mysqli_num_rows($result_rcpt);
											while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
												$rcpt_txt .= '<tr><td>'.($row_rcpt['transaction_date']!=''?date("d-m-Y",strtotime($row_rcpt['transaction_date'])):'').'</td><td >'.$row_rcpt['transaction_amount'].'</td></tr>';
												$tot_rcpt += (float)$row_rcpt['transaction_amount'];

											}

										}

										else{

											$count_rcpt=0;

										}

										$rcpt_txt .= '</table></td>';
										
										$sql = 'select * from transaction_civil_activities where invoice_id="'.$row_invoice['sno'].'"';
										$result_act = execute_query($sql);
										$act_txt = '<td colspan="2"><table>';
										if(mysqli_num_rows($result_act)!=0){
											$count_act = mysqli_num_rows($result_act);
											while($row_act = mysqli_fetch_assoc($result_act)){
												$act_txt .= '<tr><td>'.$row_act['activity'].'</td><td>'.$row_act['remarks'].'</td></tr>';
											}
										}
										else{
											$count_act = 0;
										}
										$act_txt .= '</table></td>';
										
									}
									else{
										unset($res);
										$rcpt_txt ='<td colspan="2"></td>';
										$act_txt ='<td colspan="2"></td>';
										
										$row_invoice['balance_amt_project']="";
										$row_invoice['tot_exp_project']="";
										$row_invoice['current_fy_tot_exp']="";
										$row_invoice['current_month_exp']="";
										$row_invoice['current_month_exp']="";
										
										
										$row_invoice['sanction_cost']=0;
										$row_invoice['total_received_amount']=0;
										
										$row_invoice['balance_amt_cost']="";
										$row_invoice['total_physical_progress']="";
										$row_invoice['remark']="";
										$row_invoice['last_update']="";
										
										
										
									}
									$sql = 'select * from master_tender_status where sno="'.$row_invoice['tender_status'].'"';
									// echo $sql;
									// $tender = mysqli_fetch_assoc(execute_query($sql));
									$tender = execute_query($sql);
									if(mysqli_num_rows($tender)!=0){
										$tender = mysqli_fetch_assoc($tender);
									}
									else{
										unset($tender);
										$tender['status'] = '';
									}
									$sql = 'select * from master_projoect_status_1 where sno="'.$row_invoice['project_status_1'].'"';
									// echo $sql;
									// $status1 = mysqli_fetch_assoc(execute_query($sql));
									$status1 = execute_query($sql);
									if(mysqli_num_rows($status1)!=0){
										$status1 = mysqli_fetch_assoc($status1);
									}
									else{
										unset($status1);
										$status1['status_1'] = '';
									}
								
								
								$html .= '<tr>
								<td>'.$i++.'</td>';
								if(isset($_POST['chk_project_type'])){
    								$html .= '
    								<td>';
    								if($row['project_type']!=''){
    									if($row['project_type']=='1'){
    										$html .= '<span class="">शासन स्तर</span>';
    									}
    									elseif($row['project_type']=='2'){
    										$html .= '<span class="">जिला स्तर</span>';
    									}
    								}
								$html .= '</td>';
								}
								if(isset($_POST['chk_division_name'])){
								    $html .= '<td>'.$row['division_name'].'</td>';
								}
								if(isset($_POST['chk_district_name_hindi'])){
								    $html .= '<td>'.$row['district_name_hindi'].'</td>';
								}
								if(isset($_POST['chk_department_name_hindi'])){
								    $html .= '<td>'.$row['department_name_hindi'].'</td>';
								}
								if(isset($_POST['chk_project_name_hindi'])){
								    $html .= '<td>'.$row['project_name_hindi'].'</td>';
								}
								if(isset($_POST['chk_sacntion_cost'])){
								    
									$html .= '<td>'.$row_invoice['sanction_cost'].'</td>';
								}
								if(isset($_POST['chk_revised_date'])){
								    $html .= '<td>'.$row_invoice['revised_date'].'</td>';
								}
								if(isset($_POST['chk_revised_cost'])){
								    $html .= '<td>'.$row_invoice['revised_cost'].'</td>';
								}
								if(isset($_POST['chk_land_receive_date'])){
								    $html .= '<td>'.$row['land_receive_date'].'</td>';
								}
								if(isset($_POST['chk_work_start_date'])){
								    $html .= '<td>'.$row['work_start_date'].'</td>';
								}
								if(isset($_POST['chk_work_completion_date'])){
								    $html .= '<td>'.$row['work_completion_date'].'</td>';
								}
								if(isset($_POST['chk_admin_go_no'])){
								    $html .= '<td>'.$row['admin_go_no'].'</br> '.$row['admin_go_date'].'</td>';
								}
								if(isset($_POST['chk_financial_go_no'])){
								    $html .= '<td>'.$row['financial_go_no'].' </br>'.$row['financial_go_date'].'</br>'.$row['financial_go_amount'].'</td>';
								}
								if(isset($_POST['chk_technical_sacntion_date'])){
								    $html .= '<td>'.$row['technical_sanction_date'].'</br>'.$row['technical_sanction_no'].'</td>';
								}
								if(isset($_POST['chk_tender_status'])){
								    $html .= '<td>'.$tender['status'].'</td>';
								}
								if(isset($_POST['chk_payable_gst'])){
								    $html .= '<td>'.$row['payable_gst_on_project'].'</td>';
								}
								if(isset($_POST['chk_architect_name'])){
								    $html .= '<td>'.$architect['full_name_english'].'</td>';
								}
								if(isset($_POST['chk_structural_architect'])){
								    $html .= '<td>'.$starch['full_name_english'].'</td>';
								}
								if(isset($_POST['chk_soli_testing'])){
								    $html .= '<td>'.$row['soil_testing_status'].'</td>';
								}
								if(isset($_POST['chk_estimation_status'])){
								    $html .= '<td>'.$row['estimation_formation_status'].'</td>';
								}
								if(isset($_POST['chk_je_name'])){
								    $html .= '<td>'.$je['full_name'].'</td>';
								}
								if(isset($_POST['chk_ae_name'])){
								    $html .= '<td>'.$ae['full_name'].'</td>';
								}
								if(isset($_POST['chk_excn_name'])){
								    $html .= '<td>'.$exe['full_name'].'</br>
    								'.$row['executive_engineer_from'].'--
    								'.$row['executive_engineer_to'].'</td>';
								}
								if(isset($_POST['chk_se_name'])){
								
								    $html .= '<td>'.$se['full_name'].'</br>'.$row['superintendent_engineer_from'].'--'.$row['superintendent_engineer_to'].'</td>';
								}
								if(isset($_POST['chk_revised_amount'])){
								    $html .= '<td>'.$row_invoice['revised_estimate_amount'].'</td>';
								}
								if(isset($_POST['chk_revision_reason'])){
								    $html .= '<td>'.$row_invoice['revised_estimate_remark'].'</td>';
								}
								if(isset($_POST['chk_revision_date'])){
								    $html .= '<td>'.$row_invoice['revised_estimate_date'].'</td>';
								}
								if(isset($_POST['chk_revision_sent_status'])){
								    $html .= '<td>'.$row_invoice['revised_dispatch_status'].'</br>'.$row_invoice['revised_remittance_amount'].'</td>';
								}
								if(isset($_POST['chk_revision_sent_date'])){
								    $html .= '<td>'.$row_invoice['revised_remittance_date'].'</td>';
								}
						
								if(isset($_POST['chk_total_received_amount_date'])){
									$html .= $rcpt_txt;
								}
								
								if(isset($_POST['chk_total_received_amount'])){
									
									$html .= '<td>'.$row_invoice['total_received_amount'].'</td>';
								}
								
								if(isset($_POST['chk_expense_current_fy'])){
									
									$html .='<td>'.$row_invoice['last_fy_tot_exp'].'</td>';
								}
								
								if(isset($_POST['chk_expense_current_fy_month'])){
									
									$html .='<td>'.$row_invoice['current_fy_last_month_exp'].'</td>';
								}	
								if(isset($_POST['chk_expense_current_month'])){
									
									$html .='<td>'.$row_invoice['current_month_exp'].'</td>';
								}
								
								if(isset($_POST['chk_expense_current_fy_total'])){
									
									$html .='<td>'.$row_invoice['current_fy_tot_exp'].'</td>';
								}
								if(isset($_POST['chk_expense_project'])){
									
									$html .='<td>'.$row_invoice['tot_exp_project'].'</td>';
								}
								if(isset($_POST['chk_balance_amount'])){
									
									$html .='<td>'.$row_invoice['balance_amt_cost'].'</td>';
								}
								if(isset($_POST['chk_balance_amount_refer'])){
									
									$html .='<td>'.$row_invoice['balance_amt_project'].'</td>';
								}
								if(isset($_POST['chk_percent_avmukt'])){
									$html .='<td>';
											if($row_invoice['total_received_amount']==0){
												$html .= 0;
											}else{
												$row_invoice['total_received_amount'] = (float)$row_invoice['total_received_amount'];
												$row_invoice['sanction_cost'] = ($row_invoice['sanction_cost']==''?1:$row_invoice['sanction_cost']);
												$html .= round($row_invoice['total_received_amount']/$row_invoice['sanction_cost']*100);
											}
										
									$html .= '</td>';
								}
								if(isset($_POST['chk_percent_physical_progress'])){
									$html .= $act_txt;
								}
								
								if(isset($_POST['chk_percent_physical_progress_per'])){
									
									$html .= '<td>'.$row_invoice['total_physical_progress'].'</td>';
								}
									
								if(isset($_POST['chk_project_status_1'])){
								
								
									$html .= '<td>'.$status1['status_1'].'</td>';
								}
								if(isset($_POST['chk_remarks'])){
									$html .= '<td>'.$row_invoice['remark'].'</td>';
								}
								
								if(isset($_POST['chk_last_update'])){
									$html .= '<td>';if ($row_invoice['last_update'] == "") {$html .= "NOT UPDATE";  } else {$html .= date("d-m-Y", strtotime($row_invoice['last_update']));}'</td>';
								}
								
								
								// if($row['status']=='' or $row['status']=='0'){
									// echo '<span class="text-primary">Active</span>';
								// }
								// else{
									// echo '<span class="text-danger">Disabled</span>';
								// }
								
								$html .= '</tr>'; 
							}



						$html .= '
						</tbody>
					</table>';


				
				
				header("Content-Type:application/xls");
                header("Content-Disposition:attachment;filename=download.xls");
                echo $html; ?>
	