<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
$msg = '';
$msg1 = '';
$tab = 1;

if (!isset($_GET['id'])) {
	header("Location: cash_voucher.php?view=view");
	exit();
}

page_header_start();
page_header_end();
page_sidebar();
?>
<div class="row">
	<div class="col-md-12">
		<div class="card strpied-tabled-with-hover">
			<div class="card-header ">
				<h4 class="card-title">Cash Voucher Details</h4>
			</div>
			<div class="card-body table-full-width table-responsive">

				<table class="table table-hover table-striped table-bordered">
					<thead>
						<tr>
							<th>S.No.</th>
							<th>Date</th>
							<th>Unit Name</th>
							<th>By (Parent)</th>
							<th>Voucher No.</th>
							<th>To (Particulars)</th>
							<th>Total Amount</th>
							<th class="no-print">Print</th>
							<th class="no-print">Edit</th>
							<th class="no-print">Delete</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$sql = 'SELECT * FROM `billit_invoice_cash_voucher` where sno="' . $_GET['id'] . '"';
						$result = execute_query($sql);
						$i = 1;
						if ($row = mysqli_fetch_assoc($result)) {
							echo '<tr>
								<td>' . $i++ . '</td>
                                <td>' . date("d-m-Y", strtotime($row['timestamp'])) . '</td>
                                <td>' . get_division($row['unit_id']) . '</td>
                                <td>' . get_ledger($row['first_by']) . '</td>
                                <td>' . $row['voucher_no'] . '</td>
                                <td>' . get_ledger($row['first_to']) . '</td>
                                <td>' . number_format($row['tot_debit'], 2) . '</td>
								<td class="no-print"><a href="cash_voucher_print.php?id=' . $row['sno'] . '" target="_blank" class="btn btn-sm btn-info">View</a></td>
								<td class="no-print text-center">
									<a href="cash_voucher.php?id=' . $row['sno'] . '" class="btn btn-sm btn-warning"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit Data"></span> Edit</a>
								</td>
								<td class="no-print text-center">
									<a href="cash_voucher.php?del=' . $row['sno'] . '" onClick="return confirm(\'Are you sure you want to delete this voucher?\');" class="btn btn-sm btn-danger"><span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span> Delete</a>
								</td>
								</tr>';
						} else {
							echo '<tr><td colspan="10" class="text-center">No Record Found</td></tr>';
						}
						?>
					</tbody>
				</table>

				<div class="mt-4">
					<h5>Journal Entries</h5>
					<table class="table table-bordered table-striped">
						<thead class="bg-light">
							<tr>
								<th>S.No.</th>
								<th>Description</th>
								<th>Vendor</th>
								<th>Type</th>
								<th>Ledger</th>
								<th class="text-right">Debit (₹)</th>
								<th class="text-right">Credit (₹)</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql_journal = 'SELECT * FROM billit_cash_voucher_journal WHERE journal_id="' . $_GET['id'] . '"';
							$result_journal = execute_query($sql_journal);
							$j = 1;
							$total_dr = 0;
							$total_cr = 0;
							while ($row_j = mysqli_fetch_assoc($result_journal)) {
								$debit = '';
								$credit = '';
								$type = '';
								$ledger_name = '';

								if (!empty($row_j['by'])) {
									$type = "By";
									$ledger_name = get_ledger($row_j['by']);
									$debit = $row_j['amount'];
									$total_dr += $debit;
								} else {
									$type = "To";
									$ledger_name = get_ledger($row_j['to']);
									$credit = $row_j['amount'];
									$total_cr += $credit;
								}

								echo '<tr>
									<td>' . $j++ . '</td>
									<td>' . $row_j['remarks'] . '</td>
									<td>' . $row_j['vendor'] . '</td>
									<td>' . $type . '</td>
									<td>' . $ledger_name . '</td>
									<td class="text-right">' . ($debit != '' ? number_format($debit, 2) : '-') . '</td>
									<td class="text-right">' . ($credit != '' ? number_format($credit, 2) : '-') . '</td>
								</tr>';
							}
							?>
						</tbody>
						<tfoot>
							<tr class="font-weight-bold table-info">
								<td colspan="5" class="text-right">Total:</td>
								<td class="text-right"><?php echo number_format($total_dr, 2); ?></td>
								<td class="text-right"><?php echo number_format($total_cr, 2); ?></td>
							</tr>
						</tfoot>
					</table>
				</div>

				<div class="mt-3 text-right no-print">
					<a href="cash_voucher.php?view=view" class="btn btn-secondary">Back to List</a>
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
