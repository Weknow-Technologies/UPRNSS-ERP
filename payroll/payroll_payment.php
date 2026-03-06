<?php
include("scripts/settings.php");
page_header_start('Payment');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno1=1;
$tab=1;
?>
<?php
if(isset($_POST['submit'])){
	if($_POST['edit_sno']!=''){
		$sql='UPDATE `payment` SET
			`emp_id`="'.$_POST['emp_id'].'",
			`voucher_number`="'.$_POST['voucher_no'].'",
			`amount`="'.$_POST['amount'].'",
			`mop`="'.$_POST['mode_of_pay'].'",
			`date`="'.$_POST['date'].'",
			`cheque_number`="'.$_POST['chq_dd_rtgs'].'",
			`remark`="'.$_POST['remark'].'",
			`payment_type`="'.$_POST['payment_type'].'",
			`bank_name`="'.$_POST['bank_name'].'",
			`edited_by`="'.$_SESSION['usersno'].'",
			`edition_time`="'.time().'"
			WHERE sno="'.$_POST['edit_sno'].'"';
		//echo $sql;
		$result=execute_query($sql);
		if($result){
			$sql_update = 'UPDATE `transactions` SET 
					`info`="'.$_POST['payment_type'].'",
					`amount`="'.$_POST['amount'].'",
					`mop`="'.$_POST['mode_of_pay'].'",
					`chq_no`="'.$_POST['chq_dd_rtgs'].'",
					`bank_name`="'.$_POST['bank_name'].'",
					`timestamp`="'.$_POST['date'].'",
					`financial_year`="",
					`edited_by`="'.$_SESSION['usersno'].'",
					`edited_on`="'.time().'"
					,`remarks`="'.$_POST['remark'].'"
					WHERE `trans_id`="'.$_POST['edit_sno'].'" AND `type`="payment"';
			execute_query($sql_update);
			//echo $sql_update;
	 	$msg = '<div class="alert alert-success">Data Updated !</div>';
		}
		else{
			$msg = '<div class="alert alert-success">Err in Updation !</div>';
		}
	}
	else{
		$sql='INSERT INTO `payment`(`emp_id`, `payment_type`, `voucher_number`, `amount`, `mop`, `date`, `cheque_number`, `bank_name`, `timestamp`, `remark`, `financial_year`, `invoice_number`, `created_by`, `creation_time`) VALUES ("'.$_POST['emp_id'].'", "'.$_POST['payment_type'].'", "'.$_POST['voucher_no'].'", "'.$_POST['amount'].'", "'.$_POST['mode_of_pay'].'", "'.$_POST['date'].'", "'.$_POST['chq_dd_rtgs'].'", "'.$_POST['bank_name'].'", "'.time().'", "'.$_POST['remark'].'", "", "", "'.$_SESSION['usersno'].'", "'.time().'")';
		$result=execute_query($sql);
		if($result == true){
			$id = insert_id();
			$sql_trans = 'INSERT INTO `transactions`(`emp_id`, `trans_id`, `type`, `info`, `amount`, `mop`, `chq_no`, `bank_name`, `timestamp`, `account`, `invoice_no`, `financial_year`, `created_by`, `created_on`, `remarks`) VALUES ("'.$_POST['emp_id'].'", "'.$id.'", "payment", "'.$_POST['payment_type'].'", "'.$_POST['amount'].'", "'.$_POST['mode_of_pay'].'", "'.$_POST['chq_dd_rtgs'].'", "'.$_POST['bank_name'].'",  "'.$_POST['date'].'", "", "", "", "'.$_SESSION['usersno'].'", "'.time().'", "'.$_POST['remark'].'")';
			execute_query($sql_trans);
	 		$msg = '<div class="alert alert-success">Data Saved !</div>';
		}
		else{
			$msg = '<div class="alert alert-success">Err in Saving !</div>';
		}
	}
}
if(isset($_GET['e_id'])){
	$sql="SELECT * FROM `payment` WHERE sno='".$_GET['e_id']."'";
	$result=execute_query($sql);
	$row_edit=mysqli_fetch_array($result);
}
?>
<div class="container">
	<div class="row">
	<?php echo $msg; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data" autocomplete="off">
			<div class="panel">
				<div class="panel-heading">Payment Entry
					<div class="pull-right">
						<a href="payment_report.php">Payment Report</a> &nbsp;
						<a href="employee_ledger.php">Ledger</a> &nbsp;						
					</div>
				</div>
				<div class="panel-body"> 
					<table class="table table-responsibe table-bordered table-condensed table-hover">
						<tbody>
					      <tr>
					        <td style="width: 15%;">Date</td>
					        <td  style="width: 35%;">
					        	<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_GET['e_id'])){echo $row_edit['date'];}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
								</script>
							</td>
							<td style="width: 15%;">Voucher No</td>
        					<td style="width: 35%;"><input type="text" value="<?php if(isset($_GET['e_id'])){ echo $row_edit['voucher_number']; } ?>" name="voucher_no" class="form-control"></td>
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
					      	<td><table><tr><td style="width: 35%;"><input type="text" name="amount_show" id="amount_show" class="form-control" readonly></td><td style="width: 65%;"><input type="text"  value="<?php if(isset($_GET['e_id'])){ echo $row_edit['amount']; } ?>" name="amount" id="amount" class="form-control" required></td></tr></table></td>
					      	<td>Mode Of Payment</td>
					      	<td>
					      		<select name="mode_of_pay" id="dropDown" class="form-control">
					      			<option value="">Select Payment Mode</option>
					      			<option value="Cash" <?php  if(isset($_GET['e_id'])){if($row_edit['mop'] == 'Cash') { ?> selected="selected"<?php }} ?>>Cash</option>
					      			<option value="Cheque" <?php  if(isset($_GET['e_id'])){if($row_edit['mop'] == 'Cheque') { ?> selected="selected"<?php }} ?> >Cheque</option>
					      			<option value="DD" <?php  if(isset($_GET['e_id'])){if($row_edit['mop'] == 'DD') { ?> selected="selected"<?php }} ?> >Demand Draft</option>
					      			<option value="rtgs" <?php  if(isset($_GET['e_id'])){if($row_edit['mop'] == 'RTGS') { ?> selected="selected"<?php }} ?> >RTGS</option>
					      		</select>	
					      	</td>
					      </tr>
				     	<tr class="chq_dd" id="show">
				     		<td>Bank Name</td>
				     		<td><input type="text"  value="<?php if(isset($_GET['e_id'])){ echo $row_edit['bank_name']; } ?>" id="bank_name" name="bank_name" class="form-control"></td>
				     		 <td>Chq/Dd/Rtgs No</td>
				     		 <td><input type="text" id="chq" name="chq_dd_rtgs" class="form-control" value="<?php if(isset($_GET['e_id'])){ echo $row_edit['cheque_number']; } ?>"></td>
				     	</tr>					     
					      <tr>
					      	<td>Payment Type</td>
					      	<td>
					      		<select name="payment_type" id="payment_type" class="form-control">
						      		<option value="Salary" <?php if(isset($_GET['e_id'])){if($row_edit['payment_type']=='Salary'){echo 'selected';}} ?>>Salary Pay</option>
						      		<option value="Advance" <?php if(isset($_GET['e_id'])){if($row_edit['payment_type']=='Advance'){echo 'selected';}} ?>>Advance</option>
						      	</select>
					      	</td>
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
					url: "ajax.php?id=customer_payment",
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
			   $('#amount_show').val(ui.item.amount);
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