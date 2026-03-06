<?php
	include("scripts/settings.php");
	page_header_start('Invoice Report');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
	$msg="";
	$pre_tax_amount=0;
	$print="";

?>
<div class="container">
	<div class="panel">
		<div class="panel-heading">Invoice Report</div>
			<div class="panel-body">
				<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
					<table class="table table-hover table-responsive table-bordered table-striped">
						<thead>
							<tr>
							<td><label>Invoice No</label></td>
							<td><label>Client Name</label></td>
							<td><label>Project Name</label></td>
							<td><label>Payment Head</label></td>
							<td><label>Dept</label></td>
							<td><label>Order Number</label></td>
							<td><label>Taxable amount</label></td>
							<td><label>Total amount</label></td>
							</tr>
						</thead>
						<tbody>
				<?php
					$sql="SELECT invoice.sno as sno ,attendance.client_id,attendance.project_id,invoice.payment_head,invoice.dept,
					invoice.order_number,invoice.taxable_amount,invoice.total_amount FROM `invoice` inner join attendance on attendance.invoice_status = invoice.sno 
					WHERE invoice.sno=attendance.invoice_status";
					
					$result = execute_query($sql);
					while ($row = mysqli_fetch_array($result)){
						echo '<tr>';
						echo '<td>'.$row['sno'].'</td>';
						$sql1="SELECT client_name FROM client_details WHERE sno='".$row['client_id']."'";
						$result1 = execute_query($sql1);
						$row1 = mysqli_fetch_array($result1);
						echo '<td>'.$row1['client_name'].'</td>';
						$sql2="SELECT project_name FROM projects WHERE sno='".$row['project_id']."'";
						$result2 = execute_query($sql2);
						$row2 = mysqli_fetch_array($result2);

						echo '<td>'.$row2['project_name'].'</td>';
						echo '<td>'.$row['payment_head'].'</td>';
						echo '<td>'.$row['dept'].'</td>';
						echo '<td>'.$row['order_number'].'</td>';
						echo '<td>'.$row['taxable_amount'].'</td>';
						echo '<td>'.$row['total_amount'].'</td>';
						echo'</tr>';
					}

					

				?>
			</tbody>
		</div>
	</form>
</div>
</div>
</body>