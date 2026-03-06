<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();


	if(isset($_POST['submit'])){
		if($_POST['edit_sno']==''){
			
		$sql = 'insert into invoice_fund_receive (`fund_receive_type` ,`fund_receive_to` ,`installment` ,`order_no` ,`order_date` ,`receive_date` ,`tot_receive_amount`, tds_per, `tds_deducted`, gst_tds_per , `gsttds_deducted`, labour_sess, `bank_name` ,`remark` ,`status` ,`created_by` ,`creation_time`, voucher_no ) 
		
		values ("'.$_POST['fund_receive_type'].'", "'.$_POST['fund_receive_to'].'", "'.$_POST['installment'].'", "'.$_POST['order_no'].'", "'.$_POST['order_date'].'", "'.$_POST['receive_date'].'", "'.$_POST['tot_receive_amount'].'", "'.$_POST['tds_per'].'", "'.$_POST['tds_deducted'].'", "'.$_POST['gst_tds_per'].'", "'.$_POST['gsttds_deducted'].'", "'.$_POST['labour_sess'].'", "'.$_POST['bank_name'].'", "'.$_POST['remark'].'", "0","'.$_SESSION['usersno'].'","'.date("Y-m-d H:i:s").'", "'.$_POST['voucher_no'].'" )';	
		
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			
			$inv_id = mysqli_insert_id($db);
			for($i=1; $i<=$_POST['add_rows_id']; $i++){
				//print_r($_POST['add_rows_id']);	
				$sql= ' insert into transaction_fund_receive (invoice_id, department_id, sub_department_id, district_id, project_id, p_receive_amount , status, created_by, creation_time) values ("'.$inv_id.'", "' .$_POST['department_id_'.$i].'", "'.$_POST['sub_department_id_'.$i].'","' .$_POST['district_id_'.$i].'","' .$_POST['project_id_'.$i].'", "'.$_POST['p_receive_amount_'.$i].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'")';
				//echo $sql;
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
			
			}
		}
		if($msg==''){
			//print_r($_POST);

				$sql='SELECT * FROM `uprnss_department_name` where sno="'.$_POST['department_id_1'].'"';
				$first_to = mysqli_fetch_assoc(execute_query($sql));
 
				
			if(isset($_POST['unit_id']) && $_POST['unit_id']!="") {
				$sql = 'insert into billit_invoice_erp_receipt (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, remarks, unit_id, created_by, creation_time, table_name, table_id) values ("'.$_POST['receive_date'].'", "'.$_POST['bank_name'].'", "'.$first_to['ledger_id'].'", "'.$_POST['tot_receive_amount'].'", "'.$_POST['tot_receive_amount'].'", "", "'.$_POST['voucher_no'].'","'.$_POST['remark'].'", "'.$_POST['unit_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'","invoice_fund_receive", "'.$inv_id.'" )';
			}else{
				$sql = 'insert into billit_invoice_erp_receipt (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, remarks, created_by, creation_time, table_name, table_id, unit_id) values ("'.$_POST['receive_date'].'", "'.$_POST['bank_name'].'", "'.$first_to['ledger_id'].'", "'.$_POST['tot_receive_amount'].'", "'.$_POST['tot_receive_amount'].'", "", "'.$_POST['voucher_no'].'", "'.$_POST['remark'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'","invoice_fund_receive", "'.$inv_id.'","53" )';
			}
			execute_query($sql);

			$id_stock = mysqli_insert_id($db);
			$sql = 'select * from general_settings where `desc`="CGST9"';
			$cgst9 = mysqli_fetch_assoc(execute_query($sql));
			
			$sql = 'select * from general_settings where `desc`="SGST9"';
			$sgst9 = mysqli_fetch_assoc(execute_query($sql));
			
			$sql = 'select * from general_settings where `desc`="CGST6"';
			$cgst6 = mysqli_fetch_assoc(execute_query($sql));
			
			$sql = 'select * from general_settings where `desc`="SGST6"';
			$sgst6 = mysqli_fetch_assoc(execute_query($sql));
			
			$sql = 'select * from general_settings where `desc`="GSTTDS"';
			$gsttds = mysqli_fetch_assoc(execute_query($sql));
			
			$sql = 'select * from general_settings where `desc`="ITTDS"';
			$ittds = mysqli_fetch_assoc(execute_query($sql));
			
			$sql = 'select * from general_settings where `desc`="LABORCESS"';			
			$laborcess = mysqli_fetch_assoc(execute_query($sql));
			
			
			// amount credit in bank
			$sql = 'insert into billit_stock_erp_receipt (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$_POST['bank_name'].'", "'.$_POST['tot_credit_amount'].'", "'.$_POST['receive_date'].'", "", "")';
			execute_query($sql);
			
			// TDS DEDUCTED BY DEPARTMENT
			$sql = 'insert into billit_stock_erp_receipt (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$ittds['rate'].'",  "'.$_POST['tds_deducted'].'", "'.$_POST['receive_date'].'", "", "")';
			execute_query($sql);
			
			// GST-TDS DEDUCTED BY DEPARTMENT
			$sql = 'insert into billit_stock_erp_receipt (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$gsttds['rate'].'",  "'.$_POST['gsttds_deducted'].'", "'.$_POST['receive_date'].'", "", "")';
			execute_query($sql);
			
			// LABOUR CESS DEDUCTED BY DEPARTMENT
			$sql = 'insert into billit_stock_erp_receipt (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$laborcess['rate'].'", "'.$_POST['labour_sess'].'", "'.$_POST['receive_date'].'", "", "")';
			execute_query($sql);
			
			// CREDIT BY DEPARTMENT
			$sql = 'insert into billit_stock_erp_receipt (`journal_id`, `to`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$first_to['ledger_id'].'", "'.$_POST['tot_receive_amount'].'", "'.$_POST['receive_date'].'", "", "")';
			execute_query($sql);
			
			
			// $sql = 'insert into billit_stock_journal (`journal_id`, `by`, `to`, amount, timestamp, remarks, challan_no, unit_id, status) values ("'.$id_stock.'", "", "'.$_POST['account_'.$i.'_sno'].'", "'.$_POST['credit_'.$i].'", "'.$_POST['sale_date'].'", "'.$_POST['description_'.$i].'", "'.$_POST['challan_no'].'", "'.$_POST['unit_id'].'", "'.$status.'")';
			// execute_query($sql);
			

			if(mysqli_error($db)){

				$msg .= '<div class="alert alert-danger">Error # 1.369 >> '.$sql.'</div>';

			}else{
				
				$msg .= '<p class="text text-success">Successfully Add</p>';
				unset($_POST);
				goto postblank;
			}
			
	
		}
	}
		else{
			$sql = 'update invoice_fund_receive set
		    `project_id` ="'.$_POST['project_id'].'",
			`fund_receive_to` ="'.$_POST['fund_receive_to'].'",
			`installment` ="'.$_POST['installment'].'",
			`order_no` ="'.$_POST['order_no'].'",
			`order_date` ="'.$_POST['order_date'].'",
			`receive_date` ="'.$_POST['receive_date'].'",
			`receive_amount` ="'.$_POST['receive_amount'].'",
			`tds_deducted` ="'.$_POST['tds_deducted'].'",
			`gsttds_deducted` ="'.$_POST['gsttds_deducted'].'",
			`bank_name` ="'.$_POST['bank_name'].'",
			`account_no` ="'.$_POST['account_no'].'",
			`ifsc_code` ="'.$_POST['ifsc_code'].'",
		
			`edited_by` ="'.$_SESSION['username'].'",
			`edition_time` ="'.date("Y-m-d H:i:s").'"
		
			where sno="'.$_POST['edit_sno'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data Update</p>';
		
		}
	}	
}
else{
	
	postblank:
		
		
		$_POST['fund_receive_type']= '';
		
		$_POST['installment']= ''; 
		$_POST['order_no']= '';
		$_POST['order_date']= date("Y-m-d"); 
		$_POST['receive_date']= date("Y-m-d"); 
		$_POST['request_date']= date("Y-m-d");
		$_POST['tot_receive_amount']= '0.00';
		$_POST['tds_per']= ''; 
		$_POST['tds_deducted']= '0.00'; 
		$_POST['gst_tds_per']= ''; 
		$_POST['gsttds_deducted']= '0.00'; 
		$_POST['bank_name']= '';
		$_POST['account_no']= ''; 
		$_POST['ifsc_code']= '';  
		$_POST['request_amount']= '';  
		$_POST['request_no']= '';  
		$_POST['remark']= '';

		$_POST['labour_sess']= '0.00';
		$_POST['tot_credit_amount']= '0.00';
		$_POST['division_id']= '';
		$_POST['department_id_1']= '';
		$_POST['sub_department_id_1']= '';
		$_POST['district_id_1']= '';
		$_POST['project_id_1']= '';
		$_POST['p_receive_amount_1']= '';  
		$_POST['fund_receive_type']= '';  
		$_POST['add_rows_id']= 1;  
		$_POST['edit_sno'] = '';
		
		if($_SESSION['usertype']=="6" && $_SESSION['usertype']=="sadmin" ){
			$_POST['fund_receive_to']= 'HO';
		}elseif($_SESSION['usertype']=="9"){
			$_POST['fund_receive_to']= 'Unit';
		}else{
			$_POST['fund_receive_to']= '';
			
		}
		
}


if(isset($_GET['edit_sno'])){
	$sql = 'select * from invoice_fund_receive where sno="'.$_GET['edit_sno'].'"';
//	echo $sql;
	$data = mysqli_fetch_assoc(execute_query($sql));

	$_POST['division_id'] = $data['division_id'];
	$_POST['district_id'] = $data['district_id'];
	$_POST['project_id'] = $data['project_id'];
	$_POST['fund_receive_to'] = $data['fund_receive_to'];
	$_POST['installment'] = $data['installment'];
	$_POST['order_no'] = $data['order_no'];
	$_POST['order_date'] = $data['order_no'];
	$_POST['receive_date'] = $data['receive_date'];
	$_POST['receive_amount'] = $data['receive_amount'];
	$_POST['tds_per'] = $data['tds_per'];
	$_POST['tds_deducted'] = $data['tds_deducted'];
	$_POST['gst_tds_per'] = $data['gst_tds_per'];
	$_POST['gsttds_deducted'] = $data['gsttds_deducted'];
	$_POST['bank_name'] = $data['bank_name'];
	$_POST['account_no'] = $data['account_no'];
	$_POST['ifsc_code'] = $data['to_account_no'];
	
	$_POST['edit_sno'] = $data['sno'];
}

if(isset($_GET['delid'])){
	$sql1 = 'delete from invoice_fund_receive where sno="'.$_GET['delid'].'"';
	if (execute_query($sql1)) {
		
		$sql2 = 'DELETE FROM transaction_fund_receive WHERE invoice_id="' . $_GET['delid'] . '"';
		if (execute_query($sql2)) {
			$sql = 'select * from billit_invoice_erp_receipt where `table_id`="' . $_GET['delid'] . '"';
			$journal_id = mysqli_fetch_assoc(execute_query($sql));
			$_SESSION['rct_journal_id']=$journal_id['sno'];
			
			$sql3 = 'DELETE FROM billit_invoice_erp_receipt WHERE table_id="' . $_GET['delid'] . '"';
			if (execute_query($sql3)) {
				
				$sql4 = 'DELETE FROM billit_stock_erp_receipt WHERE journal_id="' . $_SESSION['rct_journal_id'] . '"';
				if (execute_query($sql4)) {
					
					$msg .= '<p class="text text-danger">Data Deleted from all tables.</p>';
				} else {
					$msg .= '<p class="text text-danger">Failed to delete from fourth_table.</p>';
				}
			} else {
				$msg .= '<p class="text text-danger">Failed to delete from third_table.</p>';
			}
		} else {
			$msg .= '<p class="text text-danger">Failed to delete from another_table.</p>';
		}
	} else {
		$msg .= '<p class="text text-danger">Failed to delete from invoice_fund_receive.</p>';
	}
}


?>		
	<script>
		function readonly_input() {
			var type = document.getElementById("fund_receive_type");
			console.log(type);
			if (type.value === "1") {
				document.getElementById("tot_receive_amount").readOnly  = true;
			} else {	
				document.getElementById("tot_receive_amount").readOnly  = false;
			}
		}
		// document.getElementById("fund_receive_type").addEventListener("change", readonly);
		
		function onchangetype() {
			var type = document.getElementById("fund_receive_type");

			if (type.value == "1") {
				document.getElementById("hide_project_type").style.display = "flex";
				
			} else {
				document.getElementById("hide_project_type").style.display = "none";
				
			}
		}
		
		function fund_receive_to() {
			var type = document.getElementById("fund_receive_to");

			if (type.value == "Unit") {
				document.getElementById("unit").style.display = "flex";
				document.getElementById("bank_unit").style.display = "flex";
				document.getElementById("bank_ho").style.display = "none";
				
			} else {
				document.getElementById("unit").style.display = "none";
				document.getElementById("bank_unit").style.display = "none";
				document.getElementById("bank_ho").style.display = "flex";
				
			}
		}

	  window.onload = function() {
		onchangetype(); 
			document.getElementById("fund_receive_type").onchange = onchangetype;
			
		fund_receive_to(); 
			document.getElementById("fund_receive_to").onchange = fund_receive_to;
	  };
	</script>
	<form id="sale_form" name="sale_form" class="no-print" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">	
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title"></h4>
						<?php echo $msg; ?>
							<div class="text-right">
								<a href="billit_receipts_report.php?view=view"><button type="button" class="btn btn-warning">View Vouchers (Recive Report)</button></a>
							</div>
					</div>
					<div class="card-body">
						<h5>Fund Received </h5>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label>Fund Received type</label>
									<select name="fund_receive_type" id="fund_receive_type" class="form-control" onchange="onchangetype(), readonly_input()" tabindex="<?php echo $tab++; ?>">
									
									<option value="1">Through Project</option>
									<option value="2">Other</option>
									</select>
								</div>
							</div>
							
							<div class="col-md-2" >
								<div class="form-group">
									<label>Fund Received to</label>
									<select name="fund_receive_to" id="fund_receive_to" class="form-control" " tabindex="<?php echo $tab++; ?>" onchange="fund_receive_to()" <?php if($_SESSION['usertype']=="9"){ echo  "readonly"; } ?> >
										<option value="HO" <?php echo ($_POST['fund_receive_to']=='HO'?'selected':''); ?>>HO</option>
										<option value="Unit" <?php echo ($_POST['fund_receive_to']=='Unit'?'selected':''); ?>>UNIT</option>
										
									</select>
								</div>
							</div>
							<div class="col-md-3" id="unit">
								<div class="form-group">
									<label>Unit Name</label>
									<?php
									  //print_r($_SESSION);
									  $options = '';
									  if(is_array($_SESSION['divisions'])){
										  foreach($_SESSION['divisions'] as $k=>$v){
											 
											  $options .= '<option value="'.$v.'">'.get_division($v).'</option>';
										}
									  }?>

									<select name="unit_id" id="unit_id" class="form-control" onChange="fill_bank_details(this.value)">
										<option value="">-select-</option>
										<?php echo $options; ?>
									</select>
								</div>
							</div>	
							
							<div class="col-md-3">
								<div class="form-group">
									<label >Installment</label>
									<select name="installment" id="installment" class="form-control"  tabindex="<?php echo $tab++; ?>" >
									<option value="">Select--</option>
									<option value="1" <?php echo ($_POST['installment']==1?'selected':''); ?>>I Installment</option>
									<option value="2" <?php echo ($_POST['installment']==2?'selected':''); ?>>II Installment</option>
									<option value="3" <?php echo ($_POST['installment']==3?'selected':''); ?>>III Installment</option>
									<option value="4" <?php echo ($_POST['installment']==4?'selected':''); ?>>IV Installment</option>
									<option value="5" <?php echo ($_POST['installment']==5?'selected':''); ?>>V Installment</option>
									<option value="6" <?php echo ($_POST['installment']==6?'selected':''); ?>>VI Installment</option>
									<option value="7" <?php echo ($_POST['installment']==7?'selected':''); ?>>VII Installment</option>
									<option value="8" <?php echo ($_POST['installment']==8?'selected':''); ?>>VIII Installment</option>
									<option value="9" <?php echo ($_POST['installment']==9?'selected':''); ?>>IX Installment</option>
									<option value="10" <?php echo ($_POST['installment']==10?'selected':''); ?>>X Installment</option>
									</select>
								</div>
							</div>
							
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label >Order No.(GO)</label>
									<input type="text" name="order_no" id="order_no" class="form-control" placeholder="" value="<?php echo $_POST['order_no']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label >Voucher Number</label>
									<input type="text" name="voucher_no" id="request_amount" class="form-control" placeholder="" value="<?php //echo $_POST['request_amount']; ?>" tabindex="<?php //echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label >Order Date</label>
									<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('order_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['order_date']; ?>', <?php echo $tab; $tab+=4;?>));
									</script>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label >Received Date</label>
									<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('receive_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['receive_date']; ?>', <?php echo $tab; $tab+=4;?>));
									</script>
								</div>
							</div>
						</div>
						<div class="card" id="hide_project_type" style="display:none;">
							<div class="card-header">
								<h4 class="card-title"></h4>
							</div>
							<div class="card-body">
								<input type="hidden" name="division_id" id="division_id" class="form-control" placeholder="" value="<?php echo $_POST['division_id']; ?>" readonly tabindex="<?php echo $tab++; ?>">
								<div class="border rounded m-2 p-2 border-secondary" id="">
									<?php
										for($i=1;$i<=$_POST['add_rows_id'];$i++){
									?><?php echo $i; ?>.
									<div id="add_rows_length" class="row">
										<div class="col-md-2 ">
											<div class="form-group">
												<label >विभाग</label><br>
												<select class="form-control" name="department_id_<?php echo $i; ?>" id="department_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value, 1), fill_sub_department(this.value, 1)">
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
										<div class="col-md-2">
											<div class="form-group">
												<label >उप विभाग</label>
												<select class="form-control" name="sub_department_id_<?php echo $i; ?>" id="sub_department_id_<?php echo $i; ?>" value="<?php echo $_POST['sub_department_id_'.$i]; ?>" tabindex="<?php echo $tab++; ?>" >
												</select>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label >आच्छादित जनपद</label>
												<select class="form-control" name="district_id_<?php echo $i; ?>" id="district_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value, 1)">
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>परियोजना का नाम <span id="ledger_link"></span></label><br>
												<select class="form-control" name="project_id_<?php echo $i; ?>" id="project_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" onChange="view_ledger(this.value)">
												</select>
												
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label>परियोजना पर प्राप्त  राशि </label><br>
												<input type="text" name="p_receive_amount_<?php echo $i; ?>" id="p_receive_amount_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['p_receive_amount_'.$i]; ?>" tabindex="<?php echo $tab++; ?>" onInput="addCalc(<?php echo $i; ?>)">
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
							</div>
						</div>
						<div class="row">
							
							<div class="col-md-4">
								<div class="form-group">
									<label >Total Received Amount</label>
									<input type="text" onInput="percent_amt_calc()" type="text" name="tot_receive_amount" id="tot_receive_amount" class="form-control" placeholder="" value="<?php echo $_POST['tot_receive_amount']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label >Total Credit Amount in Bank</label>
									<input readOnly type="text" name="tot_credit_amount" id="tot_credit_amount" class="form-control" placeholder="" value="<?php echo $_POST['tot_credit_amount']; ?>" tabindex="<?php echo $tab++; ?>">
								</div>
							</div>
							<div class="col-md-4" id="bank_ho">
								<div class="form-group">
									<label for=""> Bank Details</label>
									<select class="form-control" name="bank_name" id="bank_name"  tabindex="<?php echo $tab++; ?>">
											<option value="">--- Select ---</option>
											<?php
											$query ='select * from billit_customer where parent ="1" and unit_id="53"';
											// echo $query;
											$run = mysqli_query($db,$query);
											while($data = mysqli_fetch_array($run)){
												echo '<option value="'.$data['sno'].'" ';
												if(isset($_POST['bank_name'])){
													if($_POST['bank_name']==$data['sno']){
														echo ' selected="Selected"';
													}
												}
												echo '>'.trim($data['cus_name']).'</option>';

											}
											?>
									</select>
								</div>
							</div>
							<div class="col-md-4" id="bank_unit">
								<div class="form-group" >
									<label for=""> Bank Details</label>
									<select class="form-control" name="bank_name" id="bank_name_unit"  tabindex="<?php echo $tab++; ?>" >
											
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-1">
								<div class="form-group">
									<label>Percentage</label>
									<input type="text" name="tds_per" id="tds_per" class="form-control" value="<?php echo $_POST['tds_per']; ?>" tabindex="<?php echo $tab++; ?>" onInput="percent_amt_calc()">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label for="">TDS Deducted</label>
									<input  type="text" name="tds_deducted" id="tds_deducted" class="form-control" placeholder="" value="<?php echo $_POST['tds_deducted']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
								</div>
							</div>
							<div class="col-md-1">
								<div class="form-group">
									<label>Percentage</label>
									<input type="text" name="gst_tds_per" class="form-control" id="gst_tds_per" value="<?php echo $_POST['gst_tds_per']; ?>" tabindex="<?php echo $tab++; ?>" onInput="percent_amt_calc()">
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label for="">GST-TDS Deducted</label>
									<input  type="text" name="gsttds_deducted" id="gsttds_deducted" class="form-control" placeholder="" value="<?php echo $_POST['gsttds_deducted']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label for="">Labour Cess</label>
									<input type="text" name="labour_sess" id="labour_sess" class="form-control" placeholder="" value="<?php echo $_POST['labour_sess']; ?>" tabindex="<?php echo $tab++; ?>" onInput="percent_amt_calc()">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label >Remark </label>
								   <textarea type="text" name="remark" id="remark" class="form-control" placeholder=""  tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['remark']; ?> </textarea>
								</div>
							</div>
						</div>
						<div class="col-md-11 pr-1"  align = "center">
							<div class="form-group">
							<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
							<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
			
		
	</form>	
	
	<div class="row">
		<div class="col-md-12">
			<div class="card strpied-tabled-with-hover">
				<div class="card-header ">
				</div>
				<div class="card-body table-full-width table-responsive">
					
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Voucher No.</th>
								<th>Fund Recevied at</th>
								<th>Order No</th>
								<th>Order Date</th>
								<th>Installment</th>
								<th>Tot Received Amount</th>
								<th>Remark</th>
								<th class="no-print">View Details</th>
								<th class="no-print">View Voucher</th>
								
								
							</tr>	
						</thead>
						<tbody>
							<?php
							$sql = 'SELECT * FROM `invoice_fund_receive` ORDER BY sno desc';
							// echo $sql;
							$result = execute_query($sql);
							$i=1;
							while($row = mysqli_fetch_assoc($result)){	
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row['voucher_no'].'</td>
								<td>'.$row['fund_receive_to'].'</td>
								
								<td>'.$row['order_no'].'</td>
								<td>'.$row['order_date'].'</td>
								<td>'.$row['installment'].'</td>
								<td>'.$row['tot_receive_amount'].'</td>';
								echo'<td>'.$row['remark'].'</td>
								<td class="no-print"><a href="fund_recive_details.php?id='.$row['sno'].'" target="_blank">View</a></td>
								
								<td class="no-print"><a href="billit_recive_print.php?voucher='.$row['sno'].'" target="_blank">View</a></td>
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

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>	
		
	function percent_amt_calc(){
	// Get the values from the respective elements

		let totamount = document.getElementById('tot_receive_amount').value;
		let tdsper=document.getElementById('tds_per').value;
		let tdsdeducted=document.getElementById('tds_deducted');

		let gsttdsper=document.getElementById('gst_tds_per').value;
		let gsttdsdeducted=document.getElementById('gsttds_deducted');

		let laboursess = document.getElementById('labour_sess');

		let totcreditamount = document.getElementById('tot_credit_amount')


		tdsdeducted.value=((totamount*tdsper)/100).toFixed(0);
		gsttdsdeducted.value=((totamount*gsttdsper)/100).toFixed(0);
		totcreditamount.value=totamount-tdsdeducted.value-gsttdsdeducted.value-laboursess.value;


		
	}


	
	function addCalc(line_serial){
		var id = parseFloat($("#add_rows_id").val());
		var tot=0;
		for(i=1; i<=id; i++){
			var amt = $('#p_receive_amount_'+i).val();
			amt = parseFloat(amt);
			if(!amt){
				amt = 0;
			}
			tot += amt;
			console.log("AMT : "+amt+" : ID: "+i);
		}
		$("#tot_receive_amount").val(tot);
		percent_amt_calc();
		
		
	}			

	$('select[multiple]').multiselect();
		
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
				
				var txt = id+'<div id="add_rows_length" class="row"><div class="col-md-2 "><div class="form-group"><label >विभाग</label><br><select class="form-control" name="department_id_'+id+'" id="department_id_'+id+'" onChange="fill_district(this.value, '+id+'),fill_sub_department(this.value, '+id+')"><option value="">--- Select ---</option>';
				<?php
				if(!empty($_SESSION['department'])){
					$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in ('.implode(",", $_SESSION['department']).') group by department_id) ';
				}
				elseif(!empty($_SESSION['divisions'])){
					$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in ('.implode(",", $_SESSION['divisions']).') group by division_id ) ';
				}
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['department_name_hindi'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-2"><div class="form-group"><label >उप विभाग</label><select class="form-control" name="sub_department_id_'+id+'" id="sub_department_id_'+id+'" value="" ></select></div></div><div class="col-md-2"><div class="form-group"><label >आच्छादित जनपद</label><select class="form-control" name="district_id_'+id+'" id="district_id_'+id+'" onChange="fill_project(this.value, '+id+')"></select></div></div><div class="col-md-3"><div class="form-group"><label>परियोजना का नाम</label><br><select class="form-control" name="project_id_'+id+'" id="project_id_'+id+'" ></select></div></div><div class="col-md-2"><div class="form-group"><label>परियोजना पर प्राप्त  राशि </label><br><input type="text" name="p_receive_amount_'+id+'" id="p_receive_amount_'+id+'" class="form-control" placeholder="" value="" onInput="addCalc('+id+')"></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
				$("#test").append(txt);
				$("#add_rows_id").val(id);
			}
	
		var actionUrl ="scripts/ajax.php";

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
					$("#sub_department_id_"+selected).html(txt);
				}
			});
		}

		function fill_district(val, selected){
			// alert(selected);
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
					$("#district_id_"+selected).html(txt);
				}
			});
		}
			
		function fill_project(val, selected){
			//"department_1";
			
			var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department_id_"+selected).val()};
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
					$("#project_id_"+selected).html(txt);
				}
			});
		}
		
	function view_ledger(val){
		var txt = '<a href="report_ledger_project.php?id='+val+'" target="_blank"><small>View Ledger</small></a>';
		$("#ledger_link").html(txt);
		
	}

	
	
	function fill_bank_details(val, selected){
		var data = {"term":"b", "id":"unit_bank", "val":val};
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
				success: function(ajaxdata){
					//console.log(ajaxdata);
					var txt = '<option value="">--Select--</option>';
					ajaxdata = JSON.parse(ajaxdata);
					$.each(ajaxdata, function(key, value){
						txt += '<option value="'+value.id+'" ';
						if(value.id==selected){
							txt += ' selected="selected" ';
						}
						txt += '>'+value.cus_name+'</option>';
						
					});
					$("#bank_name_unit").html(txt);
				}
			});
	}
	
	


<?php
	if(isset($_GET['edit_sno'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
		fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district_id']; ?>);
		fill_project(<?php echo $_POST['district_id']; ?>, <?php echo $invoice['project_id']; ?>);
		//fill_project_details(<?php echo $invoice['project_id']; ?>);
		
	}); 
<?php
	}
?>
	
</script>

    
<?php		
page_footer_end();
?>
