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

								   
								   //echo '@@@@'.mysqli_num_rows($lines_result).'@@@';
        while($line = mysqli_fetch_assoc($lines_result)) {
            $voucher_lines[] = $line;
        }
    }
}
    
    // Helper function to get ledger name
    function get_ledger_name($ledger_id) {
        global $data;
        $unit_id = $data['unit_id'] ?? 0;

        if(empty($ledger_id)) {
            return '';
        }
        
        // 1. Try direct lookup in billit_customer using get_ledger()
        $name = get_ledger($ledger_id);
        if($name != '') {
            return $name;
        }
        
        $check_setting = function($desc) use ($unit_id) {
            if ($unit_id > 0) {
                $res = execute_query('SELECT rate FROM general_settings WHERE `desc` = "' . mysqli_real_escape_string($GLOBALS['db'], $desc) . '" AND unit_id="' . mysqli_real_escape_string($GLOBALS['db'], $unit_id) . '" LIMIT 1');
                if ($res && $r = mysqli_fetch_assoc($res)) return $r['rate'];
            }
            $res = execute_query('SELECT rate FROM general_settings WHERE `desc` = "' . mysqli_real_escape_string($GLOBALS['db'], $desc) . '" AND (unit_id IS NULL OR unit_id=0 OR unit_id="") LIMIT 1');
            if ($res && $r = mysqli_fetch_assoc($res)) return $r['rate'];
            return '';
        };

        $rate = $check_setting($ledger_id);

        // Fallback for CGST/SGST if user mapped the parent GST
        if ($rate == '' && ($ledger_id == 'BILL_CGST' || $ledger_id == 'BILL_SGST')) {
            $rate = $check_setting('BILL_GST');
            if ($rate != '') {
                $name = get_ledger($rate);
                if ($name != '') return ($ledger_id == 'BILL_CGST' ? "CGST ($name)" : "SGST ($name)");
            }
        }
        if ($rate == '' && ($ledger_id == 'BILL_CGST_TDS' || $ledger_id == 'BILL_SGST_TDS')) {
            $rate = $check_setting('BILL_GST_TDS');
            if ($rate != '') {
                $name = get_ledger($rate);
                if ($name != '') return ($ledger_id == 'BILL_CGST_TDS' ? "CGST TDS ($name)" : "SGST TDS ($name)");
            }
        }

        if($rate != '') {
            if(is_numeric($rate)) {
                $name = get_ledger($rate);
                if($name != '') return $name;
            }
            return $rate;
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

				// Get mapped project ledger name
				$project_ledger_name = '';
				if (!empty($data['project_name'])) {
					// Check erp_code mapping first (standard method)
					$res_erp = execute_query('SELECT erp_code, project_name_hindi FROM uprnss_project_temp WHERE sno="'.$data['project_name'].'" LIMIT 1');
					if ($res_erp && $row_erp = mysqli_fetch_assoc($res_erp)) {
						if ($row_erp['erp_code'] != '') {
							$res_led = execute_query('SELECT cus_name FROM billit_customer WHERE erp_code="'.mysqli_real_escape_string($GLOBALS['db'], $row_erp['erp_code']).'" LIMIT 1');
							if ($res_led && $row_led = mysqli_fetch_assoc($res_led)) {
								$project_ledger_name = $row_led['cus_name'];
							}
						}
						if ($project_ledger_name == '') {
							$project_ledger_name = $row_erp['project_name_hindi']; // Fallback to project name
						}
					}
					
					// Check general_settings mapping if not found
					if ($project_ledger_name == '' || $project_ledger_name == $row_erp['project_name_hindi']) {
						$res_set = execute_query('SELECT rate FROM general_settings WHERE `desc`="PROJECT_LEDGER_MAP" AND unit_id="'.$data['unit_id'].'" AND remark="'.$data['project_name'].'" LIMIT 1');
						if ($res_set && $row_set = mysqli_fetch_assoc($res_set)) {
							$mapped_name = get_ledger_name($row_set['rate']);
							if ($mapped_name != '') {
								$project_ledger_name = $mapped_name;
							}
						}
					}
				}
				$debit_entries = [];
                $credit_entries = []; 

				if(!empty($voucher_lines)) {
					// Use actual voucher lines from billit_stock_erp_payment
					$source_bank_amount = 0;
					$source_bank_name = '';
					
					// Get proper vendor name to use instead of generic ledger name if applicable
					$vendor_full_name = '';
					if(!empty($data['firm_name'])) {
						$vendor_full_name = $data['firm_name'];
						if(!empty($data['contractor_name'])) {
							$vendor_full_name .= ' (' . $data['contractor_name'] . ')';
						}
					}
					
					foreach($voucher_lines as $line) {
						if(!empty($line['to']) && $line['amount'] > 0) {
							// This is a Credit entry
							$particulars = get_ledger_name($line['to']);
							
							// Override vendor name if it matches vendor_id
							if(!empty($data['vendor_id']) && $line['to'] == $data['vendor_id'] && $vendor_full_name != '') {
								$particulars = $vendor_full_name . ' (Net Payment)';
							}
							
							if(!isset($credit_entries[$particulars])) {
								$credit_entries[$particulars] = 0;
							}
							$credit_entries[$particulars] += $line['amount'];
						}
						
						if(!empty($line['by']) && $line['amount'] > 0) {
							// This is a Debit entry
							$particulars = get_ledger_name($line['by']);
							
							if(!empty($data['vendor_id']) && $line['by'] == $data['vendor_id'] && $vendor_full_name != '') {
								$particulars = $vendor_full_name . ' (Net Payment)';
							}
							
							// Based on user request, move TDS/Income Tax to Credit even if saved as Debit
							if($particulars == 'TDS' || $particulars == 'Income Tax' || $particulars == 'IT' || $particulars == 'GST TDS' || $particulars == 'CGST TDS' || $particulars == 'SGST TDS') {

								if(!isset($credit_entries[$particulars])) {
									$credit_entries[$particulars] = 0;
								}
								$credit_entries[$particulars] += $line['amount'];
							} else {
								if(!isset($debit_entries[$particulars])) {
									$debit_entries[$particulars] = 0;
								}
								$debit_entries[$particulars] += $line['amount'];
							}
						}
					}
					
					// Display all Debit Entries first
					foreach($debit_entries as $particulars => $amount) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$particulars.'</td>
								<td class="debit">'.number_format($amount, 2).'</td>
								<td class="credit"></td>
							  </tr>';
						$tot_debit += $amount;
					}
					
					// Display all Credit Entries
					foreach($credit_entries as $particulars => $amount) {
						echo '<tr>
								<td>'.$i++.'</td>
								<td>'.$particulars.'</td>
								<td class="debit"></td>
								<td class="credit">'.number_format($amount, 2).'</td>
							  </tr>';
						$tot_credit += $amount;
					}
					
				}
				
								
				echo '<tr class="total-row">
						<td colspan="2" align="right">Total</td>
						<td class="debit">'.number_format($tot_debit, 2).'</td>
						<td class="credit">'.number_format($tot_credit, 2).'</td>
					  </tr>';
                      
                echo '<tr>
                        <td colspan="4"><b>Narration:</b> ' . htmlspecialchars($data['remark'] ?? '') . '</td>
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
