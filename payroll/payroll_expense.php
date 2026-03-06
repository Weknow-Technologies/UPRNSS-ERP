<?php
include("scripts/settings.php");
page_header_start('Expense/Due');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno1=1;
$tab=1;
?>
<?php
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `expense` SET
			`emp_id`="'.$_POST['emp_id'].'",
			`amount`="'.$_POST['amount'].'",
			`date`="'.$_POST['date'].'",
			`remark`="'.$_POST['remark'].'",
			`expense_type`="'.$_POST['expense_type'].'",
			`edited_by`="'.$_SESSION['usersno'].'",
			`edition_time`="'.time().'"
			WHERE sno="'.$_POST['edit_sno'].'"';
		//echo $sql;
		$result=execute_query($sql);
		if($result){
			$sql_update = 'UPDATE `transactions` SET 
					`info`="'.$_POST['expense_type'].'",
					`amount`="'.$_POST['amount'].'",
					`timestamp`="'.$_POST['date'].'",
					`financial_year`="",
					`edited_by`="'.$_SESSION['usersno'].'",
					`edited_on`="'.time().'"
					,`remarks`="'.$_POST['remark'].'"
					WHERE `trans_id`="'.$_POST['edit_sno'].'" AND `type`="expense"';
			execute_query($sql_update);
			//echo $sql_update;
	 	$msg = '<div class="alert alert-success">Data Updated !</div>';
		}
		else{
			$msg = '<div class="alert alert-success">Err in Updation !</div>';
		}
	}
	else{
		$sql='INSERT INTO `expense`(`emp_id`, `expense_type`, `amount`, `date`, `timestamp`, `remark`, `financial_year`, `invoice_number`, `created_by`, `creation_time`) VALUES ("'.$_POST['emp_id'].'", "'.$_POST['expense_type'].'", "'.$_POST['amount'].'", "'.$_POST['date'].'", "'.time().'", "'.$_POST['remark'].'", "", "", "'.$_SESSION['usersno'].'", "'.time().'")';
		$result=execute_query($sql);
		if($result == true){
			$id = insert_id();
			$sql_trans = 'INSERT INTO `transactions`(`emp_id`, `trans_id`, `type`, `info`, `amount`, `timestamp`, `invoice_no`, `financial_year`, `created_by`, `created_on`, `remarks`) VALUES ("'.$_POST['emp_id'].'", "'.$id.'", "'.$_POST['expense_type'].'", "'.$_POST['expense_type'].'", "'.$_POST['amount'].'",  "'.$_POST['date'].'", "", "", "'.$_SESSION['usersno'].'", "'.time().'", "'.$_POST['remark'].'")';
			execute_query($sql_trans);
	 		$msg = '<div class="alert alert-success">Data Saved !</div>';
		}
		else{
			$msg = '<div class="alert alert-success">Err in Saving !</div>';
		}
	}
}
if(isset($_GET['e_id'])){
	$sql="SELECT * FROM `expense` WHERE sno='".$_GET['e_id']."'";
	$result=execute_query($sql);
	$row_edit=mysqli_fetch_array($result);
}
?>
<div class="container">
	<div class="row">
	<?php echo $msg; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data" autocomplete="off">
			<div class="panel">
				<div class="panel-heading">Expense/Due Entry
					<div class="pull-right">
						<a href="expense_report.php">Expense Report</a> &nbsp;
						<a href="employee_ledger.php">Ledger</a> &nbsp;						
					</div>
				</div>
				<div class="panel-body"> 
					<table class="table table-responsibe table-bordered table-condensed table-hover">
						<tbody>
					      <tr>
					        <td style="width: 15%;">Date</td>
					        <td style="width: 35%;">
					        	<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_GET['e_id'])){echo $row_edit['date'];}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
								</script>
							</td>
							<td colspan="2" style="width: 50%;"></td>
					      </tr>
					      <tr>
					        <td>Employee Name</td>
					        <td>
					        	<input type="text"  value="<?php if(isset($_GET['e_id'])){ echo employee_details($row_edit['emp_id'])['employee_name']; } ?>" name="employee_name" id="employee_name" class="form-control" required>
					        	 <input type="hidden" name="emp_id" value="<?php if(isset($_GET['e_id'])){ echo $row_edit['emp_id']; } ?>" id='emp_id'/>
					        </td>	
					        <td>Employee Mobile</td>
					      	<td><input type="text"  value="<?php if(isset($_GET['e_id'])){ echo employee_details($row_edit['emp_id'])['contact']; } ?>" id="mobile" name="mobile" class="form-control" readonly></td>			        
					      </tr>
					      <tr>
					      	<td>Employee Designation</td>
					      	<td><input type="text"  value="<?php if(isset($_GET['e_id'])){ echo employee_details($row_edit['emp_id'])['employee_designation']; } ?>" id="designation" name="designation" class="form-control" readonly></td>
					      	<td>Employee Address</td>
					      	<td><input type="text" name="address"  value="<?php if(isset($_GET['e_id'])){ echo employee_details($row_edit['emp_id'])['address']; } ?>" id="address"  class="form-control" readonly></td>
					      </tr>
					      <tr>
					      	<td>Amount</td>
					      	<td><input type="text"  value="<?php if(isset($_GET['e_id'])){ echo $row_edit['amount']; } ?>" name="amount" id="amount" class="form-control" required></td>
					      	<td>Expense Type</td>
					      	<td>
					      		<select name="expense_type" id="expense_type" class="form-control">
						      		<option value="expense" <?php if(isset($_GET['e_id'])){if($row_edit['expense_type']=='expense'){echo 'selected';}} ?>>TA/Expense</option>
						      		<option value="due" <?php if(isset($_GET['e_id'])){if($row_edit['expense_type']=='due'){echo 'selected';}} ?>>Due</option>
						      		<option value="incentive" <?php if(isset($_GET['e_id'])){if($row_edit['expense_type']=='incentive'){echo 'selected';}} ?>>Incentive</option>
						      		<option value="deduction" <?php if(isset($_GET['e_id'])){if($row_edit['expense_type']=='deduction'){echo 'selected';}} ?>>Deduction</option>
						      	</select>
					      	</td>
					      </tr>					     
					      <tr>
					      	<td>Remark</td>
					      	<td><input type="text" name="remark"  value="<?php if(isset($_GET['e_id'])){ echo $row_edit['remark']; } ?>" class="form-control"></td>
					      </tr>
					     </tbody>
					</table>
					<div class="col-sm-3 pull-right">
						<input type="hidden" name="edit_sno" value="<?php if(isset($_GET['e_id'])){ echo $_GET['e_id']; } ?>">
						<input type="submit" Value="Submit" name="submit" id="submit" class="form-control">
					</div>
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
			   $('#address').val(ui.item.address);
			   $('#mobile').val(ui.item.mobile);
			   $('#designation').val(ui.item.employee_designation);
			}
		});
	});
</script>
 <!-- Script -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<!-- jQuery UI -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>/*
	function ViewChange($this) {
    var $selectText = $('option:selected', $this).text().toLowerCase();
    var $val = $($this).val();
  	var rows = $('table.someclass tr');

    if($val == 'cheque' Or $val='dd')
    {
    	var show rows.filter('.show').show();
    }
    else{
    	var show =rows.filter('.show').hide();
    }
    /*if ($selectText != '1') {
        $('tr').each(function () {
            if ($(this).find('td').length) {
                var txt = '';
                if ($val < 3)
                    txt = $(this).find('td:eq(1)').text().toLowerCase();
                else
                    txt = $(this).find('td:eq(3)').text().toLowerCase();

                if (txt === $selectText) {
                    $(this).show();
                }
                else {
                    $(this).hide();
                }
            }
        })
    }
    else {
        $('tr').show();
    }*/

</script>

<script type="text/javascript">
$('#account_name').keyup(function() {
  // If value is not empty
  if ($(this).val().length == 0) {
    // Hide the element
    $('#bank_name').prop('disabled', false);
     $('#chq').prop('disabled', false);
     $('#dropDown').prop('disabled', false);
    
  } else {
    // Otherwise show it
    $('#bank_name').prop('disabled', true);
     $('#chq').prop('disabled', true);
     $('#dropDown').prop('disabled', true);
  }
}).keyup();
</script>