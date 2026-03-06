<?php
//print_r($_POST);
	$con = mysqli_connect("localhost", "root", "mysql", "digital_profile");
	$msg = '';
	if(!$con){
		$msg .= '<h3 style="color:#ff0000;">Error # Unable to Connect</h3>';
	}

	if(isset($_POST['submit'])){
		
		$sql = 'insert into dp_personal_info (full_name, father_name, date_of_birth, religion, caste, sub_caste, gender, email, c_number, address, aadhar_number,aadhar_file, pan_number, pan_file) values ("'.$_POST['full_name'].'", "'.$_POST['father_name'].'","'.$_POST['date_of_birth'].'","'.$_POST['religion'].'","'.$_POST['caste'].'","'.$_POST['sub_caste'].'","'.$_POST['gender'].'","'.$_POST['email'].'","'.$_POST['c_number'].'","'.$_POST['address'].'","'.$_POST['aadhar_number'].'", "'.$_FILES['aadhar_file']['name'].'","'.$_POST['pan_number'].'", "'.$_FILES['pan_file']['name'].'")'; 

		mysqli_query($con, $sql);
		if(mysqli_error($con)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($con).'>> '.$sql.'</p>';
		}
		else{
			$msg .= '<p class="text text-success">Data Saved</p>';
			$id = mysqli_insert_id($con);
			
			move_uploaded_file ($_FILES['aadhar_file']['tmp_name'], "images/aadhar/".$_FILES['aadhar_file']['name']);
			move_uploaded_file ($_FILES['pan_file']['tmp_name'], "images/pan/".$_FILES['pan_file']['name']);
		
			$sql = 'insert into dp_qulification (personal_info_id, class_id, college_name, board_name, roll_number, obtained_marks, percentage, grade, file_name) values ("'.$id.'", "'.$_POST['class_id'].'","'.$_POST['college_name'].'", "'.$_POST['board_name'].'","'.$_POST['roll_number'].'","'.$_POST['obtained_marks'].'","'.$_POST['percentage'].'","'.$_POST['grade'].'","'.$_FILES['file_name']['name'].'")';
			mysqli_query($con, $sql);
				if(mysqli_error($con)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($con).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Data Saved</p>';
					
					move_uploaded_file ($_FILES['file_name']['tmp_name'], "images/result/".$_FILES['file_name']['name']);
				
				$sql = 'insert into dp_miscellaneous (personal_info_id, title, file_name) values ("'.$id.'","'.$_POST['title'].'", "'.$_FILES['attachment']['name'].'")';
				mysqli_query($con, $sql);
			if(mysqli_error($con)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($con).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';
			}
  
			move_uploaded_file ($_FILES['attachment']['tmp_name'], "images/misc/".$_FILES['attachment']['name']);
		}
		}
	
	}



?>

<?php

if(isset($_GET['id'])){
	$sql = 'select * from dp_person_info where sno='.$_GET['id'];
	$invoice = mysqli_fetch_assoc(mysqli_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>


<html>	
<head>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</head>
<body>



	<div id="container">
		<div class="card col-md-11 mx-auto">
			<div class="card-body ">
				<div class="row d-flex my-auto">
					<form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
						<?php
						echo $msg;
						?>
						<h3>Personal Details</h3>					
						<div class="col-md-12">
							<div class="row">							
								<div class="col-md-4">							
									<label>Full Name </label>
									<input type="text" name="full_name" class="form-control" value="<?php if(isset($invoice['full_name'])){echo $row['full_name'];}?>">
								</div>
								<div class="col-md-4">							
									<label>Father Name </label>
									<input type="text" name="father_name" class="form-control" value="<?php if(isset($row['father_name'])){echo $row['father_name'];}?>">
								</div>
								<div class="col-md-4">							
									<label>Date of Birth</label>
									<input type="date" name="date_of_birth" class="form-control" value="<?php if(isset($row['date_of_birth'])){echo $row['date_of_birth'];}?>">
									
								</div>
							</div>
							<div class="row">							
								<div class="col-md-4">							
									<label>Religion</label>
									
									<select name="religion" class="form-control" value="<?php if(isset($row['religion'])){echo $row['religion'];}?>">
										<option selected=""> ----Select-----</option>
										<option value="hinduism">Hinduism</option>
										<option value="islam">Islam</option>
										<option value="cristianity">Cristianity</option>
										<option value="buddhism">Buddhism</option>
										
									</select>
								</div>
								<div class="col-md-4">							
									<label>Caste</label>
									
									<select name="caste" class="form-control" value="<?php if(isset($row['caste'])){echo $row['caste'];}?>">
										<option selected=""> ----Select-----</option>
										<option value="General">General</option>
										<option value="OBC">OBC</option>
										<option value="SC">SC</option>
										<option value="ST">ST</option>
										<option value="Minority">Minority</option>
										
									</select>
								</div>
								<div class="col-md-4">							
									<label>Sub Caste</label>
									<input type="text" name="sub_caste" class="form-control" value="<?php if(isset($row['sub_caste'])){echo $row['sub_caste'];}?>">
								</div>
							</div>
							<div class="row">							
								<div class="col-md-4">							
									<label>Gender</label>
									
									<select name="gender" class="form-control" value="<?php if(isset($row['gender'])){echo $row['gender'];}?>">
										<option selected=""> ----Select-----</option>
										<option value="male">Male</option>
										<option value="female">Female</option>
										
									</select>
								</div>
								<div class="col-md-4">							
									<label>E-mail</label>
									<input type="email" name="email" class="form-control" value="<?php if(isset($row['email'])){echo $row['email'];}?>">
								</div>
								<div class="col-md-4">							
									<label>Contact Number</label>
									<input type="text" name="c_number" class="form-control" value="<?php if(isset($row['c_number'])){echo $row['c_number'];}?>">
								</div>
							</div>
							<div class="row">							
								<div class="col-md-4">							
									<label>Address</label>
									<input type="text" name="address" class="form-control" value="<?php if(isset($row['address'])){echo $row['address'];}?>">
								</div>
								<div class="col-md-4">							
									<label>Aadhar Number</label>
									<input type="text" name="aadhar_number" class="form-control" value="<?php if(isset($row['aadhar_number'])){echo $row['aadhar_number'];}?>">
									<input type="file" name="aadhar_file" class="form-control" value="<?php if(isset($row['aadhar_file'])){echo $row['aadhar_file'];}?>">
									
								</div>
								<div class="col-md-4">							
									<label>Pan Number</label>
									<input type="text" name="pan_number" class="form-control" value="<?php if(isset($row['pan_number'])){echo $row['pan_number'];}?>">
									<input type="file" name="pan_file" class="form-control" value="<?php if(isset($row['pan_file'])){echo $row['pan_file'];}?>">
								</div>
								
							$sql = 'selec * from dp_qual where personal_info_id=$_GET['id'];
while()							
								
							<div class="row border rounded m-2 p-2 border-secondary"id="add_rows_length">
								<div class="col-md-2 ">
									<div class="form-group">
										<label>Class </label><br>
										<select class="form-control" name="class_id" id="class_id" tabindex="<?php echo $tab++; ?>">
											<option value="">--- Select ---</option>
											<?php
											$query = "select * from dp_class";
											$run = mysqli_query($con,$query);
											while($data = mysqli_fetch_array($run)){
												echo '<option value="'.$data['sno'].'" ';
												if(isset($_POST['class_name'])){
													if($_POST['sno']==$data['sno']){
														echo ' selected="Selected"';
													}
												}
												echo '>'.trim($data['class_name']).'</option>';
											}
											?>
										</select>
									</div>
								</div> 
								<div class="col-md-2">
									<div class="form_group">
										<label >College Name</label>
										<input type="text" name="college_name" id="college_name" class="form-control" placeholder="" value="">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form_group">
										<label >Board</label>
										<input type="text" name="board_name" id="" class="form-control" placeholder="" value="">
									</div>
								</div>
								
								<div class="col-md-1">
									<div class="form_group">
										<label>Roll No.</label>
										<input type="text" name="roll_number" id="roll_number" class="form-control" placeholder="" value="">
									</div>
								</div>
							
								<div class="col-md-1">
									<div class="form_group">
										<label>Marks</label>
										<input type="text" name="obtained_marks" id="obtained_marks" class="form-control" placeholder="" value="">
									</div>
								</div> 
								<div class="col-md-1">
									<div class="form_group">
										<label>Percentage</label>
										<input type="text" name="percentage" id="percentage" class="form-control" placeholder="" value="">
									</div>
								</div>
								<div class="col-md-1">
									<div class="form_group">
										<label >Grade</label>
										<input type="text" name="grade" id="grade" class="form-control" placeholder="" value="">
									</div>
								</div>
								
								<div class="col-md-1">
									<div class="form_group">
										<label> Attach</label>
										<input type="file" name="file_name" id="file_name" class="form-control" placeholder="" value="">
									</div>
								</div>
							</div>
							
							<div id="test"></div>
							<input type="hidden" name="add_rows_id" id="add_rows_id" value="<?php echo $_POST['add_rows_id']; ?>">
							
							<div class="row border rounded m-2 p-2 border-secondary">
								<div class="col-md-3">
									<div class="form-group">
										<label > Title</label>
										<input type="text" name="title" id="title" class="form-control" placeholder=" " value="" tabindex="<?php echo $tab++; ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Upload file</label>
										<input type="file" accept=".jpg, .jpeg, .gif, .png, .bmp" class="form-control" id="attachment" name="attachment" value="<?php echo $_POST['attachment']; ?>" tabindex="<?php echo $tab++; ?>">
									</div>	
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
</body>
</html>