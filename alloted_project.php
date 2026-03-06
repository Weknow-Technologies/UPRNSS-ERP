<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;


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

<!------------------------------Alloted project  ----------------------------->							
	<div class="card">
			<div class="card-header ">
				<h4 class="card-title">Alloted Project</h4>
			</div>	
		<div class="card-body">		
			<div class="card-text">
				<div class="card-body table-full-width table-responsive">
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th rowspan="2">S.No.</th>
								<th rowspan="2">Department Name</th>
								<th rowspan="2">District Name</th>
								<th rowspan="2">Project Name</th>
								<th rowspan="2">Master Freeze</th>
								<th colspan="2">Reports as On</th>
							</tr>
							<tr>
								<th>4th</th>
								<th>16th</th>
							</tr>	
						</thead>
						<tbody>
						<?php
						$sql = 'select uprnss_project_temp.sno as sno, division_name, master_freeze, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, status 
						from uprnss_project_temp 
						left join uprnss_district on uprnss_district.sno = district_id
						left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
						left join uprnss_department_name on uprnss_department_name.sno = department_id
						where department_id in ('.implode(",", $_SESSION['department']).') and (uprnss_project_temp.status !="5" or uprnss_project_temp.status ="0" or uprnss_project_temp.status ="1" or  uprnss_project_temp.status is null )';
						// echo $sql;
						$result = execute_query($sql);
						$i=1;
						while($row = mysqli_fetch_assoc($result)){
							$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-01").'" and last_update<="'.date("Y-m-13").'"';
						$result_invoice_4 = execute_query($sql);
						if(mysqli_num_rows($result_invoice_4)!=0){
							$report_4 = '<p class="text text-success">Yes</p>';
						}
						else{
							$report_4 = '<p class="text text-danger">No</p>';
						}

						$sql = 'SELECT * FROM `invoice_civil` where project_name="'.$row['sno'].'"  and last_update>="'.date("Y-m-14").'" and last_update<="'.date("Y-m-t").'"';
						//echo $sql.'<br>';
						$result_invoice_16 = execute_query($sql);
						if(mysqli_num_rows($result_invoice_16)!=0){
							$report_16 = '<p class="text text-success">Yes</p>';
						}
						else{
							$report_16 = '<p class="text text-danger">No</p>';
						}
								
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row['department_name_hindi'].'</td>
							<td>'.$row['district_name_hindi'].'</td>
							<td>'.$row['project_name_hindi'].'<h6> '.$row['admin_go_no'].'</h6></td>
							<td>'.($row['master_freeze']=='1'?'<p class="text text-success">Yes</p>':'<p class="text text-danger">No</p>').'</td>
							<td>'.$report_4.'</td>
							<td>'.$report_16.'</td>
							</tr>
							';
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

    
<?php		
page_footer_end();
?>