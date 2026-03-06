<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();


?>

		<div class="row">
			<div class="col-md-12">
				<div class="card">
						<p style="text-align:center; font-size:15px;">&nbsp; यू . पी . आर. एन. एस. एस.   </p>
						<h4 style="text-align:center; font-size:28px; margin:0px; ">&nbsp; परियोजनाओ की संख्या और उनका स्थिति</h4>
						
						<hr class="text-danger" style="height: 1px;border: 0; border-top: 1px solid red;">
					<div class="content">
		<!------------------------------Unit wise ----------------------------->						
						<div class="row">
							<div class="col-2"></div>
							<div class="col-2 text-center">
								<a target="_blank" href="Project_status_report.php"> <img  src="images/ic_project.png" class="img-fluid stat-icon"><br/>Status Wise Project Count Report (Division)  </a> 
								<br/>
								<h4 class="m-0 p-0 text-danger"></h4>
							</div>
							<div class="col-3"></div>
							<div class="col-2 text-center">
								<a target="_blank" href="status_wise_cost_division_report.php"> <img  src="images/ic_project.png" class="img-fluid stat-icon"><br/>Status Wise Project Count and Cost Report (Division)  </a> 
								<br/>
								<h4 class="m-0 p-0 text-danger"></h4>
							</div>
						</div><br/>
		<!------------------------------Department wise ----------------------------->						
						<div class="row">
							<div class="col-2"></div>
							<div class="col-2 text-center">
								<a target="_blank" href="Project_status_report_department_wise.php"> <img  src="images/ic_project.png" class="img-fluid stat-icon"><br/>Status Wise Project Count Report (Department)</a> 
								<br/>
								<h4 class="m-0 p-0 text-danger"></h4>
							</div>
							<div class="col-3"></div>
							<div class="col-2 text-center">
								<a target="_blank" href="status_wise_cost_department_report.php"> <img  src="images/ic_project.png" class="img-fluid stat-icon"><br/>Status Wise Project Count and Cost Report (Department)</a> 
								<br/>
								<h4 class="m-0 p-0 text-danger"></h4>
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
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>