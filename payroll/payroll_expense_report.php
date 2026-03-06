<?php
include("scripts/settings.php");
page_header_start('Expense Report');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno=1;
$tab=1;
if(isset($_GET['delete'])){
	$sql="DELETE FROM `expense` WHERE sno='".$_GET['delete']."'";
	$result=execute_query($sql);
	if($result == true){
		$sql_trans="DELETE FROM `transactions` WHERE trans_id='".$_GET['delete']."' AND `type`='expense'";
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
					Expense Report
					<div class="pull-right">
						<a href="expense.php">Expense Entry</a> &nbsp;
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
							</tr>
							<tr>
								<td>Employee Name</td>
								<td><input type="text" name="employee_name" id="employee_name" class="form-control" value="<?php if(isset($_POST['submit'])){echo $_POST['employee_name'];} ?>"></td>
									<input type="hidden" name="emp_id" id="emp_id" value="<?php if(isset($_POST['submit'])){echo $_POST['emp_id'];} ?>">
								<td>Expense Type</td>
						      	<td>
						      		<select name="expense_type" id="expense_type" class="form-control">
						      			<option value="">ALL</option>
							      		<option value="Expense" <?php if(isset($_POST['submit'])){if($_POST['expense_type']=='Expense'){echo 'selected';}}?>>Expense</option>
							      		<option value="Due" <?php if(isset($_POST['submit'])){if($_POST['expense_type']=='Due'){echo 'selected';}}?>>Due</option>
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
								<th>Remarks</th>
								<th>Type</th>
								<!--<th>View</th>-->
								<th>Edit</th>
								<th>Delete</th>
								</tr>
						</thead>
						<tbody>
							<?php
							$sql = "SELECT * FROM `expense` WHERE 1=1";
							if(isset($_POST['submit'])){ 
			                    if($_POST['date_from'] !="") {
			                        $sql .= " and date >='".$_POST['date_from']."'";
			                    }
			                    if($_POST['date_to'] !="") {
			                        $sql .= " and date <='".$_POST['date_to']."'";
			                    }
			                    if($_POST['expense_type'] !="") {
			                        $sql .= " and expense_type='".$_POST['expense_type']."'";
			                    }
			                     if($_POST['emp_id'] !="") {
			                        $sql .= " and emp_id='".$_POST['emp_id']."'";
			                    }
			                }
			                else{
			                	$sql .= " and date ='".date('Y-m-d')."'";
			                }
			                    //echo $sql;
			                		$amount = 0;
									$result = execute_query($sql);
									if($result){
										while($row = mysqli_fetch_array($result)){
			                           	 echo'<tr><th>'.$sno.'</th>';
			                           	 echo'<td>'.employee_name($row['emp_id']).'</td>
			                           	 <td>'.$row['amount'].'</td>';
			                           	 //<td>'.$row['invoice_number'].'</td>
			                           	 echo '<td>'.date('d-m-Y',strtotime($row['date'])).'</td>
			                           	 <td>'.$row['remark'].'</td>
			                           	 <td style="text-transform:uppercase">'.$row['expense_type'].'</td>';
			                           	// echo'<td><a href="printing_expense.php?id='.$row["sno"].'" target="_blank">View</a></td>';

			                           	 echo '<td><a href="expense.php?e_id='.$row["sno"].'">Edit</a></td>
			                           	 <td><a href="expense_report.php?delete='.$row["sno"].'">Delete</a></td></tr>';
			                           	 $sno++;
			                           	$amount += $row['amount'];
			                        	}
			                        }
			                        echo '<tr><th colspan="2" style="text-align:right;">Total:</th><th>'.$amount.'</th><th colspan="3">&nbsp;</th><th colspan="2">&nbsp;</th></tr>';
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