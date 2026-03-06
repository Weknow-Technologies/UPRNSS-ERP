<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
$path='';

page_header_start();
page_header_end();

$months = array("1"=>"Jan", "2"=>"Feb", "3"=>"Mar", "4"=>"Apr", "5"=>"May", "6"=>"Jun", "7"=>"Jul", "8"=>"Aug", "9"=>"Sep", "10"=>"Oct", "11"=>"Nov", "12"=>"Dec");

if(!isset($_GET['id'])){
	die('Invalid Request');
	
}
else{
	$sql = 'select * from invoice_civil where sno='.$_GET['id'];
	$result = execute_query($sql);
	if(mysqli_num_rows($result)==0){
		die('Invalid Request');
	}
	else{
		$row = mysqli_fetch_assoc($result);
	}
	
	$sql = 'select * from users where sno="'.$row['unit_id'].'"';
	$unit = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['block_name'] = '';
	$_POST['department'] = $row['department_id'];
	$_POST['district'] = $row['district_id'];
	$_POST['project_name'] = $row['project_name'];
	$_POST['date_of_original_approval'] = '';
	$_POST['original_project_cost'] = $row['sanction_cost'];
	$_POST['date_of_acceptance_revised_cost'] = $row['revised_date'];
	$_POST['revised_cost'] = $row['revised_cost'];
	$_POST['date_of_acquisition_of_land'] = $row['land_receive_date'];
	$_POST['work_start_date'] = $row['work_start_date'];
	$_POST['work_completion_date'] = $row['work_completion_date'];
	$_POST['technical_approval_date'] = $row['technical_sanction_date'];
	$_POST['technical_approval_no'] = $row['technical_sanction_no'];
	$_POST['tender_status'] = $row['tender_status'];

	$_POST['administrative_mandate_no'] = $row['admin_go_no'];
	$_POST['administrative_mandate_date'] = $row['admin_go_date'];
	$_POST['administrative_mandate_amount'] = '';
	$_POST['financial_mandate_no'] = $row['financial_go_no'];
	$_POST['financial_mandate_date'] = $row['financial_go_date'];
	$_POST['financial_mandate_amount'] = $row['financial_go_amount'];


	$_POST['total_expenditure_last_financial_year'] = $row['last_fy_tot_exp'];
	$_POST['expenditure_till_last_month_current_financial_year'] = $row['current_fy_last_month_exp'];
	$_POST['expenditure_in_current_month'] = $row['current_month_exp'];
	$_POST['total_expenditure_current_financial_year'] = $row['current_fy_tot_exp'];
	$_POST['total_expenditure_on_the_project'] = $row['tot_exp_project'];


	$_POST['balance_amount'] = $row['balance_amt_cost'];
	$_POST['balance_amount_on_project'] = $row['balance_amt_project'];
	$_POST['revision_date'] = $row['last_update'];
	
	$_POST['attachment'] = '';
	
	$_POST['payable_gst_on_project'] =  $row['payable_gst_on_project'];
	$_POST['architect'] =  $row['architect'];
	$_POST['structural_architect'] = $row['structural_architect'];
	$_POST['soil_testing_status'] =  $row['soil_testing_status'];
	$_POST['estimation_formation_status'] = $row['estimation_formation_status'];
	$_POST['project_status'] = $row['project_status'];
	$_POST['junior_engineer_name'] =  $row['junior_engineer_name'];
	$_POST['assistant_engineer_name'] =  $row['assistant_engineer_name'];
	
	$_POST['executive_engineer_name'] = $row['executive_engineer_name'];
	$_POST['executive_engineer_from'] =  $row['executive_engineer_from'];
	$_POST['executive_engineer_to'] =  $row['executive_engineer_to'];
	
	$_POST['superintendent_engineer_name'] =  $row['superintendent_engineer_name'];
	$_POST['superintendent_engineer_from'] =  $row['superintendent_engineer_from'];
	$_POST['superintendent_engineer_to'] =  $row['superintendent_engineer_to'];
	
	$_POST['add_rows_id'] = 1;
	$sql = 'select * from transaction_civil_activities where invoice_id="'.$row['sno'].'"';
	$result_civil = execute_query($sql);
	if(mysqli_num_rows($result_civil)!=0){
		$_POST['add_rows_id'] = mysqli_num_rows($result_civil);
		$i=1;
		while($row_civil = mysqli_fetch_assoc($result_civil)){
			$_POST['project_work_'.$i] = $row_civil['activity'];
			$_POST['project_work_start_date_'.$i] = $row_civil['activity_start_date'];
			$_POST['scheduled_date_'.$i] = $row_civil['activity_end_date'];
			$_POST['physical_details_'.$i] = $row_civil['cummulative_physical_progress'];
			$_POST['progress_last_month_'.$i] = $row_civil['last_month_physical_progress'];
			$_POST['progress_current_month_'.$i] = $row_civil['current_month_physical_progress'];
			$_POST['progress_pert_chart_'.$i] = $row_civil['target_physical_progress'];
			$_POST['remark_'.$i] = $row_civil['remarks'];
			$i++;
		}
	}
	
	$_POST['add_rows_id1'] = 1;
	$sql = 'select * from transaction_civil_receipts where invoice_id="'.$row['sno'].'"';
	$result_civil = execute_query($sql);
	if(mysqli_num_rows($result_civil)!=0){
		$_POST['add_rows_id1'] = mysqli_num_rows($result_civil);
		$i=1;
		while($row_civil = mysqli_fetch_assoc($result_civil)){
			$_POST['received_amount_'.$i] = $row_civil['transaction_amount'];
			$_POST['installment_'.$i] = $row_civil['installment'];
			$_POST['month_'.$i] = $row_civil['transaction_month'];
			$_POST['date_from_'.$i] = $row_civil['transaction_date'];
			$i++;
		}
	}
	
}

?>
   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
        <div class="row m-4">
            <div class="col-md-8">
                <div class="card border-primary">
                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>
						
                    <div class="card-body">
                    	<?php echo $msg; ?>
						</br><h4 class="card-title">योजना का विवरण</h4></br>
						</br><h5>User ID: <span class="text-primary"><?php echo $unit['userid'].'</span> | Unit Name: <span class="text-primary">'.$unit['user_name'].'</span>'; ?></h5></br>
                        <div class="row">
							<input readonly type="hidden" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
							<div class="col-md-4 ">
								<div class="form-group">
									<label >विभाग</label><br>
									<select readonly class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >आच्छादित जनपद</label>
                                    <select readonly class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
                                    	<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_district";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['department'])){
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
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>परियोजना का नाम</label><br>
                                    <input readonly type="text" name="project_name" id="project_name" class="form-control" placeholder=" " value="<?php echo $_POST['project_name']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div class="row">
							<!--<div class="col-md-3">
                                <div class="form-group">
                                    <label >मूल स्वीकृति की तिथि</label><br>
                                    <script type="text/javascript" language="javascript" >
											document.writeln(DateInput('date_from', 'date_of_original_approval', true, 'YYYY-MM-DD', '2022-12-01', 1));
										</script>
                                </div>
                            </div>-->
                            <div class="col-md-4">
                                <div class="form_group">
                                    <label >मूल परियोजना लागत ( लाख में )</label>
                                    <input readonly type="text" name="original_project_cost" id="original_project_cost" class="form-control" placeholder=""  value="<?php echo $_POST['original_project_cost']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >पुनरीक्षित लागत की स्वीकृति का दिनांक</label><br>
                                    <?php echo $_POST['date_of_acceptance_revised_cost'];?>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form_group">
                                    <label >पुनरीक्षित लागत ( लाख में )</label>
                                    <input readonly type="text" name="revised_cost" id="revised_cost" class="form-control" placeholder="" value="<?php echo $_POST['revised_cost']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div class="row">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >भूमि प्राप्त की तिथि</label><br>
                                    <?php echo $_POST['date_of_acquisition_of_land'];?>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >कार्य प्रारंभ तिथि</label><br>
                                    <?php echo $_POST['work_start_date'];?>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >कार्य पूर्ण तिथि</label><br>
                                    <?php echo $_POST['work_completion_date'];?>
                                </div>
                            </div>
						</div>
						<div class="row" >
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >तकनीकी स्वीकृति  दिनांक</label><br>
                                    <?php echo $_POST['technical_approval_date'];?>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form_group">
                                    <label >तकनीकी स्वीकृति क्रमांक</label>
                                    <input readonly type="text" name="technical_approval_no" id="technical_approval_no" class="form-control" placeholder="" value="<?php echo $_POST['technical_approval_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form_group">
                                    <label >टेंडर की स्थिति</label>
                                    
									<select readonly class="form-control" name="tender_status" id="tender_status" tabindex="<?php echo $tab++; ?>">
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
                                </div>
                            </div>
						</div>
						<br><h4 class="card-title">शासनादेश संख्या</h4></br>
						<div class="row">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label> प्रशासनिक शासनादेश संख्या</label><br>
                                    <input readonly type="text" name="administrative_mandate_no" id="administrative_mandate_no" class="form-control" placeholder=" " value="<?php echo $_POST['administrative_mandate_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label> तिथि</label><br>
                                    <?php echo $_POST['administrative_mandate_date'];?>
                                </div>
                            </div>
                        </div>
						<div class="row">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>वित्तीय शासनादेश संख्या</label><br>
                                    <input readonly type="text" name="financial_mandate_no" id="financial_mandate_no" class="form-control" placeholder=" " value="<?php echo $_POST['financial_mandate_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label> तिथि</label><br>
                                    <?php echo $_POST['financial_mandate_date'];?>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>धनराशि ( लाख में )</label><br>
                                    <input readonly type="text" name="financial_mandate_amount" id="financial_mandate_amount" class="form-control" placeholder=" " value="<?php echo $_POST['financial_mandate_amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
                        </br><h4 class="card-title">अन्य का विवरण</h4></br>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >योजना पर देय जी . एस. टी .  </label>
                                   <input readonly type="text" name="payable_gst_on_project" id="payable_gst_on_project" class="form-control" placeholder="" value="<?php echo $_POST['payable_gst_on_project']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>आर्किटेक्ट </label>
									<select readonly class="form-control" name="architect" id="architect" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name_english from uprnss_architect WHERE `uprnss_architect`.`type` = 1";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['architect'])){
												if($_POST['architect']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['full_name_english']).'</option>';
										}
										?>
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>स्ट्राक्चरल आर्किटेक्ट </label>
									<select readonly class="form-control" name="structural_architect" id="structural_architect" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name_english from uprnss_architect WHERE `uprnss_architect`.`type` = 2";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['structural_architect'])){
												if($_POST['structural_architect']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['full_name_english']).'</option>';
										}
										?>
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
								<div class="form-group">
									<label>मृदा परीक्षण </label>
									<select readonly class="form-control" name="soil_testing_status" id="soil_testing_status" value="<?php echo $_POST['soil_testing_status']; ?>" tabindex="<?php echo $tab++; ?>" >
										<option value="Select -">--Select--</option>
									
										<option value="पूर्ण"<?php echo ($_POST['soil_testing_status']=='पूर्ण'?'selected':''); ?>>पूर्ण </option>
										<option value="अपूर्ण"<?php echo ($_POST['soil_testing_status']=='अपूर्ण'?'selected':''); ?>>अपूर्ण </option>
									</select>
								</div>
							</div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >आगणन गठन की स्थिति </label>
                                   <input readonly type="text" name="estimation_formation_status" id="estimation_formation_status" class="form-control" placeholder="" value="<?php echo $_POST['estimation_formation_status']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>परियोजना की स्थिति  1</label>
									<select readonly class="form-control" name="project_status_1" id="project_status_1" tabindex="<?php echo $tab++; ?>">
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
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>परियोजना की स्थिति  2</label>
									<select readonly class="form-control" name="project_status_2" id="project_status_2" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from master_projoect_status_2";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['project_status_2'])){
												if($_POST['project_status_2']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['status_2']).'</option>';
										}
										?>
									</select>
                                </div>
                            </div>
                        </div>
						<div class="row" id="add_rows_length" >
							<div class="col-md-5">
								<div class="form-group">
									<label>परियोजना  पर तैनात  अवर  अभियंता का नाम </label>
									<select readonly class="form-control" name="junior_engineer_name" id="junior_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name from dp_personal_info ";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['junior_engineer_name'])){
												if($_POST['junior_engineer_name']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['full_name']).'</option>';
										}
										?>
									</select>
									
								</div>
							</div>
							<div class="col-md-5">
								<div class="form-group">
									<label>परियोजना  पर तैनात  सहायक  अभियंता का नाम  </label>
									<select readonly class="form-control" name="assistant_engineer_name" id="assistant_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option  value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name from dp_personal_info ";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['assistant_engineer_name'])){
												if($_POST['assistant_engineer_name']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['full_name']).'</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="row" id="add_rows_length" >
							<div class="col-md-4">
								<div class="form-group">
									<label>परियोजना  के  अधिशासी  अभियंता का नाम</label>
									<select readonly class="form-control" name="executive_engineer_name" id="executive_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name from dp_personal_info ";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['executive_engineer_name'])){
												if($_POST['executive_engineer_name']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['full_name']).'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>कब से </label></br>
									<?php echo $_POST['executive_engineer_from'];?>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>कब तक </label></br>
									<?php echo $_POST['executive_engineer_to'];?>
								</div>
							</div>
						</div>
						<div class="row" id="add_rows_length" >
							<div class="col-md-4">
								<div class="form-group">
									<label>परियोजना  के  अधीक्षण अभियंता का नाम</label>
									<select readonly class="form-control" name="superintendent_engineer_name" id="superintendent_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name from dp_personal_info ";
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['superintendent_engineer_name'])){
												if($_POST['superintendent_engineer_name']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['full_name']).'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>कब से </label></br>
									<?php echo $_POST['superintendent_engineer_from'];?>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>कब तक </label></br>
									<?php echo $_POST['superintendent_engineer_to'];?>
								</div>
							</div>
						</div>
                        <!-- Start गतिविधियों का विवरण -->
						<br><h4 class="card-title">गतिविधियों का विवरण</h4></br>
						<?php 
	   					for($i=1;$i<=$_POST['add_rows_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="add_rows_length">
                           <?php echo $i; ?>.
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>परियोजना के कार्य </label><br>
                                    <input readonly type="text" name="project_work_<?php echo $i; ?>" id="project_work_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['project_work_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >परियोजना के कार्य प्रारंभ करने की तिथि</label><br>
                                    <?php echo $_POST['project_work_start_date_'.$i];?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >परियोजना के कार्य पूर्ण करने की निर्धारित तिथि</label><br>
                                    <?php echo $_POST['scheduled_date_'.$i];?>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>अद्यतन भौतिक विवरण </label><br>
                                    <input readonly type="text" name="physical_details_<?php echo $i; ?>" id="physical_details_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['physical_details_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >गत माह तक भौतिक प्रगति</label><br>
                                    <input readonly type="text" name="progress_last_month_<?php echo $i; ?>" id="progress_last_month_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['progress_last_month_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >वर्तमान माह तक भौतिक प्रगति </label><br>
                                    <input readonly type="text" name="progress_current_month_<?php echo $i; ?>" id="progress_current_month_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['progress_current_month_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>पर्ट चार्ट तक लछित भौतिक प्रगति (प्रतिशत में )</label><br>
                                    <input readonly type="text" name="progress_pert_chart_<?php echo $i; ?>" id="progress_pert_chart_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['progress_pert_chart_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>टिप्पणी</label><br>
                                    <input readonly type="text" name="remark_<?php echo $i; ?>" id="remark_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['remark_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
                        <?php  } ?>
                        <div id="test"></div>
                        <input readonly type="hidden" name="add_rows_id" id="add_rows_id" value="<?php echo $_POST['add_rows_id']; ?>">
                        <!-- End गतिविधियों का विवरण -->
                        
                        <!-- Start प्राप्त धनराशि -->
						<br><h4 class="card-title">प्राप्त धनराशि </h4></br>
						<?php
						$tot_rcpt = 0;
						for($i=1;$i<=$_POST['add_rows_id1'];$i++){
							$tot_rcpt += $_POST['received_amount_'.$i]
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="" >
							<?php echo $i; ?>.
							<div class="col-md-3">
								<div class="form-group"> 
									<label> इण्स्टाल्मेंट</label>
									<select readonly name="installment_<?php echo $i; ?>" id="installment_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>">
										<option value="">--Select--</option>
										<option value="1" <?php echo ($_POST['installment_'.$i]==1?'selected':''); ?>>I Installment</option>
										<option value="2" <?php echo ($_POST['installment_'.$i]==2?'selected':''); ?>>II Installment</option>
										<option value="3" <?php echo ($_POST['installment_'.$i]==3?'selected':''); ?>>III Installment</option>
										<option value="4" <?php echo ($_POST['installment_'.$i]==4?'selected':''); ?>>IV Installment</option>
										<option value="5" <?php echo ($_POST['installment_'.$i]==5?'selected':''); ?>>V Installment</option>
										<option value="6" <?php echo ($_POST['installment_'.$i]==6?'selected':''); ?>>VI Installment</option>
										<option value="7" <?php echo ($_POST['installment_'.$i]==7?'selected':''); ?>>VII Installment</option>
										<option value="8" <?php echo ($_POST['installment_'.$i]==8?'selected':''); ?>>VIII Installment</option>
										<option value="9" <?php echo ($_POST['installment_'.$i]==9?'selected':''); ?>>IX Installment</option>
										<option value="10" <?php echo ($_POST['installment_'.$i]==10?'selected':''); ?>>X Installment</option>
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group"> 
									<label> माह</label>
									<select readonly name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>">
									<?php
									foreach($months as $k=>$v){
										echo '<option value="'.$k.'" ';
										echo ($_POST['month_'.$i]==$k?'selected':'');
										echo '>'.$v.'</option>';
									}	
										
									?>
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>धनराशि ( लाख में )</label>
									<input readonly type="text" name="received_amount_<?php echo $i; ?>" id="received_amount_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['received_amount_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>प्राप्ति की तिथि </label>
									<?php echo $_POST['date_from_'.$i];?>
								</div>
							</div>
						</div>
						<div id="test1"></div>
						<input readonly type="hidden" name="add_rows_id1" id="add_rows_id1" value="<?php echo $_POST['add_rows_id1']; ?>">
						<!-- End प्राप्त धनराशि-->
                        
                        <?php } ?>
                        <div class="row m-2 p-2 border-bottom">
							<div class="col-md-3">
								<div class="form-group">
									<label>कुल प्राप्त धनराशि ( लाख में )</label>
									<input readonly type="text" name="total_received_amount" id="total_received_amount" class="form-control" placeholder=" "value="<?php echo $tot_rcpt; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
						<br><h4 class="card-title">व्यय धनराशि</h4></br>
						<div class="row border-bottom" id="add_rows_length" >
							<div class="col-md-4">
								<div class="form-group">
									<label>गत वित्तीय वर्ष में कुल व्यय</label>
									<input readonly type="text" name="total_expenditure_last_financial_year" id="total_expenditure_last_financial_year" class="form-control" placeholder="" value="<?php echo $_POST['total_expenditure_last_financial_year']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>वर्तमान वित्तीय वर्ष में गत माह तक व्यय ( लाख में )</label>
									<input readonly type="text" name="expenditure_till_last_month_current_financial_year" id="expenditure_till_last_month_current_financial_year" class="form-control" placeholder="" value="<?php echo $_POST['expenditure_till_last_month_current_financial_year']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>वर्तमान माह में व्यय ( लाख में )</label>
									<input readonly type="text" name="expenditure_in_current_month" id="expenditure_in_current_month" class="form-control" placeholder="" value="<?php echo $_POST['expenditure_in_current_month']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label> वर्तमान वित्तीय वर्ष में कुल  व्यय ( लाख में )</label>
									<input readonly type="text" name="total_expenditure_current_financial_year" id="total_expenditure_current_financial_year" class="form-control" placeholder=" "value="<?php echo $_POST['total_expenditure_current_financial_year']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>परियोजना में अब तक का कुल व्यय ( लाख में )</label>
									<input readonly type="text" name="total_expenditure_on_the_project" id="total_expenditure_on_the_project" class="form-control" placeholder=" " value="<?php echo $_POST['total_expenditure_on_the_project']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
						<div id="test2"></div>
						<br><h4 class="card-title">योजना प्रगति विवरण</h4></br>
						<div class="row">
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>शेष धनराशि  परियोजना लागत के सापेक्ष ( लाख में )</label><br>
                                    <input readonly type="text" name="balance_amount" id="balance_amount" class="form-control" placeholder=" " value="<?php echo $_POST['balance_amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >परियोजना पर शेष धनराशि अवमुक्त के सापेक्ष ( लाख में )</label><br>
                                    <input readonly type="text" name="balance_amount_on_project" id="balance_amount_on_project" class="form-control" placeholder=" " value="<?php echo $_POST['balance_amount_on_project']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >संशोधन दिनांक</label>
                                    <?php echo date("d-m-Y", strtotime($_POST['revision_date'])); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
								<div class="form-group">
									<label> संलग्नक</label><br/>
									<?php
									if($path!=''){
										echo '<a href="'.$path.'" target="_blank">Click Here To View</a>';
									}
									
									?>
								</div>	
							</div>
                        </div>
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
   
<?php		
page_footer_end();
?>
