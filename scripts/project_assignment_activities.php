<?php
include("scripts/settings.php");
$msg = '';


function s($v) { global $db; return mysqli_real_escape_string($db, trim((string)($v ?? ''))); }

// Mapping for updating uprnss_project_temp after inserts/updates
	$map = [
		'SE' => ['name_col'=>'superintendent_engineer_name','from_col'=>'superintendent_engineer_from','to_col'=>'superintendent_engineer_to'],
		'EE' => ['name_col'=>'executive_engineer_name','from_col'=>'executive_engineer_from','to_col'=>'executive_engineer_to'],
		'AE' => ['name_col'=>'assistant_engineer_name','from_col'=>'assistant_engineer_from','to_col'=>'assistant_engineer_to'],
		'JE' => ['name_col'=>'junior_engineer_name','from_col'=>'junior_engineer_from','to_col'=>'junior_engineer_to'],
		'AC' => ['name_col'=>'accountant_name','from_col'=>'accountant_from','to_col'=>'accountant_to']
	];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $act = $_POST['ajax_action'];

    // fetch projects (paginated)
    if ($act === 'fetch_projects') {
        $erp = s($_POST['erp'] ?? '');
        $division = s($_POST['division'] ?? '');
        $page = max(1,(int)($_POST['page'] ?? 1));
        $pageSize = max(1,(int)($_POST['pageSize'] ?? 10));
        $offset = ($page-1)*$pageSize;

        $where = "WHERE COALESCE(apt.status,0) <> 5";
        if ($erp !== '') $where .= " AND apt.erp_code LIKE '%$erp%'";
        if ($division !== '') $where .= " AND apt.division_id='$division'";

        $cnt_sql = "SELECT COUNT(*) AS cnt FROM uprnss_project_temp apt $where";
        $cnt_res = execute_query($cnt_sql);
        $cnt_row = mysqli_fetch_assoc($cnt_res);
        $total = (int)($cnt_row['cnt'] ?? 0);
        $total_pages = (int)ceil($total / $pageSize);

        $sql = "SELECT apt.sno, apt.erp_code, apt.project_name_hindi, ud.division_name, apt.work_start_date, apt.work_completion_date
                FROM uprnss_project_temp apt
                LEFT JOIN uprnss_division ud ON ud.s_no = apt.division_id
                $where ORDER BY apt.sno DESC LIMIT $offset, $pageSize";
        $res = execute_query($sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

        echo json_encode(['success'=>true,'projects'=>$rows,'total'=>$total,'page'=>$page,'pageSize'=>$pageSize,'total_pages'=>$total_pages]);
        exit;
    }

    // fetch single project
    if ($act === 'fetch_project') {
        $sno = (int)($_POST['project_sno'] ?? 0);
        if (!$sno) { echo json_encode(['success'=>false]); exit; }
        $sql = "SELECT apt.*, ud.division_name FROM uprnss_project_temp apt LEFT JOIN uprnss_division ud ON ud.s_no = apt.division_id WHERE apt.sno='$sno' LIMIT 1";
        $res = execute_query($sql);
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['success'=>true,'project'=>$row]);
        exit;
    }

    // fetch employees lists for select boxes (grouped by role)
    if ($act === 'fetch_employees_lists') {
        // example: '{"SE":[4,7],"EE":[3],"AE":[2],"JE":[1,8]}'
        $raw = $_POST['designation_map'] ?? '';
        $map_in = ['SE'=>[4], 'EE'=>[3], 'AE'=>[2], 'JE'=>[1], 'AC'=>[9]];
        if ($raw) {
            $d = json_decode($raw, true);
            if (is_array($d)) {
                foreach (['SE','EE','AE','JE', 'AC'] as $r) {
                    if (!empty($d[$r]) && is_array($d[$r])) {
                        $map_in[$r] = array_map('intval', $d[$r]);
                    }
                }
            }
        }

        $lists = ['SE'=>[], 'EE'=>[], 'AE'=>[], 'JE'=>[], 'AC'=>[]];
        $sql = "SELECT p.sno AS emp_sno, p.full_name, COALESCE(p.employee_designation_id,0) AS did, d.designation
                FROM dp_personal_info p
                LEFT JOIN dp_designation d ON d.sno = p.employee_designation_id
                WHERE COALESCE(p.status,1) <> 0
                ORDER BY p.full_name";
        $res = execute_query($sql);
        while ($r = mysqli_fetch_assoc($res)) {
            $did = (int)($r['did'] ?? 0);
            $entry = ['sno'=> (int)$r['emp_sno'], 'name'=> $r['full_name'], 'designation'=> $r['designation']];
            if (in_array($did, $map_in['SE'], true)) { $lists['SE'][] = $entry; continue; }
            if (in_array($did, $map_in['EE'], true)) { $lists['EE'][] = $entry; continue; }
            if (in_array($did, $map_in['AE'], true)) { $lists['AE'][] = $entry; continue; }
            if (in_array($did, $map_in['JE'], true)) { $lists['JE'][] = $entry; continue; }
            if (in_array($did, $map_in['AC'], true)) { $lists['AC'][] = $entry; continue; }
        }
        echo json_encode(['success'=>true,'lists'=>$lists]);
        exit;
    }

    // fetch existing entries for a given section (role) and project
    if ($act === 'fetch_entries_for_section') {
        $project_sno = (int)($_POST['project_sno'] ?? 0);
        $officer_type = s($_POST['officer_type'] ?? '');
        if (!$project_sno || $officer_type==='') { echo json_encode(['success'=>true,'entries'=>[]]); exit; }
        $sql = "SELECT id, officer_type, dp_personal_info_id, DATE_FORMAT(from_date,'%Y-%m-%d') AS from_date, DATE_FORMAT(to_date,'%Y-%m-%d') AS to_date
                FROM project_officers
                WHERE project_sno='$project_sno' AND officer_type='$officer_type'
                ORDER BY from_date DESC";
        $res = execute_query($sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        echo json_encode(['success'=>true,'entries'=>$rows]);
        exit;
    }

    // save new rows for a given section (inserts)
    if ($act === 'save_section') {
        $project_sno = (int)($_POST['project_sno'] ?? 0);
        $erp_code = s($_POST['erp_code'] ?? '');
        $officer_type = s($_POST['officer_type'] ?? '');
        $entries = $_POST['entries'] ?? [];
        if (!$project_sno || $officer_type==='' || !is_array($entries)) { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }

        // prevent new insert if latest exists without to_date
        $chk_sql = "SELECT id, to_date, DATE_FORMAT(from_date,'%Y-%m-%d') AS from_date FROM project_officers WHERE project_sno='$project_sno' AND officer_type='$officer_type' ORDER BY from_date DESC LIMIT 1";
        $chk_res = execute_query($chk_sql);
        $chk_row = mysqli_fetch_assoc($chk_res);
        if ($chk_row && empty($chk_row['to_date'])) {
            echo json_encode(['success'=>false,'message'=>'Cannot add new entry. Previous entry (from '.$chk_row['from_date'].') has empty To Date. Update it first.']);
            exit;
        }

        $errors = [];
        foreach ($entries as $e) {
            $oname = s($e['officer_name'] ?? '');
            $fromd = s($e['from_date'] ?? '');
            $tod = s($e['to_date'] ?? '');
            if ($oname === '' || $fromd === '') continue;
            $tod_sql = ($tod === '' ? "NULL" : "'$tod'");
            $sql = "INSERT INTO project_officers (project_sno, erp_code, officer_type, dp_personal_info_id, from_date, to_date, created_by)
                    VALUES ('$project_sno', '$erp_code', '$officer_type', '$oname', '$fromd', $tod_sql, '".($_SESSION['usersno'] ?? 'NULL')."')";
            execute_query($sql);
            if (mysqli_error($db)) $errors[] = mysqli_error($db);
        }

        // update project summary for this role
        if (isset($map[$officer_type])) {
            $m = $map[$officer_type];
            $update_sql = "
                UPDATE uprnss_project_temp p
                LEFT JOIN (
                    SELECT project_sno, dp_personal_info_id AS latest_name, from_date AS latest_from, to_date AS latest_to
                    FROM project_officers
                    WHERE project_sno = '{$project_sno}' AND officer_type = '{$officer_type}'
                    ORDER BY from_date DESC LIMIT 1
                ) t ON t.project_sno = p.sno
                SET p.`{$m['name_col']}` = COALESCE(t.latest_name, p.`{$m['name_col']}`),
                    p.`{$m['from_col']}` = COALESCE(t.latest_from, p.`{$m['from_col']}`),
                    p.`{$m['to_col']}` = COALESCE(t.latest_to, DATE_ADD(t.latest_from, INTERVAL 2 MONTH))
                WHERE p.sno = '{$project_sno}'";
            execute_query($update_sql);
            if (mysqli_error($db)) $errors[] = mysqli_error($db);
        }

        if (count($errors)>0) echo json_encode(['success'=>false,'message'=>implode('; ', $errors)]);
        else echo json_encode(['success'=>true]);
        exit;
    }

    // update an existing entry (inline edit)
    if ($act === 'update_entry') {
        $id = (int)($_POST['id'] ?? 0);
        $officer_type = s($_POST['officer_type'] ?? '');
        $officer_name = s($_POST['officer_name'] ?? '');
        $from_date = s($_POST['from_date'] ?? '');
        $to_date = s($_POST['to_date'] ?? '');
        $project_sno = (int)($_POST['project_sno'] ?? 0);
        if (!$id || $officer_type==='' || $officer_name==='' || $from_date==='') { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }
        $sql = "UPDATE project_officers SET officer_type='$officer_type', dp_personal_info_id='$officer_name', from_date='$from_date', to_date=" . ($to_date===''?"NULL":"'$to_date'") . ", updated_at='".date('Y-m-d H:i:s')."' WHERE id='$id'";
        execute_query($sql);
        if (mysqli_error($db)) { echo json_encode(['success'=>false,'message'=>mysqli_error($db)]); exit; }

        // update project summary for this role if project_sno provided
        if ($project_sno && isset($map[$officer_type])) {
            $m = $map[$officer_type];
            $update_sql = "
                UPDATE uprnss_project_temp p
                LEFT JOIN (
                    SELECT project_sno, dp_personal_info_id AS latest_name, from_date AS latest_from, to_date AS latest_to
                    FROM project_officers
                    WHERE project_sno = '{$project_sno}' AND officer_type = '{$officer_type}'
                    ORDER BY from_date DESC LIMIT 1
                ) t ON t.project_sno = p.sno
                SET p.`{$m['name_col']}` = COALESCE(t.latest_name, p.`{$m['name_col']}`),
                    p.`{$m['from_col']}` = COALESCE(t.latest_from, p.`{$m['from_col']}`),
                    p.`{$m['to_col']}` = COALESCE(t.latest_to, DATE_ADD(t.latest_from, INTERVAL 2 MONTH))
                WHERE p.sno = '{$project_sno}'";
            execute_query($update_sql);
        }

        echo json_encode(['success'=>true]);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Unknown action']);
    exit;
}

// ---------- PAGE UI ----------
page_header_start();
page_header_end();
page_sidebar();

$divisions = [];

$where = "WHERE division_name IS NOT NULL AND division_name <> ''";

if ($_SESSION['usertype'] == 1 && !empty($_SESSION['divisions'])) {
    $divisionIds = implode(",", $_SESSION['divisions']);
    $where .= " AND s_no IN ($divisionIds)";
}

$sql = "SELECT s_no, division_name 
        FROM uprnss_division 
        $where 
        ORDER BY division_name";

$resDiv = execute_query($sql);

while ($d = mysqli_fetch_assoc($resDiv)) {
    $divisions[] = $d;
}

?>
<!-- UI: Search + Editor with four sections -->
<style>
/* ---------- Modal / container ---------- */
.modal-back {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: none;
  z-index: 1050;
}
.modal-box {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 95%;
  max-width: 1200px;
  max-height: 90vh;
  overflow: auto;
  background: #fff;
  border-radius: 8px;
  padding: 16px;
  z-index: 1060;
}

/* ---------- Section card & header ---------- */
.card-section {
  border: 1px solid #e6e9ec;
  border-radius: 6px;
  padding: 12px;
  margin-bottom: 12px;
  background: #fff;
}
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

/* ---------- Table (project list) ---------- */
.table-min {
  width: 100%;
  border-collapse: collapse;
}
.table-min th {
  background: #f1f3f5;
  padding: 10px;
  border: 1px solid #e6eaee;
  text-align: left;
}

/* ---------- Form-field layout inside rows ---------- */
.field-wrap { display: flex; flex-direction: column; }
.field-label { font-size: 12px; color: #555; margin-bottom: 6px; }

/* Name column */
.col-name { flex: 0 0 320px; min-width: 220px; max-width: 420px; }
.col-name > input, .col-name > select { width: 100%; box-sizing: border-box; }

/* Date columns */
.col-from, .col-to { flex: 0 0 150px; min-width: 120px; }

/* Action column */
.col-action { width: 120px; text-align: center; }

/* Small helpers */
.btn-compact { padding: 6px 10px; font-size: 13px; }
.small-note { font-size: 12px; color: #666; }

/* Pagination */
.paging { display: flex; gap: 6px; align-items: center; margin-top: 8px; flex-wrap: wrap; }
.page-btn { padding: 6px 10px; border-radius: 4px; border: 1px solid #ddd; background: #fff; cursor: pointer; }
.page-btn.active { background: #007bff; color: #fff; border-color: #007bff; }

/* Editor header */
.editor-header { display: flex; gap: 12px; align-items: flex-start; }
.editor-title { font-weight: 600; font-size: 16px; }
.editor-project-name { font-size: 15px; color: #222; line-height: 1.3; max-width: 840px; word-break: break-word; margin-top: 6px; }

/* Calendar button */
.cal-btn {
  display: inline-flex; align-items: center; justify-content: center;
  border: 1px solid #d0d3d6; background: #fff; padding: 4px 8px;
  border-radius: 4px; cursor: pointer; margin-left: 6px; height: 34px;
}
.cal-btn:hover { background: #f6f7f8; }

/* Responsive */
@media (max-width: 880px) {
  .card-section { padding: 10px; }
  .col-name { flex: 1 1 100%; min-width: 0; max-width: none; }
  .col-from, .col-to { flex: 0 0 45%; min-width: 120px; }
  .field-wrap { margin-bottom: 6px; }
}
</style>

<div class="row mb-3">
  <div class="col-md-12">
    <div class="card shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Assignment of Officials</h4>
          <small class="text-muted">Manage SE / EE / AE / JE / AC assignments</small>
        </div>
        <div>
          <button id="open_search_modal" class="btn btn-primary">Search Project</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search modal -->
<div class="modal-back" id="search_modal">
  <div class="modal-box">
    <div class="header-row">
      <strong>Search Project (ERP Code / Division)</strong>
      <button id="close_search_modal" class="btn btn-outline-secondary btn-sm">Close</button>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:10px">
      <input id="modal_filter_erp" class="form-control" placeholder="ERP code">
      <select id="modal_filter_division" class="form-control">
        <option value="">-- All Divisions --</option>
        <?php foreach($divisions as $dv): ?>
          <option value="<?php echo s($dv['s_no']); ?>"><?php echo htmlspecialchars($dv['division_name']); ?></option>
        <?php endforeach; ?>
      </select>
      <button id="modal_btn_search" class="btn btn-primary">Search</button>
    </div>

    <div style="max-height:360px;overflow:auto">
      <table class="table table-sm table-bordered table-min">
        <thead>
          <tr><th>Sr.No</th><th>ERP Code</th><th>Project Name</th><th>Division</th><th>Start</th><th>Completion</th><th>Action</th></tr>
        </thead>
        <tbody id="modal_projects_tbody"></tbody>
      </table>
    </div>

    <div id="modal_pagination" class="paging"></div>
  </div>
</div>

<!-- Editor (shows when project opened) -->
<div id="editor_card" style="display:none">
  <div class="card">
    <div class="card-header">
      <div class="editor-header">
        <div>
          <div id="editor_title" class="editor-title">Project Name </div>
          <div id="editor_project_code" class="text-muted" style="font-size:16px bold;margin-top:6px"></div>
        </div>
        <div style="flex:1">
          <div id="editor_project_name" class="editor-project-name"></div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <!-- four sections -->
      <?php $sections = [
	  'SE'=>'Superintending Engineer (SE)', 
	  'EE'=>'Executive Engineer (EE)', 
	  'AE'=>'Assistant Engineer (AE)', 
	  'JE'=>'Junior Engineer (JE)',
	  'AC'=>'Accountant(AC)'
	  ]; ?>
      <div id="sections_container">
        <?php foreach ($sections as $code => $label): ?>
        <div class="card-section" data-section="<?php echo $code; ?>">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <strong><?php echo $label; ?></strong>
            <div>
              <button class="btn btn-sm btn-outline-primary add-row" data-section="<?php echo $code; ?>">+ Add Row</button>
            </div>
          </div>

          <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px">
            <div class="field-wrap col-name"><span class="field-label">Name</span></div>
            <div class="field-wrap col-from"><span class="field-label">From</span></div>
            <div class="field-wrap col-to"><span class="field-label">To</span></div>
            <div class="field-wrap col-action"><span class="field-label">Action</span></div>
          </div>

          <div class="existing-<?php echo $code; ?>">Loading...</div>

          <hr>

          <div class="add-area" id="addarea_<?php echo $code; ?>">
            <div class="mt-2" style="text-align:right">
              <button class="btn btn-sm btn-success save-section" data-section="<?php echo $code; ?>">Save New Row(s)</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>

<?php page_footer_start(); ?>

<script>
const sections = ['SE','EE','AE','JE', 'AC'];
let employeesLists = null;
let currentProjectSno = 0;
let currentProjectErp = '';
let modalState = { page: 1, pageSize: 10, total_pages: 0, total: 0 };

function esc(s){
  if (!s && s !== 0) return '';
  return String(s).replace(/[&<>"'\/]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;'}[ch]));
}

$(function(){
  // Initial load
  loadProjects(1);
  $('#search_modal').fadeIn(120);

  // Open search modal (hide editor if open)
  $('#open_search_modal').on('click', () => {
    $('#editor_card').hide();
    loadProjects(1);
    $('#search_modal').fadeIn(120);
  });

  // Close modal
  $('#close_search_modal').on('click', () => $('#search_modal').fadeOut(120));
  $('#modal_btn_search').on('click', () => { $('#editor_card').hide(); loadProjects(1); });

  // Delegated handlers
  $(document)
    .on('click', '.page-btn', function(){ const p = parseInt($(this).data('page')); if (p) loadProjects(p); })
    .on('click', '.open-project', function(){
      const sno = $(this).closest('tr').data('sno');
      fetchProjectAndOpen(sno);
    })
    .on('click', '.add-row', function(){
      const sec = $(this).data('section');
      $('#addarea_'+sec+' .new-rows').append(renderNewRow(sec));
      initDatepickers($('#addarea_'+sec+' .new-rows'));
    })
    .on('click', '.remove-new', function(){ $(this).closest('.new-row').remove(); })
    .on('click', '.save-section', function(){
      const sec = $(this).data('section');
      saveSectionNewRows(sec);
    })
    .on('click', '.update-exist', function(){
      const id = $(this).data('id');
      const type = $(this).data('type');
      const $row = $(this).closest('.exist-row');
      const name = $row.find('.name_select').length ? $row.find('.name_select').val() : $row.find('.name_manual').val();
      const from = $row.find('.from').val();
      const to = $row.find('.to').val();
      if (!name || !from) { alert('Name and From date required'); return; }
      $.post('', { ajax_action: 'update_entry', id, officer_type: type, officer_name: name, from_date: from, to_date: to, project_sno: currentProjectSno }, 'json')
        .done(res => { if (!res.success) alert('Update failed: ' + (res.message||'')); else { alert('Updated'); renderExisting(type); } })
        .fail(() => alert('Update request failed'));
    });

  // Fetch employees lists (returns jQuery promise)
  function fetchEmployees(force = false){
    if (employeesLists && !force) return $.Deferred().resolve(employeesLists).promise();
    return $.post('', { ajax_action: 'fetch_employees_lists' }, 'json')
      .done(res => { employeesLists = (res && res.success && res.lists) ? res.lists : {SE:[],EE:[],AE:[],JE:[]}; })
      .fail(() => { employeesLists = {SE:[],EE:[],AE:[],JE:[],AC:[] }; });
  }

  // Load projects with pagination
  function loadProjects(page = 1){
    modalState.page = page || 1;
    $('#modal_projects_tbody').html('<tr><td colspan="7">Loading...</td></tr>');
    $('#modal_pagination').html('');
    $.post('', { ajax_action: 'fetch_projects', erp: $('#modal_filter_erp').val().trim(), division: $('#modal_filter_division').val(), page: modalState.page, pageSize: modalState.pageSize }, 'json')
      .done(res => {
        if (!res || !res.success) { $('#modal_projects_tbody').html('<tr><td colspan="7">Error</td></tr>'); return; }
        modalState.total = res.total || 0;
        modalState.total_pages = res.total_pages || 0;
        modalState.page = res.page || 1;
        const rows = res.projects || [];
        if (!rows.length) { $('#modal_projects_tbody').html('<tr><td colspan="7">No projects found</td></tr>'); $('#modal_pagination').html(''); return; }

        const startIndex = (modalState.page - 1) * modalState.pageSize;
        let h = '';
        rows.forEach((r, idx) => {
          const srno = startIndex + idx + 1;
          h += '<tr data-sno="'+r.sno+'" data-erp="'+esc(r.erp_code||'')+'">';
          h += '<td>'+srno+'</td><td>'+esc(r.erp_code||'')+'</td><td>'+esc(r.project_name_hindi||'')+'</td><td>'+esc(r.division_name||'')+'</td><td>'+esc(r.work_start_date||'')+'</td><td>'+esc(r.work_completion_date||'')+'</td>';
          h += '<td><button class="btn btn-sm btn-outline-primary open-project" data-sno="'+r.sno+'">Open</button></td></tr>';
        });
        $('#modal_projects_tbody').html(h);

        // build pagination
        let pagHtml = '';
        const start = Math.max(1, modalState.page - 3);
        const end = Math.min(modalState.total_pages, start + 6);
        if (modalState.page > 1) pagHtml += '<button class="page-btn" data-page="'+(modalState.page-1)+'">Prev</button>';
        for (let p = start; p <= end; p++) pagHtml += '<button class="page-btn '+(p===modalState.page?'active':'')+'" data-page="'+p+'">'+p+'</button>';
        if (modalState.page < modalState.total_pages) pagHtml += '<button class="page-btn" data-page="'+(modalState.page+1)+'">Next</button>';
        pagHtml += ' <span style="margin-left:12px;color:#666">Showing page '+modalState.page+' of '+modalState.total_pages+' (Total: '+modalState.total+')</span>';
        $('#modal_pagination').html(pagHtml);
      })
      .fail(() => { $('#modal_projects_tbody').html('<tr><td colspan="7">Error</td></tr>'); });
  }

  // Fetch single project and open editor
  function fetchProjectAndOpen(sno){
    $.post('', { ajax_action: 'fetch_project', project_sno: sno }, 'json')
      .done(res => {
        if (!res || !res.success) { alert('Cannot fetch project'); return; }
        currentProjectSno = sno;
        currentProjectErp = res.project.erp_code || '';
        $('#editor_card').show();
        
        $('#editor_project_code').text(res.project.erp_code || ('SNO '+sno));
        $('#editor_project_name').text((res.project.project_name_hindi||'') + ' | Division: ' + (res.project.division_name||''));
        fetchEmployees().then(() => { renderAllSections(); });
        $('#search_modal').fadeOut(120);
        $('html,body').animate({ scrollTop: $('#editor_card').offset().top - 60 }, 300);
      })
      .fail(() => alert('Request failed'));
  }

  // Render all sections
  function renderAllSections(){
    sections.forEach(sec => {
      renderExisting(sec);
      $('#addarea_'+sec).find('.new-rows').remove();
      $('#addarea_'+sec).prepend('<div class="new-rows"></div>');
      $('#addarea_'+sec+' .new-rows').append(renderNewRow(sec));
      initDatepickers($('#addarea_'+sec+' .new-rows'));
    });
  }

  // Render existing entries for a section
  function renderExisting(sec){
    const container = $('.card-section[data-section="'+sec+'"] .existing-'+sec);
    container.html('Loading...');
    $.post('', { ajax_action: 'fetch_entries_for_section', project_sno: currentProjectSno, officer_type: sec }, 'json')
      .done(res => {
        if (!res || !res.success) { container.html('Error'); return; }
        const rows = res.entries || [];
        

        let html = '<div>';
        rows.forEach(r => {
          // prefer dp_personal_info_id if present; otherwise officer_name string
          const selected = (r.dp_personal_info_id ? String(r.dp_personal_info_id) : (r.officer_name || ''));
          html += '<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px" class="exist-row" data-id="'+r.id+'">';
          html += '<div class="field-wrap col-name"><label class="field-label" style="display:none">Name</label>' + makeNameControl(sec, selected) + '</div>';
          html += '<div class="field-wrap col-from"><label class="field-label" style="display:none">From</label><div style="display:flex;align-items:center"><input type="text" class="form-control datepicker from" value="'+esc(r.from_date||'')+'" readonly /><button type="button" class="cal-btn" title="Open calendar">📅</button></div></div>';
          html += '<div class="field-wrap col-to"><label class="field-label" style="display:none">To</label><div style="display:flex;align-items:center"><input type="text" class="form-control datepicker to" value="'+esc(r.to_date||'')+'" readonly /><button type="button" class="cal-btn" title="Open calendar">📅</button></div></div>';
          html += '<div class="field-wrap col-action"><label class="field-label" style="display:none">Action</label><button class="btn btn-sm btn-outline-secondary update-exist" data-id="'+r.id+'" data-type="'+sec+'">Update</button></div>';
          html += '</div>';
        });
        html += '</div>';
        container.html(html);
        initDatepickers(container);
      })
      .fail(() => container.html('Error'));
  }

  // Build name control: select values are employee.sno; fallback to manual input
  function makeNameControl(sec, selectedIdOrName){
    const list = employeesLists && employeesLists[sec] ? employeesLists[sec] : [];
    if (!list || !list.length) {
      return '<input class="form-control name_manual" value="'+esc(selectedIdOrName||'')+'" />';
    }
    let s = '<select class="form-control name_select">';
    s += '<option value="">-- Select --</option>';
    let found = false;
    list.forEach(it => {
      const sel = (String(it.sno) === String(selectedIdOrName)) ? ' selected' : '';
      if (String(it.sno) === String(selectedIdOrName)) found = true;
      s += '<option value="'+esc(it.sno)+'"'+sel+'>'+esc(it.name + (it.designation ? ' ('+it.designation+')' : ''))+'</option>';
    });
    // if existing value isn't in list, show it as a manual option (so manual entries aren't lost)
    if (selectedIdOrName && !found) {
      s += '<option value="'+esc(selectedIdOrName)+'" selected>'+esc(selectedIdOrName + ' (manual)')+'</option>';
    }
    s += '</select>';
    return s;
  }

  // Render a new row (for adding)
  function renderNewRow(sec){
    const list = employeesLists && employeesLists[sec] ? employeesLists[sec] : [];
    let nameControl = '';
    if (!list || !list.length) nameControl = '<input class="form-control name_manual_new" placeholder="Officer Name">';
    else {
      nameControl = '<select class="form-control name_select_new"><option value="">-- Select --</option>';
      list.forEach(it => { nameControl += '<option value="'+esc(it.sno)+'">'+esc(it.name + (it.designation ? ' ('+it.designation+')' : ''))+'</option>'; });
      nameControl += '</select>';
    }
    return '<div class="new-row" style="display:flex;gap:8px;align-items:center;margin-top:8px">'
         + '<div class="field-wrap col-name"><label class="field-label" style="display:none">Name</label>'+nameControl+'</div>'
         + '<div class="field-wrap col-from"><label class="field-label" style="display:none">From</label><div style="display:flex;align-items:center"><input type="text" class="form-control datepicker from_new" readonly /><button type="button" class="cal-btn" title="Open calendar">📅</button></div></div>'
         + '<div class="field-wrap col-to"><label class="field-label" style="display:none">To</label><div style="display:flex;align-items:center"><input type="text" class="form-control datepicker to_new" readonly /><button type="button" class="cal-btn" title="Open calendar">📅</button></div></div>'
         + '<div class="field-wrap col-action"><label class="field-label" style="display:none">Action</label> <button class="btn btn-sm btn-outline-danger remove-new" title="Remove">-</button></div>'
         + '</div>';
  }

  // Collect new rows and send to server
  function saveSectionNewRows(sec){
    const rows = [];
    $('#addarea_'+sec+' .new-rows .new-row').each(function(){
      const $r = $(this);
      let nameVal = '';
      if ($r.find('.name_select_new').length) {
        nameVal = $r.find('.name_select_new').val();
        if (!nameVal && $r.find('.name_manual_new').length) nameVal = $r.find('.name_manual_new').val();
      } else {
        nameVal = $r.find('.name_manual_new').val();
      }
      const from = $r.find('.from_new').val();
      const to = $r.find('.to_new').val();
      if (nameVal && from) rows.push({ officer_name: nameVal, from_date: from, to_date: to });
    });
    if (!rows.length) { alert('Add at least one row with Name & From date'); return; }

    $.post('', { ajax_action: 'save_section', project_sno: currentProjectSno, erp_code: currentProjectErp, officer_type: sec, entries: rows }, 'json')
      .done(res => {
        if (!res || !res.success) { alert('Save failed: ' + (res && res.message||'')); return; }
        alert('Saved');
        renderExisting(sec);
        $('#addarea_'+sec+' .new-rows').html(renderNewRow(sec));
        initDatepickers($('#addarea_'+sec+' .new-rows'));
      })
      .fail(() => alert('Save request failed'));
  }

  // Initialize datepickers inside given container
  function initDatepickers(container){
    const $c = (container.jquery ? container : $(container));
    $c.find('.datepicker').each(function(){
      const $inp = $(this);
      if ($inp.data('dp-initialized')) return;
      try {
        $inp.datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, yearRange: "2000:2030" });
        $inp.data('dp-initialized', true);
      } catch(e){}
    });
    $c.find('.cal-btn').off('click').on('click', function(){
      const $btn = $(this);
      const $inp = $btn.prevAll('.datepicker').first();
      if ($inp.length) try { $inp.datepicker('show'); } catch(e){}
    });
  }

  // Ensure datepicker initialize on focus (delegation)
  $(document).on('focus', '.datepicker', function(){
    const $this = $(this);
    if (!$this.data('dp-initialized')) {
      try {
        $this.datepicker({ dateFormat:'yy-mm-dd', changeMonth:true, changeYear:true, yearRange:"2000:2030" });
        $this.data('dp-initialized', true);
      } catch(e){}
    }
  });

}); // end $(function)
</script>

<?php page_footer_end(); ?>
