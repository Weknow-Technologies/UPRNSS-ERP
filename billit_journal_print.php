<?php
include("scripts/settings.php");
$msg = '';
include("scripts/billit_settings.php");
$msg = '';
$response = 0;
$finalmsg = '';
$tab = 1;
date_default_timezone_set('Asia/Calcutta');
//print_r($_POST);
if (isset($_GET['id'])) {
	$sql = 'select * from billit_invoice_journal where sno="' . $_GET['id'] . '"';
	$old_data = mysqli_fetch_assoc(execute_query($sql));
}
?>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Journal Entry Print</title>
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

			.total-row {
				font-weight: bold;
				background-color: transparent !important;
			}

			th {
				background-color: transparent !important;
			}
		}
	</style>
</head>

<body>
	<div style="margin-left: 30px; margin-right: 15px;">
		<div class="header">उत्तर प्रदेश राज्य निर्माण सहकारी संघ लि. (यू. पी. आर. एन. एस. एस.)</div>
		<div class="header">G-4/5 SECTOR-4 GOMTINAGAR VISTAR LUCKNOW 226010</div>
		<p>State Name: Uttar Pradesh, Code:09</p>
		<p>E-Mail: paccfedho@gmail.com</p>

		<h4>Payment At: <?php echo get_division($old_data['unit_id']); ?></h4>
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
					<!-- <th>Vendor</th> -->
					<th>Description</th>
					<th class="debit">Debit (₹)</th>
					<th class="credit">Credit (₹)</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$sql = 'SELECT * FROM billit_stock_journal WHERE journal_id="' . $old_data['sno'] . '"';
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
							<td>' . $i++ . '</td>
							<td>' . $particulars . '</td>
							<!-- <td>' . $row['vendor'] . '</td> -->
							<td>' . $row['remarks'] . '</td>
							<td class="debit">' . $debit . '</td>
							<td class="credit">' . $credit . '</td>
						  </tr>';
				}

				echo '<tr class="total-row">
						<td colspan="3" align="right">Total</td><!-- Vendor column commented out, changed from 4 to 3 -->
						<td class="debit">' . number_format($tot_debit, 2) . '</td>
						<td class="credit">' . number_format($tot_credit, 2) . '</td>
					  </tr>';
                      
                echo '<tr>
						<td colspan="5"><b>Narration:</b> ' . htmlspecialchars($old_data['remarks'] ?? '') . '</td>
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