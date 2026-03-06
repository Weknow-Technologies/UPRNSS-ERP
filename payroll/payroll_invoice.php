<?php
	include("scripts/settings.php");
	page_header_start('Invoice');
	page_header_end();
	navigation($_SERVER['PHP_SELF']);
	$msg="";
	$print="";
	$pre_tax_amount=0;
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>
function fetch_select(val){
	$("#project").html(''); 
		$.ajax({
	        url: "ajax.php?id=projects&term="+val,
	        dataType:"json"
	    })
	    .done(function( data ) {
			console.log(data);
			var txt='<option value="9999">ALL</option>';
			$.each(data, function(key, value){
				console.log(value);
			//	txt='<option value="" >''</option>';
				txt += '<option value="'+value.id+'">'+value.label+'</option>';
		});
			$("#project").html(txt);             
	}); 
}
</script>
<?php
	if(isset($_POST['confirm'])){
		if(!empty($_POST['check_list'])){
			foreach($_POST['check_list'] as $selected){
			$sno= $selected;
			$sql="SELECT * FROM `attendance` WHERE sno='$sno'";
			$result=execute_query($sql);
			$row = mysqli_fetch_array($result);
			$emp_total=$row['total'];
			$pre_tax_amount+=$emp_total;
			

		}
	}
		echo $pre_tax_amount;
		$epf=13.16;
		$esic=4.75;
		$sc=2.37;
		$epfamount=($pre_tax_amount*13.16)/100;
		$esicamount=($pre_tax_amount*4.75)/100;
		$scamount=($pre_tax_amount*2.37)/100;
		$totalfundamount=$pre_tax_amount+$epfamount+$esicamount+$scamount;
		$gstamount=$totalfundamount+($totalfundamount*18)/100;
		$total_amount = round($gstamount,2);
		$order_no=$_POST['order_no'];
		$payment_head=$_POST['payment_head'];
		$dept=$_POST['dept'];
		$day=date('d');
			$month=date('m');
			$year=date("Y");
			$trigger=0;
			$sql="SELECT year_financial  FROM financial_year";
			$result= execute_query($sql);
			$row=mysqli_fetch_array($result);
			$fin_year=$row['year_financial'];
			if($month>4 && $year-$row['year_financial']>=1){
					$trigger=1;
			}
			if($trigger===1){
				//echo $trigger."trigger";
				$sql="UPDATE `invoice` SET year_financial='".($row['year_financial']+ $year-$row['year_financial'])."'";
				//echo $sql;
				$result=execute_query($sql);
				$invoice_number=1;	
				$fin_year=$row['year_financial']+ $year - $row['year_financial'];			
			}
			if(!isset($invoice_number)){
				$sql_in="SELECT MAX(invoice_number) as max_invoice FROM invoice";
				echo $sql_in;
				$result_in=execute_query($sql_in);
				$row_in=mysqli_fetch_array($result_in);
				//echo $row_in['max_invoice'];
				$invoice_number=$row_in['max_invoice']+1;
				//echo $invoice_number;
			}
			$date=date("y-m-d");
			$sql="INSERT INTO `invoice`(`invoice_number`, `order_number`, `payment_head`, `dept`, `financial_year`, `cgst`, `sgst`, `epf`, `esic`, `sc`, `total`, `total_with_fundtax`, `taxable_amountgst`, `grand_total`, `date`) VALUES ('$invoice_number','$order_no','$payment_head','$dept', '$fin_year', '9','9','$epf','$esic','$sc','$pre_tax_amount','$totalfundamount','$gstamount','$total_amount','$date')"; 
			$result= execute_query($sql);
			$clientid=$_POST['clientid'];
			//echo $sql;
			$qry="INSERT INTO `customer_payment`(`customer_id`, `type`, `voucher_number`, `amount`, `mop`, `date`, `cheque_number`, `bank_name`, `timestamp`, `remark`, `account`, `financial_year`, `invoice_number`, `created_by`, `creation_time`, `edited_by`, `edition_time`, `admin_remarks`, `branch`) VALUES ('$clientid','invoice','','$total_amount','','$date','','','','','','','','','','','','','')";
		//echo $qry;
			$result=execute_query($qry);
		if($result == true){
			$msg='<div class="alert alert-success">Invoice Generated !</div>';
			$sql="SELECT `sno` from `invoice` order by sno desc limit 1";
			$result=execute_query($sql);
			$row = mysqli_fetch_array($result);
			$invoice_id=$row['sno'];
			if(!empty($_POST['check_list'])){
				foreach($_POST['check_list'] as $selected){
					$sno=$selected;
				//	echo $emp_id;
					
				$sql="UPDATE `attendance` SET `invoice_status`='$invoice_id' WHERE `sno`=$sno";
					$result= execute_query($sql);
				//$qry="INSERT INTO `customer_payment`(`customer_id`, `type`, `voucher_number`, `amount`, `mop`, `date`, `cheque_number`, `bank_name`, `timestamp`, `remark`, `account`, `financial_year`, `invoice_number`, `created_by`, `creation_time`, `edited_by`, `edition_time`, `admin_remarks`, `branch`) VALUES ()";
				}
			}
				$print='<a href="print_invoice.php?invoice_id='.$invoice_id.'"><div class="" style="color:red;"><h3>Print Invoice</h3></div></a>';
		}		
		else{
			$msg='<div class="alert alert-success">Error : 1</div>';
		}
			//$project_id=$_POST["project_id"];
			
	}

$day=date('d');
			$month=date('m');
			$year=date("Y");
			$trigger=0;
			$sql="SELECT financial_year FROM attendance_invoice";
			$result= execute_query($sql);
			$row=mysqli_fetch_array($result);
			if($month>4 && $year-$row['financial_year']>=1){
					$trigger=1;
			}
			if($trigger===1){
				//echo $trigger."trigger";
				$sql="UPDATE attendance_invoice SET financial_year='".($row['financial_year']+ $year-$row['financial_year'])."'";
				//echo $sql;
				$result=execute_query($sql);
				$invoice_number=1;				
			}
	
?>
<div class="container">
	<?php echo $msg;  ?>
	<div class="panel">	
		<div class="panel-heading">Invoice<?php echo $print ;?>
		</div>
		<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="first_form"  enctype="multipart/form-data">
			<div class="panel-body">
				<table class="table table-hover table-responsive table-bordered table-striped">
					<tr>
						<td>Select Client</td>
						<td>
							<select class="form-control"  name="client" id="client" onchange="fetch_select(this.value);">
								<option value="">Select</option>
								<?php 
								$sql = "SELECT `sno`, `client_name` FROM `client_details`";	
								$result = execute_query($sql);
								while ($row = mysqli_fetch_array($result)) { 
								echo '<option value="'.$row['sno'].'">'. $row['client_name'] . "</option>";
								 }
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Select Project</td>
						<td>
							<select class="form-control" id="project" name="project"></select>
						</td>
				</tr>
				<tr>
					<td>Start Date</td>
					<td><script type="text/javascript" language="javascript">
							<?php $tab=0; ?>
							document.writeln(DateInput('start_date', 'admin_employee_from', true, 'YYYY-MM-DD', ));
						</script>
					</td>
					
				</tr>
				<tr>
					<td>End Date</td>
					<td><script type="text/javascript" language="javascript">
							document.writeln(DateInput('end_date', 'admin_employee_from', true, 'YYYY-MM-DD',));
						</script>
					</td>
				</tr>
			</table>
		</form>
			<!-- Above is search part -->
		
			<div class="col-sm-3"></div>
				<div class="col-sm-6"><input type="submit" id="submit" value="Search" name="submit" class="form-control"></div></br><br>
				
				<div class="col-sm-12">
					<table class="table table-hover table-responsive table-bordered table-striped">
						<br><label>Details</label>
						<thead>
						<tr>
							<td><label>Project Name</lable></td>
							<td><label>Employee Name</lable></td>
							<td><label>Start Date</lable></td>
							<td><label>End Date</lable></td>
							<td><label>Rate</lable></td>
							<td><label>Days</lable></td>
							<td><label>Taxable Amount</lable></td>
							<td><label>Select</lable></td>
							
						</tr>
						</thead>
					<tbody>
						<form  method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"  name="confirm" enctype="multipart/form-data" onsubmit="return valid(this);">
						<?php
							if(isset($_POST['submit'])){
								$client_id= $_POST['client'];
								$project=$_POST['project'];

								$start_date=$_POST['start_date'];
								$end_date=$_POST['end_date'];
								//echo $project;
								//echo $client_id;
								//echo $start_date;
								if($project == "9999"){
								$sql="SELECT `sno`,`project_id`,`employee_id`, `start_from`, `from_to`, `rate`, `days`,`total` FROM `attendance` WHERE (client_id='$client_id'  AND (start_from >= '$start_date' AND from_to <= '$end_date') and invoice_status='') OR (days != '' AND invoice_status='')";
								//echo $sql;
								}
								else{
									$sql="SELECT  `sno`,`project_id`, `employee_id`, `start_from`, `from_to`, `rate`,`days`, `total` FROM `attendance` WHERE (client_id='$client_id' AND project_id='$project'  AND (start_from >= '$start_date' AND from_to <= '$end_date') AND project_id='$project' AND invoice_status='') OR (days != '' AND invoice_status='')"; 
									//echo $sql;
								}
								$query = execute_query($sql);
								if(mysqli_num_rows($query) > 0){
								while ($row = mysqli_fetch_array($query)) { 
									$var=$row['project_id'];
									$emp_id=$row['employee_id'];
									$sno=$row['sno'];
									//echo'<input type="hidden" name="emp_id_list[]" value="'.$emp_id.'">';
									echo'<tr>';
									echo'<td>';
									$sql1="SELECT `project_name` FROM `projects` WHERE sno='$var'";
									$query1 = execute_query($sql1);
									$row1 = mysqli_fetch_array($query1);
									echo $row1['project_name'];
									echo'</td>';
									echo'<td>';
									$sql2="SELECT employee_name FROM `employee` WHERE sno='$emp_id'";
									$query2 = execute_query($sql2);
									$row2 = mysqli_fetch_array($query2);
									echo $row2['employee_name'];
									echo'</td>';
									echo'<td>'.$row['start_from'].'</td>';
									echo'<td>'.$row['from_to'].'</td>';
									echo'<td>'.$row['rate'].'</td>';
									$rate1=$row['rate'];
									echo '<td>'.$row['days'].'</td>';
									echo'<td>'.$row['total'].'</td>';
									//$var11=$row['total'];
									echo'<td><input type="checkbox" checked name="check_list[]" value='.$sno.'></td>';
									echo'</tr>';
								}
								}
								else{
									$msg="No project Found";
								}
							
							
						}
						?>
						<input type="hidden" name="clientid" value="<?php echo $client_id; ?>">
					</tboddy>
				</table>
				
					<div class="pull-right" id="show">
						

					</div>
					<input type="hidden" name="project_id" value="<?php echo $_POST['project'] ?>">
					<input type="hidden" name="grand_total" value="<?php echo $total_amount ?>">
					<input type="hidden" name="client_id" value="<?php echo $_POST['client'] ?>">
				<table class="table table-hover table-responsive table-bordered table-striped">
					<tbody>
						<tr>
							<br><td>Payment Head</td>
							<td><input required type="text" class="form-control" id="payment_head" name="payment_head" placeholder="Enter Payment Head" >
								<span id="paymenterr" class="text-dander"></span>
						</tr>
						<tr>
							<td>Dept.</td>
							<td><input  required type="text" class="form-control" id="dept" name="dept"  placeholder="Enter dept" ></td>
								<span id="depterr" class="text-dander"></span>
						</tr>
						<tr>
							<td>Order No.</td>
							<td><input  required type="text" class="form-control" id="order_no" name="order_no"  placeholder="Enter Order No." ></td>
							<span id="ordererr" class="text-dander"></span>
						</tr>
					</tbody>
				</table>
				
					<input type="submit" name='confirm' value="Confirm and proceed" id="submit" class="form-control" >
				</form>
	
			</div>
			</div>
		</div>
	
</div>
<script>
function valid(){
	$fl=confirm('ARE YOU SURE ?');
	if($fl){
		return true;
	}
	else{
		return false;
	}
}
</script>
<?php
page_footer();
?>
