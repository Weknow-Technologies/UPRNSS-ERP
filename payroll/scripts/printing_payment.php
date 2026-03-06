<?php
include("settings.php");
page_header_start('Print Page');
page_header_end();
$msg='';
$sno1=1;
$tab=1;
?>
<?php
if($_GET['id'] !=''){
	$id=$_GET['id'];
	$sql="SELECT * FROM `payment` WHERE sno='$id'";
	$res=execute_query($sql);
	$row=mysqli_fetch_array($res);
}
?>
<div class="container">
	<div class="pull-right">
		<button name="print"  onclick="printDiv('print');"style="height:50px;width:80px;">Print</button>
	</div>
	<div class="col-sm-8" id="print">
		<div class="col-sm-12 text-center">
		<?php
			if($row['payment_type']=='Advance'){
				echo '<h2>Advance Pay Voucher</h2>';
			}else{
				echo '<h2>Salary Pay Voucher</h2>';
			}
		?>
		</div>
		<div class="col-sm-10" style="border:1px solid">
			<span class="pull-left">Date: <?php echo date('d-m-Y',strtotime($row['date'])); ?></span>
			<span class="pull-right">Payment No. <strong><?php echo $_GET['id']; ?></strong></span><br><br>
			<span class="pull-left"><?php echo'Paid to';?> <strong> <?php echo employee_name($row['emp_id']); ?> </strong> an amount of Rs.<strong><?php echo $row['amount']; ?></strong> on <strong> <?php echo $row['date']; ?></strong></span><br><br><br><br>
			<span class="pull-right">For WeKnow Technologies&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br><br>
			<span class="pull-right"><strong>Authorized Signatory</strong></span><br><br>
		</div>
	</div>
</div>
<script type="text/javascript">
function printDiv(divName) {
   	var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
	window.print();
	document.body.innerHTML = originalContents;
}
</script>
<?php
page_footer();
?>