<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;


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
		font-size: 20px;
	}
	textarea{
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
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
					</table>
					<div style="text-align: right; width: 100%;">
						<a onclick="printPage()" class="no-print btn btn-success" style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print this page</a>
						<a onclick="printPage()" class="no-print btn btn-default" style="color: black; background-color: #ffffff; border-color: #cccccc; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download to PDF</a>
						<a onclick="printPage()" class="no-print btn btn-info" style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export to Excel</a>
					</div>
					<button type="submit" name="search" class="btn btn-primary">Search</button>
					<input type="hidden" id="id" name="id" value="1">
					<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
				
			</div>
		</div>
		</form>
	</div>
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="2">S.No.</th>
								<th rowspan="2">Department Name</th>
								<th rowspan="2">District Name</th>
								<th rowspan="2">Project Name</th>
								<th rowspan="2">Project Status</th>
								<th rowspan="2">Master Filled</th>
								<th colspan="2">Reports as On</th>
							</tr>
							<tr>
								<th>4th</th>
								<th>16th</th>
							</tr>
							<tr>
								<?php
								for($i=1;$i<=8;$i++){
									echo '<th>'.$i.'</th>';
								}
								?>
							</tr>
						</thead>
						 
						<tbody>
							<?php
							if($_SESSION['usertype']=='2'){
								if(isset($_POST['search'])){
								$sql = 'SELECT uprnss_project_temp.sno as sno, project_name_hindi, department_name_hindi, sub_department_hindi, district_name_hindi, admin_go_no  FROM `uprnss_project_temp` 
								left join uprnss_district on uprnss_district.sno = district_id 
								left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
								left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
								where uprnss_project_temp.department_id in ('.implode(",", $_SESSION['department']).') and (uprnss_project_temp.status="0" or uprnss_project_temp.status is null)';
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
									}
								$sql .=	' order by department_name_hindi ASC';
								$result = execute_query($sql);
								$i=1;
								while($row = mysqli_fetch_assoc($result)){
									$sql = 'select * from invoice_civil where project_name="'.$row['sno'].'" and division_id!="" order by sno desc limit 1';
									
									$status = mysqli_fetch_assoc(execute_query($sql));
									$sql = 'select * from master_projoect_status_1 where sno="'.$status['project_status_1'].'"';
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
									
									$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-01").'" and last_update<="'.date("Y-m-13").'"';
									$result_invoice_4 = execute_query($sql);
									if(mysqli_num_rows($result_invoice_4)!=0){
										$report_4 = '<p class="text text-success">Yes</p>';
										$report_4_flag = 1;
									}
									else{
										$report_4 = '<p class="text text-danger">No</p>';
										$report_4_flag = 0;
									}
									
									$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-14").'" and last_update<="'.date("Y-m-t").'"';
									//echo $sql.'<br>';
									$result_invoice_16 = execute_query($sql);
									if(mysqli_num_rows($result_invoice_16)!=0){
										$report_16 = '<p class="text text-success">Yes</p>';
										$report_16_flag = 1;
									}
									else{
										$report_16 = '<p class="text text-danger">No</p>';
										$report_16_flag = 0;
									}
									
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row['department_name_hindi'].'</br><h6>'.$row['sub_department_hindi'].'</h6></td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['project_name_hindi'].'<h6> '.$row['admin_go_no'].'</h6></td>
									<td>'.$status1['status_1'].'</td>
									<td>'.($row['admin_go_no']!=''?'<p class="text text-success">Yes</p>':'<p class="text text-danger">No</p>').'</td>
									
									<td class="no-print text-center">';
									
									if($report_4_flag =='1'){
										echo '<span class="text text-warning">'.$report_4.'</span>';
									}
									else{
										$date=date("Y-m-d");
										if($date <= date("Y-m-13")){
											echo '<a target="_blank" href="departmental_report.php?id='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Project Reporting" ></span></a><br/><br/>';
											echo '<span class="text text-danger">No</span>';
										}
										else{
											echo '<span class="text text-danger">No</span>';
										}
									}
									echo '</td>
									
									<td class="no-print text-center">';
									// echo $report_16;
									if($report_16_flag =='1'){
										echo '<span class="text text-warning">'.$report_16.'</span>';
									}
									else{
										$date=date("Y-m-d");
										if($date >= date("Y-m-14") and $date <= date("Y-m-27") ){
											echo '<a target="_blank" href="departmental_report.php?id='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Project Reporting" ></span></a><br/><br/>';
											echo '<span class="text text-danger">No</span>';
										}
										else{
											echo '<span class="text text-danger">No</span>';
										}
									}
									echo '</td>
									</tr>
									';
								}
								}
							}
							else{
							if(isset($_POST['search'])){	
								
							$sql = 'SELECT uprnss_project_temp.sno as sno, project_name_hindi, department_name_hindi,  sub_department_hindi, district_name_hindi, uprnss_project_temp.admin_go_no  FROM `uprnss_project_temp` 
							left join uprnss_district on uprnss_district.sno = district_id 
							left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
							left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
							where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).')and(uprnss_project_temp.status="0" or uprnss_project_temp.status="1" or uprnss_project_temp.status is null )';
							// echo $sql;
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
								}
							$sql .=	' order by department_name_hindi ASC';
							// echo $sql;
							$result = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_assoc($result)){
								$sql = 'select * from invoice_civil where project_name="'.$row['sno'].'" and division_id!="" order by sno desc limit 1';
								
								$status = mysqli_fetch_assoc(execute_query($sql));
								if (!empty($status['project_status_1'])){
								$sql = 'select * from master_projoect_status_1 where sno="'.$status['project_status_1'].'"';
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
								}else{
									$status1['status_1'] = '';
								}	
								$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-01").'" and last_update<="'.date("Y-m-13").'"';
								$result_invoice_4 = execute_query($sql);
								if(mysqli_num_rows($result_invoice_4)!=0){
									$report_4 = '<p class="text text-success">Yes</p>';
									$report_4_flag = 1;
								}
								else{
									$report_4 = '<p class="text text-danger">No</p>';
									$report_4_flag = 0;
								}
								
								$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-14").'" and last_update<="'.date("Y-m-t").'"';
								//echo $sql.'<br>';
								$result_invoice_16 = execute_query($sql);
								if(mysqli_num_rows($result_invoice_16)!=0){
									$report_16 = '<p class="text text-success">Yes</p>';
									$report_16_flag = 1;
								}
								else{
									$report_16 = '<p class="text text-danger">No</p>';
									$report_16_flag = 0;
								}
								
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row['department_name_hindi'].'</br><h6>'.$row['sub_department_hindi'].'</h6></td>
								<td>'.$row['district_name_hindi'].'</td>
								<td>'.$row['project_name_hindi'].'<h6> '.$row['admin_go_no'].'</h6></td>
								<td>'.$status1['status_1'].'</td>
								<td>'.($row['admin_go_no']!=''?'<p class="text text-success">Yes</p>':'<p class="text text-danger">No</p>').'</td>
								<td class="no-print text-center">';
								
								if($report_4_flag =='1'){
									echo '<span class="text text-warning">'.$report_4.'</span>';
								}
								else{
									$date=date("Y-m-d");
									if($date <= date("Y-m-13")){
										echo '<a target="_blank" href="departmental_report.php?id='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Project Reporting" ></span></a><br/><br/>';
										echo '<span class="text text-danger">No</span>';
									}
									else{
										echo '<span class="text text-danger">No</span>';
									}
								}
								echo '</td>
								
								<td class="no-print text-center">';
								// echo $report_16;
								if($report_16_flag =='1'){
									echo '<span class="text text-warning">'.$report_16.'</span>';
								}
								else{
									$date=date("Y-m-d");
									if($date >= date("Y-m-14") and $date <= date("Y-m-27") ){
										echo '<a target="_blank" href="departmental_report.php?id='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Project Reporting" ></span></a><br/><br/>';
										echo '<span class="text text-danger">No</span>';
									}
									else{
										echo '<span class="text text-danger">No</span>';
									}
								}
								echo '</td>
								</tr>
								';
							}
							}}
							?>
						</tbody>
					</table>
					
					</div>
                </div>
            </div>
		</div>
	
	
	
	
	
	
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