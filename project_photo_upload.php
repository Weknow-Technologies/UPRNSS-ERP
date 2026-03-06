<?php
include("scripts/settings.php");
$error='';
$msg='';
$tab=1;

function get_row($db, $sql){
    $rs = mysqli_query($db,$sql);
    return $rs ? mysqli_fetch_assoc($rs) : null;
}
function safe_basename($name){
    return preg_replace('/[^A-Za-z0-9_\.-]/','_', $name);
}

// figure out where to return after saving
$return_to = '';
if(isset($_REQUEST['return_to']) && $_REQUEST['return_to']!==''){
    $return_to = $_REQUEST['return_to'];
} elseif(!empty($_SERVER['HTTP_REFERER'])){
    $return_to = $_SERVER['HTTP_REFERER'];
} else {
    $return_to = 'index.php';
}

$mode = 'create';
$invoice_edit_id = 0;

// edit by edit_id or invoice_id
if(isset($_GET['edit_id'])) $invoice_edit_id = (int)$_GET['edit_id'];
if(isset($_GET['invoice_id'])) $invoice_edit_id = (int)$_GET['invoice_id'];
if($invoice_edit_id > 0) $mode = 'edit';

if(isset($_POST['submit']) && $mode==='create'){
    $sql= 'INSERT INTO invoice_project_file (department_id, division_id, district_id, project_id, upload_date, status, created_by, creation_time)
           VALUES ("' .$_POST['department'].'", "' .$_POST['division_name'].'", "'.$_POST['district'].'", "'.$_POST['project_id'].'", "'.date("Y-m-d").'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'")';
    execute_query($sql);
    if(mysqli_error($db)){ 
        $error .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
    } else {
        $inv_id = mysqli_insert_id($db);
        $addRows = (int)$_POST['add_rows_id'];

        // ensure folder
        $filepath="project_file_upload/".$_POST['division_name']."/".$_POST['district']."/".$_POST['project_id']."/";
        if (!file_exists($filepath)) {
            if (mkdir($filepath, 0777, true)) {
                $msg .= '<p class="alert alert-success mb-2">Folder created successfully</p>';
            } 
        }

        for($i=1;$i<=$addRows;$i++){
            $hasFile = (isset($_FILES['upload_file_'.$i]) && !empty($_FILES['upload_file_'.$i]['name']) && is_uploaded_file($_FILES['upload_file_'.$i]['tmp_name']));
            if(!$hasFile){
                // no file selected: skip row entirely in CREATE
                continue;
            }

            $ftype  = $_POST['file_type_'.$i] ?? '';
            $remark = $_POST['remark_'.$i] ?? '';

            // get extension
            $basename = safe_basename($_FILES["upload_file_".$i]["name"]);
            $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            if($ext===''){
                $error .= '<p class="alert alert-danger">Row '.$i.': Unsupported file (no extension).</p>';
                continue;
            }

            // insert row now (we need ID for filename)
            $ins = 'INSERT INTO transaction_project_file (invoice_id, file_type, upload_file, remark) 
                    VALUES ("'.$inv_id.'", "'.mysqli_real_escape_string($db,$ftype).'", "", "'.mysqli_real_escape_string($db,$remark).'")';
            execute_query($ins);
            if(mysqli_error($db)){ 
                $error .= '<p class="text text-danger">Error # 1.01 : '.mysqli_error($db).'>> '.$ins.'</p>';
                continue;
            }

            $insertid = mysqli_insert_id($db);
            $dest = $filepath.$insertid.'.'.$ext;

            if(move_uploaded_file($_FILES['upload_file_'.$i]['tmp_name'], $dest)){
                $upd="UPDATE transaction_project_file SET upload_file='".mysqli_real_escape_string($db,$dest)."' WHERE sno={$insertid}";
                $ok = mysqli_query($db,$upd);
                if($ok){ $msg .= '<p class="alert alert-success">File #'.$insertid.' uploaded.</p>'; }
            } else {
                $error .= '<p class="alert alert-danger">Row '.$i.': Could not save file.</p>';
                // cleanup row to avoid empty DB entry
                mysqli_query($db,"DELETE FROM transaction_project_file WHERE sno=".$insertid);
            }
        }
    }
    if($error===''){
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Saved</title></head><body>';
        echo '<script> alert("saved successfully."); window.location.href = "gallery.php"; </script>';
        echo '</body></html>';
        exit;
    }
}

if(isset($_POST['save_edit']) && $mode==='edit'){
    $inv_id = (int)$_POST['edit_invoice_id'];
    // Fetch parent to get folder info
    $parent = get_row($db, "SELECT * FROM invoice_project_file WHERE sno=".$inv_id);
    if(!$parent){
        $error .= '<p class="alert alert-danger">Invalid invoice.</p>';
    } else {
        $division_id = $parent['division_id'];
        $district_id = $parent['district_id'];
        $project_id  = $parent['project_id'];
        $folder = "project_file_upload/".$division_id."/".$district_id."/".$project_id."/";
        if (!file_exists($folder)) {
            if (mkdir($folder, 0777, true)) {
                $msg .= '<p class="alert alert-success mb-2">Folder created.</p>';
            }
        }

        // 1) Handle existing rows
        if(isset($_POST['existing_ids']) && is_array($_POST['existing_ids'])){
            foreach($_POST['existing_ids'] as $exist_id){
                $eid = (int)$exist_id;
                // delete?
                if(isset($_POST['delete_existing'][$eid]) && $_POST['delete_existing'][$eid]=='1'){
                    // delete file from disk
                    $f = get_row($db,"SELECT upload_file FROM transaction_project_file WHERE sno=".$eid." AND invoice_id=".$inv_id);
                    if($f && !empty($f['upload_file']) && file_exists($f['upload_file'])){
                        @unlink($f['upload_file']);
                    }
                    mysqli_query($db,"DELETE FROM transaction_project_file WHERE sno=".$eid." AND invoice_id=".$inv_id);
                    if(mysqli_error($db)){
                        $error .= '<p class="alert alert-danger">Error deleting row '.$eid.': '.mysqli_error($db).'</p>';
                    } else {
                        $msg .= '<p class="alert alert-success">Deleted row '.$eid.'.</p>';
                    }
                    continue;
                }

                // update type/remark
                $newType = $_POST['file_type_existing_'.$eid] ?? '';
                $newRem  = $_POST['remark_existing_'.$eid] ?? '';
                $upd = "UPDATE transaction_project_file 
                        SET file_type='".mysqli_real_escape_string($db,$newType)."', 
                            remark='".mysqli_real_escape_string($db,$newRem)."' 
                        WHERE sno=".$eid." AND invoice_id=".$inv_id;
                mysqli_query($db,$upd);
                if(mysqli_error($db)){
                    $error .= '<p class="alert alert-danger">Error updating row '.$eid.': '.mysqli_error($db).'</p>';
                }

                // replace file if uploaded
                if(isset($_FILES['replace_file_'.$eid]) && !empty($_FILES['replace_file_'.$eid]['name']) && is_uploaded_file($_FILES['replace_file_'.$eid]['tmp_name'])){
                    $basename = safe_basename($_FILES['replace_file_'.$eid]['name']);
                    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                    if($ext!==''){
                        $dest = $folder.$eid.'.'.$ext;
                        // delete old file first if exists and path differs
                        $old = get_row($db,"SELECT upload_file FROM transaction_project_file WHERE sno=".$eid);
                        if($old && !empty($old['upload_file']) && file_exists($old['upload_file']) && realpath($old['upload_file'])!=realpath($dest)){
                            @unlink($old['upload_file']);
                        }
                        if(move_uploaded_file($_FILES['replace_file_'.$eid]['tmp_name'],$dest)){
                            mysqli_query($db,"UPDATE transaction_project_file SET upload_file='".mysqli_real_escape_string($db,$dest)."' WHERE sno=".$eid);
                            if(mysqli_error($db)){
                                $error .= '<p class="alert alert-danger">Error saving file for row '.$eid.': '.mysqli_error($db).'</p>';
                            } else {
                                $msg .= '<p class="alert alert-success">File replaced for row '.$eid.'.</p>';
                            }
                        } else {
                            $error .= '<p class="alert alert-danger">Could not move uploaded file for row '.$eid.'.</p>';
                        }
                    }
                }
            }
        }

        // 2) New rows appended — INSERT ONLY IF A FILE IS ATTACHED
        $addRows = (int)($_POST['add_rows_id'] ?? 0);
        if($addRows>0){
            for($i=1;$i<=$addRows;$i++){
                // must exist on form and must have a file attached
                $hasType = isset($_POST['file_type_'.$i]);
                $hasFile = (isset($_FILES['upload_file_'.$i]) && !empty($_FILES['upload_file_'.$i]['name']) && is_uploaded_file($_FILES['upload_file_'.$i]['tmp_name']));
                if(!$hasType || !$hasFile){
                    // keep one blank row in UI but DO NOT insert in DB
                    continue;
                }

                $ftype  = $_POST['file_type_'.$i] ?? '';
                $remark = $_POST['remark_'.$i] ?? '';

                // extension
                $basename = safe_basename($_FILES["upload_file_".$i]["name"]);
                $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                if($ext===''){
                    $error .= '<p class="alert alert-danger">New Row '.$i.': Unsupported file (no extension).</p>';
                    continue;
                }

                // insert db row now (needs ID)
                $ins = 'INSERT INTO transaction_project_file (invoice_id, file_type, upload_file, remark) 
                        VALUES ("'.$inv_id.'", "'.mysqli_real_escape_string($db,$ftype).'", "", "'.mysqli_real_escape_string($db,$remark).'")';
                execute_query($ins);
                if(mysqli_error($db)){ 
                    $error .= '<p class="text text-danger">Error adding new row '.$i.' : '.mysqli_error($db).'>> '.$ins.'</p>';
                    continue;
                }

                $nid = mysqli_insert_id($db);
                $dest = $folder.$nid.'.'.$ext;

                if(move_uploaded_file($_FILES['upload_file_'.$i]['tmp_name'], $dest)){
                    mysqli_query($db,"UPDATE transaction_project_file SET upload_file='".mysqli_real_escape_string($db,$dest)."' WHERE sno=".$nid);
                    if(mysqli_error($db)){
                        $error .= '<p class="alert alert-danger">New Row '.$i.': Could not update file path.</p>';
                    } else {
                        $msg .= '<p class="alert alert-success">New file #'.$nid.' uploaded.</p>';
                    }
                } else {
                    $error .= '<p class="alert alert-danger">New Row '.$i.': Could not save file.</p>';
                    // cleanup to avoid empty row
                    mysqli_query($db,"DELETE FROM transaction_project_file WHERE sno=".$nid);
                }
            }
        }
    }

    // If everything is okay, show JS message and redirect back where you came from
    if($error===''){
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Saved</title></head><body>';
        echo '<script> alert("Changes saved successfully."); window.location.href = "gallery.php"; </script>';
        echo '</body></html>';
        exit;
    }
}

/* ===========================
   Prefill form state
=========================== */
if($mode==='create'){
    if(!isset($_POST['search'])){
        $_POST['edit_sno'] = '';
        $_POST['department'] = '';
        $_POST['district'] = '';
        $_POST['division_name'] = '';
        $_POST['project_name'] = '';
        $_POST['project_name_hindi'] = '';
        $_POST['project_name_hindi_unicode'] = '';
        $_POST['project_id'] = '';
        $_POST['remark_1'] = '';
        $_POST['upload_file_1'] = '';
        $_POST['file_type_1'] = '';
        $_POST['add_rows_id'] = 1;
    }
    if(isset($_GET['upload_id'])){
        $data = get_row($db, 'select * from uprnss_project_temp where sno='.(int)$_GET['upload_id']);
        if($data){
            $_POST['cid_sno'] = $data['sno'];
            $_POST['project_id'] = $data['sno'];
            $_POST['head_project_name'] = $data['new_project_id'];
            $_POST['department'] = $data['department_id'];
            $_POST['district'] = $data['district_id'];
            $_POST['division_name'] = $data['division_id'];
            $_POST['project_name'] = $data['project_name_hindi'];
            $_POST['project_type'] = $data['project_type'];
            $_POST['project_name_hindi'] = '';
            $_POST['project_name_hindi_unicode'] = $data['project_name_hindi'];
        }
    }
} else {
    // EDIT mode: prefill header fields from invoice + project
    $inv = get_row($db, "SELECT ipf.*, upt.project_name_hindi, upt.project_type 
                         FROM invoice_project_file ipf 
                         LEFT JOIN uprnss_project_temp upt ON upt.sno=ipf.project_id
                         WHERE ipf.sno=".$invoice_edit_id);
    if($inv){
        $_POST['department'] = $inv['department_id'];
        $_POST['division_name'] = $inv['division_id'];
        $_POST['district'] = $inv['district_id'];
        $_POST['project_id'] = $inv['project_id'];
        $_POST['project_name'] = $inv['project_name_hindi'];
        $_POST['project_type'] = $inv['project_type'];
    } else {
        $error .= '<p class="alert alert-danger">Invalid invoice id.</p>';
    }
}

page_header_start();
?>
<style>
    textarea{ font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; }
    .existing-file-thumb { max-height: 120px; border-radius: 6px; display:block; }
    .card-title small { font-weight: normal; }
</style>
<?php
page_header_end();
page_sidebar();
?>

<div id="container" class="no-print">
<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF'] . ($mode==='edit' ? '?edit_id='.$invoice_edit_id : (isset($_GET['upload_id'])? '?upload_id='.(int)$_GET['upload_id'] : '')); ?>">
    <div class="row no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white mt-1">
                    <h4 class="card-title text-center text-white ">
                        <?php echo ($mode==='edit'?'Edit Image / Videos <small>(Invoice #'.$invoice_edit_id.')</small>':'Upload Image / Videos'); ?>
                    </h4>
                </div>

                <?php echo $msg; echo $error; ?>

                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-3 ">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" readonly required>
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $run = mysqli_query($db,"select * from uprnss_department_name");
                                    while($d = mysqli_fetch_array($run)){
                                        echo '<option value="'.$d['sno'].'" '.(isset($_POST['department']) && $_POST['department']==$d['sno']?'selected':'').'>'.trim($d['department_name_hindi']).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-3">
                            <label>Unit/Division Name</label>
                            <select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>" readonly required>
                                <option value="">--- Select ---</option>
                                <?php
                                $run = mysqli_query($db,'select * from uprnss_division order by division_name ASC');
                                while($d = mysqli_fetch_array($run)){
                                    echo '<option value="'.$d['s_no'].'" '.(isset($_POST['division_name']) && $_POST['division_name']==$d['s_no']?'selected':'').'>'.$d['division_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>आच्छादित जनपद</label>
                                <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" readonly required>
                                    <option value="">--- Select ---</option>
                                    <?php
                                    $run = mysqli_query($db,"select * from uprnss_district");
                                    while($d = mysqli_fetch_array($run)){
                                        echo '<option value="'.$d['sno'].'" '.(isset($_POST['district']) && $_POST['district']==$d['sno']?'selected':'').'>'.$d['district_name_hindi'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project Name</label>
                                <textarea type="text" name="project_name" id="project_name" class="form-control" placeholder="" readonly><?php echo htmlspecialchars($_POST['project_name']??''); ?></textarea>
                                <input type="hidden" class="form-control" id="project_id" name="project_id" value="<?php echo htmlspecialchars($_POST['project_id']??''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <?php if($mode==='edit'): ?>
                        <!-- Existing files block -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card border">
                                    <div class="card-header">
                                        <strong>Existing Files</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle">
                                                <thead>
                                                    <tr>
                                                        <th style="width:60px;">Sr. No</th>
                                                        <th style="width:200px;">Preview</th>
                                                        <th style="width:150px;">Type</th>
                                                        <th style="width:150px;">Remark</th>
                                                        <th style="width:220px;">Replace File</th>
                                                        <th style="width:60px;">Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $rs = execute_query("SELECT * FROM transaction_project_file WHERE invoice_id=".$invoice_edit_id." ORDER BY sno ASC");
                                                    $haveAny=false;
													$sr=1;
                                                    while($r = mysqli_fetch_assoc($rs)){
                                                        $haveAny=true;
                                                        $id = (int)$r['sno'];
                                                        $type = strtolower($r['file_type']);
                                                        $path = $r['upload_file'];
                                                        echo '<tr>';
                                                        echo '<td>'.$sr++.'</td></td>';
                                                        echo '<input type="hidden" name="existing_ids[]" value="'.$id.'"><td>';
														
                                                        if($type==='image'){
                                                            echo '<img src="'.htmlspecialchars($path).'" class="existing-file-thumb" alt="img">';
                                                        } elseif($type==='video'){
                                                            echo '<video src="'.htmlspecialchars($path).'" class="w-100" style="max-height:120px;" controls></video>';
                                                        } else {
                                                            echo '<a href="'.htmlspecialchars($path).'" target="_blank">Open</a>';
                                                        }
                                                        echo '</td>';
                                                        // echo '<td><small>'.htmlspecialchars($path).'</small></td>';
                                                        echo '<td>
                                                                <select name="file_type_existing_'.$id.'" id="file_type_existing_'.$id.'" class="form-control">
                                                                    <option value="image" '.($type==='image'?'selected':'').'>Image</option>
                                                                    <option value="video" '.($type==='video'?'selected':'').'>Video</option>
                                                                    <option value="pdf" '.($type==='pdf'?'selected':'').'>PDF</option>
                                                                    <option value="doc" '.($type==='doc'?'selected':'').'>Document</option>
                                                                    <option value="excel" '.($type==='excel'?'selected':'').'>Excel</option>
                                                                    <option value="ppt" '.($type==='ppt'?'selected':'').'>PPT</option>
                                                                </select>
                                                              </td>';
                                                        echo '<td><textarea class="form-control" name="remark_existing_'.$id.'" rows="1">'.htmlspecialchars($r['remark']).'</textarea></td>';
                                                        echo '<td><input type="file" class="form-control" name="replace_file_'.$id.'" id="replace_file_'.$id.'"></td>';
                                                        echo '<td class="text-center"><input type="checkbox" name="delete_existing['.$id.']" value="1"></td>';
                                                        echo '</tr>';
                                                    }
                                                    if(!$haveAny){
                                                        echo '<tr><td colspan="7" class="text-center text-muted">No files yet for this invoice.</td></tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- New rows block -->
                    <?php 
                    $startRows = (int)($_POST['add_rows_id'] ?? 1);
                    if($startRows<1) $startRows=1;
                    for($i=1;$i<=$startRows;$i++):
                    ?>
                    <div class="row border rounded m-2 p-2 border-secondary" id="add_rows_length">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>File Type</label>
                                <select class="form-control file-type" id="file_type_<?php echo $i; ?>" name="file_type_<?php echo $i; ?>">
                                    <option value="image" <?php echo (($_POST['file_type_'.$i]??'')==='image'?'selected':''); ?>>Image</option>
                                    <option value="video" <?php echo (($_POST['file_type_'.$i]??'')==='video'?'selected':''); ?>>Video</option>
                                    <option value="pdf"   <?php echo (($_POST['file_type_'.$i]??'')==='pdf'  ?'selected':''); ?>>PDF</option>
                                    <option value="doc"   <?php echo (($_POST['file_type_'.$i]??'')==='doc'  ?'selected':''); ?>>Document</option>
                                    <option value="excel" <?php echo (($_POST['file_type_'.$i]??'')==='excel'?'selected':''); ?>>Excel</option>
                                    <option value="ppt"   <?php echo (($_POST['file_type_'.$i]??'')==='ppt'  ?'selected':''); ?>>PPT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Attach</label>
                                <input type="file" class="form-control file-input" id="upload_file_<?php echo $i; ?>" name="upload_file_<?php echo $i; ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Remark</label>
                                <textarea type="text" name="remark_<?php echo $i; ?>" id="remark_<?php echo $i; ?>" class="form-control" placeholder=""><?php echo htmlspecialchars($_POST['remark_'.$i]??''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-info" onClick="add_rows()">Add</button>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <div id="dynamic_rows_container"></div>
                    <input type="hidden" name="add_rows_id" id="add_rows_id" value="<?php echo $startRows; ?>">
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to, ENT_QUOTES); ?>">

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="form-group">
                                <?php if($mode==='edit'): ?>
                                    <button type="submit" name="save_edit" class="btn btn-success">Save Changes</button>
                                    <input type="hidden" name="edit_invoice_id" value="<?php echo $invoice_edit_id; ?>">
                                <?php else: ?>
                                    <button type="submit" name="submit" class="btn btn-success">Upload File(s)</button>
                                <?php endif; ?>
                                <input type="hidden" id="cid_sno" name="cid_sno" value="<?php echo htmlspecialchars($_POST['cid_sno']??''); ?>">
                                <input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo htmlspecialchars($_POST['edit_sno']??''); ?>">
                            </div>
                        </div>
                    </div>

                </div><!-- card-body -->
            </div>
        </div>
    </div>
</form>
</div>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
function add_rows(){
    var id = parseInt($("#add_rows_id").val(),10) || 0;
    id = id + 1;

    var html = ''
      + '<div class="row border rounded m-2 p-2 border-secondary">'
      + '  <div class="col-md-3">'
      + '    <div class="form-group">'
      + '      <label>File Type</label>'
      + '      <select class="form-control file-type" id="file_type_'+id+'" name="file_type_'+id+'">'
      + '        <option value="image">Image</option>'
      + '        <option value="video">Video</option>'
      + '        <option value="pdf">PDF</option>'
      + '        <option value="doc">Document</option>'
      + '        <option value="excel">Excel</option>'
      + '        <option value="ppt">PPT</option>'
      + '      </select>'
      + '    </div>'
      + '  </div>'
      + '  <div class="col-md-3">'
      + '    <div class="form-group">'
      + '      <label>Attach</label>'
      + '      <input type="file" class="form-control file-input" id="upload_file_'+id+'" name="upload_file_'+id+'">'
      + '    </div>'
      + '  </div>'
      + '  <div class="col-md-5">'
      + '    <div class="form-group">'
      + '      <label>Remark</label>'
      + '      <textarea class="form-control" id="remark_'+id+'" name="remark_'+id+'"></textarea>'
      + '    </div>'
      + '  </div>'
      + '  <div class="col-md-1 d-flex align-items-end">'
      + '    <button type="button" class="btn btn-info" onClick="add_rows()">Add</button>'
      + '  </div>'
      + '</div>';

    $("#dynamic_rows_container").append(html);
    $("#add_rows_id").val(id);
    bindAcceptFor(id);
}

function bindAcceptFor(i){
    var sel = document.getElementById('file_type_'+i);
    var inp = document.getElementById('upload_file_'+i);
    if(!sel || !inp) return;
    function setAccept(){
        var v = sel.value;
        if(v==='image') inp.accept = 'image/*';
        else if(v==='video') inp.accept = 'video/*';
        else if(v==='pdf') inp.accept = '.pdf';
        else if(v==='doc') inp.accept = '.doc,.docx';
        else if(v==='excel') inp.accept = '.xls,.xlsx';
        else if(v==='ppt') inp.accept = '.ppt,.pptx';
        else inp.accept='';
    }
    sel.addEventListener('change', setAccept);
    setAccept();
}

// Bind accept for initial rows
(function(){
    var limit = parseInt(document.getElementById('add_rows_id').value,10) || 1;
    for(var i=1;i<=limit;i++){
        bindAcceptFor(i);
    }
})();

// For existing rows (edit mode): set accept on replace inputs based on selected type
<?php if($mode==='edit'): ?>
(function(){
    const selects = document.querySelectorAll('[id^="file_type_existing_"]');
    selects.forEach(function(sel){
        var id = sel.id.split('_').pop();
        var inp = document.getElementById('replace_file_'+id);
        function setAcc(){
            var v = sel.value;
            if(!inp) return;
            if(v==='image') inp.accept='image/*';
            else if(v==='video') inp.accept='video/*';
            else if(v==='pdf') inp.accept='.pdf';
            else if(v==='doc') inp.accept='.doc,.docx';
            else if(v==='excel') inp.accept='.xls,.xlsx';
            else if(v==='ppt') inp.accept='.ppt,.pptx';
            else inp.accept='';
        }
        sel.addEventListener('change', setAcc);
        setAcc();
    });
})();
<?php endif; ?>
</script>
<?php
page_footer_end();
