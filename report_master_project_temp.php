<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

if(isset($_POST['submit'])){
	if($_POST['project_name']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Name</div>';
	}
	if($_POST['project_name_hindi_unicode']==''){
		$msg .= '<div class="alert alert-danger">Enter Project Name in Hindi</div>';
	}
	if($msg == ''){
		if($_POST['edit_sno']==''){
			$sql = 'insert into uprnss_project_temp (district_id, department_id, division_id, unit_id, project_name, project_name_hindi, master_freeze, created_by, creation_time) values ("'.$_POST['district'].'", "'.$_POST['department'].'", "'.$_POST['division_name'].'", "'.$_SESSION['usersno'].'", "'.$_POST['project_name'].'", "'.$_POST['project_name_hindi_unicode'].'","0", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
		}
		else{
			$sql = 'update uprnss_project_temp set
			district_id="'.$_POST['district'].'", 
			department_id="'.$_POST['department'].'", 
			division_id="'.$_POST['division_name'].'", 
			project_type="'.$_POST['project_type'].'", 
			unit_id="'.$_SESSION['usersno'].'", 
			project_name="'.$_POST['project_name'].'", 
			project_name_hindi="'.$_POST['project_name_hindi_unicode'].'", 
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
			$_POST['district'] = '';
			$_POST['division_name'] = '';
			$_POST['project_name'] = '';
			$_POST['project_name_hindi'] = '';
			$_POST['project_name_hindi_unicode'] = '';
			$_POST['project_type'] = '';
			$_POST['type'] = '';
			$_POST['type1'] = '';
			$_POST['tot_exp_project'] = '';
		}
	}
}
else{
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
}

if(isset($_GET['eid'])){
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['eid'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['edit_sno'] = $data['sno'];
	$_POST['department'] = $data['department_id'];
	$_POST['district'] = $data['district_id'];
	$_POST['division_name'] = $data['division_id'];
	$_POST['project_name'] = $data['project_name'];
	$_POST['project_name_hindi'] = '';
	$_POST['project_name_hindi_unicode'] = $data['project_name_hindi'];
}
if(isset($_GET['delid'])){
	$sql = 'update uprnss_project_temp set status="5" where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">Data Delete</div>';
}

if(isset($_GET['freeze_sno'])){
	$sql = 'update uprnss_project_temp set master_freeze="1" where sno="'.$_GET['freeze_sno'].'"';
	execute_query($sql);
	$msg .= '<div class="alert alert-warning">Data Freezed</div>';
}

if(isset($_GET['del'])){
	$sql = 'delete from uprnss_project_temp where sno="'.$_GET['del'].'"';
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

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded">	
						<tr >
							<th width="16%">Date Type</th>
							<th width="18%"><select name="date_type" id="date_type" class="form-control" >
									<option value="">--- Select ---</option>
                                    <option value="sanction_date" <?php echo ($_POST['date_type']=='sanction_date'?' selected="selected"':''); ?>>Sanction Date</option>
                                    <option value="land_receive_date" <?php echo ($_POST['date_type']=='land_receive_date'?' selected="selected"':''); ?>>Land Receive Date</option>
                                    <option value="work_start_date" <?php echo ($_POST['date_type']=='work_start_date'?' selected="selected"':''); ?>>Work Start Date</option>
                                    <option value="work_completion_date" <?php echo ($_POST['date_type']=='work_completion_date'?' selected="selected"':''); ?>>Work Completion Date</option>
                                    <option value="technical_sanction_date" <?php echo ($_POST['date_type']=='technical_sanction_date'?' selected="selected"':''); ?>>Technical Sanction Date</option>
                                    <option value="admin_go_date" <?php echo ($_POST['date_type']=='admin_go_date'?' selected="selected"':''); ?>>Administrative GO Date</option>
                                    <option value="financial_go_date" <?php echo ($_POST['date_type']=='financial_go_date'?' selected="selected"':''); ?>>Financial GO Date</option>
							        
							    </select>
							</th>
							<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
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
							<th>मण्डल  </th>							
							<th width="15%">
								<select class="form-control" name="mandal" id="mandal" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from uprnss_mandal";
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['mandal'])){
											if($_POST['mandal']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['mandal_name_english']).'</option>';
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
						<th>S.No.</th>
						<th>परियोजना का प्रकार </th>
						<th>यूनिट का नाम  </th>
						<th>जनपद </th>
						<th>विभाग </th>
						<th>परियोजना का नाम (अंग्रेजी )</th>
						<th>परियोजना का नाम (हिन्दी )</th>
						<th>मूल परियोजना लागत ( लाख में ) &amp; </th>
						<th>पुनरीक्षित लागत की स्वीकृति का दिनांक</th>
						<th>पुनरीक्षित लागत ( लाख में )</th>
						<th>भूमि प्राप्त की तिथि</th>
						<th>कार्य प्रारंभ तिथि</th>
						<th>कार्य पूर्ण तिथि</th>
						<th>प्रशासनिक शासनादेश संख्या </br>तिथि</th>
						<th>वित्तीय शासनादेश संख्या </br>तिथि</br>धनराशि ( लाख में )</th>
						<th>तकनीकी स्वीकृति  दिनांक/</br>तकनीकी स्वीकृति क्रमांक</th>
						<th>टेंडर की स्थिति</th>
						<th>योजना पर देय जी . एस. टी .</th>
						<th>आर्किटेक्ट</th>
						<th>स्ट्राक्चरल आर्किटेक्ट </th>
						<th>मृदा परीक्षण / Survey</th>
						<th>आगणन गठन की स्थिति </th>
						<th>परियोजना  पर तैनात  अवर  अभियंता का नाम</th>
						<th>परियोजना  पर तैनात  सहायक  अभियंता का नाम</th>
						<th>परियोजना  के  अधिशासी  अभियंता का नाम </br>कब से/कब तक</th>
						<th>परियोजना  के  अधीक्षण  अभियंता का नाम </br>कब से/कब तक</th>
						
						<th>पुनरीक्षित आगण की लागत ( लाख में )</th>
						<th>पुनरीक्षित आगणन का  कारण</th>
						<th>पुनरीक्षित आगण की तिथि </th>
						<th>पुनरीक्षित प्रेषण की स्थिति /</br>धनराशि ( लाख में )</th>
						
						<!--- expenditure expenditure ----->
						<th>गत वित्तीय वर्ष में कुल व्यय</th>
						<th>वर्तमान वित्तीय वर्ष में गत माह तक व्यय ( लाख में )</th>
						<th>वर्तमान माह में व्यय ( लाख में )</th>
						<th>वर्तमान वित्तीय वर्ष में कुल व्यय ( लाख में )</th>
						<th>परियोजना में अब तक का कुल व्यय ( लाख में )</th>
						<th>शेष धनराशि परियोजना लागत के सापेक्ष ( लाख में )</th>
						<th>परियोजना पर शेष धनराशि अवमुक्त के सापेक्ष ( लाख में )</th>
						<!--- expenditure expenditure end----->
						
						<th>पुनरीक्षित प्रेषण की तिथि</th>
						<th>परियोजना की स्थिति  1</th>
						<th>परियोजना की स्थिति  2</th>
						<th>अभियुक्ति </th>
						<th >Last Update </th>
						
						
						<th class="no-print text-center">view</th>
						</tr>
						<tr>
							<?php
							for($i=1;$i<=43;$i++){
								echo '<th>'.$i.'</th>';
							}
							?>
						</tr>
						
						</thead>
						 
						<tbody>
						
						<?php
						$i=1;
						if($_SESSION['usertype']=='sadmin'){
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, land_receive_date, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,
								financial_go_amount,
								technical_sanction_date,
								technical_sanction_no,
								tender_status,
								project_type,
								
								payable_gst_on_project,
								architect_id,
								structural_architect_id,
								soil_testing_status,
								estimation_formation_status,
								project_status_1,
								project_status_2,
								remark,
								junior_engineer_name,
								assistant_engineer_name,
								executive_engineer_name,
								executive_engineer_from,
								executive_engineer_to,
								superintendent_engineer_name,
								superintendent_engineer_from,
								superintendent_engineer_to,
								revised_estimate_amount,
								revised_estimate_remark,
								revised_estimate_date,
								revised_dispatch_status,
								revised_remittance_amount,
								revised_remittance_date, 
								last_fy_tot_exp,
								current_fy_last_month_exp,
								current_month_exp,
								current_fy_tot_exp,
								tot_exp_project,
								balance_amt_cost,
								balance_amt_project,
								last_update,
								status, master_freeze 
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and (uprnss_project_temp.status !="5" or uprnss_project_temp.status is null )';
									//print_r($_POST);
								if(isset($_POST['search'])){
									if($_POST['department']!=''){
										$sql .= ' and department_id="'.$_POST['department'].'"';
									}
									if($_POST['mandal']!=''){
										$sql_1 = 'SELECT sno FROM uprnss_district WHERE mandal_name="'.$_POST['mandal'].'"';
										$result_div = execute_query($sql_1);	
										// echo $sql_1;
										$sno_array = array();
										while($row_div = mysqli_fetch_assoc($result_div)){
											$sno_array[] = $row_div["sno"];
										}
										$sql .= ' and uprnss_project_temp.district_id in ('.implode(",", $sno_array).')';
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
									
									$sql = 'select * from master_projoect_status_2 where sno="'.$row['project_status_2'].'"';
									// echo $sql;
									// $status2 = mysqli_fetch_assoc(execute_query($sql));
									$status2 = execute_query($sql);
									if(mysqli_num_rows($status2)!=0){
										$status2 = mysqli_fetch_assoc($status2);
									}
									else{
										unset($status2);
										$status2['status_2'] = '';
									}
									
									$sql = 'select * from uprnss_architect where sno="'.$row['architect_id'].'"';
									$architect = execute_query($sql);
									if(mysqli_num_rows($architect)!=0){
										$architect = mysqli_fetch_assoc($architect);
									}
									else{
										unset($architect);
										$architect['full_name_english'] = '';
									}
									
									$sql = 'select * from uprnss_architect where sno="'.$row['structural_architect_id'].'"';
									// echo $sql;
									// $starch = mysqli_fetch_assoc(execute_query($sql));
									$starch = execute_query($sql);
									if(mysqli_num_rows($starch)!=0){
										$starch = mysqli_fetch_assoc($starch);
									}
									else{
										unset($starch);
										$starch['full_name_english'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['junior_engineer_name'].'"';
									// echo $sql;
									// $je = mysqli_fetch_assoc(execute_query($sql));
									$je = execute_query($sql);
									if(mysqli_num_rows($je)!=0){
										$je = mysqli_fetch_assoc($je);
									}
									else{
										unset($je);
										$je['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['assistant_engineer_name'].'"';
									// echo $sql;
									// $ae = mysqli_fetch_assoc(execute_query($sql));
									$ae = execute_query($sql);
									if(mysqli_num_rows($ae)!=0){
										$ae = mysqli_fetch_assoc($ae);
									}
									else{
										unset($ae);
										$ae['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['executive_engineer_name'].'"';
									// echo $sql;
									$exe = execute_query($sql);
									if(mysqli_num_rows($exe)!=0){
										$exe = mysqli_fetch_assoc($exe);
									}
									else{
										unset($exe);
										$exe['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['superintendent_engineer_name'].'"';
									// echo $sql;
									// $se = mysqli_fetch_assoc(execute_query($sql));
									$se = execute_query($sql);
									if(mysqli_num_rows($se)!=0){
										$se = mysqli_fetch_assoc($se);
									}
									else{
										unset($se);
										$se['full_name'] = '';
									}
									
									$sql = 'select * from master_tender_status where sno="'.$row['tender_status'].'"';
									// echo $sql;
									// $tender = mysqli_fetch_assoc(execute_query($sql));
									$tender = execute_query($sql);
									if(mysqli_num_rows($tender)!=0){
										$tender = mysqli_fetch_assoc($tender);
									}
									else{
										unset($tender);
										$tender['status'] = '';
									}
									
									
									echo '<tr>
									<td>'.$i++.'</td>
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">Ho level</span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">Division Level</span>';
										}
									}
									echo '
									</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>
									<td>'.$row['sanction_cost'].'</td>
									
									<td>'.$row['revised_date'].'</td>
									<td>'.$row['revised_cost'].'</td>
									<td>'.$row['land_receive_date'].'</td>
									<td>'.$row['work_start_date'].'</td>
									<td>'.$row['work_completion_date'].'</td>
									<td>'.$row['admin_go_no'].'</br> '.$row['admin_go_date'].'</td>
									<td>'.$row['financial_go_no'].' </br>'.$row['financial_go_date'].'</br>'.$row['financial_go_amount'].'</td>
									<td>'.$row['technical_sanction_date'].'</br>'.$row['technical_sanction_no'].'</td>
									<td>'.$tender['status'].'</td>
									<td>'.$row['payable_gst_on_project'].'</td>
									<td>'.$architect['full_name_english'].'</td>
									<td>'.$starch['full_name_english'].'</td>
									<td>'.$row['soil_testing_status'].'</td>
									<td>'.$row['estimation_formation_status'].'</td>
									<td>'.$je['full_name'].'</td>
									<td>'.$ae['full_name'].'</td>
									<td>'.$exe['full_name'].'</br>
									'.$row['executive_engineer_from'].'--
									'.$row['executive_engineer_to'].'</td>
									
									<td>'.$se['full_name'].'</br>'.$row['superintendent_engineer_from'].'--'.$row['superintendent_engineer_to'].'</td>
									
									<td>'.$row['revised_estimate_amount'].'</td>
									<td>'.$row['revised_estimate_remark'].'</td>
									<td>'.$row['revised_estimate_date'].'</td>
									<td>'.$row['revised_dispatch_status'].'</br>'.$row['revised_remittance_amount'].'</td>
									<td>'.$row['revised_remittance_date'].'</td>
									
									<td>'.$row['last_fy_tot_exp'].'</td>
									<td>'.$row['current_fy_last_month_exp'].'</td>
									<td>'.$row['current_month_exp'].'</td>
									<td>'.$row['current_fy_tot_exp'].'</td>
									<td>'.$row['tot_exp_project'].'</td>
									<td>'.$row['balance_amt_cost'].'</td>
									<td>'.$row['balance_amt_project'].'</td>
									
									
									<td>'.$status1['status_1'].'</td>
									<td>'.$status2['status_2'].'</td>
									<td>'.$row['remark'].'</td>
									<td>'.$row['last_update'].'</td>
									<td><a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									
									</tr>'; 
								}
							}
						}
						elseif ($_SESSION['usertype']=='2'){
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, land_receive_date, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,
								financial_go_amount,
								technical_sanction_date,
								technical_sanction_no,
								tender_status,
								project_type,
								
								payable_gst_on_project,
								architect_id,
								structural_architect_id,
								soil_testing_status,
								estimation_formation_status,
								project_status_1,
								project_status_2,
								remark,
								junior_engineer_name,
							   assistant_engineer_name,
							   executive_engineer_name,
							   executive_engineer_from,
							   executive_engineer_to,
							   superintendent_engineer_name,
							   superintendent_engineer_from,
							   superintendent_engineer_to,
							   revised_estimate_amount,
								revised_estimate_remark,
								revised_estimate_date,
								revised_dispatch_status,
								revised_remittance_amount,
								revised_remittance_date, 
								last_fy_tot_exp,
								current_fy_last_month_exp,
								current_month_exp,
								current_fy_tot_exp,
								tot_exp_project,
								balance_amt_cost,
								balance_amt_project,
								last_update,
								status, master_freeze
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								where department_id in ('.implode(",", $_SESSION['department']).') and (status!="5" or status="0" or status is null or status="1")';

									// print_r($_POST);
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
									if($_POST['date_type']!=''){
										$sql .= ' and '.$_POST['date_type'].'>="'.$_POST['date_from'].'" and '.$_POST['date_type'].'<"'.date('Y-m-d', strtotime($_POST['date_to'].'+1 day')).'"';
									}
								}
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
									
									$sql = 'select * from master_projoect_status_2 where sno="'.$row['project_status_2'].'"';
									// echo $sql;
									// $status2 = mysqli_fetch_assoc(execute_query($sql));
									$status2 = execute_query($sql);
									if(mysqli_num_rows($status2)!=0){
										$status2 = mysqli_fetch_assoc($status2);
									}
									else{
										unset($status2);
										$status2['status_2'] = '';
									}
									
									$sql = 'select * from uprnss_architect where sno="'.$row['architect_id'].'"';
									// echo $sql;
									// $architect = mysqli_fetch_assoc(execute_query($sql));
									$architect = execute_query($sql);
									if(mysqli_num_rows($architect)!=0){
										$architect = mysqli_fetch_assoc($architect);
									}
									else{
										unset($architect);
										$architect['full_name_english'] = '';
									}
									
									$sql = 'select * from uprnss_architect where sno="'.$row['structural_architect_id'].'"';
									// echo $sql;
									// $starch = mysqli_fetch_assoc(execute_query($sql));
									$starch = execute_query($sql);
									if(mysqli_num_rows($starch)!=0){
										$starch = mysqli_fetch_assoc($starch);
									}
									else{
										unset($starch);
										$starch['full_name_english'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['junior_engineer_name'].'"';
									// echo $sql;
									// $je = mysqli_fetch_assoc(execute_query($sql));
									$je = execute_query($sql);
									if(mysqli_num_rows($je)!=0){
										$je = mysqli_fetch_assoc($je);
									}
									else{
										unset($je);
										$je['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['assistant_engineer_name'].'"';
									// echo $sql;
									// $ae = mysqli_fetch_assoc(execute_query($sql));
									$ae = execute_query($sql);
									if(mysqli_num_rows($ae)!=0){
										$ae = mysqli_fetch_assoc($ae);
									}
									else{
										unset($ae);
										$ae['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['executive_engineer_name'].'"';
									// echo $sql;
									$exe = execute_query($sql);
									if(mysqli_num_rows($exe)!=0){
										$exe = mysqli_fetch_assoc($exe);
									}
									else{
										unset($exe);
										$exe['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['superintendent_engineer_name'].'"';
									// echo $sql;
									// $se = mysqli_fetch_assoc(execute_query($sql));
									$se = execute_query($sql);
									if(mysqli_num_rows($se)!=0){
										$se = mysqli_fetch_assoc($se);
									}
									else{
										unset($se);
										$se['full_name'] = '';
									}
									
									$sql = 'select * from master_tender_status where sno="'.$row['tender_status'].'"';
									// echo $sql;
									// $tender = mysqli_fetch_assoc(execute_query($sql));
									$tender = execute_query($sql);
									if(mysqli_num_rows($tender)!=0){
										$tender = mysqli_fetch_assoc($tender);
									}
									else{
										unset($tender);
										$tender['status'] = '';
									}
									
									
									echo '<tr>
									<td>'.$i++.'</td>
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">Ho level</span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">Division Level</span>';
										}
									}
									echo '
									</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>
									<td>'.$row['sanction_cost'].'</td>
									
									<td>'.$row['revised_date'].'</td>
									<td>'.$row['revised_cost'].'</td>
									<td>'.$row['land_receive_date'].'</td>
									<td>'.$row['work_start_date'].'</td>
									<td>'.$row['work_completion_date'].'</td>
									<td>'.$row['admin_go_no'].'</br> '.$row['admin_go_date'].'</td>
									<td>'.$row['financial_go_no'].' </br>'.$row['financial_go_date'].'</br>'.$row['financial_go_amount'].'</td>
									<td>'.$row['technical_sanction_date'].'</br>'.$row['technical_sanction_no'].'</td>
									<td>'.$tender['status'].'</td>
									<td>'.$row['payable_gst_on_project'].'</td>
									<td>'.$architect['full_name_english'].'</td>
									<td>'.$starch['full_name_english'].'</td>
									<td>'.$row['soil_testing_status'].'</td>
									<td>'.$row['estimation_formation_status'].'</td>
									<td>'.$je['full_name'].'</td>
									<td>'.$ae['full_name'].'</td>
									<td>'.$exe['full_name'].'</br>
									'.$row['executive_engineer_from'].'--
									'.$row['executive_engineer_to'].'</td>
									
									<td>'.$se['full_name'].'</br>'.$row['superintendent_engineer_from'].'--'.$row['superintendent_engineer_to'].'</td>
									
									<td>'.$row['revised_estimate_amount'].'</td>
									<td>'.$row['revised_estimate_remark'].'</td>
									<td>'.$row['revised_estimate_date'].'</td>
									<td>'.$row['revised_dispatch_status'].'</br>'.$row['revised_remittance_amount'].'</td>
									<td>'.$row['revised_remittance_date'].'</td>
									
									<td>'.$row['last_fy_tot_exp'].'</td>
									<td>'.$row['current_fy_last_month_exp'].'</td>
									<td>'.$row['current_month_exp'].'</td>
									<td>'.$row['current_fy_tot_exp'].'</td>
									<td>'.$row['tot_exp_project'].'</td>
									<td>'.$row['balance_amt_cost'].'</td>
									<td>'.$row['balance_amt_project'].'</td>
									
									
									<td>'.$status1['status_1'].'</td>
									<td>'.$status2['status_2'].'</td>
									<td>'.$row['remark'].'</td>
									<td>'.$row['last_update'].'</td>
									<td><a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									
									</tr>';
								}
							}
						}
						else{
							if(isset($_POST['search'])){
								$sql = 'select uprnss_project_temp.sno as sno, division_name, district_name_hindi, department_name_hindi, project_name, project_name_hindi, sanction_date, sanction_cost,revised_date, revised_cost, land_receive_date, work_start_date, work_completion_date, admin_go_no, admin_go_date, financial_go_no, financial_go_date,
								financial_go_amount,
								technical_sanction_date,
								technical_sanction_no,
								tender_status,
								project_type,
								
								payable_gst_on_project,
								architect_id,
								structural_architect_id,
								soil_testing_status,
								estimation_formation_status,
								project_status_1,
								project_status_2,
								remark,
								junior_engineer_name,
								assistant_engineer_name,
								executive_engineer_name,
								executive_engineer_from,
								executive_engineer_to,
								superintendent_engineer_name,
								superintendent_engineer_from,
								superintendent_engineer_to,
								revised_estimate_amount,
								revised_estimate_remark,
								revised_estimate_date,
								revised_dispatch_status,
								revised_remittance_amount,
								revised_remittance_date, 
								last_fy_tot_exp,
								current_fy_last_month_exp,
								current_month_exp,
								current_fy_tot_exp,
								tot_exp_project,
								balance_amt_cost,
								balance_amt_project,
								last_update,
								status, master_freeze
								from uprnss_project_temp 
								left join uprnss_district on uprnss_district.sno = district_id
								left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
								left join uprnss_department_name on uprnss_department_name.sno = department_id
								where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') and (status="0" or status is null or status="1")';
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
								}
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
									
									$sql = 'select * from master_projoect_status_2 where sno="'.$row['project_status_2'].'"';
									// echo $sql;
									// $status2 = mysqli_fetch_assoc(execute_query($sql));
									$status2 = execute_query($sql);
									if(mysqli_num_rows($status2)!=0){
										$status2 = mysqli_fetch_assoc($status2);
									}
									else{
										unset($status2);
										$status2['status_2'] = '';
									}
									
									$sql = 'select * from uprnss_architect where sno="'.$row['architect_id'].'"';
									// echo $sql;
									// $architect = mysqli_fetch_assoc(execute_query($sql));
									$architect = execute_query($sql);
									if(mysqli_num_rows($architect)!=0){
										$architect = mysqli_fetch_assoc($architect);
									}
									else{
										unset($architect);
										$architect['full_name_english'] = '';
									}
									
									$sql = 'select * from uprnss_architect where sno="'.$row['structural_architect_id'].'"';
									// echo $sql;
									// $starch = mysqli_fetch_assoc(execute_query($sql));
									$starch = execute_query($sql);
									if(mysqli_num_rows($starch)!=0){
										$starch = mysqli_fetch_assoc($starch);
									}
									else{
										unset($starch);
										$starch['full_name_english'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['junior_engineer_name'].'"';
									// echo $sql;
									// $je = mysqli_fetch_assoc(execute_query($sql));
									$je = execute_query($sql);
									if(mysqli_num_rows($je)!=0){
										$je = mysqli_fetch_assoc($je);
									}
									else{
										unset($je);
										$je['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['assistant_engineer_name'].'"';
									// echo $sql;
									// $ae = mysqli_fetch_assoc(execute_query($sql));
									$ae = execute_query($sql);
									if(mysqli_num_rows($ae)!=0){
										$ae = mysqli_fetch_assoc($ae);
									}
									else{
										unset($ae);
										$ae['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['executive_engineer_name'].'"';
									// echo $sql;
									$exe = execute_query($sql);
									if(mysqli_num_rows($exe)!=0){
										$exe = mysqli_fetch_assoc($exe);
									}
									else{
										unset($exe);
										$exe['full_name'] = '';
									}
									
									$sql = 'select * from dp_personal_info where sno="'.$row['superintendent_engineer_name'].'"';
									// echo $sql;
									// $se = mysqli_fetch_assoc(execute_query($sql));
									$se = execute_query($sql);
									if(mysqli_num_rows($se)!=0){
										$se = mysqli_fetch_assoc($se);
									}
									else{
										unset($se);
										$se['full_name'] = '';
									}
									
									$sql = 'select * from master_tender_status where sno="'.$row['tender_status'].'"';
									// echo $sql;
									// $tender = mysqli_fetch_assoc(execute_query($sql));
									$tender = execute_query($sql);
									if(mysqli_num_rows($tender)!=0){
										$tender = mysqli_fetch_assoc($tender);
									}
									else{
										unset($tender);
										$tender['status'] = '';
									}
									
									
									echo '<tr>
									<td>'.$i++.'</td>
									<td>';
									if($row['project_type']!=''){
										if($row['project_type']=='1'){
											echo '<span class="">Ho level</span>';
										}
										elseif($row['project_type']=='2'){
											echo '<span class="">Division Level</span>';
										}
									}
									echo '
									</td>
									<td>'.$row['division_name'].'</td>
									<td>'.$row['district_name_hindi'].'</td>
									<td>'.$row['department_name_hindi'].'</td>
									<td>'.$row['project_name'].'</td>
									<td>'.$row['project_name_hindi'].'</td>
									<td>'.$row['sanction_cost'].'</td>
									
									<td>'.$row['revised_date'].'</td>
									<td>'.$row['revised_cost'].'</td>
									<td>'.$row['land_receive_date'].'</td>
									<td>'.$row['work_start_date'].'</td>
									<td>'.$row['work_completion_date'].'</td>
									<td>'.$row['admin_go_no'].'</br> '.$row['admin_go_date'].'</td>
									<td>'.$row['financial_go_no'].' </br>'.$row['financial_go_date'].'</br>'.$row['financial_go_amount'].'</td>
									<td>'.$row['technical_sanction_date'].'</br>'.$row['technical_sanction_no'].'</td>
									<td>'.$tender['status'].'</td>
									<td>'.$row['payable_gst_on_project'].'</td>
									<td>'.$architect['full_name_english'].'</td>
									<td>'.$starch['full_name_english'].'</td>
									<td>'.$row['soil_testing_status'].'</td>
									<td>'.$row['estimation_formation_status'].'</td>
									<td>'.$je['full_name'].'</td>
									<td>'.$ae['full_name'].'</td>
									<td>'.$exe['full_name'].'</br>
									'.$row['executive_engineer_from'].'--
									'.$row['executive_engineer_to'].'</td>
									
									<td>'.$se['full_name'].'</br>'.$row['superintendent_engineer_from'].'--'.$row['superintendent_engineer_to'].'</td>
									
									<td>'.$row['revised_estimate_amount'].'</td>
									<td>'.$row['revised_estimate_remark'].'</td>
									<td>'.$row['revised_estimate_date'].'</td>
									<td>'.$row['revised_dispatch_status'].'</br>'.$row['revised_remittance_amount'].'</td>
									<td>'.$row['revised_remittance_date'].'</td>
									
									<td>'.$row['last_fy_tot_exp'].'</td>
									<td>'.$row['current_fy_last_month_exp'].'</td>
									<td>'.$row['current_month_exp'].'</td>
									<td>'.$row['current_fy_tot_exp'].'</td>
									<td>'.$row['tot_exp_project'].'</td>
									<td>'.$row['balance_amt_cost'].'</td>
									<td>'.$row['balance_amt_project'].'</td>
									
									<td>'.$status1['status_1'].'</td>
									<td>'.$status2['status_2'].'</td>
									<td>'.$row['remark'].'</td>
									<td>'.$row['last_update'].'</td>
									<td><a href="view_project_temp.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									
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