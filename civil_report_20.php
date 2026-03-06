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
									<option value="last_update" <?php echo ($_POST['date_type']=='last_update'?' selected="selected"':''); ?>>Last update </option>
							        
							    </select>
							</th>
							<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
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
							<th>विभाग </th>
							<th width="15%">
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
							<th>उप-विभाग </th>							
							<th width="10%">
								<select class="form-control" name="sub_department" id="sub_department" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from uprnss_sub_department";
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['sub_department'])){
											if($_POST['sub_department']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['sub_department_hindi']).'</option>';
									}
									?>
								</select>
							</th>
							<th>प्रखण्ड </th>
							<th width="15%"><select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
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
							<th width="15%">
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

					<div style="text-align: right; width: 100%;">
						<a onclick="printPage()" class="no-print btn btn-success" style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print this page</a>
						<a onclick="printPage()" class="no-print btn btn-default" style="color: black; background-color: #ffffff; border-color: #cccccc; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download to PDF</a>
						<a onclick="printPage()" class="no-print btn btn-info" style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export to Excel</a>
					</div>
					</div>
			</div>
		</div>
		</form>
	</div>
<style>
		.printonly{
			display:none!important;
		}
		
		#overlays{
			z-index:0;
			opacity:0.15;
			position: fixed;
			top: 50%;
			left: 50%;
			-ms-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%); 
			/* display:none; */
		}
		
		table, th, td {
			border: 1px solid black;
			border-collapse: collapse;
		}
		th,td{
			padding:0.2rem;
		}
		@media print{
			.printonly{
				display:block!important;
			}
			#overlays{
				/* display:block; */
				opacity:0.2;
				width:35%!important;
				top: 50%!important;
				-ms-transform: translate(-50%, -50%);
				transform: translate(-50%, -50%);}
			}
			
	
	@page {
	size: A4 landscape;
	}
</style>
		
	<div class="row">
		<div class="col-md-12 text-center no-print">
			<button   type="" name="" class="btn btn-primary" onclick="window.print()"  >Print</button>
		</div>
	</div>
	<form name="test" action="pragti_report_print.php" method="POST" enctype="multipart/form-data">
		<div class="row">
            <div class="col-md-12">
                <div class="card">
					
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body" style="position:relative;">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table" border="1" style="position:sticky;top:0;">
						<thead>
							<tr>
								<th rowspan="2">क्रम.</th>
								<th rowspan="2">जनपद का नाम</th>
								<th rowspan="2">परियोजना का नाम</th>
								<th rowspan="2">स्वीकृति की तिथि</th>
								<th rowspan="2">कार्य प्रारम्भ की तिथि</th>
								<th rowspan="2">शासनादेश संख्या </th>
								<th rowspan="2">स्वीकृत लागत</th>
								<th colspan="2">कुल प्राप्त धनराशि व दिनांक </th>
								<th rowspan="2">कुल प्राप्त धनराशि</th>
								<th rowspan="2">कुल व्यय धनराशि </th>
								<th rowspan="2">अवशेष धनराशि परियोजना लागत के सापेक्ष</th>
								<th rowspan="2">वित्तीय प्रगति %(अवमुक्त के सापेक्ष)</th>
								<th rowspan="2">भौतिक प्रगति प्रतिशत में</th>
								<th colspan="2">भौतिक प्रगति </th>
								<th rowspan="2">अभ्युक्ति </th>
							</tr>
							<tr>
								<th>दिनांक  </th>
								<th>धनराशि  </th>
								<th>भवन की इकाई (विस्तृत)</th>
								<th>भौतिक प्रगति</th>
							</tr><tr>
							<?php
							for($i=1; $i<=17; $i++){
								echo '<td>'.$i.'</td>';
							}
							?>
							</tr>

						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name,sub_department_id, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, work_start_date, work_completion_date,state_share,central_share, admin_go_no, admin_go_date, financial_go_no, financial_go_date, financial_go_amount, technical_sanction_date, technical_sanction_no, last_update,status,zone_id, master_freeze from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								where (status!="5" or status="0"  or status is null or status="1")';
									//print_r($_POST);
								if(isset($_POST['search'])){
									if($_POST['department']!=''){
										$sql .= ' and department_id="'.$_POST['department'].'"';
									}
									if($_POST['sub_department']!=''){
										$sql .= ' and sub_department_id="'.$_POST['sub_department'].'"';
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
								$sql .=' ORDER BY uprnss_project_temp.division_id,district_id';
								// echo $sql;
								$result = execute_query($sql);
								
								while($row = mysqli_fetch_assoc($result)){
									
									$sql = 'select * from master_zone where sno="'.$row['zone_id'].'"';
									// echo $sql;
									$res = execute_query($sql);
									$row_zone = mysqli_fetch_assoc($res);
									
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
									
									
									
									
									echo '<tr>
										<td>'.$i++.'</td>
										<td>'.$row['district_name_hindi'].'</td>
										<td>'.$row['project_name_hindi'].'</td>
										
										
										'; ?>
										<td>
											<?php
											
											if (!empty($row['admin_go_date'])) {
												echo date('d-m-Y', strtotime($row['admin_go_date'])); 
											} else {
												echo '';
												}
											?>
										</td>
										<td>
											<?php
											
											
											if (!empty($row['work_start_date'])) {
												echo date('d-m-Y', strtotime($row['work_start_date'])); 
											} else {
												echo '';
												}
											?>
										</td>
										<td>
											<?php
											
											echo $row['admin_go_no'].'</br>';
											if (!empty($row['admin_go_date'])) {
												echo date('d-m-Y', strtotime($row['admin_go_date'])); 
											} else {
												echo '';
												}
											?>
										</td>
										
										
										
										<?php echo'
										<td>'.$row['sanction_cost'].'</td>';?>
										<?php echo'
										';?>
										
										<?php 
										echo $rcpt_txt;
										echo '
										<td>'.$row_invoice['total_received_amount'].'</td>
										<td>'.$row_invoice['tot_exp_project'].'</td>
										<td>'.$row_invoice['balance_amt_cost'].'</td>
										
										<td>';
												// if($row_invoice['total_received_amount']==0){
												// 	echo 0;
												// }else{
												// 	echo round($row_invoice['total_received_amount']/$row_invoice['sanction_cost']*100,2);
												// }
												if ($row_invoice['total_received_amount'] == 0) {
													echo 0;
												} else {
													// Cast to float for safety
													$total_received_amount = (float)$row_invoice['total_received_amount'];
													$sanction_cost = (float)$row_invoice['sanction_cost'];
													
													// Check if sanction_cost is non-zero to avoid division by zero
													if ($sanction_cost != 0) {
														echo round($total_received_amount / $sanction_cost * 100, 2);
													} else {
														echo "Invalid sanction cost";
													}
												}												
											
											echo '%</td>
										<td>'.$row_invoice['total_physical_progress'].'%</td>';
										echo $act_txt;
										echo '
										
										<td>'.$row_invoice['remark'].'</td>
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
function printPage() {
    window.print();
}
</script>
<script>

$('select[multiple]').multiselect({
	search: true
});
	
</script>
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