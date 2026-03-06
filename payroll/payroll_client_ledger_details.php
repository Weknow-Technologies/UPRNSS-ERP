<?php
include("scripts/settings.php");
page_header_start('Customer Ledger');
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
			<div class="panel">
				<div class="panel-heading">Ledger Details</div>	
				<div class="panel-body"> 
					<div class="pull-left">
						<?php
							if(isset($_GET['id'])){
								$id=$_GET['id'];
								$sql="SELECT * FROM `client_details` WHERE sno='$id'";
								$result=execute_query($sql);
								$row=mysqli_fetch_array($result);
								echo $row['client_name']."<br>";
								echo $row['address']."<br>";
								echo "Tel:". $row['mobile']."<br>";
							}
						?>
								Current balance :<br>
								<?php
								 
								?>
								Opning Balance:
							
						
					</div>
					<div class="pull-right">

						Ledger From : <?php echo $_GET['from'];?><br>
						Ledger To : <?php echo $_GET['to']; ?>
						
					</div>
					<table class="table table-hover table-condensed table-bordered table-strip">
						<thead>
						<tr>
							<th>Sno</th>
							<th>Date</th>
							<th>Description</th>
							<th>Debit</th>
							<th>Credit</th>
							<th>Balance</th>
						</tr>
						</thead>
						<tbody>
							<?php
								$balance=0;
								$dr=0;
								$cr=0;
								$sql="SELECT * FROM `customer_payment` WHERE customer_id='$id'";
								//echo $sql;								
								$result=execute_query($sql);
								while($row=mysqli_fetch_array($result)){
									if($row['type']=='payment'){
										$dr+=$row['amount'];
										echo'<tr><td>'.$sno++.'</td>
										<td>'.$row['date'].'</td>
										<td>'.$row['remark'].'</td>
										<td>Rs.'.$row['amount'].'</td>';
										echo'<td> _ </td>';
										echo'<td>Rs.'.$balance-=$row['amount'].'</td></tr>';
									}
									else{
										$cr+=$row['amount'];
										echo'<tr><td>'.$sno++.'</td>
										<td>'.$row['date'].'</td>
										<td>'.$row['remark'].'</td>';
										echo'<td> _ </td>';
										echo'<td>Rs.'.$row['amount'].'</td>';
										echo'<td>Rs.'.$balance+=$row['amount'].'</td></tr>';
									}
								}
							?>
							<tr><td colspan="6"></td></tr>
							<tr>
								<td colspan="2"></td>
								<td col-span="1"class="text-right">Total :</td>
								<td> Rs.<?php echo $dr; ?></td>
								<td>Rs.<?php echo $cr; ?></td>
								<td>Rs.<?php echo $balance; ?></td>
								
							</tr>
						</tbody>
						
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
page_footer();	
?>