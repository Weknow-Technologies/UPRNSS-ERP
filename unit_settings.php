<?php
include("scripts/settings.php");
include("scripts/alerts.php");
include("scripts/billit_settings.php");
$msg = '';

if (isset($_POST['submit'])) {
    $unit_id = $_POST['unit_id'];
    $keys = [
        'ITTDS',
        'GSTTDS',
        'GST',
        'CGST',
        'SGST',
        'CGSTTDS',
        'SGSTTDS',
        'LABORCESS',
        'OTHER_CHARGES',
        'ADVCEN',
        'GSTW',
        'CGSTW',
        'SGSTW',
        'GSTTDSW',
        'CGSTTDSW',
        'SGSTTDSW',
        'ITTDSW',
        'LABOURCESSW',
        'BILL_OTHER_ADD',
        'BILL_IT',
        'BILL_GST_TDS',
        'BILL_CGST_TDS',
        'BILL_SGST_TDS',
        'BILL_SECURITY',
        'BILL_LABOUR_CESS',
        'BILL_OTHER_DED',
        'BILL_ROYALTY',
        'BILL_HOLD',
        'BILL_GST',
        'BILL_CGST',
        'BILL_SGST',
        'BILL_DED_GST',
        'BILL_DED_CGST',
        'BILL_DED_SGST',
        'ADVCENRV',
        'CGSTRV',
        'SGSTRV',
        'CGSTTDSRV',
        'SGSTTDSRV',
        'ITTDSRV',
        'LABOURCESSRV',
    ];
    $settings = [];
    foreach ($keys as $k) {
        if (isset($_POST[strtolower($k)])) {
            $settings[$k] = $_POST[strtolower($k)];
        }
    }

    foreach ($settings as $desc => $rate) {
        if ($rate != '') {
            $sqlCheck = "SELECT sno FROM general_settings WHERE `desc`='$desc' AND unit_id='$unit_id'";
            $resCheck = execute_query($sqlCheck);
            if (mysqli_num_rows($resCheck) > 0) {
                $rowCheck = mysqli_fetch_assoc($resCheck);
                $sno = $rowCheck['sno'];
                $sql = "UPDATE general_settings SET rate='$rate' WHERE sno='$sno'";
            } else {
                $sql = "INSERT INTO general_settings (`unit_id`, `desc`, `rate`) VALUES ('$unit_id', '$desc', '$rate')";
            }
            execute_query($sql);
        } else {
            $sql = "DELETE FROM general_settings WHERE `desc`='$desc' AND unit_id='$unit_id'";
            execute_query($sql);
        }
    }
    $msg = "Settings updated successfully for " . get_division($unit_id);
}

page_header_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="js/sweetalert2.min.css">
<script src="js/sweetalert2.all.min.js"></script>
<style>
    :root {
        --primary: #af4a3fff;
        --primary-dark: #96281b;
        --primary-light: #fdecea;
        --radius: 12px;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .card-custom {
        border-radius: var(--radius);
        border: none;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .card-header-custom {
        background: #d9edf7 !important;
        /* light blue */
        color: #000 !important;
        /* black text */
        padding: 1.5rem;
        border: none;
        border-bottom: 2px solid #bce8f1;
    }

    .card-header-custom h4 {
        color: #000 !important;
        font-weight: 800;
    }

    .form-control {
        border-radius: 8px;
        border: 1.5px solid #eee;
        padding: 4px 15px;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(175, 74, 63, 0.1);
    }

    .btn-save {
        background: var(--primary);
        border: none;
        border-radius: 8px;
        padding: 10px 30px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-save:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Headings Styling */
    h5,
    #receive_header_text,
    #transfer_header_text {
        color: #000 !important;
        font-weight: 800 !important;
        font-size: 1.1rem !important;
        /* Reduced font size */
    }

    label.font-weight-bold {
        color: #000 !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        /* Reduced font size */
    }
</style>
<?php
page_header_end();
page_sidebar();
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header card-header-custom text-center">
                <h4 class="mb-0"><i class="fas fa-cogs mr-2"></i>General Settings</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($msg != '')
                    echo alert($msg, 'success'); ?>

                <form method="post" action="unit_settings.php">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold">Select Unit</label>
                            <select name="unit_id" id="unit_id" class="form-control select2" required>
                                <?php
                                $div_in = implode(",", $_SESSION['divisions']);
                                $sql = "SELECT * FROM uprnss_division WHERE s_no IN ($div_in) ORDER BY division_name ASC";
                                $res = execute_query($sql);
                                $num_divs = mysqli_num_rows($res);

                                if ($num_divs != 1) {
                                    echo '<option value="">--- Select Unit ---</option>';
                                }

                                while ($row = mysqli_fetch_assoc($res)) {
                                    $sel = '';
                                    if (isset($_POST['unit_id'])) {
                                        if ($_POST['unit_id'] == $row['s_no']) {
                                            $sel = 'selected';
                                        }
                                    } else {
                                        if ($num_divs == 1) {
                                            $sel = 'selected';
                                        } elseif (isset($_SESSION['usertype']) && $_SESSION['usertype'] == '6' && $row['s_no'] == 53) {
                                            $sel = 'selected';
                                        }
                                    }
                                    echo '<option value="' . $row['s_no'] . '" ' . $sel . '>' . $row['division_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div id="settings_fields">
                        <div class="row">
                            <?php
                            $receive_keys = [ 'CGST', 'SGST', 'ITTDS', 'CGSTTDS', 'SGSTTDS', 'LABORCESS', 'OTHER_CHARGES'];
                            $transfer_keys = ['ADVCEN', 'CGSTW', 'SGSTW', 'CGSTTDSW', 'SGSTTDSW', 'ITTDSW', 'LABOURCESSW'];
                            $receipt_keys = ['ADVCENRV', 'CGSTRV', 'SGSTRV', 'CGSTTDSRV', 'SGSTTDSRV', 'ITTDSRV', 'LABOURCESSRV'];
                            $bill_keys = ['BILL_OTHER_ADD', 'BILL_IT', 'BILL_CGST_TDS', 'BILL_SGST_TDS', 'BILL_SECURITY', 'BILL_LABOUR_CESS', 'BILL_OTHER_DED', 'BILL_CGST', 'BILL_SGST', 'BILL_DED_CGST', 'BILL_DED_SGST', 'BILL_ROYALTY', 'BILL_HOLD'];
                            $friendly_names = [
                                'ITTDS' => 'TDS',
                                'CGST' => 'CGST',
                                'SGST' => 'SGST',
                                'CGSTTDS' => 'CGST TDS',
                                'SGSTTDS' => 'SGST TDS',
                                'LABORCESS' => 'Labour Cess',
                                'OTHER_CHARGES' => 'Other Charges',
								
                                'ADVCEN' => 'Advance Centage',
                                'CGSTW' => 'CGST',
                                'SGSTW' => 'SGST',
                                'GSTTDSW' => 'GST TDS',
                                'CGSTTDSW' => 'CGST TDS',
                                'SGSTTDSW' => 'SGST TDS',
                                'ITTDSW' => 'Income Tax',
                                'LABOURCESSW' => 'Labour Cess',
                                'BILL_OTHER_ADD' => 'Other Additions',
                                'BILL_IT' => 'Income Tax',
                                'BILL_CGST_TDS' => 'CGST TDS',
                                'BILL_SGST_TDS' => 'SGST TDS',
                                'BILL_SECURITY' => 'Security Deposit',
                                'BILL_LABOUR_CESS' => 'Labour Cess',
                                'BILL_OTHER_DED' => 'Other Deductions',
                                'BILL_ROYALTY' => 'Royalty Amount',
                                'BILL_HOLD' => 'Hold Amount',
                                'BILL_CGST' => 'CGST',
                                'BILL_SGST' => 'SGST',
                                'BILL_DED_CGST' => 'CGST Deduction',
                                'BILL_DED_SGST' => 'SGST Deduction',
                                'ADVCENRV' => 'Advance Centage',
                                'CGSTRV' => 'CGST',
                                'SGSTRV' => 'SGST',
                                'CGSTTDSRV' => 'CGST TDS',
                                'SGSTTDSRV' => 'SGST TDS',
                                'ITTDSRV' => 'Income Tax',
                                'LABOURCESSRV' => 'Labour Cess',
                            ];
                            ?>

                            <div class="col-12 mt-2 mb-2">
                                <h5 id="receive_header_text"
                                    style="color: var(--primary); border-bottom: 2px solid var(--primary-light); padding-bottom: 5px;">
                                    <i class="fas fa-arrow-down mr-2"></i>Fund Receive Ledgers
                                </h5>
                            </div>
                            <?php foreach ($receive_keys as $key) { ?>
                                <div class="col-md-4 mb-3">
                                    <label
                                        class="font-weight-bold"><?php echo isset($friendly_names[$key]) ? $friendly_names[$key] : str_replace('_', ' ', $key); ?>
                                        Ledger</label>
                                    <select name="<?php echo strtolower($key); ?>" id="<?php echo strtolower($key); ?>"
                                        class="form-control select2 ledger-select">
                                        <option value="">--- Select Ledger ---</option>
                                    </select>
                                </div>
                            <?php } ?>

                            <div class="col-12 mt-4 mb-2 transfer-group">
                                <h5 id="transfer_header_text"
                                    style="color: var(--primary); border-bottom: 2px solid var(--primary-light); padding-bottom: 5px;">
                                    <i class="fas fa-arrow-up mr-2"></i>Fund Transfer Ledgers
                                </h5>
                            </div>
                            <?php foreach ($transfer_keys as $key) { ?>
                                <div class="col-md-4 mb-3 transfer-group">
                                    <label
                                        class="font-weight-bold"><?php echo isset($friendly_names[$key]) ? $friendly_names[$key] : str_replace('_', ' ', $key); ?>
                                        Ledger</label>
                                    <select name="<?php echo strtolower($key); ?>" id="<?php echo strtolower($key); ?>"
                                        class="form-control select2 ledger-select">
                                        <option value="">--- Select Ledger ---</option>
                                    </select>
                                </div>
                            <?php } ?>

                            <div class="col-12 mt-4 mb-2 receipt-group">
                                <h5 style="color: var(--primary); border-bottom: 2px solid var(--primary-light); padding-bottom: 5px;">
                                    <i class="fas fa-receipt mr-2"></i>Receipt Voucher Ledgers From HO
                                </h5>
                            </div>

                            <?php foreach ($receipt_keys as $key) { ?>
                                <div class="col-md-4 mb-3 receipt-group">
                                    <label class="font-weight-bold">
                                        <?php echo $friendly_names[$key]; ?> Ledger
                                    </label>
                                    <select
                                            name="<?php echo strtolower($key); ?>"
                                            id="<?php echo strtolower($key); ?>"
                                            class="form-control select2 ledger-select">
                                        <option value="">--- Select Ledger ---</option>
                                    </select>
                                </div>
                            <?php } ?>

                            <div id="bill_section_container" class="w-100 row m-0 p-0">
                                <div class="col-12 mt-4 mb-2 bill-group">
                                    <h5
                                        style="color: var(--primary); border-bottom: 2px solid var(--primary-light); padding-bottom: 5px;">
                                        <i class="fas fa-file-invoice mr-2"></i>Add Bill and Taxes Ledgers
                                    </h5>
                                </div>
                                <?php foreach ($bill_keys as $key) { ?>
                                    <div class="col-md-4 mb-3 bill-group">
                                        <label
                                            class="font-weight-bold"><?php echo isset($friendly_names[$key]) ? $friendly_names[$key] : str_replace('BILL_', '', str_replace('_', ' ', $key)); ?>
                                            Ledger</label>
                                        <select name="<?php echo strtolower($key); ?>" id="<?php echo strtolower($key); ?>"
                                            class="form-control select2 ledger-select">
                                            <option value="">--- Select Ledger ---</option>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-primary btn-save">
                            <i class="fas fa-save mr-2"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Pehle sirf unit_id initialize karo
        $('#unit_id').select2({
            theme: 'bootstrap4'
        });

        // Ledger selects baad mein initialize honge (abhi empty hain)
        // Unit change pe
        $('#unit_id').on('change', function () {
            var selected_unit = $('#unit_id').val();
            if (selected_unit == '53') {
                $('.bill-group').hide();
                $('.transfer-group').show();
                $('.receipt-group').hide();
                $('#receive_header_text').html('<i class="fas fa-arrow-down mr-2"></i>Fund Receive at HO Ledgers');
                $('#transfer_header_text').html('<i class="fas fa-arrow-up mr-2"></i>Fund Transfer HO to Unit Ledgers');
            } else {
                $('.bill-group').show();
                $('.transfer-group').hide();
                $('.receipt-group').show();
                $('#receive_header_text').html('<i class="fas fa-arrow-down mr-2"></i>Fund Receive Ledgers');
                $('#transfer_header_text').html('<i class="fas fa-arrow-up mr-2"></i>Fund Transfer Ledgers');
            }
            load_settings(selected_unit);
        });

        // POST ke baad pre-selected unit ho toh load karo
        var preSelected = $('#unit_id').val();
        if (preSelected) {
            if (preSelected == '53') {
                $('.bill-group').hide();
                $('.transfer-group').show();
                $('.receipt-group').hide();
                $('#receive_header_text').html('<i class="fas fa-arrow-down mr-2"></i>Fund Receive at HO Ledgers');
                $('#transfer_header_text').html('<i class="fas fa-arrow-up mr-2"></i>Fund Transfer HO to Unit Ledgers');
            } else {
                $('.bill-group').show();
                $('.transfer-group').hide();
                $('.receipt-group').show();
                $('#receive_header_text').html('<i class="fas fa-arrow-down mr-2"></i>Fund Receive Ledgers From Department');
                $('#transfer_header_text').html('<i class="fas fa-arrow-up mr-2"></i>Fund Transfer Ledgers');
            }
            load_settings(preSelected);
        }

        // Edit button click handler
        $(document).on('click', '.edit-mapping-btn', function (e) {
            e.preventDefault();
            var displayDiv = $(this).closest('.mapped-text-display');
            var parentDiv = displayDiv.parent();
            displayDiv.hide();
            parentDiv.find('.select2-container').show();
        });
    });

    function load_settings(unit_id) {
        if (!unit_id) {
            return;
        }

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

                $('.ledger-select').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    $(this).html(options);
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%'
                    });
                });

                // Ab existing settings load karo
                $.ajax({
                    url: 'scripts/ajax.php?id=get_unit_settings&unit_id=' + unit_id + '&term=all',
                    dataType: 'json',
                    success: function (data) {
                        if (data) {
                            var jsKeys = [
                                'ITTDS', 'CGST', 'SGST', 'CGSTTDS', 'SGSTTDS', 'LABORCESS', 'OTHER_CHARGES',
                                'ADVCENRV', 'CGSTRV', 'SGSTRV', 'CGSTTDSRV', 'SGSTTDSRV', 'ITTDSRV', 'LABOURCESSRV',
                                'ADVCEN', 'CGSTW', 'SGSTW', 'CGSTTDSW', 'SGSTTDSW', 'ITTDSW', 'LABOURCESSW',
                                'BILL_OTHER_ADD', 'BILL_IT', 'BILL_CGST_TDS', 'BILL_SGST_TDS', 'BILL_SECURITY', 'BILL_LABOUR_CESS', 'BILL_OTHER_DED',
                                'BILL_CGST', 'BILL_SGST', 'BILL_DED_CGST', 'BILL_DED_SGST', 'BILL_ROYALTY', 'BILL_HOLD'
                            ];
                            jsKeys.forEach(function (k) {
                                var selectElem = $('#' + k.toLowerCase());
                                var parentDiv = selectElem.parent();

                                if (data[k] && data[k] !== '') {
                                    selectElem.val(data[k]).trigger('change.select2');

                                    var selectedText = selectElem.find("option:selected").text();

                                    // Hide select2 dropdown
                                    selectElem.next('.select2-container').hide();

                                    // Show text display
                                    if (parentDiv.find('.mapped-text-display').length === 0) {
                                        var btnHtml = '<button type="button" class="btn btn-sm btn-link edit-mapping-btn" style="padding:0; color:var(--primary);" title="Change Mapping"><i class="fas fa-edit"></i></button>';

                                        parentDiv.append(
                                            '<div class="mapped-text-display" style="padding: 8px 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #28a745; font-weight: 500; display: flex; justify-content: space-between; align-items: center; margin-top: 2px;">' +
                                            '<span class="text-truncate" title="' + selectedText + '"><i class="fas fa-check-circle text-success mr-2"></i> ' + selectedText + '</span>' +
                                            btnHtml +
                                            '</div>'
                                        );
                                    } else {
                                        parentDiv.find('.mapped-text-display span').html('<i class="fas fa-check-circle text-success mr-2"></i> ' + selectedText);
                                        parentDiv.find('.mapped-text-display').show();
                                    }
                                } else {
                                    selectElem.val('').trigger('change.select2');
                                    selectElem.next('.select2-container').show();
                                    parentDiv.find('.mapped-text-display').hide();
                                }
                            });

                            // Check completion based on split groups
                            var isMapped = function (key) {
                                return (data[key] !== undefined && data[key] !== null && data[key] !== '');
                            };

                            var receive_complete = ((isMapped('CGSTTDS') && isMapped('SGSTTDS'))) &&
                                isMapped('ITTDS') && isMapped('LABORCESS') && isMapped('OTHER_CHARGES');

                            var transfer_complete = ((isMapped('CGSTTDSW') && isMapped('SGSTTDSW'))) &&
                                isMapped('ITTDSW') && isMapped('LABOURCESSW') && isMapped('ADVCEN');

                            var bill_complete = ((isMapped('BILL_CGST_TDS') && isMapped('BILL_SGST_TDS'))) &&
                                isMapped('BILL_IT') && isMapped('BILL_LABOUR_CESS') && isMapped('BILL_SECURITY') &&
                                isMapped('BILL_OTHER_ADD') && isMapped('BILL_OTHER_DED') &&
                                ((isMapped('BILL_CGST') && isMapped('BILL_SGST'))) &&
                                ((isMapped('BILL_DED_CGST') && isMapped('BILL_DED_SGST'))) &&
                                isMapped('BILL_ROYALTY') && isMapped('BILL_HOLD');

                            var receipt_complete =
                                (isMapped('CGSTTDSRV') && isMapped('SGSTTDSRV')) &&
                                isMapped('ITTDSRV') &&
                                isMapped('LABOURCESSRV') &&
                                isMapped('ADVCENRV');

                            if (unit_id == '53') {
                                bill_complete = true;
                                receipt_complete = true;
                            } else {
                                transfer_complete = true;
                            }

                            if (receive_complete && transfer_complete && bill_complete && receipt_complete) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Mapping Completed!',
                                    text: 'All ledgers for this unit have been mapped successfully. Redirecting...',
                                    showConfirmButton: false,
                                    timer: 2500
                                }).then(function () {

                                });
                            }
                        }
                    }
                });
            },
            error: function (e) {
                console.log('ledger load error:', e);
            }
        });
    }
</script>

<?php
page_footer_start();
page_footer_end();
?>