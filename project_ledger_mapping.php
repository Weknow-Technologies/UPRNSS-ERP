<?php
include("scripts/settings.php");
include("scripts/alerts.php");
$msg = '';
page_header_start();
?>
<!-- Include Select2 for better dropdowns if available, or just use standard CSS -->
<style>
    .mapping-row { margin-bottom: 10px; padding: 10px; border-bottom: 1px solid #ddd; }
    .project-name { font-weight: bold; }
    .auto-match-btn { margin-top: 20px; }
    .mapped-status { color: green; font-size: 0.9em; display: none; }
</style>
<?php
page_header_end();
page_sidebar();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Project Ledger Mapping</h4>
                <p class="category">Map Projects to their corresponding Ledgers unit-wise.</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Select Unit / Division</label>
                            <select class="form-control" name="unit_id" id="unit_id" onChange="loadMappingData(this.value)">
                                <?php
                                $is_sadmin = isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'sadmin';
                                $user_divisions = (isset($_SESSION['divisions']) && is_array($_SESSION['divisions'])) ? $_SESSION['divisions'] : [];
                                $pre_selected = '';
                                
                                if ($is_sadmin) {
                                    echo '<option value="">--- Select Unit ---</option>';
                                } elseif (count($user_divisions) > 0) {
                                    $pre_selected = $user_divisions[0];
                                }

                                $query = "select * from uprnss_division order by division_name ASC";
                                $run = mysqli_query($db, $query);
                                while ($data = mysqli_fetch_array($run)) {
                                    if ($is_sadmin || in_array($data['s_no'], $user_divisions)) {
                                        $selected = ($data['s_no'] == $pre_selected) ? 'selected' : '';
                                        echo '<option value="' . $data['s_no'] . '" ' . $selected . '>' . $data['division_name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 text-left">
                        <button class="btn btn-info auto-match-btn" id="btn-auto-match" style="display:none;" onclick="autoMatchAll()">Auto-Match by Name Similarity</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="mapping-container" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Projects for selected unit</h4>
            </div>
            <div class="card-body table-full-width table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">S.No.</th>
                            <th style="width: 100px;">ERP Code</th>
                            <th style="min-width: 300px;">Project Name</th>
                            <th style="min-width: 450px;">Mapped Ledger</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="mapping-tbody">
                        <!-- Data will be populated by AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
page_footer_start();
?>
<!-- Include string similarity library or write a simple one -->
<script>
let currentProjects = [];
let currentLedgers = [];

function loadMappingData(unitId) {
    if(!unitId) {
        $('#mapping-container').hide();
        $('#btn-auto-match').hide();
        return;
    }
    
    // Show loading state
    $('#mapping-tbody').html('<tr><td colspan="5">Loading data...</td></tr>');
    $('#mapping-container').show();
    
    $.ajax({
        url: 'scripts/project_ledger_mapping_ajax.php',
        type: 'POST',
        data: { action: 'get_mapping_data', unit_id: unitId },
        dataType: 'json',
        success: function(res) {
            currentProjects = res.projects;
            currentLedgers = res.ledgers;
            renderTable();
            $('#btn-auto-match').show();
        },
        error: function() {
            $('#mapping-tbody').html('<tr><td colspan="5" class="text-danger">Error loading data.</td></tr>');
        }
    });
}

function renderTable() {
    let html = '';
    
    if(currentProjects.length === 0) {
        html = '<tr><td colspan="5">No projects found for this unit with an ERP code.</td></tr>';
    } else {
        $.each(currentProjects, function(index, project) {
            // Find which ledger has this project's erp_code
            let mappedLedgerSno = '';
            $.each(currentLedgers, function(i, ledger) {
                if(ledger.erp_code === project.erp_code) {
                    mappedLedgerSno = ledger.sno;
                }
            });
            
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + project.erp_code + '</td>';
            html += '<td class="project-name" id="proj-name-'+index+'">' + project.project_name + '</td>';
            
            html += '<td>';
            html += '<select class="form-control ledger-select" id="ledger-select-' + index + '" data-erp="' + project.erp_code + '">';
            html += '<option value="">-- Unmapped --</option>';
            $.each(currentLedgers, function(i, ledger) {
                let selected = (ledger.sno == mappedLedgerSno) ? 'selected' : '';
                html += '<option value="' + ledger.sno + '" ' + selected + '>' + ledger.cus_name + '</option>';
            });
            html += '</select>';
            html += '<span class="mapped-status" id="status-' + index + '">Saved ✓</span>';
            html += '</td>';
            
            html += '<td>';
            html += '<button class="btn btn-sm btn-success" onclick="saveMapping(' + index + ', \'' + project.erp_code + '\')">Save</button>';
            html += '</td>';
            html += '</tr>';
        });
    }
    
    $('#mapping-tbody').html(html);
    
    // Initialize Select2 on the newly created dropdowns
    if (typeof jQuery.fn.select2 !== 'undefined') {
        $('.ledger-select').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '-- Unmapped --',
            allowClear: true
        });
    }
}

$(document).ready(function() {
    // Initialize Select2 on the static unit dropdown
    if (typeof jQuery.fn.select2 !== 'undefined') {
        $('#unit_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '--- Select Unit ---',
            allowClear: true
        });
    }
    
    // Auto-load data if a unit is pre-selected
    let preSelectedUnit = $('#unit_id').val();
    if(preSelectedUnit) {
        loadMappingData(preSelectedUnit);
    }
});

function saveMapping(index, erpCode) {
    let ledgerSno = $('#ledger-select-' + index).val();
    
    $.ajax({
        url: 'scripts/project_ledger_mapping_ajax.php',
        type: 'POST',
        data: { action: 'save_mapping', erp_code: erpCode, ledger_sno: ledgerSno },
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                $('#status-' + index).show().fadeOut(3000);
                
                // Update local data
                $.each(currentLedgers, function(i, ledger) {
                    if(ledger.sno == ledgerSno) {
                        ledger.erp_code = erpCode;
                    } else if(ledger.erp_code == erpCode) {
                        ledger.erp_code = null;
                    }
                });
            } else {
                alert('Error saving mapping: ' + res.message);
            }
        },
        error: function() {
            alert('Failed to save mapping.');
        }
    });
}

// Simple Levenshtein distance based similarity
function similarity(s1, s2) {
    let longer = s1;
    let shorter = s2;
    if (s1.length < s2.length) {
        longer = s2;
        shorter = s1;
    }
    let longerLength = longer.length;
    if (longerLength == 0) {
        return 1.0;
    }
    return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
}

function editDistance(s1, s2) {
    s1 = s1.toLowerCase();
    s2 = s2.toLowerCase();
    let costs = new Array();
    for (let i = 0; i <= s1.length; i++) {
        let lastValue = i;
        for (let j = 0; j <= s2.length; j++) {
            if (i == 0)
                costs[j] = j;
            else {
                if (j > 0) {
                    let newValue = costs[j - 1];
                    if (s1.charAt(i - 1) != s2.charAt(j - 1))
                        newValue = Math.min(Math.min(newValue, lastValue),
                            costs[j]) + 1;
                    costs[j - 1] = lastValue;
                    lastValue = newValue;
                }
            }
        }
        if (i > 0)
            costs[s2.length] = lastValue;
    }
    return costs[s2.length];
}

function autoMatchAll() {
    let confirmAction = confirm("This will attempt to automatically match projects to ledgers based on name similarity. It will NOT save automatically; you will need to review and click Save on each or we can add a 'Save All' button. Continue?");
    if(!confirmAction) return;
    
    let matchedCount = 0;
    
    $.each(currentProjects, function(index, project) {
        let bestMatchSno = '';
        let highestSim = 0;
        
        let projName = project.project_name.toLowerCase();
        
        // Skip if already mapped in UI
        let currentVal = $('#ledger-select-' + index).val();
        if(currentVal !== '') return; // keep current mapped value
        
        $.each(currentLedgers, function(i, ledger) {
            let ledgName = ledger.cus_name.toLowerCase();
            let sim = similarity(projName, ledgName);
            
            // Allow matching if similarity is > 0.4 (tweak as needed)
            if(sim > highestSim && sim > 0.4) {
                highestSim = sim;
                bestMatchSno = ledger.sno;
            }
        });
        
        if(bestMatchSno !== '') {
            $('#ledger-select-' + index).val(bestMatchSno);
            matchedCount++;
        }
    });
    
    alert("Auto-matched " + matchedCount + " ledgers. Please review and click Save.");
}
</script>
<?php page_footer_end(); ?>
