<?php
include("scripts/settings.php");
include("scripts/alerts.php"); 
include("scripts/billit_settings.php");

$msg = '';
$msg1 = '';
$tab = 1;

page_header_start();
page_header_end();
page_sidebar();

if (isset($_POST['submit'])) {
    $_POST['fund_transfer_by'] = $_POST['fund_transfer_by'] ?? '';
    $_POST['fund_transfer_to'] = $_POST['fund_transfer_to'] ?? '';
    $_POST['order_date'] = $_POST['order_date'] ?? '';
    $_POST['other_add_ded'] = $_POST['other_add_ded'] ?? '';
    $_POST['sentagepercentage'] = $_POST['sentagepercentage'] ?? '';
    $_POST['sentage'] = $_POST['sentage'] ?? '';
    $_POST['remarks'] = $_POST['remarks'] ?? '';
    $_POST['challan_no'] = $_POST['challan_no'] ?? '';
    $_POST['wing'] = $_POST['wing'] ?? '';
    $_POST['branch'] = $_POST['branch'] ?? '';

    //print_r($_POST);
    //die();
    if ($_POST['fund_transfer_by'] == 1) {
        $_POST['by_id'] = 1;
    } elseif ($_POST['fund_transfer_by'] == 2) {
        $_POST['by_id'] = $_POST['unit_id'];
    } elseif ($_POST['fund_transfer_by'] == 3) {
        $_POST['by_id'] = '';
    }


    if ($_POST['fund_transfer_to'] == 1) {
        $_POST['to_id'] = 1;
    } elseif ($_POST['fund_transfer_to'] == 2) {
        $_POST['to_id'] = $_POST['unit_id'];
    } elseif ($_POST['fund_transfer_to'] == 3) {
        $_POST['to_id'] = "";
    }

    $_POST['by_type'] = "";
    $_POST['to_type'] = "";
    if ($_POST['fund_transfer_by'] == 1) {
        $_POST['by_type'] = "HO";
    } elseif ($_POST['fund_transfer_by'] == 2) {
        $_POST['by_type'] = "Unit";
    } elseif ($_POST['fund_transfer_by'] == 3) {
        $_POST['by_type'] = "Vendor";
    }

    if ($_POST['fund_transfer_to'] == 1) {
        $_POST['to_type'] = "HO";
    } elseif ($_POST['fund_transfer_to'] == 2) {
        $_POST['to_type'] = "Unit";
    } elseif ($_POST['fund_transfer_to'] == 3) {
        $_POST['to_type'] = "Vendor";
    }

    $_POST['sub_department_id'] = isset($_POST['sub_department_id']) ? $_POST['sub_department_id'] : '';

    if ($_POST['edit_sno'] != '') {
        $sql = 'UPDATE invoice_account_fund_transafer SET
			unit_id="' . $_POST['unit_id'] . '",
			fund_transfer_to="' . $_POST['fund_transfer_to'] . '",
			order_no="' . $_POST['order_no'] . '",
			voucher_no="' . $_POST['voucher_no'] . '",
			order_date="' . $_POST['order_date'] . '",
			transafer_date="' . $_POST['transafer_date'] . '",
			gst_per="' . $_POST['gst_per'] . '",
			gstdeduction="' . $_POST['gstdeduction'] . '",
			cgst_amount="' . $_POST['cgst_amount'] . '",
			sgst_amount="' . $_POST['sgst_amount'] . '",
			royalty="' . $_POST['royalty'] . '",
			it_per="' . $_POST['it_per'] . '",
			incometax="' . $_POST['incometax'] . '",
			security_per="' . $_POST['security_per'] . '",
			security="' . $_POST['security'] . '",
			gsttdspercentage="' . $_POST['gsttdspercentage'] . '",
			gsttds="' . $_POST['gsttds'] . '",
			
			other_title="' . $_POST['other_title'] . '",
			other_per="' . $_POST['other_per'] . '",
			other_add_ded="' . $_POST['other_add_ded'] . '",
			other_amount="' . $_POST['other_amount'] . '",
			
			praposemoney="' . $_POST['praposemoney'] . '",
			sentagepercentage="' . $_POST['sentagepercentage'] . '",
			sentage="' . $_POST['sentage'] . '",
			leborses_per="' . $_POST['leborses_per'] . '",
			leborses="' . $_POST['leborses'] . '",
			
			total_expen="' . $_POST['total_expen'] . '",
			remark="' . $_POST['remark'] . '",
			from_account_no="' . $_POST['from_account_no'] . '",
			to_bank_name="' . $_POST['to_bank_name'] . '",
			to_account_no="' . $_POST['vendor_ledger_sno'] . '",
			vendor_id="' . $_POST['vendor_id'] . '",
			status="1",
			request_status="7",
			
			created_by="' . $_SESSION['username'] . '",
			creation_time="' . date("Y-m-d H:i:s") . '",
			ded_gst_per="' . $_POST['ded_gst_per'] . '",
			ded_gstdeduction="' . $_POST['ded_gstdeduction'] . '",
			ded_cgst_amount="' . $_POST['ded_cgst_amount'] . '",
			ded_sgst_amount="' . $_POST['ded_sgst_amount'] . '"
			WHERE sno="' . $_POST['edit_sno'] . '"';

        execute_query($sql);
        if (mysqli_error($db)) {
            $msg .= '<p class="alert alert-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
        }
        if ($msg == '') {
            $inv_id = $_POST['edit_sno'];

            execute_query('DELETE FROM billit_stock_erp_payment WHERE journal_id IN (SELECT sno FROM billit_invoice_erp_payment WHERE table_name="invoice_account_fund_transafer" AND table_id="' . $inv_id . '")');
            execute_query('DELETE FROM billit_invoice_erp_payment WHERE table_name="invoice_account_fund_transafer" AND table_id="' . $inv_id . '"');
            $sql = 'insert into billit_invoice_erp_payment (timestamp, first_by, first_to, tot_debit, tot_credit, row_count, voucher_no, unit_id, created_by, creation_time, table_name, table_id) values ("' . $_POST['transafer_date'] . '", "' . $_POST['to_bank_name'] . '", "' . $_POST['from_account_no'] . '", "' . $_POST['transafer_amount'] . '", "' . $_POST['transafer_amount'] . '", "", "' . $_POST['voucher_no'] . '", "' . $_POST['unit_id'] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '","invoice_account_fund_transafer", "' . $inv_id . '" )';
            execute_query($sql);
            if (mysqli_error($db)) {
                $msg .= '<p class="alert alert-danger">Error # 1.02 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }
            $id_stock = mysqli_insert_id($db);
            $unit_id_for_settings = $_POST['unit_id'] ?: 53;
            $gstw = get_bill_setting_w2($db, "BILL_GST", $unit_id_for_settings);
            $cgst = get_bill_setting_w2($db, "BILL_CGST", $unit_id_for_settings);
            $sgst = get_bill_setting_w2($db, "BILL_SGST", $unit_id_for_settings);
            $ded_gstw = get_bill_setting_w2($db, "BILL_DED_GST", $unit_id_for_settings);
            $ded_cgst = get_bill_setting_w2($db, "BILL_DED_CGST", $unit_id_for_settings);
            $ded_sgst = get_bill_setting_w2($db, "BILL_DED_SGST", $unit_id_for_settings);
            $advcen = get_bill_setting_w2($db, "BILL_SECURITY", $unit_id_for_settings);
            $gsttds = get_bill_setting_w2($db, "BILL_GST_TDS", $unit_id_for_settings);
            $cgsttds = get_bill_setting_w2($db, "BILL_CGST_TDS", $unit_id_for_settings);
            $sgsttds = get_bill_setting_w2($db, "BILL_SGST_TDS", $unit_id_for_settings);
            $labourcessw = get_bill_setting_w2($db, "BILL_LABOUR_CESS", $unit_id_for_settings);
            $ittds = get_bill_setting_w2($db, "BILL_IT", $unit_id_for_settings);
            $other = get_bill_setting_w2($db, "BILL_OTHER_DED", $unit_id_for_settings);
            $royalty = get_bill_setting_w2($db, "BILL_ROYALTY", $unit_id_for_settings);
// project ledger
            $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "' . $_POST['project_ledger_id'] . '", "", "' . $_POST['transafer_amount'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
            execute_query($sql);
            if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.06 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';


            if (isset($_POST['cgst_amount']) && $_POST['cgst_amount'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "' . (!empty($cgst['rate']) ? $cgst['rate'] : ($cgst['rate'] ?? '')) . '", "", "' . $_POST['cgst_amount'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.07 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

            if (isset($_POST['sgst_amount']) && $_POST['sgst_amount'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "' . (!empty($sgst['rate']) ? $sgst['rate'] : ($ded_gstw['rate'] ?? '')) . '", "", "' . $_POST['sgst_amount'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.07 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

/////////////////by end //////////////////////


//////////////////// TO start ///////////////////////

///////////// Contractor ledger/////////

            $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . $_POST['vendor_ledger_sno'] . '", "' . $_POST['praposemoney'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
            execute_query($sql);
            if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.06 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';


            if (isset($_POST['ded_cgst_amount']) && $_POST['ded_cgst_amount'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . (!empty($ded_cgst['rate']) ? $ded_cgst['rate'] : ($ded_gstw['rate'] ?? '')) . '", "' . $_POST['ded_cgst_amount'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.07 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

            if (isset($_POST['ded_sgst_amount']) && $_POST['ded_sgst_amount'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . (!empty($ded_sgst['rate']) ? $ded_sgst['rate'] : ($ded_sgst['rate'] ?? '')) . '", "' . $_POST['ded_sgst_amount'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.07 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

// Error 1.09 & 1.10 - GST TDS (CGST + SGST split)
            if (isset($_POST['gsttds']) && $_POST['gsttds'] > 0) {
                $cgsttds_amt = number_format($_POST['gsttds'] / 2, 2, '.', '');
                $sgsttds_amt = number_format($_POST['gsttds'] - $cgsttds_amt, 2, '.', '');

                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . (!empty($cgsttds['rate']) ? $cgsttds['rate'] : ($cgsttds['rate'] ?? '')) . '", "' . $cgsttds_amt . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.09 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';

                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . (!empty($sgsttds['rate']) ? $sgsttds['rate'] : ($sgsttds['rate'] ?? '')) . '", "' . $sgsttds_amt . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.10 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }


// Error 1.11 - Labour Cess
            if (isset($_POST['leborses']) && $_POST['leborses'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . ($labourcessw['rate'] ?? '') . '", "' . $_POST['leborses'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.11 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';

            }

// Error 1.12 - IT TDS
            if (isset($_POST['incometax']) && $_POST['incometax'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . ($ittds['rate'] ?? '') . '", "' . $_POST['incometax'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.12 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

            if (isset($_POST['royalty']) && $_POST['royalty'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . ($royalty['rate'] ?? '') . '", "' . $_POST['royalty'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.13 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

            if (isset($_POST['security']) && $_POST['security'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . ($advcen['rate'] ?? '') . '", "' . $_POST['security'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.13 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

            if (isset($_POST['other_amount']) && $_POST['other_amount'] > 0) {
                $sql = 'INSERT INTO `billit_stock_erp_payment` (`journal_id`, `by`, `to`, `amount`, `timestamp`, `unit_id`, `status`, `created_by`, `creation_time`) VALUES ("' . $id_stock . '", "", "' . ($other['rate'] ?? '') . '", "' . $_POST['other_amount'] . '", "' . date("d-m-Y") . '", "' . $_POST['unit_id'] . '", "", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '")';
                execute_query($sql);
                if (mysqli_error($db)) $msg .= '<p class="alert alert-danger">Error # 1.14 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            }

            if (mysqli_error($db)) {
                $msg .= '<div class="alert alert-danger">Error # 1.369 >> ' . $sql . '</div>';
            } else {
                if (mysqli_error($db)) {
                    $msg .= '<div class="alert alert-danger">Error # 1.369 >> ' . $sql . '</div>';
                } else {
                    $msg .= 'Successfully added';
                    $voucher_no = $_POST['voucher_no'] ?: 'FT' . date('Y') . sprintf('%04d', $inv_id);
                    if (empty($_POST['voucher_no'])) {
                        $update_sql = 'UPDATE invoice_account_fund_transafer SET voucher_no = "' . $voucher_no . '" WHERE sno = "' . $inv_id . '"';
                        execute_query($update_sql);
                    }
                }
            }
        }}
    } else {

        postblank:
        $_POST['unit_id'] = "";
        $_POST['department'] = "";
        $_POST['sub_department_id'] = "";
        $_POST['district'] = "";
        $_POST['project_name'] = "";
        $_POST['fund_transfer_to'] = "";
        $_POST['order_no'] = "";
        $_POST['voucher_no'] = (!isset($_GET['edit_sno'])) ? generateVoucherNumber('invoice_account_fund_transafer', 'voucher_no', 'FT') : '';
        $_POST['order_date'] = date("d-m-Y");
        $_POST['transafer_date'] = date("d-m-Y");
        $_POST['transafer_amount'] = "";
        $_POST['gstdeduction'] = "";
        $_POST['royalty'] = "";
        $_POST['security'] = "";
        $_POST['totelmgst'] = "";
        $_POST['sentagepercentage'] = "";
        $_POST['sentage'] = "";
        $_POST['gst_per'] = "";
        $_POST['gsttdspercentage'] = "";
        $_POST['gsttds'] = "";
        $_POST['cgst_amount'] = "";
        $_POST['sgst_amount'] = "";
        $_POST['leborses'] = "";
        $_POST['incometax'] = "";
        $_POST['it_per'] = "";
        $_POST['praposemoney'] = "";
        $_POST['remark'] = "";
        $_POST['from_account_no'] = "";
        $_POST['to_bank_name'] = "";
        $_POST['to_bank_ifsc'] = "";
        $_POST['to_account_no'] = "";
        $_POST['vendor_id'] = "";
        $_POST['vendor_name'] = "";
        $_POST['other_amount'] = "";
        $_POST['total_expen'] = "";
        $_POST['ded_gst_per'] = "";
        $_POST['ded_gstdeduction'] = "";
        $_POST['ded_cgst_amount'] = "";
        $_POST['ded_sgst_amount'] = "";
        $_POST['ded_total_withgst'] = "";
        $_POST['edit_sno'] = '';
    }

    if (isset($_GET['edit_sno'])) {
        $sql = 'select * from invoice_account_fund_transafer where sno="' . $_GET['edit_sno'] . '"';
        $data = mysqli_fetch_assoc(execute_query($sql));
        $_POST['department'] = $data['department'];
        $_POST['sub_department_id'] = $data['sub_department_id'];
        $_POST['district'] = $data['district'];
        $_POST['project_name'] = $data['project_name'];
        $_POST['fund_transfer_to'] = $data['fund_transfer_to'];
        $_POST['order_no'] = $data['order_no'];
        $_POST['voucher_no'] = $data['voucher_no'];
        $_POST['order_date'] = date('Y-m-d', strtotime($data['order_date']));
        $_POST['transafer_date'] = date('Y-m-d', strtotime($data['transafer_date']));
        $_POST['bill_date'] = $data['bill_date'] ?? date('Y-m-d');
    $_POST['bill_no'] = $data['bill_no'] ?? '';
        $_POST['transafer_amount'] = $data['transafer_amount'];
        $_POST['gstdeduction'] = $data['gstdeduction'];
        $_POST['cgst_amount'] = $data['cgst_amount'];
        $_POST['sgst_amount'] = $data['sgst_amount'];
        $_POST['sentagepercentage'] = $data['sentagepercentage'];
        $_POST['sentage'] = $data['sentage'];
        $_POST['gsttdspercentage'] = $data['gsttdspercentage'];
        $_POST['gsttds'] = $data['gsttds'];
        $_POST['leborses'] = $data['leborses'];
        $_POST['incometax'] = $data['incometax'];
        $_POST['praposemoney'] = $data['praposemoney'];
        $_POST['remark'] = $data['remark'];
        $_POST['from_account_no'] = $data['from_account_no'];
        $_POST['to_bank_name'] = $data['to_bank_name'];
        $_POST['to_bank_ifsc'] = $data['to_bank_ifsc'];
        $_POST['to_account_no'] = $data['to_account_no'];
        $_POST['vendor_id'] = $data['vendor_id'];
        $_POST['ded_gst_per'] = $data['ded_gst_per'];
        $_POST['ded_gstdeduction'] = $data['ded_gstdeduction'];
        $_POST['ded_cgst_amount'] = $data['ded_cgst_amount'];
        $_POST['ded_sgst_amount'] = $data['ded_sgst_amount'];

        $_POST['edit_sno'] = $data['sno'];
    }

    if (isset($_GET['del'])) {

        $sql = 'UPDATE  invoice_account_fund_transafer SET status="5" where sno="' . $_GET['del'] . '"';
        execute_query($sql);

        // --- NAYI LINES YAHAN ADD KI GAYI HAIN ---
        // Ledger se bhi delete karein taki account books theek rahein
        execute_query('DELETE FROM billit_stock_erp_payment WHERE journal_id IN (SELECT sno FROM billit_invoice_erp_payment WHERE table_name="invoice_account_fund_transafer" AND table_id="'.$_GET['del'].'")');
        execute_query('DELETE FROM billit_invoice_erp_payment WHERE table_name="invoice_account_fund_transafer" AND table_id="'.$_GET['del'].'"');
        // ----------------------------------------

        $msg1 .= '<p class="alert alert-danger">Data Deleted.</p>';
    }

    if (isset($_GET['id'])) {
	$sql = 'select * from invoice_account_fund_transafer where sno="' . $_GET['id'] . '"';
	$data = mysqli_fetch_assoc(execute_query($sql));
	$_POST['department'] = $data['department'];
	$_POST['sub_department_id'] = $data['sub_department_id'];
	$_POST['district'] = $data['district'];
	$_POST['project_name'] = $data['project_name'];
	$_POST['fund_transfer_to'] = $data['fund_transfer_to'];
	$_POST['transafer_amount'] = $data['transafer_amount'];
	$_POST['bill_date'] = $data['bill_date'];
	$_POST['bill_no'] = $data['bill_no'];
	$_POST['edit_sno'] = $data['sno'];

	// Get vendor information from project
	$sql_vendor = 'SELECT t.project_awarded_to, v.firm_name, v.contractor_name 
				   FROM tender_allotment t 
				   LEFT JOIN vendor v ON t.project_awarded_to = v.sno 
				   WHERE t.project_id = "' . $data['project_name'] . '" AND t.status != "5" 
				   LIMIT 1';
	$vendor_data = mysqli_fetch_assoc(execute_query($sql_vendor));
	if ($vendor_data) {
		$_POST['vendor_name'] = $vendor_data['firm_name'] . ' (' . $vendor_data['contractor_name'] . ')';
		$_POST['vendor_id'] = $vendor_data['project_awarded_to'];
	} else {
		$_POST['vendor_name'] = '';
		$_POST['vendor_id'] = '';
	}
}
?>
<style>
	label {
		color: #333 !important;
		font-weight: 700 !important;
		font-size: 14px !important;
		margin-bottom: 4px;
		text-transform: uppercase;
		letter-spacing: 0.3px;
	}

	.form-control,
	.form-select {
		border: 1px solid #d1d5db !important;
		border-radius: 4px !important;
		height: 38px !important;
		background-color: #ffffff !important;
		color: #111827 !important;
		font-weight: 500 !important;
		font-size: 14px !important;
		transition: all 0.2s ease;
	}

	.form-control:focus,
	.form-select:focus {
		border-color: #ae1f20 !important;
		box-shadow: 0 0 0 3px rgba(174, 31, 32, 0.1) !important;
		background-color: #fff !important;
	}

	.form-control[readonly] {
		background-color: #f9fafb !important;
		border: 1px solid #e5e7eb !important;
		color: #4b5563 !important;
		cursor: not-allowed;
	}

	#total_expen,
	#total_withgst,
	#net_payment,
	#praposemoney {
		border-left: 4px solid #ae1f20 !important;
		font-weight: 700 !important;
		color: #ae1f20 !important;
		background-color: #fff !important;
	}

	.card-header {
		background: #ae1f20 !important;
		padding: 15px !important;
	}

	.btn-success,
	.btn-primary {
		background-color: #ae1f20 !important;
		border: none !important;
		padding: 10px 25px !important;
		border-radius: 4px !important;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	input[type="number"],
	.text-right-input {
		text-align: right !important;
	}
	
	/* All normal text inputs left-aligned, but amounts/percentages right-aligned */
	input[type="text"]:not([name="order_no"]):not([name="voucher_no"]):not([name="bill_no"]):not([name="remark"]):not([name="other_additions"]) {
		text-align: right !important;
	}
	
	.form-control[readonly],
	.form-select[readonly] {
		background-color: #f9f9f9 !important;
		color: #000 !important;
		font-weight: 600 !important;
		border-style: dashed !important;
		border-color: #ae1f20 !important;
		cursor: default;
	}

	/* Section Styles */
	.section-card {
		border: 2px solid #e5e7eb;
		border-radius: 8px;
		margin-bottom: 20px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
	}

	.section-header {
		background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
		padding: 12px 20px;
		border-bottom: 2px solid #e5e7eb;
		font-weight: 800;
		font-size: 16px;
		color: #333;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.section-header.additions {
		background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
		border-bottom: 2px solid #22c55e;
		color: #15803d;
	}

	.section-header.deductions {
		background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
		border-bottom: 2px solid #ef4444;
		color: #b91c1c;
	}

	.section-body {
		padding: 20px;
	}

	.calculation-summary {
		background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
		border: 2px solid #e2e8f0;
		color: #1e293b;
		padding: 20px;
		border-radius: 8px;
		margin-top: 20px;
		box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
	}

	.summary-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 10px 0;
		border-bottom: 1px solid rgba(0, 0, 0, 0.08);
		font-weight: 600;
	}

	.summary-item:last-child {
		border-bottom: none;
		font-size: 18px;
		font-weight: 800;
		color: #ae1f20;
	}
</style>

<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post"
	action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="return validateForm();">
	<div class="card mb-4 shadow-sm" style="border-radius: 12px; border: 1px solid #f8e5e5; overflow: hidden;">
		<div class="card-header bg-transparent pt-4 pb-3" style="border-bottom: 1px solid #f8e5e5; background-color: transparent !important; text-align: left !important;">
			<h3 class="mb-0 text-left" style="color: #ae1f20; font-weight: 800; font-size: 1.5rem; text-transform: uppercase; letter-spacing: 1px;">
				<i class="fas fa-file-invoice-dollar mr-2"></i> Add Applicable Taxes on Bill
			</h3>
		</div>
<?php 
if ($msg != '') {
    echo '<h5>' . alert($msg) . '</h5>';
} 
?>

		<div class="card-body">
			<!-- Basic Information Section -->
			<div class="section-card">
				<div class="section-header">
					📋 Basic Information
				</div>
				<div class="section-body">
					<div class="row">
						<div class="col-md-2 ">
							<div class="form-group">
								<label>विभाग</label><br>
								<select class="form-control" name="department" id="department"
									tabindex="<?php echo $tab++; ?>"
									onChange="fill_district(this.value), fill_sub_department(this.value)" readonly>
									<option value="">--- Select ---</option>
									<?php
									if (!empty($_SESSION['department'])) {
										$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi, uprnss_department_name.department_name_english FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in (' . implode(",", $_SESSION['department']) . ') group by department_id) ';
									} elseif (!empty($_SESSION['divisions'])) {
										$query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi, uprnss_department_name.department_name_english FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where division_id in (' . implode(",", $_SESSION['divisions']) . ') group by division_id ) ';
									}
									$run = mysqli_query($db, $query);
									while ($data = mysqli_fetch_array($run)) {
										echo '<option value="' . $data['sno'] . '" ';
										if (isset($_POST['department'])) {
											if ($_POST['department'] == $data['sno']) {
												echo ' selected="Selected"';
											}
										}
										echo '>' . trim($data['department_name_english'] . " (" . $data['department_name_hindi'] . ")") . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>उप विभाग</label>
								<select class="form-control" name="sub_department_id" id="sub_department_id"
									tabindex="<?php echo $tab++; ?>" readonly>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>आच्छादित जनपद</label>
								<select class="form-control" name="district" id="district"
									tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)" readonly>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>परियोजना का नाम</label><br>
								<select class="form-control" name="project_name" id="project_name"
									tabindex="<?php echo $tab++; ?>" onChange="fill_division(this.value); loadProjectMappingInfo();" readonly>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Project Ledger <span class="text-success small" id="map_status" style="display:none;">Saved ✓</span> <a href="billit_ledgers.php" target="_blank" title="Create Ledger" style="margin-left:5px; color:#16a34a;"><i class="fas fa-plus-circle"></i></a></label>
								<select class="form-control ledger-select" name="project_ledger_id" id="project_ledger_id" tabindex="<?php echo $tab++; ?>" onChange="save_project_ledger_mapping()"></select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Order No</label>
								<input type="text" name="order_no" id="order_no" class="form-control" placeholder=""
									value="<?php echo $_POST['order_no']; ?>" tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Voucher No</label>
								<input type="text" name="voucher_no" id="voucher_no" class="form-control"
									placeholder="FT20240001" value="<?php echo $_POST['voucher_no'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Transfer Date</label>
								<input type="date" name="transafer_date" id="transafer_date" class="form-control"
									placeholder="" value="<?php echo $_POST['transafer_date']; ?>"
									tabindex="<?php echo $tab++; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Bill No.</label>
								<input type="text" name="bill_no" id="bill_no" class="form-control" placeholder=""
									value="<?php echo isset($_POST['bill_no']) ? $_POST['bill_no'] : ''; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>Bill Date</label>
								<script type="text/javascript" language="javascript">
									document.writeln(DateInput('bill_date', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['bill_date']; ?>', <?php echo $tab;
									   $tab += 4; ?>));
								</script>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Bill Amount</label>
								<input type="text" name="transafer_amount" id="transafer_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['transafer_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" oninput="findcalculation()">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Vendor Name</label>
								<div class="input-group">
									<select class="form-control" name="vendor_name" id="vendor_name"
										tabindex="<?php echo $tab++; ?>" onchange="updateVendorId()">
										<option value="">--- Select Vendor ---</option>
										<?php
										$query = 'SELECT * FROM vendor ORDER BY firm_name ASC';
										$run = mysqli_query($db, $query);
										while ($vendor = mysqli_fetch_array($run)) {
											echo '<option value="' . $vendor['sno'] . '" ';
											if (isset($_POST['vendor_id']) && $_POST['vendor_id'] == $vendor['sno']) {
												echo ' selected="selected"';
											}
											echo '>' . trim($vendor['firm_name']) . ' (' . trim($vendor['contractor_name']) . ')</option>';
										}
										?>
									</select>
									<div class="input-group-append">
									<button type="button" class="btn btn-primary" onclick="addNewVendor()">Add New Vendor</button>
									</div>
								</div>
								<input type="hidden" name="vendor_id" id="vendor_id"
									value="<?php echo $_POST['vendor_id']; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Remark </label>
								<textarea type="text" name="remark" id="remark" class="form-control" placeholder=""
									tabindex="<?php echo $tab++; ?>"> <?php echo $_POST['remark']; ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Additions Section -->
			<div class="section-card">
				<div class="section-header additions">
					➕ ADDITIONS (जोड़े जाने वाले राशि)
				</div>
				<div class="section-body">
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>GST%</label>
								<select class="form-control" name="gst_per" id="gst_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="10">10%</option>
									<option value="18">18%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>GST Amount</label>
								<input type="text" name="gstdeduction" id="gstdeduction" class="form-control"
									placeholder="" value="<?php echo $_POST['gstdeduction']; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>CGST Amount</label>
								<input type="text" name="cgst_amount" id="cgst_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['cgst_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>SGST Amount</label>
								<input type="text" name="sgst_amount" id="sgst_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['sgst_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Total Amount</label>
								<input type="text" name="total_withgst" id="total_withgst" class="form-control"
									placeholder="" value="<?php echo $_POST['total_withgst'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Deductions Section -->
			<div class="section-card">
				<div class="section-header deductions">
					➖ DEDUCTIONS (जो काटे जाने वाले राशि)
				</div>
				<div class="section-body">
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>GST%</label>
								<select class="form-control" name="ded_gst_per" id="ded_gst_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="10" <?php if(isset($_POST['ded_gst_per']) && $_POST['ded_gst_per'] == '10') echo 'selected'; ?>>10%</option>
									<option value="18" <?php if(isset($_POST['ded_gst_per']) && $_POST['ded_gst_per'] == '18') echo 'selected'; ?>>18%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>GST Amount</label>
								<input type="text" name="ded_gstdeduction" id="ded_gstdeduction" class="form-control"
									placeholder="" value="<?php echo $_POST['ded_gstdeduction'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>CGST Amount</label>
								<input type="text" name="ded_cgst_amount" id="ded_cgst_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['ded_cgst_amount'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>SGST Amount</label>
								<input type="text" name="ded_sgst_amount" id="ded_sgst_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['ded_sgst_amount'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Total Amount</label>
								<input type="text" name="ded_total_withgst" id="ded_total_withgst" class="form-control"
									placeholder="" value="<?php echo $_POST['ded_total_withgst'] ?? ''; ?>"
									tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>Inc. Tax % </label>
								<select class="form-control" name="it_per" id="it_per" tabindex="<?php echo $tab++; ?>"
									onchange="findcalculation()">
									<option value=""></option>
									<option value="2">2%</option>
									<option value="10">10%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Income Tax Amount </label>
								<input type="text" name="incometax" id="incometax" class="form-control" placeholder=""
									value="<?php echo $_POST['incometax']; ?>" tabindex="<?php echo $tab++; ?>"
									readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>GST TDS% </label>
								<select class="form-control" name="gsttdspercentage" id="gsttdspercentage"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="2">2%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>GST TDS Amount</label>
								<input type="text" name="gsttds" id="gsttds" class="form-control" placeholder=""
									value="<?php echo $_POST['gsttds']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Security % </label>
								<select class="form-control" name="security_per" id="security_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="1">1%</option>
									<option value="2">2%</option>
									<option value="3">3%</option>
									<option value="4">4%</option>
									<option value="5">5%</option>
									<option value="6">6%</option>
									<option value="7">7%</option>
									<option value="8">8%</option>
									<option value="9">9%</option>
									<option value="10">10%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Security Amount </label>
								<input type="text" name="security" id="security" class="form-control" placeholder=""
									value="<?php echo $_POST['security']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label>Royalty Amount </label>
								<input type="text" name="royalty" id="royalty" class="form-control" placeholder=""
									value="<?php echo $_POST['royalty']; ?>" tabindex="<?php echo $tab++; ?>"
									oninput="findcalculation()">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>labour cess%</label>
								<select class="form-control" name="leborses_per" id="leborses_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="1">1%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>labour cess</label>
								<input type="text" name="leborses" id="leborses" class="form-control" placeholder=""
									value="<?php echo $_POST['leborses']; ?>" tabindex="<?php echo $tab++; ?>" readonly>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Other Title</label>
								<select name="other_title" id="other_title" class="form-control"
									tabindex="<?php echo $tab++; ?>">
									<option></option>
									<?php
									$sql = 'select * from fund_transfar_other_title';
									$row_type = execute_query($sql);
									while ($type_details = mysqli_fetch_array($row_type)) {
										echo '<option value="' . $type_details['sno'] . '"';

										echo '>' . $type_details['title_name'] . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Oth%/Amt. </label>
								<select class="form-control" name="other_per" id="other_per"
									tabindex="<?php echo $tab++; ?>" onchange="findcalculation()">
									<option value=""></option>
									<option value="amt">AMT.</option>
									<option value="2">2%</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label>Other Amount</label>
								<input type="text" name="other_amount" id="other_amount" class="form-control"
									placeholder="" value="<?php echo $_POST['other_amount']; ?>"
									tabindex="<?php echo $tab++; ?>" onblur="findcalculation()">
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Calculation Summary Section -->
			<div class="calculation-summary">
				<h5 class="text-center mb-3">📊 CALCULATION SUMMARY</h5>
				<div class="row">
					<div class="col-md-6">
						<div class="summary-item">
							<span>Bill Amount:</span>
							<span>₹<span id="summary_bill_amount">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>Total Additions:</span>
							<span style="color: #22c55e;">₹<span id="summary_additions">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>Total Deductions:</span>
							<span style="color: #ef4444;">₹<span id="summary_deductions">0.00</span></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="summary-item">
							<span>Total Expenditure:</span>
							<span>₹<span id="summary_total_expen">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>NET Payment:</span>
							<span style="color: #fbbf24;">₹<span id="summary_net_payment">0.00</span></span>
						</div>
						<div class="summary-item">
							<span>Total with GST:</span>
							<span>₹<span id="summary_total_withgst">0.00</span></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Final Results Section -->
			<div class="row mt-5 mb-4">
				<div class="col-md-6">
					<div class="form-group">
						<label style="font-size: 15px; color: #ae1f20 !important;">Total Expendeture</label>
						<input type="text" name="total_expen" id="total_expen" class="form-control" placeholder=""
							value="<?php echo $_POST['total_expen']; ?>" tabindex="<?php echo $tab++; ?>" readonly
							style="font-size: 18px !important; height: 45px !important; color: #ae1f20 !important; font-weight: bold !important;">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label style="font-size: 15px; color: #15803d !important;">NET Payment</label>
						<input type="text" name="praposemoney" id="praposemoney" class="form-control" placeholder=""
							value="<?php echo $_POST['praposemoney']; ?>" tabindex="<?php echo $tab++; ?>" readonly
							style="font-size: 18px !important; height: 45px !important; color: #15803d !important; font-weight: bold !important; border-left-color: #15803d !important;">
					</div>
				</div>
			</div>

			<hr>
			<div class="row mt-4">
				<div class="col-md-6">
					<div class="form-group">
						<label style="font-size: 15px;">From Account</label>
						<select class="form-control" name="from_account_no" id="from_account_no"
							tabindex="<?php echo $tab++; ?>" required style="font-size: 16px !important; height: 45px !important; font-weight: bold !important; color: #ae1f20 !important;">
							<option value="">--- Select ---</option>
							<?php
							$query = 'select * from billit_customer where parent ="479" order by cus_name';
							$run = mysqli_query($db, $query);
							while ($data = mysqli_fetch_array($run)) {
								echo '<option value="' . $data['sno'] . '" ';
								if (isset($_POST['from_account_no'])) {
									if ($_POST['from_account_no'] == $data['sno']) {
										echo ' selected="Selected"';
									}
								}
								echo '>' . trim($data['cus_name']) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-6" id="bank_unit">
					<div class="form-group">
						<input type="hidden" name="unit_id" id="unit_id" class="form-control" placeholder=""
							value="<?php echo $_POST['unit_id']; ?>" tabindex="<?php echo $tab++; ?>"
							onblur="fill_bank_details(this.value, '<?php echo $_POST['to_bank_name']; ?>')">
                        <label style="font-size: 15px;">To Account</label>
                        <input type="hidden" name="to_bank_name" id="to_bank_name_hidden" value="<?php echo htmlspecialchars($_POST['to_bank_name'] ?? ''); ?>">
                        <input type="hidden" name="vendor_ledger_sno" id="vendor_ledger_sno" value="">
                        <select class="form-control" id="bank_name_unit" tabindex="<?php echo $tab++; ?>"
                                style="font-size: 15px !important; height: 45px !important; font-weight: bold !important; color: #333 !important;">
                            <option value="">--- Select Vendor First ---</option>
                        </select>
                        <small class="text-muted" id="vendor_ledger_hint" style="display:none; color:#856404;">
                            <i class="fas fa-exclamation-triangle"></i> No ledger mapped — select one to map it automatically.
                        </small>
                    </div>
				</div>
			</div></br>
			<div class="row">
				<div class="col-md-12" align="center">
					<div class="form-group">
						<button type="submit" name="submit" class="btn btn-success btn-fill pull-right">Submit</button>
						<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

<script>
	// Validate form before submission
	function validateForm() {
		// Run calculation one more time to ensure all values are up to date
		findcalculation();

		// Get required fields
		const transferAmount = parseFloat(document.getElementById('transafer_amount').value) || 0;
		const netPayment = document.getElementById('praposemoney').value;
		const fromAccount = document.getElementById('from_account_no').value;
		const toAccount = document.getElementById('bank_name_unit').value;

		// Validations
		if (transferAmount <= 0) {
			alert('Please enter a valid Transfer Amount');
			document.getElementById('transafer_amount').focus();
			return false;
		}

		if (!fromAccount) {
			alert('Please select From Account');
			document.getElementById('from_account_no').focus();
			return false;
		}

		if (!toAccount) {
			alert('Please enter To Account');
			document.getElementById('bank_name_unit').focus();
			return false;
		}

		if (!netPayment || netPayment === '0' || netPayment === '') {
			alert('Net Payment calculation issue. Please check the amounts.');
			return false;
		}

        // Vendor ledger mapping save karo agar manually select ki hai
        var vendorId  = document.getElementById('vendor_id').value;
        var ledgerSno = document.getElementById('vendor_ledger_sno').value;
        var alreadyMapped = document.getElementById('vendor_ledger_mapped_info').style.display !== 'none';

        if (vendorId && ledgerSno && !alreadyMapped) {
            // Sync AJAX — form submit se pehle save karo
            $.ajax({
                url: 'scripts/ajax.php?id=save_vendor_ledger',
                type: 'POST',
                async: false, // form submit se pehle complete hona chahiye
                data: { term: 'b', vendor_id: vendorId, ledger_sno: ledgerSno },
                dataType: 'json'
            });
        }

		return true;
	}

	/* ---------- 50 paise rounding rule (same as fund_recive.php) ---------- */
	function customRound(number) {
		number = Number(number);
		if (!isFinite(number) || number === 0) return '0.00';
		var int = Math.floor(number);
		var decimal = number - int;
		if (decimal === 0)    return int.toFixed(2);
		if (decimal <  0.50)  return int.toFixed(2);
		if (decimal === 0.50) return (int + 0.50).toFixed(2);
		return (int + 1).toFixed(2);
	}

	//calculation of fund transfer
	function findcalculation() {

		let transafer_amount = parseFloat(document.getElementById("transafer_amount").value);
		transafer_amount = parseFloat(transafer_amount);
		if (!transafer_amount) {
			transafer_amount = 0;
		}

		let gst_per = parseFloat(document.getElementById("gst_per").value);
		gst_per = parseFloat(gst_per);
		if (!gst_per) {
			gst_per = 0;
		}

		let gstdeduction = parseFloat(document.getElementById("gstdeduction").value);
		gstdeduction = parseFloat(gstdeduction);
		if (!gstdeduction) {
			gstdeduction = 0;
		}

		let gsttdspercentage = parseFloat(document.getElementById("gsttdspercentage").value);
		gsttdspercentage = parseFloat(gsttdspercentage);
		if (!gsttdspercentage) {
			gsttdspercentage = 0;
		}

		let gsttds = parseFloat(document.getElementById("gsttds").value);
		gsttds = parseFloat(gsttds);
		if (!gsttds) {
			gsttds = 0;
		}

		let it_per = parseFloat(document.getElementById("it_per").value);
		it_per = parseFloat(it_per);
		if (!it_per) {
			it_per = 0;
		}

		let leborses = parseFloat(document.getElementById("leborses").value);
		if (!leborses) {
			leborses = 0;
		}

		let leborses_per = parseFloat(document.getElementById("leborses_per").value);
		leborses_per = parseFloat(leborses_per);
		if (!leborses_per) {
			leborses_per = 0;
		}

		let incometax = parseFloat(document.getElementById("incometax").value);
		incometax = parseFloat(incometax);
		if (!incometax) {
			incometax = 0;
		}

		let praposemoney = parseFloat(document.getElementById("praposemoney").value);
		praposemoney = parseFloat(praposemoney);
		if (!praposemoney) {
			praposemoney = 0;
		}

		let security_per = parseFloat(document.getElementById("security_per").value);
		security_per = parseFloat(security_per);
		if (!security_per) {
			security_per = 0;
		}
		// otherAmount will be read after the percentage block below

		let royalty = parseFloat(document.getElementById("royalty").value);
		royalty = parseFloat(royalty);
		if (!royalty) {
			royalty = 0;
		}

		let other_additions_el = document.getElementById("other_additions");
		let other_additions = other_additions_el ? parseFloat(other_additions_el.value) : 0;
		if (isNaN(other_additions)) {
			other_additions = 0;
		}

		let total_withgst_el = document.getElementById("total_withgst");
		let total_withgst = total_withgst_el ? parseFloat(total_withgst_el.value) : 0;
		if (isNaN(total_withgst)) {
			total_withgst = 0;
		}

		let ded_gst_per = parseFloat(document.getElementById("ded_gst_per").value);
		ded_gst_per = parseFloat(ded_gst_per);
		if (!ded_gst_per) {
			ded_gst_per = 0;
		}

		// gst calculation
		var gstdeductionres = customRound((transafer_amount * gst_per) / 100);
		document.getElementById('gstdeduction').value = gstdeductionres;

		// Split GST into CGST and SGST (50% each)
		var cgst_amount = customRound(gstdeductionres / 2);
		var sgst_amount = customRound(gstdeductionres / 2);
		document.getElementById('cgst_amount').value = cgst_amount;
		document.getElementById('sgst_amount').value = sgst_amount;

		// ded gst calculation
		var ded_gstdeductionres = customRound((transafer_amount * ded_gst_per) / 100);
		document.getElementById('ded_gstdeduction').value = ded_gstdeductionres;

		// Split ded GST into CGST and SGST (50% each)
		var ded_cgst_amount = customRound(ded_gstdeductionres / 2);
		var ded_sgst_amount = customRound(ded_gstdeductionres / 2);
		document.getElementById('ded_cgst_amount').value = ded_cgst_amount;
		document.getElementById('ded_sgst_amount').value = ded_sgst_amount;

		var ded_total_withgst_amt = parseFloat(transafer_amount) - parseFloat(ded_gstdeductionres);
		document.getElementById('ded_total_withgst').value = customRound(ded_total_withgst_amt);

		// security calculation
		var securityres = customRound((transafer_amount * security_per) / 100);
		document.getElementById('security').value = securityres;

		//gst tds % 
		var gsttdsres = customRound((transafer_amount * gsttdspercentage) / 100);
		document.getElementById('gsttds').value = gsttdsres;

		var leborsesres = customRound((transafer_amount * leborses_per) / 100);
		document.getElementById('leborses').value = leborsesres;

		//income
		var incometaxres = customRound((transafer_amount * it_per) / 100);
		document.getElementById('incometax').value = incometaxres;

		var otherPer = document.getElementById('other_per').value;
		var otherAmountInput = document.getElementById('other_amount');
		if (otherPer === 'amt') {
			// Enable manual entry
			otherAmountInput.readOnly = false;
		} else if (otherPer !== '' && !isNaN(parseFloat(otherPer))) {
			// Auto-calculate percentage of transferAmount
			var percent = parseFloat(otherPer);
			var calculatedAmount = (transafer_amount * percent) / 100;
			otherAmountInput.value = customRound(calculatedAmount);
			otherAmountInput.readOnly = true;
		} else {
			// No percentage selected - allow manual entry
			otherAmountInput.readOnly = false;
		}

		// Read final value of Other Amount for deduction
		var otherAmount = parseFloat(otherAmountInput.value) || 0;

		var abcd = parseFloat(royalty) + parseFloat(incometaxres) + parseFloat(securityres) + parseFloat(gsttdsres) + parseFloat(leborsesres) + parseFloat(ded_gstdeductionres);
		abcd = customRound(abcd);

		var total_withgst_amt = parseFloat(transafer_amount) + parseFloat(gstdeductionres);
		total_withgst_amt = customRound(total_withgst_amt);
		document.getElementById('total_withgst').value = total_withgst_amt;

		// Total expenditure calculation (without centage and contingency)
		var total_expenses = (
			(parseFloat(transafer_amount) || 0) +
			(parseFloat(gstdeductionres) || 0) +
			(parseFloat(other_additions) || 0)
		);
		document.getElementById('total_expen').value = customRound(total_expenses);

		// NET Payment calculation — customRound applies 50 paise rule
		var prakhand_ko_preshit_amount = customRound(total_expenses - abcd - otherAmount);

		document.getElementById('praposemoney').value = prakhand_ko_preshit_amount;

		// Update Summary Section
		updateSummary(transafer_amount, gstdeductionres, other_additions, abcd, otherAmount, 'Deducted', total_expenses, prakhand_ko_preshit_amount, total_withgst_amt);
	}

	// Update Summary Section
	function updateSummary(billAmount, gstAmount, otherAdditions, totalDeductions, otherAmount, otherType, totalExpenses, netPayment, totalWithGst) {
		document.getElementById('summary_bill_amount').textContent = customRound(parseFloat(billAmount || 0));

		// Total Additions
		var totalAdditions = parseFloat(gstAmount || 0) + parseFloat(otherAdditions || 0);
		document.getElementById('summary_additions').textContent = customRound(totalAdditions);

		// Total Deductions
		var totalDed = parseFloat(totalDeductions || 0);
		if (otherType === 'Add') {
			// Other amount is added, not deducted
		} else if (otherType === 'Deducted') {
			totalDed += parseFloat(otherAmount || 0);
		}
		document.getElementById('summary_deductions').textContent = customRound(totalDed);

		document.getElementById('summary_total_expen').textContent = customRound(parseFloat(totalExpenses || 0));
		document.getElementById('summary_net_payment').textContent = customRound(parseFloat(netPayment || 0));
		document.getElementById('summary_total_withgst').textContent = customRound(parseFloat(totalWithGst || 0));
	}

</script>

<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>


<!--  Charts Plugin -->
<script src="js/chartist.min.js"></script>

<script>
	var actionUrl = 'scripts/ajax.php';

	function fill_sub_department(val, selected) {
		var data = { "term": "b", "id": "sub_dep", "val": val };

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				// console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.sub_department_hindi + '</option>';

				});
				$("#sub_department_id").html(txt);
			}
		});
	}

	function fill_district(val, selected) {
		var data = { "term": "b", "id": "dist", "val": val };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.district_name + '</option>';

				});
				$("#district").html(txt);
			}
		});
	}

	function fill_project(val, selected) {
		var data = { "term": "b", "id": "proj", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (data) {
				var txt = '<option value="">--Select--</option>';
				data = JSON.parse(data);
				$.each(data, function (key, value) {
					txt += '<option value="' + value.id + '" ';
					if (selected == value.id) {
						txt += ' selected ';
					}
					txt += '>' + value.project_name_hindi + '</option>';

				});
				$("#project_name").html(txt);
			}
		});
	}


	function fill_division(val) {
		var data = { "term": "b", "id": "proj_div", "val": val, "dept": $("#department").val() };
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
            success: function (data) {
                try {
                    data = JSON.parse(data);
                    $("#unit_id").val(data.division_id);
                    fill_bank_details(data.division_id);
                    fill_unit_ledgers(data.division_id);
                } catch(e) {
                    console.error('fill_division parse error:', data);
                }
			}

		});
	}



	function fill_bank_details(val, selected) {
		var data = { "term": "b", "id": "unit_bank", "val": val };
		var vendorSelect = document.getElementById('vendor_name');
		var isVendorSelected = vendorSelect && vendorSelect.value !== '';

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function (ajaxdata) {
				//console.log(ajaxdata);
				ajaxdata = JSON.parse(ajaxdata);

				// Add those fetched unit-bank options into From Account dropdown if they don't exist
				$.each(ajaxdata, function (key, value) {
					if ($("#from_account_no option[value='" + value.id + "']").length === 0) {
						var newOption = new Option(value.cus_name, value.id, false, false);
						$('#from_account_no').append(newOption);
					}
				});

				// Auto-sync: From Account ki selected value set karo
				var fromVal = selected ? selected : (ajaxdata.length > 0 ? ajaxdata[0].id : '');

				if (fromVal) {
					// Use .trigger('change') for Select2 support
					$("#from_account_no").val(fromVal).trigger('change');
				} else if (!selected && ajaxdata.length > 0) {
					$("#from_account_no").val(ajaxdata[0].id).trigger('change');
				}

				if (isVendorSelected) {
					updateVendorId();
				}
			}
		});
	}

    function updateVendorId() {
        var vendorSelect = document.getElementById('vendor_name');
        var selectedVendor = vendorSelect.value;
        document.getElementById('vendor_id').value = selectedVendor;
        document.getElementById('vendor_ledger_sno').value = '';
        document.getElementById('vendor_ledger_hint').style.display = 'none';

        var $dropdown = $('#bank_name_unit');
        if (!selectedVendor) {
            $dropdown.html('<option value="">--- Select Vendor First ---</option>');
            if ($dropdown.hasClass('select2-hidden-accessible')) $dropdown.select2('destroy');
            $dropdown.select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select Vendor First ---' });
            $('#to_bank_name_hidden').val('');
            return;
        }

        var unit_id = document.getElementById('unit_id').value || 53;

        $.ajax({
            url: 'scripts/ajax.php?id=get_vendor_ledger',
            type: 'POST',
            data: { term: 'b', vendor_id: selectedVendor, unit_id: unit_id },
            dataType: 'json',
            success: function(res) {
                // Dropdown populate karo
                var opts = '<option value="">--- Select Ledger ---</option>';
                (res.ledgers || []).forEach(function(l) {
                    opts += '<option value="' + l.id + '">' + l.text + '</option>';
                });
                $dropdown.html(opts);

                if ($dropdown.hasClass('select2-hidden-accessible')) $dropdown.select2('destroy');
                $dropdown.select2({ theme: 'bootstrap4', width: '100%', placeholder: '--- Select Ledger ---', allowClear: true });

                if (res.ledger_sno) {
                    // Mapped — auto-select
                    $dropdown.val(res.ledger_sno).trigger('change.select2');
                    document.getElementById('vendor_ledger_sno').value = res.ledger_sno;

                    // hidden field bhi update karo
                    var ledgerName = '';
                    (res.ledgers || []).forEach(function(l) {
                        if (l.id == res.ledger_sno) ledgerName = l.text;
                    });
                    $('#to_bank_name_hidden').val(ledgerName);
                    document.getElementById('vendor_ledger_hint').style.display = 'none';
                } else {
                    // Not mapped — hint dikhao
                    document.getElementById('vendor_ledger_hint').style.display = 'block';
                }
            }
        });
    }

    // Dropdown change hone par hidden fields update karo
    $(document).on('change', '#bank_name_unit', function() {
        var selectedText = $(this).find('option:selected').text();
        document.getElementById('vendor_ledger_sno').value = this.value;
        $('#to_bank_name_hidden').val(selectedText);
    });

    // Dropdown se select karne par vendor_ledger_sno update karo
    $(document).on('change', '#vendor_ledger_dropdown', function() {
        document.getElementById('vendor_ledger_sno').value = this.value;
    });

	// Add new vendor function
	function addNewVendor() {
		var firmName = prompt("Enter Firm Name:");
		if (!firmName) return;

		var contractorName = prompt("Enter Contractor Name:");
		if (!contractorName) return;

		// AJAX call to add new vendor
		$.ajax({
			type: "POST",
			url: actionUrl,
			data: {
				"term": "add_vendor",
				"firm_name": firmName,
				"contractor_name": contractorName
			},
			success: function (response) {
				var result = JSON.parse(response);
				if (result.success) {
					// Add new option to vendor dropdown
					var vendorText = firmName + ' (' + contractorName + ')';
					var newOption = '<option value="' + result.vendor_id + '" selected="selected">' + vendorText + '</option>';
					$('#vendor_name').append(newOption);
					$('#vendor_id').val(result.vendor_id);

					// Update To Account field
					$('#bank_name_unit').val(vendorText);

					alert('Vendor added successfully!');
				} else {
					alert('Error adding vendor: ' + result.message);
				}
			},
			error: function () {
				alert('Error adding vendor. Please try again.');
			}
		});
	}

	$(document).ready(function () {
		$('#general_stat_table').DataTable({

		});

		// Initialize To Account with vendor name if a vendor is pre-selected
		if ($('#vendor_name').val() !== '') {
			updateVendorId();
		}

        <?php if (isset($_GET['edit_sno']) && !empty($_POST['vendor_id'])): ?>
        $(function() {
            // Edit mode mein vendor already selected hai, mapping load karo
            setTimeout(function() {
                updateVendorId();
            }, 300);
        });
        <?php endif; ?>
	});


	<?php
	if (isset($_GET['edit_sno'])) {
		?>
		$(document).ready(function () {
            fill_sub_department(<?php echo intval($_POST['department']); ?>, <?php echo intval($_POST['sub_department_id']); ?>);
            fill_district(<?php echo intval($_POST['department']); ?>, <?php echo intval($_POST['district']); ?>);
            fill_project(<?php echo intval($_POST['district']); ?>, <?php echo intval($_POST['project_name']); ?>);


		});
		<?php
	}
	?>


	<?php
	if (isset($_GET['id'])) {
		?>
		$(document).ready(function () {
            fill_sub_department(<?php echo intval($_POST['department']); ?>, <?php echo intval($_POST['sub_department_id']); ?>);
            fill_district(<?php echo intval($_POST['department']); ?>, <?php echo intval($_POST['district']); ?>);
            fill_project(<?php echo intval($_POST['district']); ?>, <?php echo intval($_POST['project_name']); ?>);
			fill_division(<?php echo $_POST['project_name']; ?>, <?php echo $_POST['unit_id']; ?>);

		});
		<?php
	}
	?>

	/* ---------- Select2 Searchable Dropdowns ---------- */
	function makeSearchable(selectId) {
		if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
			console.error('Select2 or jQuery not loaded');
			return;
		}
		var $select = $('#' + selectId);
		if (!$select.length) return;

		// Only apply to select elements
		if (!$select.is('select')) return;

		if ($select.data('select2')) $select.select2('destroy');
		$select.select2({
			theme: 'bootstrap4',
			width: '100%',
			placeholder: '--- Select ---',
			allowClear: true
		});
	}

	$(function () {
		var dropdowns = [
			'department', 'sub_department_id', 'district', 'project_name',
			'vendor_name', 'gst_per', 'ded_gst_per', 'it_per', 'gsttdspercentage',
			'security_per', 'leborses_per', 'other_title', 'other_per',
			'from_account_no'
		];
		dropdowns.forEach(makeSearchable);
	});

	// Re-apply Select2 after AJAX loads new option data
	$(document).ajaxComplete(function () {
		['sub_department_id', 'district', 'project_name'].forEach(makeSearchable);
	});

    function fill_unit_ledgers(unit_id) {
        if(!unit_id) return;
        $.ajax({
            url: 'scripts/ajax.php?id=get_unit_ledgers&unit_id=' + unit_id + '&term=all',
            dataType: 'json',
            success: function (ledgers) {
                var options = '<option value="">--- Select Ledger ---</option>';
                if (ledgers && ledgers.length > 0) {
                    ledgers.forEach(function (l) {
                        options += '<option value="' + l.id + '">' + l.text + '</option>';
                    });
                }
                var select = $('#project_ledger_id');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.html(options);
                if(currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
            }
        });
    }

    function save_project_ledger_mapping() {
        var project_id = $('#project_name').val();
        var ledger_id = $('#project_ledger_id').val();
        var unit_id = $('#unit_id').val();
        
        if (!project_id || !ledger_id || !unit_id) return;
        
        $.ajax({
            url: 'scripts/ajax.php',
            type: 'POST',
            data: {
                id: 'save_project_ledger_mapping',
                term: 'all',
                project_id: project_id,
                ledger_id: ledger_id,
                unit_id: unit_id
            },
            success: function(response) {
                if(response.trim() === 'success') {
                    $('#map_status').fadeIn().delay(2000).fadeOut();
                }
            }
        });
    }

    function loadProjectMappingInfo() {
        var project_id = $('#project_name').val();
        if (!project_id) return;
        
        $.ajax({
            url: 'scripts/ajax.php?id=get_project_ledger_mapping&term=all&project_id=' + project_id,
            success: function(ledger_id) {
                if (ledger_id && ledger_id.trim() !== '') {
                    $('#project_ledger_id').val(ledger_id.trim()).trigger('change.select2');
                }
            }
        });
    }

</script>

<?php
page_footer_end();

function get_bill_setting_w2($db, $desc, $unit_id) {
	$res = mysqli_query($db, "SELECT * FROM general_settings WHERE `desc`='$desc' AND unit_id='$unit_id'");
	if(mysqli_num_rows($res) > 0){
		return mysqli_fetch_assoc($res);
	}
	
	$res = mysqli_query($db, "SELECT * FROM general_settings WHERE `desc`='$desc' AND unit_id='53'");
	return mysqli_fetch_assoc($res);
}
?>
