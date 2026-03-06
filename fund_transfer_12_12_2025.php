<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";


	if(isset($_POST['submit'])){
		if($_POST['edit_sno']==''){
			
			$unit='select division_id from uprnss_project_temp where sno="'.$_POST['project_name'].'"';
			$unit_row = mysqli_fetch_assoc(execute_query($unit));
			$sql = "INSERT INTO `invoice_account_fund_transafer`(
				`department`, `sub_department_id`, `district`,unit_id, `project_name`, `fund_transfer_to`, `order_no`, `order_date`, `transafer_date`, `transafer_amount`, `gstdeduction`, `totelmgst`, `sentagepercentage`, `sentage`, `gsttdspercentage`, `gsttds`, `leborses`, `incometax`, `praposemoney`, `remark`, `from_account_no`, `to_bank_name`, `to_bank_ifsc`, `to_account_no`, `status`, `created_by`, `creation_time`
				)
			values (
			'{$_POST['department']}','{$_POST['sub_department_id']}','{$_POST['district']}','{$unit_row['division_id']}','{$_POST['project_name']}','{$_POST['fund_transfer_to']}','{$_POST['order_no']}', '{$_POST['order_date']}', '{$_POST['transafer_date']}', '{$_POST['transafer_amount']}', '{$_POST['gstdeduction']}', '{$_POST['totelmgst']}', '{$_POST['sentagepercentage']}', '{$_POST['sentage']}', '{$_POST['gsttdspercentage']}', '{$_POST['gsttds']}', '{$_POST['leborses']}', '{$_POST['incometax']}', '{$_POST['praposemoney']}', '{$_POST['remark']}', '{$_POST['from_account_no']}','{$_POST['to_bank_name']}','{$_POST['to_bank_ifsc']}','{$_POST['to_account_no']}','0','".$_SESSION['username']."','".date("Y-m-d H:i:s")."')";
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			if($msg==''){
				//print_r($_POST);
			$inv_id = mysqli_insert_id($db);
					$sql='SELECT * FROM `uprnss_department_name` where sno="'.$_POST['department_id_1'].'"';
					$first_to = mysqli_fetch_assoc(execute_query($sql));
	 
					
				$sql = 'insert into billit_invoice_erp_payment (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, unit_id, created_by, creation_time, table_name, table_id) values ("'.$_POST['transafer_date'].'", "'.$_POST['to_bank_name'].'", "'.$_POST['from_account_no'].'", "'.$_POST['transafer_amount'].'", "'.$_POST['transafer_amount'].'", "", "'.$_POST['order_no'].'", "'.$_POST['unit_id'].'", "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'","invoice_account_fund_transafer", "'.$inv_id.'" )';
				execute_query($sql);

				$id_stock = mysqli_insert_id($db);
				$sql = 'select * from general_settings where `desc`="GSTW"';
				$gstw = mysqli_fetch_assoc(execute_query($sql));
				
				$sql = 'select * from general_settings where `desc`="ADVCEN"';
				$advcen = mysqli_fetch_assoc(execute_query($sql));
				
				$sql = 'select * from general_settings where `desc`="GSTTDSW"';
				$gsttds = mysqli_fetch_assoc(execute_query($sql));
				
				$sql = 'select * from general_settings where `desc`="LABOURCESSW"';
				$labourcessw = mysqli_fetch_assoc(execute_query($sql));
				
				$sql = 'select * from general_settings where `desc`="ITTDSW"';
				$ittds = mysqli_fetch_assoc(execute_query($sql));
				
				
				
				
				
				// amount in unit bank
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$_POST['to_bank_name'].'", "'.$_POST['praposemoney'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				// GST AMOUNT
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$gstw['rate'].'",  "'.$_POST['gstdeduction'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				// ADV centage 
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$advcen['rate'].'",  "'.$_POST['sentage'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				
				// GST-TDS 
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$gsttds['rate'].'",  "'.$_POST['gsttds'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				// LABOUR CESS 
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$labourcessw['rate'].'", "'.$_POST['leborses'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				// IT
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `by`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$ittds['rate'].'", "'.$_POST['incometax'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				
				
				// CREDIT BY Ho BANK
				$sql = 'insert into billit_stock_erp_payment (`journal_id`, `to`,  amount, timestamp, unit_id, status) values ("'.$id_stock.'", "'.$_POST['from_account_no'].'", "'.$_POST['transafer_amount'].'", "'.$_POST['transafer_date'].'", "", "")';
				execute_query($sql);
				
				if(mysqli_error($db)){

					$msg .= '<div class="alert alert-danger">Error # 1.369 >> '.$sql.'</div>';

				}else{
					
					$msg .= '<p class="text text-success">Successfully Add</p>';
					unset($_POST);
					goto postblank;
				}
				
		
			}
		}else{
			 $sql = 'UPDATE invoice_account_fund_transafer SET
			department="' . $_POST['department'] . '",
			sub_department_id="' . $_POST['sub_department_id'] . '",
			district="' . $_POST['district'] . '",
			project_name="' . $_POST['project_name'] . '",
			fund_transfer_to="' . $_POST['fund_transfer_to'] . '",
			order_no="' . $_POST['order_no'] . '",
			order_date="' . $_POST['order_date'] . '",
			transafer_date="' . $_POST['transafer_date'] . '",
			transafer_amount="' . $_POST['transafer_amount'] . '",
			gstdeduction="' . $_POST['gstdeduction'] . '",
			totelmgst="' . $_POST['totelmgst'] . '",
			sentagepercentage="' . $_POST['sentagepercentage'] . '",
			sentage="' . $_POST['sentage'] . '",
			gsttdspercentage="' . $_POST['gsttdspercentage'] . '",
			gsttds="' . $_POST['gsttds'] . '",
			leborses="' . $_POST['leborses'] . '",
			incometax="' . $_POST['incometax'] . '",
			praposemoney="' . $_POST['praposemoney'] . '",
			remark="' . $_POST['remark'] . '",
			from_account_no="' . $_POST['from_account_no'] . '",
			to_bank_name="' . $_POST['to_bank_name'] . '",
			to_bank_ifsc="' . $_POST['to_bank_ifsc'] . '",
			to_account_no="' . $_POST['to_account_no'] . '",
			edited_by="' . $_SESSION['username'] . '",
			edition_time="' . date("Y-m-d H:i:s") . '"
			WHERE sno="' . $_POST['edit_sno'] . '"';

			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			if($msg==''){
				$msg .= '<p class="alert alert-success">Data Update</p>';
				unset($_POST);
				goto postblank;
			
			}
		}	
	}
	else{
		
		postblank:
		$_POST['unit_id']="";
		$_POST['department']="";
		$_POST['sub_department_id']="";
		$_POST['district']="";
		$_POST['project_name']="";
		$_POST['fund_transfer_to']="";
		$_POST['order_no']="";
		$_POST['order_date']=date("d-m-Y");
		$_POST['transafer_date']=date("d-m-Y");
		$_POST['transafer_amount']="";
		$_POST['gstdeduction']="";
		$_POST['totelmgst']="";
		$_POST['sentagepercentage']="";
		$_POST['sentage']="";
		$_POST['gst_per']="";
		$_POST['gsttdspercentage']="";
		$_POST['gsttds']="";
		$_POST['leborses']="";
		$_POST['incometax']="";
		$_POST['it_per']="";
		$_POST['praposemoney']="";
		$_POST['remark']="";
		$_POST['from_account_no']="";
		$_POST['to_bank_name']="";
		$_POST['to_bank_ifsc']="";
		$_POST['to_account_no']="";
		$_POST['edit_sno'] = '';
	}


if(isset($_GET['edit_sno'])){
	$sql = 'select * from invoice_account_fund_transafer where sno="'.$_GET['edit_sno'].'"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	print_r($data);
	$_POST['department']=$data['department'];
	$_POST['sub_department_id']=$data['sub_department_id'];
	$_POST['district']=$data['district'];
	$_POST['project_name']=$data['project_name'];
	$_POST['fund_transfer_to']=$data['fund_transfer_to'];
	$_POST['order_no']=$data['order_no'];
	$_POST['order_data']=$data['order_data'];
	$_POST['transafer_data']=$data['transafer_data'];
	$_POST['transafer_amount']=$data['transafer_amount'];
	$_POST['gstdeduction']=$data['gstdeduction'];
	$_POST['totelmgst']=$data['totelmgst'];
	$_POST['sentagepercentage']=$data['sentagepercentage'];
	$_POST['sentage']=$data['sentage'];
	$_POST['gsttdspercentage']=$data['gsttdspercentage'];
	$_POST['gsttds']=$data['gsttds'];
	$_POST['leborses']=$data['leborses'];
	$_POST['incometax']=$data['incometax'];
	$_POST['praposemoney']=$data['praposemoney'];
	$_POST['remark']=$data['remark'];
	$_POST['from_account_no']=$data['from_account_no'];
	$_POST['to_bank_name']=$data['to_bank_name'];
	$_POST['to_bank_ifsc']=$data['to_bank_ifsc'];
	$_POST['to_account_no']=$data['to_account_no'];

	$_POST['edit_sno'] =  $data['sno'];
}

if(isset($_GET['del'])){
	//status=0 // fund transfer from md camp
	//status=5 //fund deleted by md camp
	$sql = 'UPDATE  invoice_account_fund_transafer SET status="5" where sno="'.$_GET['del'].'"';
	execute_query($sql);
	
	$msg1 .= '<p class="alert alert-danger">Data Deleted.</p>';
}

?>			

	<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
			<div class="card">
				<div class="card-header">
						<h4 class="card-title"></h4>
				</div>
				<?php echo $msg;?>
				<div class="text-right">
								<a href="billit_payment_report.php?view=view"><button type="button" class="btn btn-warning">View Vouchers (Payment Report)</button></a>
							</div>
				<div class="card-body">
					<h5>Fund Transafer </h5>
					<div class="row">
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
								<select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)">
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>परियोजना का नाम</label><br>
								<select class="form-control" name="project_name" id="project_name" tabindex="<?php echo $tab++; ?>" onChange="fill_division(this.value)">
								</select>
							</div>
						</div>
						
						<div class="col-md-3">
							<div class="form_group">
								<label >Fund Transfer To</label>
								<select class="form-control" name="fund_transfer_to" id="fund_transfer_to" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from transfer_to_type";
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_POST['fund_transfer_to'])){
											if($_POST['fund_transfer_to']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['transfer_to']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >Order No.(GO)</label>
								<input type="text" name="order_no" id="order_no" class="form-control" placeholder="" value="<?php echo $_POST['order_no']; ?>" tabindex="<?php echo $tab++; ?>">
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
								<label >Transafer Date</label>
								<script  type="text/javascript" language="javascript">
								document.writeln(DateInput('transafer_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['transafer_date']; ?>', <?php echo $tab; $tab+=4;?>));
								</script>
							</div>
						</div>
					
						<div class="col-md-3">
							<div class="form-group">
								<label >Transafer Amount</label>
								<input type="text" name="transafer_amount" id="transafer_amount" class="form-control" placeholder="" value="<?php echo $_POST['transafer_amount']; ?>" tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label >जीएसटी%</label>
								
								
								<select class="form-control" name="gst_per" id="gst_per"  tabindex="<?php echo $tab++; ?>"  onchange="findcalculation()">
									<option value="">--- Select ---</option>
									<option value="12">12%</option>
									<option value="18">18%</option>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >जीएसटी राशि</label>
								<input type="text" name="gstdeduction" id="gstdeduction" class="form-control" placeholder="" value="<?php echo $_POST['gstdeduction']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >अवशेष राशि </label>
								<input type="text" name="totelmgst" id="totelmgst" class="form-control" placeholder="" value="<?php echo $_POST['totelmgst']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label >अग्रिम सेंटेज % </label>
								<input type="text" name="sentagepercentage" id="sentagepercentage" class="form-control" placeholder="" value="<?php echo $_POST['sentagepercentage']; ?>" tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >अग्रिम सेंटेज राशि</label>
								<input type="text" name="sentage" id="sentage" class="form-control" placeholder="" value="<?php echo $_POST['sentage']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label >जीएसटी टीडीएस % </label>
								<input type="text" name="gsttdspercentage" id="gsttdspercentage" class="form-control" placeholder="" value="<?php echo $_POST['gsttdspercentage']; ?>" tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >जीएसटी टीडीएस राशि </label>
								<input type="text" name="gsttds" id="gsttds" class="form-control" placeholder="" value="<?php echo $_POST['gsttds']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label >लेबर सेस </label>
								<input type="text" name="leborses" id="leborses" class="form-control" placeholder="" value="<?php echo $_POST['leborses']; ?>" tabindex="<?php echo $tab++; ?>" oninput="findcalculation()"> 
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label >श्रोत पर की गई आयकर कटौती % </label>
								<input type="text" name="it_per" id="it_per" class="form-control" placeholder="" value="<?php echo $_POST['it_per']; ?>" tabindex="<?php echo $tab++; ?>" oninput="findcalculation()" >
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >श्रोत पर की गई आयकर कटौती धनराशि </label>
								<input type="text" name="incometax" id="incometax" class="form-control" placeholder="" value="<?php echo $_POST['incometax']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label >प्रखण्ड को प्रेषित की जाने वाली प्रस्तावित राशि</label>
								<input type="text" name="praposemoney" id="praposemoney" class="form-control" placeholder="" value="<?php echo $_POST['praposemoney']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						
						
						<!-- <div class="col-md-3 ">
							<div class="form-group">
								<label for="">TDS Deducted</label>
								<input type="text" name="tds_deducted" id="tds_deducted" class="form-control" placeholder=""value="<?php //echo $_POST['tds_deducted']; ?>" tabindex="<?php //echo $tab++; ?>">
							</div>
						</div> -->
						<!-- <div class="col-md-3">
							<div class="form-group">
								<label for="">TCS Deducted</label>
								<input type="text" name="tcs_deducted" id="tcs_deducted" class="form-control" placeholder="" value="<?php //echo $_POST['tcs_deducted']; ?>" tabindex="<?php //echo $tab++; ?>">
							</div>
						</div> -->
						<div class="col-md-3">
							<div class="form-group">
								<label >Remark </label>
							   <textarea type="text" name="remark" id="remark" class="form-control" placeholder=""  tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['remark']; ?> </textarea>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-4 ">
							<div class="form-group">
								<label>From Account </label>
								<select class="form-control" name="from_account_no" id="from_account_no"  tabindex="<?php echo $tab++; ?>" required>
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
						<div class="col-md-3">
							<div class="form-group">
								
								<input type="hidden" name="unit_id" id="unit_id" class="form-control" placeholder="" value="<?php echo $_POST['unit_id']; ?>" tabindex="<?php echo $tab++; ?>" onblur="fill_bank_details(this.value)">
							</div>
						</div>
						<div class="col-md-4" id="bank_unit">
							<div class="form-group" >
								<label for="">To Account</label>
								<select class="form-control" name="to_bank_name" id="bank_name_unit"  tabindex="<?php echo $tab++; ?>" >
										
								</select>
							</div>
						</div>
						<!--<div class="col-md-3 ">
							<div class="form-group">
								<label>To Bank Name</label>
								<input type="text" name="to_bank_name" id="to_bank_name" class="form-control" placeholder=""value="<?php echo $_POST['to_bank_name']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3 ">
							<div class="form-group">
								<label>To Ifsc Code</label>
								<input type="text" name="to_bank_ifsc" id="to_bank_ifsc" class="form-control" placeholder=""value="<?php echo $_POST['to_bank_ifsc']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3 ">
							<div class="form-group">
								<label>To Account Number</label>
								<input type="text" name="to_account_no" id="to_account_no" class="form-control" placeholder=""value="<?php echo $_POST['to_account_no']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>-->
					</div></br>
					<div class="row">
						<div class="col-md-12"  align = "center">
								<div class="form-group">
								<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
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
					<?php echo $msg1; ?>
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover " id="general_stat_table">
						<thead>
							<tr class="">
								<th>S.No.</th>
								<th>विभाग / <br> कार्य / <br> प्रखण्ड का नाम </th>
								<th>Fund Transfer To</th>
								<th>Order No.(G.O.)   Order Date </th>
								<th>Transfer Date  Transfer Amount</th>
								<th>GST 18%</th>
								<th>अवशेष राशि </th>
								<th>अग्रिम सेंटेज राशि  %</th>
								<th>जीएसटी टीडीएस राशि  % </th>
								<th>लेबर सेस</th>
								<th>आयकर </th>
								<th>प्रस्तावित राशि</th>
								<th>Remark</th>
								<th>From Acc. No.</th>
								<th>To Bank Name/ <br> IFSC Code </th>
								<th>To Account Number</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
						$sql = 'select * from invoice_account_fund_transafer WHERE status!="5" ';
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							
							$sql = 'SELECT uprnss_project_temp.sno,uprnss_project_temp.project_name_hindi, uprnss_project_temp.Project_name,uprnss_division.division_name FROM uprnss_project_temp LEFT JOIN uprnss_division on uprnss_project_temp.division_id=uprnss_division.s_no WHERE sno ="'.$row['project_name'].'"';
							$row_project = execute_query($sql);
							if(mysqli_num_rows($row_project)!=0){
								$row_project = mysqli_fetch_assoc($row_project);
							}
							else{
								unset($row_project);
								$row_project['project_name_hindi'] = '';
							}

							$sql = 'select * from uprnss_department_name where sno="'.$row['department'].'"';
							$row_dep = mysqli_fetch_assoc(execute_query($sql));
							$row_dep = execute_query($sql);
							if(mysqli_num_rows($row_dep)!=0){
								$row_dep = mysqli_fetch_assoc($row_dep);
							}
							else{
								unset($row_dep);
								$row_dep['department_name_hindi'] = '';
							}
							$sql = 'select * from transfer_to_type where sno="'.$row['fund_transfer_to'].'"';
							$status1 = execute_query($sql);
							if(mysqli_num_rows($status1)!=0){
								$status1 = mysqli_fetch_assoc($status1);
							}
							else{
								unset($status1);
								$status1['transfer_to'] = '';
							}

							$sql = 'select * from ho_bank_details where sno="'.$row['from_account_no'].'"';
							$status2 = execute_query($sql);
							if(mysqli_num_rows($status2)!=0){
								$status2 = mysqli_fetch_assoc($status2);
							}
							else{
								unset($status2);
								$status2['account_no'] = '';
							}
							
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row_dep['department_name_hindi']."".$row_project['project_name_hindi']."".$row_project['division_name'].'</td>
							<td>'.$status1['transfer_to'].'</td>
							<td>'.$row['order_no']." <hr> ".date("d-m-Y",strtotime($row['order_date'])).'</td>
							<td>'.$row['transafer_amount']."<hr>  ".date("d-m-Y",strtotime($row['transafer_date'])).'</td>
							<td>'.$row['gstdeduction'].'</td>
							<td>'.$row['totelmgst'].'</td>
							<td>'.$row['sentage']."<hr>".$row['sentagepercentage']."%".'</td>
							<td>'.$row['gsttds']."<hr>".$row['gsttdspercentage']."%".'</td>
							<td>'.$row['leborses'].'</td>
							<td>'.$row['incometax'].'</td>
							<td>'.$row['praposemoney'].'</td>
							<td>'.$row['remark'].'</td>
							<td>'.$status2['account_no'].'</td>
							<td>'.$row['to_bank_name']."<hr>".$row['to_bank_ifsc'].'</td>
							<td>'.$row['to_account_no'].'</td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?edit_sno='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');" alt="Edit Details" data-toggle="tooltip" title="Edit Details"><span class="far fa-edit" aria-hidden="true"></span></a></td>
							<td><a href="'.$_SERVER['PHP_SELF'].'?del='.$row['sno'].'" onclick="return confirm(\'Are you sure?\');" style="color:#f00" alt="Delete Entry"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete Entry"></span></a></td>
							</tr>';
						}
						?>
						</tbody>
					</table>
					
					</div>
                </div>
            </div>
		</div>	

					
	<script>
		//calculation of fund transfer
		function findcalculation(){
			//var transafer_amount=document.getElementById("transafer_amount").value;
			
			let transafer_amount = parseFloat(document.getElementById("transafer_amount").value);
							
			transafer_amount = parseFloat(transafer_amount);
			if(!transafer_amount){
				transafer_amount = 0;
			}
			
			//var totelmgst=document.getElementById("totelmgst");//transafer_amount-gstdeduction
			let totelmgst = parseFloat(document.getElementById("totelmgst").value);				
			totelmgst = parseFloat(totelmgst);
			if(!totelmgst){
				totelmgst = 0;
			}
			
			//var gst_per=document.getElementById("gst_per").value;//user put % here
			let gst_per = parseFloat(document.getElementById("gst_per").value);
							
			gst_per = parseFloat(gst_per);
			if(!gst_per){
				gst_per = 0;
			}
			
			//var gstdeduction=document.getElementById("gstdeduction")//18 %
			let gstdeduction = parseFloat(document.getElementById("gstdeduction").value);			
			gstdeduction = parseFloat(gstdeduction);
			if(!gstdeduction){
				gstdeduction = 0;
			}
			
			//var sentagepercentage=document.getElementById("sentagepercentage").value;//user put % here
			let sentagepercentage = parseFloat(document.getElementById("sentagepercentage").value);			
			sentagepercentage = parseFloat(sentagepercentage);
			if(!sentagepercentage){
				sentagepercentage = 0;
			}
			
			//var sentage=document.getElementById("sentage");////sentage % of transafer_amount 
			let sentage = parseFloat(document.getElementById("sentage").value);			
			sentage = parseFloat(sentage);
			if(!sentage){
				sentage = 0;
			}

			//var gsttdspercentage=document.getElementById("gsttdspercentage");
			let gsttdspercentage = parseFloat(document.getElementById("gsttdspercentage").value);			
			gsttdspercentage = parseFloat(gsttdspercentage);
			if(!gsttdspercentage){
				gsttdspercentage = 0;
			}
			
			//var gsttds=document.getElementById("gsttds");
			let gsttds = parseFloat(document.getElementById("gsttds").value);			
			gsttds = parseFloat(gsttds);
			if(!gsttds){
				gsttds = 0;
			}

			//var it_per=document.getElementById("it_per").value;//user put % here
			let it_per = parseFloat(document.getElementById("it_per").value);			
			it_per = parseFloat(it_per);
			if(!it_per){
				it_per = 0;
			}
			
			//var leborses=document.getElementById("leborses"); laborcess
			let leborses = parseFloat(document.getElementById("leborses").value);			
			if(!leborses){
				leborses = 0;
			}
			
			//var incometax=document.getElementById("incometax").value;
			let incometax = parseFloat(document.getElementById("incometax").value);			
			incometax = parseFloat(incometax);
			if(!incometax){
				incometax = 0;
			}
			
			
			let praposemoney = parseFloat(document.getElementById("praposemoney").value);			
			praposemoney = parseFloat(praposemoney);
			if(!praposemoney){
				praposemoney = 0;
			}


			// gst calculation
			
			var gstdeductionres= ((transafer_amount * gst_per) / (100+ gst_per)).toFixed();
			document.getElementById('gstdeduction').value = gstdeductionres;
			

			var remain_amount=(transafer_amount-gstdeductionres).toFixed();
			document.getElementById('totelmgst').value = remain_amount;
			
			

			//sentage 
		//	var sentageres=((remain_amount*sentagepercentage)/100).toFixed(2);
		//	document.getElementById('sentage').value = sentageres;
			
			var sentageres= ((remain_amount * sentagepercentage) / (100+ sentagepercentage)).toFixed();
			document.getElementById('sentage').value = sentageres;


			//gst tds % 
			
			var gsttdsres= ((transafer_amount * gsttdspercentage) /100).toFixed();
			document.getElementById('gsttds').value = gsttdsres;

			// console.log(gsttds.value);
			
			
			//income
			//incometax.value=((parseFloat(transafer_amount)) * (parseFloat(it_per)) / ((100+ parseFloat(it_per)))).toFixed(2);
			
			var incometaxres= ((transafer_amount * it_per) / 100 ).toFixed();
			document.getElementById('incometax').value = incometaxres;

			
			

			//praposemoney.value=((totelmgst.value)-(sentage.value+gsttds.value+leborses.value+incometax.value)).toFixed(2);
			var abcd = parseFloat(sentageres)+parseFloat(gsttdsres)+parseFloat(leborses)+parseFloat(incometaxres);
			// console.log("A"+abcd);
			abcd = abcd.toFixed(2);
			var prakhand_ko_preshit_amount=((remain_amount)-abcd).toFixed(2);
			// console.log(remain_amount, abcd)
			document.getElementById('praposemoney').value = prakhand_ko_preshit_amount;
			// console.log(prakhand_ko_preshit_amount);
			// console.log(remain_amount,sentageres,gsttdsres,leborses,incometaxres,prakhand_ko_preshit_amount);



			

			// document.getElementById("totelmgst").value=transafer_amount-document.getElementById("totelmgst").value;
			// console.log(transafer_amount);
		}
	
	</script>	
				
<?php
page_footer_start();
?>


    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

    
	<!--  Charts Plugin -->
	<script src="js/chartist.min.js"></script>

<?php		
page_footer_end();
?>

<script>
		
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
	
	
	function fill_division(val){
		var data = {"term":"b", "id":"proj_div", "val":val, "dept":$("#department").val()};
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(data){
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$("#unit_id").val(data.division_id); 
				fill_bank_details(data.division_id)
			}
			
		});
	}
	
	function fill_bank_details(val, selected){
		var data = {"term":"b", "id":"unit_bank", "val":val};
		$("#bank_name_unit").html('<option value="">--Select--</option>');
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

	$(document).ready( function () {
		$('#general_stat_table').DataTable({
				
		});
	});

	
<?php
	if(isset($_GET['edit_sno'])){
?>
	$(document).ready(function() {
		fill_sub_department(<?php echo $_POST['department']; ?>, <?php echo $_POST['sub_department_id']; ?>);
		fill_district(<?php echo $_POST['department']; ?>, <?php echo $_POST['district']; ?>);
		fill_project(<?php echo $_POST['district']; ?>, <?php echo $_POST['project_name']; ?>);
		
	}); 
<?php
	}
?>
</script>
