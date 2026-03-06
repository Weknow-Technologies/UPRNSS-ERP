<?php
include("scripts/settings.php");
page_header_start('Payment');
page_header_end();
navigation($_SERVER['PHP_SELF']);
$msg='';
$sno1=1;
$tab=1;
$mode_of_pay='';
$chq_dd_rtgs_no='';
?>
<?php
if(isset($_POST['submit1'])){
	if(empty($_POST['mode_of_pay'] )|| empty($_POST['chq_dd_rtgs'])){
		$_POST['mode_of_pay']='';
		$_POST['chq_dd_rtgs']='';
	}
	if($_POST['get_id'] != ''){
		$id=$_POST['get_id'];
		$date=$_POST['date'];
		$voucher_no=$_POST['voucher_no'];
		$account_name=$_POST['account_name'];
		$customer_name=$_POST['customer_name'];
		$address=$_POST['address'];
		$mobile=$_POST['gst'];
		$amount=$_POST['amount'];
		$mode_of_pay=$_POST['mode_of_pay'];
		$chq_dd_rtgs_no=$_POST['chq_dd_rtgs'];
		$remark=$_POST['remark'];
		$mode_of_pay=$_POST['mode_of_pay'];
		$cust_id=$_POST['cust_id'];
		$sql="UPDATE `customer_payment` SET `customer_id`='$cust_id',`voucher_number`='$voucher_no',`amount`='$amount',
		`mop`='$mode_of_pay',`date`='$date',`cheque_number`='$chq_dd_rtgs_no',`remark`='$remark',
		`account`='$account_name' WHERE sno='$id'";
		//echo $sql;
		$result=execute_query($sql);
		if($result){
		 	$msg = '<div class="alert alert-success">Data Updated !</div>';
		}
		else{
				$msg = '<div class="alert alert-success">Err in Updation !</div>';
		}
	}
	else{
		
		$date=$_POST['date'];
		$voucher_no=$_POST['voucher_no'];
		$account_name=$_POST['account_name'];
		$customer_name=$_POST['customer_name'];
		$address=$_POST['address'];
		$mobile=$_POST['gst'];
		$amount=$_POST['amount'];
		$mode_of_pay=$_POST['mode_of_pay'];
		$chq_dd_rtgs_no=$_POST['chq_dd_rtgs'];
		$remark=$_POST['remark'];
		$mode_of_pay=$_POST['mode_of_pay'];
		$cust_id=$_POST['cust_id'];
		$date_now = date('Y-m-d H:i:s');
		$sql="INSERT INTO `customer_payment`(`customer_id`, `type`, `voucher_number`, `amount`, `mop`, `date`,
		 `cheque_number`, `bank_name`, `timestamp`, `remark`, `account`, `financial_year`, `invoice_number`)
		  VALUES ('$cust_id','receipt','$voucher_no','$amount','$mode_of_pay','$date','$chq_dd_rtgs_no','','$date_now','$remark','$account_name','','')";
		$result=execute_query($sql);

		if($result == true){
		 	$msg = '<div class="alert alert-success">Data Saved !</div>';
			}
			else{
				$msg = '<div class="alert alert-success">Err in Saving !</div>';
			}
	}
}

	if(isset($_GET['edit1'])){
		$id=$_GET['edit1'];
		//echo $id;
		$sql="SELECT * FROM `customer_payment` WHERE sno='$id'";
		$result=execute_query($sql);
		$row=mysqli_fetch_array($result);
		$date=$row['date'];
		$voucher_no=$row['voucher_number'];
		echo $voucher_no;
		$account_name=$row['account'];
		$cust_name=$row['customer_id'];
		$amount=$row['amount'];
		$mode_of_pay=$row['mop'];
		$bank_name=$row['bank_name'];
		$remark=$row['remark'];

		

	}

	if(isset($_GET['delete1'])){
		$sql="DELETE FROM `customer_payment` WHERE sno='".$_GET['delete1']."'";
		$result=execute_query($sql);
		if($result == true){
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
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data" autocomplete="off">
			<div class="panel">
				<div class="panel-heading">
					Receipt Entry
					<div class="pull-right">
						<a href="receipt_report.php">Recipt Report</a> &nbsp;
						<a href="cash_flow.php">Cash Flow</a> &nbsp;
						
					</div>
				</div>
				<div class="panel-body"> 
					<table class="table table-responsibe table-bordered table-condensed table-hover"  id="tableID">
						<tbody>
					      <tr>
					        <td>Date</td>
					        <td>
					        	<script required type="text/javascript" language="javascript">
									document.writeln(DateInput('date', 'admin_employee_from',true, 'YYYY-MM-DD','<?php if(isset($_GET['edit1'])){echo $date;}else{echo date("Y-m-d");} ?>', <?php echo $tab++; $tab=$tab+3; ?>));
								</script>
							</td>
							<td>Voucher No</td>
        					<td><input type="text" value="<?php if(isset($_GET['edit1'])){ echo $voucher_no; } ?>" name="voucher_no" class="form-control input-sm"></td>
					      </tr>
					      <tr>
					        <td>Account Name(Payment Mode)</td>
					        <td><input type="text"  value="<?php if(isset($_GET['edit1'])){ echo $account_name; } ?>" name="account_name" id="account_name" class="form-control input-sm"></td>
					        <td>Customer Name</td>
					        <td>
					        	<?php
					        		 if(isset($_GET['edit1'])){
					        		 	$sql1="SELECT * FROM `client_details` WHERE sno='$cust_name'";
					        		 	//echo $sql1;

					        		 	$result1=execute_query($sql1);
					        		 	if($result1){
					        		 		$row1=mysqli_fetch_array($result1);

					        		 	}
					        	
					        		 }
					        	?>
					        	<input type="text"  value="<?php if(isset($_GET['edit1'])){ echo $row1['client_name']; } ?>" name="customer_name" id="autocomplete" class="form-control input-sm">
					        	 <input type="hidden" value="<?php if(isset($_GET['edit1'])){ echo $cust_name; } ?>" name="cust_id" id='cust_id' />
					        </td>				        
					      </tr>
					      <tr>
					      	<td>Address</td>
					      	<td><input type="text" name="address"  value="<?php if(isset($_GET['edit1'])){ echo $row1['address']; } ?>" id="address"  class="form-control input-sm"></td>
					      	<td>Mobile</td>
					      	<td><input type="text"  value="<?php if(isset($_GET['edit1'])){ echo $row1['mobile']; } ?>" id="mobile" name="mobile" class="form-control input-sm"></td>
					      </tr>
					      <tr>
					      	<td>Gstin</td>
					      	<td><input type="text"   value="<?php if(isset($_GET['edit1'])){ echo $row1['gst']; } ?>"name="gst" name="gst" id="gst" class="form-control input-sm"></td>
					      	<td>Amount</td>
					      	<td><input type="text"  value="<?php if(isset($_GET['edit1'])){ echo $amount; } ?>" name="amount" class="form-control input-sm"></td>
					      </tr>
					      <tr>
					      	<td>Mode Of Payment</td>
					      	<td colspan="3"> 
					      		<select name="mode_of_pay" id="dropDown" class="form-control input-sm">
					      			<option value="">Select Payment Mode</option>
					      			<option value="cheque" <?php if(isset($_GET['edit1'])){if($mode_of_pay == 'cheque') { ?> selected="selected"<?php } }?> >Cheque</option>
					      			<option value="dd" value="cheque" <?php if(isset($_GET['edit1'])){if($mode_of_pay == 'dd') { ?> selected="selected"<?php } }?> >Dmand Draft</option>
					      			<option value="rtgs" value="cheque" <?php if(isset($_GET['edit1'])){if($mode_of_pay == 'rtgs') { ?> selected="selected"<?php } }?> >RTGS</option>
					      		</select>	
					      	</td>
					      </tr>
					      
					     	<tr class="chq_dd" id="show">
					     		<td>Bank Name</td>
					     		<td><input type="text"  value="<?php if(isset($_GET['edit1'])){ echo $bank_name; } ?>" id="bank_name" name="bank_name" class="form-control input-sm"></td>
					     		 <td>Chq/Dd/Rtgs No</td>
					     		 <td><input type="text" id="chq" name="chq_dd_rtgs" class="form-control input-sm"></td>
					     	</tr>
					     
					      <tr>
					      	<td>Remark</td>
					      	<td><input type="text" name="remark"  value="<?php if(isset($_GET['edit1'])){ echo $remark; } ?>" class="form-control input-sm"></td>
					      </tr>

					</table>
					<div class="col-sm-2 pull-right">
						<input type="hidden" name="get_id" value="<?php if(isset($_GET['edit1'])){ echo $_GET['edit1']; } ?>">
						<input type="submit" Value="Submit" name="submit1" id="submit" class="form-control input-sm">
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
		//alert('aac');
	 $( "#autocomplete" ).autocomplete({
	  source: function( request, response ) {
	   // Fetch data
	   $.ajax({
	    url: "ajax.php",
	    type: 'post',
	    dataType: "json",
	    data: {
	     search:request.term
	    },
	    success: function( data ) {
	     response( data );
	    }
	   });
	  },
	  select: function (event, ui) {
	   // Set selection
	   $('#autocomplete').val(ui.item.cust_name); // display the selected text
	   $('#cust_id').val(ui.item.id); 
	   $('#address').val(ui.item.address);
	   $('#mobile').val(ui.item.mobile);
	   $('#gst').val(ui.item.gst);
	   // save selected id to input
	   return false;
	  }
 });
});
 </script>
 <script>
	$( function() {
		//alert('aac');
	 $( "#account_name" ).autocomplete({
	  source: function( request, response ) {
	   // Fetch data
	   $.ajax({
	    url: "ajax.php",
	    type: 'post',
	    dataType: "json",
	    data: {
	     search1:request.term
	    },
	    success: function( data ) {
	     response( data );
	    }
	   });
	  },
	  select: function (event, ui) {
	   // Set selection
	   $('#account_name').val(ui.item.cust_name);
	   return false;
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