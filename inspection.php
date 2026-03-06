<?php
include('scripts/settings.php');

$msg = '';
$tab = 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    $uploaded_images = [];
    $uploaded_pdf = [];
    $upload_success = true;

    // ===== PDF upload =====
    if (isset($_FILES['inspection_pdf']) && $_FILES['inspection_pdf']['error'] === UPLOAD_ERR_OK) {
        $pdf_upload_dir = 'inspection_pdfs/';
        if (!is_dir($pdf_upload_dir)) { @mkdir($pdf_upload_dir, 0777, true); }

        $pdf_name = $_FILES['inspection_pdf']['name'];
        $pdf_tmp  = $_FILES['inspection_pdf']['tmp_name'];
        $pdf_size = (int)$_FILES['inspection_pdf']['size'];

        $ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf'];
        $mime = function_exists('mime_content_type') ? mime_content_type($pdf_tmp) : 'application/pdf';
        $allowed_mime = ['application/pdf'];

        if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
            $msg .= '<div class="alert alert-danger">Invalid PDF type. Only .pdf allowed.</div>';
            $upload_success = false;
        } elseif ($pdf_size > 10 * 1024 * 1024) { // 10MB
            $msg .= '<div class="alert alert-danger">PDF too large. Max 10MB.</div>';
            $upload_success = false;
        } else {
            $pdf_unique = uniqid().'_'.time().'.'.$ext;
            $pdf_path = $pdf_upload_dir.$pdf_unique;
            if (move_uploaded_file($pdf_tmp, $pdf_path)) {
                $uploaded_pdf = [
                    'filename' => $pdf_unique,
                    'original_name' => $pdf_name,
                    'path' => $pdf_path,
                ];
            } else {
                $msg .= '<div class="alert alert-danger">Failed to save PDF.</div>';
                $upload_success = false;
            }
        }
    } else {
        $msg .= '<div class="alert alert-danger">Please upload a PDF report file.</div>';
        $upload_success = false;
    }

    // ===== Images upload =====
    if ($upload_success && isset($_FILES['inspection_photos']) && !empty($_FILES['inspection_photos']['name'][0])) {
        $img_dir = 'inspection_images/';
        if (!is_dir($img_dir)) { @mkdir($img_dir, 0777, true); }

        $names  = $_FILES['inspection_photos']['name'];
        $tmps   = $_FILES['inspection_photos']['tmp_name'];
        $sizes  = $_FILES['inspection_photos']['size'];
        $errors = $_FILES['inspection_photos']['error'];

        $count = count($names);
        if ($count > 5) {
            $msg .= '<div class="alert alert-danger">Maximum 5 images allowed.</div>';
            $upload_success = false;
        }

        $allowed_img_ext = ['jpg','jpeg','png','gif'];
        for ($i = 0; $i < $count && $upload_success; $i++) {
            if ($errors[$i] === UPLOAD_ERR_OK) {
                $name = $names[$i];
                $tmp  = $tmps[$i];
                $size = (int)$sizes[$i];
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_img_ext)) {
                    $msg .= '<div class="alert alert-danger">Invalid image type for '.htmlspecialchars($name).'.</div>';
                    $upload_success = false; break;
                }
                if ($size > 5 * 1024 * 1024) { // 5MB
                    $msg .= '<div class="alert alert-danger">Image '.htmlspecialchars($name).' exceeds 5MB.</div>';
                    $upload_success = false; break;
                }

                $unique = uniqid().'_'.time().'.'.$ext;
                $dest   = $img_dir.$unique;
                if (move_uploaded_file($tmp, $dest)) {
                    $uploaded_images[] = [
                        'filename' => $unique,
                        'original_name' => $name,
                        'path' => $dest,
                        'caption' => '', // captions optional
                    ];
                } else {
                    $msg .= '<div class="alert alert-danger">Failed to save image '.htmlspecialchars($name).'.</div>';
                    $upload_success = false; break;
                }
            }
        }
    } else if ($upload_success) {
        $msg .= '<div class="alert alert-danger">Please select at least one image.</div>';
        $upload_success = false;
    }

    // ===== DB insert =====
    if ($upload_success) {
        // Check tables
        $has_reports = mysqli_num_rows(mysqli_query($db, "SHOW TABLES LIKE 'inspection_reports'")) > 0;
        $has_images  = mysqli_num_rows(mysqli_query($db, "SHOW TABLES LIKE 'inspection_images'")) > 0;
        if (!$has_reports) {
            $msg .= '<div class="alert alert-danger">Table `inspection_reports` missing. Run schema.</div>';
            $upload_success = false;
        }
        if ($upload_success) {
            $sql = 'INSERT INTO inspection_reports (
                        department, district, project_name, inspection_date,
                        officer_designation, inspection_summary,
                        pdf_filename, pdf_original_name, pdf_file_path, pdf_uploaded_at,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
            $stmt = mysqli_prepare($db, $sql);
            $pdf_uploaded_at = date('Y-m-d H:i:s');
            mysqli_stmt_bind_param(
                $stmt, 'ssssssssss',
                $_POST['department'], $_POST['district'], $_POST['project_name'],
                $_POST['inspection_date'], $_POST['officer_designation'], $_POST['inspection_report_summary'],
                $uploaded_pdf['filename'], $uploaded_pdf['original_name'], $uploaded_pdf['path'], $pdf_uploaded_at
            );
            if (mysqli_stmt_execute($stmt)) {
                $report_id = (int)mysqli_insert_id($db);
                mysqli_stmt_close($stmt);

                if ($has_images) {
                    $img_sql = 'INSERT INTO inspection_images (report_id, filename, original_name, file_path, caption, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())';
                    foreach ($uploaded_images as $img) {
                        $is = mysqli_prepare($db, $img_sql);
                        mysqli_stmt_bind_param($is, 'issss', $report_id, $img['filename'], $img['original_name'], $img['path'], $img['caption']);
                        mysqli_stmt_execute($is);
                        mysqli_stmt_close($is);
                    }
                }

                // Redirect to the separate reports page on success
                header('Location: inspection_report.php?success=1');
                exit;
            } else {
                $msg .= '<div class="alert alert-danger">DB insert failed: '.htmlspecialchars(mysqli_error($db)).'</div>';
            }
        }
    } else {
        $msg .= '<div class="alert alert-warning">Please fix the upload errors above.</div>';
    }
}

// Avoid undefined index notices
foreach ([
    'division_id','department','sub_department_id','district','project_name','location_place','state_name',
    'project_type','estimated_cost','funding_source','funding_agency','approved_amount','released_amount',
    'inspection_report_title','inspection_date','officer_designation','inspection_report_summary','summary_officer_designation'
] as $f) { if (!isset($_POST[$f])) { $_POST[$f] = ''; } }

page_header_start();
// CKEditor (keep your version if preferred)
?>
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<?php
page_header_end();
page_sidebar();
?>

<style>
.preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.preview-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center; }
.preview-card img { max-width: 100%; height: 120px; object-fit: cover; border-radius: 6px; }
.small-muted { font-size: 12px; color: #6b7280; }
.invalid-feedback { display: block; }
</style>

<form id="sale_form" name="sale_form" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return validateForm()">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
			<h4 class="card-title mb-0 text-center w-100 text-white">Inspection Details</h4>
			<a href="inspection_report.php" class="btn btn-outline-primary ms-3" style="white-space: nowrap;">View Reports</a>
		</div>

        <div class="card-body">
          <?php echo $msg; ?>

          <h4 class="card-title">परियोजना का विवरण</h4>
          <div class="row">
            <input type="hidden" name="unit_name" id="unit_name" value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
            <input type="hidden" name="division_id" id="division_id" value="<?php echo $_POST['division_id']; ?>" readonly tabindex="<?php echo $tab++; ?>">

            <div class="col-md-3">
              <div class="form-group">
                <label>विभाग <span class="text-danger">*</span></label>
                <select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value)" required>
                  <option value="">--- Select ---</option>
                  <?php
                  if(!empty($_SESSION['department'])){
                    $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_department_name.sno = department_id where department_id in ('.implode(", ", $_SESSION['department']).') group by department_id) ';
                  } elseif(!empty($_SESSION['divisions'])){
                    $query = '(SELECT uprnss_department_name.sno as sno, uprnss_department_name.department_name_hindi FROM `uprnss_project_temp` left join uprnss_department_name on uprnss_project_temp.division_id in ('.implode(", ", $_SESSION['divisions']).') group by uprnss_project_temp.division_id ) ';
                  }
                  $run = mysqli_query($db,$query);
                  while($data = mysqli_fetch_array($run)){
                    echo '<option value="'.$data['sno'].'" '.(($_POST['department']==$data['sno'])?'selected':'').'>'.trim($data['department_name_hindi']).'</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>आच्छादित जनपद <span class="text-danger">*</span></label>
                <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)" required>
                  <option value="">--- Select ---</option>
                </select>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>परियोजना का नाम <span class="text-danger">*</span></label>
                <select class="form-control" name="project_name" id="project_name" tabindex="<?php echo $tab++; ?>" required>
                  <option value="">--- Select ---</option>
                </select>
                <small class="form-text text-muted">Select district first to load projects</small>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>निरीक्षण रिपोर्ट PDF <span class="text-danger">*</span></label>
                <input type="file" name="inspection_pdf" id="inspection_pdf" class="form-control" accept=".pdf" tabindex="<?php echo $tab++; ?>" required>
                <small id="pdfMeta" class="small-muted"></small>
                <div id="pdfError" class="invalid-feedback"></div>
              </div>
            </div>
          </div>

          <h4 class="card-title">निरीक्षण विवरण</h4>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>निरीक्षण की तिथि <span class="text-danger">*</span></label>
                <input type="date" name="inspection_date" id="inspection_date" class="form-control" value="<?php echo $_POST['inspection_date']; ?>" tabindex="<?php echo $tab++; ?>" required>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>अधिकारी का पदनाम <span class="text-danger">*</span></label>
                <select class="form-control" name="officer_designation" id="officer_designation" tabindex="<?php echo $tab++; ?>" required>
                  <option value="">--- Select ---</option>
                  <?php $opts = ['Chief Engineer','Superintending Engineer','Executive Engineer','Assistant Engineer','Junior Engineer','Other Officers'];
                  foreach ($opts as $opt) {
                    $sel = ($_POST['officer_designation']===$opt)?'selected':'';
                    echo '<option '.$sel.' value="'.$opt.'">'.$opt.'</option>';
                  } ?>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>फोटो चुनें (एक से अधिक) <span class="text-danger">*</span></label>
                <input type="file" name="inspection_photos[]" id="inspection_photos" class="form-control" accept=".jpg,.jpeg,.png,.gif" multiple tabindex="<?php echo $tab++; ?>" required>
                <small class="form-text text-muted">Maximum 5 files, each up to 5MB. Supported formats: JPG, JPEG, PNG, GIF</small>
                <div id="imagesError" class="invalid-feedback"></div>
              </div>
            </div>

            <div class="col-md-12">
              <div id="imagePreviews" class="preview-grid"></div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>निरीक्षण रिपोर्ट सारांश <span class="text-danger">*</span></label>
                <textarea name="inspection_report_summary" id="inspection_report_summary" class="form-control" rows="6" placeholder="निरीक्षण निष्कर्षों का विस्तृत विवरण लिखें" tabindex="<?php echo $tab++; ?>" required><?php echo $_POST['inspection_report_summary']; ?></textarea>
              </div>
            </div>

            <div class="col-md-12 text-center mt-3">
              <div class="form-group">
                <button type="submit" name="submit_form" id="submitBtn" class="btn btn-success">
                  <i class="fa fa-save"></i> Submit Report
                </button>
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php page_footer_start(); ?>
<script>
const MAX_IMG_COUNT = 5;
const IMG_MAX_BYTES = 5 * 1024 * 1024; // 5MB
const PDF_MAX_BYTES = 10 * 1024 * 1024; // 10MB
const allowedImgTypes = ['image/jpeg','image/jpg','image/png','image/gif'];

function bytesToHuman(bytes){
  if (bytes === 0) return '0 Bytes';
  const k = 1024; const sizes = ['Bytes','KB','MB','GB'];
  const i = Math.floor(Math.log(bytes)/Math.log(k));
  return (bytes/Math.pow(k,i)).toFixed(2)+' '+sizes[i];
}

function setError(el, msg){ el.textContent = msg || ''; }

function validatePDF(){
  const input = document.getElementById('inspection_pdf');
  const file = input.files[0];
  const err = document.getElementById('pdfError');
  const meta = document.getElementById('pdfMeta');
  setError(err, ''); meta.textContent='';
  if (!file) return true;
  if (file.type !== 'application/pdf') { setError(err, 'Only PDF files are allowed.'); return false; }
  if (file.size > PDF_MAX_BYTES) { setError(err, 'PDF too large. Max 10MB. Selected: '+bytesToHuman(file.size)); return false; }
  meta.textContent = file.name + ' • ' + bytesToHuman(file.size);
  return true;
}

let previewURLs = [];
function clearPreviews(){
  const grid = document.getElementById('imagePreviews');
  grid.innerHTML = '';
  previewURLs.forEach(u=>URL.revokeObjectURL(u));
  previewURLs = [];
}

function validateAndPreviewImages(){
  const input = document.getElementById('inspection_photos');
  const files = Array.from(input.files || []);
  const err = document.getElementById('imagesError');
  setError(err, '');
  clearPreviews();

  if (files.length === 0) { return false; }
  if (files.length > MAX_IMG_COUNT){ setError(err, 'Maximum '+MAX_IMG_COUNT+' images allowed.'); return false; }

  for (const f of files){
    if (!allowedImgTypes.includes(f.type)) { setError(err, 'Invalid image: '+f.name); return false; }
    if (f.size > IMG_MAX_BYTES){ setError(err, 'Image too large: '+f.name+' ('+bytesToHuman(f.size)+')'); return false; }
  }

  // Build previews
  const grid = document.getElementById('imagePreviews');
  files.forEach((f, idx)=>{
    const url = URL.createObjectURL(f); previewURLs.push(url);
    const card = document.createElement('div'); card.className='preview-card';
    card.innerHTML = `
      <img src="${url}" alt="preview-${idx}">
      <div class="small-muted">${f.name}<br>${bytesToHuman(f.size)}</div>
    `;
    grid.appendChild(card);
  });
  return true;
}

function validateForm(){
  const okPDF = validatePDF();
  const okIMG = validateAndPreviewImages();
  if (!okPDF || !okIMG){
    alert('Please fix the highlighted errors before submitting.');
    return false;
  }
  // Sync CKEditor
  if (window.CKEDITOR){ for (let i in CKEDITOR.instances){ CKEDITOR.instances[i].updateElement(); } }
  return true;
}

// Wire up
document.getElementById('inspection_pdf').addEventListener('change', validatePDF);
document.getElementById('inspection_photos').addEventListener('change', validateAndPreviewImages);

// AJAX helpers from your original code
var actionUrl = 'scripts/ajax.php';
function fill_district(val, selected){
  $.post(actionUrl, {term:'b', id:'dist', val:val}, function(data){
    let txt = '<option value="">--Select--</option>'; data = JSON.parse(data);
    $.each(data, function(_, v){ txt += '<option value="'+v.id+'" '+(selected==v.id?'selected':'')+'>'+v.district_name+'</option>'; });
    $('#district').html(txt);
  });
}
function fill_project(val, selected){
  $.post(actionUrl, {term:'b', id:'proj', val:val, dept:$('#department').val()}, function(data){
    let txt = '<option value="">--Select--</option>'; data = JSON.parse(data);
    $.each(data, function(_, v){ txt += '<option value="'+v.id+'" '+(selected==v.id?'selected':'')+'>'+v.project_name_hindi+'</option>'; });
    $('#project_name').html(txt);
  });
}
</script>
<?php page_footer_end(); ?>