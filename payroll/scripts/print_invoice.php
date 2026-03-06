<?php
	include("settings.php");
	page_header_start('Invoice');
	page_header_end();
?>
	<style>
		.table-bordered>tbody>tr>td{
			height:20px;
		}
	</style>
<?php
	navigation($_SERVER['PHP_SELF']);
	$msg="";
	$total_amount=0;
	$print="";

?>
<?php 
	if(isset($_GET['invoice_id'])){
		$invoice_id=$_GET['invoice_id'];
		//echo $invoice_id;
		$sql="SELECT * FROM `attendance` WHERE invoice_status='$invoice_id'";
		$result=execute_query($sql);
		$row=mysqli_fetch_array($result);
		$client_id=$row['client_id'];
		//echo $client_id;
		$sql="SELECT * FROM `client_details` WHERE sno='$client_id'";
		$result=execute_query($sql);
		$row = mysqli_fetch_array($result);
		$client_name=$row['client_name'];
		//echo $client_name;
		$address=$row['address'];
		$gst=$row['gst'];
		$pan=$row['pan'];
		//echo $client_name;
		$sql="SELECT * FROM `invoice` WHERE sno='$invoice_id'";
		$result=execute_query($sql);
		$row=mysqli_fetch_array($result);
		$order_no=$row['order_number'];
		$dept=$row['dept'];
		$payment_head=$row['payment_head'];

	}
?>
<div class="container">
	<div class="col-sm-12 text-center" style="padding-left:100px;padding-right:100px;">
		<span class="pull-left">Registered-Company Act.</Span>
		<p class="pull-right">Mob <span class="glyphicon glyphicon-earphone">&nbsp;</span>9454381361</p><br>
		<h1 class="text-center" style="margin:0px;">MISHRA Security Services</h1>
		<p class="text-center"  style="margin:0px;">Head Office :- 205/99, Chaupatiyan Road, Lucknow-226 003(U.P.)</p>
		<h3>TAX INVOICE</h3>
	</div>
	<div class="col-sm-12" style="border:1px solid black">
		<table class="table table-bordered table-condensed">
			<tbody>
			    <tr>
					<td>Client Name</td>
					<td><?php echo $client_name; ?></td>
					<td>Invoice Date: &nbsp;&nbsp;<?php echo  date("d/m/y"); ?></td>
				</tr>
				<tr>
					<td>Client Address</td>
					<td><?php echo $address; ?></td>
					<td>Invoice No: &nbsp;&nbsp;<?php echo $invoice_id; ?></td>
				</tr>
				<tr>
					<td>Client GST No.</td>
					<td><?php echo $gst; ?></td>
					<td>Payment Head: &nbsp;&nbsp; <?php echo $payment_head; ?></td>
				</tr>
				 <tr>
					<td>Client PAN No.</td>
					<td><?php echo $pan; ?></td>
					<td>Dept: &nbsp;&nbsp; <?php echo $dept;  ?></td>
				</tr>
				<tr>
					<td>Category of service</td>
					<td>D</td>
					<td>Order No: &nbsp;&nbsp; <?php echo $order_no; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-sm-12" style="border:1px solid black">
		<table class="table table-bordered table-condensed" >
			<thead>
				<tr>
					<th>SNO.</th>
					<th>CLASS</th>
					<th>DESCRIPTION</th>
					<th>Qty</th>
					<th>P DAYS</th>
					<th>RATE</th>
					<th>AMOUNT</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$count=1;
					$total_days=0;
					$total_amount=0;
						    
							
					$sql="SELECT count(*) as c, attendance.employee_id as emp_id,attendance.days as days,attendance.rate as rate,attendance.total as total,employee.employee_class as emp_class,employee.employee_designation as emp_designation FROM `attendance` LEFT JOIN `employee` on `employee`.`sno` = `attendance`.`employee_id` WHERE invoice_status='$invoice_id' group by employee.employee_designation";
					//echo $sql;
					$result=execute_query($sql);
					while($row=mysqli_fetch_array($result)){
						
						echo '<tr>';
						echo '<td>'.$count.'</td>';
						echo '<td>'.$row['emp_class'].'</td>';
						echo '<td>'.$row['emp_designation'].'</td>';
						echo '<td>'.$row['c'].'</td>';
						echo '<td>'.$row['days'].'</td>';
						echo'<td>'.$row['rate'].'</td>';
						echo'<td>'.$row['total']*$row['c'].'</td>';
						
						$total_days+=$row['days'];
						$total_amount+=$row['total']*$row['c'];
						$count++;
					}
					
				?>
			  		</tr>
				<tr>
					<td colspan="7"><b>TOTAL<b></td>
				</tr>
				<tr>
					<td colspan="3"> </td>
					<td ></td>
					<th ><?php echo $total_days; ?></th>
					<th></th>
					<th ><?php echo $total_amount; ?></th>
								
				</tr>	
			<tr>
				<?php

				?>
				<td colspan="5"></td>
				<td >E.P.F : 13.16%</td>
				<td>
					<?php
					$epf=$total_amount * 13.16/100;
					echo $epf;
					$total_amt=$total_amount+$epf;
					?>
				</td>
					
			</tr> 
			<tr>
				<td colspan="5"></td>
				<td>E.S.I.C : 4.75%</td>
				<td> <?php
					$esic=$total_amount * 4.75/100;
					echo $esic;
					$total_amt+=$esic;
					?>
				</td>
				
			</tr> 
			<tr>
				<td colspan="5"></td>
				<td>S.C : 2.37%</td>
				<td> <?php
					$sc=$total_amount * 2.37/100;
					echo $sc;
					$total_amt+=$sc;
					?>
				</td>
				
			</tr>
			<tr>
				<td colspan="5"></td>
				<th>TOTAL</th>
				<td> <b><?php echo $total_amt; ?>Rs. </td>
			</tr>
			<tr>
				<td colspan="5"></td>
				<td>S.GST : 9%</td>
				<td> 
					<?php
						$sgst=$total_amt*9/100;
						echo $sgst;
					?>
				</td>
			</tr>
			<tr>
				<td colspan="5"></td>
				<td>C.GST : 9%</td>
				<td> 
					<?php
						$cgst=$total_amt*9/100;
						echo $cgst;
					?>
				 </td>
			</tr>
			<tr>
				<td colspan="5"></td>
				<td>Grand Total</td>
				<td> <b><?php $grand_tot=$total_amt+$sgst+$cgst; echo $grand_tot; ?></b> </td>
			</tr>
			<tr>
				<td colspan="5"></td>
				<td>Round Off</td>
				<td><b> Rs.<?php  $roundval= round($grand_tot); echo $roundval; ?><b> </td>
			</tr>
			</tbody>
		</table>
	
					<table class="table table-bordered table-condensed">
						<label>AGENCY</label>
						<tr>
							<td>GST No:</td>
							<td>09ALAPM2506Q1ZU</td>
						</tr>
						<tr>
							<td>PAN NO:</td>
							<td>ALAPM2506Q</td>
						</tr>
						<tr>
							<td>EPF No:</td>
							<td>UP/21615</td>
						</tr>
						<tr>
							<td>ESIC No:</td>
							<td>21-1213274-101</td>
						</tr>
						
					</table>
				
					&nbsp;&nbsp;<label>FOR:</label>MISHRA SECURITY SERVICES<label class="pull-right">Authorized Signatory</label><br>

				</div>
			</div>
				<div class="col-sm-12 text-center"><button onclick="myFunction()">Print Page</button></div>

						
		</div>
<div>
	</body>
</html>
<script>
	function myFunction() {
    	window.print();
	}
</script>		