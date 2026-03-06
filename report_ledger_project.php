<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;


page_header_start();
page_header_end();
page_sidebar();

if(isset($_GET['id'])){
	$sql = 'select project_name,
	project_name_hindi,
	project_type,
	sanction_date,
	sanction_cost,
	revised_date,
	revised_cost,
	admin_go_no,
	admin_go_date,
	last_update,
	project_status_1,
	district_name_english,
	division_name,
	department_name_hindi,
	sub_department_hindi
	from uprnss_project_temp
	left join uprnss_district on uprnss_district.sno = uprnss_project_temp.district_id
	left join uprnss_division on uprnss_division.s_no = uprnss_project_temp.division_id
	left join uprnss_department_name on uprnss_department_name.sno = uprnss_project_temp.department_id
	left join uprnss_sub_department on uprnss_sub_department.sno = uprnss_project_temp.sub_department_id
	where uprnss_project_temp.sno="'.$_GET['id'].'"';
	$project_result = execute_query($sql);
	$row_project = mysqli_fetch_assoc($project_result);
	//echo mysqli_error($db);
	//echo $sql;
	
	$sql = 'SELECT * FROM `transaction_fund_receive` where project_id="'.$_GET['id'].'"';
	$data = execute_query($sql);

}
?>		
	<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">	
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title"></h4>
						<?php echo $msg; ?>
					</div>
					<div class="card-body">
						<h5>Project Financial Ledger</h5>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Name</label><br/>
									<?php echo $row_project['project_name']; ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Name</label><br/>
									<?php echo $row_project['project_name_hindi']; ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Project Cost</label><br/>
									<?php echo $row_project['sanction_cost']; ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>G.O. Details</label><br/>
									<?php echo $row_project['admin_go_no'].'. Dated : '.$row_project['admin_go_date']; ?>
								</div>
							</div>
							
						</div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>District Name</label><br/>
									<?php echo $row_project['district_name_english']; ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Division Name</label><br/>
									<?php echo $row_project['division_name']; ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Department Name</label><br/>
									<?php echo $row_project['department_name_hindi']; ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Sub Department Name</label><br/>
									<?php echo $row_project['sub_department_hindi']; ?>
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
			<div class="card strpied-tabled-with-hover">
				<div class="card-header bg-danger text-center">
					<h2 class="text-white">Head Office to UNIT</h2>
				</div>
				<div class="card-body table-full-width table-responsive">	
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Transaction Date</th>
								<th>Transaction Type</th>
								<th>Description</th>
								<th>Receipt</th>
								<th>Payment</th>
								<th>Balance</th>
							</tr>	
						</thead>
						<tbody>
							<?php
							$sql = '(SELECT transaction_fund_receive.sno as sno, p_receive_amount, fund_receive_to, installment, order_no, order_date, receive_date, bank_name, account_no, ifsc_code, "Receive" as type FROM `transaction_fund_receive` left join invoice_fund_receive on invoice_fund_receive.sno = invoice_id where transaction_fund_receive.project_id="'.$_GET['id'].'" order by receive_date) 
							union all 
							(SELECT transaction_fund_transfer.sno as sno, transafer_amount, fund_transfer_to, "" as installment, order_no, order_date, transafer_date, to_bank_name, to_account_no, to_bank_ifsc, "Transfer" as type FROM `transaction_fund_transfer` where project_name="'.$_GET['id'].'")';
							//echo $sql;
							$result = execute_query($sql);
							$i=1;
							$tot=0;
							$balance = 0;
							while($row = mysqli_fetch_assoc($result)){	
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row['receive_date'].'</td>
								<td>'.$row['type'].'</td>';
								if($row['type']=='Receive'){
									echo '<td>Received At : '.$row['fund_receive_to'].'</td>
									<td>'.amount_format($row['p_receive_amount']).'</td>
									<td></td>';
									$balance+=(float)$row['p_receive_amount'];
								}
								else{
									echo '<td>Transferred To : ';
									if($row['fund_receive_to']=='2'){
										echo 'Head Office';
									}
									elseif($row['fund_receive_to']=='3'){
										echo 'Unit';
									}
									elseif($row['fund_receive_to']=='3'){
										echo 'Unit';
									}
									elseif($row['fund_receive_to']=='3'){
										echo 'Unit';
									}
									else{
										echo 'Vender';
									}
									echo '</td>
									<td></td>
									<td>'.amount_format($row['p_receive_amount']).'</td>';
									$balance-=(float)$row['p_receive_amount'];
								}
								
								echo '<td>'.amount_format($balance).'</td></tr>';
								$tot+= $row['p_receive_amount'];
							
							}
							echo '<tr>
							<th colspan="5"></th>
							<th>Balance : </th>
							<th>'.amount_format($balance).'</th></tr>';
							?>
						</tbody>
					</table>
					<!--/*Sample Report 2nd type ends*/-->
				</div>
			</div>
		</div>
	</div>

<div class="row">
		<div class="col-md-12">
			<div class="card strpied-tabled-with-hover">
				<div class="card-header bg-danger text-center">
					<h2 class="text-white">UNIT to CONTRACTOR</h2>
				</div>
				<div class="card-body table-full-width table-responsive">	
					<table class="table table-hover table-striped table-bordered">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Transaction Date</th>
								<th>Transaction Type</th>
								<th>Description</th>
								<th>Receipt</th>
								<th>Payment</th>
								<th>Balance</th>
							</tr>	
						</thead>
						<tbody>
							<?php
							$sql = '(SELECT transaction_fund_transfer.sno as sno, transafer_amount, fund_transfer_to, "" as installment, order_no, order_date, transafer_date, to_bank_name, to_account_no, to_bank_ifsc, "Receive" as type FROM `transaction_fund_transfer` where project_name="'.$_GET['id'].'") 
							union all 
							(SELECT invoice_account_fund_transafer_vendor.sno as sno, transafer_amount, fund_transfer_to, "" as installment, order_no, order_date, transafer_date, to_bank_name, to_account_no, to_bank_ifsc, "Transfer" as type FROM `invoice_account_fund_transafer_vendor` where project_name="'.$_GET['id'].'")';
							//echo $sql;
							$result = execute_query($sql);
							$i=1;
							$tot=0;
							$balance = 0;
							while($row = mysqli_fetch_assoc($result)){	
								echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$row['transafer_date'].'</td>
								<td>'.$row['type'].'</td>';
								if($row['type']=='Receive'){
									
									echo '<td>Received At : ';
									if($row['fund_transfer_to']=='2'){
										echo 'Head Office';
									}
									elseif($row['fund_transfer_to']=='3'){
										echo 'Unit';
									}
									else{
										echo 'Vender';
									}
									echo '</td>
									<td>'.amount_format($row['transafer_amount']).'</td>
									<td></td>';
									$balance+=(float)$row['transafer_amount'];
								}
								else{
									echo '<td>Transferred To : ';
									if($row['fund_transfer_to']=='2'){
										echo 'Head Office';
									}
									elseif($row['fund_transfer_to']=='3'){
										echo 'Unit';
									}
									else{
										echo 'Vender';
									}
									echo '</td>
									<td></td>
									<td>'.amount_format($row['transafer_amount']).'</td>';
									$balance-=(float)$row['transafer_amount'];
								}
								
								echo '<td>'.amount_format($balance).'</td></tr>';
								$tot+= $row['transafer_amount'];
							
							}
							echo '<tr>
							<th colspan="5"></th>
							<th>Balance : </th>
							<th>'.amount_format($balance).'</th></tr>';
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
    
<?php		
page_footer_end();
?>
