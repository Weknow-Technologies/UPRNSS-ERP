<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
$msg = '';
$msg = '';
$response = 0;
$finalmsg = '';
$tab = 1;
date_default_timezone_set('Asia/Calcutta');
if (isset($_GET['id'])) {
	$sql = 'select * from billit_invoice_erp_receipt where sno="' . $_GET['id'] . '"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
}

if (isset($_GET['voucher'])) {

	$sql = 'select * from billit_invoice_erp_receipt where table_id="' . $_GET['voucher'] . '"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
}
?>
<html>

<head>
	<title>Receipt Voucher</title>
	<style>
		body {
			width: 1024px;
		}

		td,
		th {
			padding: 5px;
		}


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
            margin: 20px auto;
            width: 1024px;
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

		th,
		td {
			border: 1px solid black;
			padding: 8px;
			text-align: left;
		}

		th {
			background-color: #f2f2f2;
		}

		.debit,
		.credit {
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
		<p>State Name: Uttar Pradesh, Code:09
		<p>
		<p>E-Mail: paccfedho@gmail.com
		<p>
		<h4><?php echo get_division($old_data['unit_id']); ?></h4>

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
				$sql = 'SELECT * FROM billit_stock_erp_receipt WHERE journal_id="' . $old_data['sno'] . '"';
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
						
						
						$debit_amt = $row['amount'];
						$tot_debit += $debit_amt;
						
						// Specifically split 'TDS DEDUCTED BY DEPARTMENT' as requested
						if (trim($particulars) == "TDS DEDUCTED BY DEPARTMENT") {
							$half = $debit_amt / 2;
							echo '<tr>
									<td>' . $i++ . '</td>
									<td>CGST TDS DEDUCTED BY DEPARTMENT</td>
									<td class="debit">' . number_format($half, 2) . '</td>
									<td class="credit"></td>
								  </tr>';
							echo '<tr>
									<td>' . $i++ . '</td>
									<td>SGST TDS DEDUCTED BY DEPARTMENT</td>
									<td class="debit">' . number_format($half, 2) . '</td>
									<td class="credit"></td>
								  </tr>';
							continue;
						}

						$debit = number_format($debit_amt, 2);
					} else {
						$particulars = get_ledger($row['to']);
						$credit = number_format($row['amount'], 2);
						$tot_credit += $row['amount'];
						$debit = '';
					}

					echo '<tr>
							<td>' . $i++ . '</td>
							<td>' . $particulars . '</td>
							<td class="debit">' . $debit . '</td>
							<td class="credit">' . $credit . '</td>
						  </tr>';
				}

				echo '<tr class="total-row">
						<td colspan="2" align="right">Total</td>
						<td class="debit">' . number_format($tot_debit, 2) . '</td>
						<td class="credit">' . number_format($tot_credit, 2) . '</td>
					  </tr>';

				echo '<tr>
						<td colspan="4"><b>Narration:</b> ' . htmlspecialchars($old_data['remarks'] ?? '') . '</td>
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