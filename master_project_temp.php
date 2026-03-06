<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

if(isset($_POST['submit'])){
	if($_POST['department']==''){
		$msg .= '<div class="alert alert-danger">Select Department Name</div>';
	}
	if($_POST['division_name']==''){
		$msg .= '<div class="alert alert-danger">Select Division Name</div>';
	}
	if($_POST['project_name']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Name</div>';
	}
	if($_POST['project_name_hindi_unicode']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Name in Hindi</div>';
	}
	if($msg == ''){
		if($_POST['edit_sno']==''){
			$sql = 'insert into uprnss_project_temp (district_id, department_id, sub_department_id, division_id, unit_id, project_name, project_name_hindi, project_type, master_freeze, created_by, creation_time, status) values ("'.$_POST['district'].'", "'.$_POST['department'].'","'.$_POST['sub_department_id'].'", "'.$_POST['division_name'].'", "'.$_SESSION['username'].'", "'.$_POST['project_name'].'", "'.$_POST['project_name_hindi_unicode'].'","'.$_POST['project_type'].'","0", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'","0")';
		}
		else{
			$sql = 'update uprnss_project_temp set
			district_id="'.$_POST['district'].'", 
			department_id="'.$_POST['department'].'", 
			sub_department_id="'.$_POST['sub_department_id'].'", 
			division_id="'.$_POST['division_name'].'", 
			unit_id="'.$_SESSION['username'].'", 
			project_name="'.$_POST['project_name'].'", 
			project_name_hindi="'.$_POST['project_name_hindi_unicode'].'", 
			project_type="'.$_POST['project_type'].'", 
			edited_by="'.$_SESSION['username'].'", 
			edition_time="'.date("Y-m-d H:i:s").'"		
			where sno="'.$_POST['edit_sno'].'"';
		}
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$msg .= '<p class="text text-success">Data Saved</p>';
			$_POST['edit_sno'] = '';
			$_POST['department'] = '';
			$_POST['sub_department_id'] = '';
			$_POST['district'] = '';
			$_POST['division_name'] = '';
			$_POST['project_name'] = '';
			$_POST['project_name_hindi'] = '';
			$_POST['project_name_hindi_unicode'] = '';
			$_POST['project_type'] = '';
		}
	}
}
else{
	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['department'] = '';
		$_POST['sub_department_id'] = '';
		$_POST['district'] = '';
		$_POST['division_name'] = '';
		$_POST['project_name'] = '';
		$_POST['project_name_hindi'] = '';
		$_POST['project_name_hindi_unicode'] = '';
		$_POST['project_type'] = '';
	}
}

if(isset($_GET['eid'])){
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['eid'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['department'] = $data['department_id'];
	$_POST['sub_department_id'] = $data['sub_department_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['division_name'] = $data['division_id'];
	$_POST['project_name'] = $data['project_name'];
	$_POST['project_type'] = $data['project_type'];
	$_POST['project_name_hindi'] = '';
	$_POST['project_name_hindi_unicode'] = $data['project_name_hindi'];
}
if(isset($_GET['delid'])){
	
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['delid'].'"';
	$trans_project = mysqli_fetch_assoc(execute_query($sql));
	
	
	$sql = 'update uprnss_project_temp set status="5" where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	
	$sql = 'update transaction_new_project set status="5" where sno="'.$trans_project['new_project_trans_id'].'"';
	execute_query($sql);
	
	
	$msg .= '<div class="alert alert-warning">Data Delete</div>';
}

if(isset($_GET['freeze_sno'])){
	
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['freeze_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	
	if($data['project_name_hindi']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Name</div>';
	}
	if($data['project_status_1']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Status 1</div>';
	}
	if($data['project_type']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Type </div>';
	}
	if($data['work_start_date']==''){
		$msg .= '<div class="alert alert-danger">Enter Work Start date </div>';
	}
	if($data['work_completion_date']==''){
		$msg .= '<div class="alert alert-danger">Enter Work Complition date </div>';
	}
	// if($data['technical_sanction_date']==''){
		// $msg .= '<div class="alert alert-danger">Enter Ts date </div>';
	// }
	if($data['technical_sanction_no']==''){
		$msg .= '<div class="alert alert-danger">Enter Ts Number </div>';
	}
	if($data['tender_status']==''){
		$msg .= '<div class="alert alert-danger">Enter Tedenr Status</div>';
	}
	if($data['financial_go_no']==''){
		$msg .= '<div class="alert alert-danger">Enter Financical Go</div>';
	}
	if($data['land_receive_date']==''){
		$msg .= '<div class="alert alert-danger">Enter Land Recevie Date</div>';
	}
	if($msg==''){
		$sql = 'update uprnss_project_temp set master_freeze="1" where sno="'.$_GET['freeze_sno'].'"';
		execute_query($sql);
		$msg .= '<div class="alert alert-warning">Data Freezed</div>';
	}
}

if(isset($_GET['del'])){
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['del'].'"';
	$trans_project = mysqli_fetch_assoc(execute_query($sql));
	
	$sql = 'delete from uprnss_project_temp where sno="'.$_GET['del'].'"';
	execute_query($sql);
	$sql = 'delete transaction_new_project where sno="'.$trans_project['new_project_trans_id'].'"';
	execute_query($sql);
	
	$msg .= '<p class="text text-danger">Data Deleted.</p>';
	
	
}

if(isset($_GET['act'])){
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['act'].'"';
	
	$product = mysqli_fetch_assoc(execute_query($sql));
	
	if($product['status']=='' or $product['status']=='0'){
		$sql = 'update uprnss_project_temp set status=1 where sno='.$_GET['act'];
		execute_query($sql);
	}
	else{
		$sql = 'update uprnss_project_temp set status=0 where sno='.$_GET['act'];
		execute_query($sql);
	}
	if(mysqli_error($db)){ 
		$msg .= '<div class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</div>';
	}
	else{
		$msg .= '<div class="alert alert-success">Data Saved</div>';
		$_POST['division_name'] = '';
		$_POST['edit_sno'] = '';
	}
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


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
        <div class="row no-print" >
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
					
						<?php echo $msg; 
						// $project_type = ($_SESSION['usertype'] == 1) ? 2 : 1;
						// echo $project_type;
						?>
                    <div class="card-body">
						<div class="row">
                    		<div class="col-md-5">
								<div class="form-group">
									<label >Head Project Name</label><br>
									<select class="form-control" name="head_project_name" id="head_project_name" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from invoice_new_project where status!='5' ";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['head_project_name'])){
												if($_POST['head_project_name']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}
										?>
									</select>
								</div>
                            </div>
						</div>
                    	<div class="row">
                    		<div class="col-md-3 ">
								<div class="form-group">
									<label >Deprtment</label><br>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name ";
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
								</div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >Sub-Deprtment</label>
                                    <select class="form-control" name="sub_department_id" id="sub_department_id" value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>" >
									</select>
                                </div>
                            </div>
							<div class="col-3">
                    			<label >Unit/Division Name</label>
                    			<select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
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
								</select>
							</div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >आच्छादित जनपद</label>
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
                                </div>
                            </div>
						    <div class="col-md-3">
                                <div class="form-group">
                                    <label >Project Name</label><br>
                                    <input type="text" name="project_name" id="project_name" class="form-control" placeholder="" value="<?php echo $_POST['project_name']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >Project Name in Hindi (KrutiDev)</label><br>
                                    <input type="text" name="project_name_hindi" id="project_name_hindi" class="form-control" placeholder="" value="<?php echo $_POST['project_name_hindi']; ?>" tabindex="<?php echo $tab++; ?>" onChange="krutiunicode()" onKeyDown="krutiunicode()" onKeyPress="krutiunicode()" onKeyUp="krutiunicode()">
                                </div>
                            </div>
                            <div class="col-md-3">
                            	<div class="form-group">
                                    <label >Project Name in Hindi (Unicode)</label><br>
                                    <input type="text" name="project_name_hindi_unicode" id="project_name_hindi_unicode" class="form-control" placeholder="" value="<?php echo $_POST['project_name_hindi_unicode']; ?>" tabindex="<?php echo $tab++; ?>">
                                    
                                    
									<!--<script language="javascript" type="text/javascript">CreateCustomHindiTextArea("languageTextareaNew","",30,5,true);</script>-->
                                </div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Type :</label>
									<select name="project_type" id="project_type" class="form-control" onchange="onchangetype()" >
										<option value="">--- Select ---</option>
										<option value="1"<?php echo ($_POST['project_type']==1?'selected':''); ?>>शासन स्तर</option>
										<option value="2"<?php echo ($_POST['project_type']==2?'selected':''); ?>>जिला स्तर </option>
									</select>
								</div>
							</div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<?php if($_SESSION['usertype']!='1'){ echo '<button type="submit" name="submit" class="btn btn-success">Submit</button>'; } ?>
									
									<button type="submit" name="search" class="btn btn-primary">Search</button>
									<input type="hidden" id="id" name="id" value="1">
									<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </form>
	
	
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
						<th>S.No.</th>
						<th>Erp-Project code</th>
						<th>Project Type</th>
						<th>Division Name</th>
						<th>District Name</th>
						<th>Department/Sub-Department Name</th>
						<th>Project Name</th>
						<th>Project Name Hindi</th>
						<th>Sanction Date &amp; Cost</th>
						<th>Work Start Date</th>
						<th>Work Completion Date</th>
						<th>Admin G.O. Details</th>
						<th>Financial G.O. Details</th>
						<th>Project Status</th>
						<th class="no-print text-center">&nbsp;</th>
						
						
						<?php if($_SESSION['usertype']=='sadmin'){ echo '<th class="no-print text-center">Civil Action</th>'; } ?>
						<?php if($_SESSION['usertype']=='sadmin'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						<?php if($_SESSION['usertype']=='sadmin'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						<?php if($_SESSION['usertype']=='sadmin'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						
						
						<?php if($_SESSION['usertype']=='2'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						<?php if($_SESSION['usertype']=='2'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						<?php if($_SESSION['usertype']=='2'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						
						<?php if($_SESSION['usertype']=='1'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						<?php if($_SESSION['usertype']=='1'){ echo '<th class="no-print text-center">&nbsp;</th>'; } ?>
						
						<th class="no-print text-center">Status</th>
						</tr>
						<tr>
							<?php
							for($i=1;$i<=18;$i++){
								echo '<th>'.$i.'</th>';	
							}
							if($_SESSION['usertype']=='sadmin'){ echo '<th class="no-print text-center">19</th>'; }
							if($_SESSION['usertype']=='sadmin'){ echo '<th class="no-print text-center">20</th>'; }
							?>
						</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
						if($_SESSION['usertype']=='sadmin'){
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, new_project_trans_id, uprnss_department_name.department_sort_name as dpsortname,uprnss_project_temp.creation_time as ctime, erp_code, division_name,sub_department_hindi, district_name_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, master_freeze,uprnss_project_temp.status, uprnss_project_temp.creation_time 
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
								where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and (uprnss_project_temp.status !="5" or uprnss_project_temp.status is null )';
								//print_r($_POST);
							
								if($_POST['department']!=''){
									$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
								}
								if(isset($_POST['sub_department_id'])){
									if($_POST['sub_department_id']!=''){
										$sql .= ' and sub_department_id="'.$_POST['sub_department_id'].'"';
									}
								}
								if($_POST['district']!=''){
									$sql .= ' and district_id="'.$_POST['district'].'"';
								}
								if($_POST['head_project_name']!=''){
									$sql .= ' and new_project_id="'.$_POST['head_project_name'].'"';
								}
								if($_POST['division_name']!=''){
									$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
								}
								if($_POST['project_type']!=''){
									$sql .= ' and project_type="'.$_POST['project_type'].'"';
								}
								$sql .= ' ORDER BY `uprnss_project_temp`.`sno` DESC';
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
									
									$dateString = $row['creation_time'];
									if ($dateString !== null) {
									$dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);
										if ($dateTime !== false) {
											$year = $dateTime->format("Y");
											
										} else {
											// echo 'Invalid date format';
										}
									}
									
									
									if(!isset($row['dpsortname'])){
										$row['dpsortname']="--";
									}
									if(!isset($row['ctime'])){
										$row['ctime']="--";
									}else{
										$row['ctime']=date("Y",strtotime($row['ctime']));
									}

									$serialnum=$row['dpsortname']."/".$row['ctime']."/".$row['sno'];
									
									
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row['erp_code'].'</td>
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">शासन स्तर</span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">जिला स्तर </span>';
										}
									}
									echo '
									</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</br><h6>'.$row['sub_department_hindi'].'</h6></td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>'; ?>
									<td>
										<?php
										
										echo  $row['sanction_cost'];echo '</br>';
										if (!empty($row['sanction_date'])) {
											echo date('d-m-Y', strtotime($row['sanction_date'])); 
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
										if (!empty($row['work_completion_date'])) {
											echo date('d-m-Y', strtotime($row['work_completion_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
									<td>
									  <?php
										echo  $row['admin_go_no'];echo '</br>';
										if (!empty($row['admin_go_date'])) {
											echo date('d-m-Y', strtotime($row['admin_go_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
									<td>
									  <?php
										echo  $row['financial_go_no'];echo '</br>';
										if (!empty($row['financial_go_date'])) {
											echo date('d-m-Y', strtotime($row['financial_go_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
								<?php echo'
									
									<td>'.$status1['status_1'].'</td>
									<td class="no-print text-center">
										<a href="master_project_temp.php?eid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Transfer Department"></span></a>
									</td>
									<td class="no-print text-center">';
									if($row['new_project_trans_id']!=''){
										if($row['status']=="2"){
											echo'<a href="new_project_edit.php?eid='.$row['new_project_trans_id'].'" onClick="return confirm(\'Are you sure you?\');" target="_blank" class="btn btn-success" >Change And verfy</a>';
										}else{
											echo'<div class="alert alert-" style="background-color:red">Approved!</div>';
										}
									}
									echo'</td>
									<td><a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td class="no-print text-center">';
									if($row['master_freeze']=='0'){
										echo '<a target="_blank" href="edit_master_project_temp.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Master Data" ></span></a><br/><br/>
										<a href="master_project_temp.php?freeze_sno='.$row['sno'].'" class="text text-danger" onClick="return confirm(\'Are you sure want to Freeze Data?\');">Freeze Data</a>';
									}
									else{
										echo '<span class="text text-warning">Data Freezed</span>';
									}
									echo '</td>
									
									<td class="no-print text-center">
										<a href="master_project_temp.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
									</td>
									
									<td class="no-print text-center">';
									if($row['status']=='1'){
										echo'<a  href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');" class="btn btn-danger">Inactivate</a>';
									}
									else{
										echo'<a href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');" class="btn btn-success">Active</a>';
									}
									echo'</td>
									
									</tr>';
								}
							}
						}				
						elseif ($_SESSION['usertype']=='2'){
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, new_project_trans_id, division_name,sub_department_id, district_name_hindi,sub_department_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, uprnss_project_temp.status, master_freeze, uprnss_project_temp.creation_time,uprnss_department_name.department_sort_name as dpsortname,uprnss_project_temp.creation_time as ctime 
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
								left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
								where uprnss_project_temp.department_id in ('.implode(",", $_SESSION['department']).') and (uprnss_project_temp.status !="5" or uprnss_project_temp.status is null)';
									//print_r($_POST); 
								// echo $sql;
							
								if($_POST['department']!=''){
									$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
								}
								if($_POST['sub_department_id']!=''){
									$sql .= ' and sub_department_id="'.$_POST['sub_department_id'].'"';
								}
								if($_POST['district']!=''){
									$sql .= ' and district_id="'.$_POST['district'].'"';
								}
								if($_POST['division_name']!=''){
									$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
								}
								if($_POST['project_type']!=''){
									$sql .= ' and project_type="'.$_POST['project_type'].'"';
								}
								if($_POST['head_project_name']!=''){
									$sql .= ' and new_project_id="'.$_POST['head_project_name'].'"';
								}
								$sql .= ' ORDER BY `uprnss_project_temp`.`sno` DESC';
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
									
									if(!isset($row['dpsortname'])){
										$row['dpsortname']="--";
									}
									if(!isset($row['ctime'])){
										$row['ctime']="--";
									}else{
										$row['ctime']=date("Y",strtotime($row['ctime']));
									}

									$serialnum=$row['dpsortname']."/".$row['ctime']."/".$row['sno'];

									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$serialnum.'</td>
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">शासन स्तर</span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">जिला स्तर </span>';
										}
									}
									echo '
									</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</br><h6>'.$row['sub_department_hindi'].'</h6></td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>'; ?>
									<td>
										<?php
										
										echo  $row['sanction_cost'];echo '</br>';
										if (!empty($row['sanction_date'])) {
											echo date('d-m-Y', strtotime($row['sanction_date'])); 
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
										if (!empty($row['work_completion_date'])) {
											echo date('d-m-Y', strtotime($row['work_completion_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
									<td>
									  <?php
										echo  $row['admin_go_no'];echo '</br>';
										if (!empty($row['admin_go_date'])) {
											echo date('d-m-Y', strtotime($row['admin_go_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
									<td>
									  <?php
										echo  $row['financial_go_no'];echo '</br>';
										if (!empty($row['financial_go_date'])) {
											echo date('d-m-Y', strtotime($row['financial_go_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
								<?php echo'
									
									<td>'.$status1['status_1'].'</td>
									
									<td>
									<a href="master_project_temp.php?eid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Transfer Department"></span></a>
									<!--<a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Master Data" >Edit</span></a>-->
									</td>
									<td class="no-print text-center">';
									//if($row['master_freeze']=='0'){
										echo '<a target="_blank" href="edit_master_project_temp.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Master Data" >Fill Data</span></a><br/><br/>
										<!--<a href="master_project_temp.php?freeze_sno='.$row['sno'].'" class="text text-danger" onClick="return confirm(\'Are you sure want to Freeze Data?\');">Freeze Data</a>-->
										';
									//
									echo '</td>
									
									<td class="no-print text-center">
										<a href="master_project_temp.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
									</td>
									<td class="no-print text-center">';
									if($row['new_project_trans_id']!=''){
										if($row['status']=="2"){
											echo'<a href="new_project_edit.php?eid='.$row['new_project_trans_id'].'" onClick="return confirm(\'Are you sure you?\');" target="_blank" class="btn btn-success" >Change And verfy</a>';
										}else{
											echo'<div class="alert alert-" style="background-color:red">Approved!</div>';
										}
									}
									echo'</td>
									<td class="no-print text-center">';
									if($row['status']=='1'){
										echo'<a  href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');" class="btn btn-danger">Inactivate</a>';
									}
									else{
										echo'<a href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');" class="btn btn-success">Active</a>';
									}
									echo'</td>
									</tr>';
								}	
							}
						}
						elseif ($_SESSION['usertype']=='6'){
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name,sub_department_id, district_name_hindi,sub_department_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, uprnss_project_temp.status, master_freeze, uprnss_project_temp.creation_time, uprnss_department_name.department_sort_name as dpsortname,uprnss_project_temp.creation_time as ctime 
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = division_id
								left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
								left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
								where division_id in ('.implode(",", $_SESSION['divisions']).') and (uprnss_project_temp.status !="5" or uprnss_project_temp.status is null)';
									//print_r($_POST); 
								// echo $sql;
							
								if($_POST['department']!=''){
									$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
								}
								if($_POST['sub_department_id']!=''){
									$sql .= ' and sub_department_id="'.$_POST['sub_department_id'].'"';
								}
								if($_POST['district']!=''){
									$sql .= ' and district_id="'.$_POST['district'].'"';
								}
								if($_POST['division_name']!=''){
									$sql .= ' and division_id="'.$_POST['division_name'].'"';
								}
								if($_POST['project_type']!=''){
									$sql .= ' and project_type="'.$_POST['project_type'].'"';
								}
								if($_POST['head_project_name']!=''){
									$sql .= ' and new_project_id="'.$_POST['head_project_name'].'"';
								}
								$sql .= ' ORDER BY `uprnss_project_temp`.`sno` DESC';
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
									
									if(!isset($row['dpsortname'])){
										$row['dpsortname']="--";
									}
									if(!isset($row['ctime'])){
										$row['ctime']="--";
									}else{
										$row['ctime']=date("Y",strtotime($row['ctime']));
									}

									$serialnum=$row['dpsortname']."/".$row['ctime']."/".$row['sno'];

									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$serialnum.'</td>
									
									
									
									
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">शासन स्तर</span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">जिला स्तर </span>';
										}
									}
									echo '
									</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</br><h6>'.$row['sub_department_hindi'].'</h6></td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>'; ?>
									<td>
										<?php
										
										echo  $row['sanction_cost'];echo '</br>';
										if (!empty($row['sanction_date'])) {
											echo date('d-m-Y', strtotime($row['sanction_date'])); 
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
										if (!empty($row['work_completion_date'])) {
											echo date('d-m-Y', strtotime($row['work_completion_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
									<td>
									  <?php
										echo  $row['admin_go_no'];echo '</br>';
										if (!empty($row['admin_go_date'])) {
											echo date('d-m-Y', strtotime($row['admin_go_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
									<td>
									  <?php
										echo  $row['financial_go_no'];echo '</br>';
										if (!empty($row['financial_go_date'])) {
											echo date('d-m-Y', strtotime($row['financial_go_date'])); 
										} else {
											echo '';
											}
										?>
									</td>
								<?php echo'
									
									<td>'.$status1['status_1'].'</td>
									<td class="no-print text-center">
										<a href="master_project_temp.php?eid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Transfer Department"></span></a>
									</td>
									<td><a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td class="no-print text-center">';
									if($row['master_freeze']=='0'){
										echo '<a target="_blank" href="edit_master_project_temp.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Master Data" ></span></a><br/><br/>
										<a href="master_project_temp.php?freeze_sno='.$row['sno'].'" class="text text-danger" onClick="return confirm(\'Are you sure want to Freeze Data?\');">Freeze Data</a>';
									}
									else{
										echo '<span class="text text-warning">Data Freezed</span>';
									}
									echo '</td>
									
									<td class="no-print text-center">
										<a href="master_project_temp.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
									</td>
									
									<td class="no-print text-center">';
									if($row['status']=='1'){
										echo'<a  href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');" class="btn btn-danger">Inactivate</a>';
									}
									else{
										echo'<a href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you ?\');" class="btn btn-success">Active</a>';
									}
									echo'</td>
									</tr>';
								}	
							}
						}
						else{
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name, master_freeze, district_name_hindi,sub_department_hindi, department_name_hindi, project_name,project_type, project_name_hindi, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, uprnss_project_temp.status, uprnss_project_temp.creation_time, uprnss_department_name.department_sort_name as dpsortname,uprnss_project_temp.creation_time as ctime 
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
								where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and (uprnss_project_temp.status="0" or uprnss_project_temp.status is null or uprnss_project_temp.status="1")';
								//print_r($_POST); 
							
								if($_POST['department']!=''){
									$sql .= ' and uprnss_project_temp.department_id="'.$_POST['department'].'"';
								}
								if($_POST['sub_department_id']!=''){
									$sql .= ' and sub_department_id="'.$_POST['sub_department_id'].'"';
								}
								if($_POST['district']!=''){
									$sql .= ' and district_id="'.$_POST['district'].'"';
								}
								if($_POST['division_name']!=''){
									$sql .= ' and uprnss_project_temp.division_id="'.$_POST['division_name'].'"';
								}
								if($_POST['project_type']!=''){
									$sql .= ' and project_type="'.$_POST['project_type'].'"';
								}
								if($_POST['head_project_name']!=''){
									$sql .= ' and new_project_id="'.$_POST['head_project_name'].'"';
								}
								$sql .= ' ORDER BY `uprnss_project_temp`.`sno` DESC';
								//echo $sql;
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
									
									if(!isset($row['dpsortname'])){
										$row['dpsortname']="--";
									}
									if(!isset($row['ctime'])){
										$row['ctime']="--";
									}else{
										$row['ctime']=date("Y",strtotime($row['ctime']));
									}

									$serialnum=$row['dpsortname']."/".$row['ctime']."/".$row['sno'];

									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$serialnum.'</td>
									
									
									
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">शासन स्तर </span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">जिला स्तर </span>';
										}
									}
									echo '
									</td>';
									
									$start_date = $row['work_start_date']==''?'':date('d-m-Y', strtotime($row['work_start_date']));
									$completion_date = $row['work_completion_date']==''?'':date('d-m-Y', strtotime($row['work_completion_date']));$sanction_date = $row['sanction_date']==''?'':date('d-m-Y', strtotime($row['sanction_date']));
									$admin_go_date = $row['admin_go_date']==''?'':date('d-m-Y', strtotime($row['admin_go_date']));
									$financial_go_date = $row['financial_go_date']==''?'':date('d-m-Y', strtotime($row['financial_go_date']));
									
									
									echo'
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</br><h6>'.$row['sub_department_hindi'].'</h6></td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>
									<td>'.$sanction_date.'</br>'.$row['sanction_cost'].'</td>
									<td>'.$start_date .'</td>
									<td>'.$completion_date .'</td>
									<td>' . $row['admin_go_no'] . '</br>'.$admin_go_date .'</td>
									<td>' . $row['financial_go_no'] . '</br>'.$financial_go_date .'</td>
									<td>'.$status1['status_1'].'</td>';
									//<td class="no-print text-center">
									// if($row['master_freeze']=='0'){
										// echo '<a target="_blank" href="edit_master_project_temp.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Master Data" >Data Feed</span></a><br/><br/>
										// <a href="master_project_temp.php?freeze_sno='.$row['sno'].'" class="text text-danger" onClick="return confirm(\'Are you sure want to Freeze Data?\');">Freeze Data</a>';
									// }
									// else{
										// echo '<span class="text text-warning">Data Freezed</span>';
									// }
									// echo '</td>
									 echo '
									 <td><a target="_blank" href="edit_master_project_temp.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Master Data" >Data Feed</span></a></td>
									 
									 
									 <td><a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td class="no-print text-center">
										<a href="master_project_temp.php?eid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Transfer Department">project Edit</span></a>
									</td>
									<td class="no-print text-center">';
									if($row['status']=='1'){
										echo'<a  href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you want to Active your project? ?\');" class="btn btn-danger">Inactivate</a>';
									}
									else{
										echo'<a href="master_project_temp.php?act='.$row['sno'].'" onclick="return confirm(\'Are you sure you want to Inactive your project?\');" class="btn btn-success">Active</a>';
									}
									echo'</td>

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
    $('#general_stat_table').DataTable({
			// paging: false,
			fixedHeader: true,
			// colReorder: true,
			// scrollX: true, // Enable horizontal scrolling if needed
	});
	
    
});

var actionUrl = 'scripts/ajax.php';
function fill_sub_department(val, selected=''){
	var data = {"term":"b", "id":"sub_dep", "val":val};

	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(ajaxdata){
			console.log(ajaxdata);
			var txt = '<option value="">--Select--</option>';
			ajaxdata = JSON.parse(ajaxdata);
			$.each(ajaxdata, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.sub_department_hindi+'</option>';
				
			});
          	$("#sub_department_id").html(txt);
        }
    });
}

<?php
	if(isset($_GET['eid'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>  );
		
	});
	
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>