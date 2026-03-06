<?php
include("scripts/settings.php");
$msg='';
$msg1='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();


?>			
<style>
    body {
      background-color: #f8f9fa;
    }
    .project-card {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
    }
    .project-card h2 {
      color: #007bff;
    }
    .project-card p {
      font-size: 18px;
    }
  </style>


				<div class="container">
				  <button class="btn btn-primary mt-3" onclick="downloadPDF()">Download PDF</button>
				</div>
		<div class="row">
            <div class="col-md-12">
                <div class="card">
					
                    <div class="card-header">
					<?php 
						// $sql = 'select * from uprnss_project_temp where (status!="5" or status="0" or status is null or status="1") and project_type="1" and division_id!=""';
						// $result_project = execute_query($sql);
						// $ho_projects =  mysqli_num_rows($result_project);
						
						$sql = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where (status!="5" or status="0" or status is null or status="1") and project_type="1"';
						// echo $sql;
						$ho_projects = mysqli_fetch_assoc(execute_query($sql));
						
						
						$sql1 = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where (status!="5" or status="0" or status is null or status="1") and project_type="2"';
						// echo $sql;
						$unit_projects = mysqli_fetch_assoc(execute_query($sql1));
						
						$sql2 = 'select count(*) c, sum(abs(sanction_cost)) as amount from uprnss_project_temp where (status!="5" or status="0" or status is null or status="1") and (project_type ="" or project_type is null)';
						// echo $sql;
						$oth_projects = mysqli_fetch_assoc(execute_query($sql2));
						
						
						// $sql = 'select * from uprnss_project_temp where (status!="5" or status="0" or status is null or status="1") and project_type="2" and division_id!=""';
						// $result_project_unit = execute_query($sql);
						// $unit_projects =  mysqli_num_rows($result_project_unit);
						
						// $sql = 'select * from uprnss_project_temp where (status!="5" or status="0" or status is null or status="1") and (project_type ="" or project_type is null) and division_id!=""';
						// $result_project_oth = execute_query($sql);
						// $oth_projects =  mysqli_num_rows($result_project_oth);

					?>
                        
                    </div>
                    <div class="card-body">
					  <div class="row">
						<div class="col-md-12">
						  <div class="project-table">
							
							<table class="table  table-striped-hover" id="project-table">
							<thead>
								<tr>
									<td colspan="4">
								<h4 class="card-title text-center bg-danger p-3 text-white">परियोजनाओ का विवरण</h4></br>
								</td>
								</tr>
							  
								<tr class="p-3 table-danger  ">
								  <th>क्रं ० </th>
								  <th>परियोजना का प्रकार </th>
								  <th>परियोजना की संख्या </th>
								  <th>परियोजना  लागत (लाख मे ) </th>
								</tr>
							  </thead>
							  
							  <tbody class="text-center">
								<tr>
								<td>1.</td>
								  <td><a href="head_office_project.php?id=1" target="_blank">शासन स्तर</a></td>
								  <td><?php echo round($ho_projects['c'], 2); ?></td>
								  <td><?php echo round($ho_projects['amount'], 2); ?></td>
								</tr>
								<tr>
								<td>2.</td>
								 <td><a href="head_office_project.php?id=2" target="_blank">जिला स्तर</a></td>
								  <td><?php echo round($unit_projects['c'], 2); ?></td>
								  <td><?php echo round($unit_projects['amount'], 2); ?></td>
								</tr>
								<tr>
								<td>3.</td>
								  <td><a href="head_office_project.php?id=3" target="_blank">अन्य</a></td>
								  <td><?php echo round($oth_projects['c'], 2); ?></td>
								  <td><?php echo round($oth_projects['amount'], 2); ?></td>
								</tr>

							  </tbody>
							  <tfoot >
								<tr>
									
								  <td colspan="2" class="text-right">कुल परियोजना </td>
								  <td class="text-center"><?php echo ($ho_projects['c']+$unit_projects['c']+$oth_projects['c']); ?></td>
								  <td class="text-center"><?php echo round(($ho_projects['amount']+$unit_projects['amount']+$oth_projects['amount']),2); ?></td>
								</tr>
							  </tfoot>
							</table>
						  </div>
						</div>
					  </div>
                </div>
            </div>
		</div>

		
<?php
page_footer_start();
?>


    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>

    
	<!--  Charts Plugin -->
	<script src="js/chartist.min.js"></script>
	
	

<?php		
page_footer_end();
?>

<script>
function downloadPDF() {
  const element = document.getElementById('project-table');
  html2pdf().from(element).save('project_details.pdf');
}
</script>