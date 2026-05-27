<?php
include("scripts/settings.php");
include("scripts/setting_dbase_emb.php");
 
$msg='';
$tab=1;


if(isset($_POST['submit'])){

	if($_POST['cid_sno']!=''){
		
		$sql = 'update projects set 
		erp_code="'.mysqli_real_escape_string($db_emb, $_POST['erp_code']).'"		
		where id="'.(int)$_POST['cid_sno'].'"';
	
		$result = mysqli_query($db_emb, $sql);
		if(mysqli_error($db_emb)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db_emb).'>> '.$sql.'</p>';
		}
		else{
			// Update in uprnss_project_temp (on $db)
			$safe_erp = mysqli_real_escape_string($db, $_POST['erp_code']);
			$safe_cid = (int)$_POST['cid_sno'];
			$up_temp = "UPDATE `uprnss_project_temp` SET `emb_project_id` = '$safe_cid' WHERE TRIM(erp_code) = TRIM('$safe_erp')";
			mysqli_query($db, $up_temp);

			$msg .= '<div class="alert alert-success">ERP CODE & EMB PROJECT ID UPDATED SUCCESSFULLY!</div>';
			$_POST['cid_sno'] = '';
			$_POST['department'] = '';
			$_POST['sub_department_id'] = '';
			$_POST['district'] = '';
			$_POST['division_name'] = '';
			$_POST['project_name'] = '';
			$_POST['project_name_hindi'] = '';
			$_POST['project_name_hindi_unicode'] = '';
			$_POST['erp_code'] = '';
		}
	}
}
if(isset($_GET['rid'])){
		
 	$sql = 'update uprnss_project_temp set 
		reporting_status="0", 
		closing_remark="", 
		closing_date=""	
		where sno="'.$_GET['rid'].'"';
	
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
				$sql = 'select * from uprnss_project_temp where sno="'.$_GET['rid'].'"';
				$data = mysqli_fetch_assoc(execute_query($sql));
			$msg .= '<div class="alert alert-danger">'.$data['project_name_hindi'].'&nbsp; Project Start</div>';	
			
			$_POST['edit_sno'] = '';
			$_POST['department'] = '';
			$_POST['sub_department_id'] = '';
			$_POST['unit'] = '';
			$_POST['zone'] = '';
			$_POST['project_name'] = '';
			$_POST['project_name_hindi'] = '';
			$_POST['project_name_hindi_unicode'] = '';
			$_POST['project_type'] = '';
		}
	}


if(!isset($_POST['search'])){
	$_POST['edit_sno'] = '';
	$_POST['department'] = '';
	$_POST['unit'] = '';
	$_POST['zone'] = '';
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
	$_POST['erp_code'] = '';
	$_POST['closing_remark'] = '';
}

if(isset($_GET['cid'])){
	$page=2;
}else{
	$page=1;
}
if(isset($_GET['view'])){
	$page=3;
}

if(isset($_GET['cid'])){
	
		$sql = 'SELECT 
			projects.id AS project_id,
			master_projects.id AS master_project_id,
			parent_projects.id AS parent_project_id,
			parent_project_zone_units.id AS zone_unit_id,
			
			parent_project_zone_units.zone_master_id as zone_id, 
			zone_masters.zone_code,
			
			parent_project_zone_units.zone_unit_id as unit_id,
			zonal_units.name,
			
			projects.client_id,
			projects.erp_code,
			projects.project_name,
			projects.parent_project_id,
			projects.project_cost,
			projects.project_estimated_cost,
			projects.agreed_project_cost,
			projects.project_description,
			projects.project_requirement,
			projects.job_code,
			projects.work_type,
			projects.project_type,
			projects.budget_approved_date,
			projects.financial_sanction_date,
			projects.government_order_date,
			projects.budget_head_id,
			projects.start_date,
			projects.end_date,
			projects.total_cost,
			projects.status,
			projects.government_order,
			parent_projects.id AS parent_project_id,
			projects.parent_project_id,
			parent_projects.master_project_id
			
		FROM 
			projects
		LEFT JOIN 
			parent_projects ON projects.parent_project_id = parent_projects.id
		LEFT JOIN 
			master_projects ON parent_projects.master_project_id = master_projects.id
		LEFT JOIN 
			parent_project_zone_units ON  parent_projects.id = parent_project_zone_units.parent_project_id
		LEFT JOIN 
			zone_masters ON parent_project_zone_units.zone_master_id = zone_masters.id
		LEFT JOIN 
			zonal_units ON parent_project_zone_units.zone_unit_id = zonal_units.id 
		where projects.id ="'.$_GET['cid'].'"';
			
		$run = mysqli_query($db_emb,$sql);
		$data = mysqli_fetch_array($run);
		
		$_POST['cid_sno'] = $data['project_id'];
		
		$_POST['department'] = $data['client_id'];
		$_POST['project_name'] = $data['project_name'];
		
		$_POST['zone'] = $data['zone_id'];
		$_POST['unit'] = $data['unit_id'];
		$_POST['erp_code'] = $data['erp_code'];

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

	<?php
	//running project page
	
		switch ($page) {
			case "1":	
	?>
       
	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">
			<?php echo $msg?>
        	<div class="row d-flex my-auto">  
						<div class="col-md-12 text-right" ><a href="emb_project.php?view=view" style="color: #ffffff"><button type="button" name="student_ledger" class="btn btn-danger" >Click Here for ERP Code field Project List</button></a></div>
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th>Zone</th>
							<th width="18%">
								<select class="form-control" name="zone" id="zone" tabindex="<?php echo $tab++; ?>">
                                    <option value="">--- Select ---</option>
										<?php
										$query = "select * from zone_masters";
										$run = mysqli_query($db_emb, $query);
										if (mysqli_error($db_emb)) {
											die("Query failed: " . mysqli_error($db_emb));
										}
										while($data = mysqli_fetch_assoc($run)){
											echo '<option value="'.$data['id'].'" ';
											if(isset($_POST['zone'])){
												if($_POST['zone']==$data['id']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.$data['zone_code'].'</option>';
										}
										?>
								</select>
							</th>
							<th>Zonal Unit </th>
							<th width="18%"><select class="form-control" name="unit" id="unit" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from zonal_units order by name ASC';
									$run = mysqli_query($db_emb,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['id'].'" ';
										if(isset($_POST['unit'])){
											if($_POST['unit']==$data['id']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['name'].'</option>';
									}
									?>
								</select>
							</th>
							<th>Client Name</th>							
							<th width="18%">
								<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from clients";
									$run = mysqli_query($db_emb,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['id'].'" ';
										if(isset($_POST['department'])){
											if($_POST['department']==$data['id']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['name']).'</option>';
									}
									?>
								</select>
							</th>
						</tr>
					</table>
					<div class="col-md-12 text-center">
						<button type="submit" name="search" class="btn btn-primary">Search</button>
						<input type="hidden" id="id" name="id" value="1">
						<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
					</div>
			</div>
		</div>
		</form>
	</div>
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-danger text-white mt-2">
                        <h4 class="card-title text-center text-white ">ERP code Not Field Project List</h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="">
						<thead style="position:sticky;top:0; z-index:2;">
							<tr>
								<th rowspan="2">S.No.</th>
								<th rowspan="2">Zone</th>
								<th rowspan="2">Zonal Unit</th>
								<th rowspan="2">Client Name</th>
								<th rowspan="2">Project Name</th>
								<th rowspan="2">Project cost</th>
								<th rowspan="2">G.O. Number</th>
								<th rowspan="2">G.O. Date</th>
								<th rowspan="2">Action</th>
							</tr>
							
							<tr>
								 <?php
								// for($i=1;$i<=10;$i++){
									// echo '<th>'.$i.'</th>';
								// }
								 ?>
							</tr>
						</thead>
						 
						<tbody>
						<?php
							if(isset($_POST['search'])){	
								
								
								$sql = "SELECT 
										projects.id AS project_id,
										MAX(master_projects.id) AS master_project_id,
										MAX(parent_projects.id) AS parent_project_id,
										MAX(parent_project_zone_units.id) AS zone_unit_id,
										
										MAX(parent_project_zone_units.zone_master_id) as zone_id, 
										GROUP_CONCAT(DISTINCT zone_masters.zone_code SEPARATOR ', ') AS zone_code,
										
										MAX(parent_project_zone_units.zone_unit_id) as unit_id,
										GROUP_CONCAT(DISTINCT zonal_units.name SEPARATOR ', ') AS name,
										MAX(clients.name) AS client_name,
										
										projects.client_id,
										projects.erp_code,
										projects.project_name,
										projects.project_cost,
										projects.project_estimated_cost,
										projects.agreed_project_cost,
										projects.project_description,
										projects.project_requirement,
										projects.job_code,
										projects.work_type,
										projects.project_type,
										projects.budget_approved_date,
										projects.financial_sanction_date,
										projects.government_order_date,
										projects.budget_head_id,
										projects.start_date,
										projects.end_date,
										projects.total_cost,
										projects.status,
										projects.government_order
										
									FROM 
										projects
									LEFT JOIN 
										parent_projects ON projects.parent_project_id = parent_projects.id
									LEFT JOIN 
										master_projects ON parent_projects.master_project_id = master_projects.id
									LEFT JOIN 
										parent_project_zone_units ON  parent_projects.id = parent_project_zone_units.parent_project_id
									LEFT JOIN 
										zone_masters ON parent_project_zone_units.zone_master_id = zone_masters.id
									LEFT JOIN 
										zonal_units ON parent_project_zone_units.zone_unit_id = zonal_units.id
									LEFT JOIN
										clients ON projects.client_id = clients.id
								
								where (erp_code IS NULL OR erp_code = '') ";
								
								if ($_POST['department'] != '') {
									$sql .= ' and client_id="' . $_POST['department'] . '"';
								}
								if ($_POST['zone'] != '') {
									$sql .= ' and parent_project_zone_units.zone_master_id ="' . $_POST['zone'] . '"';
								}
								if ($_POST['unit'] != '') {
									$sql .= ' and parent_project_zone_units.zone_unit_id="' . $_POST['unit'] . '"';
								}
								$sql .= 'GROUP BY projects.id order by projects.id DESC ';
								$query = mysqli_query($db_emb, $sql);
								if (!$query) {
									die("Query Error case 1: " . mysqli_error($db_emb) . "<br>Query: " . $sql);
								}
								$i=1;
								while ($row = mysqli_fetch_array($query , MYSQLI_ASSOC)){
										echo '<tr>
										<td>'.$i++.'</td>
									
										<td>'.$row['zone_code'].'</td>
										<td>'.$row['name'].'</td>
										
										<td>'.$row['client_name'].'</td>
										<td>'.$row['project_name'].'</td>
										<td>'.$row['project_cost'].'</td>
										<td>'.$row['government_order'].'</td>
										<td>'.$row['government_order_date'].'</td>
										
										<td>';
											echo '<a href="emb_project.php?cid='.$row['project_id'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Fill ERP Code"></span></a>';
										
											echo'</td>
										</tr>
										';
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
	// close project form
			break;
		case "2":
	?>

	<div id="container" class="no-print">
	   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
        <div class="row no-print" >
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title text-center text-white  bg-danger text-white mt-2" style="font-size:3.125rem !important">Fill Erp Code here </h5></br>
                    </div>
					
						<?php echo $msg; 
						// $project_type = ($_SESSION['usertype'] == 1) ? 2 : 1;
						// echo $project_type;
						?>
                    <div class="card-body">
                    	<div class="row">
                    		<div class="col-md-3 ">
								<div class="form-group">
									<label style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Client Name</label><br>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" readonly>
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from clients";
									$run = mysqli_query($db_emb,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['id'].'" ';
										if(isset($_POST['department'])){
											if($_POST['department']==$data['id']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['name']).'</option>';
									}
									?>
								</select>
								</div>
                            </div>
							<div class="col-3">
                    			<label style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Zone</label>
                    			<select class="form-control" name="zone" id="zone" tabindex="<?php echo $tab++; ?>" readonly>
                                    <option value="">--- Select ---</option>
										<?php
										$query = "select * from zone_masters";
										$run = mysqli_query($db_emb,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['id'].'" ';
											if(isset($_POST['zone'])){
												if($_POST['zone']==$data['id']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.$data['zone_code'].'</option>';
										}
										?>
								</select>
							</div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Zonal Unit</label>
                                    <select class="form-control" name="unit" id="unit" tabindex="<?php echo $tab++; ?>" readonly>
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from zonal_units order by name ASC';
									$run = mysqli_query($db_emb,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['id'].'" ';
										if(isset($_POST['unit'])){
											if($_POST['unit']==$data['id']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['name'].'</option>';
									}
									?>
								</select>
                                </div>
                            </div>
						    <div class="col-md-3">
                                <div class="form-group">
                                    <label style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">Project Name</label><br>
									<textarea type="text" name="project_name" id="project_name" class="form-control" placeholder=""><?php echo $_POST['project_name']; ?> </textarea>
                                    
                                </div>
                            </div>
							
							<div class="col-md-4" >
                                <div class="form-group">
                                    <label style="color: #2a6496; font-weight: bold; font-size: 13px; background-color: #f5f8fa; padding: 3px 6px; border-radius: 4px;">ERP Code </label><br>
                                    <input type="text" name="erp_code" id="erp_code" class="form-control" placeholder="" value="<?php echo $_POST['erp_code']; ?>" tabindex="<?php echo $tab++; ?>" required>
                                </div>
                            </div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">UPDATE ERP CODE</button>
									
									<input type="hidden" id="cid_sno" name="cid_sno" value="<?php echo $_POST['cid_sno']; ?>">
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </form>
	</div>
	<?php
	// close project list page
			break;
		case "3":
	?>
	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="emb_project.php?view=view" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">  
						<div class="col-md-12 text-right" ><a href="emb_project.php" style="color: #ffffff"><button type="button" name="student_ledger" class="btn btn-danger" >Click Here for ERP Code Not field Project List</button></a></div>
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th>Zone</th>
							<th width="18%">
								<select class="form-control" name="zone" id="zone" tabindex="<?php echo $tab++; ?>">
                                    <option value="">--- Select ---</option>
										<?php
										$query = "select * from zone_masters";
										$run = mysqli_query($db_emb,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['id'].'" ';
											if(isset($_POST['zone'])){
												if($_POST['zone']==$data['id']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.$data['zone_code'].'</option>';
										}
										?>
								</select>
							</th>
							<th>Zonal Unit </th>
							<th width="18%"><select class="form-control" name="unit" id="unit" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from zonal_units order by name ASC';
									$run = mysqli_query($db_emb,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['id'].'" ';
										if(isset($_POST['unit'])){
											if($_POST['unit']==$data['id']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.$data['name'].'</option>';
									}
									?>
								</select>
							</th>
							<th>Client Name</th>							
							<th width="18%">
								<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from clients";
									$run = mysqli_query($db_emb,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['id'].'" ';
										if(isset($_POST['department'])){
											if($_POST['department']==$data['id']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['name']).'</option>';
									}
									?>
								</select>
							</th>
						</tr>
					</table>
					<div class="col-md-12 text-center">
						<button type="submit" name="search" class="btn btn-primary">Search</button>
					</div>
			</div>
		</div>
		</form>
	</div>
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-danger text-white mt-2">
                        <h5 class="card-title text-center text-white">ERP Code field Project List</h5></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="">
						<thead style="position:sticky;top:0; z-index:2;">
							
							<tr>
								<th rowspan="2">S.No.</th>
								<th colspan="8">EMB S/W Details</th>
								<th colspan="4">ERP S/W Details</th>
								<th rowspan="2">Action</th>
							</tr>
							<tr>
								<th rowspan="1">Client Name</th>
								<th rowspan="1">Zone</th>
								<th rowspan="1">Zonal Unit</th>
								<th rowspan="1">Project Name</th>
								<th rowspan="1">Project Cost</th>
								<th rowspan="1">G.O. Number</th>
								<th rowspan="1">G.O. Date</th>
								<th rowspan="1">ERP Code</th>
								<th rowspan="1">Division name</th>
								<th rowspan="1">District Name</th>
								<th rowspan="1">Department Name</th>
								<th rowspan="1">Project Name</th>
								
							</tr>
							
							<tr>
								 <?php
								// for($i=1;$i<=10;$i++){
									// echo '<th>'.$i.'</th>';
								// }
								 ?>
							</tr>
						</thead>
						 
						<tbody>
						<?php
							if(isset($_POST['search'])){	
								
								$sql = "SELECT 
										projects.id AS project_id,
										MAX(master_projects.id) AS master_project_id,
										MAX(parent_projects.id) AS parent_project_id,
										MAX(parent_project_zone_units.id) AS zone_unit_id,
										
										MAX(parent_project_zone_units.zone_master_id) as zone_id, 
										GROUP_CONCAT(DISTINCT zone_masters.zone_code SEPARATOR ', ') AS zone_code,
										
										MAX(parent_project_zone_units.zone_unit_id) as unit_id,
										GROUP_CONCAT(DISTINCT zonal_units.name SEPARATOR ', ') AS name,
										MAX(clients.name) AS client_name,
										
										projects.client_id,
										projects.erp_code,
										projects.project_name,
										projects.project_cost,
										projects.project_estimated_cost,
										projects.agreed_project_cost,
										projects.project_description,
										projects.project_requirement,
										projects.job_code,
										projects.work_type,
										projects.project_type,
										projects.budget_approved_date,
										projects.financial_sanction_date,
										projects.government_order_date,
										projects.budget_head_id,
										projects.start_date,
										projects.end_date,
										projects.total_cost,
										projects.status,
										projects.government_order
										
									FROM 
										projects
									LEFT JOIN 
										parent_projects ON projects.parent_project_id = parent_projects.id
									LEFT JOIN 
										master_projects ON parent_projects.master_project_id = master_projects.id
									LEFT JOIN 
										parent_project_zone_units ON  parent_projects.id = parent_project_zone_units.parent_project_id
									LEFT JOIN 
										zone_masters ON parent_project_zone_units.zone_master_id = zone_masters.id
									LEFT JOIN 
										zonal_units ON parent_project_zone_units.zone_unit_id = zonal_units.id 
									LEFT JOIN
										clients ON projects.client_id = clients.id
									where (erp_code IS not NULL OR erp_code != '')";	
								
								if ($_POST['department'] != '') {
									$sql .= ' and client_id="' . $_POST['department'] . '"';
								}
								if ($_POST['zone'] != '') {
									$sql .= ' and parent_project_zone_units.zone_master_id ="' . $_POST['zone'] . '"';
								}
								if ($_POST['unit'] != '') {
									$sql .= ' and parent_project_zone_units.zone_unit_id="' . $_POST['unit'] . '"';
								}
								$sql .= 'GROUP BY projects.id order by projects.id DESC';
								$query = mysqli_query($db_emb, $sql);
								if (!$query) {
									die("Query Error case 3: " . mysqli_error($db_emb) . "<br>Query: " . $sql);
								}
								
								$projects = [];
								$erp_codes = [];
								while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
									$projects[] = $row;
									if ($row['erp_code'] != '') {
										$erp_codes[] = $row['erp_code'];
									}
								}
								
								$erp_details_map = [];
								if (!empty($erp_codes)) {
									$escaped_codes = array_map(function($code) use ($db) {
										return "'" . mysqli_real_escape_string($db, $code) . "'";
									}, $erp_codes);
									
									$sql_erp = 'select uprnss_project_temp.sno as sno, uprnss_project_temp.erp_code as erp_code, new_project_trans_id, uprnss_department_name.department_sort_name as dpsortname,uprnss_project_temp.creation_time as ctime,  division_name,sub_department_hindi, district_name_hindi, department_name_hindi, project_name, project_name_hindi, project_type, sanction_date, sanction_cost, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date, project_status_1, master_freeze,uprnss_project_temp.status, uprnss_project_temp.creation_time 
									from uprnss_project_temp 
									left join uprnss_district on uprnss_district.sno = district_id
									left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
									left join uprnss_department_name on uprnss_department_name.sno = department_id
									left join uprnss_sub_department on uprnss_sub_department.sno = sub_department_id 
									where uprnss_project_temp.erp_code IN (' . implode(',', $escaped_codes) . ')';
									
									$result = execute_query($sql_erp);
									if ($result) {
										while ($erp_row = mysqli_fetch_assoc($result)) {
											$erp_details_map[$erp_row['erp_code']] = $erp_row;
										}
									}
								}
								
								$i=1;
								foreach ($projects as $row) {
									$row_erp_details = isset($erp_details_map[$row['erp_code']]) ? $erp_details_map[$row['erp_code']] : null;
									if (!$row_erp_details) {
										$row_erp_details = [
											'division_name' => '',
											'district_name_hindi' => '',
											'department_name_hindi' => '',
											'sub_department_hindi' => '',
											'project_name_hindi' => '',
										];
									}
									
										echo '<tr>
										<td>'.$i++.'</td>
									
										<td>'.$row['client_name'].'</td>
									
										<td>'.$row['zone_code'].'</td>
										<td>'.$row['name'].'</td>
										<td>'.$row['project_name'].'</td>
										<td>'.$row['project_cost'].'</td>
										<td>'.$row['government_order'].'</td>
										<td>'.$row['government_order_date'].'</td>
										<td>'.$row['erp_code'].'</td>
										<td>'.$row_erp_details['division_name'].'</td>
										<td>'.$row_erp_details['district_name_hindi'].'</td>
										<td>'.$row_erp_details['department_name_hindi'].'</br><h6>'.$row_erp_details['sub_department_hindi'].'</h6></td>
										<td>'.$row_erp_details['project_name_hindi'].'</td>
										
										<td>';
											echo '<a href="emb_project.php?cid='.$row['project_id'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit ERP Code"></span></a>';
										
											echo'</td>
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
	
	<?php		
	
			break;
		
	}

	?>	
	
	
	
	
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