<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;



$months = array("1"=>"Jan", "2"=>"Feb", "3"=>"Mar", "4"=>"Apr", "5"=>"May", "6"=>"Jun", "7"=>"Jul", "8"=>"Aug", "9"=>"Sep", "10"=>"Oct", "11"=>"Nov", "12"=>"Dec");

// print_r($_POST);
if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){ 
		if($_POST['project_name']==''){
			$msg .= '<div class="alert alert-danger">Select Project Name</div>';
		}
		if($msg==''){
			
			$current_date = date("Y-m-d"); // Current date in YYYY-MM-DD format
			$current_day = date("d"); // Current day of the month

			if ($current_day <= 11) {
				// If the current date is on or before the 11th of the month
				$sql = 'SELECT * FROM `invoice_civil` WHERE project_name="' . $_POST['project_name'] . '" 
						AND last_update >= "' . date("Y-m-01") . '" 
						AND last_update <= "' . date("Y-m-12") . '"';
			} else {
				// If the current date is after the 12th of the month
				$sql = 'SELECT * FROM `invoice_civil` WHERE project_name="' . $_POST['project_name'] . '" 
						AND last_update >= "' . date("Y-m-13") . '" 
						AND last_update <= "' . date("Y-m-t") . '"';
			}
			// echo $sql;
			$result_mpr = execute_query($sql);
			if(mysqli_num_rows($result_mpr)==0){
			
				foreach($_POST as $k=>$v){
					$_POST[$k] = htmlentities($v);
				}
			$sql = 'INSERT INTO `invoice_civil` (`unit_id`, `department_id`,`sub_department_id`,`division_id`, `district_id`, `project_name`, `sanction_cost`, `revised_date`, `revised_cost`,`revised_cost_diff`, `land_receive_date`, `work_start_date`, `work_completion_date`, `technical_sanction_date`, `technical_sanction_no`, `tender_status`, `admin_go_no`, `admin_go_date`, `financial_go_no`, `financial_go_date`, `financial_go_amount`, `last_update`, `last_fy_tot_exp`, `current_fy_last_month_exp`, `current_month_exp`, `current_fy_tot_exp`, `tot_exp_project`, `balance_amt_cost`, `balance_amt_project`,`total_received_amount`,`total_physical_progress`, `payable_gst_on_project`,  `architect`,`structural_architect`,`soil_testing_status`, `estimation_formation_status`, `project_status_1`,status_1_date,`remark`, `junior_engineer_name`, `assistant_engineer_name`,`executive_engineer_name`,`executive_engineer_from`, `executive_engineer_to`, `superintendent_engineer_name`,`superintendent_engineer_from`, `superintendent_engineer_to`,`status`, `created_by`, `creation_time`) 
			
			VALUES ("'.$_SESSION['usersno'].'", "'.$_POST['department'].'", "'.$_POST['sub_department_id'].'","'.$_POST['division_id'].'", "'.$_POST['district'].'", "'.$_POST['project_name'].'", "'.$_POST['original_project_cost'].'", "'.$_POST['date_of_acceptance_revised_cost'].'", "'.$_POST['revised_cost'].'","'.$_POST['revised_cost_diff'].'", "'.$_POST['date_of_acquisition_of_land'].'", "'.$_POST['work_start_date'].'", "'.$_POST['work_completion_date'].'", "'.$_POST['technical_approval_date'].'", "'.$_POST['technical_approval_no'].'", "'.$_POST['tender_status'].'", "'.$_POST['administrative_mandate_no'].'", "'.$_POST['administrative_mandate_date'].'", "'.$_POST['financial_mandate_no'].'", "'.$_POST['financial_mandate_date'].'", "'.$_POST['financial_mandate_amount'].'", "'.date("Y-m-d").'",  "'.$_POST['total_expenditure_last_financial_year'].'", "'.$_POST['expenditure_till_last_month_current_financial_year'].'", "'.$_POST['expenditure_in_current_month'].'", "'.$_POST['total_expenditure_current_financial_year'].'", "'.$_POST['total_expenditure_on_the_project'].'", "'.$_POST['balance_amount'].'", "'.$_POST['balance_amount_on_project'].'","'.$_POST['total_received_amount'].'","'.$_POST['total_physical_progress'].'", "'.$_POST['payable_gst_on_project'].'", "'.$_POST['architect'].'","'.$_POST['structural_architect'].'","'.$_POST['soil_testing_status'].'", "'.$_POST['estimation_formation_status'].'", "'.$_POST['project_status_1'].'","'.$_POST['status_1_date'].'","'.$_POST['remark'].'", "'.$_POST['junior_engineer_name'].'", "'.$_POST['assistant_engineer_name'].'","'.$_POST['executive_engineer_name'].'", "'.$_POST['executive_engineer_from'].'", "'.$_POST['executive_engineer_to'].'", "'.$_POST['superintendent_engineer_name'].'","'.$_POST['superintendent_engineer_from'].'", "'.$_POST['superintendent_engineer_to'].'","0", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$id = mysqli_insert_id($db);
				$updatesqluprnssprojecttemp=execute_query("UPDATE uprnss_project_temp SET invoice_civil_sno='".$id."' WHERE sno= '".$_POST['project_name']."'");
				if($updatesqluprnssprojecttemp){
					// $msg .= '<p class="text text-success">Ho gya ho gya</p>';
				}
				
				for($i=1;$i<=$_POST['add_rows_id'];$i++){
					if($_POST['project_work_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_activities` (`invoice_id`, `activity`, `activity_start_date`, `activity_end_date`, `weightage`, `cummulative_physical_progress`, `last_month_physical_progress`, `current_month_physical_progress`, `target_physical_progress`, `remarks`, `created_by`, `creation_time`) VALUES ("'.$id.'", "'.$_POST['project_work_'.$i].'", "'.$_POST['project_work_start_date_'.$i].'", "'.$_POST['scheduled_date_'.$i].'","'.$_POST['weightage_'.$i].'", "'.$_POST['physical_details_'.$i].'", "'.$_POST['progress_last_month_'.$i].'", "'.$_POST['progress_current_month_'.$i].'", "'.$_POST['progress_pert_chart_'.$i].'", "'.$_POST['remark_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d").'")';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}


				for($i=1;$i<=$_POST['add_rows_id1'];$i++){
					if($_POST['received_amount_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_receipts` (`invoice_id`, `installment`, `transaction_month`, `transaction_date`, `transaction_amount`, `status`, `created_by`, `created_time`) VALUES ("'.$id.'", "'.$_POST['installment_'.$i].'", "'.$_POST['month_'.$i].'", "'.$_POST['date_from_'.$i].'", "'.$_POST['received_amount_'.$i].'", "0", "'.$_SESSION['username'].'", "'.date("Y-m-d").'")';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.02 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}

				if($_FILES['attachment']['name']!=''){
					$imageFileType = strtolower(pathinfo($_FILES['attachment']['name'],PATHINFO_EXTENSION));
					$new_name = $id.'_civil';
					$target_dir = $_SESSION['usersno'].'/'.$_POST['department'];
					$file_name = upload_img($_FILES['attachment'], $target_dir, $new_name);
					if($file_name['error']!='1'){
						$msg .= '<p class="text text-danger">Error # 1.03 : '.$file_name['msg'].'</p>';
					}

					$sql = 'INSERT INTO `transaction_civil_attachment` (`invoice_id`, `attachment_file_name`, `status`, `created_by`, `creation_time`) VALUES ("'.$id.'", "'.$file_name['file_name'].'", 0, "'.$_SESSION['username'].'", "'.date("Y-m-d").'")';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1.04 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
				}
				for($i=1;$i<=$_POST['add_rows_id_revised_estimate'];$i++){
					
					if($_POST['revised_estimate_remark_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_revised_estimate` (`invoice_id`, `revised_estimate_amount`, `revised_estimate_remark`, `revised_estimate_date`) 
						
						VALUES ("'.$id.'", "'.$_POST['revised_estimate_amount_'.$i].'", "'.$_POST['revised_estimate_remark_'.$i].'", "'.$_POST['revised_estimate_date_'.$i].'")';
						execute_query($sql); 
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}
				for($i=1;$i<=$_POST['add_rows_id_revised_remittance'];$i++){
					if($_POST['revised_dispatch_status_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_revised_remittance` (`invoice_id`, `revised_dispatch_status`, `revised_remittance_amount`, `revised_remittance_date`) 
						VALUES ("'.$id.'", "'.$_POST['revised_dispatch_status_'.$i].'", "'.$_POST['revised_remittance_amount_'.$i].'", "'.$_POST['revised_remittance_date_'.$i].'")';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}
				if($msg==''){
					$msg .= '<p class="text text-success">Data Saved</p>';
					unset($_POST);
					goto postblank;
				}
				//upload_img($name, $society, $new_name, $maxDim = 1500)
			}
		}
		
		else{
			$msg .= '<p class="text text-success">Allready filled</p>';
		}
		}
	}
	else{
		$sql = 'update invoice_civil set 
			`sanction_cost` = "'.$_POST['original_project_cost'].'", 
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
			`last_fy_tot_exp` = "'.$_POST['total_expenditure_last_financial_year'].'", 
			`current_fy_last_month_exp` = "'.$_POST['expenditure_till_last_month_current_financial_year'].'", 
			`current_month_exp` = "'.$_POST['expenditure_in_current_month'].'", 
			`current_fy_tot_exp` = "'.$_POST['total_expenditure_current_financial_year'].'", 
			`tot_exp_project` = "'.$_POST['total_expenditure_on_the_project'].'", 
			`balance_amt_cost` = "'.$_POST['balance_amount'].'", 
			`balance_amt_project` = "'.$_POST['balance_amount_on_project'].'",
			`total_received_amount` = "'.$_POST['total_received_amount'].'",
			`total_physical_progress` = "'.$_POST['total_physical_progress'].'",
			
			`payable_gst_on_project`= "'.$_POST['payable_gst_on_project'].'", 
			`architect`= "'.$_POST['architect'].'",
			`structural_architect`= "'.$_POST['structural_architect'].'",
			`soil_testing_status`= "'.$_POST['soil_testing_status'].'",
			`estimation_formation_status`= "'.$_POST['estimation_formation_status'].'",
			`project_status_1`= "'.$_POST['project_status_1'].'",
			`status_1_date`= "'.$_POST['status_1_date'].'",
			`remark`= "'.$_POST['remark'].'",
			`junior_engineer_name`= "'.$_POST['junior_engineer_name'].'",
			`assistant_engineer_name`= "'.$_POST['assistant_engineer_name'].'",
			`executive_engineer_name`= "'.$_POST['executive_engineer_name'].'",
			`executive_engineer_from`= "'.$_POST['executive_engineer_from'].'",
			`executive_engineer_to`= "'.$_POST['executive_engineer_to'].'",
			`superintendent_engineer_name`= "'.$_POST['superintendent_engineer_name'].'",
			`superintendent_engineer_from`= "'.$_POST['superintendent_engineer_from'].'", 
			`superintendent_engineer_to`= "'.$_POST['superintendent_engineer_to'].'", 
			`status` = "0",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("Y-m-d H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				// echo "hello";
				$sql = 'delete from transaction_civil_activities where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1;$i<=$_POST['add_rows_id'];$i++){
					if($_POST['project_work_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_activities` (`invoice_id`, `activity`, `activity_start_date`, `activity_end_date`, `weightage`, `cummulative_physical_progress`, `last_month_physical_progress`, `current_month_physical_progress`, `target_physical_progress`, `remarks`, `created_by`, `creation_time`) VALUES ("'.$_POST['edit_sno'].'", "'.$_POST['project_work_'.$i].'", "'.$_POST['project_work_start_date_'.$i].'", "'.$_POST['scheduled_date_'.$i].'","'.$_POST['weightage_'.$i].'", "'.$_POST['physical_details_'.$i].'", "'.$_POST['progress_last_month_'.$i].'", "'.$_POST['progress_current_month_'.$i].'", "'.$_POST['progress_pert_chart_'.$i].'", "'.$_POST['remark_'.$i].'", "'.$_SESSION['username'].'", "'.date("Y-m-d").'")';
						// echo $sql;
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}

				$sql = 'delete from transaction_civil_receipts where invoice_id="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				for($i=1;$i<=$_POST['add_rows_id1'];$i++){
					if($_POST['received_amount_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_receipts` (`invoice_id`, `installment`, `transaction_month`, `transaction_date`, `transaction_amount`, `status`, `edited_by`, `edition_time`) VALUES ("'.$_POST['edit_sno'].'", "'.$_POST['installment_'.$i].'", "'.$_POST['month_'.$i].'", "'.$_POST['date_from_'.$i].'", "'.$_POST['received_amount_'.$i].'", "0", "'.$_SESSION['username'].'", "'.date("Y-m-d").'")';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.02 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}
				$sql = 'delete from transaction_civil_attachment where invoice_id="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				if($_FILES['attachment']['name']!=''){
					$imageFileType = strtolower(pathinfo($_FILES['attachment']['name'],PATHINFO_EXTENSION));
					$new_name = $_POST['edit_sno'].'_civil';
					$target_dir = $_SESSION['usersno'].'/'.$_POST['department'];
					$file_name = upload_img($_FILES['attachment'], $target_dir, $new_name);
					if($file_name['error']!='1'){
						$msg .= '<p class="text text-danger">Error # 1.03 : '.$file_name['msg'].'</p>';
					}

					$sql = 'INSERT INTO `transaction_civil_attachment` (`invoice_id`, `attachment_file_name`, `status`, `created_by`, `creation_time`) VALUES ("'.$_POST['edit_sno'].'", "'.$file_name['file_name'].'", 0, "'.$_SESSION['username'].'", "'.date("Y-m-d").'")';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1.04 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
				}
				$sql = 'delete from transaction_civil_revised_estimate where invoice_id="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				for($i=1;$i<=$_POST['add_rows_id_revised_estimate'];$i++){
					
					if($_POST['revised_estimate_remark_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_revised_estimate` (`invoice_id`, `revised_estimate_amount`, `revised_estimate_remark`, `revised_estimate_date`) 
						
						VALUES ("'.$_POST['edit_sno'].'", "'.$_POST['revised_estimate_amount_'.$i].'", "'.$_POST['revised_estimate_remark_'.$i].'", "'.$_POST['revised_estimate_date_'.$i].'")';
						execute_query($sql); 
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}
				$sql = 'delete from transaction_civil_revised_remittance where invoice_id="'.$_POST['edit_sno'].'"';
				execute_query($sql);
				for($i=1;$i<=$_POST['add_rows_id_revised_remittance'];$i++){
					if($_POST['revised_dispatch_status_'.$i]!=''){
						$sql = 'INSERT INTO `transaction_civil_revised_remittance` (`invoice_id`, `revised_dispatch_status`, `revised_remittance_amount`, `revised_remittance_date`) 
						VALUES ("'.$_POST['edit_sno'].'", "'.$_POST['revised_dispatch_status_'.$i].'", "'.$_POST['revised_remittance_amount_'.$i].'", "'.$_POST['revised_remittance_date_'.$i].'")';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}				
				
				if($msg==''){
						$msg .= '<p class="text text-success">Data Update</p>';
						unset($_POST);
						goto postblank;
					}	
			}
			
	}
	
		
}
else{
	
	postblank:
	$_POST['block_name'] = '';
	$_POST['department'] = '';
	$_POST['sub_department_id'] = '';
	$_POST['division_id'] = '';
	$_POST['district'] = '';
	$_POST['project_name'] = '';
	$_POST['date_of_original_approval'] = '';
	$_POST['original_project_cost'] = '';
	$_POST['date_of_acceptance_revised_cost'] = '';
	$_POST['revised_cost'] = '';
	$_POST['date_of_acquisition_of_land'] = '';
	$_POST['work_start_date'] = '';
	$_POST['work_completion_date'] = '';
	$_POST['technical_approval_date'] = '';
	$_POST['technical_approval_no'] = '';
	$_POST['tender_status'] = '';

	$_POST['administrative_mandate_no'] = '';
	$_POST['administrative_mandate_date'] = '';
	$_POST['administrative_mandate_amount'] = '';
	$_POST['financial_mandate_no'] = '';
	$_POST['financial_mandate_date'] = '';
	$_POST['financial_mandate_amount'] = '';

	$_POST['project_work_1'] = '';
	$_POST['project_work_start_date_1'] = '';
	$_POST['scheduled_date_1'] = '';
	$_POST['weightage_1'] = '';
	$_POST['physical_details_1'] = '0';
	$_POST['progress_last_month_1'] = '0';
	$_POST['progress_current_month_1'] = '';
	$_POST['progress_pert_chart_1'] = '';
	$_POST['remark_1'] = '';
	$_POST['month'] = '';
	$_POST['received_amount'] = '';
	$_POST['received_date'] = '';
	$_POST['total_received_amount'] = '';
	$_POST['total_physical_progress'] = '';

	$_POST['total_expenditure_last_financial_year'] = '';
	$_POST['expenditure_till_last_month_current_financial_year'] = '';
	$_POST['expenditure_in_current_month'] = '';
	$_POST['total_expenditure_current_financial_year'] = '';
	$_POST['total_expenditure_on_the_project'] = '';


	$_POST['balance_amount'] = '';
	$_POST['balance_amount_on_project'] = '';
	$_POST['revision_date'] = '';
	$_POST['attachment'] = '';
	
	$_POST['date_from_1'] ='';
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
	$_POST['status_1_date'] = '';
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
	
	
	$_POST['add_rows_id_revised_estimate'] = 1;
	$_POST['revised_estimate_amount_1'] = '';
	$_POST['revised_estimate_remark_1'] = '';
	$_POST['revised_estimate_date_1'] = '';
	
	$_POST['add_rows_id_revised_remittance'] = 1;
	$_POST['revised_dispatch_status_1'] = '';
	$_POST['revised_remittance_amount_1'] = '';
	$_POST['revised_remittance_date_1'] = '';
	
	
	$_POST['edit_sno'] = '';
	$_POST['id'] = '';
}

if(isset($_GET['id'])){
	
	$sql = 'select * from uprnss_project_temp where sno="'.$_GET['id'].'"';
		$row_div = mysqli_fetch_assoc(execute_query($sql));
		$_POST['division_id'] = $row_div['division_id'];
	
	$invoice = '';
	$sql = 'select * from invoice_civil where project_name="'.$_GET['id'].'" and division_id!="" order by sno desc limit 1';
			// echo $sql;
	$res = execute_query($sql);
	$flag='';
	if($res && mysqli_num_rows($res)>0){
		$invoice = mysqli_fetch_assoc(execute_query($sql));
		$flag = "invoice_civil";
	}
	else{
		$sql = 'select * from uprnss_project_temp where sno="'.$_GET['id'].'"';
		// echo $sql;
		$invoice = mysqli_fetch_assoc(execute_query($sql));
		$flag = "project_temp";
		
	}
	// echo $flag;		
	// print_r($invoice);
	$_POST['department'] = $invoice['department_id'];
	$_POST['sub_department_id'] = $invoice['sub_department_id'];
	$_POST['district'] = $invoice['district_id'];
	$_POST['project_name'] = $invoice['project_name'];
	$_POST['original_project_cost'] = $invoice['sanction_cost'];
	$_POST['date_of_acceptance_revised_cost'] = $invoice['revised_date'];
	$_POST['revised_cost'] = $invoice['revised_cost'];
	$_POST['revised_cost_diff'] = $invoice['revised_cost_diff'];
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
	$_POST['revision_date'] = $invoice['last_update'];
	$_POST['total_expenditure_last_financial_year'] = $invoice['last_fy_tot_exp'];
	$_POST['expenditure_till_last_month_current_financial_year'] = $invoice['current_fy_last_month_exp'];
	$_POST['expenditure_in_current_month'] = $invoice['current_month_exp'];
	$_POST['total_expenditure_current_financial_year'] = $invoice['current_fy_tot_exp'];
	$_POST['total_expenditure_on_the_project'] = $invoice['tot_exp_project'];
	$_POST['balance_amount'] = $invoice['balance_amt_cost'];
	$_POST['balance_amount_on_project'] = $invoice['balance_amt_project'];
	
	$_POST['total_received_amount'] = $invoice['total_received_amount'];
	$_POST['total_physical_progress'] = $invoice['total_physical_progress'];
	
	$_POST['payable_gst_on_project'] = $invoice['payable_gst_on_project'];
	if($flag == "invoice_civil"){
		$_POST['architect'] = $invoice['architect'];
		$_POST['structural_architect'] = $invoice['structural_architect'];
		$_POST['status_1_date'] = $invoice['status_1_date'];
	}
	else{
		$_POST['architect'] = $invoice['architect_id'];
		$_POST['structural_architect'] = $invoice['structural_architect_id'];
		$_POST['status_1_date'] = '';
	}
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

	$sql = 'select * from invoice_civil  where project_name="'.$_GET['id'].'" order by sno desc limit 1';
	$res = execute_query($sql);
	if(mysqli_num_rows($res)!=0){
		$project_id = mysqli_fetch_assoc($res);
		$sql = 'select * from transaction_civil_activities where  invoice_id="' . $project_id['sno'] . '"';
		//echo $sql;
		$result_civil = execute_query($sql);
		if(mysqli_num_rows($result_civil)!=0){
			$_POST['add_rows_id'] = mysqli_num_rows($result_civil);
			$i=1;
			while($row_civil = mysqli_fetch_assoc($result_civil)){
				$_POST['project_work_'.$i] = $row_civil['activity'];
				$_POST['project_work_start_date_'.$i] = $row_civil['activity_start_date'];
				$_POST['scheduled_date_'.$i] = $row_civil['activity_end_date'];
				$_POST['weightage_'.$i] = $row_civil['weightage'];
				$_POST['physical_details_'.$i] = $row_civil['cummulative_physical_progress'];
				// $_POST['progress_last_month_'.$i] = $row_civil['last_month_physical_progress']+$row_civil['current_month_physical_progress'];
				$_POST['progress_last_month_'.$i] = (float)$row_civil['last_month_physical_progress']+(float)$row_civil['current_month_physical_progress'];
				////////////////////////////////////////////////////////////////////////////////////
				$_POST['progress_current_month_'.$i] = '';
				$_POST['progress_pert_chart_'.$i] = $row_civil['target_physical_progress'];
				$_POST['remark_'.$i] = $row_civil['remarks'];
				$i++;
			}
		}
		else{
			$_POST['project_work_1'] = '';
			$_POST['project_work_start_date_1'] = '';
			$_POST['scheduled_date_1'] = '';
			$_POST['weightage_1'] = '';
			$_POST['physical_details_1'] = '0';
			$_POST['progress_last_month_1'] = '0';
			$_POST['progress_current_month_1'] = '';
			$_POST['progress_pert_chart_1'] = '';
			$_POST['remark_1'] = '';
			
		}
	}
	
	
	$sql = 'select * from transaction_civil_receipts where invoice_id="'.(isset($project_id['sno'])?$project_id['sno']:'').'"';
			// echo $sql;
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['add_rows_id1'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			$_POST['installment_'.$i] = $row_rcpt['installment'];
			$_POST['month_'.$i] = $row_rcpt['transaction_month'];
			$_POST['received_amount_'.$i] = $row_rcpt['transaction_amount'];
			$_POST['date_from_'.$i] = $row_rcpt['transaction_date'];
			$i++;
		}
	}
	else{
		$_POST['installment_1'] = '';
		$_POST['month_1'] = '';
		$_POST['received_amount_1'] = '';
		$_POST['date_from_1'] = '';
		
	}
	/*पुनरिक्षित लागत  */
	$sql = 'select * from transaction_civil_revised_estimate where invoice_id="'.(isset($project_id['sno'])?$project_id['sno']:'').'"';
	
	// echo $sql.'<br>';
	$result_revised_estimate = execute_query($sql);
	if(mysqli_num_rows($result_revised_estimate)!=0){
		$_POST['add_rows_id_revised_estimate'] = mysqli_num_rows($result_revised_estimate);
		$i=1;
		while($row_revised_estimate = mysqli_fetch_assoc($result_revised_estimate)){
			//echo '<h1>Test '.$i.'</h1>'.$_POST['add_rows_id_revised_estimate'].'<br/>';
			$_POST['revised_estimate_amount_'.$i] = $row_revised_estimate['revised_estimate_amount'];
			$_POST['revised_estimate_remark_'.$i] = $row_revised_estimate['revised_estimate_remark'];
			$_POST['revised_estimate_date_'.$i] = $row_revised_estimate['revised_estimate_date'];
			$i++;
		}
	}
	else{
		$_POST['revised_estimate_amount_1'] = '';
		$_POST['revised_estimate_remark_1'] = '';
		$_POST['revised_estimate_date_1'] = '';
		
	}
	
	$sql = 'select * from transaction_civil_revised_remittance where invoice_id="'.(isset($project_id['sno'])?$project_id['sno']:'').'"';
	// echo $sql.'<br>';
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['add_rows_id_revised_remittance'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			//echo '<h1>Test '.$i.'</h1>'.$_POST['add_rows_id_revised_remittance'].'<br/>';
			$_POST['revised_dispatch_status_'.$i] = $row_rcpt['revised_dispatch_status'];
			$_POST['revised_remittance_amount_'.$i] = $row_rcpt['revised_remittance_amount'];
			$_POST['revised_remittance_date_'.$i] = $row_rcpt['revised_remittance_date'];
			$i++;
		}
	}
	else{
		
		$_POST['revised_dispatch_status_1'] = '';
		$_POST['revised_remittance_amount_1'] = '';
		$_POST['revised_remittance_date_1'] = '';

	}
	
	$_POST['id'] = $_GET['id'];
	
}
if(isset($_GET['edit_sno'])){

	$sql = 'select * from invoice_civil where sno="'.$_GET['edit_sno'].'"';
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	
	// print_r($invoice);
	$_POST['department'] = $invoice['department_id'];
	$_POST['sub_department_id'] = $invoice['sub_department_id'];
	$_POST['district'] = $invoice['district_id'];
	$_POST['project_name'] = $invoice['project_name'];
	$_POST['original_project_cost'] = $invoice['sanction_cost'];
	$_POST['date_of_acceptance_revised_cost'] = $invoice['revised_date'];
	$_POST['revised_cost'] = $invoice['revised_cost'];
	$_POST['revised_cost_diff'] = $invoice['revised_cost_diff'];
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
	$_POST['revision_date'] = $invoice['last_update'];
	$_POST['total_expenditure_last_financial_year'] = $invoice['last_fy_tot_exp'];
	$_POST['expenditure_till_last_month_current_financial_year'] = $invoice['current_fy_last_month_exp'];
	$_POST['expenditure_in_current_month'] = $invoice['current_month_exp'];
	$_POST['total_expenditure_current_financial_year'] = $invoice['current_fy_tot_exp'];
	$_POST['total_expenditure_on_the_project'] = $invoice['tot_exp_project'];
	$_POST['balance_amount'] = $invoice['balance_amt_cost'];
	$_POST['balance_amount_on_project'] = $invoice['balance_amt_project'];
	
	$_POST['total_received_amount'] = $invoice['total_received_amount'];
	$_POST['total_physical_progress'] = $invoice['total_physical_progress'];
	
	$_POST['payable_gst_on_project'] = $invoice['payable_gst_on_project'];

	$_POST['architect'] = $invoice['architect'];
	$_POST['structural_architect'] = $invoice['structural_architect'];
	$_POST['status_1_date'] = $invoice['status_1_date'];
	
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
	
	
	$sql = 'select * from transaction_civil_activities where invoice_id="'.$_GET['edit_sno'].'"';
	$result_civil = execute_query($sql);
	if($result_civil && mysqli_num_rows($result_civil)!=0){
		$_POST['add_rows_id'] = mysqli_num_rows($result_civil);
		$i=1;
		while($row_civil = mysqli_fetch_assoc($result_civil)){
			$_POST['project_work_'.$i] = $row_civil['activity'];
			$_POST['project_work_start_date_'.$i] = $row_civil['activity_start_date'];
			$_POST['scheduled_date_'.$i] = $row_civil['activity_end_date'];
			$_POST['weightage_'.$i] = $row_civil['weightage'];
			$_POST['physical_details_'.$i] = $row_civil['cummulative_physical_progress'];
			$_POST['progress_last_month_'.$i] = $row_civil['last_month_physical_progress'];
			$_POST['progress_current_month_'.$i] = $row_civil['current_month_physical_progress'];
			$_POST['progress_pert_chart_'.$i] = $row_civil['target_physical_progress'];
			$_POST['remark_'.$i] = $row_civil['remarks'];
			$i++;
		}
	}
	else{
		$_POST['project_work_1'] = '';
		$_POST['project_work_start_date_1'] = '';
		$_POST['scheduled_date_1'] = '';
		$_POST['physical_details_1'] = '0';
		$_POST['progress_last_month_1'] = '0';
		$_POST['progress_current_month_1'] = '';
		$_POST['progress_pert_chart_1'] = '';
		$_POST['remark_1'] = '';
		$_POST['weightage_1'] = '';
		
	}
	
	$sql = 'select * from transaction_civil_receipts where invoice_id="'.$_GET['edit_sno'].'"';
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['add_rows_id1'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			$_POST['installment_'.$i] = $row_rcpt['installment'];
			$_POST['month_'.$i] = $row_rcpt['transaction_month'];
			$_POST['received_amount_'.$i] = $row_rcpt['transaction_amount'];
			$_POST['date_from_'.$i] = $row_rcpt['transaction_date'];
			$i++;
		}
	}
	else{
		$_POST['installment_1'] = '';
		$_POST['month_1'] = '';
		$_POST['received_amount_1'] = '';
		$_POST['date_from_1'] = '';
		
	}
	/*पुनरिक्षित लागत  */
	$sql = 'select * from transaction_civil_revised_estimate where invoice_id="'.$_GET['edit_sno'].'"';
	// echo $sql.'<br>';
	$result_revised_estimate = execute_query($sql);
	if(mysqli_num_rows($result_revised_estimate)!=0){
		$_POST['add_rows_id_revised_estimate'] = mysqli_num_rows($result_revised_estimate);
		$i=1;
		while($row_revised_estimate = mysqli_fetch_assoc($result_revised_estimate)){
			//echo '<h1>Test '.$i.'</h1>'.$_POST['add_rows_id_revised_estimate'].'<br/>';
			$_POST['revised_estimate_amount_'.$i] = $row_revised_estimate['revised_estimate_amount'];
			$_POST['revised_estimate_remark_'.$i] = $row_revised_estimate['revised_estimate_remark'];
			$_POST['revised_estimate_date_'.$i] = $row_revised_estimate['revised_estimate_date'];
			$i++;
		}
	}
	else{
		$_POST['revised_estimate_amount_1'] = '';
		$_POST['revised_estimate_remark_1'] = '';
		$_POST['revised_estimate_date_1'] = '';
		
	}
	
	$sql = 'select * from transaction_civil_revised_remittance where invoice_id="'.$_GET['edit_sno'].'"';
	// echo $sql.'<br>';
	$result_rcpt = execute_query($sql);
	if(mysqli_num_rows($result_rcpt)!=0){
		$_POST['add_rows_id_revised_remittance'] = mysqli_num_rows($result_rcpt);
		$i=1;
		while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
			//echo '<h1>Test '.$i.'</h1>'.$_POST['add_rows_id_revised_remittance'].'<br/>';
			$_POST['revised_dispatch_status_'.$i] = $row_rcpt['revised_dispatch_status'];
			$_POST['revised_remittance_amount_'.$i] = $row_rcpt['revised_remittance_amount'];
			$_POST['revised_remittance_date_'.$i] = $row_rcpt['revised_remittance_date'];
			$i++;
		}
	}
	else{
		
		$_POST['revised_dispatch_status_1'] = '';
		$_POST['revised_remittance_amount_1'] = '';
		$_POST['revised_remittance_date_1'] = '';

	}
	
	$_POST['edit_sno'] = $_GET['edit_sno'];
	
}
page_header_start();
page_header_end();
page_sidebar();

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
							<input type="hidden" name="division_id" id="division_id" class="form-control" placeholder=" " value="<?php echo $_POST['division_id']; ?>" readonly tabindex="<?php echo $tab++; ?>">
							<div class="col-md-3 ">
								<div class="form-group">
									<label >विभाग</label><br>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value), fill_sub_department(this.value)">
										<option value="">--- Select ---</option>
										<?php
										if(!empty($_SESSION['department'])){
											$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in ('.implode(",", $_SESSION['department']).') group by department_id) ';
										}
										elseif(!empty($_SESSION['divisions'])){
											$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where uprnss_project_temp.division_id in ('.implode(",", $_SESSION['divisions']).') group by uprnss_project_temp.division_id ) ';
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
                                    <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)">
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>परियोजना का नाम</label><br>
                                    <select class="form-control" name="project_name" id="project_name" tabindex="<?php echo $tab++; ?>">
									</select>
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
                                    <input type="text" name="original_project_cost" id="original_project_cost" class="form-control" placeholder="" value="<?php echo $_POST['original_project_cost']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >पुनरीक्षित लागत की स्वीकृति का दिनांक</label><br>
                                    <input type="date" name="date_of_acceptance_revised_cost" id="date_of_acceptance_revised_cost" class="form-control" value="<?php echo $_POST['date_of_acceptance_revised_cost']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-2">
                                <div class="form_group">
                                    <label >पुनरीक्षित लागत ( लाख में )</label>
                                    <input type="text" name="revised_cost" id="revised_cost" class="form-control" placeholder="" value="<?php echo $_POST['revised_cost']; ?>" tabindex="<?php echo $tab++; ?>" onInput="addCalc()">
                                </div>
                            </div>
							<div class="col-md-2">
                                <div class="form_group">
                                    <label >पुनरीक्षित लागत अंतर </label>
                                    <input type="text" name="revised_cost_diff" id="revised_cost_diff" class="form-control" placeholder="" value="<?php echo $_POST['revised_cost_diff']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
                                </div>
                            </div>
                        </div>
						
						<div class="row">
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
						</div>
						<div class="row" >
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
							<div class="col-md-4">
                                <div class="form_group">
                                    <label >टेंडर की स्थिति</label>
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
                                    <label>धनराशि ( लाख में )</label><br>
                                    <input type="text" name="financial_mandate_amount" id="financial_mandate_amount" class="form-control" placeholder=" " value="<?php echo $_POST['financial_mandate_amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						
						</br><h4 class="card-title">अन्य का विवरण</h4></br>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >योजना पर देय जी . एस. टी .  </label>
                                   <input type="text" name="payable_gst_on_project" id="payable_gst_on_project" class="form-control" placeholder="" value="<?php echo $_POST['payable_gst_on_project']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
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
							<div class="col-md-3">
								<div class="form-group">
									<label>मृदा परीक्षण </label>
									<select class="form-control" name="soil_testing_status" id="soil_testing_status"  tabindex="<?php echo $tab++; ?>" >
										<option value="Select -">--Select--</option>
									
										<option value="पूर्ण"<?php echo ($_POST['soil_testing_status']=='पूर्ण'?'selected':''); ?>>पूर्ण </option>
										<option value="अपूर्ण"<?php echo ($_POST['soil_testing_status']=='अपूर्ण'?'selected':''); ?>>अपूर्ण </option>
									</select>
								</div>
							</div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >आगणन गठन की स्थिति </label>
                                   <input type="text" name="estimation_formation_status" id="estimation_formation_status" class="form-control" placeholder="" value="<?php echo $_POST['estimation_formation_status']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>परियोजना की स्थिति 1</label>
									<select required class="form-control" name="project_status_1" id="project_status_1" tabindex="<?php echo $tab++; ?>">
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
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >तिथि</label>
                                   <input type="date" name="status_1_date" id="status_1_date" class="form-control" placeholder="" value="<?php echo $_POST['status_1_date']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >अभियुक्ति </label>
                                   <textarea type="text" name="remark" id="remark" class="form-control" placeholder=""  tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['remark']; ?> </textarea>
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
				<!-- Start  पुनरीक्षित आगणन  का विवरण -->	
						<br><h4 class="card-title">पुनरीक्षित आगणन  का विवरण </h4></br>
						<?php
						// echo '<h1>'.$_POST['add_rows_id_revised_estimate'].'</h1>';
						for($i=1;$i<=$_POST['add_rows_id_revised_estimate'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="" >
							<?php echo $i; ?>.
							<div class="col-md-3">
								<div class="form-group">
									<label>पुनरीक्षित आगणन की लागत ( लाख में ) </label>
									
									<input type="text" name="revised_estimate_amount_<?php echo $i; ?>" id="revised_estimate_amount_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['revised_estimate_amount_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>पुनरीक्षित आगणन का  कारण  </label>
									<input type="text" name="revised_estimate_remark_<?php echo $i; ?>" id="revised_estimate_remark_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['revised_estimate_remark_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>पुनरीक्षित आगणन की तिथि </label>
									<input type="date" name="revised_estimate_date_<?php echo $i; ?>" id="revised_estimate_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['revised_estimate_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" class="btn btn-info pull-right" onClick="add_rows_revised_estimate()">Add</button>
							</div>
						</div>
						<?php } ?>
						<div id="revised_estimate"></div>
						<input type="hidden" name="add_rows_id_revised_estimate" id="add_rows_id_revised_estimate" value="<?php echo $_POST['add_rows_id_revised_estimate']; ?>">
						
						</br>
						<!-- Start  पुनरीक्षित प्रेषण का विवरण -->
						<h4 class="card-title">पुनरीक्षित प्रेषण का विवरण </h4></br>
						<?php
						for($i=1;$i<=$_POST['add_rows_id_revised_remittance'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="" >
							<?php echo $i; ?>.
							<div class="col-md-3">
								<div class="form-group"> 
									<label> पुनरीक्षित प्रेषण की स्थिति </label>
									<input type="text" name="revised_dispatch_status_<?php echo $i; ?>" id="revised_dispatch_status_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['revised_dispatch_status_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3 ">
								<div class="form-group">
									<label>धनराशि ( लाख में )</label>
									<input type="text" name="revised_remittance_amount_<?php echo $i; ?>" id="revised_remittance_amount_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['revised_remittance_amount_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>पुनरीक्षित प्रेषण की तिथि </label>
									<input type="date" name="revised_remittance_date_<?php echo $i; ?>" id="revised_remittance_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['revised_remittance_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" class="btn btn-info pull-right" onClick="add_rows_revised_remittance()">Add</button>
							</div>
						</div>
						<?php } ?>
						<div id="revised_remittance"></div>
						<input type="hidden" name="add_rows_id_revised_remittance" id="add_rows_id_revised_remittance" value="<?php echo $_POST['add_rows_id_revised_remittance']; ?>">
						
                   <!-- End  पुनरीक्षित प्रेषण का विवरण -->
				   
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
                                    <input type="text" name="project_work_<?php echo $i; ?>" id="project_work_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['project_work_'.$i]; ?>" <?php 
									// if(edit_sno)
									if($_POST['edit_sno']==""){
										if($_POST['project_work_'.$i]!=''){
											echo "readonly";
										}
									}
									// echo $_POST['project_work_'.$i]!=''?"readonly":""; 
									?> tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >परियोजना के कार्य प्रारंभ करने की तिथि</label><br>
                                    <input type="date" name="project_work_start_date_<?php echo $i; ?>" id="project_work_start_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['project_work_start_date_'.$i]; ?>"  tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >परियोजना के कार्य पूर्ण करने की निर्धारित तिथि</label><br>
                                    <input type="date" name="scheduled_date_<?php echo $i; ?>" id="scheduled_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['scheduled_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-2">
                                <div class="form-group">
                                    <label >वेटेज (प्रतिशत में )</label><br>
                                    <input type="number" name="weightage_<?php echo $i; ?>" id="weightage_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['weightage_'.$i]; ?>" tabindex="<?php echo $tab++; ?>" <?php 
									// if(edit_sno)
									if($_POST['edit_sno']==""){
										if($_POST['project_work_'.$i]!=''){
											echo "readonly";
										}
									}
									// echo $_POST['project_work_'.$i]!=''?"readonly":""; 
									?>  >
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>भौतिक प्रगति प्रतिशत मे ( समग्र ) </label><br>
                                    <input type="number" name="physical_details_<?php echo $i; ?>" id="physical_details_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['physical_details_'.$i]; ?>" tabindex="<?php echo $tab++; ?>" <?php 
									// if(edit_sno)
									if($_POST['edit_sno']==""){
											echo "readonly";
									}
									// echo $_POST['project_work_'.$i]!=''?"readonly":""; 
									?> >
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >गत माह तक भौतिक प्रगति (प्रतिशत में )</label><br>
                                    <input type="number" name="progress_last_month_<?php echo $i; ?>" id="progress_last_month_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['progress_last_month_'.$i]; ?>" tabindex="<?php echo $tab++; ?>" <?php 
									// if(edit_sno)
									if($_POST['edit_sno']==""){
										
											echo "readonly";
										
									}
									// echo $_POST['project_work_'.$i]!=''?"readonly":""; 
									?> >
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >वर्तमान माह का भौतिक प्रगति (प्रतिशत में ) </label><br>
                                    <input type="number" name="progress_current_month_<?php echo $i; ?>" id="progress_current_month_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['progress_current_month_'.$i]; ?>" tabindex="<?php echo $tab++; ?>" oninput="physicalreportjs(<?php echo $i; ?>)">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>पर्ट चार्ट तक लछित भौतिक प्रगति (प्रतिशत में )</label><br>
                                    <input type="number" name="progress_pert_chart_<?php echo $i; ?>" id="progress_pert_chart_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['progress_pert_chart_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>प्रगति का स्तर </label><br>
									<textarea type="text" name="remark_<?php echo $i; ?>" id="remark_<?php echo $i; ?>" class="form-control" placeholder=""  tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['remark_'.$i]; ?> </textarea>
                                   
                                </div>
                            </div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" class="btn btn-info pull-right" onClick="add_rows()">Add</button>
							</div>
                        </div>
                        <?php  } ?>
                        <div id="test"></div>
                        <input type="hidden" name="add_rows_id" id="add_rows_id" value="<?php echo $_POST['add_rows_id']; ?>">
						
						<!-- Total Physical work percentage  गतिविधियों का विवरण -->
                        
                        <div class="row m-2 p-2 border-bottom">
							<div class="col-md-3">
								<div class="form-group">
									<label>Total physical progress</label>
									<input type="text" name="total_physical_progress" id="total_physical_per" class="form-control" placeholder=" "value="<?php echo $_POST['total_physical_progress']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
						
						
						<script>			
							// Define the weights and completion percentages for each work
							function physicalreportjs(ids){
								var id = parseFloat($("#add_rows_id").val());
								// for (let d = 1; d <= id; d++) {
									let progress_last_month=parseFloat(document.getElementById('progress_last_month_'+ids).value);
									let progress_current_month=parseFloat(document.getElementById('progress_current_month_'+ids).value);
									document.getElementById('physical_details_'+ids).value=progress_last_month+progress_current_month;
									
								// }	
							}		
					</script>
						
                    <!-- End गतिविधियों का विवरण -->
                        
                    <!-- Start प्राप्त धनराशि -->
						<br><h4 class="card-title">प्राप्त धनराशि </h4></br>
						<?php
						$tot_received = 0;
						for($i=1;$i<=$_POST['add_rows_id1'];$i++){
							$tot_received += (float)$_POST['received_amount_'.$i];
						?>
						<div class="row border rounded m-2 p-2 border-secondary" id="" >
							<?php echo $i; ?>.
							<div class="col-md-3">
								<div class="form-group"> 
									<label> इण्स्टाल्मेंट</label>
									<select name="installment_<?php echo $i; ?>" id="installment_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>">
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
									<select name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>">
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
									<input type="text" name="received_amount_<?php echo $i; ?>" id="received_amount_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['received_amount_'.$i]; ?>" onInput="addCalc(<?php echo $i; ?>)" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>प्राप्ति की तिथि </label>
									<input type="date" name="date_from_<?php echo $i; ?>" id="date_from_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['date_from_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" class="btn btn-info pull-right" onClick="add_rows1()">Add</button>
							</div>
						</div>
						<?php } ?>
						<div id="test1"></div>
						<input type="hidden" name="add_rows_id1" id="add_rows_id1" value="<?php echo $_POST['add_rows_id1']; ?>">
					<!-- End प्राप्त धनराशि-->
                        
                        <div class="row m-2 p-2 border-bottom">
							<div class="col-md-3">
								<div class="form-group">
									<label>कुल प्राप्त धनराशि ( लाख में )</label>
									<input type="text" name="total_received_amount" id="total_received_amount" class="form-control" placeholder=" "value="<?php echo $tot_received; ?>" onInput="addCalc()" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
						</div>
							
						<!-- Start व्यय धनराशि -->
						
						<br><h4 class="card-title">वित्तीय प्रगति विवरण</h4></br>
						<div class="row border-bottom" id="add_rows_length" >
							<div class="col-md-4">
								<div class="form-group">
									<label>गत वित्तीय वर्ष में कुल व्यय</label>
									<input type="text" name="total_expenditure_last_financial_year" id="total_expenditure_last_financial_year" class="form-control" placeholder="" value="<?php echo $_POST['total_expenditure_last_financial_year']; ?>" tabindex="<?php echo $tab++; ?>" onInput="addCalc()">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>वर्तमान वित्तीय वर्ष में गत माह तक व्यय ( लाख में )</label>
									<input type="text" name="expenditure_till_last_month_current_financial_year" id="expenditure_till_last_month_current_financial_year" class="form-control" placeholder="" value="<?php echo $_POST['expenditure_till_last_month_current_financial_year']; ?>" tabindex="<?php echo $tab++; ?>" onInput="addCalc()">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>वर्तमान माह में व्यय ( लाख में )</label>
									<input type="text" name="expenditure_in_current_month" id="expenditure_in_current_month" class="form-control" placeholder="" value="<?php echo $_POST['expenditure_in_current_month']; ?>" tabindex="<?php echo $tab++; ?>" onInput="addCalc()">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label> वर्तमान वित्तीय वर्ष में कुल  व्यय ( लाख में )</label>
									<input readonly type="text" name="total_expenditure_current_financial_year" id="total_expenditure_current_financial_year" class="form-control" placeholder=" "value="<?php echo $_POST['total_expenditure_current_financial_year']; ?>" tabindex="<?php echo $tab++; ?>" >
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>परियोजना में अब तक का कुल व्यय ( लाख में )</label>
									<input readonly onInput="addCalc()"  type="text" name="total_expenditure_on_the_project" id="total_expenditure_on_the_project" class="form-control" placeholder=" " value="<?php echo $_POST['total_expenditure_on_the_project']; ?>" tabindex="<?php echo $tab++; ?>" >
								</div>
							</div>
							
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
									<label> संलग्नक</label>
									<input type="file" accept=".jpg, .jpeg, .gif, .png, .bmp" class="form-control" id="attachment" name="attachment" value="<?php echo $_POST['attachment']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>	
							</div>
                        </div>
					
					<script>		
						function addCalc(line_serial){
							var id = parseFloat($("#add_rows_id1").val());
							var tot=0;
							for(i=1; i<=id; i++){
								var amt = $('#received_amount_'+i).val();
								amt = parseFloat(amt);
								if(!amt){
									amt = 0;
								}
								tot += amt;
								//console.log("AMT : "+amt+" : ID: "+i);
							}
							tot = ((tot*100)/100).toFixed(2);
							$("#total_received_amount").val(tot);
							
							let previous_year_expenditure = parseFloat(document.getElementById("total_expenditure_last_financial_year").value);
							
							previous_year_expenditure = parseFloat(previous_year_expenditure);
							if(!previous_year_expenditure){
								previous_year_expenditure = 0;
							}
							let current_year_last_month_expenditure = parseFloat(document.getElementById("expenditure_till_last_month_current_financial_year").value);
							if(!current_year_last_month_expenditure){
								current_year_last_month_expenditure = 0;
							}
							let current_month_expenditure = parseFloat(document.getElementById("expenditure_in_current_month").value);
							if(!current_month_expenditure){
								current_month_expenditure = 0;
							}
							let rcv_tot_amounts = parseFloat(document.getElementById("total_received_amount").value);
							//console.log("TOTRCPT:"+rcv_tot_amounts);
							if(!rcv_tot_amounts){
								rcv_tot_amounts = 0;
								
							}
							let total_expenditure_on_the_projects = parseFloat(document.getElementById("total_expenditure_on_the_project").value);
							if(!total_expenditure_on_the_projects){
								total_expenditure_on_the_projects = 0;
							}
							let original_project_costs = parseFloat(document.getElementById("original_project_cost").value);
							if(!original_project_costs){
								original_project_costs = 0;
							}
							
							let revised_costs = parseFloat(document.getElementById("revised_cost").value);
							if(!revised_costs){
								
							}
							
							let revised_cost_differ = parseFloat(document.getElementById("revised_cost_diff").value);
							if(!revised_cost_differ){
								revised_cost_differ = 0;
							}
							
							
							// // console.log(typeof(parseInt(previous_year_expenditure)));
							// // console.log(current_year_last_month_expenditure);
							// // console.log(current_month_expenditure);
							/*document.getElementById('total_expenditure_current_financial_year').value = 
							Math.round((Math.round(parseFloat(current_year_last_month_expenditure) * 100) / 100 + Math.round(parseFloat(current_month_expenditure) * 100) / 100)*100)/100;*/
							
							var total_expenditure_current_financial_year = current_year_last_month_expenditure +current_month_expenditure;
							total_expenditure_current_financial_year = ((total_expenditure_current_financial_year*100)/100).toFixed(2);
							document.getElementById('total_expenditure_current_financial_year').value = total_expenditure_current_financial_year;
							
							var total_expenditure_on_the_project = current_year_last_month_expenditure +current_month_expenditure + previous_year_expenditure;
							total_expenditure_on_the_project = ((total_expenditure_on_the_project*100)/100).toFixed(2);
							document.getElementById('total_expenditure_on_the_project').value = total_expenditure_on_the_project;
							
							var balance_amount_on_project = rcv_tot_amounts - total_expenditure_on_the_projects;
							balance_amount_on_project = ((balance_amount_on_project*100)/100).toFixed(2);
							document.getElementById('balance_amount_on_project').value = balance_amount_on_project;
							
							var a = original_project_costs + revised_cost_differ
							a = ((a*100)/100).toFixed(2);
							console.log(a);
							
							var balance_amount = a - rcv_tot_amounts;
							
							// console.log(balance_amount);
							balance_amount = ((balance_amount*100)/100).toFixed(2);
							document.getElementById('balance_amount').value = balance_amount;
							
							
							// var revised_amount_diff = revised_costs - original_project_costs;
							// revised_amount_diff = ((revised_amount_diff*100)/100).toFixed(2);
							// document.getElementById('revised_cost_diff').value = revised_amount_diff;
							
							if (!isNaN(revised_costs) && revised_costs > 0) {
								let revised_amount_diff = revised_costs - original_project_costs;
								revised_amount_diff = ((revised_amount_diff * 100) / 100).toFixed(2);

								// Assuming you want to set this value to an input field
								document.getElementById('revised_cost_diff').value = revised_amount_diff;
							} else {
								// Handle case where revised_costs is blank or not greater than 0
								document.getElementById('revised_cost_diff').value = '0.00';
							}
							
						}	
										
					</script>
					<!-- End व्यय धनराशि -->
					
						</br>
						
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<input type="hidden" name="edit_sno" id="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
									<input type="hidden" name="id" id="id" value="<?php echo $_POST['id']; ?>">
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
	$('select[multiple]').multiselect();
	
	function add_rows(){
		var id = parseFloat($("#add_rows_id").val());
		if(!id){
			id=0;
		}
		for(var i=1; i<=id; i++){
			if($("#project_work_"+i).val()=='' || $("#physical_details_"+i).val()==''){
				alert("पंक्ति संख्या "+i+" खाली है");
				$("#project_work_"+i).focus();
				return;
			}
		}
		id = id+1;

		var date_1 = DateInput('project_work_start_date_'+i, 'date_from', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', 1)
		var date_2 = DateInput('scheduled_date_'+i, 'date_from', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', 1);

		var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="add_rows_length">'+id+'<div class="col-md-3"><div class="form-group"><label>परियोजना के कार्य </label><br><input type="text" name="project_work_'+id+'" id="project_work_'+id+'" class="form-control" placeholder="" value="" tabindex="" ></div></div><div class="col-md-3"><div class="form-group"><label >परियोजना के कार्य प्रारंभ करने की तिथि</label><input type="date" name="project_work_start_date_'+id+'" id="project_work_start_date_'+id+'" class="form-control" placeholder=" "value="" tabindex="" ></div></div><div class="col-md-3"><div class="form-group"><label >परियोजना के कार्य पूर्ण करने की निर्धारित तिथि</label><br><input type="date" name="scheduled_date_'+id+'" id="scheduled_date_'+id+'" class="form-control" placeholder=" " value="" tabindex="" ></div></div><div class="col-md-2"><div class="form-group"><label >वेटेज (प्रतिशत में )</label><br><input onInput="addCalc()" type="number" name="weightage_'+id+'" id="weightage_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-3"><div class="form-group"><label>भौतिक प्रगति प्रतिशत मे ( समग्र )</label><br><input  type="text" name="physical_details_'+id+'" id="physical_details_'+id+'" class="form-control" placeholder=" " value="0" readonly tabindex="" ></div></div><div class="col-md-3"><div class="form-group"><label >गत माह तक भौतिक प्रगति (प्रतिशत में )</label><br><input type="text" readonly name="progress_last_month_'+id+'" id="progress_last_month_'+id+'" class="form-control" placeholder=" "value="0" tabindex="" ></div></div><div class="col-md-3"><div class="form-group"><label >वर्तमान माह का  भौतिक प्रगति  (प्रतिशत में )</label><br><input type="text" name="progress_current_month_'+id+'" id="progress_current_month_'+id+'" class="form-control" oninput="physicalreportjs('+id+')" placeholder="" value="" tabindex="" ></div></div><div class="col-md-3"><div class="form-group"><label>पर्ट चार्ट तक लछित भौतिक प्रगति (प्रतिशत में )</label><br><input type="text" name="progress_pert_chart_'+id+'" id="progress_pert_chart_'+id+'" class="form-control" placeholder=" " value="" tabindex="" ></div></div><div class="col-md-3"><div class="form-group"><label>प्रगति का स्तर </label><br><textarea type="text" name="remark_'+id+'" id="remark_'+id+'" class="form-control" placeholder=""  tabindex="" > </textarea></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
		$("#test").append(txt);
		$("#add_rows_id").val(id);
	}			
	function add_rows1(){
		var id = parseFloat($("#add_rows_id1").val());
		
		if(!id){
			id=0;
		}
		id = id+1;

		var date_1 = DateInput('date_from_'+id, 'user_form', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', 1);

		var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="" >'+id+'<div class="col-md-3"><div class="form-group"><label> इण्स्टाल्मेंट</label><select name="installment_'+id+'" id="installment_'+id+'" class="form-control" ><option value="">--Select--</option><option value="1">I Installment</option><option value="2">II Installment</option><option value="3">III Installment</option><option value="4">IV Installment</option><option value="5">V Installment</option><option value="6">VI Installment</option><option value="7">VII Installment</option><option value="8">VIII Installment</option><option value="9">IX Installment</option><option value="10">X Installment</option></select></div></div><div class="col-md-3"><div class="form-group"><label> माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control" tabindex="16"><option value="1">Jan</option><option value="2">Feb</option><option value="3">Mar</option><option value="4">Apr</option><option value="5">May</option><option value="6">Jun</option><option value="7">Jul</option><option value="8">Aug</option><option value="9">Sep</option><option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option></select></div></div><div class="col-md-2"><div class="form-group"><label>धनराशि </label><input type="text" name="received_amount_'+id+'" id="received_amount_'+id+'" class="form-control" placeholder=" " value="" onChange="addCalc('+id+')" tabindex=""></div></div><div class="col-md-2"><div class="form-group"><label>प्राप्ति की तिथि </label><input type="date" name="date_from_'+id+'" id="date_from_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows1()">Add</button></div></div>';
		$("#test1").append(txt);
		$("#add_rows_id1").val(id);
	}
	function add_rows_revised_estimate(){
		var id = parseFloat($("#add_rows_id_revised_estimate").val());
		if(!id){
			id=0;
		}
		id = id+1;

		var date_1 = DateInput('revised_estimate_date_'+id, 'user_form', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', 1);

		var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="" >'+id+'<div class="col-md-3 "><div class="form-group"><label>पुनरीक्षित आगणन की लागत ( लाख में ) </label><input type="text" name="revised_estimate_amount_'+id+'" id="revised_estimate_amount_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-3"><div class="form-group"><label>पुनरीक्षित आगणन का  कारण  </label><input type="text" name="revised_estimate_remark_'+id+'" id="revised_estimate_remark_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-3"><div class="form-group"><label>पुनरीक्षित आगणन की तिथि </label><input type="date" name="revised_estimate_date_'+id+'" id="revised_estimate_date_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows_revised_estimate()">Add</button></div></div>';
		$("#revised_estimate").append(txt);
		$("#add_rows_id_revised_estimate").val(id);
	}

		function add_rows_revised_remittance(){
		var id = parseFloat($("#add_rows_id_revised_remittance").val());
		for(var i=1; i<=id; i++){
			if($("#revised_dispatch_status_"+i).val()=='' || $("#revised_remittance_date_"+i).val()==''){
				alert("पंक्ति संख्या "+i+" खाली है");
				$("#revised_dispatch_status_"+i).focus();
				return;
			}
		}
		id = id+1;

		var date_1 = DateInput('revised_remittance_date_'+id, 'user_form', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', 1);

		var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="" >'+id+'<div class="col-md-3"><div class="form-group"> <label> पुनरीक्षित प्रेषण की स्थिति </label><input type="text" name="revised_dispatch_status_'+id+'" id="revised_dispatch_status_'+id+'" class="form-control" ></div></div><div class="col-md-3 "><div class="form-group"><label>धनराशि ( लाख में )</label><input type="text" name="revised_remittance_amount_'+id+'" id="revised_remittance_amount_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-3"><div class="form-group"><label>पुनरीक्षित प्रेषण की तिथि </label><input type="date" name="revised_remittance_date_'+id+'" id="revised_remittance_date_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows_revised_remittance()">Add</button></div></div>';
		$("#revised_remittance").append(txt);
		$("#add_rows_id_revised_remittance").val(id);
	}	
	
	function add_rows_uc(){
		var id = parseFloat($("#add_rows_id_uc").val());
		if(!id){
			id=0;
		}
		for(var i=1; i<=id; i++){
			if($("#uc_update_status_"+i).val()=='' || $("#uc_date_"+i).val()==''){
				alert("पंक्ति संख्या "+i+" खाली है");
				$("#uc_update_status_"+i).focus();
				return;
			}
		}
		id = id+1;

		var date_1 = DateInput('uc_date_'+id, 'user_form', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', 1);

		var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="" >'+id+'<div class="col-md-3"><div class="form-group"> <label> उपयोगिता  प्रमाण  पत्र की अद्यतन स्थिति </label><input type="text" name="uc_update_status_'+id+'" id="uc_update_status_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-3 "><div class="form-group"><label>धनराशि ( लाख में )</label><input type="text" name="uc_amount_'+id+'" id="uc_amount_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-3"><div class="form-group"><label> तिथि </label><input type="date" name="uc_date_'+id+'" id="uc_date_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows_uc()">Add</button></div></div>';
		$("#uc").append(txt);
		$("#add_rows_id_uc").val(id);
	}	
	
var actionUrl = 'scripts/ajax.php';

function fill_sub_department(val, selected){
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
				if(selected==value.id){
					txt += ' selected ';
				}
				txt += '>'+value.sub_department_hindi+'</option>';
				
			});
          	$("#sub_department_id").html(txt);
        }
    });
}

function fill_district(val, selected){
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
				if(selected==value.id){
					txt += ' selected ';
				}
				txt += '>'+value.district_name+'</option>';
				
			});
          	$("#district").html(txt);
        }
    });
}
	
function fill_project(val, selected){
	var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department").val()};
	$.ajax({
        type: "POST",
        url: actionUrl,
        data: data, // serializes the form's elements.
        success: function(data){
			var txt = '<option value="">--Select--</option>';
			data = JSON.parse(data);
			$.each(data, function(key, value){
				txt += '<option value="'+value.id+'" ';
				if(selected==value.id){
					txt += ' selected ';
				}
				txt += '>'+value.project_name_hindi+'</option>';
				
			});
          	$("#project_name").html(txt);
        }
    });
}
	
// function fill_project_details(val){
	// var data = {"term":"b", "id":"proj_detail", "val":val, "dept":$("#department").val()};
	// $.ajax({
        // type: "POST",
        // url: actionUrl,
        // data: data, // serializes the form's elements.
        // success: function(data){
			// var txt = '<option value="">--Select--</option>';
			// data = JSON.parse(data);
          	// $("#original_project_cost").val(data.sanction_cost);
          	// $("#date_of_acceptance_revised_cost").val(data.revised_date);
          	// $("#revised_cost").val(data.revised_cost);
          	// $("#date_of_acquisition_of_land").val(data.land_receive_date);
          	// $("#work_start_date").val(data.work_start_date);
          	// $("#work_completion_date").val(data.work_completion_date);
          	// $("#technical_approval_date").val(data.technical_sanction_date);
          	// $("#technical_approval_no").val(data.technical_sanction_no);
          	// $("#tender_status").val(data.tender_status);
          	// $("#administrative_mandate_no").val(data.admin_go_no);
          	// $("#administrative_mandate_date").val(data.admin_go_date);
          	// $("#financial_mandate_no").val(data.financial_go_no);
          	// $("#financial_mandate_date").val(data.financial_go_date);
          	// $("#financial_mandate_amount").val(data.financial_go_amount);
          	// // $("#total_expenditure_last_financial_year").val(data.last_fy_tot_exp);
          	// // $("#expenditure_till_last_month_current_financial_year").val(data.current_fy_last_month_exp);
          	// // $("#expenditure_in_current_month").val(data.current_month_exp);
          	// // $("#total_expenditure_current_financial_year").val(data.current_fy_tot_exp);
          	// // $("#total_expenditure_on_the_project").val(data.tot_exp_project);
          	// // $("#balance_amount").val(data.balance_amt_cost);
          	// // $("#balance_amount_on_project").val(data.balance_amt_project);
          	// $("#payable_gst_on_project").val(data.payable_gst_on_project);
          	// $("#architect").val(data.architect_id);
          	// $("#structural_architect").val(data.structural_architect_id);
          	// $("#soil_testing_status").val(data.soil_testing_status);
          	// $("#estimation_formation_status").val(data.estimation_formation_status);
          	// $("#project_status_1").val(data.project_status_1);
          	// $("#project_status_2").val(data.project_status_2);
          	// $("#remark").val(data.remark);
          	// $("#junior_engineer_name").val(data.junior_engineer_name);
          	// $("#assistant_engineer_name").val(data.assistant_engineer_name);
          	// $("#executive_engineer_name").val(data.executive_engineer_name);
          	// $("#executive_engineer_from").val(data.executive_engineer_from);
          	// $("#executive_engineer_to").val(data.executive_engineer_to);
          	// $("#superintendent_engineer_name").val(data.superintendent_engineer_name);
          	// $("#superintendent_engineer_from").val(data.superintendent_engineer_from);
          	// $("#superintendent_engineer_to").val(data.superintendent_engineer_to);
          	// // $("#revised_estimate_amount_1").val(data.revised_estimate_amount);
          	// // $("#revised_estimate_remark_1").val(data.revised_estimate_remark);
          	// // $("#revised_estimate_date_1").val(data.revised_estimate_date);
          	// // $("#revised_dispatch_status_1").val(data.revised_dispatch_status);
          	// // $("#revised_remittance_amount_1").val(data.revised_remittance_amount);
          	// // $("#revised_remittance_date_1").val(data.revised_remittance_date);
        // }
    // });
// }

<?php
	if(isset($_GET['id'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
		fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
		fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['id']; ?>);
		//fill_project_details(<?php echo $_POST['id']; ?>);
		
	});
	
<?php
	}
?>

<?php
	if(isset($_GET['edit_sno'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
		fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
		fill_project(<?php echo $_POST['district']; ?>, <?php echo $invoice['project_name']; ?>);
		//fill_project_details(<?php echo $invoice['project_name']; ?>);
		
	}); 
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>

<script>
// 	// Define the weights and completion percentages for each work
// var id = parseFloat($("#add_rows_id").val());
// const weights = [];
// const completionPercentages_array = [];

// for (let d = 1; d <= id; d++) {
//     let weight = parseFloat($('#weightage_' + d).val()) || 0;
//     let completionPercentages = parseFloat($('#physical_details_' + d).val()) || 0;

//     // Ensure that weight is a valid number, otherwise set it to 0
//     if (isNaN(weight)) {
//         weight = 0;
//     }

//     weights.push(weight);
//     completionPercentages_array.push(completionPercentages);
// }

// // Function to calculate overall completion percentage
// function calculateOverallCompletion(weights, completionPercentages) {
//     const weightedSum = weights.reduce((sum, weight, index) => {
//         const completion = completionPercentages[index];
//         // Check if completion is a valid number, otherwise set it to 0
//         const validCompletion = isNaN(completion) ? 0 : completion;
//         return sum + weight * validCompletion;
//     }, 0);

//     const totalWeight = weights.reduce((sum, weight) => sum + weight, 0);

//     return (totalWeight === 0) ? 0 : (weightedSum / totalWeight).toFixed(2);
// }

// // Calculate and print the overall completion percentage
// const overallCompletionPercentage = calculateOverallCompletion(weights, completionPercentages_array);
// document.getElementById('total_physical_per').value = overallCompletionPercentage;
// console.log(`Overall Completion Percentage: ${overallCompletionPercentage}%`);

</script>
