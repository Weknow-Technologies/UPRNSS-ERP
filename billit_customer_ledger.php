ff<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");
error_reporting(E_ALL);
$msg = '';
page_header_start();
$tab = 1;
$response = 1;

if (isset($_POST['date_from'])) {
	$_SESSION['report_ledger_date_from'] = $_POST['date_from'];
	$_SESSION['report_ledger_date_to'] = $_POST['date_to'];
} elseif (!isset($_SESSION['report_ledger_date_from'])) {
	$_SESSION['report_ledger_date_from'] = date("Y-m-01");
	$_SESSION['report_ledger_date_to'] = date("Y-m-d");
}

if (isset($_GET['ledid'])) {
	if (isset($_GET['t'])) {
		if ($_GET['i_o'] == 'i') {
			$sql = 'select sno, concat(description, " (Income)") as cus_name, "" as address, "" as mobile, 0 as opening_balance, treat_as_ledger as parent from billit_stock_available where sno=' . $_GET['ledid'];
		} else {
			$sql = 'select sno, concat(description, " (Expense)") as cus_name, "" as address, "" as mobile, 0 as opening_balance treat_as_ledger as parent from billit_stock_available where sno=' . $_GET['ledid'];
		}
		$billit_customer = mysqli_fetch_array(execute_query($sql));

	} else {
		$sql = "select * from billit_customer where sno=" . $_GET['ledid'];
		$billit_customer = mysqli_fetch_array(execute_query($sql));
	}
	$response = 3;
}
if (isset($_GET['del'])) {
	$sql = "delete from billit_customer_transactions where sno=" . $_GET['del'];
	$delete = execute_query($sql);
	$response = 3;
}

if (isset($_GET['del_j'])) {
	$sql = "delete from billit_stock_journal where sno=" . $_GET['del_j'];
	$delete = execute_query($sql);
	$response = 3;
}

if (isset($_GET['report'])) {
	$sql = 'select * from billit_customer where sno="' . $_GET['report'] . '"';
	$billit_customer = mysqli_fetch_array(execute_query($sql));
	$response = 3;
}


if (isset($_POST['cust_name'])) {
	$_SESSION['report_ledger_cust_name'] = $_POST['cust_name'];
	$_SESSION['report_ledger_category'] = $_POST['category'];
	$_SESSION['report_ledger_cus_type'] = $_POST['cus_type'];
	$_SESSION['report_ledger_amount_symbol'] = $_POST['amount_symbol'];
	$_SESSION['report_ledger_amount'] = $_POST['amount'];
	$_SESSION['report_ledger_parent'] = $_POST['parent'];
	$_SESSION['report_ledger_division'] = $_POST['division'];
	$response = 1;
	$filter = '';
	$filter_stock = '';
	$_SESSION['sql_ledger_result_filter'] = "select * from billit_customer where 1=1 ";
	if ($_POST['cust_sno'] != '') {
		$filter .= " and sno=" . $_POST['cust_sno'];
	}
	if ($_POST['division'] != '') {
		$filter .= " and unit_id=" . $_POST['division'];
	}
	if ($_POST['cust_name']) {
		$filter .= " and cus_name like '%" . $_POST['cust_name'] . "%'";
		$filter_stock .= ' and description like "%' . $_POST['cust_name'] . '%"';
	}
	if ($_POST['category'] != '') {
		$filter .= " and category=" . $_POST['category'];
	}
	if ($_POST['cus_type'] != '') {
		$filter .= " and cus_type=" . $_POST['cus_type'];
	}
	if ($_POST['parent'] != '') {
		if ($_POST['parent'] == 'NA') {
			$_POST['parent'] = '';
			$filter .= " and (parent='" . $_POST['parent'] . "' or parent is null or parent='0')";
			$filter_stock .= ' and treat_as_ledger="' . $_POST['parent'] . '"';
			$_POST['parent'] = 'NA';

		} else {
			$filter .= " and parent='" . $_POST['parent'] . "'";
			$filter_stock .= ' and treat_as_ledger="' . $_POST['parent'] . '"';
		}
	}
	$_SESSION['sql_ledger_result_filter'] = "(select sno, cus_name, category, cus_type, address, mobile, 'billit_customer' as type, parent, auto_ledger, auto_ledger_value from billit_customer where 0=0 $filter) union all (select sno, description as cus_name, 0 as category, 0 as cus_type, '' as address, '' as mobile, 'stock' as type, treat_as_ledger as parent, '' as auto_ledger, '' as auto_ledger_value from billit_stock_available where treat_as_ledger!='' and treat_as_ledger is not null $filter_stock)";
} elseif (!isset($_SESSION['sql_ledger_result_filter'])) {
	$_SESSION['sql_ledger_result_filter'] = "(select sno, cus_name, category, cus_type, address, mobile, 'billit_customer' as type, parent, auto_ledger, auto_ledger_value from billit_customer where 0=0) union all (select sno, description as cus_name, 0 as category, 0 as cus_type, '' as address, '' as mobile, 'stock' as type, treat_as_ledger as parent, '' as auto_ledger, '' as auto_ledger_value from billit_stock_available where treat_as_ledger!='' and treat_as_ledger is not null)";
	$_SESSION['sql_ledger_result_filter'] .= ' order by cus_name';
}
$result_data = execute_query($_SESSION['sql_ledger_result_filter']);

?>
<style>
	.edit {
		//width: 100%;
		height: 25px;
	}

	.editMode {
		background: #666666;
		padding: 5px;
		color: #fff;
	}

	/* Table Layout */
	table {
		border: 3px solid lavender;
		border-radius: 3px;
	}

	table tr:nth-child(1) {
		background-color: dodgerblue;
	}

	table tr:nth-child(1) th {
		padding: 10px 0px;
		letter-spacing: 1px;
	}

	/* Table rows and columns */
	table td {
		padding: 10px;
	}

	table tr:nth-child(even) {
		background-color: lavender;
		color: black;
	}
</style>
<script language="javascript" type="text/javascript">

	$(function () {
		var options1 = {
			source: "scripts/ajax.php?id=cust_name",
			minLength: 1,
			select: function (event, ui) {
				log(ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.id :
					"Nothing selected, input was " + this.value);
			},
			select: function (event, ui) {
				$("#cust_name").val(ui.item.cust_name);
				$("#cust_mobile").val(ui.item.cust_mobile);
				$("#cust_sno").val(ui.item.id);
				return false;
			}
		};
		$("input#cust_name").on("keydown.autocomplete", function () {
			$(this).autocomplete(options1);
		});
	});
</script>
<?php
page_header_end();
page_sidebar();

switch ($response) {
	case $response == 1: {
		?>
			<div class="row">
				<div class="col-md-12">
					<?php
					if ($msg != '') {
						echo '<h5>' . alert($msg) . '</h5>';
					}
					?>
					<form name="form_filters" id="form_filters" enctype="multipart/form-data"
						action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
						<table class=" table table-striped table-hover table-bordered">
							<tr>
								<th>Name</th>
								<td><input type="text" name="cust_name" tabindex="<?php echo $tab++; ?>" id="cust_name" value="<?php if (isset($_SESSION['report_ledger_cust_name'])) {
									   echo $_SESSION['report_ledger_cust_name'];
								   } ?>" class="field text medium" />
									<input type="hidden" id="comment">
								</td>
								<th>Date From</th>
								<td>
									<script type="text/javascript" language="javascript">
										document.writeln(DateInput('date_from', 'filters', false, 'YYYY-MM-DD', '<?php if (isset($_SESSION['report_ledger_date_from'])) {
											echo $_SESSION['report_ledger_date_from'];
										} else {
											echo date("Y-m-d");
										} ?>', '<?php echo $tab++;
										 $tab += 3; ?>'));
									</script><input type="hidden" name="custsno" id="custsno">
								</td>
								<th>Date To</th>
								<td colspan="2">
									<script type="text/javascript" language="javascript">
										document.writeln(DateInput('date_to', 'filters', false, 'YYYY-MM-DD', '<?php if (isset($_SESSION['report_ledger_date_to'])) {
											echo $_SESSION['report_ledger_date_to'];
										} else {
											echo date("Y-m-d");
										} ?>', '<?php echo $tab++;
										 $tab += 3; ?>'));
									</script><input type="hidden" name="cust_sno" id="cust_sno">
								</td>
							</tr>
							<tr>
								<th>Parent</th>
								<th><select name="parent" tabindex="<?php echo $tab++; ?>">
										<option></option>
										<option value="NA" <?php if (isset($_POST['parent'])) {
											if ($_POST['parent'] == 'NA') {
												echo ' selected="selected"';
											}
										} ?>>No Parent</option>
										<?php
										$sql = 'select * from billit_pl_heads';
										$row_heads = execute_query($sql);
										while ($heads_details = mysqli_fetch_array($row_heads)) {
											echo '<option value="' . $heads_details['sno'] . '"';
											if (isset($_SESSION['report_ledger_parent'])) {
												if ($_SESSION['report_ledger_parent'] == $heads_details['sno']) {
													echo ' selected="selected" ';
												}
											}
											echo '>' . $heads_details['description'] . '</option>';
										}
										?>
									</select></th>
								<th>Mobile</th>
								<th><input type="text" name="mobile" tabindex="<?php echo $tab++; ?>" id="mobile" value="<?php if (isset($_SESSION['report_ledger_mobile'])) {
									   echo $_SESSION['report_ledger_mobile'];
								   } ?>" class="field text medium" /></th>
								<th>Amount</th>
								<td colspan="2">
									<select name="amount_symbol" id="amount_symbol" tabindex="<?php echo $tab++; ?>">
										<option value="=" <?php if (isset($_SESSION['report_ledger_amount_symbol'])) {
											if ($_SESSION['report_ledger_amount_symbol'] == '=') {
												echo 'selected';
											}
										} ?>>=</option>
										<option value=">=" <?php if (isset($_SESSION['report_ledger_amount_symbol'])) {
											if ($_SESSION['report_ledger_amount_symbol'] == '>=') {
												echo 'selected';
											}
										} ?>>>=</option>
										<option value="<=" <?php if (isset($_SESSION['report_ledger_amount_symbol'])) {
											if ($_SESSION['report_ledger_amount_symbol'] == '<=') {
												echo 'selected';
											}
										} ?>>
											<=< /option>
									</select>
									<input id="amount" name="amount" class="fieldtextmedium" maxlength="255"
										tabindex="<?php echo $tab++; ?>" type="text" value="<?php if (isset($_SESSION['report_ledger_amount'])) {
											   echo $_SESSION['report_ledger_amount'];
										   } ?>">
								</td>
							</tr>
							<tr>
								<th>Type</th>
								<td><select name="cus_type" <?php echo $tab++; ?>>
										<option></option>
										<?php
										$sql = 'select * from billit_cust_type';
										$row_type = execute_query($sql);
										while ($type_details = mysqli_fetch_array($row_type)) {
											echo '<option value="' . $type_details['sno'] . '"';
											if (isset($_SESSION['report_ledger_cus_type'])) {
												if ($_SESSION['report_ledger_cus_type'] == $type_details['sno']) {
													echo 'selected';
												}
											}
											echo '>' . $type_details['type'] . '</option>';
										}
										?>
									</select></td>
								<th>Category</th>
								<td><select name="category" tabindex="<?php echo $tab++; ?>">
										<option></option>
										<?php
										$sql = 'select * from billit_cust_category';
										$row_category = execute_query($sql);
										while ($category_details = mysqli_fetch_array($row_category)) {
											echo '<option value="' . $category_details['sno'] . '"';
											if (isset($_SESSION['report_ledger_category'])) {
												if ($_SESSION['report_ledger_category'] == $category_details['sno']) {
													echo ' selected="selected" ';
												}
											}
											echo '>' . $category_details['category'] . '</option>';
										}
										?>
									</select></td>
								<th>Division</th>
								<th><select name="division" tabindex="<?php echo $tab++; ?>">
										<option></option>
										<?php
										$sql = 'select * from uprnss_division';
										$row_heads = execute_query($sql);
										while ($heads_details = mysqli_fetch_array($row_heads)) {
											echo '<option value="' . $heads_details['s_no'] . '"';
											if (isset($_SESSION['report_ledger_division'])) {
												if ($_SESSION['report_ledger_division'] == $heads_details['s_no']) {
													echo ' selected="selected" ';
												}
											}
											echo '>' . $heads_details['division_name'] . '</option>';
										}
										?>
									</select></th>
								<th colspan="2" class="text-center">
									<button type="submit" name="submit_form" class="btn btn-success">Search with Filters</button>
								</th>
							</tr>
						</table>
						<table class=" table table-striped table-hover table-bordered">
							<thead>
								<tr>
									<th>
										<?php
										include('pagination/paginate.php'); //include of paginat page
										$total_results = mysqli_num_rows($result_data);
										$total_pages = ceil($total_results / $per_page);//total pages we going to have
										$tpages = $total_pages;
										if (isset($_GET['page'])) {
											$show_page = $_GET['page'];             //it will telles the current page
											if ($show_page > 0 && $show_page <= $total_pages) {
												$start = ($show_page - 1) * $per_page;
												$end = $start + $per_page;
											} else {
												// error - show first set of results
												$start = 0;
												$end = $per_page;
											}
										} else {
											// if page isn't set, show first set of results
											$_GET['page'] = 1;
											$show_page = 1;
											$start = 0;
											$end = $per_page;
										}
										// display pagination
										$page = intval($_GET['page']);

										if ($page <= 0)
											$page = 1;


										$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages;
										echo '<div class="pagination"><ul>';
										if ($total_pages > 1) {
											echo paginate($reload, $show_page, $total_pages);
										}
										echo "</ul></div>";
										?>
									</th>
								</tr>
							</thead>
						</table>
						<table class=" table table-striped table-hover table-bordered" id="ledgerReportTable">
							<thead>
								<tr>
									<th>S.No</th>
									<th>Name</th>
									<th>Parent</th>
									<th>Category</th>
									<th>Type</th>
									<th>Opening</th>
									<th>Transaction</th>
									<th>Closing</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								$page_receivable = 0;
								$page_payable = 0;
								$page_total = 0;
								$receivable = 0;
								$payable = 0;
								$total = 0;
								$from = $_SESSION['report_ledger_date_from'];
								$to = date("Y-m-d", strtotime($_SESSION['report_ledger_date_to']) + 86400);

								for ($pgid = $start; $pgid < $end; $pgid++) {
									if ($pgid == $total_results) {
										break;
									}
									mysqli_data_seek($result_data, $pgid);
									$row = mysqli_fetch_array($result_data);
									if ($row['type'] == 'stock') {
										$chk_amount = stock_as_ledger_balance($row['sno'], $from, $to, 'in');
									} else {
										$chk_amount = get_cust_balace($from, $to, $row['sno'], '', $_SESSION['report_ledger_division'] ?? '');
									}
									if (isset($_POST['amount'])) {
										if ($_POST['amount'] != '') {
											//echo $chk_amount.'>>>'.$_POST['amount'].'<br>';
											switch ($_POST['amount_symbol']) {
												case '=': {
													if ($chk_amount != $_POST['amount']) {
														goto c;
													}
													break;
												}
												case '>=': {
													if ($chk_amount < $_POST['amount']) {
														goto c;
													}
													break;
												}
												case '<=': {
													if ($chk_amount > $_POST['amount']) {
														goto c;
														break;
													}
												}
											}
										}
									}
									$i = $pgid + 1;
									if ($row['type'] == 'stock') {
										echo '<tr> 
				<th>' . $i . '</th>
				<td><a href="billit_customer_ledger.php?ledid=' . $row['sno'] . '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'] . '&t=s&i_o=i"  class="no-print">' . $row['cus_name'] . ' (Income)</a></td>';
										$amount = stock_as_ledger_balance($row['sno'], $from, $to, 'in');
										if ($amount < 0) {
											$page_payable += $amount;
										} else {
											$page_receivable += $amount;
										}
										$page_total += $amount;
										$amount = amount_format($amount);

										echo '
				<td>' . get_parent($row['parent']) . '</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="right">' . $amount . '</td>';
										echo '
				</tr>';
										$i++;

										echo '<tr> <th>' . $i . '</th>';
										echo '<td><a href="billit_customer_ledger.php?ledid=' . $row['sno'] . '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'] . '&t=s&i_o=o">' . $row['cus_name'] . ' (Expense)</a></td>';
										$amount = stock_as_ledger_balance($row['sno'], $from, $to, 'out');
										if ($amount < 0) {
											$page_payable += $amount;
										} else {
											$page_receivable += $amount;
										}
										$page_total += $amount;
										$amount = amount_format($amount);

										echo '
				<td>' . get_parent($row['parent']) . '</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="right">' . $amount . '</td>';
										echo '
				</tr>';
										$i++;
									} else {
										echo '<tr> <th scope="row">' . $i . '</th>';
										echo '<td><a href="billit_ledgers.php?id=' . $row['sno'] . '"><i class="fa fa-edit alert-danger"></i></a> &nbsp;<a href="billit_customer_ledger.php?ledid=' . $row['sno'] . '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'] . '" class="no-print">' . $row['cus_name'] . '<br/><small>' . $row['address'] . '</small></a><span class="print-only">' . $row['cus_name'] . '<br/><small>' . $row['address'] . '</small></span></td>';
										if ($row['type'] == 'billit_customer' && $row['auto_ledger'] == '') {
											$opening = get_cust_balace('1970-01-01', $from, $row['sno'], '', $_SESSION['report_ledger_division'] ?? '');
											$closing = get_cust_balace($from, $to, $row['sno'], '', $_SESSION['report_ledger_division'] ?? '');
											$trans = $closing - $opening;
										} elseif ($row['auto_ledger'] != '') {
											$opening = '0';
											$closing = '0';
											$trans = '0';
										} else {
											$opening = stock_as_ledger_balance($row['sno'], '1970-01-01', $from);
											$closing = stock_as_ledger_balance($row['sno'], $from, $to);
											$trans = $closing - $opening;
										}
										if ($closing < 0) {
											$page_payable += $closing;
										} else {
											$page_receivable += $closing;
										}
										$page_total += $closing;

										echo '
				<td  contentEditable="true" class="edit" id="row_' . $row['sno'] . '">' . get_parent($row['parent']) . '</td>
				<td>' . get_ledger_category($row['category']) . '</td>
				<td>' . get_ledger_type($row['cus_type']) . '</td>
				<td class="right ';
										if ($opening < 0) {
											echo ' red';
										} else {
											echo 'green';
										}
										echo '">' . amount_format($opening) . '</td>
				<td class="right ';
										if ($trans < 0) {
											echo ' red';
										} else {
											echo 'green';
										}

										echo '">' . amount_format($trans) . '</td>
				<td class="right ';
										if ($closing < 0) {
											echo ' red';
										} else {
											echo 'green';
										}

										echo '">' . amount_format($closing) . '</td>';
										echo '
				</tr>';
										$i++;
									}
									c:
								}
								echo '</tbody></table>
		<table class="table table-striped table-hover table-bordered"><tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th class="right">Page Total :</th>
		<th class="right">Receivable : ' . amount_format($page_receivable) . '</th>
		<th class="right">Payable : ' . amount_format($page_payable) . '</th>
		<th class="right">Total : </th>
		<th class="right">' . amount_format($page_total) . '</th>
		</tr>';
								?>
							</tbody>
						</table>
					</form>
				</div>
				<?php
				break;
	}
	case 3: {
		if (!isset($_GET['df'])) {
			$_GET['df'] = date("Y-m-01");
			$_GET['dt'] = date("Y-m-d");
		}
		?>

				<div class="row">
					<div class="col-md-12">
						<div class="topnav no-print">
							<a
								href="billit_customer_ledger.php?details=1&ledid=<?php echo $_GET['ledid'] . '&df=' . $_GET['df'] . '&dt=' . $_GET['dt']; ?>"><span
									class="glyphicon glyphicon-file"></span>Detailed Ledger</a>&nbsp;|&nbsp;
							<a
								href="billit_customer_ledger_stock.php?ledid=<?php echo $_GET['ledid'] . '&df=' . $_GET['df'] . '&dt=' . $_GET['dt']; ?>"><span
									class="glyphicon glyphicon-file"></span>Stock Ledger</a>
						</div>

						<?php
						if ($msg != '') {
							echo '<h5>' . alert($msg) . '</h5>';
						}
						?>
						<table class=" table table-striped table-hover table-bordered">
							<tr>
								<td colspan="6">
									<div style="float:right; font-size:18px; text-align: right;">
										Ledger From :<?php if (isset($_GET['df'])) {
											echo $_GET['df'];
										} ?> to
									<?php if (isset($_GET['dt'])) {
										echo $_GET['dt'];
									} ?><br />
									<?php echo get_parent($billit_customer['parent']); ?>
										<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post"
											enctype="multipart/form-data">
											<table class=" table table-striped table-hover table-bordered">
												<tr>
													<td>Ledgers :</td>
													<td><select name="filter_ledger" id="filter_ledger">
															<option value=""></option>
															<?php
															$sql = 'select * from billit_customer';
															$result_billit_customer = execute_query($sql);
															while ($row_billit_customer = mysqli_fetch_array($result_billit_customer)) {
																echo '<option value="' . $row_billit_customer['sno'] . '" ';
																if (isset($_POST['filter_ledger'])) {
																	if ($_POST['filter_ledger'] == $row_billit_customer['sno']) {
																		echo ' selected="selected"';
																	}
																}
																echo '>' . $row_billit_customer['cus_name'] . '</option>';
															}

															?>
														</select></td>
													<td><input type="checkbox" name="filter_debit" id="filter_debit" <?php if (isset($_POST['filter_debit'])) {
														echo ' checked="checked"';
													} elseif (!isset($_POST['filter_search'])) {
														echo ' checked="checked"';
													} ?>> Payments</td>
													<td><input type="checkbox" name="filter_credit" id="filter_credit" <?php if (isset($_POST['filter_credit'])) {
														echo ' checked="checked"';
													} elseif (!isset($_POST['filter_search'])) {
														echo ' checked="checked"';
													} ?>> Receipts</td>
													<td><input type="submit" name="filter_search" id="filter_search" value="Search">
													</td>
												</tr>
											</table>

										</form>
									</div>
									<div id="party" style="font-size:18px;">
										<h3><?php echo $billit_customer['cus_name']; ?></h3>
										<?php
										if ($billit_customer['address'] != '') {
											echo '<br>' . $billit_customer['address'];
										}
										if ($billit_customer['mobile'] != '') {
											echo '<br>Mobile : ' . $billit_customer['mobile'] . '<br>';
										}
										?>
									<?php echo 'CURRENT BALANCE: ' . amount_format(get_cust_balace($_GET['df'], $_GET['dt'], $_GET['ledid'], '', $_SESSION['report_ledger_division'] ?? '')); ?><br />
									</div>
								</td>
							</tr>
							<tr>
								<th>S.No.</th>
								<th>Date</th>
								<th>Description</th>
								<th>Debit</th>
								<th>Credit</th>
								<th>Balance</th>
							</tr>
							<?php
							if (!isset($_GET['t'])) {
								if (isset($_GET['df']) && isset($_GET['dt'])) {
									//$sql .= ' and timestamp>='.strtotime($_GET['df']).' and timestamp<='.strtotime($_GET['dt']);
									$sql_auto = '';
									$filter_ledger = '';
									$filter_journal = '';
									$filter_contra = '';
									$filter_ledger_credit = '';
									$filter_journal_credit = '';
									$filter_contra_credit = '';
									$filter_ledger_debit = '';
									$filter_journal_debit = '';
									$filter_contra_debit = '';
									if (isset($_POST['filter_ledger'])) {
										if ($_POST['filter_ledger'] != '') {
											$filter_ledger = ' and account="' . $_POST['filter_ledger'] . '"';
											$filter_journal = ' and (`by`="' . $_POST['filter_ledger'] . '" or `to`="' . $_POST['filter_ledger'] . '")';
											$filter_contra = ' and (`by`="' . $_POST['filter_ledger'] . '" or `to`="' . $_POST['filter_ledger'] . '")';
										}
										if (isset($_POST['filter_credit']) && !isset($_POST['filter_debit'])) {
											$filter_ledger_credit = ' and upper(type) in ("RECEIPT", "RECIEPT", "PURCHASE", "CREDIT_NOTE")';
											$filter_journal_credit = ' and `to`="' . $billit_customer['sno'] . '"';
											$filter_contra_credit = ' and `to`="' . $billit_customer['sno'] . '"';
										}
										if (isset($_POST['filter_debit']) && !isset($_POST['filter_credit'])) {
											$filter_ledger_debit = ' and upper(type) in ("PAYMENT", "SALE", "DEBIT_NOTE")';
											$filter_journal_debit = ' and `by`="' . $billit_customer['sno'] . '"';
											$filter_contra_debit = ' and `by`="' . $billit_customer['sno'] . '"';
										}
									}

									if ($billit_customer['auto_ledger'] != '') {
										switch ($billit_customer['auto_ledger']) {
											case 'CGST_IN': {
												$sql_auto = '(select stock_sale.supplier_id as cust_by, null as cust_to, "sale" as type, stock_sale.invoice_no as number, vat_value as amount, part_dateofpurchase as timestamp, "" as remarks, stock_sale.invoice_no as ct_sno, "" as account, "" as mop, "" as chq_no, "" as status from stock_sale left join invoice_sale on invoice_sale.sno = stock_sale.invoice_no where invoice_sale.invoice_type="TAX" and vat="' . $billit_customer['auto_ledger_value'] . '" and part_dateofpurchase>="' . $_GET['df'] . '" and part_dateofpurchase<="' . $_GET['dt'] . '") union all ';
												//echo $sql_auto;
												break;
											}
											case 'CGST_OUT': {
												$sql_auto = '(select stock_purchase.supplier_id as cust_by, null as cust_to, "purchase" as type, stock_purchase.invoice_no as number, vat_value as amount, part_dateofpurchase as timestamp, "" as remarks, stock_purchase.invoice_no as ct_sno, "" as account, "" as mop, "" as chq_no, "" as status from stock_purchase left join invoice_purchase on invoice_purchase.sno = stock_purchase.invoice_no where invoice_type="TAX" and vat="' . $billit_customer['auto_ledger_value'] . '" and part_dateofpurchase>="' . $_GET['df'] . '" and part_dateofpurchase<="' . $_GET['dt'] . '") union all ';
												//echo $sql;
												break;
											}
											case 'CGST': {
												$sql_auto = '(select stock_sale.supplier_id as cust_by, null as cust_to, "sale" as type, stock_sale.invoice_no as number, vat_value as amount, part_dateofpurchase as timestamp, "" as remarks, stock_sale.invoice_no as ct_sno, "" as account, "" as mop, "" as chq_no, "" as status from stock_sale left join invoice_sale on invoice_sale.sno = stock_sale.invoice_no where invoice_sale.invoice_type="TAX" and vat="' . $billit_customer['auto_ledger_value'] . '" and part_dateofpurchase>="' . $_GET['df'] . '" and part_dateofpurchase<="' . $_GET['dt'] . '") union all  (select stock_purchase.supplier_id as cust_by, null as cust_to, "purchase" as type, stock_purchase.invoice_no as number, vat_value as amount, part_dateofpurchase as timestamp, "" as remarks, stock_purchase.invoice_no as ct_sno, "" as account, "" as mop, "" as chq_no, "" as status from stock_purchase left join invoice_purchase on invoice_purchase.sno = stock_purchase.invoice_no where invoice_type="TAX" and vat="' . $billit_customer['auto_ledger_value'] . '" and part_dateofpurchase>="' . $_GET['df'] . '" and part_dateofpurchase<="' . $_GET['dt'] . '") union all ';
												break;
											}
										}
									}


									$sql = "$sql_auto (SELECT cust_id as cust_by, null as cust_to, type, number, amount, billit_customer_transactions.timestamp, remarks, billit_customer_transactions.sno as ct_sno, account, mop, chq_no, status FROM `billit_customer_transactions` where cust_id=" . $billit_customer['sno'] . " and timestamp>='" . $_GET['df'] . "' and timestamp<='" . $_GET['dt'] . "' $filter_ledger $filter_ledger_credit $filter_ledger_debit) 
									
									union all (SELECT `by` as cust_by, `to` as cust_to, 'journal' as type, sno as number, amount, timestamp, remarks,sno as jr_sno, '' as account, '' as mop, '' as chq_no, status FROM `billit_stock_journal` where (`to`=" . $billit_customer['sno'] . " or `by`=" . $billit_customer['sno'] . ") and timestamp>='" . $_GET['df'] . "' and timestamp<='" . $_GET['dt'] . "' $filter_journal $filter_journal_debit $filter_journal_credit) 
									
									union all (SELECT `by` as cust_by, `to` as cust_to, 'journal' as type, sno as number, amount, timestamp, remarks,sno as jr_sno, '' as account, '' as mop, '' as chq_no, status FROM `billit_stock_erp_receipt` where (`to`=" . $billit_customer['sno'] . " or `by`=" . $billit_customer['sno'] . ") and timestamp>='" . $_GET['df'] . "' and timestamp<='" . $_GET['dt'] . "' $filter_journal $filter_journal_debit $filter_journal_credit)
									
									union all (SELECT `by` as cust_by, `to` as cust_to, 'journal' as type, sno as number, amount, timestamp, remarks,sno as jr_sno, '' as account, '' as mop, '' as chq_no, status FROM `billit_stock_erp_payment` where (`to`=" . $billit_customer['sno'] . " or `by`=" . $billit_customer['sno'] . ") and timestamp>='" . $_GET['df'] . "' and timestamp<='" . $_GET['dt'] . "' $filter_journal $filter_journal_debit $filter_journal_credit)  order by timestamp";

									
									
									// echo $sql;
									$billit_customer['opening_balance'] = get_cust_balace("1970-01-01", date("Y-m-d", strtotime($_GET['df']) - 86400), $billit_customer['sno'], '', $_SESSION['report_ledger_division'] ?? '');
								}
							} elseif (isset($_GET['t'])) {
								if ($_GET['i_o'] == 'i') {
									$sql = "(select part_id as cust_by, '' as cust_to, 'sale' as type, invoice_sale.sno as number, amount, part_dateofpurchase as timestamp, '' as remarks, stock_sale.s_no as ct_sno, '' as account, '' as mop, '' as chq_no from  stock_sale left join invoice_sale on invoice_sale.sno = stock_sale.invoice_no where part_id=" . $_GET['ledid'] . " and part_dateofpurchase>='" . $_GET['df'] . "' and part_dateofpurchase<='" . $_GET['dt'] . "')";
								} elseif ($_GET['i_o'] == 'o') {
									$sql = "(select part_id as cust_by, '' as cust_to, 'purchase' as type, invoice_sale.sno as number, amount, part_dateofpurchase as timestamp, '' as remarks, stock_purchase.s_no as ct_sno, '' as account, '' as mop, '' as chq_no from  stock_purchase left join invoice_sale on invoice_sale.sno = stock_purchase.invoice_no where part_id=" . $_GET['ledid'] . " and part_dateofpurchase>='" . $_GET['df'] . "' and part_dateofpurchase<='" . $_GET['dt'] . "')";
								}
								/*$sql = "(select part_id as cust_by, '' as cust_to, sale as type, invoice_sale.sno as number, amount, part_dateofdispatch, '' as remarks, stock_sale.sno as ct_sno, '' as account, '' as mop, '' as chq_no from   )

								(SELECT cust_id as cust_by, null as cust_to, type, number, amount, billit_customer_transactions.timestamp, remarks, billit_customer_transactions.sno as ct_sno, account, mop, chq_no FROM `billit_customer_transactions` where cust_id=".$billit_customer['sno']." and timestamp>='".$_GET['df']."' and timestamp<='".$_GET['dt']."') union all (SELECT `by` as cust_by, `to` as cust_to, 'journal' as type, sno as number, amount, timestamp, remarks,sno as jr_sno, '' as account, '' as mop, '' as chq_no FROM `billit_stock_journal` where (`to`=".$billit_customer['sno']." or `by`=".$billit_customer['sno'].") and timestamp>='".$_GET['df']."' and timestamp<='".$_GET['dt']."') union all (SELECT `by` as cust_by, `to` as cust_to, 'contra' as type, sno as number, amount, timestamp, remarks,sno as jr_sno, '' as account, '' as mop, '' as chq_no FROM `contra_entry` where (`to`=".$billit_customer['sno']." or `by`=".$billit_customer['sno'].") and timestamp>='".$_GET['df']."' and timestamp<='".$_GET['dt']."') order by timestamp";*/
							}
							//echo $sql;
							$result = execute_query($sql);
							$balance = 0;
							$i = 2;
							$amount = 0;
							$amount_due = 0;
							if ($billit_customer['opening_balance'] < 0) {
								echo '<tr><td>1</td><td>-</td><td>OPENING BALANCE</td><td>&nbsp;</td><td class="right">' . amount_format($billit_customer['opening_balance']) . '</td><td class="right">' . amount_format($billit_customer['opening_balance']) . '</tr>';
							} else {
								echo '<tr><td>1</td><td>-</td><td>OPENING BALANCE</td><td class="right">' . amount_format($billit_customer['opening_balance']) . '</td><td>&nbsp;</td><td class="right">' . amount_format($billit_customer['opening_balance']) . '</tr>';
							}
							$balance = $billit_customer['opening_balance'];
							$tot_debit = 0;
							$tot_credit = 0;
							$tot_sale_inv = 0;
							$tot_sale_amt = 0;
							$tot_purchase_inv = 0;
							$tot_purchase_amt = 0;
							$tot_receipt = 0;
							$tot_receipt_amt = 0;
							$tot_payment = 0;
							$tot_payment_amt = 0;
							while ($trans = mysqli_fetch_array($result)) {
								$trans['amount'] = (float) $trans['amount'];
								if ($trans['type'] != 'sale') {
									//goto b;
								}
								$details = '';
								if (strtolower($trans['type']) == 'purchase') {
									$sql_invoice = 'select invoice_no from invoice_purchase where sno=' . $trans['number'];
									$invoice_no = mysqli_fetch_array(execute_query($sql_invoice));
									$tot_purchase_inv++;
									$tot_purchase_amt += $trans['amount'];
								} elseif (strtolower($trans['type']) == 'sale') {
									$sql_invoice = 'select invoice_no, department, invoice_type from invoice_sale where sno=' . $trans['number'];
									$sale_inv = mysqli_fetch_array(execute_query($sql_invoice));
									$tot_sale_inv++;
									$tot_sale_amt += $trans['amount'];
								} elseif (strtolower($trans['type']) == 'sale_revert') {
									$sql_invoice = 'select invoice_no, department from invoice_sale_revert where sno=' . $trans['number'];
									$sale_revert = mysqli_fetch_array(execute_query($sql_invoice));
								} elseif (strtolower($trans['type']) == 'purchase_revert') {
									$sql_invoice = 'select invoice_no, department from invoice_purchase_revert where sno=' . $trans['number'];
									$purchase_revert = mysqli_fetch_array(execute_query($sql_invoice));
								} elseif (strtolower($trans['type']) == 'sale_ply') {
									$sql_invoice = 'select invoice_no, department from invoice_sale_ply where sno=' . $trans['number'];
									$sale_ply = mysqli_fetch_array(execute_query($sql_invoice));
								} elseif (strtolower($trans['type']) == 'purchase_ply') {
									$sql_invoice = 'select invoice_no, department from invoice_purchase_ply where sno=' . $trans['number'];
									$purchase_ply = mysqli_fetch_array(execute_query($sql_invoice));
								}
								echo '<tr>
			<td>' . $i . '</td>
			<td>' . date("d-m-Y", strtotime($trans['timestamp'])) . '</td>
			<td>';
								if (strtolower($trans['type']) == 'sale') {
									$balance += $trans['amount'];
									$tot_debit += $trans['amount'];
									if (isset($_GET['details'])) {
										$sql1 = 'select * from stock_sale join billit_stock_available on billit_stock_available.sno = stock_sale.part_id where invoice_no="' . $trans['number'] . '"';
										//echo $sql1;
										$res = execute_query($sql1);
										$details = '<table width="100%" id="details">';
										while ($row_details = mysqli_fetch_array($res)) {
											$barcode = '';
											$sql = 'select * from barcode_new where type="sale" and number="' . $trans['number'] . '" and part_desc="part_desc' . $i . '"';
											$res_barcode = execute_query($sql);
											if (mysqli_num_rows($res_barcode) != 0) {
												while ($row_barcode = mysqli_fetch_array($res_barcode)) {
													$barcode .= '<br /><small>(' . $row_barcode['barcode'] . ')</small>';
												}
											}
											$details .= '<tr>
						<td>' . $row_details['description'] . $barcode . '</td>
						<td>' . $row_details['qty'] . ' ' . get_unit($row_details['unit']) . '</td>
						<td class="right">' . amount_format($row_details['effective_price']) . '</td>
						<td class="right">' . amount_format($row_details['amount']) . '</td>
						</tr>';
										}
										$details .= '</table>';
									}
									echo '<a href="scripts/printing_sale.php?id=' . $trans['number'] . '" target="_blank">Sale Invoice No.: ' . $sale_inv['invoice_no'];
									if ($sale_inv['invoice_type'] != 'TAX') {
										echo ' <span style="color:#009900;">(OTHER)</span> ';
									}
									if ($sale_inv['department'] != '') {
										echo ' (' . $sale_inv['department'] . ') ';
									}
									echo '</a>
				<a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}
									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a>' . $details . '</td>
				<td class="right green">' . amount_format($trans['amount']) . '</td>
				<td>&nbsp;</td>';
								} elseif (strtolower($trans['type']) == 'sale_revert') {
									$balance -= $trans['amount'];
									$tot_credit += $trans['amount'];
									echo '<a href="scripts/printing_sale_revert.php?id=' . $trans['number'] . '" target="_blank">Sale Revert No.: ' . $sale_revert['invoice_no'];
									if ($sale_revert['department'] != '') {
										echo ' (' . $sale_revert['department'] . ') ';
									}
									echo '</a><a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}
									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></td>
				
				<td>&nbsp;</td><td class="right red">' . amount_format($trans['amount']) . '</td>';
								} elseif (strtolower($trans['type']) == 'purchase') {
									$balance -= $trans['amount'];
									$tot_credit += $trans['amount'];
									if (isset($_GET['details'])) {
										$sql1 = 'select * from stock_purchase join billit_stock_available on billit_stock_available.sno = stock_purchase.part_id where invoice_no="' . $trans['number'] . '"';
										$res = execute_query($sql1);
										$details = '<table width="100%" id="details">';
										$i = 1;
										while ($row_details = mysqli_fetch_array($res)) {
											$barcode = '';
											$sql = 'select * from barcode_new where type="purchase" and number="' . $trans['number'] . '" and part_desc="part_desc' . $i . '"';
											//echo $sql;
											$res_barcode = execute_query($sql);
											if (mysqli_num_rows($res_barcode) != 0) {
												while ($row_barcode = mysqli_fetch_array($res_barcode)) {
													$barcode .= '<br /><small>(' . $row_barcode['barcode'] . ')</small>';
												}
											}


											$details .= '<tr>
						<td>' . $row_details['description'] . $barcode . '</td>
						<td>' . $row_details['qty'] . ' ' . get_unit($row_details['unit']) . '</td>
						<td class="right">' . amount_format($row_details['effective_price']) . '</td>
						<td class="right">' . amount_format($row_details['amount']) . '</td>
						</tr>';
											$i++;
										}
										$details .= '</table>';
									}

									echo '<a href="scripts/printing_purchase.php?id=' . $trans['number'] . '" target="_blank">Purchase Invoice No.: ' . $invoice_no['invoice_no'] . ' </a><a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a>' . $details . '</td>
				<td>&nbsp;</td>
				<td class="right red">' . amount_format($trans['amount']) . '</td>';
								} elseif (strtolower($trans['type']) == 'purchase_revert') {
									$balance += $trans['amount'];
									$tot_debit += $trans['amount'];
									echo '<a href="scripts/printing_purchase_revert.php?id=' . $trans['number'] . '" target="_blank">Purchase Revert: ' . $purchase_revert['invoice_no'] . ' </a><a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></td>
				<td class="right green">' . amount_format($trans['amount']) . '</td><td>&nbsp;</td>
				';
								} elseif (strtolower($trans['type']) == 'sale_ply') {
									$balance += $trans['amount'];
									$tot_debit += $trans['amount'];
									echo '<a href="scripts/printing_sale_ply.php?id=' . $trans['number'] . '" target="_blank">Sale : ' . $sale_ply['invoice_no'] . ' </a><a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></td>
				<td class="right green">' . amount_format($trans['amount']) . '</td><td>&nbsp;</td>
				';
								} elseif (strtolower($trans['type']) == 'purchase_ply') {
									$balance += $trans['amount'];
									$tot_debit += $trans['amount'];
									echo '<a href="scripts/printing_purchase_ply.php?id=' . $trans['number'] . '" target="_blank">Purchase : ' . $purchase_ply['invoice_no'] . ' </a><a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></td>
				<td class="right green">' . amount_format($trans['amount']) . '</td><td>&nbsp;</td>
				';
								} elseif (strtolower($trans['type']) == 'reciept' or strtolower($trans['type']) == 'receipt') {
									$tot_receipt++;
									$tot_receipt_amt += $trans['amount'];
									$balance -= $trans['amount'];
									$tot_credit += $trans['amount'];
									echo '<a href="receipts.php?id=' . $trans['ct_sno'] . '" target="_blank">Receipt No.: ' . $trans['number'] . '. VIA : ' . get_ledger($trans['account']) . '</a>&nbsp;<a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a>
				<div id="' . $i . '">
					' . $trans['remarks'] . '
					</div></td>
				<td>&nbsp;</td>
				<td class="right red">' . amount_format($trans['amount']) . '</td>';
								} elseif (strtolower($trans['type']) == 'credit_note') {
									$balance -= $trans['amount'];
									$tot_credit += $trans['amount'];
									echo 'Credit Note No.: ' . $trans['number'] . '<br> ' . $trans['remarks'] . '<a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></td>
				<td>&nbsp;</td>
				<td class="right red">' . amount_format($trans['amount']) . '</td>';
								} elseif (strtolower($trans['type']) == 'debit_note') {
									$balance += $trans['amount'];
									$tot_debit += $trans['amount'];
									echo 'Debit Note No.: ' . $trans['number'] . ' ' . $trans['remarks'] . '<a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}
									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></td>
				<td class="right green">' . amount_format($trans['amount']) . '</td>
				<td>&nbsp;</td>';
								} elseif (strtolower($trans['type']) == 'payment') {
									$tot_payment++;
									$tot_payment_amt += $trans['amount'];
									$balance += $trans['amount'];
									$tot_debit += $trans['amount'];
									echo '<a href="payments.php?id=' . $trans['ct_sno'] . '" target="_blank">Paid from ' . get_ledger($trans['account']) . ' (' . $trans['mop'] . ' # ' . $trans['chq_no'] . ') Payment Voucher No.: ' . $trans['number'] . ' </a>&nbsp;<a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del=' . $trans['ct_sno'];
									if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
										echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
									}

									echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a>
				<div id="' . $i . '">
					' . $trans['remarks'] . '
					</div></td>
				<td class="right green">' . amount_format($trans['amount']) . '</td>
				<td>&nbsp;</td>';
								} elseif (strtolower($trans['type']) == 'journal') {
									if ($trans['status'] != 1) {
										$stat = ' <span style="color:#009900;">(Draft)</span> ';
									} else {
										$stat = '';
									}
									if ($trans['cust_by'] == $billit_customer['sno']) {
										$balance -= $trans['amount'];
										$tot_debit -= $trans['amount'];
										echo '<a href="journal.php?id=' . $trans['ct_sno'] . '" target="_blank">Journal : To ';
										echo get_ledger($trans['cust_to']) . '</a>&nbsp;' . $stat . '<small><a href="billit_customer_ledger.php?ledid=' . $_GET['ledid'] . '&del_j=' . $trans['ct_sno'];
										if ($_SESSION['report_ledger_date_from'] != '' && $_SESSION['report_ledger_date_to'] != '') {
											echo '&df=' . $_SESSION['report_ledger_date_from'] . '&dt=' . $_SESSION['report_ledger_date_to'];
										}

										echo '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></small>
											<div id="' . $i . '">
											' . $trans['remarks'] . '
											</div></td>
											<td class="right green">' . amount_format($trans['amount']) . '</td>
											<td>&nbsp;</td>';
									} elseif ($trans['cust_to'] == $billit_customer['sno']) {
										$balance += $trans['amount'];
										$tot_credit += $trans['amount'];
										echo '<a href="journal.php?id=' . $trans['number'] . '">Journal : By ';
										echo get_ledger($trans['cust_by']) . '</a>&nbsp;' . $stat . '<small><a href="journal.php?del=' . $trans['number'] . '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></small>
											<div id="' . $i . '">
											' . $trans['remarks'] . '
											</div></td>
											<td>&nbsp;</td>
											<td class="right red">' . amount_format($trans['amount']) . '</td>';
									}
								} elseif (strtolower($trans['type']) == 'contra') {
									if ($trans['cust_by'] == $billit_customer['sno']) {
										$balance -= $trans['amount'];
										$tot_credit += $trans['amount'];
										echo '<a href="contra_edit.php?id=' . $trans['number'] . '">Contra : To ';
										if (!is_numeric($trans['cust_to'])) {
											echo $trans['cust_to'] . '</a>&nbsp;<small><a href="contra_edit.php?del=' . $trans['number'] . '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></small>
						<div id="' . $i . '">
						' . $trans['remarks'] . '
						</div></td>
						<td class="right red">' . amount_format($trans['amount']) . '</td>
						<td>&nbsp;</td>';
										} else {
											echo get_ledger($trans['cust_to']) . '</a>&nbsp;<small><a href="contra_edit.php?del=' . $trans['number'] . '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></small>
						<div id="' . $i . '">
						' . $trans['remarks'] . '
						</div></td>
						<td class="right red">' . amount_format($trans['amount']) . '</td>
						<td>&nbsp;</td>';
										}
									}
									if ($trans['cust_to'] == $billit_customer['sno']) {
										$balance += $trans['amount'];
										$tot_debit += $trans['amount'];
										echo '<a href="contra_edit.php?id=' . $trans['number'] . '">Contra : By ';
										if (!is_numeric($trans['cust_by'])) {
											echo $trans['cust_by'] . '</a>
						&nbsp;<small><a href="contra_edit.php?del=' . $trans['number'] . '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></small>
						<div id="' . $i . '">
						' . $trans['remarks'] . '
						</div></td>
						<td>&nbsp;</td>
						<td class="right green">' . amount_format($trans['amount']) . '</td>';
										} else {
											echo get_ledger($trans['cust_by']) . '</a>&nbsp;<small><a href="contra_edit.php?del=' . $trans['number'] . '" onclick="return confirm(\'Are you sure?\');" style="color:#F00;">(Delete)</a></small>
						<div id="' . $i . '">
						' . $trans['remarks'] . '
						</div></td>
						<td>&nbsp;</td>
						<td class="right green">' . amount_format($trans['amount']) . '</td>';
										}
									}
								}
								echo '			
			<td class="right ';
								if ($balance < 0) {
									echo ' red';
								} else {
									echo ' green';
								}
								echo '">' . amount_format($balance) . '</td>
			</tr>';
								$i++;
								b:
							}
							echo '<tr><td colspan="6">&nbsp;</td></tr>
								<tr><th colspan="3" style="text-align:right;">TOTAL :</th><th class="right">' . amount_format($tot_debit) . '</th><th class="right">' . amount_format($tot_credit) . '</th><th  class="right">' . amount_format($balance) . '</th></tr>
								
								<tr><th colspan="6">Total Sale Invoices : ' . $tot_sale_inv . ' (' . amount_format($tot_sale_amt) . ') | Total Purchase Invoices : ' . $tot_purchase_inv . ' (' . amount_format($tot_purchase_amt) . ') | Total Receipts : ' . $tot_receipt . ' (' . amount_format($tot_receipt_amt) . ') | Total Payment : ' . $tot_payment . ' (' . amount_format($tot_payment_amt) . ')</th></tr>';
							$a = 30 - $i;
							?>
						</table>
					</div>
					<?php
					break;
	}
}
?>
		<script>
			$(document).ready(function () {
				$('#ledgerReportTable').DataTable({
					paging: false,
					fixedHeader: true,
					colReorder: true
				}
				);




				// Add Class
				$('.edit').click(function () {
					$(this).addClass('editMode');
					$(this).html('');
				});

				// Save data
				$(".edit").focusout(function () {
					$(this).removeClass("editMode");
					var id = this.id;
					var split_id = id.split("_");
					var field_name = split_id[0];
					var edit_id = split_id[1];
					var value = $(this).text();

					$.ajax({
						url: 'scripts/billit_ajax.php?term=edl&id=edl',
						type: 'get',
						data: { field: field_name, value: value, sno: edit_id },
						success: function (response) {
							console.log('Save successfully >> ' + response);
							$('#' + id).html(response);
						}
					});

				});

			});

		</script>
		<?php
		page_footer_start();
		page_footer_end();
		?>