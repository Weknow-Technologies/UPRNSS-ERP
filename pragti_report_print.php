<?php


include("scripts/settings.php");
 
$msg='';
$tab=1;
//print_r($_POST);
$chk = array();
foreach($_POST as $k=>$v){
	if($k!='student_ledger'){
		$chk[] = $v;
	}
}

if(isset($_SESSION['pragti_report_print'])){
	$_POST = $_SESSION['pragti_report_print'];
}
else{
	die('Invalid Request');
}

?>
<!DOCTYPE html>
<html>
<head>
    
</head>
<style>
	table, th, td{
		border:1px solid black;
		border-collapse: collapse;
	}
	@media print{
		*{
			font-size:0.8rem;
		}
	}
	@page{
		size:Legal landscape;
	}
</style>
<body>

		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table" border="1">
						<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							
							<th rowspan="2">क्रम संख्या </th>
							<th rowspan="2">ज़ोन   </th>
							<th rowspan="2">प्रखण्ड का नाम  </th>
							<th rowspan="2">जनपद  का नाम </th>
							<th rowspan="2">परियोजना का नाम (हिन्दी )</th>
							<th rowspan="2">विभाग </th>
							<th rowspan="2"> शासनादेश स्वकृत वर्ष </th>
							<th rowspan="2">योजना की स्वकृत लागत </th>
							<th colspan="2">अब तक अवमुक्त धनराशि का विवरण </th>
							<th rowspan="2">कुल अवमुक्त धनराशि </th>
							<th rowspan="2">कुल व्यय </th>
							<th rowspan="2">कार्य प्रारम्भ तिथि  </th>
							<th rowspan="2">कार्य पूर्ण होने की तिथि  </th>
							<th colspan="3">यू. सी. का विवरण </th>
							<th rowspan="2" >कार्य का स्तर </th>
							<th rowspan="2">भौतिक प्रगति </th>
							<th rowspan="2">Last Update </th>
						
						</tr>
						<tr style="border:1px solid white!important;">
							
							<th>दिनांक  </th>
							<th>धनराशि </th>
							<th>धनराशि </th>
							<th>प्रेषण तिथि </th>
							<th>कहाँ प्रेषित  </th>
							
						</tr>
						<tr>
							<?php
							for($i=1;$i<=19;$i++){
								echo '<th>'.$i.'</th>';
							}
							?>
						</tr>
						
						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, financial_go_amount, technical_sanction_date, technical_sanction_no, zone_id, last_update,status, master_freeze from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								where department_id in ('.implode(",", $_SESSION['department']).') and (status!="5" or status="0" or status is null or status="1")';
									//print_r($_POST);
								if(isset($_POST['search'])){
									if($_POST['department']!=''){
										$sql .= ' and department_id="'.$_POST['department'].'"';
									}
									if($_POST['district']!=''){
										$sql .= ' and district_id="'.$_POST['district'].'"';
									}
									if($_POST['division_name']!=''){
										$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
									}
									if($_POST['project_status_1']!=''){
										$sql .= ' and project_status_1="'.$_POST['project_status_1'].'"';	
									}
									if($_POST['project_type']!=''){
										$sql .= ' and project_type="'.$_POST['project_type'].'"';
									}
									if($_POST['sanction_cost']!=''){
										 $sanction_cost = $_POST['sanction_cost'];
										$sql .= ' and abs(sanction_cost)'.$_POST['type'].'"'.$sanction_cost.'"';
									}
									if($_POST['tot_exp_project']!=''){
										$sql .= ' and abs(tot_exp_project)'.$_POST['type1'].'"'.$_POST['tot_exp_project'].'"';
									}
									if($_POST['tender_status']!=''){
										$sql .= ' and tender_status="'.$_POST['tender_status'].'"';
									}
									if($_POST['date_type']!=''){
									$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
									}
									
								}
								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									if(in_array($row['sno'], $chk)){
										$sql = 'select * from invoice_civil where project_name="'.$row['sno'].'" order by sno desc limit 1';
										// echo $sql;
										$res = execute_query($sql);
										$row_invoice = mysqli_fetch_assoc($res);
										$sql = 'select * from master_zone where sno="'.$row['zone_id'].'"';
									// echo $sql;
									$res = execute_query($sql);
									$row_zone = mysqli_fetch_assoc($res);
										
										$sql = 'select * from transaction_civil_receipts where invoice_id="'.$row_invoice['sno'].'"';
											//echo $sql.'<br>';
											$result_rcpt = execute_query($sql);
											$rcpt_txt = '<td colspan="2" style="padding:0.2rem;"><table style="height:100%;">';
											$tot_rcpt = 0;
												$tot_exp = 0;
											if(mysqli_num_rows($result_rcpt)!=0){
												$count_rcpt = mysqli_num_rows($result_rcpt);
												
												while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
													$rcpt_txt .= '<tr><td width="45%">'.($row_rcpt['transaction_date']!=''?date("d-m-Y",strtotime($row_rcpt['transaction_date'])):'').'</td><td width="55%" >'.$row_rcpt['transaction_amount'].'</td></tr>';
												$tot_rcpt += (float)$row_rcpt['transaction_amount'];

												}

											}

											else{

												$count_rcpt=0;

											}

											$rcpt_txt .= '</table></td>
											<td>'.$tot_rcpt.'</td>';
										
										
										echo '<tr>
											
											<td>'.$i++.'</td>
											<td>'.$row_zone['zone_name'].'</td>
											<td>'.$row['division_name'].'</td>
											<td>'.$row['district_name_hindi'].'</td>
											<td>'.$row['project_name_hindi'].'</td>
											<td>'.$row['department_name_hindi'].'</td>
											<td>'.$row['admin_go_no'].'</td>
											<td>'.$row['sanction_cost'].'</td>';
											echo $rcpt_txt;
											echo '
										
											<td>'.$row_invoice['tot_exp_project'].'</td>
											
											<td>';if ($row_invoice['work_start_date'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row_invoice['work_start_date']));};echo'</td>
											
											<td>';if ($row_invoice['work_completion_date'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row_invoice['work_completion_date']));};echo'</td>
											<td></td>
											<td></td>
											<td></td>
											<td >'.$row_invoice['remark'].'</td>
											<td></td>
											
											<td>';if ($row_invoice['last_update'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row_invoice['last_update']));};echo'</td>
										
										</tr>'; 
									}
								}
							}
						
						
						?>
						</tbody>
					</table>
					
					</div>
                </div>
            </div>
		</div>

<script>
window.onload = function() {
            window.print();
        }
</script>
</body>
</html>