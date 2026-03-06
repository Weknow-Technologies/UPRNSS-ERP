<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

$months = array("1"=>"Jan", "2"=>"Feb", "3"=>"Mar", "4"=>"Apr", "5"=>"May", "6"=>"Jun", "7"=>"Jul", "8"=>"Aug", "9"=>"Sep", "10"=>"Oct", "11"=>"Nov", "12"=>"Dec");

if(isset($_POST['edit_sno'])){
	if($_POST['edit_sno']==''){
		$sql= ' insert into user (name, father_name) 
		values ("' .$_POST['name'].'","' .$_POST['father_name'].'")';
	}
	else{
		$sql = 'update uprnss_project_temp set 
		
		`project_type` = "'.$_POST['project_type'].'", 
		`project_name` = "'.$_POST['project_name'].'", 
		`project_name_hindi` = "'.$_POST['project_name_hindi'].'", 
		
		
		`department_id` = "'.$_POST['department'].'", 
		`sub_department_id` = "'.$_POST['sub_department_id'].'", 
		`district_id` = "'.$_POST['district'].'", 
		
		
		`sanction_cost` = "'.$_POST['original_project_cost'].'",
		`sanction_cost_inrupes` = "'.$_POST['sanction_cost_inrupes'].'",		
		`revised_date` = "'.$_POST['date_of_acceptance_revised_cost'].'", 
		`revised_cost` = "'.$_POST['revised_cost'].'", 
		`land_receive_date` = "'.$_POST['date_of_acquisition_of_land'].'", 
		`work_start_date` = "'.$_POST['work_start_date'].'", 
		`work_completion_date` = "'.$_POST['work_completion_date'].'", 
		`technical_sanction_date` = "'.$_POST['technical_approval_date'].'", 
		`technical_sanction_no` = "'.$_POST['technical_approval_no'].'", 
		`tender_status` = "'.$_POST['tender_status'].'", 
		`admin_go_no` = "'.$_POST['administrative_mandate_no'].'", 
		`admin_go_date` = "'.$_POST['administrative_mandate_date'].'", 
		`financial_go_no` = "'.$_POST['financial_mandate_no'].'", 
		`financial_go_date` = "'.$_POST['financial_mandate_date'].'", 
		`financial_go_amount` = "'.$_POST['financial_mandate_amount'].'", 
		`last_update` = "'.date("Y-m-d").'", 


		`last_fy_tot_exp` = "'.$_POST['total_expenditure_last_financial_year'].'",
		`current_fy_last_month_exp` = "'.$_POST['expenditure_till_last_month_current_financial_year'].'",
		`current_month_exp` = "'.$_POST['expenditure_in_current_month'].'",
		`current_fy_tot_exp` = "'.$_POST['total_expenditure_current_financial_year'].'",
		`tot_exp_project` = "'.$_POST['total_expenditure_on_the_project'].'",
		`balance_amt_cost` = "'.$_POST['balance_amount'].'",
		`balance_amt_project` = "'.$_POST['balance_amount_on_project'].'",
		`rcv_tot_amount` = "'.$_POST['rcv_tot_amount'].'",


		`payable_gst_on_project` = "'.$_POST['payable_gst_on_project'].'", 
		`architect_id` = "'.$_POST['architect'].'", 
		`structural_architect_id` = "'.$_POST['structural_architect'].'", 
		`soil_testing_status` = "'.$_POST['soil_testing_status'].'", 
		`estimation_formation_status` = "'.$_POST['estimation_formation_status'].'", 
		`project_status_1` = "'.$_POST['project_status_1'].'",
		`project_status_2` = "'.$_POST['project_status_2'].'", 
		`remark` = "'.$_POST['remark'].'", 
		`junior_engineer_name` = "'.$_POST['junior_engineer_name'].'", 
		`assistant_engineer_name` = "'.$_POST['assistant_engineer_name'].'", 
		`executive_engineer_name` = "'.$_POST['executive_engineer_name'].'", 
		`executive_engineer_from` = "'.$_POST['executive_engineer_from'].'", 
		`executive_engineer_to` = "'.$_POST['executive_engineer_to'].'", 
		`superintendent_engineer_name` = "'.$_POST['superintendent_engineer_name'].'", 
		`superintendent_engineer_from` = "'.$_POST['superintendent_engineer_from'].'", 
		`superintendent_engineer_to` = "'.$_POST['superintendent_engineer_to'].'",
		
		`revised_estimate_amount` = "'.$_POST['revised_estimate_amount_1'].'", 
		`revised_estimate_remark` = "'.$_POST['revised_estimate_remark_1'].'", 
		`revised_estimate_date` = "'.$_POST['revised_estimate_date_1'].'", 
		`revised_dispatch_status` = "'.$_POST['revised_dispatch_status_1'].'", 
		`revised_remittance_amount` = "'.$_POST['revised_remittance_amount_1'].'", 
		`revised_remittance_date` = "'.$_POST['revised_remittance_date_1'].'", 
		`status` = "0", 
		`created_by` = "'.$_SESSION['username'].'", 
		`creation_time` = "'.date("Y-m-d H:i:s").'"
		where sno="'.$_POST['edit_sno'].'"';

		execute_query($sql);
		// echo $sql;
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		
		switch($_POST['project_status_1']){
			case '4':{
				$sql = 'update uprnss_project_temp set 
				`controversial_date` = "'.$_POST['controversial_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
			case '6':{
				$sql = 'update uprnss_project_temp set 
				`lack_money_date` = "'.$_POST['lack_money_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
			case '12':{
				$sql = 'update uprnss_project_temp set 
				`contract_date` = "'.$_POST['contract_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
			case '10':{
				$sql = 'update uprnss_project_temp set 
				`progress_date` = "'.$_POST['progress_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
			case '11':{
				$sql = 'update uprnss_project_temp set 
				`complete_date` = "'.$_POST['complete_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
			case '7':{
				$sql = 'update uprnss_project_temp set 
				`inventory_date` = "'.$_POST['inventory_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
			case '8':{
				$sql = 'update uprnss_project_temp set 
				`in_hand_date` = "'.$_POST['in_hand_date'].'"
				where sno="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				
				break;
			}
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data Update</p>';
			unset($_POST);
			goto postblank;
		}
	}	
}						
else{
	
	postblank:
	$_POST['block_name'] = '';
	$_POST['department'] = '';
	$_POST['sub_department_id'] = '';
	$_POST['district'] = '';
	$_POST['project_name'] = '';
	$_POST['project_name_hindi'] = '';

	$_POST['project_type'] = '';
	$_POST['date_of_original_approval'] = '';
	$_POST['sanction_cost_inrupes'] = '';
	$_POST['original_project_cost'] = '';
	$_POST['date_of_acceptance_revised_cost'] = date("Y-m-d");
	$_POST['revised_cost'] = '';
	$_POST['date_of_acquisition_of_land'] = date("Y-m-d");
	$_POST['work_start_date'] = date("Y-m-d");
	$_POST['work_completion_date'] = date("Y-m-d");
	$_POST['technical_approval_date'] = date("Y-m-d");
	$_POST['technical_approval_no'] = '';
	$_POST['tender_status'] = '';

	$_POST['administrative_mandate_no'] = '';
	$_POST['administrative_mandate_date'] = date("Y-m-d");
	$_POST['administrative_mandate_amount'] = '';
	$_POST['financial_mandate_no'] = '';
	$_POST['financial_mandate_date'] = date("Y-m-d");
	$_POST['financial_mandate_amount'] = '';

	$_POST['project_work_1'] = '';
	$_POST['project_work_start_date_1'] = date("Y-m-d");
	$_POST['scheduled_date_1'] = date("Y-m-d");
	$_POST['physical_details_1'] = '';
	$_POST['progress_last_month_1'] = '';
	$_POST['progress_current_month_1'] = '';
	$_POST['progress_pert_chart_1'] = '';
	$_POST['remark_1'] = '';
	$_POST['month'] = '';
	$_POST['received_amount'] = '';
	$_POST['received_date'] = '';
	$_POST['total_received_amount'] = '';

	$_POST['total_expenditure_last_financial_year'] = '';
	$_POST['expenditure_till_last_month_current_financial_year'] = '';
	$_POST['expenditure_in_current_month'] = '';
	$_POST['total_expenditure_current_financial_year'] = '';
	$_POST['total_expenditure_on_the_project'] = '';


	$_POST['rcv_tot_amount'] = '';
	$_POST['balance_amount'] = '';
	$_POST['balance_amount_on_project'] = '';
	$_POST['revision_date'] = '';
	$_POST['attachment'] = '';
	
	$_POST['date_from_1'] = date("Y-m-d");
	$_POST['add_rows_id'] = 1;
	$_POST['add_rows_id1'] = 1;
	$_POST['installment_1'] = '';
	$_POST['month_1'] = '';
	$_POST['received_amount_1'] = '';
	
	$_POST['payable_gst_on_project'] = '';
	$_POST['architect'] = '';
	$_POST['structural_architect'] = '';
	$_POST['soil_testing_status'] = '';
	$_POST['estimation_formation_status'] = '';
	$_POST['project_status_1'] = '';
	$_POST['project_status_2'] = '';
	$_POST['remark'] = '';
	$_POST['junior_engineer_name'] = '';
	$_POST['assistant_engineer_name'] = '';
	
	$_POST['executive_engineer_name'] = '';
	$_POST['executive_engineer_from'] = '';
	$_POST['executive_engineer_to'] = '';
	
	$_POST['superintendent_engineer_name'] = '';
	$_POST['superintendent_engineer_from'] = '';
	$_POST['superintendent_engineer_to'] = '';
	

	
	$_POST['edit_sno'] = '';
}


	
if(isset($_GET['edit_sno'])){
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['edit_sno'].'"';
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	// print_r($invoice);
	//echo $sql;
	$_POST['department'] = $invoice['department_id'];
	$_POST['sub_department_id'] = $invoice['sub_department_id'];
	$_POST['project_type'] = $invoice['project_type'];
	$_POST['district'] = $invoice['district_id'];
	$_POST['project_name_hindi'] = $invoice['project_name_hindi'];
	$_POST['project_name'] = $invoice['project_name'];
	$_POST['original_project_cost'] = $invoice['sanction_cost'];
	$_POST['sanction_cost_inrupes'] = $invoice['sanction_cost_inrupes'];
	$_POST['date_of_acceptance_revised_cost'] = $invoice['revised_date'];
	$_POST['revised_cost'] = $invoice['revised_cost'];
	$_POST['date_of_acquisition_of_land'] = $invoice['land_receive_date'];
	$_POST['work_start_date'] = $invoice['work_start_date'];
	$_POST['work_completion_date'] = $invoice['work_completion_date'];
	$_POST['technical_approval_date'] = $invoice['technical_sanction_date'];
	$_POST['technical_approval_no'] = $invoice['technical_sanction_no'];
	$_POST['tender_status'] = $invoice['tender_status'];
	$_POST['administrative_mandate_no'] = $invoice['admin_go_no'];
	$_POST['administrative_mandate_date'] = $invoice['admin_go_date'];
	$_POST['financial_mandate_no'] = $invoice['financial_go_no'];
	$_POST['financial_mandate_date'] = $invoice['financial_go_date'];
	$_POST['financial_mandate_amount'] = $invoice['financial_go_amount'];
	
	

	
	$_POST['revised_estimate_amount_1'] = $invoice['revised_estimate_amount'];
	$_POST['revised_estimate_remark_1'] = $invoice['revised_estimate_remark'];
	$_POST['revised_estimate_date_1'] = $invoice['revised_estimate_date'];
	$_POST['revised_dispatch_status_1'] = $invoice['revised_dispatch_status'];
	$_POST['revised_remittance_amount_1'] = $invoice['revised_remittance_amount'];
	$_POST['revised_remittance_date_1'] = $invoice['revised_remittance_date'];
	$_POST['payable_gst_on_project'] = $invoice['payable_gst_on_project'];
	$_POST['architect'] = $invoice['architect_id'];
	$_POST['structural_architect'] = $invoice['structural_architect_id'];
	$_POST['soil_testing_status'] = $invoice['soil_testing_status'];
	$_POST['estimation_formation_status'] = $invoice['estimation_formation_status'];
	$_POST['project_status_1'] = $invoice['project_status_1'];
	$_POST['project_status_2'] = $invoice['project_status_2'];
	$_POST['remark'] = $invoice['remark'];
	$_POST['junior_engineer_name'] = $invoice['junior_engineer_name'];
	$_POST['assistant_engineer_name'] = $invoice['assistant_engineer_name'];
	$_POST['executive_engineer_name'] = $invoice['executive_engineer_name'];
	$_POST['executive_engineer_from'] = $invoice['executive_engineer_from'];
	$_POST['executive_engineer_to'] = $invoice['executive_engineer_to'];
	$_POST['superintendent_engineer_name'] = $invoice['superintendent_engineer_name'];
	$_POST['superintendent_engineer_from'] = $invoice['superintendent_engineer_from'];
	$_POST['superintendent_engineer_to'] = $invoice['superintendent_engineer_to'];

	
	$_POST['edit_sno'] = $invoice['sno'];
}

?>




   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>
						
                    <div class="card-body">
                    	<?php echo $msg; ?>
						</br><h4 class="card-title">योजना का विवरण</h4></br>
                        <div class="row">
							<input type="hidden" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
							<div class="col-md-3 ">
								<div class="form-group">
									<label >विभाग</label><br>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value)" onChange="fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
										if(!empty($_SESSION['department'])){
											$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in ('.implode(",", $_SESSION['department']).') group by department_id) ';
										}
										elseif(!empty($_SESSION['divisions'])){
											$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in ('.implode(",", $_SESSION['divisions']).') group by division_id ) ';
										}
										// echo $query;
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
                                    <label >उप विभाग</label>
                                    <select class="form-control" name="sub_department_id" id="sub_department_id" value="<?php echo $_POST['sub_department_id']; ?>" tabindex="<?php echo $tab++; ?>" >
									</select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >आच्छादित जनपद</label>
                                    <select class="form-control" name="district" id="district" value="<?php echo $_POST['district']; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)">
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>परियोजना का नाम</label><br>
                                   
									<textarea type="text" name="project_name_hindi" id="project_name_hindi" class="form-control" placeholder=""  tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['project_name_hindi']; ?> </textarea>
									
									
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>परियोजना का नाम (English)</label><br>
                                   <textarea type="text" name="project_name" id="project_name" class="form-control" placeholder=""  tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['project_name']; ?> </textarea>
									
									
                                </div>
                            </div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Type :</label>
									<select required name="project_type" id="project_type" class="form-control" onchange="onchangetype()" >
										<option value="">--- Select ---</option>
										<option value="1"<?php echo ($_POST['project_type']==1?'selected':''); ?>>शासन स्तर</option>
										<option value="2"<?php echo ($_POST['project_type']==2?'selected':''); ?>>जिला स्तर </option>
									</select>
								</div>
							</div>
						
							<!--<div class="col-md-3">
                                <div class="form-group">
                                    <label >मूल स्वीकृति की तिथि</label><br>
                                    <script type="text/javascript" language="javascript" >
											document.writeln(DateInput('date_from', 'date_of_original_approval', true, 'YYYY-MM-DD', '2022-12-01', 1));
										</script>
                                </div>
                            </div>-->
                           <div class="col-md-3">
                                <div class="form_group">
                                    <label >मूल परियोजना लागत ( लाख में )</label>
                                    <input oninput="convertLakhsToRupees()" type="text" name="original_project_cost" id="original_project_cost" class="form-control" placeholder="" value="<?php echo $_POST['original_project_cost']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form_group">
                                    <label >मूल परियोजना लागत ( रुपए  में )</label>
                                    <input type="text" name="sanction_cost_inrupes" id="sanction_cost_inrupes" class="form-control" placeholder="" value="<?php echo $_POST['sanction_cost_inrupes']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >भूमि प्राप्त की तिथि</label><br>
                                    <input type="date" name="date_of_acquisition_of_land" id="date_of_acquisition_of_land" class="form-control" value="<?php echo $_POST['date_of_acquisition_of_land']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >कार्य प्रारंभ तिथि</label><br>
                                    <input type="date" name="work_start_date" id="work_start_date" class="form-control" value="<?php echo $_POST['work_start_date']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >कार्य पूर्ण तिथि</label><br>
                                    <input type="date" name="work_completion_date" id="work_completion_date" class="form-control" value="<?php echo $_POST['work_completion_date']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
						
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >तकनीकी स्वीकृति  दिनांक</label><br>
                                    <input type="date" name="technical_approval_date" id="technical_approval_date" class="form-control" value="<?php echo $_POST['technical_approval_date']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form_group">
                                    <label >तकनीकी स्वीकृति क्रमांक</label>
                                    <input type="text" name="technical_approval_no" id="technical_approval_no" class="form-control" placeholder="" value="<?php echo $_POST['technical_approval_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							
						</div>
						<br><h4 class="card-title">शासनादेश संख्या</h4></br>
						<div class="row">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label> प्रशासनिक शासनादेश संख्या</label><br>
                                    <input type="text" name="administrative_mandate_no" id="administrative_mandate_no" class="form-control" placeholder=" " value="<?php echo $_POST['administrative_mandate_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label> तिथि</label><br>
                                    <input type="date" name="administrative_mandate_date" id="administrative_mandate_date" class="form-control" value="<?php echo $_POST['administrative_mandate_date']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<div class="row">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>वित्तीय शासनादेश संख्या</label><br>
                                    <input type="text" name="financial_mandate_no" id="financial_mandate_no" class="form-control" placeholder=" " value="<?php echo $_POST['financial_mandate_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label> तिथि</label><br>
                                    <input type="date" name="financial_mandate_date" id="financial_mandate_date" class="form-control" value="<?php echo $_POST['financial_mandate_date']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>वित्तीय शासनादेश धनराशि ( लाख में )</label><br>
                                    <input type="text" name="financial_mandate_amount" id="financial_mandate_amount" class="form-control" placeholder=" " value="<?php echo $_POST['financial_mandate_amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						
						</br><h4 class="card-title">अन्य का विवरण</h4></br>
                        <div class="row">
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>आर्किटेक्ट </label>
									<select class="form-control" name="architect" id="architect" tabindex="<?php echo $tab++; ?>">
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
									<select class="form-control" name="structural_architect" id="structural_architect" tabindex="<?php echo $tab++; ?>">
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
                        </div>
						<div class="row" id="add_rows_length" >
							<div class="col-md-4">
								<div class="form-group">
									<label>परियोजना  पर तैनात  अवर  अभियंता का नाम </label>
									<select class="form-control" name="junior_engineer_name" id="junior_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name, employee_designation_id from dp_personal_info where employee_designation_id=1 ";
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
							<div class="col-md-4">
								<div class="form-group">
									<label>परियोजना  पर तैनात  सहायक  अभियंता का नाम  </label>
									<select class="form-control" name="assistant_engineer_name" id="assistant_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name, employee_designation_id from dp_personal_info where employee_designation_id=2 ";
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
									<select class="form-control" name="executive_engineer_name" id="executive_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name, employee_designation_id from dp_personal_info where employee_designation_id=3 ";
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
									<label>कब से </label>
									<input type="date" name="executive_engineer_from" id="executive_engineer_from" class="form-control" placeholder="" value="<?php echo $_POST['executive_engineer_from']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>कब तक </label>
									<input type="date" name="executive_engineer_to" id="executive_engineer_to" class="form-control" placeholder="" value="<?php echo $_POST['executive_engineer_to']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
						<div class="row" id="add_rows_length" >
							<div class="col-md-4">
								<div class="form-group">
									<label>परियोजना  के  अधीक्षण अभियंता का नाम</label>
									<select class="form-control" name="superintendent_engineer_name" id="superintendent_engineer_name" tabindex="<?php echo $tab++; ?>">
										<option value="">--- Select ---</option>
										<?php
										$query = "select sno,full_name, employee_designation_id from dp_personal_info where employee_designation_id=4 ";
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
									<label>कब से </label>
									<input type="date" name="superintendent_engineer_from" id="superintendent_engineer_from" class="form-control" placeholder="" value="<?php echo $_POST['superintendent_engineer_from']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>कब तक </label>
									<input type="date" name="superintendent_engineer_to" id="superintendent_engineer_to" class="form-control" placeholder="" value="<?php echo $_POST['superintendent_engineer_to']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
						
						
						
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<input type="hidden" name="edit_sno" id="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
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
<script>

		 function convertLakhsToRupees() {
            // Get the value from lakhs input
            var lakhsInput = document.getElementById("original_project_cost").value;

            // Convert lakhs to rupees
            var rupeesOutput = lakhsInput * 100000;

            // Display the result in rupees output
            document.getElementById("sanction_cost_inrupes").value = rupeesOutput.toFixed(2);;
        }
	
var actionUrl = 'scripts/ajax.php';

function fill_district(val, selected=''){
	var data = {"term":"b", "id":"dist", "val":val};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
			$.each(data, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(value.id==selected){
					txt += ' selected="selected" ';
				}
				txt += '>'+value.district_name+'</option>';
				
			});
          	$("#district").html(txt);
        }
    });
}
	
// function fill_project(val, selected=''){
	// var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department").val()};
	// $.ajax({
        // type: "POST",
        // url: actionUrl,
        // data: data, // serializes the form's elements.
        // success: function(data){
			// var txt = '<option value="">--Select--</option>';
			// data = JSON.parse(data);
			// $.each(data, function(key, value){
				// txt += '<option value="'+value.id+'" ';
				// if(value.id==selected){
					// txt += ' selected="selected" ';
				// }
				// txt += '>'+value.project_name_hindi+'</option>';
				
			// });
          	// $("#project_name").html(txt);
        // }
    // });
// }
	
function fill_project_details(val){
	var data = {"term":"b", "id":"proj_detail", "val":val, "dept":$("#department").val()};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
          	$("#original_project_cost").val(data.sanction_cost);
          	$("#date_of_acceptance_revised_cost").val(data.revised_date);
          	$("#revised_cost").val(data.revised_cost);
          	$("#date_of_acquisition_of_land").val(data.land_receive_date);
          	$("#work_start_date").val(data.work_start_date);
          	$("#work_completion_date").val(data.work_completion_date);
          	$("#technical_approval_date").val(data.technical_sanction_date);
          	$("#technical_approval_no").val(data.technical_sanction_no);
          	$("#tender_status").val(data.tender_status);
          	$("#administrative_mandate_no").val(data.admin_go_no);
          	$("#administrative_mandate_date").val(data.admin_go_date);
          	$("#financial_mandate_no").val(data.financial_go_no);
          	$("#financial_mandate_date").val(data.financial_go_date);
          	$("#financial_mandate_amount").val(data.financial_go_amount);
          	
        }
    });
}
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
	if(isset($_GET['id']) || isset($_GET['edit_sno'])){
?>
	$(document).ready(function() {
		fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>  );
		fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['edit_sno']; ?>);
		fill_project_details(<?php echo $_POST['edit_sno']; ?>);
		
		
	});
	
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>
