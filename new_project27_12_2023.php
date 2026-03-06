<?php
include("scripts/settings.php");
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

//print_r($_POST);

if(isset($_POST['department'])){
    $sql= ' insert into invoice_new_project (department,sub_department_id, go_type, go_number, go_date, go_short_number, sanction_cost, sanction_date, central_share, state_share, project_name_english, project_name_hindi, scheme, sub_scheme, project_under, status, created_by, creation_time)
	
    values ("' .$_POST['department'].'", "' .$_POST['sub_department_id'].'", "' .$_POST['go_type'].'", "'.$_POST['go_number'].'", "'.$_POST['go_date'].'", "'.$_POST['go_short_number'].'", "'.$_POST['sanction_cost'].'", "'.$_POST['sanction_date'].'", "'.$_POST['central_share'].'", "'.$_POST['state_share'].'", "'.$_POST['project_name_english'].'", "'.$_POST['project_name_hindi'].'", "'.$_POST['scheme'].'", "'.$_POST['sub_scheme'].'", "'.$_POST['project_under'].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'")';
    execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['add_rows_id']; $i++){
			for($a=1; $a<=$_POST['project_qun_'.$i]; $a++){
				$project_type = ($_SESSION['usertype'] == 1) ? 2 : 1;
				
				$sql= ' insert into transaction_new_project (invoice_id, division_id, district_id, sub_project_name,sub_project_name_hindi, status, created_by, creation_time, civil_status) values ("'.$inv_id.'", "' .$_POST['unit_name_'.$i].'", "'.$_POST['district_name_'.$i].'", "'.$_POST['project_name_english'].' '.$a.'","'.$_POST['project_name_hindi'].' '.$a.'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","0")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$trns_id = mysqli_insert_id($db);
					// $msg .= '<p class="text text-success">Data Saved</p>';
					$sql= ' insert into uprnss_project_temp 
					(new_project_id, new_project_trans_id , division_id, district_id, project_name, project_name_hindi, department_id, sub_department_id, scheme, sub_scheme, project_under, status, created_by, creation_time, master_freeze, civil_status, project_type)
					
					values ("'.$inv_id.'","'.$trns_id.'", "' .$_POST['unit_name_'.$i].'", "'.$_POST['district_name_'.$i].'", "'.$_POST['project_name_english'].' '.$a.'","'.$_POST['project_name_hindi'].' '.$a.'","' .$_POST['department'].'", "' .$_POST['sub_department_id'].'", "'.$_POST['scheme'].'", "'.$_POST['sub_scheme'].'", "'.$_POST['project_under'].'","2", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","0","0","'.$project_type.'")';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
					else{
						$project_id = mysqli_insert_id($db);
						switch($_POST['go_type']){
							case '1':{
								$sql = 'update uprnss_project_temp set 
									admin_go_no = "'.$_POST['go_number'].'",
									admin_go_date = "'.$_POST['go_date'].'"
									where sno="'.$project_id.'"';
									execute_query($sql);
									
								break;
							}
							case '2':{
								// sanction_cost =  "'.$_POST['sanction_cost'].'",
								// central_share = "' .$_POST['central_share'].'",
								// state_share = "'.$_POST['state_share'].'"
								$sql = ' update uprnss_project_temp set 
								financial_go_no = "'.$_POST['go_number'].'",
								financial_go_date = "'.$_POST['go_date'].'",
								sanction_date =  "'.$_POST['sanction_date'].'"
							
								where sno="'.$project_id.'"';
								execute_query($sql);
								
								break;
							}
							case '3':{
								// sanction_cost =  "'.$_POST['sanction_cost'].'",
								// central_share = "' .$_POST['central_share'].'",
								// state_share = "'.$_POST['state_share'].'"
								$sql = ' update uprnss_project_temp set 
								admin_go_no = "'.$_POST['go_number'].'",
								financial_go_no = "'.$_POST['go_number'].'",
								admin_go_date = "'.$_POST['go_date'].'",
								financial_go_date = "'.$_POST['go_date'].'",
								sanction_date =  "'.$_POST['sanction_date'].'"
								
								where sno="'.$project_id.'"';
								execute_query($sql);
								
								break;
							}
						}
					}
					$msg .= '<div class="alert alert-success">Successfully Add New project  </div>';
				}
			}
		}
	}
}
else{
	$_POST['department'] = '';
	$_POST['sub_department_id'] = '';
	$_POST['go_type'] = '';
	$_POST['go_number'] = '';
	$_POST['go_date'] =  date("Y-m-d");
	$_POST['go_short_number'] = '';
	$_POST['sanction_cost'] = '';
	$_POST['sanction_date'] =  date("Y-m-d");
	$_POST['central_share'] = '';
	$_POST['state_share'] = '';
	$_POST['project_name_english'] = '';
	$_POST['project_name_hindi'] = '';
	$_POST['scheme'] = '';
	$_POST['sub_scheme'] = '';
	$_POST['project_under'] = '';
	$_POST['unit_name_1'] = '';
	$_POST['district_name_1'] = '';
	$_POST['project_qun_1'] = '';
	
	$_POST['add_rows_id'] = 1;
}

if(isset($_GET['edit_sno'])){
	$sql = 'select * from invoice_new_project where sno="'.$_GET['edit_sno'].'"';
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	// print_r($invoice);
	$_POST['department'] = $invoice['department'];
	$_POST['sub_department_id'] = $invoice['sub_department_id'];
	$_POST['go_type'] = $invoice['go_type'];
	$_POST['go_number'] = $invoice['go_number'];
	$_POST['go_date'] = $invoice['go_date'];
	$_POST['go_short_number'] = $invoice['go_short_number'];
	$_POST['sanction_cost'] = $invoice['sanction_cost'];
	$_POST['sanction_date'] = $invoice['sanction_date'];
	$_POST['central_share'] = $invoice['central_share'];
	$_POST['state_share'] = $invoice['state_share'];
	$_POST['project_name_english'] = $invoice['project_name_english'];
	$_POST['project_name_hindi'] = $invoice['project_name_hindi'];
	$_POST['scheme'] = $invoice['scheme'];
	$_POST['sub_scheme'] = $invoice['sub_scheme'];
	$_POST['project_under'] = $invoice['project_under'];
	
	$sql = 'select * from transaction_new_project where invoice_id="'.$_GET['edit_sno'].'"';
	// echo $sql.'<br>';
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['add_rows_id'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			//echo '<h1>Test '.$i.'</h1>'.$_POST['add_rows_id_revised_remittance'].'<br/>';
			$_POST['unit_name_'.$i] = $row_rcpt['division_id'];
			$_POST['district_name_'.$i] = $row_rcpt['district_id'];
			$_POST['project_qun_'.$i] = $row_rcpt['sub_project_name'];
			$i++;
		}
	}
	else{
		
		$_POST['unit_name_1'] = '';
		$_POST['district_name_1'] = '';
		$_POST['project_qun_1'] = '';

	}


	
	$_POST['edit_sno'] = $invoice['sno'];
}
	
	if(isset($_GET['delid'])){
		$sql = 'update invoice_new_project set status="5" where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		
		$sql = 'update transaction_new_project set status="5" where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'update uprnss_project_temp set status="5" where new_project_id="'.$_GET['delid'].'"';
		execute_query($sql);
		
		$msg .= '<div class="alert alert-warning">Data Delete</div>';
	}

?>

	<script>
		function onchangetype() {
			var type = document.getElementById("go_type");

			if (type.value == "1") {
				document.getElementById("admin").style.display = "flex";
				document.getElementById("financial").style.display = "none";
			} else if (type.value == '2' || type.value == '3') {
				document.getElementById("admin").style.display = "flex";
				document.getElementById("financial").style.display = "flex";
			} else {
				document.getElementById("admin").style.display = "none";
				document.getElementById("financial").style.display = "none";
			}
		}

	  window.onload = function() {
		onchangetype(); 
		document.getElementById("go_type").onchange = onchangetype;
	  };
	</script>



	
	<div >	
		<form id="form" name="form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
						
		<div class="row" >
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
					<h5 class="card-title"></h5>
					<?php
							if($msg!=''){
								echo '<h5>'.$msg.'</h5>';
							}
							?>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-3 ">
								<div class="form-group">
									<label ><h6>Department</h6></label>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department(this.value)">
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
								</div>
							</div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label ><h6>Sub-Department</h6></label>
                                    <select class="form-control" name="sub_department_id" id="sub_department_id" value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>" >
									</select>
                                </div>
                            </div>
						</div>
						<h5>G.O details</h5>
						<div class="row">
							<div class="col-md-4 pr-1">
								<div class="form-group">
									<label>G.O type :</label>
									<select name="go_type" id="go_type" class="form-control" onchange="onchangetype()" >
										<option value="">--Select--	</option>
										<option value="1"<?php echo ($_POST['go_type']==1?'selected':''); ?>>Administrative</option>
										<option value="2"<?php echo ($_POST['go_type']==2?'selected':''); ?>>Financial</option>
										<option value="3"<?php echo ($_POST['go_type']==3?'selected':''); ?>>Administrative + Financial</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row" id="admin" style="display:none;" >
							<div class="col-md-3 ">
								<div class="form-group">
									<label>G.O. Number</label>
									<input type="text" name="go_number" id="go_number" class="form-control" placeholder=" " value="<?php echo $_POST['go_number']; ?>" <?php echo $tab++; ?>>
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>G.O. Date</label>
									<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('go_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['go_date']; ?>', <?php echo $tab; $tab+=4;?>));
									</script>
								</div>
							</div>
						<!--	<div class="col-md-3 ">
								<div class="form-group">
								<label>G.O. Short Number</label>
									<input type="text" name="go_short_number" id="go_short_number" class="form-control" placeholder="" value="<?php echo $_POST['go_short_number']; ?>"
									</input>
								</div>
							</div>-->
						</div>    
						<div class="row" id="financial" style="display:none;">
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Sanction Cost (In Lacs)</label>
									<input   type="text" name="sanction_cost" id="sanction_cost" class="form-control" placeholder="" value="<?php echo $_POST['sanction_cost']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Sanction Date</label>
									<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('sanction_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['sanction_date']; ?>', <?php echo $tab; $tab+=4;?>));
									</script>
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Central Share</label>
									<input type="text" name="central_share" id="central_share" class="form-control" placeholder=" " value="<?php echo $_POST['central_share']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>State Share</label>
									<input type="text" name="state_share" id="state_share" class="form-control" placeholder=" " value="<?php echo $_POST['state_share']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>	
						</div>    	
					</div>
				</div>
			</div>
		</div>
			
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title"></h4>
						</div>					
						<div class="card-body">
							<div class="row" >
								<div class="col-md-4 ">
									<div class="form-group">
										<label>Project Name (English)</label>
										<input type="text" name="project_name_english" id="project_name_english" class="form-control" placeholder="" value="<?php echo $_POST['project_name_english']; ?>" tabindex="<?php echo $tab++; ?>">
										</input>
									</div>
								</div>
								<div class="col-md-4 ">
									<div class="form-group">
										<label>Project Name(हिन्दी)</label>
										<input type="text" name="project_name_hindi" id="project_name_hindi" class="form-control" placeholder=" " value="<?php echo $_POST['project_name_hindi']; ?>" tabindex="<?php echo $tab++; ?>">
										</input>
									</div>
								</div>
							</div>	
							<div class="row" >
								<div class="col-md-3">
									<div class="form-group">
										<label> Scheme </label>					
										<select class="form-control" name="scheme" id="scheme" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_project_scheme";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['scheme'])){
												if($_POST['scheme']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['scheme_name_hindi']).'</option>';
										}
										?>
									</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label> Sub-Scheme </label>
										<input type="text" name="sub_scheme" id="sub_scheme" class="form-control" placeholder="Sub-Scheme " value="<?php echo $_POST['sub_scheme']; ?>" tabindex="<?php echo $tab++; ?>" >
										</input>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Project Under</label>
										<select class="form-control" name="project_under" id="project_under" tabindex="<?php echo $tab++; ?>" >
										<option value="">--Select--	</option>
										<option value="CM Announcement"<?php echo ($_POST['project_under']=='CM Announcement'?'selected':''); ?>>CM Announcement</option>
										<option value="PM Announcement"<?php echo ($_POST['project_under']=='PM Announcement'?'selected':''); ?>>PM Announcement</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title"></h4>
						</div>					
						<div class="card-body">
							<div class="border rounded m-2 p-2 border-secondary" id="">
								<?php
									// echo '<h1>'.$_POST['add_rows_id'].'</h1>';
									for($i=1;$i<=$_POST['add_rows_id'];$i++){
								?>
								<div id="add_rows_length" class="row">
									<div class="col-md-4 "><?php echo $i; ?>.
										<div class="form-group">
											<label>Division Name</label>
											<select class="form-control" name="unit_name_<?php echo $i; ?>" id="unit_name_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
												<option value="">--- Select ---</option>
												<?php
												$query = 'select * from uprnss_division order by division_name ASC';
												$run = mysqli_query($db,$query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['s_no'].'" ';
													if(isset($_POST['unit_name_'.$i])){
														if($_POST['unit_name_'.$i]==$data['s_no']){
															echo ' selected="Selected"';
														}
													}
													echo '>'.$data['division_name'].'</option>';
												}
												?>
											</select>
										</div>
									</div>
									<div class="col-md-4 ">
										<div class="form-group">
											<label>District Name</label>
											<select class="form-control" name="district_name_<?php echo $i; ?>" id="district_name_<?php echo $i; ?>"  >
												<option value="">--- Select ---</option>
											<?php
											$query = 'select * from uprnss_district ';
											$run = mysqli_query($db,$query);
											while($data = mysqli_fetch_array($run)){
												echo '<option value="'.$data['sno'].'" ';
												if(isset($_POST['district_name_'.$i])){
													if($_POST['district_name_'.$i]==$data['sno']){
														echo ' selected="Selected"';
													}
												}
												echo '>'.$data['district_name_english'].'</option>';
											}
											?>
											</select>
										</div>
									</div>
									<div class="col-md-3 ">
										<div class="form-group">
											<label>project Quantity</label>
											<input type="text" name="project_qun_<?php echo $i; ?>" id="project_qun_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['project_qun_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
										</div>
									</div>
									<div class="col-md-1 d-flex justify-content- align-items-center">
										<button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button>
										
										
									</div>
								</div>
								<?php } ?>
								<div id="test"></div>
								<input type="hidden" name="add_rows_id" id="add_rows_id" value=<?php echo $_POST['add_rows_id']; ?>>
							</div>	
							<div class="col-md-12 pr-1"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
								<input type="hidden" name="edit_sno" value="<?php if(isset($row['id'])){echo $row['id']; }?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
		</form>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="card strpied-tabled-with-hover">
				<div class="card-header ">
				</div>
				<div class="card-body table-full-width table-responsive">
					
					<!--/*Sample Report 2nd type starts*/-->
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Department</th>
								<th>G.O.Details</th>
								<th>Project Name</th>
								<th>Scheme</th>
								<th>Sub scheme</th>
								<th colspan="3">Details</th>
								<th>View</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>	
						</thead>
						<tbody>
							<?php
							$sql = 'SELECT * FROM `invoice_new_project` where status!=5';
							// echo $sql;
							$result = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_assoc($result)){	
								$sql = 'select * from transaction_new_project where invoice_id="'.$row['sno'].'"';
								$row_transaction = mysqli_fetch_assoc(execute_query($sql));
								
								$sql = 'select * from uprnss_department_name where sno="'.$row['department'].'"';
								
								$department = mysqli_fetch_assoc(execute_query($sql));
								$sql = 'select * from uprnss_project_scheme where sno="'.$row['scheme'].'"';
								$scheme = mysqli_fetch_assoc(execute_query($sql));
								$scheme = execute_query($sql);
								if(mysqli_num_rows($scheme)!=0){
								$scheme = mysqli_fetch_assoc($scheme);
								}
								else{
									unset($scheme);
									$scheme['scheme_name_english'] = '';
								}
									
								$sql= 'select count(*) c, division_id, district_id from transaction_new_project where invoice_id="'.$row['sno'].'" and Status!="5" group by division_id, district_id';
								$result_act = execute_query($sql);
								$act_txt = '<td colspan="2"><table>
								<tr><th>Division</th><th>District</th><th>Project Quantity</th></tr>';
								if(mysqli_num_rows($result_act)!=0){
									$count_act = mysqli_num_rows($result_act);
									while($row_act = mysqli_fetch_assoc($result_act)){
										$sql = 'select * from uprnss_division where s_no="'.$row_act['division_id'].'"';
										$division = mysqli_fetch_assoc(execute_query($sql));
							
										$sql = 'select * from uprnss_district where sno="'.$row_act['district_id'].'"';
										$district = mysqli_fetch_assoc(execute_query($sql));
												
										$act_txt .= '<tr><td>'.$division['division_name'].'</td><td>'.$district['district_name_english'].'</td><td>'.$row_act['c'].'</td></tr>';
									}
								}
								else{
									$count_act = 0;
								}
								$act_txt .= '</table></td>';
									
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$department['department_name_english'].'</td>
								
								<td>';
									if($row['go_type']!=''){
										if($row['go_type']=='1'){
											echo '<span class="">Administrative</span>';
										}
										elseif($row['go_type']=='2'){
											echo '<span class="">Financial</span>';
										}
										elseif($row['go_type']=='3'){
											echo '<span class="">DAdministrative + Financial</span>';
										}
									}
									echo '
								</td>
								<td>'.$row['project_name_english'].'</td>
								<td>'.$scheme['scheme_name_english'].'</td>
								<td>'.$row['sub_scheme'].'</td>
								<td>';echo $act_txt; echo'</td>
								<td><a href="view_new_project.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
								<td class="no-print text-center">
									<a href="new_project.php?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure you?\');" target="_blank"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Project Data"></span></a>
								</td>
								<td class="no-print text-center">
									<a href="new_project.php?delid='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" style="color:#f00"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span></a>
								</td>
								</tr>';
							
							}
							?>
						</tbody>
					</table>
					<!--/*Sample Report 2nd type ends*/-->
				</div>
			</div>
		</div>
	</div>
	
<?php
page_footer_start();
?>	
		<script>
	
		
			function add_rows(){
				var id = parseFloat($("#add_rows_id").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = id+'<div id="add_rows_length" class="row"><div class="col-md-4 "><div class="form-group"><label>Unit Name</label><select class="form-control" name="unit_name_'+id+'" id="unit_name_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from uprnss_division order by division_name ASC";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['s_no'].'\'>'.$data['division_name'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-4 "><div class="form-group"><label>District Name</label><select class="form-control" name="district_name_'+id+'" id="district_name_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from uprnss_district";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['district_name_english'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-3 "><div class="form-group"><label>project Quantity</label><input type="text" name="project_qun_'+id+'" id="project_qun_'+id+'" class="form-control" placeholder=" " value=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
				$("#test").append(txt);
				$("#add_rows_id").val(id);
			}

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
						
		</script>	
	
				

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>

