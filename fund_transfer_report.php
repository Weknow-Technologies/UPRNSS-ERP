<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("scripts/settings.php");
$msg = '';
$msg1 = '';
$tab = 1;

page_header_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php
page_header_end();
page_sidebar();

// Handle delete
if (isset($_GET['delid'])) {
    $delid = intval($_GET['delid']);
    $sql = 'UPDATE invoice_account_fund_transafer SET status="5" WHERE sno="' . $delid . '"';
    execute_query($sql);
    $msg .= '<p class="text text-success">Deleted successfully</p>';
}

// Handle edit
if (isset($_GET['edit_sno'])) {
    $edit_sno = intval($_GET['edit_sno']);
    $sql = 'SELECT * FROM invoice_account_fund_transafer WHERE sno="' . $edit_sno . '"';
    $data = mysqli_fetch_assoc(execute_query($sql));

    if ($data) {
        $_POST = $data;
        $_POST['edit_sno'] = $edit_sno;
    }
}
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <!-- Report Section -->
                <div class="card card-custom">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-history me-2"></i>
                        <h6 class="mb-0 text-white">Recent Fund Transfers</h6>
                    </div>
                    <div class="card-body table-full-width table-responsive">
                        <?php if ($msg != '')
                            echo $msg; ?>

                        <table id="fundTransferReportTable" class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Voucher No.</th>
                                    <th>Transfer Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Order No</th>
                                    <th>Transfer Amount</th>
                                    <th>Net Payment</th>
                                    <th>Remark</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $isAdmin = (isset($_SESSION['username']) && in_array(strtolower((string) $_SESSION['username']), ['sadmin', 'headacc']));
                                    $divFilter = "";
                                    if (!$isAdmin && !empty($_SESSION['divisions'])) {
                                        $divIds = implode(',', array_map('intval', $_SESSION['divisions']));
                                        $divFilter = " AND f.unit_id IN ($divIds)";
                                    }

                                    $sql = 'SELECT f.*, 
               d.department_name_hindi, 
               p.project_name_hindi,
               ud.division_name,
               ud.division_name_english,
               v.firm_name, v.contractor_name
        FROM invoice_account_fund_transafer f
        LEFT JOIN uprnss_department_name d ON f.department = d.sno
        LEFT JOIN uprnss_project_temp p ON f.project_name = p.sno
        LEFT JOIN uprnss_division ud ON f.unit_id = ud.s_no
        LEFT JOIN vendor v ON f.vendor_id = v.sno
        WHERE f.status != "5"' . $divFilter . '
        ORDER BY f.sno DESC LIMIT 200';

                                    $result = execute_query($sql);
                                    $i = 1;

                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $actId = (int) $row['sno'];

                                            // Get from/to names
                                            $from_name = '';
                                            $to_name = '';

                                            // From field logic - Money is coming FROM Unit
                                            $from_name = $row['division_name'] ?? $row['division_name_english'] ?? 'Unit';

                                            // To field logic - Money is going TO Vendor
                                            if (!empty($row['vendor_id'])) {
                                                $to_name = $row['firm_name'] ?? 'Vendor';
                                            } else {
                                                $to_name = $row['firm_name'] ?? 'Vendor';
                                            }

                                            echo '<tr>
                                            <td>' . $i++ . '</td>
                                            <td>' . htmlspecialchars($row['voucher_no'] ?: 'FT' . date('Y') . sprintf('%04d', $row['sno'])) . '</td>
                                            <td>' . date('d-m-Y', strtotime($row['transafer_date'] ?? '')) . '</td>
                                            <td>' . htmlspecialchars($from_name) . '</td>
                                            <td>' . htmlspecialchars($to_name) . '</td>
                                            <td>' . htmlspecialchars($row['order_no'] ?? '') . '</td>
                                            <td class="text-right">' . number_format($row['transafer_amount'] ?? 0, 2) . '</td>
                                            <td class="text-right">' . number_format($row['praposemoney'] ?? 0, 2) . '</td>
                                            <td>' . htmlspecialchars($row['remark'] ?? '') . '</td>
                                            <td class="no-print actions-col" style="white-space:nowrap">
                                                <div class="dropdown">
                                                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                  </button>
                                                  <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="fund_transfer.php?edit_sno=' . $actId . '">✏️ Edit</a>
                                                    <a class="dropdown-item" target="_blank" href="fund_transfer_details_new.php?id=' . $actId . '">👁️ View Details</a>
                                                    <a class="dropdown-item" target="_blank" href="fund_transfer_voucher.php?voucher=' . $actId . '">🧾 Voucher</a>
                                                    
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="?delid=' . $actId . '" onclick="return confirm(\'Delete this entry?\')">🗑️ Delete</a>
                                                  </div>
                                                </div>
                                            </td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="10" class="text-center">No records found</td></tr>';
                                    }
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="10" class="text-center text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
page_footer_start();
?>

<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

<?php
page_footer_end();
?>

<!-- DataTables Buttons libs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="dataTables/Buttons-1.6.1/js/dataTables.buttons.min.js"></script>
<script src="dataTables/Buttons-1.6.1/js/buttons.bootstrap4.min.js"></script>
<script src="dataTables/Buttons-1.6.1/js/buttons.html5.min.js"></script>
<script src="dataTables/Buttons-1.6.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function () {
        if ($.fn.DataTable.isDataTable('#fundTransferReportTable')) {
            $('#fundTransferReportTable').DataTable().destroy();
        }
        $('#fundTransferReportTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[0, 'desc']],
            dom: '<"d-flex justify-content-between align-items-center mb-2"lB>frtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Fund Transfer Report',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Fund Transfer Report',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-secondary btn-sm',
                    title: 'Fund Transfer Report',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                }
            ],
            columnDefs: [{ orderable: false, targets: -1 }],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                paginate: { first: 'First', last: 'Last', next: '›', previous: '‹' }
            }
        });
    });
</script>