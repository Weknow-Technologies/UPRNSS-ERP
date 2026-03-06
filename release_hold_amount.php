<?php
include("scripts/settings.php");
include("scripts/approval_system_functions.php");
$module_name= "invoice_deduction_release";
$msg = '';

// ===== AJAX: return projects for a vendor (single-file endpoint) =====
if (isset($_GET['ajax']) && $_GET['ajax'] === 'projects') {
    header('Content-Type: application/json; charset=utf-8');
    $vendor_id = intval($_GET['vendor_id'] ?? 0);
    $divisions = array_map('intval', $_SESSION['divisions'] ?? []);
    $division_ids = $divisions ? implode(',', $divisions) : '0';

    // Only show projects awarded to this vendor (and having at least one notesheet-generated bill)
    $q = "
        SELECT DISTINCT 
            ta.project_id,
            COALESCE(upt.project_name_hindi, CONCAT('Project #', ta.project_id)) AS project_label
        FROM tender_allotment ta
        JOIN uprnss_project_temp upt ON upt.sno = ta.project_id
        JOIN invoice_account_fund_transafer iat ON iat.project_name = ta.project_id AND iat.genrate_note_sheet = 1
        WHERE ta.project_awarded_to = ?
          AND ta.division_id IN ($division_ids)
        ORDER BY project_label
    ";
    $stmt = $db->prepare($q);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = [
            'project_id'    => (int)$r['project_id'],
            'project_label' => $r['project_label']
        ];
    }
    echo json_encode($out);
    exit;
}

// ===== Handle release submit (POST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['release_submit'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $type = 'ho_to_unit';
    $deduction_type = $_POST['deduction_type'];
    $bill_ids = $_POST['bill_ids'] ?? [];
    $release_amounts = $_POST['release_amounts'] ?? [];
    $created_by = $_SESSION['usersno'];

    // Header
	   $head_stmt = $db->prepare("INSERT INTO invoice_deduction_release 
		(vendor_id, type, deduction_type, release_date, created_by) VALUES (?, ?, ?, CURDATE(), ?)");
	$head_stmt->bind_param("issi", $vendor_id, $type, $deduction_type, $created_by);
    $head_stmt->execute();
    $head_id = $db->insert_id;

    // Transactions
    foreach ($bill_ids as $bill_id) {
        $amount = (float)($release_amounts[$bill_id] ?? 0);
        if ($amount > 0) {
            $get_sql = "SELECT $deduction_type AS held_amt, project_name FROM invoice_account_fund_transafer WHERE sno = ".intval($bill_id);
            $get_res = $db->query($get_sql);
            if ($get_res && $get_res->num_rows > 0) {
                $data = $get_res->fetch_assoc();
                $held = (float)$data['held_amt'];
                $project_id = (int)$data['project_name'];

                // IMPORTANT: store deduction_type too (so the preview JOIN matches)
                $trans_stmt = $db->prepare("
                    INSERT INTO deduction_release (invoice_id, project_id, bill_id, deduction_type, held_amount, released_amount)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $trans_stmt->bind_param("iiisdd", $head_id, $project_id, $bill_id, $deduction_type, $held, $amount);
                $trans_stmt->execute();
            }
        }
    }
	createApprovalRequest($module_name, $head_id);

    echo "<script>alert('Deductions released successfully.'); window.location='release_amount_report.php';</script>";
}

// ===== UI =====
page_header_start();
page_header_end();
page_sidebar();
?>

<div class="card">
    <div class="card-header text-center">
        <h4>Release Hold Deductions</h4>
    </div>
    <div class="card-body">
        <form method="get" action="">
            <div class="row">
                <!-- Contractor -->
                <div class="col-md-4">
                    <label>Vendor / Contractor</label>
                    <select name="vendor_id" id="vendor_id" class="form-control" required>
                        <option value="">-- Select Vendor --</option>
                        <?php
                        $divisions = array_map('intval', $_SESSION['divisions']);
                        $division_ids = $divisions ? implode(',', $divisions) : '0';

                        $vendor_sql = "
                            SELECT DISTINCT 
                                v.sno AS vendor_id,
                                v.firm_name,
                                v.contractor_name,
                                v.contractor_code
                            FROM vendor v
                            JOIN tender_allotment ta ON ta.project_awarded_to = v.sno
                            JOIN invoice_account_fund_transafer iat ON iat.project_name = ta.project_id
                            WHERE ta.division_id IN ($division_ids)
                              AND iat.genrate_note_sheet = 1
                            ORDER BY v.contractor_name, v.firm_name
                        ";

                        $vres = $db->query($vendor_sql);
                        $vendor_id_sel = intval($_GET['vendor_id'] ?? 0);
                        while ($row = $vres->fetch_assoc()) {
                            $sel = $vendor_id_sel === (int)$row['vendor_id'] ? 'selected' : '';
                            $cn = htmlspecialchars($row['contractor_name']);
                            $fn = htmlspecialchars($row['firm_name']);
                            echo "<option value='{$row['vendor_id']}' $sel>{$cn} ({$fn})</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Project (populates when vendor selected) -->
                <div class="col-md-4">
                    <label>Project</label>
                    <select name="project_id" id="project_id" class="form-control" <?= $vendor_id_sel ? '' : 'disabled' ?> required>
                        <option value="">-- Select Project --</option>
                        <?php
                        // Pre-populate if vendor already chosen (e.g., after submit)
                        $project_id_sel = intval($_GET['project_id'] ?? 0);
                        if ($vendor_id_sel) {
                            $q = "
                                SELECT DISTINCT 
                                    ta.project_id,
                                    COALESCE(upt.project_name_hindi, CONCAT('Project #', ta.project_id)) AS project_label
                                FROM tender_allotment ta
                                JOIN uprnss_project_temp upt ON upt.sno = ta.project_id
                                JOIN invoice_account_fund_transafer iat ON iat.project_name = ta.project_id AND iat.genrate_note_sheet = 1
                                WHERE ta.project_awarded_to = ?
                                  AND ta.division_id IN ($division_ids)
                                ORDER BY project_label
                            ";
                            $stmt = $db->prepare($q);
                            $stmt->bind_param("i", $vendor_id_sel);
                            $stmt->execute();
                            $pres = $stmt->get_result();
                            while ($p = $pres->fetch_assoc()) {
                                $pid = (int)$p['project_id'];
                                $plabel = htmlspecialchars($p['project_label']);
                                $sel = $project_id_sel === $pid ? 'selected' : '';
                                echo "<option value='{$pid}' {$sel}>{$plabel}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Deduction Head -->
                <div class="col-md-4">
                    <label>Deduction Type</label>
                    <select name="deduction_type" id="deduction_type" class="form-control" required>
                        <option value="">-- Select Deduction --</option>
                        <?php
                        $heads = [
                            'gstdeduction' => 'GST',
                            'security'     => 'Security',
                            'royalty'      => 'Royalty',
                            'gsttds'       => 'GST TDS',
                            'sentage'      => 'Sentage',
                            'leborses'     => 'LeborCess',
                            'contingency'  => 'Contingency'
                        ];
                        $dtype_sel = $_GET['deduction_type'] ?? '';
                        foreach ($heads as $key => $label) {
                            $selected = $dtype_sel === $key ? 'selected' : '';
                            echo "<option value='$key' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-12 pt-3 text-end">
                    <button type="submit" class="btn btn-primary">Preview Deductions</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// ===== Preview table =====
if (isset($_GET['vendor_id'], $_GET['deduction_type']) && (int)$_GET['vendor_id'] > 0 && !empty($_GET['deduction_type'])) {
    $vendor_id = intval($_GET['vendor_id']);
    $deduction_type = $_GET['deduction_type'];
    $project_id = intval($_GET['project_id'] ?? 0);
    $head_label = $heads[$deduction_type] ?? strtoupper($deduction_type);

    $projectFilter = $project_id ? " AND iat.project_name = {$project_id} " : '';

    $sql = "
        SELECT 
            iat.sno as bill_id, 
            iat.project_name, 
            upt.project_name_hindi, 
            iat.bill_no, 
            iat.bill_date, 
            iat.$deduction_type AS deducted_amount,
            IFNULL(SUM(dr.released_amount), 0) AS total_released
        FROM invoice_account_fund_transafer iat
        JOIN tender_allotment ta ON ta.project_id = iat.project_name
        JOIN uprnss_project_temp upt ON upt.sno = iat.project_name
        LEFT JOIN deduction_release dr 
               ON dr.bill_id = iat.sno 
              AND dr.deduction_type = '$deduction_type'
        WHERE ta.project_awarded_to = $vendor_id 
          AND iat.genrate_note_sheet = 1 
          AND iat.$deduction_type > 0
          $projectFilter
        GROUP BY iat.sno
        ORDER BY iat.bill_date, iat.sno
    ";

    $res = $db->query($sql);
    if ($res && $res->num_rows > 0) {
?>
<div class="card mt-4">
    <div class="card-header text-center">
        <h5><?= htmlspecialchars($head_label) ?> - Hold Amount Release </h5>
    </div>
    <div class="card-body">
        <form method="post" action="release_hold_amount.php">
            <input type="hidden" name="vendor_id" value="<?= $vendor_id ?>">
            <input type="hidden" name="deduction_type" value="<?= htmlspecialchars($deduction_type) ?>">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Project</th>
                        <th>Bill No</th>
                        <th>Bill Date</th>
                        <th><?= htmlspecialchars($head_label) ?> Hold Amount</th>
                        <th><?= htmlspecialchars($head_label) ?> Amount Released</th>
                        <th><?= htmlspecialchars($head_label) ?> Available to Release</th>
                        <th><?= htmlspecialchars($head_label) ?> Release Now</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()) { 
                        $available = (float)$row['deducted_amount'] - (float)$row['total_released'];
                        if ($available <= 0) continue;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['project_name_hindi']) ?></td>
                            <td><?= htmlspecialchars($row['bill_no']) ?></td>
                            <td><?= htmlspecialchars($row['bill_date']) ?></td>
                            <td>₹ <?= number_format((float)$row['deducted_amount'], 2) ?></td>
                            <td>₹ <?= number_format((float)$row['total_released'], 2) ?></td>
                            <td>₹ <?= number_format($available, 2) ?></td>
                            <td>
                                <input type="hidden" name="bill_ids[]" value="<?= (int)$row['bill_id'] ?>">
                                <input type="number" name="release_amounts[<?= (int)$row['bill_id'] ?>]" 
                                       step="0.01" max="<?= $available ?>" class="form-control release-input">
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="text-center">
                <button type="submit" name="release_submit" class="btn btn-success">Release Selected Deductions</button>
            </div>
        </form>
    </div>
</div>
<?php 
    } else {
        echo "<div class='alert alert-warning mt-3'>No deductions found for the selected filters.</div>";
    }
}

page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
// Limit inputs to [0, max]
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.release-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const max = parseFloat(input.getAttribute('max'));
            const val = parseFloat(input.value || 0);
            if (val > max) input.value = max;
            if (val < 0) input.value = 0;
        });
    });

    // Load projects when vendor changes (AJAX)
    const vendorSel = document.getElementById('vendor_id');
    const projectSel = document.getElementById('project_id');

    vendorSel?.addEventListener('change', async function () {
        projectSel.innerHTML = '<option value="">Loading...</option>';
        projectSel.disabled = true;
        const vid = vendorSel.value;
        if (!vid) {
            projectSel.innerHTML = '<option value="">-- Select Project --</option>';
            return;
        }
        try {
            const resp = await fetch(`release_hold_amount.php?ajax=projects&vendor_id=${encodeURIComponent(vid)}`);
            const data = await resp.json();
            projectSel.innerHTML = '<option value="">-- Select Project --</option>';
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.project_id;
                opt.textContent = p.project_label;
                projectSel.appendChild(opt);
            });
            projectSel.disabled = false;
        } catch (e) {
            projectSel.innerHTML = '<option value="">(Failed to load projects)</option>';
        }
    });
});
</script>
<?php
page_footer_end();
