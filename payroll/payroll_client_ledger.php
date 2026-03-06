<?php
include("scripts/settings.php");
page_header_start('Customer Ledger');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno=1;
$tab=1;
$date= date("y-m-d");
$newdate = strtotime ( '-29 day' , strtotime ( $date ) ) ;
$newdate = date ( 'j-m-d' , $newdate );
$_POST['date_from']=$newdate;
$_POST['date_to']=  date("y-m-d");
session_start();
?>
<div class="container">
	<div class="row">
	<?php echo $msg; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel">
				<div class="panel-heading">
					Customer Ledger
				</div>	
				<div class="panel-body"> 
					<div class="pull-right">
						
					</div>
					<table class="table taable-responsibe table-bordered table-condensed table-hover ">
						<tbody>
							<tr>
								<td>Name</td>
								<td><input type="text" name="name" class="form-control input-sm"></td>
								<td>Date From </td>
								<td>
									<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date_from', 'admin_employee_from',true, 'YYYY-MM-DD',<?php echo $tab++; $tab=$tab+3; ?>));
									</script>
								</td>
								<td>Date To</td>
								<td><script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date_to', 'admin_employee_from',true, 'YYYY-MM-DD',<?php echo $tab++; $tab=$tab+3; ?>));
									</script>
								</td>
							</tr>
						</tbody>
					</table>
					<div class=" pull-right" style="width:200px; margin:10px;">
						<input type="submit" name="submit" value="Search" id="submit" class="form-control input-sm">
					</div>
		
					<table class="table taable-responsibe table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th>Sno</th>
								<th>Name</th>
								<th>Address</th>
								<th>Mobile</th>
								<th>Balance</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$from=$_POST['date_from'];
								$to=$_POST['date_to'];
								$sql="SELECT customer_id, SUM(COALESCE(CASE WHEN type ='receipt' THEN amount END,0))  - SUM(COALESCE(CASE WHEN type ='payment' THEN amount END,0)) balance FROM customer_payment GROUP BY customer_id HAVING balance != 0";
									$result=execute_query($sql);
									while($row=mysqli_fetch_array($result)){
									echo'<tr><td>'.$sno++.'</td>
									<td><a href="customer_ledger_details.php?id='.$row['customer_id'].'&from='.$from.'&to='.$to.'">'.client_name($row['customer_id']).'</a></td>';
									$sql1='SELECT * FROM `client_master` WHERE sno="'.$row['customer_id'].'"';
									$result1=execute_query($sql1);
									$row1=mysqli_fetch_array($result1);
									echo'<td>'.$row1['address'].'</td>
									<td>'.$row1['mobile'].'</td>
									<td>Rs.'.$row['balance'].'</td></tr>';
								}
							?>
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