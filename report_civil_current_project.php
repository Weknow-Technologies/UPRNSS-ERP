<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;


	// if(!isset($_POST['search'])){
		// $_POST['edit_sno'] = '';
		// $_POST['department'] = '';
		// $_POST['district'] = '';
		// $_POST['division_name'] = '';
		// $_POST['project_name'] = '';
		// $_POST['project_name_hindi'] = '';
		// $_POST['project_name_hindi_unicode'] = '';
		
		// $_POST['work_start_date'] = '';
		// $_POST['date_from'] = '';
		// $_POST['date_to'] = '';
		// $_POST['date_type'] ='';
		// $_POST['sanction_cost'] = '';
		// $_POST['type'] = '';
		// $_POST['type1'] = '';
		// $_POST['tot_exp_project'] = '';
	// }
	




page_header_start();

?>
<script src="js/krutidev.js"></script>
<script src="js/unicode_keyboard.js"></script>
<style>
	#project_name_hindi{
		font-family: 'Kruti Dev 010';
		font-size: 20px;
	}
	textarea{
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
	}
</style>

<?php
page_header_end();
page_sidebar();

?>


<style>
	.stat-icon{
		height:80px;
	}

	.box{
		width:95%;
		border-radius:6px;
		overflow:hidden;
		transition:all 0.5s ease;
	}
	.box:hover{
		transform:scale(1.03);
	}
	.heads{
		font-size:2.5rem;
		color:whitesmoke;
		display:flex;
		justify-content:space-between;
		padding:1rem 1rem 2rem;
	}
	.right-heads{
		color:#333;
		opacity:0.4;
		padding-top:0.5rem;
	}
	.foot{
		background-color:#3333339e;
		color:white;
		text-align:center;
		padding:0.5rem;

	}
	.box1{
		background-color:#E84C3D;
	}
	.box2{
		background-color:#F39C11;
	}
	.box3{
		background-color:#1ABB9C;
	}
	.box4{
		background-color:#3598DB;
	}
	.box5{
		background-color:red;
	}
	.box6{
		background-color:red;
	}
	.box7{
		background-color:red;
	}
	
</style>


	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card ">    
			<div class="card-head">
				<div style="font-size:1.5rem;text-align:center;text-decoration:underline;">वर्तमान मे संचालित समस्त परियोजनाओं का प्रगति विवरण</div>
			</div>
			<?php
				$sql ='SELECT invoice_civil.department_id,uprnss_project_temp.project_type, count(*) c, 
				sum(invoice_civil.sanction_cost) as sanction_cost ,
				sum(invoice_civil.total_physical_progress) as total_physical_progress , sum(invoice_civil.total_received_amount) as total_received_amount , sum(invoice_civil.tot_exp_project) as tot_exp_project , 
				sum(invoice_civil.revised_cost) as revised_cost   
				FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where  uprnss_project_temp.status!="5" and uprnss_project_temp.project_type="1" and uprnss_project_temp.reporting_status="0"';
				
				if(mysqli_num_rows(execute_query($sql))>0){
					$shashan_level_tot_project = mysqli_fetch_assoc(execute_query($sql));
					
					$shashan_level_tot_project['c'] = $shashan_level_tot_project['c']==''?0:$shashan_level_tot_project['c'];
					// $tot_shashan_level_tot_project['c'] += $shashan_level_tot_project['c'];
				}else{
					$shashan_level_tot_project['c']=0;
					
				}
				
				$sql ='SELECT invoice_civil.department_id,uprnss_project_temp.project_type, count(*) c, 
				sum(invoice_civil.sanction_cost) as sanction_cost ,
				sum(invoice_civil.total_physical_progress) as total_physical_progress , sum(invoice_civil.total_received_amount) as total_received_amount , sum(invoice_civil.tot_exp_project) as tot_exp_project , 
				sum(invoice_civil.revised_cost) as revised_cost   
				FROM `uprnss_project_temp` left join invoice_civil on invoice_civil_sno = invoice_civil.sno where  uprnss_project_temp.status!="5" and uprnss_project_temp.project_type="2" and uprnss_project_temp.reporting_status!="1"';
				
				if(mysqli_num_rows(execute_query($sql))>0){
					$district_level_tot_project = mysqli_fetch_assoc(execute_query($sql));
					
					$district_level_tot_project['c'] = $district_level_tot_project['c']==''?0:$district_level_tot_project['c'];
					// $tot_shashan_level_tot_project['c'] += $shashan_level_tot_project['c'];
				}else{
					$district_level_tot_project['c']=0;
					
				}
			?>
			
			<div class="card-body">
				<div class="row" >
					<div class="col-md-3 mb-2">
						
					</div>
					<div class="col-md-3 mb-2">
						<a href="civil_report_13.php?id=1" target="_blank">
							<div class="box box1">
								<div class="heads">
									<div class="left-heads" id="count1">
										<?php echo $shashan_level_tot_project['c']; ?>
									</div>
									<div class="right-heads">
										<i class="fas fa-users"></i>
									</div>
								</div>
								<div class="foot">
									शाशन स्तर परियोजनाये
								</div>
							</div>
						</a>
					</div>
					<div class="col-md-3 mb-2">
						<a href="civil_report_13.php?id=2" target="_blank">
							<div class="box box4">
								<div class="heads">
									<div class="left-heads" id="count4">
										<?php echo $district_level_tot_project['c']; ?>
									</div>
									<div class="right-heads">
										<i class="fas fa-users"></i>
										<i class="fa-solid fa-diagram-project"></i>
									</div>
								</div>
								<div class="foot">
									जिला  स्तर परियोजनाये
								</div>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
		</form>
	</div>


		
	
	
	
	
	
	
	
<?php
page_footer_start();
?>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect({
	search: true
});
	
$(document).ready( function () {
    /*$('#general_stat_table').DataTable({
		paging: false,
		fixedHeader: true,
		colReorder: true
		});
	});	*/

	
	var t = $('#general_stat_table').DataTable({
		// paging: false
    });
 
    
});
	
</script>

    
<?php		
page_footer_end();
?>