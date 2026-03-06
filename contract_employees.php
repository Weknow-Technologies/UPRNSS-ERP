<?php
include("scripts/settings.php");
 
$msg='';
$msg1='';
$tab=1;
page_header_start();
page_header_end();
page_sidebar();


if(isset($_POST['submit'])){
	//print_r($_POST);
	//Array ( [employee_designation] => [sanctioned_post] => [filled_post] => [running_projects] => [employee_name] => [previously_employed] => [previous_date_from] => 2023-01-01 [previous_date_to] => 2023-04-03 [current_date_from] => 2023-01-01 [current_date_to] => 2023-04-03 [add_rows_id1] => 
	if($_POST['edit']==''){
	$sql = 'INSERT INTO `uprnss_contract_employees` (`unit_id`, `user_id`, `employee_id`, `entry_date`, `designation`, `sanctioned_posts`, `occupied_posts`, `running_projects`, `previous_working`, `previous_working_date_from`, `previous_working_date_to`, `previous_working_performance`, `previous_working_dues`, `contract_start_date`, `contract_end_date`, `status`, `created_by`, `creation_time`) VALUES ("'.$_SESSION['usersno'].'", "'.$_SESSION['username'].'", "'.$_POST['employee_name'].'", "'.date("Y-m-d").'", "'.$_POST['employee_designation'].'", "'.$_POST['sanctioned_post'].'", "'.$_POST['filled_post'].'", "'.$_POST['running_projects'].'", "'.$_POST['previously_employed'].'", "'.$_POST['previous_date_from'].'", "'.$_POST['previous_date_to'].'", "'.$_POST['previous_performance'].'", "'.$_POST['previous_amount'].'", "'.$_POST['current_date_from'].'", "'.$_POST['current_date_to'].'", 0, "'.$_SESSION['username'].'", "'.date("Y-m-d H:i:s").'")';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data save</p>';
		}
	}
		else{
			$sql = 'update uprnss_contract_employees set

		    `employee_id` ="'.$_POST['employee_name'].'",
		    `designation` ="'.$_POST['employee_designation'].'",
			`sanctioned_posts` ="'.$_POST['sanctioned_post'].'",
			`occupied_posts` ="'.$_POST['filled_post'].'",
			`running_projects` ="'.$_POST['running_projects'].'",
			`previous_working` ="'.$_POST['previously_employed'].'",
			`previous_working_date_from` ="'.$_POST['previous_date_from'].'",
			`previous_working_date_to` ="'.$_POST['previous_date_to'].'",
			`previous_working_performance` ="'.$_POST['previous_performance'].'",
			`previous_working_dues` ="'.$_POST['previous_amount'].'",
			`contract_start_date` ="'.$_POST['current_date_from'].'",
			`contract_end_date` ="'.$_POST['current_date_to'].'",
		
			`edited_by` ="'.$_SESSION['username'].'",
			`edition_time` ="'.date("Y-m-d H:i:s").'"
		
			where sno="'.$_POST['edit'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<p class="text text-success">Data Update</p>';
		
		}
	}	
}
else{
	
	postblank:
		$_POST['employee_designation']= '';
		$_POST['sanctioned_post']= '';
		$_POST['filled_post']= '';
		$_POST['running_projects']= '';
		$_POST['employee_name']= '';
		$_POST['previously_employed']= ''; 
		$_POST['previous_date_from']=  date("Y-m-d");
		$_POST['previous_date_to']=  date("Y-m-d");
		$_POST['previous_performance']= ''; 
		$_POST['previous_amount']= '';
		$_POST['current_date_from']=  date("Y-m-d");
		$_POST['current_date_to']=  date("Y-m-d");  
		$_POST['edit'] = '';
}

if(isset($_GET['edit'])){
	$sql = 'select * from uprnss_contract_employees where sno="'.$_GET['edit'].'"';
	$row = mysqli_fetch_assoc(execute_query($sql));
	
	$_POST['employee_designation'] = $row['designation'];
	$_POST['sanctioned_post'] = $row['sanctioned_posts'];
	$_POST['filled_post'] = $row['occupied_posts'];
	$_POST['running_projects'] = $row['running_projects'];
	$_POST['employee_name'] = $row['employee_id'];
	$_POST['previously_employed'] = $row['previous_working'];
	$_POST['previous_date_from'] = $row['previous_working_date_from'];
	$_POST['previous_date_to'] = $row['previous_working_date_to'];
	$_POST['previous_performance'] = $row['previous_working_performance'];
	$_POST['previous_amount'] = $row['previous_working_dues'];
	$_POST['current_date_from'] = $row['contract_start_date'];
	$_POST['current_date_to'] = $row['contract_end_date'];
	
	$_POST['edit'] = $row['sno'];

}

if(isset($_GET['del'])){
	$sql = 'delete from uprnss_contract_employees where sno="'.$_GET['del'].'"';
	execute_query($sql);
	
	$msg1 .= '<p class="text text-danger">Data Deleted.</p>';
}

?>
   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
		<div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <?php echo $msg; ?>
                    </div>
						
                    <div class="card-body">
                    	<div class="row">
							<div class="col-md-12">
								<h3>Contract Details</h3>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<label>पद जिसके लिए अनुबंध किया जाना है </label>
									<select class="form-control" name="employee_designation" id="employee_designation" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from dp_designation";
									$run = execute_query($query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_GET['edit'])){
											if($row['designation']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['designation']).'</option>';
									}
									?>
									</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">							
								<label>स्वीक्र्त पद (संख्या) </label>
								<input type="text" name="sanctioned_post" id="sanctioned_post" value="<?php echo $_POST['sanctioned_post']; ?>" tabindex="<?php echo $tab++; ?>" class="form-control">
							</div>
							<div class="col-md-3">							
								<label>मौके पर तैनात व्यक्ति की संख्या</label>
								<input type="text" name="filled_post" id="filled_post" value="<?php echo $_POST['filled_post']; ?>" tabindex="<?php echo $tab++; ?>" class="form-control">
							</div>
							<div class="col-md-3">							
								<label>प्रखण्ड मे चल रही परियोजनाओ की संख्या </label>
								<input type="text" name="running_projects" id="running_projects" value="<?php echo $_POST['running_projects']; ?>" tabindex="<?php echo $tab++; ?>" class="form-control">
							</div>
							<div class="col-md-3">							
								<label>अनुबंध के लिए प्रस्तावित व्यक्ति का नाम </label>
								<select class="form-control" name="employee_name" id="employee_name" tabindex="<?php echo $tab++; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = "select * from dp_personal_info";
									$run = execute_query($query);
									while($data = mysqli_fetch_array($run)){
										echo '<option value="'.$data['sno'].'" ';
										if(isset($_GET['edit'])){
											if($row['employee_id']==$data['sno']){
												echo ' selected="Selected"';
											}
										}
										echo '>'.trim($data['full_name']).'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="row mt-4">
							<div class="col-md-3">
								<label>क्या प्रस्तावित व्यक्ति पूर्व मे संस्था के साथ कार्यरत रहे है?</label>
								<select name="previously_employed" id="previously_employed"  tabindex="<?php echo $tab++; ?>" class="form-control">
									<option value="">--Select--</option>
									<option value="yes" <?php if(isset($row['previous_working']))if($row['previous_working']=='yes'){ echo ' selected="Selected"'; }?>>Yes</option>
									
									<option value="no" <?php if(isset($row['previous_working']))if($row['previous_working']=='no'){ echo ' selected="Selected"'; }?>>No</option>
									
								</select>
							</div>
							<div class="col-md-3">
								<label>पूर्व अनुबंध प्रारम्भ होने की तिथि</label>
								<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('previous_date_from', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['previous_date_from']; ?>', <?php echo $tab; $tab+=4;?>));
								</script>
							</div>
							<div class="col-md-3">
								<label>पूर्व अनुबंध समाप्त होने की तिथि</label>
								<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('previous_date_to', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['previous_date_to']; ?>', <?php echo $tab; $tab+=4;?>));
								</script>
							</div>
							<div class="col-md-3">
								<label>पूर्व अनुबंध मे कार्य कुशलता </label>
								<select name="previous_performance" id="previous_performance"  tabindex="<?php echo $tab++; ?>" class="form-control">
								
									<option value="">--Select--</option>
									<option value="satisfactory" <?php if(isset($row['previous_working_performance']))if($row['previous_working_performance']=='satisfactory'){ echo ' selected="Selected"'; }?>>संतोषजनक</option>
									
									<option value="unsatisfactory" <?php if(isset($row['previous_working_performance']))if($row['previous_working_performance']=='unsatisfactory'){ echo ' selected="Selected"'; }?> >असंतोषजनक</option>
								</select>							
							</div>
						</div>
						<div class="row mt-4">
							<div class="col-md-3">							
								<label>संस्था के प्रति बकाए पावना की रकम? <br/>(कुछ नहीं की दशा मे शून्य (0) लिखे)</label>
								<input type="text" name="previous_amount" id="previous_amount" value="<?php echo $_POST['previous_amount']; ?>" tabindex="<?php echo $tab++; ?>" class="form-control">
							</div>
							<div class="col-md-3">
								<label>नए अनुबंध की प्रारम्भ होने की तिथि</label>
								<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('current_date_from', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['current_date_from']; ?>', <?php echo $tab; $tab+=4;?>));
								</script>
							</div>
							<div class="col-md-3">
								<label>नए अनुबंध की समाप्त होने की तिथि</label>
								<script  type="text/javascript" language="javascript">
									document.writeln(DateInput('current_date_to', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['current_date_to']; ?>', <?php echo $tab; $tab+=4;?>));
								</script>
							</div>
						</div>
						<div id="test1"></div>
						<input type="hidden" name="add_rows_id1" id="add_rows_id1" value="">
							
						<div class="row">
							<div class="col-6">
								<button type="submit" name="submit" class="btn btn-success" > Submit  </button>	
								<input type="hidden" id="edit" name="edit" value="<?php echo $_POST['edit']; ?>">								
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body text-center">
					<div class="row">
					<?php echo $msg1; ?>
						<div class="col-md-12">
							<h5>अनुबंध के लिए आवेदन किए गए प्रपत्र </h5>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<table class="table table-striped">
								<thead>
									<tr>
										<th scope="col">S No.</th>
										<th scope="col">Application Date</th>
										<th scope="col">Designation</th>
										<th scope="col">Full Name</th>
										<th scope="col">Father Name</th>
										<th scope="col">DOB</th>
										<th scope="col">Religion</th>
										<th scope="col">E-mail</th>
										<th scope="col">Contact Number</th>
										<th scope="col">Status</th>
										<th scope="col"></th>
										<th scope="col">View</th>
										
										<th scope="col">Edit</th>
										<th scope="col">Delete</th>
									</tr>
								</thead>
								<?php
								$sql = 'SELECT uprnss_contract_employees.sno as sno, entry_date, dp_designation.designation as designation, full_name, father_name, date_of_birth, religion, email, c_number, status from uprnss_contract_employees left join dp_personal_info on dp_personal_info.sno = employee_id left join dp_designation on dp_designation.sno = uprnss_contract_employees.designation';
								//echo $sql;
								$result = execute_query($sql);
								$i=1;
								while($row = mysqli_fetch_assoc($result)){
									echo '<tr>
									<td>'.$i++.'</td>
									<td>'.$row['entry_date'].'</td>
									<td>'.$row['designation'].'</td>
									<td>'.$row['full_name'].'</td>
									<td>'.$row['father_name'].'</td>
									<td>'.date("d-m-Y", strtotime($row['date_of_birth'])).'</td>
									<td>'.$row['religion'].'</td>
									<td>'.$row['email'].'</td>
									<td>'.$row['c_number'].'</td>
									<td class="no-print text-center">';
										
										if ($row['status']=='0') {
											echo '<span class="text text-warning">Requested</span>';
										}
										elseif ($row['status']=='1') {
											echo '<span class="text text-denger">Reject</span>';
										}
										elseif ($row['status']=='2') {
											echo '<span class="text text-warning">Approve</span></br>';
											
										}
										else {
											echo '<span class="text text-warning">Contact Adm</span>';
										}
										echo '
									</td>
									<td class="no-print text-center">';
										if($row['status']=='0'){
											echo '<span class="text text-warning">Apply</span>';
										}
										elseif ($row['status']=='1'){
											echo '<span class="text text-warning">Reject</span>';
										}
										else{
											// echo '<span class="text text-warning">approve</span></br>';
											echo'<a href="print_final_contract.php?id='.$row['sno'].'" target="_blank">print Contract<i class="fa fa fa-print"></i></a>';
										}
										echo '

									</td>
									<td><a href="print_contract.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
									<td><a href="contract_employees.php?edit='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');">Edit</a></td>
									<td><a href="contract_employees.php?del='.$row['sno'].'" onClick="return confirm(\'Are you sure? \');" > Delete </a></td>';
								}
								?>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<script>
	$('select[multiple]').multiselect();
	function add_rows(){
		var id = parseFloat($("#add_rows_id").val());
		if(!id){
			id=0;
		}
		for(var i=1; i<=id; i++){
			if($("#class_name"+i).val()=='' || $("#grade"+i).val()==''){
				alert("पंक्ति संख्या "+i+" खाली है");
				$("#class_name"+i).focus();
				return;
			}
		}
		id = id+1;

		

		var txt = '<div class="row border rounded m-2 p-2 border-secondary"id="add_rows_length"><div class="col-md-3 "><div class="form-group"><label>Class </label><br><select class="form-control" name="class_name" id="class_name" tabindex=""><option value="">--- Select ---</option></select></div></div> <div class="col-md-3"><div class="form_group"><label >College Name</label><input type="text" name="college_name" id="college_name" class="form-control" placeholder="" value=""></div></div><div class="col-md-3"><div class="form_group"><label >Board</label><input type="text" name="board_name" id="" class="form-control" placeholder="" value=""></div></div><div class="col-md-3"><div class="form_group"><label>Roll Number</label><input type="text" name="roll_number" id="roll_number" class="form-control" placeholder="" value=""></div></div><div class="col-md-3"><div class="form_group"><label>Obtained Marks</label><input type="text" name="obtained_marks" id="obtained_marks" class="form-control" placeholder="" value=""></div></div> <div class="col-md-3"><div class="form_group"><label>Percentage</label><input type="text" name="percentage" id="percentage" class="form-control" placeholder="" value=""></div></div><div class="col-md-3"><div class="form_group"><label >Grade</label><input type="text" name="grade" id="grade" class="form-control" placeholder="" value=""></div></div><div class="col-md-3"><div class="form_group"><label> upload File</label><input type="text" name="file_name" id="file_name" class="form-control" placeholder="" value=""></div></div></br><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
		$("#test").append(txt);
		$("#add_rows_id").val(id);
	}
	
				
	function add_rows1(){
		var id = parseFloat($("#add_rows_id1").val());
		if(!id){
			id=0;
		}
		for(var i=1; i<=id; i++){
			if($("#title"+i).val()=='' || $("#attachment"+i).val()==''){
				alert("पंक्ति संख्या "+i+" खाली है");
				$("#title"+i).focus();
				return;
			}
		}
		
		id = id+1;

		

		var txt = '<div class="row border rounded m-2 p-2 border-secondary" id="" >'+id+'<div class="col-md-4"><div class="form-group"><label> Title</label><input type="text" name="title '+id+'" id="title '+id+'" class="form-control" placeholder=" " value="" ></div></div><div class="col-md-4"><div class="form-group"><label>Upload file</label><input type="file" name="attachment'+id+'" id="attachment'+id+'" class="form-control" placeholder=" " value="" ></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows1()">Add</button></div></div>';
		$("#test1").append(txt);
		$("#add_rows_id1").val(id);
	}			
	
	
</script>

</article>
<?php
	
page_footer_start();
page_footer_end();
?>