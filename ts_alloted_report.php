<?php
include("scripts/settings.php");
$msg='';

page_header_start();
page_header_end();
page_sidebar();

if(isset($_GET['delid'])){

	$sql = 'select * from technical_sanction where sno="'.$_GET['delid'].'"';
		$data = mysqli_fetch_assoc(execute_query($sql));  
		 $data['project_id'];
	// status 5= deleted
	
	$sql = 'update technical_sanction set
		status = "5"
		
		where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
					execute_query($sql);
				$msg .= '<p class="alert alert-success">Deleted</p>';
			}
	
}
?>
		<form name="test" action="technical_sanction_print.php" method="POST" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-12">
					<div class="card strpied-tabled-with-hover">
						<?php  if($_SESSION['usertype']!='1'){ echo'
							<div class="card-header ml-auto no-print">
								<a href="create_ts.php" class="text-right" target=""><u><i class="icon fas fa-plus" aria-hidden="true"></i> नामित के लिए शेष प्रियोजनाये</u></a>
							</div>';
							}
						echo $msg;
							?>
						<div class="col-md-12 text-center">
							<button  formtarget="_blank" type="submit" name="student_ledger" class="btn btn-primary"  >Print</button>
						</div>
						<div class="card-body table-full-width table-responsive">
							<table class="table table-hover table-striped" id="general_stat_table">
								<thead>
									<tr>
										<?php if($_SESSION['usertype']!='1'){ echo'
											<th class="no-print"></th>';
										}
										?>
										<th>क्रं  सं.</th>
										<th>कार्य का नाम </th>
										<th>धनराशि (लाख मे )</th>
										<th>स्तर </th>
										<th>शासनादेश  संख्या/ दिनांक</th>
										<th>जनपद का नाम  </th>
										<th>प्रखण्ड का नाम  </th>
										<th>Technical Sanction Number</th>
										<th>Technical Sanction Date </th>
										<?php if($_SESSION['usertype']!='1'){ echo'
											<th class= "no-print">Edit</th>
											<th class= "no-print">Delete</th>';
										}
										?>
										<th>Work Order</th>
									</tr>
								</thead>
								
								<tbody>
									<?php
									if($_SESSION['usertype']!='1'){
										$sql = 'SELECT * FROM `technical_sanction` WHERE status!="5" order by sno DESC';
										// echo $sql;
										$result = execute_query($sql);
										$i=1;
										while($row = mysqli_fetch_assoc($result)){	
												$sql = 'select * from uprnss_division where s_no="'.$row['division_id'].'"';
												// echo $sql;
												$divisions = mysqli_fetch_assoc(execute_query($sql));

												$sql = 'select * from uprnss_district where sno="'.$row['district_id'].'"';
												// echo $sql;
												$district = mysqli_fetch_assoc(execute_query($sql));
												
												$sql = 'select * from uprnss_project_temp where sno="'.$row['project_id'].'"';
												// echo $sql;
												$row_project = mysqli_fetch_assoc(execute_query($sql));
												
												
												$starch = mysqli_fetch_assoc(execute_query($sql));
												
												echo '<tr>
												<td><input type="checkbox" name="print_sno_'.$row['sno'].'" id="date_of_acceptance_revised_cost" class="form-control" value="'.$row['sno'].'" tabindex=""></td>
												<td>'.$i++.'</td>
												<td>'.$row_project['project_name_hindi'].'</td>
												<td>'.$row_project['sanction_cost'].'</td>
												<td>';
												if($row_project['project_type']!=''){
													if($row_project['project_type']=='1'){
														echo '<span class="">शासन स्तर </span>';
													}
													elseif($row_project['project_type']=='2'){
														echo '<span class="">जिला स्तर </span>';
													}
												}
												echo '
												</td>
												<td>'.$row_project['admin_go_no'].'<br>';if ($row_project['admin_go_date'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row_project['admin_go_date']));};echo'</td>
												
												<td>'.$district['district_name_hindi'].'</td>
												<td>'.$divisions['division_name'].'</td>
												
												
												<td>'.$row['ts_no'].'</td>
												<td>'.$row['ts_date'].'</td>
												
												<td class="no-print text-center">
													<a href="create_ts.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title=""></span></a>
												</td>
												<td class="no-print text-center">
													<a href="ts_alloted_report.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
												</td>
												<td class="no-print text-center">
												<a href="ts_print.php?id='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');" target="_blank" class="btn btn-success" >Print</a></td>
												</tr>';
											}
									}
									else{
										// $sql = 'SELECT * FROM `technical_sanction` WHERE status!="5" order by sno DESC';
										$sql = 'SELECT * FROM `technical_sanction` where division_id in ('.implode(",", $_SESSION['divisions']).') and status!="5" order by sno DESC';
										// echo $sql;
										$result = execute_query($sql);
										$i=1;
										while($row = mysqli_fetch_assoc($result)){	
												$sql = 'select * from uprnss_division where s_no="'.$row['division_id'].'"';
												// echo $sql;
												$divisions = mysqli_fetch_assoc(execute_query($sql));

												$sql = 'select * from uprnss_district where sno="'.$row['district_id'].'"';
												// echo $sql;
												$district = mysqli_fetch_assoc(execute_query($sql));
												
												$sql = 'select * from uprnss_project_temp where sno="'.$row['project_id'].'"';
												// echo $sql;
												$row_project = mysqli_fetch_assoc(execute_query($sql));
												
												
												$starch = mysqli_fetch_assoc(execute_query($sql));
												
												echo '<tr>
												<td><input type="checkbox" name="print_sno_'.$row['sno'].'" id="date_of_acceptance_revised_cost" class="form-control" value="'.$row['sno'].'" tabindex=""></td>
												<td>'.$i++.'</td>
												<td>'.$row_project['project_name_hindi'].'</td>
												<td>'.$row_project['sanction_cost'].'</td>
												<td>';
												if($row_project['project_type']!=''){
													if($row_project['project_type']=='1'){
														echo '<span class="">शासन स्तर </span>';
													}
													elseif($row_project['project_type']=='2'){
														echo '<span class="">जिला स्तर </span>';
													}
												}
												echo '<tr>
												<td><input type="checkbox" name="print_sno_'.$row['sno'].'" id="date_of_acceptance_revised_cost" class="form-control" value="'.$row['sno'].'" tabindex=""></td>
												<td>'.$i++.'</td>
												<td>'.$row_project['project_name_hindi'].'</td>
												<td>'.$row_project['sanction_cost'].'</td>
												<td>';
												if($row_project['project_type']!=''){
													if($row_project['project_type']=='1'){
														echo '<span class="">शासन स्तर </span>';
													}
													elseif($row_project['project_type']=='2'){
														echo '<span class="">जिला स्तर </span>';
													}
												}
												echo '
												</td>
												<td>'.$row_project['admin_go_no'].'<br>';if ($row_project['admin_go_date'] == "") {echo "-";  } else {echo date("d-m-Y", strtotime($row_project['admin_go_date']));};echo'</td>
												
												<td>'.$district['district_name_hindi'].'</td>
												<td>'.$divisions['division_name'].'</td>
												
												
												<td>'.$row['ts_no'].'</td>
												<td>'.$row['ts_date'].'</td>
												
												<td class="no-print text-center">
													<a href="create_ts.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title=""></span></a>
												</td>
												<td class="no-print text-center">
													<a href="ts_alloted_report.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
												</td>
												<td class="no-print text-center">
												<a href="ts_print.php?id='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');" target="_blank" class="btn btn-success" >Print</a></td>
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
	<!--  Charts Plugin -->
	<script src="js/chartist.min.js"></script>
	
<script>
	$('select[multiple]').multiselect();	
	
$(document).ready( function () {
    $('#general_stat_table').DataTable({
			// paging: false,
			fixedHeader: true,
			// colReorder: true,
			// scrollX: true, // Enable horizontal scrolling if needed
	});
	
    
});
	
</script>

<?php		
page_footer_end();
?>
