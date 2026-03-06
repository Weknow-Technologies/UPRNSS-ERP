<?php
include("scripts/settings.php");
page_header_start('Payment Report');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno=1;
$tab=1;
if(isset($_GET['delete'])){
	$sql="DELETE FROM `payment` WHERE sno='".$_GET['delete']."'";
	$result=execute_query($sql);
	if($result == true){
		$sql_trans="DELETE FROM `transactions` WHERE trans_id='".$_GET['delete']."' AND `type`='payment'";
		$result_trans=execute_query($sql_trans);
		//echo $sql_trans;
		$msg = '<div class="alert alert-success">Data Deleted !</div>';
	}
	else{
		$msg = '<div class="alert alert-success">Err in Deletion !</div>';
	}
}
?>
<div class="container">
	<div class="row">
	<?php echo $msg; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="panel panel-default ">
				<div class="panel-heading">
					Payment Report
					<div class="pull-right">
						<a href="payment.php">Payment Entry</a> &nbsp;
					</div>
				</div>
				<div class="panel-body"> 
					<table class="table taable-responsibe table-bordered table-condensed table-hover ">
						<tbody>
							<tr>
								<td>Date From</td>
								<td>
									<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date_from', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_POST['submit'])){echo $_POST['date_from'];}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
									</script>
								</td>
								<td>Date To</td>
								<td>
									<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date_to', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_POST['submit'])){echo $_POST['date_to'];}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
									</script>
								</td>
								<td>Mode Of Payment</td>
								<td>
									<select name="mop" class="form-control">
										<option value="">ALL</option>
										<option value="cash" <?php if(isset($_POST['submit'])){if($_POST['mop']=='cash'){echo 'selected';}}?>>CASH</option>
										<option value="rtgs" <?php if(isset($_POST['submit'])){if($_POST['mop']=='rtgs'){echo 'selected';}}?>>RTGS/NEFT</option>
										<option value="cheque" <?php if(isset($_POST['submit'])){if($_POST['mop']=='cheque'){echo 'selected';}}?>>CHEQUE</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Employee Name</td>
								<td><input type="text" name="employee_name" id="employee_name" class="form-control" value="<?php if(isset($_POST['submit'])){echo $_POST['employee_name'];} ?>"></td>
									<input type="hidden" name="emp_id" id="emp_id" value="<?php if(isset($_POST['submit'])){echo $_POST['emp_id'];} ?>">
								<td>Payment Type</td>
						      	<td>
						      		<select name="payment_type" id="payment_type" class="form-control">
						      			<option value="">ALL</option>
							      		<option value="salary" <?php if(isset($_POST['submit'])){if($_POST['payment_type']=='salary'){echo 'selected';}}?>>Salary Pay</option>
							      		<option value="advance" <?php if(isset($_POST['submit'])){if($_POST['payment_type']=='advance'){echo 'selected';}}?>>Advance</option>
							      	</select>
						      	</td>
							</tr>
						</tbody>
					</table>
					<input type="submit" name="submit" Value="Search" id="submit" class="form-control">
				</div>
			</div>
			<div class="panel">
					<div class="panel-body"> 
					<table class="table taable-responsibe table-bordered table-condensed table-hover ">
						<thead>
							<tr>
								<th>Sno</th>
								<th>Customer Name</th>
								<th>Amount</th>
								<!--<th>Invoice No</th>-->
								<th>Date</th>
								<th>Mode of payments</th>
								<th>Remarks</th>
								<th>Type</th>
								<th>View</th>
								<th>Edit</th>
								<th>Delete</th>
								</tr>
						</thead>
						<tbody>
							<?php
							$amount = 0;
							$sql = "SELECT * FROM `payment` WHERE 1=1"; 
							if(isset($_POST['submit'])){
			                    if($_POST['date_from'] !="") {
			                        $sql .= " and date >='".$_POST['date_from']."'";
			                    }
			                    if($_POST['date_to'] !="") {
			                        $sql .= " and date <='".$_POST['date_to']."'";
			                    }
			                    if($_POST['mop'] !="") {
			                        $sql .= " and mop='".$_POST['mop']."'";
			                    }
			                    if($_POST['payment_type'] !="") {
			                        $sql .= " and payment_type='".$_POST['payment_type']."'";
			                    }
			                     if($_POST['emp_id'] !="") {
			                        $sql .= " and emp_id='".$_POST['emp_id']."'";
			                    }
			                }
			                else{
			                	$sql .= " and date ='".date('Y-m-d')."'";
			                }
			                    //echo $sql;
									$result = execute_query($sql);
									if($result){
										while($row = mysqli_fetch_array($result)){
			                           	 echo'<tr><td>'.$sno.'</td>';
			                           	 echo'<td>'.employee_name($row['emp_id']).'</td>
			                           	 <td>'.$row['amount'].'</td>';
			                           	 //<td>'.$row['invoice_number'].'</td>
			                           	 echo '<td>'.date('d-m-Y',strtotime($row['date'])).'</td>
			                           	 <td>'.$row['mop'].'</td>
			                           	 <td>'.$row['remark'].'</td>
			                           	 <td style="text-transform:uppercase">'.$row['payment_type'].'</td>';
			                           	 echo'<td><a href="printing_payment.php?id='.$row["sno"].'" target="_blank">View</a></td>
			                           	 <td><a href="payment.php?e_id='.$row["sno"].'">Edit</a></td>
			                           	 <td><a href="payment_report.php?delete='.$row["sno"].'">Delete</a></td>';
			                           	 $sno++;
			                           	 $amount += $row['amount'];
			                        	}
			                        }
			                        echo '<tr><th colspan="2" style="text-align:right;">Total:</th><th>'.$amount.'</th><th colspan="3">&nbsp;</th><th colspan="4">&nbsp;</th></tr>';
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
<script>
	$( function() {
		$("#employee_name").autocomplete({
			source: function( request, response ) {
				$.ajax( {
					url: "ajax.php?id=employee_detail",
					dataType: "json",
					data: {
						term: request.term
					},
					success: function( data ) {
						response(data);
					}
				} );
			},
			minLength: 1,
			select: function( event, ui ) {
			   $('#employee_name').val(ui.item.employee_name); // display the selected text
			   $('#emp_id').val(ui.item.id); 
			}
		});
	});
</script>