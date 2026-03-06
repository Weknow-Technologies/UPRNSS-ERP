<?php
include("scripts/settings.php");
$msg='';
$response=0;
$finalmsg='';
$tab=1;
date_default_timezone_set('Asia/Calcutta');

if(isset($_GET['voucher_no'])){
    $voucher_no = $_GET['voucher_no'];
    
    // Get voucher details
    $sql = "SELECT bir.*, 
                   ud.department_name_hindi as department_name
            FROM billit_invoice_erp_receipt bir
            LEFT JOIN uprnss_department_name ud ON bir.first_to COLLATE utf8mb3_unicode_ci = ud.ledger_id COLLATE utf8mb3_unicode_ci
            WHERE bir.voucher_no = ? AND bir.table_name = 'percentage_entries'
            LIMIT 1";
    
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $voucher_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $voucher = mysqli_fetch_assoc($result);
    
    if($voucher) {
        // Get project details
        $project_sql = "SELECT project_name_hindi FROM uprnss_project_temp WHERE sno = ?";
        $project_stmt = mysqli_prepare($db, $project_sql);
        mysqli_stmt_bind_param($project_stmt, "i", $voucher['table_id']);
        mysqli_stmt_execute($project_stmt);
        $project_result = mysqli_stmt_get_result($project_stmt);
        $project = mysqli_fetch_assoc($project_result);
        
        // Get district details
        $district_sql = "SELECT district_name_english FROM uprnss_district WHERE sno = (SELECT district_id FROM uprnss_project_temp WHERE sno = ?)";
        $district_stmt = mysqli_prepare($db, $district_sql);
        mysqli_stmt_bind_param($district_stmt, "i", $voucher['table_id']);
        mysqli_stmt_execute($district_stmt);
        $district_result = mysqli_stmt_get_result($district_stmt);
        $district = mysqli_fetch_assoc($district_result);
        
        $voucher['project_name'] = $project['project_name_hindi'] ?? 'N/A';
        $voucher['district_name'] = $district['district_name_english'] ?? 'N/A';
    }
}
?>
<html>
<head>
    <title>Centage Voucher</title>
    <style>
        body{width:1024px; font-family: Arial, sans-serif;}
        td, th{padding: 8px; border: 1px solid #000;}
        table{border-collapse: collapse; width: 100%; margin: 20px 0;}
        h3{margin: 10px 0;}
        .header{text-align: center;}
        .amount{text-align: right;}
        .no-border{border: none;}
    </style>
</head>
<body>
    <div class="header">
        <h2>Centage Entry Voucher</h2>
    </div>
    
    <h3>Journal Entry : <?php echo $voucher['timestamp']; ?></h3>
    <h3>Department : <?php echo $voucher['department_name'] ?? 'N/A'; ?></h3>
    <h3>District : <?php echo $voucher['district_name'] ?? 'N/A'; ?></h3>
    <h3>Voucher No. : <?php echo $voucher['voucher_no']; ?></h3>
    
    <table border="1" width="100%" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Particulars</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?php echo $voucher['first_by']; ?></td>
                <td><?php echo $voucher['remarks']; ?></td>
                <td class="amount"><?php echo number_format($voucher['tot_debit'], 2); ?></td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td>2</td>
                <td><?php echo $voucher['first_to']; ?></td>
                <td><?php echo $voucher['remarks']; ?></td>
                <td class="amount"></td>
                <td class="amount"><?php echo number_format($voucher['tot_credit'], 2); ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: right;">Total:</th>
                <th class="amount"><?php echo number_format($voucher['tot_debit'], 2); ?></th>
                <th class="amount"><?php echo number_format($voucher['tot_credit'], 2); ?></th>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 30px;">
        <p><strong>Project:</strong> <?php echo $voucher['project_name'] ?? 'N/A'; ?></p>
        <!-- <p><strong>Created By:</strong> <?php echo $voucher['created_by']; ?></p> -->
        <p><strong>Created On:</strong> <?php echo $voucher['creation_time']; ?></p>
    </div>
    
    <div style="margin-top: 50px; text-align: center;">
        <p>_________________________</p>
        <p>Authorized Signatory</p>
    </div>
</body>
</html>
