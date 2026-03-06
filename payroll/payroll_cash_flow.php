<?php
include("scripts/settings.php");
page_header_start('Payment Report');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno=1;
$tab=1;
?>
<div class="container">
	<div class="row">
	<?php echo $msg; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel panel-default">
				<div class="panel-heading">CASH FLOW</div>	
				<div class="panel-body"> 
					<div class="pull-right">
							<a href="payment.php">Customer Ladger</a> &nbsp;
							<a href="payment_report.php">Payment Report</a> &nbsp;
								
							<a href="receipt_report.php">Recipt Ladger</a> &nbsp;
					</div>
					<table class="table taable-responsibe table-bordered table-condensed table-hover ">
						<tbody>
							<tr>
								<td>Cash Book</td>
								<td>
									<select name="cash_book" class="form-control input-sm">
										<option value="">Select</option>
										<?php
											$sql="SELECT * FROM `client_master` WHERE parents !=''";
											$result=execute_query($sql);
											if($result){
												while($row=mysqli_fetch_array($result)){
													echo'<option value='.$row['customer_name'].'>'.$row['customer_name'].'</option>';
												}
											}
												
										?>
									</select>

							</tr>
							<tr>
								<td>Date From</td>
								<td><script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date_from', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_GET['edit'])){echo $joining_date;}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
									</script>
								</td>
								<td>Date To</td>
								<td>
									<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date_to', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_GET['edit'])){echo $joining_date;}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
									</script>
								</td>
							</tr>
						</tbody>
					</table>
					<div class=" pull-right" style="width:300px;">
						<input type="submit" name="submit" value="submit" id="submit" class="form-control">
					</div>
				</div>
			</div>
		</form>
		<div class="panel panel-default">
			<div class="panel-body"> 
				<table class="table taable-responsibe table-bordered table-condensed table-hover ">
					<thead>
						<tr style="background-color:#EDEDED;">
							<td>Sno</td>
							<td>Date</td>
							<td>Party Name</td>
							<td>Remark</td>
							<td>Debit</td>
							<td>Credit</td>
							<td>Balance</td>
							<td></td>
						</tr>

					</thead>
					<tbody>
					<?php
					if(isset($_POST['submit'])){
						$balance=0;
						$dr=0;
						$cr=0;
						$date_from=$_POST['date_from'];
						$date_to=$_POST['date_to'];
						$cash_book=$_POST['cash_book'];
							$sql ="SELECT * FROM `customer_payment` WHERE (type='payment' or type='receipt')";
							 $condition = array();
			                    if($date_from !="") {
			                        $condition[] .= "date >='$date_from'";
			                      }
			                    if($date_to !="") {
			                        $condition[] .= "date <='$date_to'";
			                    }
			                    if($cash_book !="") {
			                        $condition[] .= "account='$cash_book'";
			                      
			                    }
							  
			                     if (count($condition) > 0 ){
			                         $sql.=" AND ".implode(' AND ', $condition);
			                      }
							 $result=execute_query($sql);
							 ///echo $sql;
							  $rowcount=mysqli_num_rows($result);
							  //echo $rowcount;
							 if($rowcount > 0){
								while($row=mysqli_fetch_array($result)){
								 	if($row['type'] == 'payment'){
									 	echo'<tr><td style="background-color:#EDEDED;">'.$sno++.'</td>
									 	<td>'.$row['date'].'</td>
									 	<td>'.client_name($row['customer_id']).'</td>
									 	<td>'.$row['remark'].'</td>
									 	<td>'.$row['amount'].'</td>
									 	<td></td>';
									 	$dr+=$row['amount'];
									 	$balance-=$row['amount'];
									 	echo'<td>'.$balance.'</td>';
									 	echo'<td><a href="payment.php?edit_sno='.$row["sno"].'">Edit</a></td>';
									 }
									else if($row['type']=='receipt'){
										echo'<tr ><td style="background-color:#EDEDED;c">'.$sno++.'</td>
									 	<td>'.$row['date'].'</td>
									 	<td>'.client_name($row['customer_id']).'</td>
									 	<td>'.$row['remark'].'</td>
									 	<td></td>
									 	<td>'.$row['amount'].'</td>';
									 	$cr+=$row['amount'];
									 	$balance+=$row['amount'];
									 	echo'<td>'.$balance.'</td>';
									 	echo'<td><a href="receipt.php?edit1='.$row["sno"].'">Edit</a></td>';
									 }
								}
							}	 
					?>
							<tr style="background-color:#EDEDED;">
								<td colspan="4"><div class="pull-right" >Total : </div> &nbsp;&nbsp;</td>
								<td><?php echo $dr; ?> </td>
								<td colspan="3"> <?php echo $cr; ?> </td>
							</tr>
							<tr style="background-color:#EDEDED;">
								<td colspan="6"></td>
								<td colspan="2"><span>Balance : &nbsp;&nbsp;<?php echo $balance ?></sapn> </td>
							</tr>
						<?php
					}
						?>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>
<?php
page_footer();
?>