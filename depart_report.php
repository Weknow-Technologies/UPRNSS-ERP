<?php
include("scripts/settings.php");
$msg = '';

page_header_start();
page_header_end();
page_sidebar();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card strpied-tabled-with-hover">
            <div class="card-header">
                <h4 class="card-title text-center">Department Wise Project Report</h4>
            </div>
            <div class="card-body table-full-width table-responsive">
                <table class="table table-hover table-striped" id="division_report_table">
                    <thead class="thead-dark">
                        <tr>
                            <th>S.no</th>
                            <th>Department</th>
                            <th>Sub department</th>
                            <th>Total Projects</th>
                            <th>Architect Not Alloted</th>
                            <th>Land Survey Pending</th>
                             <th>Without Technical Sanction</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php

  $sql = "
SELECT 
    d.department_name_hindi AS department_name,
    s.sub_department_hindi AS sub_department_name,
    COUNT(DISTINCT p.sno) AS total_projects,

    -- Architect Pending (no record in inovoice_architect_allotment)
    COUNT(DISTINCT CASE WHEN ia.project_id IS NULL THEN p.sno END) AS architect_pending,

    -- Land Survey Pending (no record in invoice_survey)
    COUNT(DISTINCT CASE WHEN ls.project_id IS NULL THEN p.sno END) AS land_survey_pending,

    -- Tender Pending (no record in tender_allotment)
    COUNT(DISTINCT CASE WHEN ta.project_id IS NULL THEN p.sno END) AS tender_pending,

    -- Technical Sanction Pending (no record in technical_sanction)
    COUNT(DISTINCT CASE WHEN ts.project_id IS NULL THEN p.sno END) AS technical_sanction_pending,

    -- Civil Progress Report Pending (no record in invoice_civil)
    COUNT(DISTINCT CASE WHEN ic.project_name IS NULL THEN p.sno END) AS civil_progress_pending

FROM uprnss_project_temp p

-- Department join
LEFT JOIN uprnss_department_name d 
    ON d.sno = p.department_id

-- Sub Department join
LEFT JOIN uprnss_sub_department s 
    ON s.sno = p.sub_department_id

-- Joins to detect pending statuses
LEFT JOIN inovoice_architect_allotment ia 
    ON ia.project_id = p.sno 
    AND ia.status != '5'

LEFT JOIN invoice_survey ls 
    ON ls.project_id = p.sno

LEFT JOIN tender_allotment ta 
    ON ta.project_id = p.sno

LEFT JOIN technical_sanction ts 
    ON ts.project_id = p.sno

LEFT JOIN invoice_civil ic 
    ON ic.project_name = p.sno

WHERE p.status != '5'

GROUP BY d.department_name_hindi, s.sub_department_hindi
ORDER BY d.department_name_hindi, s.sub_department_hindi;


";



$result = execute_query($sql);

$serial = 1;

// Variables to store grand totals
$grand_total_projects = 0;
$grand_architect_not_alloted = 0;
$grand_land_survey_pending = 0;
$grand_without_technical_sanction = 0;

while($row = mysqli_fetch_assoc($result)){
    echo "<tr>
        <td>".$serial++."</td>
        <td>".($row['department_name'] ?: '-')."</td>
        <td>".($row['sub_department_name'] ?: '-')."</td>
        <td>{$row['total_projects']}</td>
        <td>{$row['architect_pending']}</td>
        <td>{$row['land_survey_pending']}</td>
        <td>{$row['technical_sanction_pending']}</td>
    </tr>";

    // Add to grand totals
    $grand_total_projects += $row['total_projects'];
    $grand_architect_not_alloted += $row['architect_pending'];
    $grand_land_survey_pending += $row['land_survey_pending'];
    $grand_without_technical_sanction += $row['technical_sanction_pending'];
}

	// Display Grand Total row
	echo "<tr style='font-weight:bold; background-color:#f0f0f0;'>
		<td colspan='3'>Grand Total</td>
		<td>{$grand_total_projects}</td>
		<td>{$grand_architect_not_alloted}</td>
		<td>{$grand_land_survey_pending}</td>
		<td>{$grand_without_technical_sanction}</td>
	</tr>";
?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
page_footer_start();
?>
<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script src="js/chartist.min.js"></script>

<script>
$(document).ready(function(){
    $('#division_report_table').DataTable({
        paging: true,
        fixedHeader: true,
        ordering: true,
        info: true,
        pageLength: 20
    });
});
</script>

<?php
page_footer_end();
?>
