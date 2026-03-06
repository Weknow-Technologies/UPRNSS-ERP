<?php
include("scripts/settings.php");
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

// print_r($_POST);

if(isset($_POST['submit'])){
	
	$sql = 'update uprnss_project_temp  set 
		
		department_id = "'.$_POST['department'].'",
		sub_department_id = "'.$_POST['sub_department_id'].'",
		division_id = "'.$_POST['division_id'].'",
		district_id = "'.$_POST['district_name'].'",
		project_name = "'.$_POST['project_sub_name_english'].'",   
		project_name_hindi = "'.$_POST['project_sub_name_hindi'].'", 
		go_type = "'.$_POST['go_type'].'", 
		sanction_cost = "'.$_POST['sanction_cost'].'", 
		sanction_date = "'.$_POST['sanction_date'].'", 
		central_share = "'.$_POST['central_share'].'", 
		state_share = "'.$_POST['state_share'].'", 
		scheme = "'.$_POST['scheme'].'", 
		sub_scheme = "'.$_POST['sub_scheme'].'", 
		project_under = "'.$_POST['project_under'].'", 
		
		civil_status = "1",
		status = "0",
		edited_by = "'.$_SESSION['usersno'].'", 
		edition_time = "'.date("Y-m-d H:i:s").'"   
		where new_project_trans_id="'.$_POST['eid'].'"';
		// echo $sql;
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			switch($_POST['go_type']){
				case '1':{
					$sql = 'update uprnss_project_temp set 
						admin_go_no = "'.$_POST['go_number'].'",
						admin_go_date = "'.$_POST['go_date'].'"
						where new_project_trans_id="'.$_POST['eid'].'"';
						execute_query($sql);
						
					break;
				}
				case '2':{
					$sql = ' update uprnss_project_temp set 
					financial_go_no = "'.$_POST['go_number'].'",
					financial_go_date = "'.$_POST['go_date'].'",
					financial_go_amount = "'.$_POST['go_amount'].'",
					sanction_cost =  "'.$_POST['sanction_cost'].'",
					sanction_date =  "'.$_POST['sanction_date'].'",
					central_share = "' .$_POST['central_share'].'",
					state_share = "'.$_POST['state_share'].'"
					where new_project_trans_id="'.$_POST['eid'].'"';
					execute_query($sql);
					
					break;
				}
				case '3':{
					$sql = ' update uprnss_project_temp set 
					admin_go_no = "'.$_POST['go_number'].'",
					financial_go_no = "'.$_POST['go_number'].'",
					admin_go_date = "'.$_POST['go_date'].'",
					financial_go_date = "'.$_POST['go_date'].'",
					financial_go_amount = "'.$_POST['go_amount'].'",
					sanction_cost =  "'.$_POST['sanction_cost'].'",
					sanction_date =  "'.$_POST['sanction_date'].'",
					central_share = "' .$_POST['central_share'].'",
					state_share = "'.$_POST['state_share'].'"
					where new_project_trans_id="'.$_POST['eid'].'"';
					execute_query($sql);
					
					break;
				}
			}		
		}
		$msg .= '<div class="alert alert-success">Successfully Update project Details </div>';
}


if(isset($_GET['eid'])){
	
	$sql = 'select * from transaction_new_project where sno="'.$_GET['eid'].'"';
	$trans = mysqli_fetch_assoc(execute_query($sql));
		$_POST['division_id'] = $trans['division_id'];
		$_POST['district_name'] = $trans['district_id'];
		$_POST['project_sub_name_english'] = $trans['sub_project_name'];
		$_POST['project_sub_name_hindi'] = $trans['sub_project_name_hindi'];
	
	$sql = 'select * from invoice_new_project where sno="'.$trans['invoice_id'].'"';
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	// print_r($invoice);
	$_POST['department'] = $invoice['department'];
	$_POST['sub_department_id'] = $invoice['sub_department_id'];
	$_POST['go_type'] = "2";
	$_POST['go_number'] = $invoice['go_number'];
	$_POST['go_date'] = $invoice['go_date'];
	$_POST['go_short_number'] = $invoice['go_short_number'];
	// $_POST['sanction_cost'] = $invoice['sanction_cost'];
	// $_POST['sanction_date'] = $invoice['sanction_date'];
	// $_POST['central_share'] = $invoice['central_share'];
	// $_POST['state_share'] = $invoice['state_share'];
	$_POST['project_name_english'] = $invoice['project_name_english'];
	$_POST['project_name_hindi'] = $invoice['project_name_hindi'];
	$_POST['scheme'] = $invoice['scheme'];
	$_POST['sub_scheme'] = $invoice['sub_scheme'];
	$_POST['project_under'] = $invoice['project_under'];
	
	$sql = 'select * from uprnss_project_temp where new_project_trans_id="'.$_GET['eid'].'"';
	$project_temp = mysqli_fetch_assoc(execute_query($sql));
	
		$_POST['sanction_cost'] = $project_temp['sanction_cost'];
		$_POST['central_share'] = $project_temp['central_share'];
		$_POST['state_share'] = $project_temp['state_share'];
		
	if($project_temp['sanction_date'] ==''){
		$_POST['sanction_date'] = $invoice['sanction_date'];
	}else{
		$_POST['sanction_date'] = $project_temp['sanction_date'];
	}
	
	$_POST['eid'] = $trans['sno'];
}
else{
	$_POST['department'] = '';
	$_POST['sub_department_id'] = '';
	$_POST['go_type'] = '';
	$_POST['go_number'] = '';
	$_POST['go_date'] = date("Y-m-d");
	$_POST['go_short_number'] = '';
	$_POST['sanction_cost'] = '';
	$_POST['sanction_date'] = date("Y-m-d");
	$_POST['central_share'] = '';
	$_POST['state_share'] = '';
	$_POST['project_name_english'] = '';
	$_POST['project_name_hindi'] = '';
	$_POST['scheme'] = '';
	$_POST['sub_scheme'] = '';
	$_POST['project_under'] = '';
	$_POST['division_id'] = '';
	$_POST['district_name'] = '';
	$_POST['project_sub_name_english'] = '';
	$_POST['project_sub_name_hindi'] = '';
	$_POST['eid'] = '';
	
	$_POST['add_rows_id'] = 1;
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
									<select readonly class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_sub_department(this.value)">
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
                                    <select readonly class="form-control" name="sub_department_id" id="sub_department_id" value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>" >
									</select>
                                </div>
                            </div>
						
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Project Name (English)</label>
									<input readonly type="text" name="project_name_english" id="project_name_english" class="form-control" placeholder="" value="<?php echo $_POST['project_name_english']; ?>" tabindex="<?php echo $tab++; ?>">
									</input>
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Project Name(हिन्दी)</label>
									<input readonly type="text" name="project_name_hindi" id="project_name_hindi" class="form-control" placeholder=" " value="<?php echo $_POST['project_name_hindi']; ?>" tabindex="<?php echo $tab++; ?>">
									</input>
								</div>
							</div>
						
							<div class="col-md-3">
								<div class="form-group">
									<label> Scheme </label>					
									<select readonly class="form-control" name="scheme" id="scheme" tabindex="<?php echo $tab++; ?>">
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
									<input readonly type="text" name="sub_scheme" id="sub_scheme" class="form-control" placeholder="Sub-Scheme " value="<?php echo $_POST['sub_scheme']; ?>" tabindex="<?php echo $tab++; ?>" >
									</input>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Under</label>
									<select readonly class="form-control" name="project_under" id="project_under" tabindex="<?php echo $tab++; ?>" >
									<option value="">--Select--	</option>
									<option value="CM Announcement"<?php echo ($_POST['project_under']=='CM Announcement'?'selected':''); ?>>CM Announcement</option>
									<option value="PM Announcement"<?php echo ($_POST['project_under']=='PM Announcement'?'selected':''); ?>>PM Announcement</option>
									</select>
								</div>
							</div>
						</div>
						<h5>G.O details</h5>
						<div class="row">
							<div class="col-md-4 pr-1">
								<div class="form-group">
									<label>G.O type :</label>
									<select readonly name="go_type" id="go_type" class="form-control" onchange="onchangetype()" >
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
									<label >G.O. Number</label>
									<input  type="text" name="go_number" id="go_number" class="form-control" placeholder=" " value="<?php echo $_POST['go_number']; ?>" <?php echo $tab++; ?>>
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
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Sanction Cost (In Lacs)</label>
									<input  type="text" name="sanction_cost" id="sanction_cost" class="form-control" placeholder="" value="<?php echo $_POST['sanction_cost']; ?>" tabindex="<?php echo $tab++; ?>">
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
							<!--<div class="col-md-3 ">
								<div class="form-group">
								<label>G.O. Short Number</label>
									<input type="text" name="go_short_number" id="go_short_number" class="form-control" placeholder="" value="<?php //echo $_POST['go_short_number']; ?>"
									</input>
								</div>
							</div>-->
						</div>    
						<div class="row" id="financial" style="display">
							<div class="col-md-3 ">
								<div class="form-group">
									<label>Central Share</label>
									<input  type="text" name="central_share" id="central_share" class="form-control" placeholder=" " value="<?php echo $_POST['central_share']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>State Share</label>
									<input  type="text" name="state_share" id="state_share" class="form-control" placeholder=" " value="<?php echo $_POST['state_share']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>G.O Amount</label>
									<input  type="text" name="go_amount" id="go_amount" class="form-control" placeholder=" " value="<?php echo $_POST['go_amount']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div> 
						
							<div class="border rounded m-2 p-2 border-secondary" id="">
								<div id="add_rows_length" class="row">
									<div class="col-md-3 ">
										<div class="form-group">
											<label>Division Name</label>
											<select readonly class="form-control" name="division_id" id="division_id" tabindex="<?php echo $tab++; ?>">
												<option value="">--- Select ---</option>
												<?php
												$query = 'select * from uprnss_division order by division_name ASC';
												$run = mysqli_query($db,$query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['s_no'].'" ';
													if(isset($_POST['division_id'])){
														if($_POST['division_id']==$data['s_no']){
															echo ' selected="Selected"';
														}
													}
													echo '>'.$data['division_name'].'</option>';
												}
												?>
											</select>
										</div>
									</div>
									<div class="col-md-3 ">
										<div class="form-group">
											<label>District Name</label>
											<select readonly class="form-control" name="district_name" id="district_name"  >
												<option value="">--- Select ---</option>
											<?php
											$query = 'select * from uprnss_district ';
											$run = mysqli_query($db,$query);
											while($data = mysqli_fetch_array($run)){
												echo '<option value="'.$data['sno'].'" ';
												if(isset($_POST['district_name'])){
													if($_POST['district_name']==$data['sno']){
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
											<label>project Sub-Name English</label>
											<input type="text" name="project_sub_name_english" id="project_sub_name_english" class="form-control" placeholder=" " value="<?php echo $_POST['project_sub_name_english']; ?>" tabindex="<?php echo $tab++; ?>">
										</div>
									</div>
									<div class="col-md-3 ">
										<div class="form-group">
											<label>project Sub-Name hindi</label>
											<input type="text" name="project_sub_name_hindi" id="project_sub_name_hindi" class="form-control" placeholder=" " value="<?php echo $_POST['project_sub_name_hindi']; ?>" tabindex="<?php echo $tab++; ?>">
										</div>
									</div>
								</div>
							</div>	
							<div class="col-md-12 pr-1"  align = "center">
								<div class="form-group">
								<input type="submit" name="submit" value="Update" class="btn btn-success btn-fill pull-right">
								<input type="hidden" name="eid" value="<?php echo $_POST['eid']; ?>">
								</div>
							</div>
						
					</div>
				</div>
			</div>
		</div>
			
		</form>
	</div>
	

	
<?php
page_footer_start();
?>	
		
				

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
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

