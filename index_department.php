<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['division_name'])){
	if($_POST['edit_sno']!=''){
		$sql = 'update uprnss_division set division_name="'.$_POST['division_name'].'" where s_no="'.$_POST['edit_sno'].'"';
	}
	else{
		$sql = 'insert into uprnss_division (division_name) values ("'.$_POST['division_name'].'")';
    	
	}
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
}
else{
	$_POST['division_name'] = '';
	$_POST['edit_sno'] = '';
}

if(isset($_GET['id'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['id'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['division_name'] = $data['division_name'];
	$_POST['edit_sno'] = $data['s_no'];
}

if(isset($_GET['act'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['act'].'"';
	
	$product = mysqli_fetch_assoc(execute_query($sql));
	
	if($product['active_inactive']=='' or $product['active_inactive']=='0'){
		$sql = 'update uprnss_division set active_inactive=1 where s_no='.$_GET['act'];
		execute_query($sql);
	}
	else{
		$sql = 'update uprnss_division set active_inactive=0 where s_no='.$_GET['act'];
		execute_query($sql);
	}
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
}


if(isset($_GET['delid'])){
	$sql='select * from uprnss_project_temp where division_id='.$_GET['delid']; 
	if(mysqli_num_rows(execute_query($sql))!=0){
		$msg.='<div class="alert alert-danger">Cannot Delete,You have Projects related to this division.</div>';
	}
	if($msg==''){
		$sql = 'delete from uprnss_project_temp where s_no='.$_GET['delid'];
		execute_query($sql);
		$msg.='<div class="alert alert-danger">Delete Successful</div>';
	}
}

if(isset($_GET['mid'])){
	$sql = 'update uprnss_project_temp set division_id="'.$_GET['mid'].'" where division_id="'.$_GET['alt'].'"';
	execute_query($sql);
	
	$sql = 'delete from uprnss_division where s_no='.$_GET['alt'];
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Merge Completed</p>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
}

?>


	<!------------------------------Department wise ----------------------------->
					

	
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<h4>&nbsp; Department</h4>
								<div class="content">
									<div class="row">
										<table class="table table-striped table-bordered table-hover">
											<thead style="position:sticky;top:0; z-index:2;">
												<tr>
													<th rowspan="3">क्र.सं. </th>
													<th rowspan="3">विभाग का नाम </th>
													<th rowspan="3">कुल परियोजनाये</th>
													<th rowspan="3">Close</th>
													<th rowspan="3">Running</th>
													<th colspan="6">वर्तमान में संचालित परियोजनाये</th>
													<th colspan="2">MPR Report as on</th>
												</tr>
												<tr>
													<th rowspan="2">Not Updated Project</th>
													<th rowspan="2">अनारम्भ परियोजनाये</th>
													<th rowspan="2">बाधित परियोजनाये</th>
													<th rowspan="2">प्रगति पर परियोजनाये</th>
													<th rowspan="2">पूर्ण परियोजनाये</th>
													<th rowspan="2">हस्तगत परियोजनाये</th>
													<th>4th</th>
													<th>16th</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$sql = 'select * from uprnss_department_name';
												$result_dep = execute_query($sql);
												$i = 1;
												$tot_allotted = 0;
												$tot_close_allotted = 0;
												$tot_running_allotted = 0;
												$tot_running_project = 0;
												$tot_interrupted_project = 0;
												$tot_unstarted_project = 0;
												$tot_complete_project = 0;
												$tot_handover_project = 0;
												$tot_allotted_not_update= 0;
												

												$tot_master = 0;
												$tot_mpr4 = 0;
												$tot_mpr16 = 0;
												while($row_dep = mysqli_fetch_assoc($result_dep)){
													$sql = 'select * from uprnss_project_temp where department_id="'.$row_dep['sno'].'" and status!="5"  ';
													$allotted = mysqli_num_rows(execute_query($sql));
													
									//////Close  Projects /////////////			
										
													$sql = 'select * from uprnss_project_temp where department_id="'.$row_dep['sno'].'" and status!="5" and reporting_status=1';
													$close_allotted = mysqli_num_rows(execute_query($sql));
													
											//////Running Projects /////////////	
											
													$sql = 'select * from uprnss_project_temp where department_id="'.$row_dep['sno'].'" and status!="5" and reporting_status=0';
													$running_allotted = mysqli_num_rows(execute_query($sql));
													
									//////Running Projects Brekup/////////////		
						
													$sql ='SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where uprnss_project_temp.department_id="'.$row_dep['sno'].'" and  uprnss_project_temp.status!="5" and( invoice_civil.project_status_1="" or invoice_civil.project_status_1 IS NUll ) and uprnss_project_temp.reporting_status=0';
													$allotted_not_update = mysqli_num_rows(execute_query($sql));
													
													
													$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and invoice_civil.project_status_1="5" and uprnss_project_temp.reporting_status=0';
													
													$unstarted_project = mysqli_num_rows(execute_query($sql));

													$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="4" or invoice_civil.project_status_1="6"or invoice_civil.project_status_1="13") and uprnss_project_temp.reporting_status=0';
													$interrupted_project = mysqli_num_rows(execute_query($sql));
													
													$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5"  and (invoice_civil.project_status_1="10" or invoice_civil.project_status_1="12") and uprnss_project_temp.reporting_status=0';
													$running_project = mysqli_num_rows(execute_query($sql));
													

													$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="7" or invoice_civil.project_status_1="11") and uprnss_project_temp.reporting_status=0';
													$complete_project = mysqli_num_rows(execute_query($sql));
													
													$sql = 'SELECT invoice_civil.department_id,uprnss_project_temp.project_type, invoice_civil.project_status_1  FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where invoice_civil.department_id="'.$row_dep['sno'].'" and uprnss_project_temp.status!="5" and (invoice_civil.project_status_1="8") and uprnss_project_temp.reporting_status=0';
													$handover_project = mysqli_num_rows(execute_query($sql));


													$sql = 'SELECT * FROM `invoice_civil` where department_id="'.$row_dep['sno'].'"  and last_update>="' . date("Y-m-01") . '" and last_update<="' . date("Y-m-13") . '"';
													$mpr_4 = mysqli_num_rows(execute_query($sql));

													$sql = 'SELECT * FROM `invoice_civil` where department_id="'.$row_dep['sno'].'"  and last_update>="' . date("Y-m-14") . '" and last_update<="' . date("Y-m-t") . '"';
													$mpr_16 = mysqli_num_rows(execute_query($sql));

													$tot_allotted += $allotted;
													$tot_close_allotted += $close_allotted;
													$tot_running_allotted += $running_allotted;
													$tot_running_project += $running_project;
													$tot_interrupted_project += $interrupted_project;
													$tot_unstarted_project += $unstarted_project;
													$tot_complete_project += $complete_project;
													$tot_handover_project += $handover_project;
													$tot_allotted_not_update += $allotted_not_update;

													$tot_mpr4 += $mpr_4;
													$tot_mpr16 += $mpr_16;
													echo '<tr>
													<td>' . $i++ . '</td>
													<td><a href="dashboard_department.php?id='.$row_dep['sno'].'" target="_blank">'.$row_dep['department_name_hindi'].'</a></td>
													<td>' . $allotted . '</td>
													<td>' . $close_allotted . '</td>
													<td>' . $running_allotted . '</td>
													
													<td>' . $allotted_not_update.'</td>
													<td>' . $unstarted_project . '</td>
													<td>' . $interrupted_project . '</td>
													<td>' . $running_project . '</td>
													<td>' . $complete_project . '</td>
													<td>' . $handover_project . '</td>
													<td>' . $mpr_4 . '</td>
													<td>' . $mpr_16 . '</td>
													</tr>';
												}
												echo '
												</tbody>
												<tfoot><tr>
												<th>&nbsp;</th>
												<th>Total</th>
												<th>' . $tot_allotted . '</th>
												<th>' . $tot_close_allotted . '</th>
												<th>' . $tot_running_allotted . '</th>
												<th>'.$tot_allotted_not_update.'</th>
												<th>' . $tot_unstarted_project . '</th>
												<th>' . $tot_interrupted_project . '</th>
												<th>' . $tot_running_project . '</th>
												<th>' . $tot_complete_project . '</th>
												<th>' . $tot_handover_project . '</th>
												<th>' . $tot_mpr4 . '</th>
												<th>' . $tot_mpr16 . '</th>
												</tr>';
												?>
												</tfoot>
										</table>
									</div>
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
function alternate_value(id){
	var alternate = prompt("Please enter product id to merge with this id.","");
	if(!alternate){
		alert("Can not merge without product id.");
		return false;
	}
	else{
		window.open("master_division.php?mid="+id+"&alt="+alternate, '_self');
		return true;
	}
}

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>