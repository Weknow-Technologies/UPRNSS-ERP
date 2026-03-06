<?php
include("scripts/settings.php");

$msg = '';

/* =====================================================
   HANDLE FILTERS (POST BASED)
===================================================== */
$f_department = $_POST['department_id'] ?? '';
$f_division   = $_POST['division_id'] ?? '';
$f_district   = $_POST['district_id'] ?? '';
$f_erp        = $_POST['erp_code'] ?? '';

/* =====================================================
   SECURE PDF DOWNLOAD (ONLY FILE USES GET)
===================================================== */
if(isset($_GET['download']) && !empty($_GET['file'])){
    $allowedDir = realpath(__DIR__.'/materials_report');
  $fullPath = realpath(__DIR__ . '/' . $_GET['file']);



    if($fullPath && file_exists($fullPath) && strpos($fullPath,$allowedDir) === 0){
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.basename($fullPath).'"');
        header('Content-Length: '.filesize($fullPath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($fullPath);
        exit;
    }
    echo "File not found or access denied";
    exit;
}

/* =====================================================
   PAGE HEADER
===================================================== */
page_header_start();
page_header_end();
page_sidebar();

/* =====================================================
   FILTER MASTER DATA
===================================================== */
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

/* =====================================================
   BUILD WHERE CLAUSE (USING pt)
===================================================== */
$where = "WHERE 1=1";

if($f_department !== ''){
    $where .= " AND pt.department_id = '".mysqli_real_escape_string($db,$f_department)."'";
}
if($f_division !== ''){
    $where .= " AND pt.division_id = '".mysqli_real_escape_string($db,$f_division)."'";
}
if($f_district !== ''){
    $where .= " AND pt.district_id = '".mysqli_real_escape_string($db,$f_district)."'";
}
if($f_erp !== ''){
    $where .= " AND pt.erp_code LIKE '%".mysqli_real_escape_string($db,$f_erp)."%'";
}

/* =====================================================
   MAIN REPORT QUERY (pt BASED MAPPING)
===================================================== */
$sql = "
SELECT 
    mt.sno AS sno,

    pt.erp_code,
    pt.project_name_hindi AS project_name,

    dept.department_name_hindi,
    divs.division_name,
    dist.district_name_hindi,

    mt.material_type,
    mt.test_name,
    mt.sample_date,
    mt.test_date,
    mt.testing_lab,
    mt.report_status,
    mt.report_pdf_path,

    mt.created_by,
    mt.creation_time

FROM materials_testing mt

INNER JOIN uprnss_project_temp pt 
        ON pt.sno = mt.project_name

LEFT JOIN uprnss_department_name dept 
       ON dept.sno = pt.department_id

LEFT JOIN uprnss_division divs 
       ON divs.s_no = pt.division_id

LEFT JOIN uprnss_district dist 
       ON dist.sno = pt.district_id

$where
ORDER BY mt.creation_time DESC
";

$res = execute_query($sql);

function h($v){
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="row">
<div class="col-md-12">

<!-- ================= FILTER FORM ================= -->
<div class="card mb-3">
<div class="card-header bg-danger text-white">
    <strong>Material Testing – Reports</strong>
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
<label>ERP Code</label>
<input type="text" name="erp_code" class="form-control"
       value="<?php echo h($f_erp); ?>" placeholder="ERP Code">
</div>

<div class="col-md-12 mt-3">
<button type="submit" class="btn btn-primary">Apply Filter</button>
<a href="materials_report.php" class="btn btn-secondary">Reset</a>
</div>

</form>
</div>
</div>

<!-- ================= REPORT TABLE ================= -->
<div class="card">


<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered table-striped" id="materials_report_table">
<thead>
<tr>
<th>#</th>
<th>Department</th>
<th>Division</th>
<th>District</th>
<th>ERP Code</th>
<th>Project</th>

<th>Material</th>
<th>Test Name</th>
<th>Sample Date</th>
<th>Test Date</th>
<th>Testing Lab</th>
<th>Report Summary</th>
<th>Material Report PDF</th>
</tr>
</thead>
<tbody>
<?php $i=1; while($row=mysqli_fetch_assoc($res)){ ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo h($row['department_name_hindi']); ?></td>
<td><?php echo h($row['division_name']); ?></td>
<td><?php echo h($row['district_name_hindi']); ?></td>
<td><?php echo h($row['erp_code']); ?></td>
<td><?php echo h($row['project_name']); ?></td>
<td><?php echo h($row['material_type']); ?></td>
<td><?php echo h($row['test_name']); ?></td>
<td><?php echo h($row['sample_date']); ?></td>
<td><?php echo h($row['test_date']); ?></td>
<td><?php echo h($row['testing_lab']); ?></td>
<td><?php echo h($row['report_status']); ?></td>
<td>
<?php if($row['report_pdf_path']){ ?>
<a class="btn btn-sm btn-success"
   href="?download=1&file=<?php echo urlencode($row['report_pdf_path']); ?>">
   Download
</a>
<?php } else echo '-'; ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

</div>
</div>

<?php page_footer_start(); ?>
<script>
if($.fn.DataTable){
    $('#materials_report_table').DataTable({
        pageLength: 10,
        order: [[0,'desc']]
    });
}
</script>
<?php page_footer_end(); ?>
