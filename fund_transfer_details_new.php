<?php
include("scripts/settings.php");
$msg = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: fund_transfer_report.php');
    exit;
}

$id = intval($_GET['id']);

// Get main fund transfer details
$sql = 'SELECT f.*, d.department_name_hindi, d.department_name_english, 
               p.project_name_hindi, p.project_name,
               v.firm_name, v.contractor_name,
               ot.title_name as other_title_name
        FROM invoice_account_fund_transafer f
        LEFT JOIN uprnss_department_name d ON f.department = d.sno
        LEFT JOIN uprnss_project_temp p ON f.project_name = p.sno
        LEFT JOIN vendor v ON f.vendor_id = v.sno
        LEFT JOIN fund_transfar_other_title ot ON f.other_title = ot.sno
        WHERE f.sno = "' . $id . '"';

$result = execute_query($sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $msg = '<div class="alert alert-danger">Fund transfer not found!</div>';
}

// Get voucher details (check if exists)
$voucher = mysqli_fetch_assoc(execute_query('SELECT * FROM billit_invoice_erp_payment 
                                             WHERE table_name="invoice_account_fund_transafer" 
                                             AND table_id="' . $id . '" LIMIT 1'));

// Get voucher lines (only if journal exists)
$voucher_lines = [];
if ($voucher) {
    $lines_result = execute_query('SELECT * FROM billit_stock_erp_payment 
                                   WHERE journal_id="' . $voucher['sno'] . '" 
                                   ORDER BY sno ASC');
    while ($line = mysqli_fetch_assoc($lines_result)) {
        $voucher_lines[] = $line;
    }
} else {
    // Create dummy voucher lines from main table when journal entries are missing
    if ($data) {
        // Add net payment as debit
        if (!empty($data['praposemoney']) && $data['praposemoney'] > 0) {
            $voucher_lines[] = [
                'by' => 'Bank Transfer',
                'to' => '',
                'amount' => $data['praposemoney']
            ];
        }
        // Add GST TDS as debit
        if (!empty($data['gsttds']) && $data['gsttds'] > 0) {
            $voucher_lines[] = [
                'by' => 'GST TDS',
                'to' => '',
                'amount' => $data['gsttds']
            ];
        }
        // Add Labour Cess as debit
        if (!empty($data['leborses']) && $data['leborses'] > 0) {
            $voucher_lines[] = [
                'by' => 'Labour Cess',
                'to' => '',
                'amount' => $data['leborses']
            ];
        }
        // Add Income Tax as debit
        if (!empty($data['incometax']) && $data['incometax'] > 0) {
            $voucher_lines[] = [
                'by' => 'Income Tax',
                'to' => '',
                'amount' => $data['incometax']
            ];
        }
        // Add Security as debit
        if (!empty($data['security']) && $data['security'] > 0) {
            $voucher_lines[] = [
                'by' => 'Security',
                'to' => '',
                'amount' => $data['security']
            ];
        }
        // Add Hold Amount as debit
        if (!empty($data['other_amount']) && $data['other_amount'] > 0) {
            $voucher_lines[] = [
                'by' => (!empty($data['other_title_name']) ? $data['other_title_name'] : 'Hold Amount'),
                'to' => '',
                'amount' => $data['other_amount']
            ];
        }
        // Add ADV Cess as debit
        if (!empty($data['sentage']) && $data['sentage'] > 0) {
            $voucher_lines[] = [
                'by' => 'ADV Cess',
                'to' => '',
                'amount' => $data['sentage']
            ];
        }
        // Add source bank as credit
        $voucher_lines[] = [
            'by' => '',
            'to' => 'HO Bank Account',
            'amount' => $data['transafer_amount']
        ];
    }
}

?>
<style>
    .detail-header {
        background: linear-gradient(135deg, #ae1f20 0%, #dc2626 100%);
        color: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
    }

    .detail-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #ae1f20;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #ae1f20;
        margin-bottom: 15px;
        letter-spacing: 0.5px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: #374151;
        min-width: 150px;
    }

    .detail-value {
        color: #6b7280;
        text-align: right;
        font-weight: 500;
    }

    .amount-value {
        color: #059669;
        font-weight: 700;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background-color: #dcfce7;
        color: #15803d;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #b45309;
    }

    .status-deleted {
        background-color: #fef2f2;
        color: #b91c1c;
    }

    .voucher-table {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .voucher-table th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .voucher-table td {
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
    }

    .amount-cell {
        text-align: right;
        font-weight: 600;
        color: #059669;
    }

    .btn-back {
        background-color: #6b7280;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background-color: #4b5563;
        color: white;
    }

    .calculation-summary {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .summary-item:last-child {
        border-bottom: none;
        font-size: 18px;
        font-weight: 700;
        color: #fbbf24;
    }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-2"><i class="fas fa-exchange-alt me-2"></i>Fund Transfer Details</h3>
                <p class="mb-0">Order No: <?php echo htmlspecialchars($data['order_no'] ?? ''); ?></p>
            </div>
            <div>
                <a href="fund_transfer_report.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Report
                </a>
            </div>
        </div>
    </div>

    <?php echo $msg; ?>

    <?php if ($data): ?>
        <!-- Basic Information -->
        <div class="detail-section">
            <div class="section-title"><i class="fas fa-info-circle me-2"></i>Basic Information</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">Order No:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['order_no'] ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value"><?php echo date('d-m-Y', strtotime($data['order_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Transfer Date:</span>
                        <span class="detail-value"><?php echo date('d-m-Y', strtotime($data['transafer_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span
                            class="detail-value"><?php echo htmlspecialchars($data['department_name_english'] . " (" . $data['department_name_hindi'] . ")" ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sub Department:</span>
                        <span
                            class="detail-value"><?php echo htmlspecialchars($data['sub_department_name_hindi'] ?? ''); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">District:</span>
                        <span
                            class="detail-value"><?php echo htmlspecialchars($data['district_name_hindi'] ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Project:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['project_name_hindi'] ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Unit:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['unit_name_hindi'] ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Vendor:</span>
                        <span
                            class="detail-value"><?php echo htmlspecialchars($data['firm_name'] . ' (' . $data['contractor_name'] . ')'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <?php
                            $status_class = 'status-pending';
                            $status_text = 'Pending';

                            if ($data['status'] == '1') {
                                $status_class = 'status-active';
                                $status_text = 'Active';
                            } elseif ($data['status'] == '5') {
                                $status_class = 'status-deleted';
                                $status_text = 'Deleted';
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Details -->
        <div class="detail-section">
            <div class="section-title"><i class="fas fa-rupee-sign me-2"></i>Financial Details</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">Bill Amount:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['transafer_amount'] ?? 0), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">GST Amount:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['gstdeduction'] ?? 0), 2); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">Income Tax:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['incometax'] ?? 0), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">GST TDS:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['gsttds'] ?? 0), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Security Amount:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['security'] ?? 0), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Labour Cess:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['leborses'] ?? 0), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><?php echo htmlspecialchars($data['other_title_name'] ?? 'Hold Amount'); ?>:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['other_amount'] ?? 0), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Net Payment:</span>
                        <span
                            class="detail-value amount-value">₹<?php echo number_format((float)($data['praposemoney'] ?? 0), 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="detail-section">
            <div class="section-title"><i class="fas fa-university me-2"></i>Bank Details</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">From Account:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['from_account_no'] ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">To Bank:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['to_bank_name'] ?? ''); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">To Account No:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['to_account_no'] ?? ''); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">IFSC Code:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($data['to_bank_ifsc'] ?? ''); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <?php if (!empty($data['remark'])): ?>
            <div class="detail-section">
                <div class="section-title"><i class="fas fa-comment me-2"></i>Remarks</div>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($data['remark'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Voucher Details -->
        <?php if ($voucher && !empty($voucher_lines)): ?>
            <div class="detail-section">
                <div class="section-title"><i class="fas fa-file-invoice me-2"></i>Voucher Details</div>
                <div class="detail-row mb-3">
                    <span class="detail-label">Voucher No:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($voucher['voucher_no']); ?></span>
                </div>

                <div class="voucher-table">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_debit = 0;
                            $total_credit = 0;

                            foreach ($voucher_lines as $line) {
                                $debit = (!empty($line['by']) && $line['amount'] > 0) ? $line['amount'] : 0;
                                $credit = (!empty($line['to']) && $line['amount'] > 0) ? $line['amount'] : 0;


                                $total_debit += $debit;
                                $total_credit += $credit;

                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($line['particulars'] ?? ($line['by'] ?: $line['to'])) . '</td>';
                                echo '<td class="amount-cell">' . ($debit > 0 ? '₹' . number_format($debit, 2) : '') . '</td>';
                                echo '<td class="amount-cell">' . ($credit > 0 ? '₹' . number_format($credit, 2) : '') . '</td>';
                                echo '<td><span class="badge bg-info">' . htmlspecialchars($line['transaction_type'] ?? 'Payment') . '</span></td>';
                                echo '</tr>';
                            }

                            echo '<tr class="table-primary fw-bold">';
                            echo '<td>Total</td>';
                            echo '<td class="amount-cell">₹' . number_format($total_debit, 2) . '</td>';
                            echo '<td class="amount-cell">₹' . number_format($total_credit, 2) . '</td>';
                            echo '<td></td>';
                            echo '</tr>';
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Calculation Summary -->
        <div class="calculation-summary">
            <h5 class="text-center mb-3">📊 Calculation Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="summary-item">
                        <span>Bill Amount:</span>
                        <span>₹<?php echo number_format((float)($data['transafer_amount'] ?? 0), 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Additions:</span>
<span style="color: #fbbf24;">₹<?php echo number_format((float)($data['praposemoney'] ?? 0), 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Deductions:</span>
                        <span style="color: #ef4444;">₹<?php
                       $total_deductions = (float)($data['incometax'] ?? 0) +
                      (float)($data['gsttds'] ?? 0) +
                      (float)($data['security'] ?? 0) +
                      (float)($data['leborses'] ?? 0) +
    (float)($data['other_amount'] ?? 0);
echo number_format((float)$total_deductions, 2);

                        ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-item">
                        <span>Total Expenditure:</span>
                        <span>₹<?php echo number_format((float)($data['total_expen'] ?? $data['transafer_amount'] ?? 0), 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>NET Payment:</span>
                        <span style="color: #fbbf24;">₹<?php echo number_format((float)($data['praposemoney'] ?? 0), 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-4">
            <?php if ($data['status'] != '5'): ?>
                <a href="fund_transfer.php?edit_sno=<?php echo $data['sno']; ?>" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i>Edit Transfer
                </a>
                <a href="fund_transfer_report.php?delid=<?php echo $data['sno']; ?>" class="btn btn-danger me-2"
                    onclick="return confirm('Are you sure you want to delete this fund transfer?')">
                    <i class="fas fa-trash me-2"></i>Delete Transfer
                </a>
            <?php endif; ?>

            <?php if ($voucher): ?>
                <a href="view_voucher.php?id=<?php echo $voucher['sno']; ?>&type=payment" class="btn btn-success me-2">
                    <i class="fas fa-file-invoice me-2"></i>View Voucher
                </a>
            <?php endif; ?>

            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print me-2"></i>Print Details
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
page_footer_start();
page_footer_end();
?>