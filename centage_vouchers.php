<?php
include("scripts/settings.php");

page_header_start();
page_header_end();
page_sidebar();
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="header">
                        <h4 class="title">Centage Vouchers</h4>
                        <p class="category">View all generated centage vouchers</p>
                    </div>
                    
                    <div class="content">
                        <?php
                        // Get all vouchers
                        $sql = "SELECT cv.*, 
                                       ud.department_name_hindi as department_name,
                                       udist.district_name_english as district_name,
                                       upt.project_name_hindi as project_name
                                FROM centage_vouchers cv
                                LEFT JOIN uprnss_department_name ud ON cv.department_id = ud.sno
                                LEFT JOIN uprnss_district udist ON cv.district_id = udist.sno
                                LEFT JOIN uprnss_project_temp upt ON cv.project_id = upt.sno
                                ORDER BY cv.created_at DESC";
                        
                        $result = execute_query($sql);
                        ?>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="voucherTable">
                                <thead>
                                    <tr>
                                        <th>Voucher No</th>
                                        <th>Date</th>
                                        <th>Department</th>
                                        <th>District</th>
                                        <th>Project</th>
                                        <th>Debit Account</th>
                                        <th>Credit Account</th>
                                        <th>Amount</th>
                                        <th>Centage Rate</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sn = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['voucher_no']) . '</td>';
                                        echo '<td>' . date('d-m-Y', strtotime($row['voucher_date'])) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['department_name'] ?? 'N/A') . '</td>';
                                        echo '<td>' . htmlspecialchars($row['district_name'] ?? 'N/A') . '</td>';
                                        echo '<td>' . htmlspecialchars($row['project_name'] ?? $row['project_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['debit_account']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['credit_account']) . '</td>';
                                        echo '<td class="text-right">' . number_format($row['amount'], 2) . '</td>';
                                        echo '<td>' . $row['centage_rate'] . '%</td>';
                                        echo '<td>' . htmlspecialchars($row['created_by']) . '</td>';
                                        echo '</tr>';
                                        $sn++;
                                    }
                                    
                                    if (mysqli_num_rows($result) == 0) {
                                        echo '<tr><td colspan="10" class="text-center">No vouchers found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <a href="centage_management.php" class="btn btn-primary">
                                    <i class="fa fa-arrow-left"></i> Back to Centage Management
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $("#voucherTable").DataTable({
        "pageLength": 25,
        "order": [[1, "desc"]]
    });
});
</script>

<?php
page_footer_start();
page_footer_end();
?>
