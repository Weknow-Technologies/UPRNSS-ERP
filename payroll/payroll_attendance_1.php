<?php
include("scripts/settings.php");
$msg = '';
$tab = 1;

// Function to get previous attendance summary
function get_employee_attendance($emp_id, $financial_year, $salary_month) {
    global $db;
    $sql = 'SELECT d.*, i.company_id, c.division_name 
            FROM attendance_details d 
            LEFT JOIN attendance_invoice i ON d.attendance_id = i.sno
            LEFT JOIN cloudice_uprnss.uprnss_division c ON i.company_id = c.s_no
            WHERE d.emp_id = "'.$emp_id.'" 
            AND d.financial_year = "'.$financial_year.'" 
            AND d.salary_generation_month = "'.$salary_month.'"';
    $res = mysqli_query($db, $sql);
    $summary = ['present' => 0, 'leave' => 0, 'total' => 0, 'records' => []];

    while($row = mysqli_fetch_assoc($res)) {
        $summary['present'] += (int)$row['presented_days'];
        $summary['leave'] += (int)$row['granted_leave'];
        $summary['total'] += (int)$row['presented_days'] + (int)$row['granted_leave'];
        $summary['records'][] = [
            'division' => $row['division_name'],
            'present'  => (int)$row['presented_days'],
            'leave'    => (int)$row['granted_leave'],
            'remaining'=> (int)$row['working_days'] - ((int)$row['presented_days'] + (int)$row['granted_leave'])
        ];
    }
    return $summary;
}

// Function to get number of days in a month
function get_days_in_month($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

if(isset($_POST['submit'])) {
    $working_days_month = get_days_in_month((int)$_POST['for_month'], (int)substr($_POST['attendance_session'],0,4));

    $sql_emp = 'SELECT e.*, d.designation AS designation_name, d.sort_no 
                FROM employee e
                LEFT JOIN cloudice_uprnss.dp_designation d ON e.employee_designation = d.sno
                WHERE e.company_id="'.$_POST['company_id'].'" AND e.working_status!="1"';
    if($_POST['employee_category_id'] != '') $sql_emp .= ' AND e.employee_category_id="'.$_POST['employee_category_id'].'"';
    $sql_emp .= ' ORDER BY d.sort_no ASC, e.employee_name ASC';

    $res_emp = execute_query($sql_emp);
    $emp_sno = [];
    while($rowemp = mysqli_fetch_array($res_emp)){
        $emp_sno[] = $rowemp['sno'];
    }

    if(count($emp_sno) > 0){
        $attendance_id = 0;
        $sql_invoice = 'INSERT INTO attendance_invoice(company_id, working_days, financial_year, salary_generation_month, created_by, creation_time) 
                        VALUES ("'.$_POST['company_id'].'","'.$working_days_month.'","'.$_POST['attendance_session'].'","'.$_POST['for_month'].'","'.$_SESSION['username'].'","'.date('Y-m-d H:i:s').'")';
        execute_query($sql_invoice);
        if(!mysqli_error($db)) $attendance_id = mysqli_insert_id($db);

        $res_emp = execute_query($sql_emp);
        while($rowemp = mysqli_fetch_array($res_emp)){
            $att_data = get_employee_attendance($rowemp['sno'], $_POST['attendance_session'], $_POST['for_month']);
            $remaining_days = $working_days_month - $att_data['total'];
            if($remaining_days <= 0) continue;

            $present = isset($_POST['present_'.$rowemp['sno']]) ? (int)$_POST['present_'.$rowemp['sno']] : 0;
            $leave   = isset($_POST['granted_leave_'.$rowemp['sno']]) ? (int)$_POST['granted_leave_'.$rowemp['sno']] : 0;
            $remaining   = isset($_POST['remaining_'.$rowemp['sno']]) ? (int)$_POST['remaining_'.$rowemp['sno']] : 0;

            // Ensure no negative or exceeding remaining
            if($present < 0) $present = 0;
            if($leave < 0) $leave = 0;
            if($present > $remaining_days){
                $present = $remaining_days;
                $leave = 0;
            }
            if($present + $leave > $remaining_days){
                $leave = max(0, $remaining_days - $present);
            }

            $sql_insert = 'INSERT INTO attendance_details(financial_year, salary_generation_month, attendance_id, company_id, emp_id, working_days, granted_leave, presented_days, created_by, creation_time) 
                           VALUES ("'.$_POST['attendance_session'].'","'.$_POST['for_month'].'","'.$attendance_id.'","'.$_POST['company_id'].'","'.$rowemp['sno'].'","'.$working_days_month.'","'.$leave.'","'.$present.'","'.$_SESSION['username'].'","'.date('Y-m-d H:i:s').'")';
            execute_query($sql_insert);
        }
    }

    $msg = '<div class="alert alert-success">Attendance Processed Successfully</div>';
}

page_header_start('Attendance');
page_header_end();
navigation($_SERVER['PHP_SELF']);
?>

<style>
.att-table th, .att-table td { vertical-align: middle; }
.small-note { font-size:12px; color:#666; display:block; }
.bg-full { background: #f8d7da; } 
.input-sm { padding:6px; height:34px; }
.prev-summary { font-size:12px; color:#555; display:block; margin-top:3px; }
</style>

<div class="container">
    <?php echo $msg; ?>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
        <!-- Filters Table -->
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <div class="panel">
                    <div class="panel-heading">Attendance</div>
                    <div class="panel-body">
                        <table class="table table-hover table-bordered table-striped">
                            <tr>
                                <td>Select Division</td>
                                <td>
                                    <select required class="form-control" name="company_id" id="company_id">
                                        <option value="">Select</option>
                                        <?php
                                        $sql = "SELECT * FROM uprnss_division ORDER BY division_name ASC";
                                        $q = mysqli_query($db_erp, $sql);
                                        while($row = mysqli_fetch_array($q)){
                                            echo '<option value="'.$row['s_no'].'"';
                                            if(isset($_POST['company_id']) && $_POST['company_id']==$row['s_no']) echo ' selected';
                                            echo '>'.$row['division_name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Employee Type</td>
                                <td>
                                    <select required name="employee_category_id" class="form-control">
                                        <option value="">Select</option>
                                        <?php
                                        $sql = "SELECT * FROM dp_category";
                                        $q = mysqli_query($db_erp, $sql);
                                        while($row = mysqli_fetch_array($q)){
                                            echo '<option value="'.$row['sno'].'"';
                                            if(isset($_POST['employee_category_id']) && $_POST['employee_category_id']==$row['sno']) echo ' selected';
                                            echo '>'.$row['category_name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Attendance Session</td>
                                <td>
                                    <select name="attendance_session" class="form-control">
                                        <?php
                                        $current_year = date('Y');
                                        $session_start = $current_year - 50;
                                        $select_start_session = (date('m') > 3) ? $current_year : $current_year-1;
                                        for($i=$session_start;$i<=$session_start+100;$i++){
                                            $end = $i+1;
                                            echo '<option value="'.$i.'-'.$end.'"';
                                            if(isset($_POST['attendance_session']) && $_POST['attendance_session']==$i.'-'.$end) echo ' selected';
                                            elseif($i==$select_start_session) echo ' selected';
                                            echo '>'.$i.'-'.$end.'</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>For Month</td>
                                <td>
                                    <select name="for_month" class="form-control">
                                        <?php
                                        $cur_month = date('m');
                                        for($m=1;$m<=12;$m++){
                                            echo '<option value="'.str_pad($m,2,'0',STR_PAD_LEFT).'"';
                                            if(isset($_POST['for_month']) && $_POST['for_month']==$m) echo ' selected';
                                            elseif(!isset($_POST['for_month']) && $m==$cur_month) echo ' selected';
                                            echo '>'.date('F',strtotime("01.$m.2020")).'</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <input type="submit" class="btn btn-info form-control" name="calculate" value="Search">
            </div>
        </div>

        <?php if(isset($_POST['calculate'])): 
            $working_days_month = get_days_in_month((int)$_POST['for_month'], (int)substr($_POST['attendance_session'],0,4));
            $sql_emp = 'SELECT e.*, d.designation AS designation_name, d.sort_no 
                        FROM employee e 
                        LEFT JOIN cloudice_uprnss.dp_designation d ON e.employee_designation = d.sno
                        WHERE e.company_id="'.$_POST['company_id'].'" AND e.working_status!="1"';
            if($_POST['employee_category_id']!='') $sql_emp .= ' AND e.employee_category_id="'.$_POST['employee_category_id'].'"';
            $sql_emp .= ' ORDER BY abs(d.sort_no) ASC, e.employee_name ASC';
            $res_emp = execute_query($sql_emp);
            $n=0;
        ?>
        <div class="row" style="margin-top:15px;">
            <div class="col-sm-12">
                <div class="panel">
                    <div class="panel-heading">Result</div>
                    <div class="panel-body">
                        <div class="table-responsive">
                        <table class="table table-bordered att-table">
                            <thead style="background:#f1f5f9;">
                                <tr class="text-center">
                                    <th>S.No.</th>
                                    <th>Emp Code</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Working Days</th>
                                    <th>Presented</th>
                                    <th>Leave</th>
                                    <th>Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php while($emp=mysqli_fetch_array($res_emp)):
                            $att = get_employee_attendance($emp['sno'], $_POST['attendance_session'], $_POST['for_month']);
                            $remaining_capacity = max(0, $working_days_month - $att['total']);
                            $full_marked = $remaining_capacity <= 0;
                            $n++;
                        ?>
                                <tr class="<?php if($full_marked) echo 'bg-full'; ?>">
                                    <td class="text-center"><strong><?php echo $n; ?>.</strong></td>
                                    <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
                                    <td>
										<?php echo htmlspecialchars($emp['employee_name']); ?>
										<?php if(!empty($att['records'])): // Show only if previous attendance exists ?>
										<span class="prev-summary">
											<?php 
											$prev = $att['records'];
											$divs = $p = $l = [];
											foreach($prev as $r){
												$divs[] = $r['division'];
												$p[] = $r['present'];
												$l[] = $r['leave'];
											}
											echo 'Div: '.implode(',',$divs).' | P: '.implode(',',$p).' | L: '.implode(',',$l);
											?>
										</span>
										<?php endif; ?>
									</td>
                                    <td><?php echo htmlspecialchars($emp['designation_name']); ?></td>
                                    <td class="text-center"><input type="number" class="form-control input-sm" value="<?php echo $working_days_month; ?>" readonly></td>
                                    <td><input type="number" min="0" class="form-control input-sm present_input" name="present_<?php echo $emp['sno']; ?>" value="<?php echo $remaining_capacity; ?>" <?php if($full_marked) echo 'readonly'; ?> data-capacity="<?php echo $remaining_capacity; ?>" /></td>
                                    <td><input type="number" min="0" class="form-control input-sm leave_input" name="granted_leave_<?php echo $emp['sno']; ?>" value="0" <?php if($full_marked) echo 'readonly'; ?> data-capacity="<?php echo $remaining_capacity; ?>" /></td>
                                    <td><input type="number" min="0" class="form-control input-sm remaining_input" name="remaining_<?php echo $emp['sno']; ?>" value="0" <?php if($full_marked) echo 'readonly'; ?> data-capacity="<?php echo $remaining_capacity; ?>" /></td>
                                </tr>
                        <?php endwhile; ?>
                            </tbody>
                        </table>
                        </div>
                        <input type="hidden" id="total_entries" value="<?php echo $n; ?>">
                        <input type="submit" class="btn btn-success form-control" name="submit" value="Submit">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>

<script>
document.querySelector('.att-table').addEventListener('input', function(e){
    var target = e.target;
    if(!target.classList.contains('present_input') && !target.classList.contains('leave_input') && !target.classList.contains('remaining_input')) return;

    var row = target.closest('tr');
    var presentEl = row.querySelector('.present_input');
    var leaveEl = row.querySelector('.leave_input');
    var remainingEl = row.querySelector('.remaining_input');

    var capacity = parseInt(presentEl.dataset.capacity) || 0;
    var present = parseInt(presentEl.value) || 0;
    var leave = parseInt(leaveEl.value) || 0;
    var remaining = parseInt(remainingEl.value) || 0;

    var total = present + leave + remaining;
    if(total > capacity){
        alert('Total of Present + Leave + Remaining cannot exceed '+capacity+' days!');
        // Reset the last changed input to maximum allowed
        var allowed = Math.max(0, capacity - (leave + remaining));
        if(target.classList.contains('present_input')) presentEl.value = allowed;
        else if(target.classList.contains('leave_input')) leaveEl.value = Math.max(0, capacity - present - remaining);
        else if(target.classList.contains('remaining_input')) remainingEl.value = Math.max(0, capacity - present - leave);
    }
});

</script>

<?php
page_footer();
?>
