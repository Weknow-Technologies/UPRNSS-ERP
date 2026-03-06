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
                <h4 class="card-title text-center">Division Wise Project Report</h4>
            </div>
            <div class="card-body table-full-width table-responsive">
                <table class="table table-hover table-striped" id="division_report_table">
                    <thead class="thead-dark">
                        <tr>
                            <th>S.no</th>
                            <th>Division Name</th>
                            <th>Total Projects</th>
                            <th>Architect Pending</th>
                            <th>Land Survey Pending</th>
                             <th>technical Sanction pending</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php
$sql = "
    SELECT 
        d.division_name,
        COUNT(p.sno) AS total_projects,

        SUM(
            CASE 
                WHEN (p.architect_id = '' OR p.architect_id IS NULL)
                 AND (p.structural_architect_id = '' OR p.structural_architect_id IS NULL)
                THEN 1 ELSE 0
            END
        ) AS architect_not_alloted,

        SUM(
            CASE 
                WHEN i.project_id IS NULL OR i.soil_testing_date IS NULL THEN 1
                ELSE 0
            END
        ) AS land_survey_pending,

        SUM(
            CASE 
                WHEN ts.project_id IS NULL THEN 1 
                ELSE 0 
            END
        ) AS without_technical_sanction

    FROM uprnss_division d
    LEFT JOIN uprnss_project_temp p 
        ON d.s_no = p.division_id 
        AND p.status NOT IN ('5','2')

    LEFT JOIN invoice_survey i
        ON p.sno = i.project_id

    LEFT JOIN technical_sanction ts
        ON p.sno = ts.project_id
        AND ts.status != '5'

    GROUP BY d.division_name
    ORDER BY d.division_name;
";

$result = execute_query($sql);
$serial = 1;
while($row = mysqli_fetch_assoc($result)){
    echo "<tr>
        <td>".$serial++."</td>
        <td>{$row['division_name']}</td>
        <td>{$row['total_projects']}</td>
        <td>{$row['architect_not_alloted']}</td>
        <td>{$row['land_survey_pending']}</td>
        <td>{$row['without_technical_sanction']}</td>
    </tr>";
}
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
