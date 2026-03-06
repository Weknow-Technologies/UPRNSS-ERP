<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");$msg='';
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();



?>		

			<div class="card">

			  <div class="row">
				
				<div class="col-12">
					<div class="text-right">
					<a href="fund_transfer.php?view=view"><button type="button" class="btn btn-warning">Create New Payment Voucher</button></a>
				</div>


					<table class="table table-striped table-hover">

						<tr>

							<thead>

								<th>S.No.</th>

								<th>Date</th>

								<th>Unit Name</th>

								<th>Voucher No.</th>

								<th>Particulars</th>

								<th>Amount</th>

								<th></th>

							</thead>

							<tbody>

								<?php

								if($_SESSION['usertype']!='sadmin' && $_SESSION['usertype']!="6" ){

			  					    $sql = 'select * from billit_invoice_erp_payment where created_by="'.$_SESSION['username'].'"';

								}

								else{

								    $sql = 'select * from billit_invoice_erp_payment ';

								}

			  					$result = execute_query($sql);

			  					$i=1;

			  					while($row = mysqli_fetch_assoc($result)){

									echo '<tr>

									<td>'.$i++.'</td>

									<td>'.$row['timestamp'].'</td>

									<td>'.get_division($row['unit_id']).'</td>

									<td>'.$row['voucher_no'].'</td>

									<td>'.get_ledger($row['first_by']).'</td>

									<td>'.$row['tot_debit'].'</td>

									<td><a href="billit_payment_print.php?id='.$row['sno'].'" target="_blank">View</a></td>

									</tr>';

								}

								?>

								

							</tbody>

						</tr>

					</table>

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
