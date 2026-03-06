<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;


page_header_start();
page_header_end();
page_sidebar();

if(isset($_GET['id'])){
	$sql = 'select * from uprnss_division where s_no="'.$_GET['id'].'"';
	$division_result = execute_query($sql);
	$row_division = mysqli_fetch_assoc($division_result);
	//echo mysqli_error($db);
	//echo $sql;
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
						<h5>Division Financial Ledger</h5>
						<div class="row">
							<!--<div class="col-md-3">
								<div class="form-group">
									<label>District Name</label><br/>
									<?php //echo $row_division['district_name_english']; ?>
								</div>
							</div>-->
							<div class="col-md-3">
								<div class="form-group">
									<label>Division Name</label><br/>
									<?php echo $row_division['division_name']; ?>
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
				<div class="card-header ">
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
							$sql = '(SELECT transaction_fund_receive.sno as sno, p_receive_amount, fund_receive_to, installment, order_no, order_date, receive_date, request_no, request_date, bank_name, account_no, ifsc_code, "receive" as type FROM `transaction_fund_receive` left join invoice_fund_receive on invoice_fund_receive.sno = invoice_id where transaction_fund_receive.project_id="'.$_GET['id'].'" order by receive_date) 
							union all 
							(SELECT invoice_account_fund_transafer.sno as sno, praposemoney, fund_transfer_to, "" as installment, order_no, order_date, transafer_date, "" as request_no, "" as request_date, to_bank_name, to_account_no, to_bank_ifsc, "transfer" as type FROM `invoice_account_fund_transafer` where project_name="'.$_GET['id'].'")';
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
								if($row['type']=='receive'){
									echo '<td>Received At : '.$row['fund_receive_to'].'</td>
									<td>'.$row['p_receive_amount'].'</td>
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
									else{
										echo 'Vender';
									}
									echo '</td>
									<td></td>
									<td>'.$row['p_receive_amount'].'</td>';
									$balance-=(float)$row['p_receive_amount'];
								}
								
								echo '<td>'.$balance.'</td></tr>';
								$tot+= $row['p_receive_amount'];
							
							}
							echo '<tr>
							<th colspan="5"></th>
							<th>Balance : </th>
							<th>'.$balance.'</th></tr>';
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
