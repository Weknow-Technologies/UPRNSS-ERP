<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

if(isset($_POST['search'])){
	$_SESSION['pragti_report_print'] = $_POST;
}
// print_r($_POST);
	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['department'] = '';
		$_POST['district'] = '';
		$_POST['division_name'] = '';
		$_POST['project_name'] = '';
		$_POST['project_name_hindi'] = '';
		$_POST['project_name_hindi_unicode'] = '';
		$_POST['project_type'] = '';
		$_POST['work_start_date'] = '';
		$_POST['date_from'] = '';
		$_POST['date_to'] = '';
		$_POST['date_type'] ='';
		$_POST['sanction_cost'] = '';
		$_POST['type'] = '';
		$_POST['type1'] = '';
		$_POST['tot_exp_project'] = '';
	}




page_header_start();

?>
<script src="js/krutidev.js"></script>
<script src="js/unicode_keyboard.js"></script>
<style>
	#project_name_hindi{
		font-family: 'Kruti Dev 010';
		font-size:20px;
	}
	textarea{
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
	}

table, th, td {
  border: 0.1px solid black!important;
  border-collapse: collapse!important;
}
</style>

<?php
page_header_end();
page_sidebar();

?>

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th width="16%">Date Type</th>
							<th width="18%"><select name="date_type" id="date_type" class="form-control" >
									<option value="">--- Select ---</option>
                                    <option value="work_start_date" <?php echo ($_POST['date_type']=='work_start_date'?' selected="selected"':''); ?>>Work Start Date</option>
                                    <option value="work_completion_date" <?php echo ($_POST['date_type']=='work_completion_date'?' selected="selected"':''); ?>>Work Completion Date</option>
							        
							    </select>
							</th>
							<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
						</tr>
						<tr >
							<th>विभाग </th>							
							<th width="18%">
								<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from uprnss_department_name";
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['department'])){
											if($_POST['department']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['department_name_hindi']).'</option>';
									}
									?>
								</select>
							</th>
							<th>प्रखण्ड </th>
							<th width="18%"><select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from uprnss_division order by division_name ASC';
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['s_no'].'" ';
										if(isset($_POST['division_name'])){
											if($_POST['division_name']==$data['s_no']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['division_name'].'</option>';
									}
									?>
								</select></th>						
							<th>आच्छादित जनपद</th>
							<th width="18%">
								<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
                                    <option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_district";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['district'])){
												if($_POST['district']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.$data['district_name_hindi'].'</option>';
										}
										?>
								</select>
							</th>
						</tr>
						<tr >
							<th>Project Type</th>
							<th width="18%">
								<select name="project_type" id="project_type" class="form-control" onchange="onchangetype()" >
										<option value="">--- Select ---</option>
										<option value="1"<?php echo ($_POST['project_type']==1?'selected':''); ?>>Ho Level</option>
										<option value="2"<?php echo ($_POST['project_type']==2?'selected':''); ?>>Division Level</option>
									</select>
							</th>
							<th>Project Status</th>
							<th>
								<select class="form-control" name="project_status_1" id="project_status_1" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from master_projoect_status_1";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_status_1'])){
												if($_POST['project_status_1']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['status_1']).'</option>';
										}
										?>
									</select>
							</th>						
							<th>टेंडर की स्थिति</th>
							<th>
								<select class="form-control" name="tender_status" id="tender_status" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from master_tender_status";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['tender_status'])){
												if($_POST['tender_status']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['status']).'</option>';
										}
										?>
									</select>
							</th>
						</tr>
					</table>
					<table width="100%" class="table table-striped table-hover rounded" style="margin:0px; padding:0px;">
						
						<tr >
							
							<th width="18%">मूल परियोजना लागत ( लाख में )</th>
							<th width="6%">
								<select name="type" id="type" class="form-control" onchange="onchangetype()" >
									
									<option value="="<?php echo ($_POST['type']==1?'selected':''); ?>>=</option>
									<option value=">"<?php echo ($_POST['type']==1?'selected':''); ?>>></option>
									<option value="<"<?php echo ($_POST['type']==1?'selected':''); ?>> < </option>
									<option value="<="<?php echo ($_POST['type']==1?'selected':''); ?>> <= </option>
									<option value=">="<?php echo ($_POST['type']==1?'selected':''); ?>>>=</option>
								</select>
							</th>
							<th width="12%" style="margin:0px; padding:0px;">
								<input type="text" name="sanction_cost" id="sanction_cost" class="form-control" placeholder="" value="<?php echo $_POST['sanction_cost']; ?>" tabindex="<?php echo $tab++; ?>">
							</th>
							<th width="18%">परियोजना में अब तक का कुल व्यय ( लाख में )</th>
							<th width="6%">
								<select name="type1" id="type1" class="form-control" onchange="onchangetype()" >
									
									<option value="="<?php echo ($_POST['type']==1?'selected':''); ?>>=</option>
									<option value=">"<?php echo ($_POST['type']==1?'selected':''); ?>>></option>
									<option value="<"<?php echo ($_POST['type']==1?'selected':''); ?>> < </option>
									<option value="<="<?php echo ($_POST['type']==1?'selected':''); ?>> <= </option>
									<option value=">="<?php echo ($_POST['type']==1?'selected':''); ?>>>=</option>S
								</select>
							</th>
							<th width="12%" style="margin:0px; padding:0px;">
								<input type="text" name="tot_exp_project" id="tot_exp_project" class="form-control" placeholder="" value="<?php echo $_POST['tot_exp_project']; ?>" tabindex="<?php echo $tab++; ?>">
							</th>
						</tr>
						
						
						
					</table>
					<div class="col-md-12 text-center mt-3">
					<button type="submit" name="search" class="btn btn-primary">Search</button>
					<input type="hidden" id="id" name="id" value="1">
					<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
					</div>
				
			</div>
		</div>
		</form>
	</div>
	<form name="test" action="pragti_report_print.php" method="POST" enctype="multipart/form-data">
		<div class="row">
            <div class="col-md-12">
                <div class="card">
					<div class="col-md-12 text-center">
						<button  formtarget="_blank" type="submit" name="student_ledger" class="btn btn-primary"  >Print</button>
					</div>
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table" border="1">
						<thead style="position:sticky;top:0; z-index:2;">
						<tr>
							<th rowspan="2"></th>
							<th rowspan="2">क्रम संख्या </th>
							<th rowspan="2">जोन  </th>
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
							for($i=1;$i<=21;$i++){
								echo '<th>'.$i.'</th>';
							}
							?>
						</tr>
						
						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, uprnss_project_temp.project_name, project_name_hindi, uprnss_project_temp.sanction_date, uprnss_project_temp.sanction_cost,uprnss_project_temp.revised_date, uprnss_project_temp.revised_cost, uprnss_project_temp.work_start_date, uprnss_project_temp.work_completion_date, uprnss_project_temp.admin_go_no, uprnss_project_temp.admin_go_date, uprnss_project_temp.financial_go_no, uprnss_project_temp.financial_go_date, uprnss_project_temp.financial_go_amount, uprnss_project_temp.technical_sanction_date, uprnss_project_temp.technical_sanction_no, uprnss_project_temp.last_update,uprnss_project_temp.status,zone_id, uprnss_project_temp.master_freeze, invoice_civil_sno  from uprnss_project_temp 
								left join invoice_civil on uprnss_project_temp.invoice_civil_sno = invoice_civil.sno
								left join uprnss_district on uprnss_district.sno = uprnss_project_temp.district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
								where uprnss_project_temp.department_id in ('.implode(",", $_SESSION['department']).') and (uprnss_project_temp.status!="5" or uprnss_project_temp.status="0" or uprnss_project_temp.status is null or uprnss_project_temp.status="1")';
									//print_r($_POST);
								if(isset($_POST['search'])){
									if($_POST['department']!=''){
										$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
									}
									if($_POST['district']!=''){
										$sql .= ' and uprnss_project_temp.district_id="'.$_POST['district'].'"';
									}
									if($_POST['division_name']!=''){
										$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
									}
									if($_POST['project_status_1']!=''){
										$sql .= ' and invoice_civil.project_status_1="'.$_POST['project_status_1'].'"';	
									}
									if($_POST['project_type']!=''){
										$sql .= ' and uprnss_project_temp.project_type="'.$_POST['project_type'].'"';
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

										$rcpt_txt .= '</table></td>
										<td>'.$tot_rcpt.'</td>';
									
									
									echo '<tr>
									<td><input type="checkbox" name="print_sno_'.$row['sno'].'" id="date_of_acceptance_revised_cost" class="form-control" value="'.$row['sno'].'" tabindex=""></td>
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
						
						
						?>
						</tbody>
					</table>
					
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
<script>

$('select[multiple]').multiselect({
	search: true
});
	
$(document).ready( function () {
    /*$('#general_stat_table').DataTable({
		paging: false,
		fixedHeader: true,
		colReorder: true
		});
	});	*/

	
	var t = $('#general_stat_table').DataTable({
		paging: false
    });
 
    
});
	
</script>

    
<?php		
page_footer_end();
?>