<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['unit_name'])){
	
	/*$sql = 'insert into invoice_format_1 (user_id, entry_date, creation_time) values ("'.$_SESSION['usersno'].'", "'.date("Y-m-d").'", "'.date("Y-m-d H:i:s").'")';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['id']; $i++){
			$sql= ' insert into transaction_format_1 (invoice_id, month, head_type, amount, utr_no, transaction_date, other) values ("'.$inv_id.'", "' .$_POST['month_'.$i].'", "'.$_POST['head_type_'.$i].'", "'.$_POST['amount_'.$i].'", "'.$_POST['utr_no_'.$i].'", "'.$_POST['transaction_date_'.$i].'", "'.$_POST['other_'.$i].'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';
			}
		}
	}*/
}
else{
		
	$_POST['unit_name_1'] = '';
	$_POST['month_1'] = '';
	$_POST['head_type_1'] = '';
	$_POST['amount_1'] = '';
	$_POST['utr_no_1'] = '';
	$_POST['transaction_date_1'] = '';
	$_POST['other_1'] = '';


}


if(isset($_GET['del']) && $_SESSION['usertype']=='sadmin' ){
	$sql = 'delete from invoice_civil where sno="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 1 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_activities where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 2 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_receipts where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 3 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_attachment where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 4 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_revised_estimate where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 3 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_revised_remittance where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 3 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	if($msg==''){
		$msg .= '<div class="alert alert-danger">Deleted</div>';
	}
	
}
if(isset($_GET['del']) && $_SESSION['usertype']=='2' ){
	$sql = 'delete from invoice_civil where sno="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 1 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_activities where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 2 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_receipts where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 3 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	$sql = 'delete from transaction_civil_attachment where invoice_id="'.$_GET['del'].'"';
	execute_query($sql);
	if(mysqli_error($db)){
		$msg .= '<div class="alert alert-danger">Er # 4 : Delete Failed. >> '.$sql.' >> '.mysqli_error($db).'</div>';
	}
	if($msg==''){
		$msg .= '<div class="alert alert-success">Delete Success</div>';
	}
	
}

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

?>

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">
				<div class="card-header">
                        <h4 class="card-title text-center">Civil Report </h4></br>
                    </div>
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th width="16%">Date Type</th>
							<th width="18%"><select name="date_type" id="date_type" class="form-control" >
									
                                    <option value="invoice_civil.last_update" <?php echo ($_POST['date_type']=='last_update'?' selected="selected"':''); ?>>Entry Date</option> 
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
					</table>

					<div style="text-align: right; width: 100%;">
						<a onclick="printPage()" class="no-print btn btn-success" style="color: white; background-color: #5bc0de; border-color: #46b8da; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Print this page</a>
						<a onclick="printPage()" class="no-print btn btn-default" style="color: black; background-color: #ffffff; border-color: #cccccc; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 10px;">Download to PDF</a>
						<a onclick="printPage()" class="no-print btn btn-info" style="color: white; background-color: #5cb85c; border-color: #4cae4c; padding: 5px 10px; border-radius: 4px; text-decoration: none;">Export to Excel</a>
					</div>

					<div class="col-md-12 text-center">
						<button type="submit" name="search" class="btn btn-primary">Search</button>
						<input type="hidden" id="id" name="id" value="1">
						<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
					</div>
			</div>
		</div>
		</form>
	</div>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">Civil Report </h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th class="text-left">S.No.</th>
								<th class="text-left">Department Name</th>
								<?php if($_SESSION['usertype']=='sadmin'){ echo '<th class="text-left">Division Name</th>'; } ?>
								<?php if($_SESSION['usertype']=='2'){ echo '<th class="text-left">Division Name</th>'; } ?>
								<th class="text-left">District Name</th>
								<th class="text-left">Project Name</th>
								<th class="text-left">Entry Date</th>
								<th class="text-left">Project Status 1</th>
								<?php if($_SESSION['usertype']=='sadmin'){ echo '<th class="text-left"></th>'; } ?>
								<?php if($_SESSION['usertype']=='2'){ echo '<th class="text-left"></th>'; } ?>
								
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
						if($_SESSION['usertype']=='sadmin'){
							if(isset($_POST['search'])){
								$sql = 'SELECT invoice_civil.sno as sno, division_name, invoice_civil.unit_id as unit_id, invoice_civil.department_id as department_id, department_name_hindi, invoice_civil.district_id as district_id, district_name_hindi, uprnss_project_temp.project_name_hindi as project_name, invoice_civil.last_update as last_update, invoice_civil.project_status_1, invoice_civil.project_status_2 
								FROM `invoice_civil`
								left join uprnss_department_name on uprnss_department_name.sno = invoice_civil.department_id
								left join uprnss_district on uprnss_district.sno = district_id 
								left join uprnss_division on uprnss_division.s_no = invoice_civil.division_id 
								left join uprnss_project_temp on uprnss_project_temp.sno = invoice_civil.project_name 
								where 1=1';
								
								if(isset($_POST['department'])){
									if($_POST['department']!=''){
										$sql .= ' and invoice_civil.department_id="'.$_POST['department'].'"';
									}
									if($_POST['district']!=''){
										$sql .= ' and invoice_civil.district_id="'.$_POST['district'].'"';
									}
									if($_POST['division_name']!=''){
										$sql .= ' and invoice_civil.division_id="'.$_POST['division_name'].'"';
									}
									if($_POST['date_type']!=''){
										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
									}
								}
								$sql .=	' order by invoice_civil.last_update DESC';
								// $sql .=	' order by abs(invoice_civil.department_id and invoice_civil.last_update)';
								// echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									$sql = 'select * from master_projoect_status_1 where sno="'.$row['project_status_1'].'"';
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
									
									$sql = 'select * from users where sno="'.$row['unit_id'].'"';
									$unit_name = mysqli_fetch_assoc(execute_query($sql));
									
									echo '<tr>
									<td>'.$i++.'</td>
									
									<td>'.$row['department_name_hindi'].'</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['last_update'].'</td>
									<td>'.$status1['status_1'].'</td>
									
									<td><a href="view_civil.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td><a href="departmental_report.php?edit_sno='.$row['sno'].'" target="_blank"><i class="fa fa-pen"></i></a></td>';
									if($_SESSION['usertype']=='sadmin'){
										echo '<td><a href="report_civil.php?del='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00;"><i class="fa fa-trash-alt"></i></a></td>';
									}
									
									echo '</tr>';
								}
							}
						}
						elseif ($_SESSION['usertype']=='2'){
							if(isset($_POST['search'])){
								$sql = 'SELECT invoice_civil.sno as sno, division_name, invoice_civil.unit_id as unit_id, invoice_civil.department_id as department_id, department_name_hindi, invoice_civil.district_id as district_id, district_name_hindi, uprnss_project_temp.project_name_hindi as project_name, invoice_civil.last_update as last_update, invoice_civil.project_status_1, invoice_civil.project_status_2 
								FROM `invoice_civil`
								left join uprnss_department_name on uprnss_department_name.sno = invoice_civil.department_id
								left join uprnss_district on uprnss_district.sno = district_id 
								left join uprnss_division on uprnss_division.s_no = invoice_civil.division_id 
								left join uprnss_project_temp on uprnss_project_temp.sno = invoice_civil.project_name 
								where 1=1 and invoice_civil.department_id in ('.implode(",", $_SESSION['department']).')';
								
								if(isset($_POST['department'])){
									if($_POST['department']!=''){
										$sql .= ' and invoice_civil.department_id="'.$_POST['department'].'"';
									}
									if($_POST['district']!=''){
										$sql .= ' and invoice_civil.district_id="'.$_POST['district'].'"';
									}
									if($_POST['division_name']!=''){
										$sql .= ' and invoice_civil.division_id="'.$_POST['division_name'].'"';
									}
									if($_POST['date_type']!=''){
										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
										}
								}
								$sql .=	' order by invoice_civil.last_update DESC';
								// $sql .=	' order by abs(invoice_civil.department_id and invoice_civil.last_update)';
								$result = execute_query($sql);
								echo mysqli_error($db);
								while($row = mysqli_fetch_assoc($result)){
									$sql = 'select * from master_projoect_status_1 where sno="'.$row['project_status_1'].'"';
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
									
									$sql = 'select * from users where sno="'.$row['unit_id'].'"';
									$unit_name = mysqli_fetch_assoc(execute_query($sql));
									
									echo '<tr>
									<td>'.$i++.'</td>
									
									<td>'.$row['department_name_hindi'].'</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['last_update'].'</td>
									<td>'.$status1['status_1'].'</td>
									
									<td><a href="view_civil.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td><a href="departmental_report.php?edit_sno='.$row['sno'].'" target="_blank"><i class="fa fa-pen"></i></a></td>';
									
									echo '</tr>';
								}
							}
						}
						else{
							if(isset($_POST['search'])){
								$sql = 'SELECT invoice_civil.sno as sno, division_name, invoice_civil.unit_id as unit_id, invoice_civil.department_id as department_id, department_name_hindi, invoice_civil.district_id as district_id, district_name_hindi, uprnss_project_temp.project_name_hindi as project_name, invoice_civil.last_update as last_update, invoice_civil.project_status_1, invoice_civil.project_status_2 
								FROM `invoice_civil`
								left join uprnss_department_name on uprnss_department_name.sno = invoice_civil.department_id
								left join uprnss_district on uprnss_district.sno = district_id 
								left join uprnss_division on uprnss_division.s_no = invoice_civil.division_id 
								left join uprnss_project_temp on uprnss_project_temp.sno = invoice_civil.project_name 
								where 1=1 and (invoice_civil.unit_id = "'.$_SESSION['usersno'].'" or invoice_civil.division_id in ('.implode(",", $_SESSION['divisions']).')) ';
								
								if(isset($_POST['search'])){
									if($_POST['department']!=''){
										$sql .= ' and invoice_civil.department_id="'.$_POST['department'].'"';
									}
									if($_POST['district']!=''){
										$sql .= ' and invoice_civil.district_id="'.$_POST['district'].'"';
									}
									if($_POST['division_name']!=''){
										$sql .= ' and invoice_civil.division_id="'.$_POST['division_name'].'"';
									}
									if($_POST['date_type']!=''){
										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
									}
								}
								
								$sql .=	' order by invoice_civil.last_update DESC';
								// echo '@@@@@@@@@@'.$sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									$sql = 'select * from master_projoect_status_1 where sno="'.$row['project_status_1'].'"';
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
									
									$sql = 'select * from users where sno="'.$row['unit_id'].'"';
									$unit_name = mysqli_fetch_assoc(execute_query($sql));
									
									echo '<tr>
									<td>'.$i++.'</td>
									
									<td>'.$row['department_name_hindi'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['last_update'].'</td>
									<td>'.$status1['status_1'].'</td>
									
									<td><a href="view_civil.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td><a href="departmental_report.php?edit_sno='.$row['sno'].'" target="_blank"><i class="fa fa-pen"></i></a></td>';
									
									echo '</tr>';
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

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>