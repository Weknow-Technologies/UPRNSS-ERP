<?php
include('scripts/settings.php');

$msg = '';
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $msg = '<div class="alert alert-success">Inspection report submitted successfully!</div>';
}

/* ================= FILTER VALUES ================= */
$f_department  = $_POST['department_id'] ?? '';
$f_division    = $_POST['division_id'] ?? '';
$f_district    = $_POST['district_id'] ?? '';
$f_designation = $_POST['officer_designation'] ?? '';
$f_erp         = $_POST['erp_code'] ?? '';

page_header_start();
page_header_end();
page_sidebar();

/* ================= DROPDOWNS ================= */
$departments = execute_query("
    SELECT sno, department_name_hindi 
    FROM uprnss_department_name 
    ORDER BY department_name_hindi
");

$divisions = execute_query("
    SELECT s_no, division_name 
    FROM uprnss_division 
    ORDER BY division_name
");

$districts = execute_query("
    SELECT sno, district_name_hindi 
    FROM uprnss_district 
    ORDER BY district_name_hindi
");

/* ================= WHERE ================= */
$where = " WHERE 1=1 ";

if ($f_department !== '') {
    $where .= " AND pt.department_id = '".intval($f_department)."'";
}
if ($f_division !== '') {
    $where .= " AND pt.division_id = '".intval($f_division)."'";
}
if ($f_district !== '') {
    $where .= " AND pt.district_id = '".intval($f_district)."'";
}
if ($f_designation !== '') {
    $where .= " AND r.officer_designation = '".mysqli_real_escape_string($db,$f_designation)."'";
}
if ($f_erp !== '') {
    $where .= " AND pt.erp_code LIKE '%".mysqli_real_escape_string($db,$f_erp)."%'";
}

/* ================= MAIN QUERY ================= */
$sql = "
SELECT 
    r.*,
    pt.project_name_hindi,
    pt.erp_code,
    d.department_name_hindi,
    dist.district_name_hindi,
    divi.division_name,
    GROUP_CONCAT(i.file_path SEPARATOR '|') AS image_paths,
    GROUP_CONCAT(IFNULL(i.caption,'') SEPARATOR '|') AS image_captions
FROM inspection_reports r
LEFT JOIN uprnss_project_temp pt ON pt.sno = r.project_name
LEFT JOIN uprnss_department_name d ON d.sno = pt.department_id
LEFT JOIN uprnss_division divi ON divi.s_no = pt.division_id
LEFT JOIN uprnss_district dist ON dist.sno = pt.district_id
LEFT JOIN inspection_images i ON r.id = i.report_id
$where
GROUP BY r.id
ORDER BY r.created_at DESC
";

$res = execute_query($sql);

function h($v){
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="row">
<div class="col-md-12">

<!-- ================= FILTER CARD ================= -->
<div class="card mb-3">
<div class="card-header bg-danger text-white">
<strong>Inspection Report </strong>
</div>
<div class="card-body">
<form method="post" class="row">

<div class="col-md-3">
<label>Department</label>
<select name="department_id" class="form-control">
<option value="">-- All --</option>
<?php while($d=mysqli_fetch_assoc($departments)){ ?>
<option value="<?php echo $d['sno']; ?>" <?php if($f_department==$d['sno']) echo 'selected'; ?>>
<?php echo h($d['department_name_hindi']); ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-3">
<label>Division</label>
<select name="division_id" class="form-control">
<option value="">-- All --</option>
<?php while($dv=mysqli_fetch_assoc($divisions)){ ?>
<option value="<?php echo $dv['s_no']; ?>" <?php if($f_division==$dv['s_no']) echo 'selected'; ?>>
<?php echo h($dv['division_name']); ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-3">
<label>District</label>
<select name="district_id" class="form-control">
<option value="">-- All --</option>
<?php while($di=mysqli_fetch_assoc($districts)){ ?>
<option value="<?php echo $di['sno']; ?>" <?php if($f_district==$di['sno']) echo 'selected'; ?>>
<?php echo h($di['district_name_hindi']); ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-3">
<label>Officer Designation</label>
<select name="officer_designation" class="form-control">
<option value="">All</option>
<?php
$opts = ['Assistant Engineer','Executive Engineer','Superintending Engineer','Chief Engineer','Senior Officers'];
foreach ($opts as $opt){
    echo '<option value="'.$opt.'" '.($f_designation==$opt?'selected':'').'>'.$opt.'</option>';
}
?>
</select>
</div>

<div class="col-md-3 mt-2">
<label>ERP Code</label>
<input type="text" name="erp_code" class="form-control" value="<?php echo h($f_erp); ?>">
</div>

<div class="col-md-12 mt-3">
<button type="submit" class="btn btn-primary">
<i class="fa fa-search"></i> Apply Filter
</button>
<a href="inspection_report.php" class="btn btn-secondary">Reset</a>
</div>

</form>
</div>
</div>

<!-- ================= REPORT CARD ================= -->
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<h4 class="card-title mb-0">Inspection Reports</h4>
<a class="btn btn-primary btn-sm" href="inspection.php">
<i class="fa fa-plus"></i> New Report
</a>
</div>

<div class="card-body">
<?php echo $msg; ?>

<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead>
<tr>
<th>Department</th>
<th>Division</th>
<th>District</th>
<th>ERP Code</th>
<th>Project Name</th>
<th>Inspection Date</th>
<th>Officer</th>
<th>Summary</th>
<th>PDF</th>
<th>Images</th>
</tr>
</thead>
<tbody>

<?php
if ($res && mysqli_num_rows($res) > 0) {
while ($r = mysqli_fetch_assoc($res)) {

$paths = $r['image_paths'] ?: '';
$caps  = $r['image_captions'] ?: '';
$imgCount = $paths ? count(explode('|',$paths)) : 0;

echo '<tr data-images="'.h($paths).'" data-captions="'.h($caps).'">';
echo '<td>'.h($r['department_name_hindi']).'</td>';
echo '<td>'.h($r['division_name']).'</td>';
echo '<td>'.h($r['district_name_hindi']).'</td>';
echo '<td>'.h($r['erp_code']).'</td>';
echo '<td>'.h($r['project_name_hindi']).'</td>';
echo '<td>'.h($r['inspection_date']).'</td>';
echo '<td>'.h($r['officer_designation']).'</td>';
echo '<td>'.h(mb_strimwidth($r['inspection_summary'],0,80,'...')).'</td>';

echo '<td>';
echo $r['pdf_file_path']
    ? '<a class="btn btn-sm btn-outline-primary" href="'.h($r['pdf_file_path']).'" target="_blank">Download</a>'
    : '<span class="text-muted">—</span>';
echo '</td>';

echo '<td>';
echo $imgCount
    ? '<button class="btn btn-sm btn-info" onclick="openGallery(this)">View ('.$imgCount.')</button>'
    : '<span class="text-muted">—</span>';
echo '</td>';

echo '</tr>';
}
} else {
echo '<tr><td colspan="9" class="text-center text-muted">No record found</td></tr>';
}
?>

</tbody>
</table>
</div>
</div>
</div>

</div>
</div>

<!-- ================= GALLERY MODAL ================= -->
<div class="modal fade" id="galleryModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Inspection Photos</h5>
<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
<div id="galleryGrid" class="row"></div>
</div>
</div>
</div>
</div>

<?php page_footer_start(); ?>
<script>
function openGallery(btn){
    const tr = btn.closest('tr');
    const images = (tr.dataset.images||'').split('|').filter(Boolean);
    const captions = (tr.dataset.captions||'').split('|');
    const grid = document.getElementById('galleryGrid');
    grid.innerHTML = '';

    images.forEach((src,i)=>{
        grid.innerHTML += `
        <div class="col-md-4 mb-3">
            <div class="card">
                <img src="${src}" class="card-img-top">
                <div class="card-body p-2">
                    <small class="text-muted">${captions[i]||'—'}</small>
                </div>
                <div class="card-footer p-2 text-right">
                    <a href="${src}" download class="btn btn-sm btn-outline-primary">Download</a>
                </div>
            </div>
        </div>`;
    });

    $('#galleryModal').modal('show');
}
</script>
<?php page_footer_end(); ?>
