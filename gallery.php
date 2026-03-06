<?php
include("scripts/settings.php");

$msg='';
$tab=1;

/* ===== Open/Close project (kept as-is) ===== */
if(isset($_POST['submit'])){
    if($_POST['cid_sno']!=''){
        $sql = 'update uprnss_project_temp set 
        reporting_status="1", 
        closing_remark="'.$_POST['closing_remark'].'", 
        closing_date="'.$_POST['closing_date'].'", 
        edited_by="'.$_SESSION['username'].'", 
        edition_time="'.date("Y-m-d H:i:s").'"		
        where sno="'.$_POST['cid_sno'].'"';
        execute_query($sql);
        if(mysqli_error($db)){ 
            $msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
        } else {
            $msg .= '<div class="alert alert-danger">Project Close</div>';
            $_POST = [];
        }
    }
}
if(isset($_GET['rid'])){
    $sql = 'update uprnss_project_temp set 
    reporting_status="0", 
    closing_remark="", 
    closing_date=""	
    where sno="'.$_GET['rid'].'"';
    execute_query($sql);
    if(mysqli_error($db)){ 
        $msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
    } else {
        $sql = 'select * from uprnss_project_temp where sno="'.$_GET['rid'].'"';
        $data = mysqli_fetch_assoc(execute_query($sql));
        $msg .= '<div class="alert alert-danger">'.htmlspecialchars($data['project_name_hindi']).' Project Start</div>';	
        $_POST = [];
    }
}

/* ===== Reset defaults on first load ===== */
if(!isset($_POST['search'])){
    $_POST = [
        'edit_sno' => '',
        'department' => '',
        'district' => '',
        'division_name' => '',
        'project_name' => '',
        'project_name_hindi' => '',
        'project_name_hindi_unicode' => '',
        'project_type' => '',
        'work_start_date' => '',
        'date_from' => '',
        'date_to' => '',
        'date_type' => '',
        'sanction_cost' => '',
        'type' => '',
        'type1' => '',
        'tot_exp_project' => '',
        'closing_date' => '',
        'closing_remark' => '',
    ];
}

/* ====== AJAX: return files list for an invoice ====== */
if(isset($_GET['ajax']) && $_GET['ajax']==='files' && isset($_GET['invoice_id'])){
    $invoice_id = (int)$_GET['invoice_id'];
    $q = 'SELECT sno, file_type, upload_file, remark 
          FROM transaction_project_file 
          WHERE invoice_id='.$invoice_id.' 
          ORDER BY sno ASC';
    $rs = execute_query($q);
    if(mysqli_error($db)){
        echo '<div class="alert alert-danger">Error: '.htmlspecialchars(mysqli_error($db)).'</div>';
        exit;
    }
    $rows = [];
    while($r = mysqli_fetch_assoc($rs)){ $rows[] = $r; }
    $count = count($rows);

    // echo '<div class="mb-2"><strong>Total files: '.$count.'</strong></div>';

    if($count===0){
        echo '<div class="alert alert-warning">No files uploaded for this invoice.</div>';
        exit;
    }

    echo '<div class="row g-3">';
    foreach($rows as $r){
        $path = $r['upload_file'];
        $type = strtolower($r['file_type']);
        $remark = htmlspecialchars($r['remark']);
        echo '<div class="col-md-4"><div class="card h-100">';
        echo '<div class="card-body">';
        if($type==='image'){
            echo '<img src="'.htmlspecialchars($path).'" data-full="'.htmlspecialchars($path).'" class="img-fluid rounded mb-2 file-thumb" alt="image">';
        } elseif($type==='video'){
            echo '<video src="'.htmlspecialchars($path).'" class="w-100 rounded mb-2" controls></video>';
        } elseif($type==='pdf'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open PDF</a></p>';
        } elseif($type==='doc'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open Document</a></p>';
        } elseif($type==='excel'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open Excel</a></p>';
        } elseif($type==='ppt'){
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open PPT</a></p>';
        } else {
            echo '<p class="mb-2"><a href="'.htmlspecialchars($path).'" target="_blank">Open File</a></p>';
        }
        // Only remark (no id, no type)
        if($remark!==''){
            echo '<div class="mt-1"><small class="text-muted">'.$remark.'</small></div>';
        }
        echo '</div></div></div>';
    }
    echo '</div>';
    exit;
}

/* ===== UI state ===== */
$page = 1;
if(isset($_GET['cid'])) $page = 2;
if(isset($_GET['view'])) $page = 3;

/* Prefill if cid provided (kept as-is) */
if(isset($_GET['cid'])){
    $sql = 'select * from uprnss_project_temp where sno="'.$_GET['cid'].'"';
    $data = mysqli_fetch_assoc(execute_query($sql));
    $_POST['cid_sno'] = $data['sno'];
    $_POST['head_project_name'] = $data['new_project_id'];
    $_POST['department'] = $data['department_id'];
    $_POST['district'] = $data['district_id'];
    $_POST['division_name'] = $data['division_id'];
    $_POST['project_name'] = $data['project_name_hindi'];
    $_POST['project_type'] = $data['project_type'];
    $_POST['project_name_hindi'] = '';
    $_POST['project_name_hindi_unicode'] = $data['project_name_hindi'];
}

page_header_start();
?>

<style>
    textarea{ font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; }
    .modal-lg { max-width: 95% !important; }

    /* Make close (X) always visible on colored header */
    .modal-header .btn-close {
        filter: invert(1); /* force white icon */
        opacity: .9;
    }
    .modal-header .btn-close:hover { opacity: 1; }

    /* Thumbs and preview */
    .file-thumb { cursor: zoom-in; }
    .img-preview-full {
        max-width: 100%;
        max-height: 80vh;
        display: block;
        margin: 0 auto;
    }

    /* Icon-only buttons */
    .icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,.15);
        background: #fff;
    }
    .icon-btn:hover { background: #f6f6f6; }
    .icon-label {
        font-size: 12px;
        margin-left: 6px;
        color: #6c757d;
        vertical-align: middle;
    }
    .icon-stack { display:inline-flex; align-items:center; gap:6px; }

    /* Sticky thead already set via inline style; ensure z-index */
    #general_stat_table thead { position: sticky; top: 0; z-index: 2; }
</style>
<?php
page_header_end();
page_sidebar();
?>

<div id="container" class="no-print">
<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div class="card card-body">
        <?php echo $msg?>
        <div class="row d-flex my-auto">  
            <table width="100%" class="table table-striped table-hover rounded">	
                <tr>
                    <th>विभाग</th>							
                    <th width="18%">
                        <select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>">
                            <option value="">--- Select ---</option>
                            <?php
                            $run = mysqli_query($db,"select * from uprnss_department_name");
                            while($d = mysqli_fetch_array($run)){
                                echo '<option value="'.$d['sno'].'" '.(isset($_POST['department']) && $_POST['department']==$d['sno']?'selected':'').'>'.trim($d['department_name_hindi']).'</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <th>प्रखण्ड</th>
                    <th width="18%">
                        <select class="form-control" name="division_name" id="division_name" tabindex="<?php echo $tab++; ?>">
                            <option value="">--- Select ---</option>
                            <?php
                            $run = mysqli_query($db,"select * from uprnss_division where s_no IN (".implode(",", array_map('intval', $_SESSION['divisions'])).") order by division_name ASC");
                            while($d = mysqli_fetch_array($run)){
                                echo '<option value="'.$d['s_no'].'" '.(isset($_POST['division_name']) && $_POST['division_name']==$d['s_no']?'selected':'').'>'.$d['division_name'].'</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <th>आच्छादित जनपद</th>
                    <th width="18%">
                        <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>">
                            <option value="">--- Select ---</option>
                            <?php
                            $run = mysqli_query($db,"select * from uprnss_district");
                            while($d = mysqli_fetch_array($run)){
                                echo '<option value="'.$d['sno'].'" '.(isset($_POST['district']) && $_POST['district']==$d['sno']?'selected':'').'>'.$d['district_name_hindi'].'</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <th>परियोजना का स्तर</th>
                    <th width="18%">
                        <select name="project_type" id="project_type" class="form-control">
                            <option value="">--- Select ---</option>
                            <option value="1" <?php echo ($_POST['project_type']==1?'selected':''); ?>>शासन स्तर</option>
                            <option value="2" <?php echo ($_POST['project_type']==2?'selected':''); ?>>जिला स्तर</option>
                        </select>
                    </th>
                </tr>
            </table>
            <div class="col-md-12 text-center">
                <button type="submit" name="search" class="btn btn-primary">Search</button>
                <input type="hidden" id="id" name="id" value="1">
                <input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo htmlspecialchars($_POST['edit_sno']); ?>">
            </div>
        </div>
    </div>
</form>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white mt-2">
                <h4 class="card-title text-center text-white">Gallery Project List</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover table-bordered" id="general_stat_table">
                    <thead style="position:sticky;top:0; z-index:2;">
                        <tr>
                            <th>S.No.</th>
                            <th>Project Type</th>
                            <th>Department</th>
                            <th>Division</th>
                            <th>District</th>
                            <th>Project Name</th>
                            <th>View</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
					<?php
					if(isset($_POST['search'])){
						$base = "SELECT 
							ipf.sno AS inv_sno,
							ipf.department_id, ipf.division_id, ipf.district_id, ipf.project_id, ipf.upload_date,
							upt.project_type, upt.project_name_hindi, upt.admin_go_no,
							d.department_name_hindi, 
							divv.division_name, 
							dist.district_name_hindi,
							(SELECT COUNT(*) FROM transaction_project_file tpf WHERE tpf.invoice_id = ipf.sno) AS files_count
						FROM invoice_project_file ipf
						LEFT JOIN uprnss_project_temp upt ON upt.sno = ipf.project_id
						LEFT JOIN uprnss_department_name d ON d.sno = ipf.department_id
						LEFT JOIN uprnss_division divv ON divv.s_no = ipf.division_id
						LEFT JOIN uprnss_district dist ON dist.sno = ipf.district_id
						WHERE 1=1";

						if($_SESSION['usertype']=='2' && !empty($_SESSION['department'])){
							$base .= " AND ipf.department_id IN (".implode(",", array_map('intval', $_SESSION['department'])).")";
						} else if(!empty($_SESSION['divisions'])){
							$base .= " AND ipf.division_id IN (".implode(",", array_map('intval', $_SESSION['divisions'])).")";
						}

						if($_POST['department']!=''){
							$base .= ' AND ipf.department_id="'.intval($_POST['department']).'"';
						}
						if($_POST['district']!=''){
							$base .= ' AND ipf.district_id="'.intval($_POST['district']).'"';
						}
						if($_POST['division_name']!=''){
							$base .= ' AND ipf.division_id="'.intval($_POST['division_name']).'"';
						}
						if($_POST['project_type']!=''){
							$base .= ' AND upt.project_type="'.intval($_POST['project_type']).'"';
						}

						$base .= " ORDER BY ipf.sno DESC";

						$result = execute_query($base);
						if(mysqli_error($db)){
							echo '<tr><td colspan="9" class="text-danger">Error: '.htmlspecialchars(mysqli_error($db)).'</td></tr>';
						} else {
							$i=1;
							while($row = mysqli_fetch_assoc($result)){
								$divName = htmlspecialchars($row['division_name'], ENT_QUOTES);
								$distName = htmlspecialchars($row['district_name_hindi'], ENT_QUOTES);
								$projName = htmlspecialchars($row['project_name_hindi'], ENT_QUOTES);

								echo '<tr>';
								echo '<td>'.($i++).'</td>';
								echo '<td>'.($row['project_type']=='1'?'शासन स्तर':($row['project_type']=='2'?'जिला स्तर':'' )).'</td>';
								echo '<td>'.htmlspecialchars($row['department_name_hindi']).'</td>';
								echo '<td>'.htmlspecialchars($row['division_name']).'</td>';
								echo '<td>'.htmlspecialchars($row['district_name_hindi']).'</td>';
								echo '<td>'.htmlspecialchars($row['project_name_hindi']).'<br><small>'.htmlspecialchars($row['admin_go_no']).'</small></td>';
								// echo '<td class="text-center"><span class="small text-muted">'.$row['files_count'].' files</span></td>';

								// 👁 View button with Bootstrap icon
								echo '<td class="text-center">
										<span class="icon-stack">
										  <button type="button" class="icon-btn btn-view" 
												  title="View Files"
												  data-invoice="'.$row['inv_sno'].'"
												  data-division="'.$divName.'"
												  data-district="'.$distName.'"
												  data-project="'.$projName.'"
												  data-count="'.$row['files_count'].'">
											<i class="fa fa-eye"></i>
										  </button>
										  <span class="icon-label">('.$row['files_count'].')</span>
										</span><br>
										<small>'.date("d-m-Y",strtotime($row['upload_date'])).'</small>
									  </td>';

								// ✏ Edit button with Bootstrap icon
								echo '<td class="text-center">
										<a class="icon-btn" href="project_photo_upload.php?edit_id='.$row['inv_sno'].'" title="Edit">
											<i class="far fa-edit"></i>
										</a>
									  </td>';
								echo '</tr>';
							}
							if($i===1){
								echo '<tr><td colspan="9" class="text-center text-muted">No records found.</td></tr>';
							}
						}
					}
					?>
					</tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <div class="w-100">
          <div id="filesModalTop" class="small text-white-50"></div>
          <h5 class="modal-title m-0" id="filesModalLabel"></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="filesModalBody">
        <div class="text-center text-muted">Loading...</div>
      </div>
    </div>
  </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imgPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex align-items-center justify-content-center">
        <img id="imgPreviewEl" src="" class="img-preview-full" alt="preview">
      </div>
    </div>
  </div>
</div>

<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>
$(document).ready(function () {
    var t = $('#general_stat_table').DataTable({ paging: false });

    $(document).on('click', '.btn-view', function(){
        var inv  = $(this).data('invoice');
        var cnt  = $(this).data('count');
        var divn = $(this).data('division') || '';
        var dist = $(this).data('district') || '';
        var proj = $(this).data('project') || '';

        // Header: Division • District (small) + Project (title)
        $('#filesModalTop').text(divn + (divn && dist ? ' • ' : '') + dist);
        $('#filesModalLabel').text(proj);

        $('#filesModalBody').html('<div class="text-center text-muted">Loading...</div>');
        $('#filesModal').modal('show');

        $.get('<?php echo $_SERVER["PHP_SELF"]; ?>', { ajax:'files', invoice_id: inv }, function(html){
            $('#filesModalBody').html(html);
        }).fail(function(){
            $('#filesModalBody').html('<div class="alert alert-danger">Failed to load files.</div>');
        });
    });

    // Click image to preview fullscreen
    $(document).on('click', '#filesModalBody img.file-thumb', function(){
        var src = $(this).attr('data-full') || $(this).attr('src');
        $('#imgPreviewEl').attr('src', src);
        $('#imgPreviewModal').modal('show');
    });
});
</script>
<?php		
page_footer_end();
