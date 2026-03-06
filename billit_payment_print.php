<?php
include("scripts/settings.php");
$msg = '';
include("scripts/billit_settings.php");
$msg = '';
$response = 0;
$finalmsg = '';
$tab = 1;
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Calcutta');

if(isset($_GET['id'])){
    // First get the main voucher info
    $sql = 'SELECT pay.* FROM billit_invoice_erp_payment pay WHERE pay.sno="'.$_GET['id'].'"';
    $old_data = mysqli_fetch_assoc(execute_query($sql));
    
    // Check if voucher data exists
    if (!$old_data) {
        echo "<h2>Error: Voucher not found</h2>";
        echo "<p>Voucher ID {$_GET['id']} does not exist or has been deleted.</p>";
        exit;
    }
    
    // Try to get unit and project info from different possible sources
    $unit_info = '';
    $project_info = '';
    $department_info = '';
    
    // Method 1: Check if it's a fund transfer and try to get from invoice_fund_transfer table
    if ($old_data['table_name'] == 'invoice_fund_transfer' && $old_data['table_id'] > 0) {
        // Try to get from invoice_fund_transfer table (if it has the columns)
        try {
            $fund_sql = "SELECT * FROM invoice_fund_transfer WHERE sno = '" . $old_data['table_id'] . "'";
            $fund_result = execute_query($fund_sql);
            if ($fund_result && mysqli_num_rows($fund_result) > 0) {
                $fund_data = mysqli_fetch_assoc($fund_result);
                // Check if the fields exist and have values
                if (isset($fund_data['unit_id']) && !empty($fund_data['unit_id'])) {
                    $unit_info = get_unit_name($fund_data['unit_id']);
                }
                if (isset($fund_data['project_name']) && !empty($fund_data['project_name'])) {
                    $project_info = get_project_name($fund_data['project_name']);
                }
                if (isset($fund_data['department']) && !empty($fund_data['department'])) {
                    $department_info = get_department_name($fund_data['department']);
                }
            }
        } catch (Exception $e) {
            // Ignore errors and continue with fallback
        }
    }
    
    // Method 2: Get unit info from billit_stock_erp_payment table (this is where unit_id is actually stored)
    if ($old_data && $old_data['sno']) {
        try {
            $stock_sql = "SELECT sp.*, 
                                d.department_name_hindi, 
                                p.project_name_hindi,
                                bu.unit as unit_name, bu.unit_desc as unit_name_hindi,
                                sub.sub_department_hindi, dist.district_name_hindi
                         FROM billit_stock_erp_payment sp
                         LEFT JOIN uprnss_department_name d ON sp.department_id = d.sno
                         LEFT JOIN uprnss_sub_department sub ON sp.sub_department_id = sub.sno
                         LEFT JOIN uprnss_district dist ON sp.district_id = dist.sno
                         LEFT JOIN uprnss_project_temp p ON sp.project_id = p.sno
                         LEFT JOIN billit_unit bu ON sp.unit_id = bu.sno
                         WHERE sp.journal_id='" . $old_data['sno'] . "' AND sp.unit_id != '' AND sp.unit_id IS NOT NULL
                         LIMIT 1";
            $stock_result = execute_query($stock_sql);
            if ($stock_result && mysqli_num_rows($stock_result) > 0) {
                $stock_data = mysqli_fetch_assoc($stock_result);
                // Use the unit information from stock table
                if (!empty($stock_data['unit_id'])) {
                    $unit_info = $stock_data['unit_name_hindi'] ?? $stock_data['unit_name'] ?? '';
                }
                // Also merge any other available data
                $old_data = array_merge($old_data, $stock_data);
            }
        } catch (Exception $e) {
            // Ignore errors and continue
        }
    }
    
    // Helper functions to get names
    function get_unit_name($unit_id) {
        if (empty($unit_id)) return '';
        $result = execute_query("SELECT unit, unit_desc FROM billit_unit WHERE sno = '" . $unit_id . "' LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['unit_desc'] ?? $row['unit'] ?? '';
        }
        return $unit_id;
    }
    
    function get_project_name($project_id) {
        if (empty($project_id)) return '';
        $result = execute_query("SELECT project_name_hindi FROM uprnss_project_temp WHERE sno = '" . $project_id . "' LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['project_name_hindi'] ?? '';
        }
        return $project_id;
    }
    
    function get_department_name($dept_id) {
        if (empty($dept_id)) return '';
        $result = execute_query("SELECT department_name_hindi FROM uprnss_department_name WHERE sno = '" . $dept_id . "' LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['department_name_hindi'] ?? '';
        }
        return $dept_id;
    }
}
?>
<html>
<head>
	<title>Payment Voucher</title>
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
    <title>Payment Entry Print</title>
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
		<h4>Payment  At: <?php echo get_division($old_data['unit_id']); ?></h4>
		
		<h4>Payment Voucher</h4>
		
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
				$sql = 'SELECT * FROM billit_stock_erp_payment WHERE journal_id="'.$old_data['sno'].'"';
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
