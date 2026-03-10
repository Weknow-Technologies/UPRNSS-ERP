<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
$msg='';
$msg='';
$response=0;
$finalmsg='';
$tab=1;
date_default_timezone_set('Asia/Calcutta');
//print_r($_POST);
if(isset($_GET['id'])){
	$sql = 'select * from billit_invoice_erp_receipt where sno="'.$_GET['id'].'"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
}

if(isset($_GET['voucher'])){
		
	$sql = 'select * from billit_invoice_erp_receipt where table_id="'.$_GET['voucher'].'"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
}
?>
<html>
<head>
	<title>Receipt Voucher</title>
	<style>
		body{width:1024px;}
		td, th{padding: 5px;}
		
			
		@media print {
			.total-row {
				font-weight: bold;
				background-color: transparent !important;
			}
			th {
				background-color: transparent !important;
			}
		}

		@media print {
			body::after {
				content: "Powered By AIPPCA";
				position: fixed;
				font-size: 50px;
				color: rgba(0, 0, 0, 0.1);
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%) rotate(-45deg);
				z-index: -2;
			}
		}
	</style>

	
</head>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Entry Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        h4 {
            margin: 5px 0;
			text-align: center;
        }
		p {
            margin: 2px 0;
			text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .debit, .credit {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #ddd;
        }
		
		.header-container {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 10px;
		}
    </style>
</head>
<body>
	<div style="margin-left: 30px; margin-right: 15px;">
		<div class="header">उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)</div>
		<div class="header">G-4/5 SECTOR-4 GOMTINAGAR VISTAR LUCKNOW 226010</div>
		<p>State Name: Uttar Pradesh, Code:09<p>
		<p>E-Mail: paccfedho@gmail.com<p>
		<h4>Receipt  At: <?php echo get_division($old_data['unit_id']); ?></h4>
		
		<h4>Journal Voucher</h4>
		
		<div class="header-container">
			<div class="left">Voucher No.: <?php echo $old_data['voucher_no']; ?></div>
			<div class="right">Date: <?php echo date("d-m-Y", strtotime($old_data['timestamp'])); ?></div>
		</div>

		<table>
			<thead>
				<tr>
					<th>S.No.</th>
					<th>Particulars</th>
					<th class="debit">Debit (₹)</th>
					<th class="credit">Credit (₹)</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$sql = 'SELECT * FROM billit_stock_erp_receipt WHERE journal_id="'.$old_data['sno'].'"';
				$result_trans = execute_query($sql);
				$i = 1;
				$tot_debit = 0;
				$tot_credit = 0;

				while ($row = mysqli_fetch_assoc($result_trans)) {
					$particulars = '';
					$debit = '';
					$credit = '';

					if (!empty($row['by'])) {
						$particulars = get_ledger($row['by']);
						$debit = number_format($row['amount'], 2);
						$tot_debit += $row['amount'];
					} else {
						$particulars = get_ledger($row['to']);
						$credit = number_format($row['amount'], 2);
						$tot_credit += $row['amount'];
					}

					echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$particulars.'</td>
							<td class="debit">'.$debit.'</td>
							<td class="credit">'.$credit.'</td>
						  </tr>';
				}
				
				// Add deduction details if available from invoice header
				$deduction_sql = 'SELECT * FROM invoice_fund_receive WHERE sno="'.$old_data['table_id'].'"';
				$deduction_result = execute_query($deduction_sql);
				if ($deduction_data = mysqli_fetch_assoc($deduction_result)) {
					// Get ledger account codes from general_settings for proper identification
					$gstw = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='GSTW'"));
					$advcen = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='ADVCEN'"));
					$gsttdsw = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='GSTTDSW'"));
					$cgsttds = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='CGSTTDSW'"));
					$sgsttds = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='SGSTTDSW'"));
					$labourw = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='LABOURCESSW'"));
					$ittds = mysqli_fetch_assoc(execute_query("select * from general_settings where `desc`='ITTDSW'"));
					
					// Show TDS DEDUCTED BY DEPARTMENT
					if (!empty($deduction_data['total_tds']) && $deduction_data['total_tds'] > 0) {
						$ledger_name = $ittds ? get_ledger($ittds['sno']) : 'TDS DEDUCTED BY DEPARTMENT';
						echo '<tr>
								<td>'.($i++).'</td>
								<td>'.$ledger_name.'</td>
								<td class="debit">'.number_format($deduction_data['total_tds'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $deduction_data['total_tds'];
					}
					
					// Show GST-TDS DEDUCTED BY DEPARTMENT
					if (!empty($deduction_data['total_gst_tds']) && $deduction_data['total_gst_tds'] > 0) {
						$ledger_name = $gsttdsw ? get_ledger($gsttdsw['sno']) : 'GST-TDS DEDUCTED BY DEPARTMENT';
						echo '<tr>
								<td>'.($i++).'</td>
								<td>'.$ledger_name.'</td>
								<td class="debit">'.number_format($deduction_data['total_gst_tds'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $deduction_data['total_gst_tds'];
					}
					
					// Show LABOUR CESS DEDUCTED BY DEPARTMENT
					if (!empty($deduction_data['total_labour']) && $deduction_data['total_labour'] > 0) {
						$ledger_name = $labourw ? get_ledger($labourw['sno']) : 'LABOUR CESS DEDUCTED BY DEPARTMENT';
						echo '<tr>
								<td>'.($i++).'</td>
								<td>'.$ledger_name.'</td>
								<td class="debit">'.number_format($deduction_data['total_labour'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $deduction_data['total_labour'];
					}
					
					// Show CGST
					if (!empty($deduction_data['total_cgst']) && $deduction_data['total_cgst'] > 0) {
						$ledger_name = $gstw ? get_ledger($gstw['sno']) : 'CGST';
						echo '<tr>
								<td>'.($i++).'</td>
								<td>'.$ledger_name.'</td>
								<td class="debit">'.number_format($deduction_data['total_cgst'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $deduction_data['total_cgst'];
					}
					
					// Show SGST
					if (!empty($deduction_data['total_sgst']) && $deduction_data['total_sgst'] > 0) {
						$ledger_name = $sgsttds ? get_ledger($sgsttds['sno']) : 'SGST';
						echo '<tr>
								<td>'.($i++).'</td>
								<td>'.$ledger_name.'</td>
								<td class="debit">'.number_format($deduction_data['total_sgst'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $deduction_data['total_sgst'];
					}
					
					// Show Advance Centage
					if (!empty($deduction_data['total_adv_centage']) && $deduction_data['total_adv_centage'] > 0) {
						$ledger_name = $advcen ? get_ledger($advcen['sno']) : 'ADVANCE CENTAGE DEDUCTED';
						echo '<tr>
								<td>'.($i++).'</td>
								<td>'.$ledger_name.'</td>
								<td class="debit">'.number_format($deduction_data['total_adv_centage'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $deduction_data['total_adv_centage'];
					}
				}
				
				echo '<tr class="">
						<td colspan="2" align="right"></br><b>On Account of:&nbsp; &nbsp;</b>'. $old_data['remarks'].'<br></td>
						<td></td>
						<td></td>
					  </tr>';
				
				echo '<tr class="total-row">
						<td colspan="2" align="right"></td>
						<td class="debit">'.number_format($tot_debit, 2).'</td>
						<td class="credit">'.number_format($tot_credit, 2).'</td>
					  </tr>';
				?>
			</tbody>
		</table>
		
		<div class="header-container">
			<div class="left"></div>
			<div class="right" style="margin-top:60px; margin-right:70px;">Authorised Signatory</div>
		</div>
		
	</div>
</body>
</html>