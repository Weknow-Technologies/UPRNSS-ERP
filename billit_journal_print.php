<?php
include("scripts/settings.php");$msg='';
include("scripts/billit_settings.php");$msg='';
$response=0;
$finalmsg='';
$tab=1;
date_default_timezone_set('Asia/Calcutta');
//print_r($_POST);
if(isset($_GET['id'])){
	$sql = 'select * from billit_invoice_journal where sno="'.$_GET['id'].'"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
}
?>
<html>
<head>
	<title>Journal</title>
	<style>
		body{width:1024px;}
		td, th{padding: 5px;}
	</style>
</head>
<body>
	<h3>Journal Entry : <?php echo $old_data['timestamp']; ?></h3>
	<h3>Unit Name : <?php echo get_division($old_data['unit_id']); ?></h3>
	<h3>Voucher No. : <?php echo $old_data['voucher_no']; ?></h3>
	<table border="1" width="100%" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th>S.No.</th>
				<th>Particulars</th>
				<th>Vendor</th>
				<th>Description</th>
				<th>Debit</th>
				<th>Credit</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$sql = 'select * from billit_stock_journal where journal_id="'.$old_data['sno'].'"';
			$result_trans = execute_query($sql);
			$i=1;
			$tot_debit=0;
			$tot_credit=0;
			while($row = mysqli_fetch_assoc($result_trans)){
				$particulars='';
				$debit='';
				$credit='';
				if($row['by']!=''){
					$particulars = get_ledger($row['by']);
					$debit = $row['amount'];
					$tot_debit+=$debit;
				}
				else{
					$particulars = get_ledger($row['to']);
					$credit = $row['amount'];
					$tot_credit+=$credit;
				}
				
				
				echo '<tr>
				<td>'.$i++.'</td>
				<td>'.$particulars.'</td>
				<td>'.$row['vendor'].'</td>
				<td>'.$row['remarks'].'</td>
				<td align="right">'.$debit.'</td>
				<td align="right">'.$credit.'</td>
				</tr>';
			}
			echo '<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th align="right">'.$tot_debit.'</th>
			<th align="right">'.$tot_credit.'</th>
			</tr>';
			
			?>
		</tbody>
		
	</table>
</body>
</html>