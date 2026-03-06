<?php
include("scripts/settings.php");

$msg='';
$tab=1;

$months = array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dec");

/* =====================================================
   AUTO CREATE TABLE (SAFE)
===================================================== */
$createTable = "
CREATE TABLE IF NOT EXISTS materials_testing (
    sno INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    division_id INT NULL,
    district_id INT NOT NULL,
    project_name INT NOT NULL,
    material_type VARCHAR(100) NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    sample_date DATE NULL,
    test_date DATE NOT NULL,
    testing_lab VARCHAR(255) NOT NULL,
    report_pdf_path VARCHAR(255) NULL,
    report_status VARCHAR(255) NOT NULL,
    created_by VARCHAR(100),
    creation_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
execute_query($createTable);

/* =====================================================
   EDIT MODE
===================================================== */
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editData = [];

if($edit_id){
    $res = execute_query("SELECT * FROM materials_testing WHERE id='$edit_id'");
    $editData = mysqli_fetch_assoc($res);
}

/* =====================================================
   FORM SUBMIT (INSERT / UPDATE)
===================================================== */
if(isset($_POST['submit'])){

    $requiredFields = [
        'department'=>'विभाग',
        'district'=>'आच्छादित जनपद',
        'project_name'=>'परियोजना का नाम',
        'material_type'=>'सामग्री प्रकार',
        'test_name'=>'परीक्षण का नाम',
        'test_date'=>'परीक्षण की तारीख',
        'testing_lab'=>'परीक्षण प्रयोगशाला',
        'report_status'=>'रिपोर्ट की स्थिति'
    ];

    foreach($requiredFields as $k=>$label){
        if(!isset($_POST[$k]) || trim($_POST[$k])===''){
            $msg .= '<div class="alert alert-danger">कृपया "'.$label.'" भरें</div>';
        }
    }

    /* ---------- PDF UPLOAD ---------- */
    $uploadedReportPath = $editData['report_pdf_path'] ?? '';
    if(isset($_FILES['testing_report_pdf']) && $_FILES['testing_report_pdf']['error'] === UPLOAD_ERR_OK){
        $ext = strtolower(pathinfo($_FILES['testing_report_pdf']['name'], PATHINFO_EXTENSION));
        if($ext !== 'pdf'){
            $msg .= '<div class="alert alert-danger">केवल PDF फ़ाइल अपलोड करें</div>';
        } else {
            $dir = __DIR__.'/materials_testing_reports';
            if(!is_dir($dir)) mkdir($dir,0775,true);
            $file = 'MTR_'.date('YmdHis').'.pdf';
            if(move_uploaded_file($_FILES['testing_report_pdf']['tmp_name'],$dir.'/'.$file)){
                $uploadedReportPath = 'materials_testing_reports/'.$file;
            }
        }
    }

    if($msg===''){

        $testingLabValue = ($_POST['testing_lab']==='other_eng_college')
            ? $_POST['testing_lab_other']
            : $_POST['testing_lab'];

        $materialTypeValue = ($_POST['material_type']==='other')
            ? $_POST['material_type_other']
            : $_POST['material_type'];

        $divisionIdSql = (isset($_POST['division_id']) && is_numeric($_POST['division_id']))
            ? (int)$_POST['division_id']
            : 'NULL';

        $sampleDateValue = !empty($_POST['sample_date'])
            ? $_POST['sample_date']
            : $_POST['test_date'];

        /* ---------- INSERT / UPDATE ---------- */
        if($edit_id==0){

            $sql = "
            INSERT INTO materials_testing (
                department_id, division_id, district_id, project_name,
                material_type, test_name, sample_date, test_date,
                testing_lab, report_pdf_path, report_status,
                created_by, creation_time
            ) VALUES (
                '{$_POST['department']}',
                $divisionIdSql,
                '{$_POST['district']}',
                '{$_POST['project_name']}',
                '$materialTypeValue',
                '{$_POST['test_name']}',
                '$sampleDateValue',
                '{$_POST['test_date']}',
                '$testingLabValue',
                '$uploadedReportPath',
                '{$_POST['report_status']}',
                '".($_SESSION['username']??'')."',
                '".date('Y-m-d H:i:s')."'
            )";

        } else {

            $sql = "
            UPDATE materials_testing SET
                department_id='{$_POST['department']}',
                division_id=$divisionIdSql,
                district_id='{$_POST['district']}',
                project_name='{$_POST['project_name']}',
                material_type='$materialTypeValue',
                test_name='{$_POST['test_name']}',
                sample_date='$sampleDateValue',
                test_date='{$_POST['test_date']}',
                testing_lab='$testingLabValue',
                report_status='{$_POST['report_status']}',
                updated_at=NOW()
            ";

            if($uploadedReportPath!=''){
                $sql .= ", report_pdf_path='$uploadedReportPath'";
            }

            $sql .= " WHERE id='$edit_id'";
        }

        execute_query($sql);

        if(mysqli_error($db)){
            $msg .= '<div class="alert alert-danger">'.mysqli_error($db).'</div>';
        } else {
            header("Location: materials_testing.php");
            exit;
        }
    }
}

page_header_start();
page_header_end();
page_sidebar();
?>
   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="return true;">
        <div class="row">
            <div class="col-md-12">
			      <div class="card">
					<div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
						<h4 class="card-title mb-0 text-center w-100 text-white">Matterial Testing</h4>
						<a href="materials_report.php" class="btn btn-outline-primary ms-3" style="white-space: nowrap;">View Reports</a>
					</div>

					<div class="card-body">
					  <?php echo $msg; ?>

					  <h4 class="card-title">परियोजना का विवरण</h4>
					  <div class="row">
						
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
							
						  </div>
						</div>

						<div class="col-md-3">
						  <div class="form-group">
							<label>सामग्री  रिपोर्ट PDF <span class="text-danger">*</span></label>
							<input type="file" accept="application/pdf" class="form-control" id="testing_report_pdf" name="testing_report_pdf" tabindex="<?php echo $tab++; ?>" required>
							
							<small id="pdfMeta" class="small-muted"></small>
							<div id="pdfError" class="invalid-feedback"></div>
						  </div>
						</div>
					  </div>                        
                        </br><h4 class="card-title">सामग्री प्रशिक्षण </h4></br>
                        <div class="row">
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label>रिपोर्ट आईडी</label>
                                    <input type="text" name="report_id" id="report_id" class="form-control" placeholder="" value="<?php echo isset($_POST['report_id']) ? $_POST['report_id'] : ''; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div> -->
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label>प्रोजेक्ट का नाम/आईडी</label>
                                    <input type="text" name="project_name_or_id" id="project_name_or_id" class="form-control" placeholder="" value="<?php echo isset($_POST['project_name_or_id']) ? $_POST['project_name_or_id'] : ''; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div> -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>सामग्री प्रकार</label>
                                    <select name="material_type" id="material_type" class="form-control" tabindex="<?php echo $tab++; ?>" onchange="toggleMaterialTypeOther()">
                                        <option value="">-- Select --</option>
                                        <option value="Concrete" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Concrete')?'selected':''; ?>>Concrete</option>
                                        <option value="Soil" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Soil')?'selected':''; ?>>Soil</option>
                                        <option value="Steel" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Steel')?'selected':''; ?>>Steel</option>
                                        <option value="Asphalt" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Asphalt')?'selected':''; ?>>Asphalt</option>
                                        <option value="Bricks" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Bricks')?'selected':''; ?>>Bricks</option>
                                        <option value="Wood" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Wood')?'selected':''; ?>>Wood</option>
                                        <option value="Aggregates" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Aggregates')?'selected':''; ?>>Aggregates</option>
                                        <option value="Cement" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Cement')?'selected':''; ?>>Cement</option>
                                        <option value="Mortar" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='Mortar')?'selected':''; ?>>Mortar</option>
                                        <option value="other" <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='other')?'selected':''; ?>>Other</option>
                                    </select>
                                    <input type="text" name="material_type_other" id="material_type_other" class="form-control mt-2" placeholder="अन्य सामग्री प्रकार लिखें" value="<?php echo isset($_POST['material_type_other']) ? $_POST['material_type_other'] : ''; ?>" style="display: <?php echo (isset($_POST['material_type']) && $_POST['material_type']==='other')?'block':'none'; ?>;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>परीक्षण का नाम</label>
                                    <input type="text" name="test_name" id="test_name" class="form-control" placeholder="" value="<?php echo isset($_POST['test_name']) ? $_POST['test_name'] : ''; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label>नमूने की तारीख</label>
                                    <input type="date" name="sample_date" id="sample_date" class="form-control" value="<?php echo isset($_POST['sample_date']) ? $_POST['sample_date'] : ''; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div> -->
                      
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>परीक्षण की तारीख</label>
                                    <input type="date" name="test_date" id="test_date" class="form-control" value="<?php echo isset($_POST['test_date']) ? $_POST['test_date'] : ''; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>परीक्षण प्रयोगशाला</label>
                                    <select name="testing_lab" id="testing_lab" class="form-control" tabindex="<?php echo $tab++; ?>">
                                        <option value="">-- Select --</option>
                                        <option value="hq" <?php echo (isset($_POST['testing_lab']) && $_POST['testing_lab']==='hq')?'selected':''; ?>>मुख्यालय</option>
                                        <option value="block_level" <?php echo (isset($_POST['testing_lab']) && $_POST['testing_lab']==='block_level')?'selected':''; ?>>प्रखंड स्तर</option>
                                        <option value="pwd" <?php echo (isset($_POST['testing_lab']) && $_POST['testing_lab']==='pwd')?'selected':''; ?>>PWD</option>
                                        <option value="govt_eng_college" <?php echo (isset($_POST['testing_lab']) && $_POST['testing_lab']==='govt_eng_college')?'selected':''; ?>>राजकीय इंजीनियरिंग कॉलेज</option>
                                        <option value="other_eng_college" <?php echo (isset($_POST['testing_lab']) && $_POST['testing_lab']==='other_eng_college')?'selected':''; ?>>अन्य इंजीनियरिंग कॉलेज</option>
                                    </select>
                                    <input type="text" name="testing_lab_other" id="testing_lab_other" class="form-control mt-2" placeholder="कॉलेज का नाम लिखें" value="<?php echo isset($_POST['testing_lab_other']) ? $_POST['testing_lab_other'] : ''; ?>" style="display: <?php echo (isset($_POST['testing_lab']) && $_POST['testing_lab']==='other_eng_college')?'block':'none'; ?>;">
                                </div>
                            </div>
                        </div>
						<div class="col-md-12">
						  <div class="form-group">
                                    <label>रिपोर्ट की सारांश</label>
									<textarea name="report_status" id="report_status" class="form-control" rows="6" placeholder="परीक्षण निष्कर्षों का विस्तृत विवरण लिखें" tabindex="<?php echo $tab++; ?>" required><?php echo isset($_POST['report_status']) ? $_POST['report_status'] : ''; ?></textarea>
                                </div>
						</div>
                        <div class="row m-2 p-2 border-bottom">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <button type="submit" name="submit" class="btn btn-success" style="margin-left: -15px;">
                                        <i class="fa fa-save"></i>Submit Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php
page_footer_start();
?>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
    $('select[multiple]').multiselect();

    var actionUrl = 'scripts/ajax.php';

    // Function to toggle visibility of the other material type input
    function toggleMaterialTypeOther() {
        var selectedValue = document.getElementById('material_type').value;
        var otherInput = document.getElementById('material_type_other');
        if (selectedValue === 'other') {
            otherInput.style.display = 'block';
        } else {
            otherInput.style.display = 'none';
            otherInput.value = ''; // Clear the input if not selected
        }
    }

    // Client-side validation for materials testing fields
    function validateMaterialsTesting(){
        var errors = [];
        function isEmpty(id){
            var v = $(id).val();
            return !v || String(v).trim()==='';
        }
        if(isEmpty('#department')) errors.push('विभाग');
        if(isEmpty('#district')) errors.push('आच्छादित जनपद');
        if(isEmpty('#project_name')) errors.push('परियोजना का नाम');
        // if(isEmpty('#project_name_or_id')) errors.push('प्रोजेक्ट का नाम/आईडी');
        if(isEmpty('#material_type')) errors.push('सामग्री प्रकार');
        if(isEmpty('#test_name')) errors.push('परीक्षण का नाम');
        // if(isEmpty('#sample_date')) errors.push('नमूने की तारीख');
        if(isEmpty('#test_date')) errors.push('परीक्षण की तारीख');
        if(isEmpty('#testing_lab')) errors.push('परीक्षण प्रयोगशाला');
        if($('#testing_lab').val()==='other_eng_college' && (!$('#testing_lab_other').val() || String($('#testing_lab_other').val()).trim()==='')){
            errors.push('अन्य इंजीनियरिंग कॉलेज (नाम)');
        }
        if(isEmpty('#report_status')) errors.push('রिपोर्ट की स्थिति');
        // PDF input client-side MIME/extension check
        var fileInput = document.getElementById('testing_report_pdf');
        if(fileInput && fileInput.files && fileInput.files.length){
            var f = fileInput.files[0];
            var nameOk = /\.pdf$/i.test(f.name);
            var typeOk = (f.type === 'application/pdf');
            if(!(nameOk && typeOk)){
                errors.push('Material Testing Report (PDF)');
            }
        }
        if(errors.length){
            alert('कृपया निम्न फ़ील्ड भरें:\n- '+errors.join('\n- '));
            return false;
        }
        return true;
    }

    // Auto fill project_name_or_id with selected project id if empty
    $(document).on('change', '#project_name', function(){
        if(!$('#project_name_or_id').val()){
            $('#project_name_or_id').val($(this).val());
        }
    });

    // Toggle other text input for testing lab
    $(document).on('change', '#testing_lab', function(){
        if($(this).val()==='other_eng_college'){
            $('#testing_lab_other').show();
        } else {
            $('#testing_lab_other').hide().val('');
        }
    });

    function fill_district(val, selected){
        var data = {"term":"b", "id":"dist", "val":val};
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: data,
            success: function(data){
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function(key, value){
                    txt += '<option value="'+value.id+'" ';
                    if(selected==value.id){
                        txt += ' selected ';
                    }
                    txt += '>'+value.district_name+'</option>';
                });
                $("#district").html(txt);
            }
        });
    }

    function fill_project(val, selected){
        var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department").val()};
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: data,
            success: function(data){
                var txt = '<option value="">--Select--</option>';
                data = JSON.parse(data);
                $.each(data, function(key, value){
                    txt += '<option value="'+value.id+'" ';
                    if(selected==value.id){
                        txt += ' selected ';
                    }
                    txt += '>'+value.project_name_hindi+'</option>';
                });
                $("#project_name").html(txt);
            }
        });
    }
</script>

<?php
page_footer_end();
?>


