<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

	if(!isset($_POST['search'])){
		$_POST['edit_sno'] = '';
		$_POST['department'] = '';
		$_POST['division_id'] = '';
		$_POST['district'] = '';
		$_POST['division_name'] = '';
		$_POST['project_name'] = '';
		$_POST['project_name_hindi'] = '';
		$_POST['project_name_hindi_unicode'] = '';
		$_POST['project_type'] = '';
		$_POST['work_start_date'] = '';
		$_POST['date_from'] = '';
		$_POST['date_to'] = '';
		$_POST['date_type'] ='';
		$_POST['sanction_cost'] = '';
		$_POST['type'] = '';
		$_POST['type1'] = '';
		$_POST['tot_exp_project'] = '';
	}

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

	<div id="container" class="no-print">
		<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="card card-body">    
        	<div class="row d-flex my-auto">    	
					<table width="100%" class="table table-striped table-hover rounded" id="general_stat_table">	
						<tr >
							<th  width="15%">Form</th>
							<th width="18%"><input type="date" name="date_from" id="date_from" value="<?php echo $_POST['date_from']; ?>" class="form-control" ></th>
							<th  width="15%">To</th>
							<th width="18%"><input type="date" name="date_to" id="date_to" value="<?php echo $_POST['date_to']; ?>" class="form-control" ></th>
						</tr>
						<tr >
							<th>Employee Name </th>							
							<th width="18%">
								<select class="form-control" name="employee_name" id="employee_name" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from users";
									$run = execute_query($query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										echo '>'.trim($data['user_name']).'</option>';
									}
									?>
								</select>
							</th>
							<th>Division</th>
							<th>
								<select class="form-control" name="division_id" id="division_id" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = 'select * from uprnss_division order by division_name ASC';
									$run = mysqli_query($db,$query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['s_no'].'" ';
										echo '>'.$data['division_name'].'</option>';
									}
									?>
								</select>
							</th>
						</tr>	
					</table>
					<button type="submit" name="search" class="btn btn-primary">Search</button>
			</div>
		</div>
		</form>
	</div>
	
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
                    <div class="card-body">
					<table class="table table-striped table-hover table-bordered" id="general_stat_table">
						<thead style="position:sticky;top:0; z-index:2;" >
							<tr>
								<th>S.No.</th>
								<th scope="col">Division</th>
								<th scope="col">Full Name</th>
								<th scope="col">Attendance Time</th>
								<th scope="col">Office Location</th>
								<th scope="col">Current Location</th>
								<th>Distance</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=1;
							/*
							if(isset($_POST['search'])){
									if($_POST['employee_name']!=''){
										$sql = 'SELECT * FROM `user_division` RIGHT JOIN attendance on attendance.user_id=user_division.user_id LEFT JOIN users on users.sno=attendance.user_id LEFT JOIN uprnss_division on user_division.division_id=uprnss_division.s_no LEFT JOIN users on users.sno = attendance.user_id where attendance.user_id="'.$_POST['employee_name'].'"; ';
									}
									if($_POST['division_id']!=''){
										
										$sql = 'SELECT *,users.user_name FROM `user_division` RIGHT JOIN attendance on attendance.user_id=user_division.user_id LEFT JOIN users on users.sno = attendance.user_id where user_division.division_id="'.$_POST['division_id'].'"';
									}
							}
							else{
							*/	
								$sql = 'select * from attendance';
								 //echo $sql;
								$result = execute_query($sql);
								while($row = mysqli_fetch_assoc($result)){
									
									$sql2 = 'SELECT uprnss_division.division_name,users.user_name,users.type,user_type.user_type, latitude, longitude FROM `users` LEFT JOIN user_division on users.sno=user_division.user_id LEFT JOIN uprnss_division on user_division.division_id=uprnss_division.s_no LEFT JOIN user_type on users.type=user_type.sno where users.sno="'.$row['user_id'].'"';
									//echo $row['sno'].' >> '.$sql2.'<br>';
									$row_user = mysqli_fetch_assoc(execute_query($sql2));
		                            $distance = vincentyGreatCircleDistance($row_user['latitude'], $row_user['longitude'], $row['current_lat'], $row['current_long']);
		                            $distance = round($distance);
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row_user['division_name'].'</td>
									<td>'.$row_user['user_name'].'</td>
									<td>'.$row['datetime'].'</td>
									<td><a href="https://www.google.com/maps?q='.$row_user['latitude'].','.$row_user['longitude'].'" target="_blank">'.$row_user['latitude'].' '.$row_user['longitude'].'</a></td>
									<td><a href="https://www.google.com/maps?q='.$row['current_lat'].','.$row['current_long'].'" target="_blank">'.$row['current_lat'].' '.$row['current_long'].'</a></td>
									<td>'.$distance.' Mtrs</td>
									<td>'.($distance<50?'<span style="color:#0f0;">In Office</span>':'<span style="color:#f00;">Out-Of Office</span>').'</td>
								</tr>'; 
								}
							//}	
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
		paging: false
    });
 
    
});
	
</script>

    
<?php	

function vincentyGreatCircleDistance(
  $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $lonDelta = $lonTo - $lonFrom;
  $a = pow(cos($latTo) * sin($lonDelta), 2) +
    pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
  $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

  $angle = atan2(sqrt($a), $b);
  return $angle * $earthRadius;
}

page_footer_end();
?>