<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
$msg='';
$tab=1;
date_default_timezone_set('Asia/Calcutta');

if(isset($_GET['voucher'])){
    $sql = 'SELECT f.*, 
                    d.department_name_hindi, 
                    p.project_name_hindi,
                    bu.unit as unit_name, bu.unit_desc as unit_name_hindi,
                    v.firm_name, v.contractor_name,
                    sub.sub_department_hindi, dist.district_name_hindi,
                    pay.first_by, pay.first_to, pay.tot_debit, pay.tot_credit, 
                    pay.remarks, pay.timestamp,
                    COALESCE(pay.voucher_no, f.voucher_no) as voucher_no
            FROM invoice_account_fund_transafer f
            LEFT JOIN uprnss_department_name d ON f.department = d.sno
            LEFT JOIN uprnss_sub_department sub ON f.sub_department_id = sub.sno
            LEFT JOIN uprnss_district dist ON f.district = dist.sno
            LEFT JOIN uprnss_project_temp p ON f.project_name = p.sno
            LEFT JOIN billit_unit bu ON f.unit_id = bu.sno
            LEFT JOIN vendor v ON f.vendor_id = v.sno
            LEFT JOIN billit_invoice_erp_payment pay ON pay.table_name="invoice_account_fund_transafer" AND pay.table_id=f.sno
            WHERE f.sno="'.$_GET['voucher'].'"';
    $data = mysqli_fetch_assoc(execute_query($sql));
    
    // Get voucher lines
    $voucher_lines = [];
    if($data) {
        // Simple query to get voucher lines
        $lines_result = execute_query('SELECT * FROM billit_stock_erp_payment 
                                   WHERE journal_id IN (
                                       SELECT sno FROM billit_invoice_erp_payment 
                                       WHERE table_name="invoice_account_fund_transafer" 
                                       AND table_id="'.$data['sno'].'"
                                   ) 
                                   ORDER BY sno ASC');
        while($line = mysqli_fetch_assoc($lines_result)) {
            $voucher_lines[] = $line;
        }
    }
}
    
    // Helper function to get ledger name
    function get_ledger_name($ledger_id) {
        if(empty($ledger_id)) {
            return '';
        }
        
        // 1. Try direct lookup in billit_customer using get_ledger()
        $name = get_ledger($ledger_id);
        if($name != '') {
            return $name;
        }
        
        // 2. Try looking up as a tag in general_settings
        $result = execute_query('SELECT rate FROM general_settings WHERE `desc` = "' . mysqli_real_escape_string($GLOBALS['db'], $ledger_id) . '" LIMIT 1');
        if($result && $row = mysqli_fetch_assoc($result)) {
            // If the rate is a numeric ID, try getting the ledger name for it
            if(is_numeric($row['rate'])) {
                $name = get_ledger($row['rate']);
                if($name != '') return $name;
            }
            return $row['rate'];
        }
        
        return $ledger_id;
    }
?>
<html>
<head>
	<title>Fund Transfer Voucher</title>
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
    <title>Fund Transfer Voucher Print</title>
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
		<h4>Fund Transfer At: <?php echo get_division($data['unit_id']); ?></h4>
		
		<h4>Journal Voucher</h4>
		
		<div class="header-container">
			<div class="left">Voucher No.: <?php echo $data['voucher_no']; ?></div>
			<div class="right">Date: <?php echo date("d-m-Y", strtotime($data['timestamp'] ?? $data['transafer_date'])); ?></div>
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
				$i = 1;
				$tot_debit = 0;
				$tot_credit = 0;

				if(!empty($voucher_lines)) {
					// Use actual voucher lines from billit_stock_erp_payment
					$source_bank_amount = 0;
					$source_bank_name = '';
					$debit_entries = [];
					$credit_entries = [];
					
					foreach($voucher_lines as $line) {
						if(!empty($line['to']) && $line['amount'] > 0) {
							// This is a Credit entry
							$particulars = get_ledger_name($line['to']);
							$credit_entries[] = [
								'particulars' => $particulars,
								'amount' => $line['amount']
							];
						}
						elseif(!empty($line['by']) && $line['amount'] > 0) {
							// This is a Debit entry
							$particulars = get_ledger_name($line['by']);
							// Based on user request, move TDS/Income Tax to Credit even if saved as Debit
							if(stripos($particulars, 'TDS') !== false || stripos($particulars, 'Income Tax') !== false || stripos($particulars, 'IT') !== false) {
								$credit_entries[] = [
									'particulars' => $particulars,
									'amount' => $line['amount']
								];
							} else {
								$debit_entries[] = [
									'particulars' => $particulars,
									'amount' => $line['amount']
								];
							}
						}
					}
					
					// Display all Debit Entries first
					foreach($debit_entries as $debit) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$debit['particulars'].'</td>
								<td class="debit">'.number_format($debit['amount'], 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $debit['amount'];
					}
					
					// Display all Credit Entries
					foreach($credit_entries as $credit) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$credit['particulars'].'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($credit['amount'], 2).'</td>
							  </tr>';
						$tot_credit += $credit['amount'];
					}
					
				} else {
					// Fallback: Show based on component breakdown
					$total_debit_amount = 0;
					
					// Debit items: Net, CGST, SGST
					$net_payment = floatval($data['praposemoney']);
					$cgst = floatval($data['cgst_amount'] ?? 0);
					$sgst = floatval($data['sgst_amount'] ?? 0);
					
					// Credit items: Deductions
					$gsttds = floatval($data['gsttds'] ?? 0);
					$it = floatval($data['incometax'] ?? 0);
					$cess = floatval($data['leborses'] ?? 0);
					$centage = floatval($data['sentage'] ?? 0);

					// 1. Show Bank Transfer (Debit)
					echo '<tr>
							<td>'.$i++.'</td>
							<td>'.get_ledger_name($data['first_by'] ?: ($data['firm_name'] ?: ($data['to_bank_name'] ?: 'Bank Transfer'))).'</td>
							<td class="debit">'.number_format($net_payment, 2).'</td>
							<td class="credit"></td>
						  </tr>';
					$tot_debit += $net_payment;
					
					// 2. Show CGST (Debit)
					if($cgst > 0) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('CGST').'</td>
								<td class="debit">'.number_format($cgst, 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $cgst;
					}
					
					// 3. Show SGST (Debit)
					if($sgst > 0) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('SGST').'</td>
								<td class="debit">'.number_format($sgst, 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $sgst;
					}
					
					// 4. Show GST-TDS (Credit)
					if($gsttds > 0) {
						$tds_half = $gsttds / 2;
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('CGST-TDS Deducted').'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($tds_half, 2).'</td>
							  </tr>';
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('SGST-TDS Deducted').'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($tds_half, 2).'</td>
							  </tr>';
						$tot_credit += $gsttds;
					}
					
					// 5. Show Income Tax (Credit)
					if($it > 0) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('Income Tax Deducted').'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($it, 2).'</td>
							  </tr>';
						$tot_credit += $it;
					}
					
					// 6. Show other deductions on Credit side if exists
					if($cess > 0) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('LABOUR CESS').'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($cess, 2).'</td>
							  </tr>';
						$tot_credit += $cess;
					}
					if($centage > 0) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.get_ledger_name('CENTAGE').'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($centage, 2).'</td>
							  </tr>';
						$tot_credit += $centage;
					}

					// 7. HO Bank Account - The source bank (Credit)
					// It should be the balancing figure or the total transfer amount from HO
					$ho_bank_credit = $tot_debit - $tot_credit;
					// If ho_bank_credit is basically the net payout or specific amount in DB
					// In current screenshot logic, Source was credit for GROSS
					// But we want it to balance. 
					// Let's use the Gross from DB if available, otherwise Debit total
					
					$source_name = get_ledger_name($data['first_to'] ?: ($data['from_account_no'] ?: 'HO Bank Account'));
					echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$source_name.'</td>
							<td class="debit"></td>
							<td class="credit">'.number_format($ho_bank_credit, 2).'</td>
						  </tr>';
					$tot_credit += $ho_bank_credit;
				}
				
								
				echo '<tr class="">
						<td colspan="2" align="right"></br><b>On Account of:&nbsp; &nbsp;</b>'. $data['remark'].'<br></td>
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
