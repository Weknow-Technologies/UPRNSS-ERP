<?php
include("scripts/settings.php");
 
$msg='';
$msg1='';
$error_msg='';
$tab=1;

if(isset($_POST['submit'])){
	$_POST['physical_handicap'] = isset($_POST['physical_handicap'])?$_POST['physical_handicap']:'';
	$_POST['freedom_fighter'] = isset($_POST['freedom_fighter'])?$_POST['freedom_fighter']:'';
	$_POST['ex_army_man'] = isset($_POST['ex_army_man'])?$_POST['ex_army_man']:'';
	$_POST['ladies'] = isset($_POST['ladies'])?$_POST['ladies']:'';
	$_POST['other'] = isset($_POST['other'])?$_POST['other']:'';
	if($_POST['edit']==''){
		$sql = 'insert into dp_personal_info (old_employee_code, file_no, service_book_no, employee_category_id, full_name, father_name,
		date_of_birth, religion, caste, sub_caste, gender, email, c_number, 
		aadhar_number,
		pan_number,
		p_address1, p_address2, p_post, p_tehseel, p_block, p_district, p_pin, p_state, 
		c_address1, c_address2, c_post, c_tehseel, c_block, c_district, c_pin, c_state, 
		ac_number, ifsc_code,attached_check, 
		physical_handicap, freedom_fighter, ex_army_man, ladies, other,
		status, created_by, creation_time) values 
		(
		"'.$_POST['old_employee_code'].'", 
		"'.$_POST['file_no'].'", 
		"'.$_POST['service_book_no'].'",
		"'.$_POST['employee_category_id'].'",
		"'.$_POST['full_name'].'", 
		"'.$_POST['father_name'].'",
		"'.$_POST['date_of_birth'].'",
		"'.$_POST['religion'].'","'.$_POST['caste'].'","'.$_POST['sub_caste'].'","'.$_POST['gender'].'","'.$_POST['email'].'","'.$_POST['c_number'].'","'.$_POST['aadhar_number'].'","'.$_POST['pan_number'].'", "'.$_POST['p_address1'].'", "'.$_POST['p_address2'].'","'.$_POST['p_post'].'","'.$_POST['p_tehseel'].'", "'.$_POST['p_block'].'","'.$_POST['p_district'].'","'.$_POST['p_pin'].'","'.$_POST['p_state'].'", "'.$_POST['c_address1'].'", "'.$_POST['c_address2'].'","'.$_POST['c_post'].'","'.$_POST['c_tehseel'].'", "'.$_POST['c_block'].'","'.$_POST['c_district'].'","'.$_POST['c_pin'].'","'.$_POST['c_state'].'",  "'.$_POST['ac_number'].'","'.$_POST['ifsc_code'].'","'.$_FILES['attached_check']['name'].'", "'.$_POST['physical_handicap'].'","'.$_POST['freedom_fighter'].'","'.$_POST['ex_army_man'].'","'.$_POST['ladies'].'","'.$_POST['other'].'","0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'")';
		
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$id = mysqli_insert_id($db);
			
						
						if($_FILES['employee_image']['name']!=""){
							$filepath="master_employees_img/employee_image/";
							// echo $filepath."<br>";
							if (!file_exists($filepath)) {
								// Create the folder
								if (mkdir($filepath, 0777, true)) {
									// echo '.<br>';
									$msg .= '<p class="alert alert-success">Folder created successfully</p>';
								} 
							}
							$newfileName=$_FILES["employee_image"]["name"];
							$newfileTmpName=$_FILES["employee_image"]["tmp_name"];
							$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
							
							$file_name=$filepath."{$id}.".$newfileExtension;
							if( move_uploaded_file ($_FILES['employee_image']['tmp_name'], $file_name)){
								//updating file name in the database
								$updateempimg="UPDATE `dp_personal_info` SET 
								`employee_image`='{$file_name}'
									WHERE sno={$id}";

								$updateres=mysqli_query($db,$updateempimg);
								if($updateres){
									$msg .= '<p class="alert alert-success">File Uploaded save</p>';
								}
							}else{
								$error .= '<p class="alert alert-danger">Could not save files</p>';
							}
						}
						
						if($_FILES['aadhar_file']['name']!=""){
					
							$filepath_aadhar="master_employees_img/aadhar/";
									// echo $filepath."<br>";
								if (!file_exists($filepath_aadhar)) {
									// Create the folder
									if (mkdir($filepath_aadhar, 0777, true)) {
										// echo '.<br>';
										$msg .= '<p class="alert alert-success">Folder created successfully</p>';
									} 
								}
							$newfileName=$_FILES["aadhar_file"]["name"];
							$newfileTmpName=$_FILES["aadhar_file"]["tmp_name"];
							$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
							
							$file_name_adhar=$filepath_aadhar."{$id}.".$newfileExtension;
							if( move_uploaded_file ($_FILES['aadhar_file']['tmp_name'], $file_name_adhar)){
								//updating file name in the database
								$updateimgnameadhar="UPDATE `dp_personal_info` SET 
								`aadhar_file`='{$file_name_adhar}'
									WHERE sno={$id}";

								$updateres=mysqli_query($db,$updateimgnameadhar);
								if($updateres){
									$msg .= '<p class="alert alert-success">File Uploaded save</p>';
								}
							}else{
								$error .= '<p class="alert alert-danger">Could not save files</p>';
							}
							
							
						}
						
						if($_FILES['pan_file']['name']!=""){
							
							$filepath_pan="master_employees_img/pan/";
									// echo $filepath."<br>";
								if (!file_exists($filepath_pan)) {
									// Create the folder
									if (mkdir($filepath_pan, 0777, true)) {
										// echo '.<br>';
										$msg .= '<p class="alert alert-success">Folder created successfully</p>';
									} 
								}
							$newfileName=$_FILES["pan_file"]["name"];
							$newfileTmpName=$_FILES["pan_file"]["tmp_name"];
							$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
							
							$file_name_pan=$filepath_pan."{$id}.".$newfileExtension;
							if( move_uploaded_file ($_FILES['pan_file']['tmp_name'], $file_name_pan)){
								//updating file name in the database
								$updateimgnamepan="UPDATE `dp_personal_info` SET 
								`pan_file`='{$file_name_pan}'
									WHERE sno={$id}";

								$updateres=mysqli_query($db,$updateimgnamepan);
								if($updateres){
									$msg .= '<p class="alert alert-success">File Uploaded save</p>';
								}
							}else{
								$error .= '<p class="alert alert-danger">Could not save files</p>';
							}
							
							
						}
						
						if($_FILES['attached_check']['name']!=""){
							
							$filepath_check="master_employees_img/attached/";
									// echo $filepath."<br>";
								if (!file_exists($filepath_check)) {
									// Create the folder
									if (mkdir($filepath_check, 0777, true)) {
										// echo '.<br>';
										$msg .= '<p class="alert alert-success">Folder created successfully</p>';
									} 
								}
							$newfileName=$_FILES["attached_check"]["name"];
							$newfileTmpName=$_FILES["attached_check"]["tmp_name"];
							$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
							
							$file_name_pan=$filepath_check."{$id}.".$newfileExtension;
							if( move_uploaded_file ($_FILES['attached_check']['tmp_name'], $file_name_pan)){
								//updating file name in the database
								$updateimgnamechek="UPDATE `dp_personal_info` SET 
								`attached_check`='{$file_name_pan}'
									WHERE sno={$id}";

								$updateres=mysqli_query($db,$updateimgnamechek);
								if($updateres){
									$msg .= '<p class="alert alert-success">File Uploaded save</p>';
								}
							}else{
								$error .= '<p class="alert alert-danger">Could not save files</p>';
							}
							
						}
						if($_FILES['nomination_file']['name']!=""){
							
							$filepath_check="master_employees_img/nomination_file/";
									// echo $filepath."<br>";
								if (!file_exists($filepath_check)) {
									// Create the folder
									if (mkdir($filepath_check, 0777, true)) {
										// echo '.<br>';
										$msg .= '<p class="alert alert-success">Folder created successfully</p>';
									} 
								}
							$newfileName=$_FILES["nomination_file"]["name"];
							$newfileTmpName=$_FILES["nomination_file"]["tmp_name"];
							$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
							
							$file_name_pan=$filepath_check."{$id}.".$newfileExtension;
							if( move_uploaded_file ($_FILES['nomination_file']['tmp_name'], $file_name_pan)){
								//updating file name in the database
								$updateimgnamenom="UPDATE `dp_personal_info` SET 
								`nomination_file`='{$file_name_pan}'
									WHERE sno={$id}";

								$updateres=mysqli_query($db,$updateimgnamenom);
								if($updateres){
									$msg .= '<p class="alert alert-success">File Uploaded save</p>';
								}
							}else{
								$error .= '<p class="alert alert-danger">Could not save files</p>';
							}
							
						}
			
			
			for($i=1; $i<=$_POST['add_rows_id']; $i++){
				$sql = 'insert into dp_qulification (personal_info_id, class_id, college_name, board_name, roll_number, obtained_marks, percentage, grade) values ("'.$id.'", "'.$_POST['class_id_'.$i].'","'.$_POST['college_name_'.$i].'", "'.$_POST['board_name_'.$i].'","'.$_POST['roll_number_'.$i].'","'.$_POST['obtained_marks_'.$i].'","'.$_POST['percentage_'.$i].'","'.$_POST['grade_'.$i].'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$insertid = mysqli_insert_id($db);
					if($_FILES['file_name_'.$i]['name']!=""){
						
						$filepath="project_file_upload/result/";
						// echo $filepath."<br>";
						if (!file_exists($filepath)) {
							// Create the folder
							if (mkdir($filepath, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
						
						$newfileName=$_FILES["file_name_".$i]["name"];
						$newfileTmpName=$_FILES["file_name_".$i]["tmp_name"];
						$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
						
					
						$file_name=$filepath . $i . "_" . $insertid . "." . $newfileExtension;
						if( move_uploaded_file ($_FILES['file_name_'.$i]['tmp_name'], $file_name)){
							//updating file name in the database
							$updateimgname="UPDATE `dp_qulification` SET 
							`file_name`='{$file_name}'
								WHERE sno={$insertid}";

							$updateres=mysqli_query($db,$updateimgname);
							if($updateres){
								$msg .= '<p class="alert alert-success">File Uploaded save</p>';
							}
						}else{
							$error .= '<p class="alert alert-danger">Could not save files</p>';
						}
					}
				}
			}
			
			for($i=1; $i<=$_POST['add_rows_id1']; $i++){
				$sql = 'insert into dp_miscellaneous (personal_info_id, title) values ("'.$id.'","'.$_POST['title_'.$i].'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$insertid = mysqli_insert_id($db);
					if($_FILES['attachment_'.$i]['name']!=""){
						
						$filepath="project_file_upload/misc/";
						// echo $filepath."<br>";
						if (!file_exists($filepath)) {
							// Create the folder
							if (mkdir($filepath, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
						
						$newfileName=$_FILES["attachment_".$i]["name"];
						$newfileTmpName=$_FILES["attachment_".$i]["tmp_name"];
						$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
						
					
						$file_name=$filepath . $i . "_" . $insertid . "." . $newfileExtension;
						if( move_uploaded_file ($_FILES['attachment_'.$i]['tmp_name'], $file_name)){
							//updating file name in the database
							$updateimgname="UPDATE `dp_miscellaneous` SET 
							`file_name`='{$file_name}'
								WHERE sno={$insertid}";

							$updateres=mysqli_query($db,$updateimgname);
							if($updateres){
								$msg .= '<p class="alert alert-success">File Uploaded save</p>';
							}
						}else{
							$error .= '<p class="alert alert-danger">Could not save files</p>';
						}
					}
				}
			}

			for($i=1; $i<=$_POST['add_rows_id_nomi']; $i++){
				$sql = 'insert into dp_nominee (personal_info_id, nominee_name, nominee_dob, nominee_relation, nominee_share, nominee_aadhar) values 
				("'.$id.'","'.$_POST['nominee_name_'.$i].'","'.$_POST['nominee_dob_'.$i].'","'.$_POST['nominee_relation_'.$i].'","'.$_POST['nominee_share_'.$i].'","'.$_POST['nominee_aadhar_'.$i].'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}
				else{
					$msg .= '<p class="text text-success">Data Saved</p>';
				}
			}
			if($_POST['appo_id']!=''){
				$sql = 'insert into employee_appointment (employee_id, appo_id, appointment_no, appointment_date, appoint_division, pay_band_id, grade_pay_id, pay_level_id, post, joinning_date, deputation_dep_name, deputation_from ,deputation_to, status, created_by, creation_time, pay_scale) values ("'.$id.'", "'.$_POST['appo_id'].'", "'.$_POST['appointment_no'].'","'.$_POST['appointment_date'].'", "'.$_POST['appoint_division'].'", "'.$_POST['pay_band_id'].'", "'.$_POST['grade_pay_id'].'", "'.$_POST['pay_level_id'].'","'.$_POST['appoint_post'].'", "'.$_POST['joinning_date'].'","'.$_POST['deputation_dep_name'].'","'.$_POST['deputation_from'].'","'.$_POST['deputation_to'].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","'.$_POST['pay_scale'].'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}else{
					$msg .= '<div class="alert alert-danger">Successfully Add </div>';
				}
			}
			for($i=1; $i<=$_POST['add_rows_id_work'] && $_POST['pramotaion_type_'.$i]!='';  $i++){
				$sql = 'insert into employee_appointment (employee_id, appo_id, appointment_no, appointment_date, appoint_division, post, pay_band_id, grade_pay_id, pay_level_id, joinning_date, status, created_by, creation_time, pay_scale) values ("'.$id.'", "'.$_POST['pramotaion_type_'.$i].'", "'.$_POST['order_no_'.$i].'","'.$_POST['order_date_'.$i].'", "'.$_POST['place_'.$i].'","'.$_POST['post_'.$i].'", "'.$_POST['pay_band_id_'.$i].'", "'.$_POST['grade_pay_id_'.$i].'", "'.$_POST['post_'.$i].'", "'.$_POST['joinning_date_'.$i].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","'.$_POST['pay_scale_'.$i].'")';
				// echo $sql;
				execute_query($sql);
				if(mysqli_error($db)){ 
					$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}else{
					$msg .= '<div class="alert alert-danger">Promotion  Add </div>';
				}
			}
		}
	}
	else{
		$sql = 'update dp_personal_info set 
		employee_category_id="'.$_POST['employee_category_id'].'",
		old_employee_code="'.$_POST['old_employee_code'].'",
		file_no="'.$_POST['file_no'].'",
		service_book_no="'.$_POST['service_book_no'].'",
		full_name  =  "'.$_POST['full_name'].'", 
		father_name  =   "'.$_POST['father_name'].'",
		date_of_birth  =  "'.$_POST['date_of_birth'].'",
		religion  =  "'.$_POST['religion'].'", 
		caste  =  "'.$_POST['caste'].'",
		sub_caste  = "'.$_POST['sub_caste'].'",  
		gender  =   "'.$_POST['gender'].'",
		email  =   "'.$_POST['email'].'",
		c_number  =   "'.$_POST['c_number'].'",
		aadhar_number  = "'.$_POST['aadhar_number'].'", 
		pan_number  =  "'.$_POST['pan_number'].'",    
		p_address1  =  "'.$_POST['p_address1'].'", 
		p_address2  = "'.$_POST['p_address2'].'" , 
		p_post  =  "'.$_POST['p_post'].'", 
		p_tehseel  = "'.$_POST['p_tehseel'].'",  
		p_block  = "'.$_POST['p_block'].'",  
		p_district  = "'.$_POST['p_district'].'",  
		p_pin  =    "'.$_POST['p_pin'].'",
		p_state  =   "'.$_POST['p_state'].'",
		c_address1  = "'.$_POST['c_address1'].'",  
		c_address2  =  "'.$_POST['c_address2'].'", 
		c_post  =   "'.$_POST['c_post'].'",
		c_tehseel  =  "'.$_POST['c_tehseel'].'", 
		c_block  =  "'.$_POST['c_block'].'", 
		c_district  =  "'.$_POST['c_district'].'",  
		c_pin  =   "'.$_POST['c_pin'].'",
		c_state  = "'.$_POST['c_state'].'",  
		ac_number  =   "'.$_POST['ac_number'].'",
		ifsc_code  =  "'.$_POST['ifsc_code'].'", 
		
		physical_handicap  = "'.$_POST['physical_handicap'].'", 
		freedom_fighter  = "'.$_POST['freedom_fighter'].'", 
		ex_army_man  = "'.$_POST['ex_army_man'].'", 
		ladies  =  "'.$_POST['ladies'].'",
		other ="'.$_POST['other'].'",
		edited_by = "'.$_SESSION['usersno'].'", 
		edition_time = "'.date("Y-m-d H:i:s").'" 
		where sno ="'.$_POST['edit'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$id = $_POST['edit'];
			
				if($_FILES['employee_image']['name']!=""){
					
					$filepath="master_employees_img/employee_image/";
							// echo $filepath."<br>";
						if (!file_exists($filepath)) {
							// Create the folder
							if (mkdir($filepath, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
					$newfileName=$_FILES["employee_image"]["name"];
					$newfileTmpName=$_FILES["employee_image"]["tmp_name"];
					$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
					
					$file_name=$filepath."{$id}.".$newfileExtension;
					if( move_uploaded_file ($_FILES['employee_image']['tmp_name'], $file_name)){
						//updating file name in the database
						$updateimgname="UPDATE `dp_personal_info` SET 
						`employee_image`='{$file_name}'
							WHERE sno={$id}";

						$updateres=mysqli_query($db,$updateimgname);
						if($updateres){
							$msg .= '<p class="alert alert-success">File Uploaded save</p>';
						}
					}else{
						$error .= '<p class="alert alert-danger">Could not save files</p>';
					}
					
					
				}
				
				if($_FILES['aadhar_file']['name']!=""){
					
					$filepath_aadhar="master_employees_img/aadhar/";
							// echo $filepath."<br>";
						if (!file_exists($filepath_aadhar)) {
							// Create the folder
							if (mkdir($filepath_aadhar, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
					$newfileName=$_FILES["aadhar_file"]["name"];
					$newfileTmpName=$_FILES["aadhar_file"]["tmp_name"];
					$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
					
					$file_name_adhar=$filepath_aadhar."{$id}.".$newfileExtension;
					if( move_uploaded_file ($_FILES['aadhar_file']['tmp_name'], $file_name_adhar)){
						//updating file name in the database
						$updateimgname="UPDATE `dp_personal_info` SET 
						`aadhar_file`='{$file_name_adhar}'
							WHERE sno={$id}";

						$updateres=mysqli_query($db,$updateimgname);
						if($updateres){
							$msg .= '<p class="alert alert-success">File Uploaded save</p>';
						}
					}else{
						$error .= '<p class="alert alert-danger">Could not save files</p>';
					}
					
					
				}
				
				if($_FILES['pan_file']['name']!=""){
					
					$filepath_pan="master_employees_img/pan/";
							// echo $filepath."<br>";
						if (!file_exists($filepath_pan)) {
							// Create the folder
							if (mkdir($filepath_pan, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
					$newfileName=$_FILES["pan_file"]["name"];
					$newfileTmpName=$_FILES["pan_file"]["tmp_name"];
					$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
					
					$file_name_pan=$filepath_pan."{$id}.".$newfileExtension;
					if( move_uploaded_file ($_FILES['pan_file']['tmp_name'], $file_name_pan)){
						//updating file name in the database
						$updateimgname="UPDATE `dp_personal_info` SET 
						`pan_file`='{$file_name_pan}'
							WHERE sno={$id}";

						$updateres=mysqli_query($db,$updateimgname);
						if($updateres){
							$msg .= '<p class="alert alert-success">File Uploaded save</p>';
						}
					}else{
						$error .= '<p class="alert alert-danger">Could not save files</p>';
					}
					
					
				}
				
				if($_FILES['attached_check']['name']!=""){
					
					$filepath_check="master_employees_img/attached/";
							// echo $filepath."<br>";
						if (!file_exists($filepath_check)) {
							// Create the folder
							if (mkdir($filepath_check, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
					$newfileName=$_FILES["attached_check"]["name"];
					$newfileTmpName=$_FILES["attached_check"]["tmp_name"];
					$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
					
					$file_name_pan=$filepath_check."{$id}.".$newfileExtension;
					if( move_uploaded_file ($_FILES['attached_check']['tmp_name'], $file_name_pan)){
						//updating file name in the database
						$updateimgname="UPDATE `dp_personal_info` SET 
						`attached_check`='{$file_name_pan}'
							WHERE sno={$id}";

						$updateres=mysqli_query($db,$updateimgname);
						if($updateres){
							$msg .= '<p class="alert alert-success">File Uploaded save</p>';
						}
					}else{
						$error .= '<p class="alert alert-danger">Could not save files</p>';
					}
					
				}
				if($_FILES['nomination_file']['name']!=""){
							
					$filepath_check="master_employees_img/nomination_file/";
							// echo $filepath."<br>";
						if (!file_exists($filepath_check)) {
							// Create the folder
							if (mkdir($filepath_check, 0777, true)) {
								// echo '.<br>';
								$msg .= '<p class="alert alert-success">Folder created successfully</p>';
							} 
						}
					$newfileName=$_FILES["nomination_file"]["name"];
					$newfileTmpName=$_FILES["nomination_file"]["tmp_name"];
					$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
					
					$file_name_pan=$filepath_check."{$id}.".$newfileExtension;
					if( move_uploaded_file ($_FILES['nomination_file']['tmp_name'], $file_name_pan)){
						//updating file name in the database
						$updateimgname="UPDATE `dp_personal_info` SET 
						`nomination_file`='{$file_name_pan}'
							WHERE sno={$id}";

						$updateres=mysqli_query($db,$updateimgname);
						if($updateres){
							$msg .= '<p class="alert alert-success">File Uploaded save</p>';
						}
					}else{
						$error .= '<p class="alert alert-danger">Could not save files</p>';
					}
					
				}
			
				// Fetch existing records for the given personal_info_id
				$existingRecords = [];
				$query = "SELECT sno FROM dp_qulification WHERE personal_info_id='$id' ORDER BY sno ASC";
				$result = mysqli_query($db, $query);

				while ($row = mysqli_fetch_assoc($result)) {
					$existingRecords[] = $row['sno'];
				}

				// Total number of input rows
				$totalRows = $_POST['add_rows_id'];

				for ($i = 1; $i <= $totalRows; $i++) {
					$class_id = $_POST['class_id_' . $i] ?? '';
					$college_name = $_POST['college_name_' . $i] ?? '';
					$board_name = $_POST['board_name_' . $i] ?? '';
					$roll_number = $_POST['roll_number_' . $i] ?? '';
					$obtained_marks = $_POST['obtained_marks_' . $i] ?? '';
					$percentage = $_POST['percentage_' . $i] ?? '';
					$grade = $_POST['grade_' . $i] ?? '';

					$fileField = 'file_name_' . $i;

					if ($i <= count($existingRecords)) {
						// Update existing record
						$sno = $existingRecords[$i - 1];
						$sql = "UPDATE dp_qulification 
								SET class_id='$class_id', college_name='$college_name', board_name='$board_name', 
									roll_number='$roll_number', obtained_marks='$obtained_marks', percentage='$percentage', grade='$grade'
								WHERE sno='$sno'";
					} else {
						// Insert new record
						$sql = "INSERT INTO dp_qulification (personal_info_id, class_id, college_name, board_name, roll_number, obtained_marks, percentage, grade) 
								VALUES ('$id', '$class_id', '$college_name', '$board_name', '$roll_number', '$obtained_marks', '$percentage', '$grade')";
					}

					execute_query($sql);

					if (mysqli_error($db)) {
						$msg .= '<p class="text text-danger">Error: ' . mysqli_error($db) . ' >> ' . $sql . '</p>';
					} else {
						$insertid = ($i <= count($existingRecords)) ? $existingRecords[$i - 1] : mysqli_insert_id($db);

						// Handle file upload
						if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
							$filepath = "project_file_upload/result/";

							if (!file_exists($filepath)) {
								mkdir($filepath, 0777, true);
							}

							$newfileName = $_FILES[$fileField]["name"];
							$newfileTmpName = $_FILES[$fileField]["tmp_name"];
							$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
							$file_name_nomination = $filepath . $i . "_" . $insertid . "." . $newfileExtension;

							if (move_uploaded_file($newfileTmpName, $file_name_nomination)) {
								$updateimgname = "UPDATE dp_qulification SET file_name='$file_name_nomination' WHERE sno=$insertid";
								$updateres = mysqli_query($db, $updateimgname);

								if ($updateres) {
									$msg .= '<p class="alert alert-success">File Uploaded & Saved</p>';
								}
							} else {
								$error .= '<p class="alert alert-danger">Could not save file</p>';
							}
						}
					}
				}
				
			$sql = 'delete from dp_nominee where personal_info_id="'.$id.'"';
			execute_query($sql);
			for($i=1; $i<=$_POST['add_rows_id_nomi']; $i++){
				$sql = 'insert into dp_nominee (personal_info_id, nominee_name, nominee_dob, nominee_relation, nominee_share, nominee_aadhar) values 
				("'.$id.'","'.$_POST['nominee_name_'.$i].'","'.$_POST['nominee_dob_'.$i].'","'.$_POST['nominee_relation_'.$i].'","'.$_POST['nominee_share_'.$i].'","'.$_POST['nominee_aadhar_'.$i].'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
					$error_msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}else{
					// $insertid=mysqli_insert_id($db);
				}
			}
			if($error_msg==''){
				$msg .= '<div class="alert alert-danger">Update personal Information</div>';
			}
			
			// Fetch existing records for the given personal_info_id
			$existingRecords = [];
			$query = "SELECT sno FROM dp_miscellaneous WHERE personal_info_id='$id' ORDER BY sno ASC";
			$result = mysqli_query($db, $query);

			while ($row = mysqli_fetch_assoc($result)) {
				$existingRecords[] = $row['sno'];
			}

			// Total number of input rows
			$totalRows = $_POST['add_rows_id1'];

			for ($i = 1; $i <= $totalRows; $i++) {
				$title = $_POST['title_' . $i];
				$attachmentField = 'attachment_' . $i;

				if ($i <= count($existingRecords)) {
					// Update existing record
					$sno = $existingRecords[$i - 1]; // Fetch corresponding record
					$sql = "UPDATE dp_miscellaneous SET title='$title' WHERE sno='$sno'";
				} else {
					// Insert new record
					$sql = "INSERT INTO dp_miscellaneous (personal_info_id, title) VALUES ('$id', '$title')";
				}

				execute_query($sql);

				if (mysqli_error($db)) {
					$msg .= '<p class="text text-danger">Error: ' . mysqli_error($db) . ' >> ' . $sql . '</p>';
				} else {
					$insertid = ($i <= count($existingRecords)) ? $existingRecords[$i - 1] : mysqli_insert_id($db);

					// Handle file upload
					if (!empty($_FILES[$attachmentField]['name'])) {
						$filepath = "project_file_upload/misc/";

						if (!file_exists($filepath)) {
							mkdir($filepath, 0777, true);
						}

						$newfileName = $_FILES[$attachmentField]["name"];
						$newfileTmpName = $_FILES[$attachmentField]["tmp_name"];
						$newfileExtension = strtolower(pathinfo($newfileName, PATHINFO_EXTENSION));
						$file_name_attachment = $filepath . $i . "_" . $insertid . "." . $newfileExtension;

						if (move_uploaded_file($newfileTmpName, $file_name_attachment)) {
							echo $updateimgname = "UPDATE dp_miscellaneous SET file_name='$file_name_attachment' WHERE sno=$insertid";
							$updateres = mysqli_query($db, $updateimgname);

							if ($updateres) {
								$msg .= '<p class="alert alert-success">File Uploaded & Saved</p>';
							}
						} else {
							$error .= '<p class="alert alert-danger">Could not save file</p>';
						}
					}
				}
			}
			if($error_msg==''){
				$msg .= '<div class="alert alert-danger">Update misc Details</div>';
			}
			 $sql_app_chcek='SELECT * FROM `employee_appointment` where employee_id ="'.$_POST['edit'].'" and (appo_id="1" or appo_id="4" or appo_id="5")';
			//echo $sql_app_chcek='select * employee_appointment where employee_id ="'.$_POST['edit'].'" and (appo_id="1" or appo_id="4" or appo_id="5")';
				$result_check = execute_query($sql_app_chcek);
				$num_check = mysqli_num_rows($result_check);
			if($num_check== 0){
				
				if($_POST['appo_id']!=''){
				 $sql = 'insert into employee_appointment (employee_id, appo_id, appointment_no, appointment_date, appoint_division, pay_band_id, grade_pay_id, pay_level_id, post, joinning_date, deputation_dep_name, deputation_from ,deputation_to, status, created_by, creation_time, pay_scale) values ("'.$id.'", "'.$_POST['appo_id'].'", "'.$_POST['appointment_no'].'","'.$_POST['appointment_date'].'", "'.$_POST['appoint_division'].'", "'.$_POST['pay_band_id'].'", "'.$_POST['grade_pay_id'].'", "'.$_POST['pay_level_id'].'","'.$_POST['appoint_post'].'", "'.$_POST['joinning_date'].'","'.$_POST['deputation_dep_name'].'","'.$_POST['deputation_from'].'","'.$_POST['deputation_to'].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'","'.$_POST['pay_scale'].'")';
				execute_query($sql);
				if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}else{
					$msg .= '<div class="alert alert-danger">Successfully Add </div>';
				}
				}
				
			}else{
			 	$sql = 'update employee_appointment set 
				appo_id = "'.$_POST['appo_id'].'",
				appointment_no = "'.$_POST['appointment_no'].'",
				appointment_date = "'.$_POST['appointment_date'].'",
				appoint_division = "'.$_POST['appoint_division'].'",
				post = "'.$_POST['appoint_post'] .'", 
				joinning_date = "'.$_POST['joinning_date'] .'", 
				pay_scale = "'.$_POST['pay_scale'] .'", 
				pay_band_id = "'.$_POST['pay_band_id'] .'", 
				grade_pay_id = "'.$_POST['grade_pay_id'] .'", 
				pay_level_id = "'.$_POST['pay_level_id'] .'", 
				
				deputation_dep_name = "'.$_POST['deputation_dep_name'] .'", 
				deputation_from = "'.$_POST['deputation_from'] .'" ,
				deputation_to = "'.$_POST['deputation_to'] .'" 
				where employee_id ="'.$_POST['edit'].'" 
				and (appo_id="1" or appo_id="4" or appo_id="5")';
				execute_query($sql);
			}
			
			// echo $sql;
			
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$msg .= '<div class="alert alert-danger">Appointment Update</div>';
			}
			
			$sql = 'delete from employee_appointment where employee_id="'.$id.'" and (appo_id="6" or appo_id="7" or appo_id="8")';
			execute_query($sql);
			for($i=1; $i<=$_POST['add_rows_id_work'] &&$_POST['pramotaion_type_'.$i]!='';  $i++){
				$sql = 'insert into employee_appointment (employee_id, appo_id, appointment_no, appointment_date, appoint_division, post, pay_band_id, grade_pay_id, pay_level_id, joinning_date, status, created_by, creation_time, pay_scale) values ("'.$id.'", "'.$_POST['pramotaion_type_'.$i].'", "'.$_POST['order_no_'.$i].'","'.$_POST['order_date_'.$i].'", "'.$_POST['place_'.$i].'","'.$_POST['post_'.$i].'", "'.$_POST['pay_band_id_'.$i].'", "'.$_POST['grade_pay_id_'.$i].'", "'.$_POST['pay_level_id_'.$i].'", "'.$_POST['joinning_date_'.$i].'", "0", "'.$_SESSION['usersno'].'", "'.date("Y-m-d H:i:s").'", "'.$_POST['pay_scale_'.$i].'")';
				//echo $sql;
				execute_query($sql);
				if(mysqli_error($db)){ 
					$error_msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
				}else{
					$msg .= '<div class="alert alert-danger">Promotion  Add </div>';
				}
			}
			if($error_msg==''){
				$msg .= '<div class="alert alert-danger">update Details</div>';
			}
		}		
	}
	
	
	///////////////////////////////// Dp_personal info update according to Joinning date /////////////////////
	$sql = 'select * from employee_appointment where employee_id="'.$id.'" order by joinning_date DESC limit 1';
	// echo $sql;
	$row_data = mysqli_fetch_assoc(execute_query($sql));
	$sql = 'select * from dp_designation where sno="'.$row_data['post'].'"';
	$row_grade = mysqli_fetch_assoc(execute_query($sql));
	
	if($row_data['post']!=''){
		$sql = 'update dp_personal_info set
		appointment_id="'.$row_data['appo_id'].'",
		division_id="'.$row_data['appoint_division'].'", 		
		employee_designation_id="'.$row_data['post'].'", 		
		employee_type_id="'.$row_grade['grade_id'].'", 		
		basic_pay="'.$row_data['post'].'"		
		where sno="'.$id.'"';
		execute_query($sql);
		// echo $sql;
		if(mysqli_error($db)){ 
			$error_msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
	}			
	if($error_msg==''){
		$msg .= '<div class="alert alert-danger">Update Post</div>';
	}
}
else{
	$_POST['employee_designation'] = '';
	$_POST['service_book_no'] = '';
	$_POST['file_no'] = '';
	$_POST['employee_type'] = '';
	$_POST['employee_category_id'] = '';
	$_POST['employee_subgroup'] = '';
	$_POST['full_name'] = ''; 
	$_POST['father_name'] = '';
	$_POST['date_of_birth'] =  date("Y-m-d");
	$_POST['religion'] = ''; 
	$_POST['caste'] = '';
	$_POST['sub_caste'] = '';  
	$_POST['gender'] = '';
	$_POST['email'] = '';
	$_POST['c_number'] = '';
	$_POST['employee_image'] = '';
	$_POST['aadhar_number'] = ''; 
	$_POST['aadhar_file'] = ''; 
	$_POST['pan_number'] = '';  
	$_POST['pan_file'] = '';  
	$_POST['p_address1'] = ''; 
	$_POST['p_address2']=''; 
	$_POST['p_post'] = ''; 
	$_POST['p_tehseel'] = '';  
	$_POST['p_block'] = '';  
	$_POST['p_district'] = '';  
	$_POST['p_pin'] = '';
	$_POST['p_state'] = '';
	$_POST['c_address1'] = '';  
	$_POST['c_address2'] = ''; 
	$_POST['c_post'] = '';
	$_POST['c_tehseel'] = ''; 
	$_POST['c_block'] = ''; 
	$_POST['c_district'] = '';  
	$_POST['c_pin'] = '';
	$_POST['c_state'] = '';  
	$_POST['ac_number'] = '';
	$_POST['ifsc_code'] = '';
	$_POST['attached_check']= ''; 
	$_POST['physical_handicap'] = '';
	$_POST['freedom_fighter'] = '';
	$_POST['ex_army_man'] = '';
	$_POST['ladies'] = '';
	$_POST['other'] = '';
	$_POST['edit'] = '';
	$_POST['add_rows_id']= '1';
	$_POST['class_id_1']= '';
	$_POST['college_name_1'] = '';
	$_POST['board_name_1'] = '';
	$_POST['roll_number_1'] = '';
	$_POST['obtained_marks_1']= '';
	$_POST['percentage_1']= '';
	$_POST['grade_1']= '';
	$_POST['file_name_1']= '';
	$_POST['add_rows_id1']= '1';
	$_POST['title_1']= '';
	$_POST['attachment_1']= '';
	$_POST['old_employee_code']= '';
	$_POST['add_rows_id_nomi']= '1';
	$_POST['nominee_name_1'] = ''; 
	$_POST['nominee_dob_1'] = ''; 
	$_POST['nominee_relation_1'] = ''; 
	$_POST['nominee_share_1'] = '';
	$_POST['nominee_aadhar_1'] = '';
	$_POST['nomination_file_1'] = '';

	$_POST['grade_id'] = '';
	$_POST['designation_id'] = '';
	$_POST['employee_id'] = '';
	
	$_POST['appo_id'] = '';
	$_POST['appointment_no'] = '';
	$_POST['appointment_date'] = '';
	$_POST['appoint_division'] = '';
	$_POST['appoint_post'] = '';
	$_POST['joinning_date'] = '';
	
	$_POST['pramotaion_type_1'] = '';
	$_POST['order_no_1'] = '';
	$_POST['order_date_1'] = '';
	$_POST['place_1'] = '';
	$_POST['post_1'] = '';
	$_POST['joinning_date_1'] = '';
	$_POST['add_rows_id_work'] = 1;
	
	$_POST['deputation_dep_name'] = '';
	$_POST['deputation_from'] = '';
	$_POST['deputation_to'] = '';
	
	$_POST['edit'] = '';
}
	
	
if(isset($_GET['del'])){
		$sql = 'delete from dp_personal_info where sno="'.$_GET['del'].'"';
		mysqli_query($db, $sql);
		if(mysqli_error($db)){
			$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
		}
		else{
		$sql = 'delete from dp_qulification where personal_info_id="'.$_GET['del'].'"';
		mysqli_query($db, $sql);
		if(mysqli_error($db)){
			$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
		}
		else{
		$sql = 'delete from dp_nominee where personal_info_id="'.$_GET['del'].'"';
		mysqli_query($db, $sql);
		if(mysqli_error($db)){
			$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
		}
		else{
		$sql = 'delete from dp_miscellaneous where personal_info_id="'.$_GET['del'].'"';
		mysqli_query($db, $sql);
		if(mysqli_error($db)){
			$msg1 .= '<h3 style="color:#ff0000;">Error in deleting . '.mysqli_error($db).' >> '.$sql.'</h3>';
		}
		else{
			$msg1 .= '<p class="text text-denger">Data Deleted</p>';
			}
		}
	}
	}
}	 
		
if(isset($_GET['edit'])){
	$sql= 'select * from dp_personal_info where sno="'.$_GET['edit'].'"';
	$row = mysqli_fetch_assoc(execute_query($sql));
	$_POST['old_employee_code'] = $row['old_employee_code'];
	$_POST['file_no'] = $row['file_no'];
	$_POST['service_book_no'] = $row['service_book_no'];
	$_POST['employee_category_id'] = $row['employee_category_id'];
	
	
	$_POST['date_of_birth'] = $row['date_of_birth'];
	$_POST['physical_handicap'] = $row['physical_handicap'];
	$_POST['freedom_fighter'] = $row['freedom_fighter'];
	$_POST['ex_army_man'] =$row['ex_army_man'];
	$_POST['ladies'] = $row['ladies'];
	$_POST['other'] = $row['other'];
	
	$sql = 'select * from dp_qulification where personal_info_id="'.$_GET['edit'].'"';
	$result_qul = execute_query($sql);
	if(mysqli_num_rows($result_qul)!=0){
		$_POST['add_rows_id'] = mysqli_num_rows($result_qul);
		$i=1;
		while($row_qul = mysqli_fetch_assoc($result_qul)){
			$_POST['class_id_'.$i]= $row_qul['class_id'];
			$_POST['college_name_'.$i] = $row_qul['college_name'];
			$_POST['board_name_'.$i] = $row_qul['board_name'];
			$_POST['roll_number_'.$i] = $row_qul['roll_number'];
			$_POST['obtained_marks_'.$i]= $row_qul['obtained_marks'];
			$_POST['percentage_'.$i]= $row_qul['percentage'];
			$_POST['grade_'.$i]= $row_qul['grade'];
			$_POST['file_name_'.$i]= $row_qul['file_name'];
			$i++;
		}
	}

	$sql = 'select * from dp_nominee where personal_info_id="'.$_GET['edit'].'"';
	$result_nomi = execute_query($sql);
	if(mysqli_num_rows($result_nomi)!=0){
		$_POST['add_rows_id_nomi'] = mysqli_num_rows($result_nomi);
		$i=1;
		while($row_nomi = mysqli_fetch_assoc($result_nomi)){
			$_POST['nominee_name_'.$i]= $row_nomi['nominee_name'];
			$_POST['nominee_dob_'.$i] = $row_nomi['nominee_dob'];
			$_POST['nominee_relation_'.$i] = $row_nomi['nominee_relation'];
			$_POST['nominee_share_'.$i] = $row_nomi['nominee_share'];
			$_POST['nominee_aadhar_'.$i]= $row_nomi['nominee_aadhar'];
			// $_POST['nomination_file_'.$i]= $row_nomi['nomination_file'];
			$i++;
		}
	}

	$sql = 'select * from dp_miscellaneous where personal_info_id="'.$_GET['edit'].'"';
	$result_misc = execute_query($sql);
	if(mysqli_num_rows($result_misc)!=0){
		$_POST['add_rows_id1'] = mysqli_num_rows($result_misc);
		$i=1;
		while($row_misc = mysqli_fetch_assoc($result_misc)){
			$_POST['title_'.$i]= $row_misc['title'];
			$_POST['attachment_'.$i]= $row_misc['file_name'];
			$i++;
		}
	}

	$sql = 'select * from employee_appointment where employee_id="'.$_GET['edit'].'" and (appo_id="1" or appo_id="4" or appo_id="5")';
	// echo $sql;
	$result = execute_query($sql);
	if (mysqli_num_rows($result) != 0) {
		$data = mysqli_fetch_assoc($result);
		if (isset($data['appo_id'])) {
			$_POST['appo_id'] = $data['appo_id'];
			$_POST['appointment_no'] = $data['appointment_no'];
			$_POST['appointment_date'] = $data['appointment_date'];
			$_POST['appoint_division'] = $data['appoint_division'];
			$_POST['appoint_post'] = $data['post'];
			$_POST['joinning_date'] = $data['joinning_date'];
			$_POST['pay_scale'] = $data['pay_scale'];
			$_POST['pay_band_id'] = $data['pay_band_id'];
			$_POST['grade_pay_id'] = $data['grade_pay_id'];
			$_POST['pay_level_id'] = $data['pay_level_id'];
			
			$_POST['deputation_dep_name'] = $data['deputation_dep_name'];
			$_POST['deputation_from'] = $data['deputation_from'];
			$_POST['deputation_to'] = $data['deputation_to'];	
		}else{
			$_POST['appo_id'] = '';
			$_POST['appointment_no'] = '';
			$_POST['appointment_date'] = '';
			$_POST['appoint_division'] = '';
			$_POST['appoint_post'] = '';
			$_POST['joinning_date_1'] = '';
			$_POST['pay_band_id'] = '';
			$_POST['grade_pay_id'] = '';
			$_POST['pay_level_id']= '';
			
			$_POST['deputation_dep_name'] = '';
			$_POST['deputation_from'] = '';
			$_POST['deputation_to'] = '';
		}	
	}
	$sql = 'select * from employee_appointment where employee_id="'.$_GET['edit'].'" and (appo_id="6" or appo_id="7" or appo_id="8")';
	$result_work = execute_query($sql);
	if(mysqli_num_rows($result_work)!=0){
		$_POST['add_rows_id_work'] = mysqli_num_rows($result_work);
		$i=1;
		while($row_work = mysqli_fetch_assoc($result_work)){
			$_POST['pramotaion_type_'.$i] = $row_work['appo_id'];
			$_POST['order_no_'.$i] = $row_work['appointment_no'];
			$_POST['order_date_'.$i] = $row_work['appointment_date'];
			$_POST['place_'.$i] = $row_work['appoint_division'];
			$_POST['post_'.$i] = $row_work['post'];
			$_POST['joinning_date_'.$i] = $row_work['joinning_date'];
			
			echo$_POST['pay_scale_'.$i] = $row_work['pay_scale'];
			$_POST['pay_band_id_'.$i] = $row_work['pay_band_id'];
			$_POST['grade_pay_id_'.$i] = $row_work['grade_pay_id'];
			$_POST['pay_level_id_'.$i] = $row_work['pay_level_id'];
			
			$i++;
		}
	}	
	$_POST['edit'] = $_GET['edit'];
}


page_header_start();
page_header_end();
page_sidebar();


?>
	<script>

	function onchangetype()
	{
		var status = document.getElementById("appo_id");
		
		if(status.value == "5")
		{
			document.getElementById("hide").style.display="flex";
		}
		else{
			document.getElementById("hide").style.display="none";
			
			
		}
	}
	
	</script>
	
	<style>
		
		/* Mark input boxes that gets an error on validation: */
		input.invalid {
		  background-color: #ffdddd;
		}

		/* Hide all steps by default: */
		.tab {
		  display: none;
		}
		/* Make circles that indicate the steps of the form: */
		.step {
		  height: 20px;
		  width: 20px;
		  margin: 10 9 0px !important;
		  background-color: #bbbbbb;
		  border: 2px solid black;
		  border-radius: 50%;
		  display: inline-block;
		  opacity: 0.5;
		}

		.step.active {
		  opacity: 1;
		}

		/* Mark the steps that are finished and valid: */
		.step.finish {
		  background-color: yellow;
		}
	</style>

	<form id="employees_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">
	
		<div class="container col-md-12 no-print" >
			<div class="card ">
				<div class="card-body ">
						<?php echo $msg; ?>
						<!-- Circles which indicates the steps of the form: -->
						<div style="text-align:center;margin-top:10px; margin-bottom:10px; display:flex; border-radius: 5px;" class="bg-danger mx-auto text-white" >
							<h5 >Personal Detail &nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Address&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Bank Details&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Qualifcation Details&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Other Information&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Appointment Details&nbsp;<span class="step"></span></h5>
							<h5>&nbsp;Employeement Details&nbsp;<span class="step"></span></h5>
						</div>
						<div class="row d-flex my-auto">		
							<div class="col-md-12">
								<div class="card tab" >
									<h3>Personal Details</h5>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr style="background-color:whitesmoke;">
										<th>Employee Group</th>
										<td>
											<select  name="employee_category_id" id="employee_category_id" tabindex="<?php echo $tab++; ?>" class="form-control">
												<option value="">--Select--</option>
												<?php 
												$query = "select * from dp_category";
												$run = execute_query($query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['sno'].'" ';
													if(isset($_POST['employee_category_id'])){
													if($_POST['employee_category_id']==$data['sno']){
														echo ' selected="Selected"';
														}
													}
													echo '>'.trim($data['category_name']).'</option>';
												}
												?>
											</select>
										</td>
										<th>Old Employee Code:</th>
										<th><input  type="text" name="old_employee_code" id="old_employee_code" class="form-control" placeholder=" " value="<?php echo $_POST['old_employee_code']; ?>" tabindex="<?php echo $tab++; ?>"></th>
										<th>File No:</th>
										<th><input  type="text" name="file_no" id="file_no" class="form-control" placeholder=" " value="<?php echo $_POST['file_no']; ?>" tabindex="<?php echo $tab++; ?>"></th>
										<th>Service Book No:</th>
										<th><input  type="text" name="service_book_no" id="service_book_no" class="form-control" placeholder=" " value="<?php echo $_POST['service_book_no']; ?>" tabindex="<?php echo $tab++; ?>"></th>
										
										</tr>
									</table>
									<table width="100%" class="table table-striped table-hover rounded">
										<tr>
											<td>Full Name </td>
											<td><input type="text" name="full_name" id="full_name" tabindex="<?php echo $tab++; ?>" class="form-control" value="<?php if(isset($row['full_name'])){echo $row['full_name'];}?>"></td>
											<td>Father Name </td>
											<td><input type="text" name="father_name" class="form-control" value="<?php if(isset($row['father_name'])){echo $row['father_name'];}?>" tabindex="<?php echo $tab++; ?>"></td>
											<td>Date of Birth</td>
											<td>
												<script  type="text/javascript" language="javascript">
												document.writeln(DateInput('date_of_birth', 'user_form', true, 'YYYY-MM-DD', '<?php echo $_POST['date_of_birth']; ?>', <?php echo $tab; $tab+=4;?>));
												</script>
											</td>
										
										</tr>
										<tr class="table-danger">
											<td>Religion</td>
											<td>
												<select name="religion" class="form-control" tabindex="<?php echo $tab++; ?>" >
													<option value="">--Select--</option>
													<option value="hinduism" <?php if(isset($row['religion']))if($row['religion']=='hinduism'){ echo ' selected="Selected"'; }?>>Hinduism</option>
													<option value="islam" <?php if(isset($row['religion']))if($row['religion']=='islam'){ echo ' selected="Selected"'; }?>>Islam</option>
													<option value="cristianity" <?php if(isset($row['religion']))if($row['religion']=='cristianity'){ echo ' selected="Selected"'; }?>">Cristianity</option>
													<option value="buddhism" <?php if(isset($row['religion']))if($row['religion']=='buddhism'){ echo ' selected="Selected"'; }?>>Buddhism</option>
													
												</select>
											</td>
											<td>Category</td>
											<td>
												<select name="caste" class="form-control" tabindex="<?php echo $tab++; ?>">
													<option value=""> --Select--</option>
													<option value="Un-Reserved/General" <?php if(isset($row['caste']))if($row['caste']=='Un-Reserved/General'){ echo ' selected="Selected"'; }?>">Un-Reserved /General</option>
													<option value="OBC" <?php if(isset($row['caste']))if($row['caste']=='OBC'){ echo ' selected="Selected"'; }?>>OBC</option>
													<option value="SC" <?php if(isset($row['caste']))if($row['caste']=='SC'){ echo ' selected="Selected"'; }?>>SC</option>
													<option value="ST" <?php if(isset($row['caste']))if($row['caste']=='ST'){ echo ' selected="Selected"'; }?>>ST</option>
													<option value="Minority" <?php if(isset($row['caste']))if($row['caste']=='Minority'){ echo ' selected="Selected"'; }?>>Minority</option>
													<option value="EWS" <?php if(isset($row['caste']))if($row['caste']=='EWS'){ echo ' selected="Selected"'; }?>>EWS</option>
													
												</select>
											</td>
											<td>Caste</td>
											<td>
												<input type="text" name="sub_caste" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['sub_caste'])){echo $row['sub_caste'];}?>"></td>
										</tr>
										<tr>
											<td>Gender</td>
											<td><select name="gender" class="form-control" tabindex="<?php echo $tab++; ?>">
												<option value=""> --Select--</option>
												<option value="male" <?php if(isset($row['gender']))if($row['gender']=='male'){ echo ' selected="Selected"'; }?>">Male</option>
												<option value="female" <?php if(isset($row['gender']))if($row['gender']=='female'){ echo ' selected="Selected"'; }?>">Female</option>
												
											</select></td>
											<td>E-mail</td>
											<td><input type="email" name="email" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['email'])){echo $row['email'];}?>"></td>
											<td>Contact Number</td>
											<td><input type="text" name="c_number" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_number'])){echo $row['c_number'];}?>"></td>
										</tr>
										<tr class="table-danger">
											<td>Photo Upload</td>
											<td></td>
											<td>Aadhar No.</td>
											<td><input type="text" name="aadhar_number" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['aadhar_number'])){echo $row['aadhar_number'];}?>"></td>
											<td>Pan Number</td>
											<td><input type="text" name="pan_number" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['pan_number'])){echo $row['pan_number'];}?>"></td>
											
											
										</tr>
										<tr>
											<td></td>
											<td><input type="file" name="employee_image" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['employee_image'])){echo $row['employee_image'];}?>" accept="image/*">
												<?php
												if(!empty($row['employee_image']) && file_exists($row['employee_image'])) {
													?>
													<div class="">
														<img src="<?php echo $row['employee_image']; ?>" class="img-fluid img-thumbnail" style="height:50px;"
															id="society_photo_uploaded">
														<label><a href="<?php echo $row['employee_image']; ?>"
																target="_blank">संलग्न देखें</a></label>

													</div>
													<?php
												}
												?>     
											</td>
											<td>Aadhar file</td>
											<td><input type="file" name="aadhar_file" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['aadhar_file'])){echo $row['aadhar_file'];}?>">
											
												<?php
												if(!empty($row['aadhar_file']) && file_exists($row['aadhar_file'])) {
													?>
													<div class="">
														<img src="<?php echo $row['aadhar_file']; ?>" class="img-fluid img-thumbnail" style="height:50px;"
															id="society_photo_uploaded">
														<label><a href="<?php echo $row['aadhar_file']; ?>"
																target="_blank">संलग्न देखें</a></label>

													</div>
													<?php
												}
												?>     
											</td>
											<td>Pan File</td>
											<td><input type="file" name="pan_file" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['pan_file'])){echo $row['pan_file'];}?>">
												<?php
												if(!empty($row['pan_file']) && file_exists($row['pan_file'])) {
													?>
													<div class="">
														<img src="<?php echo $row['pan_file']; ?>" class="img-fluid img-thumbnail" style="height:50px;"
															id="society_photo_uploaded">
														<label><a href="<?php echo $row['pan_file']; ?>"
																target="_blank">संलग्न देखें</a></label>

													</div>
													<?php
												}
												?>
											</td>
											
											
										</tr>
									</table>
								</div>
								<div class="card tab" id="info_table" name="info_table" >
									<h4>Permanent Address</h4>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>Address Line 1</td>
											<td><input type="text" name="p_address1" id="p_address1" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_address1'])){echo $row['p_address1'];}?>"></td>
											<td>Address Line 2</td>
											<td><input type="text" name="p_address2" id="p_address2" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_address2'])){echo $row['p_address2'];}?>"></td>
											<td>Post</td>
											<td><input type="text" name="p_post" id="p_post" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_post'])){echo $row['p_post'];}?>"></td>
										</tr>
										<tr class=" ">
											<td>Tehseel</td>
											<td><input type="text" name="p_tehseel" id="p_tehseel" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_tehseel'])){echo $row['p_tehseel'];}?>"></td>
											<td>Block</td>
											<td><input type="text" name="p_block" id="p_block" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_block'])){echo $row['p_block'];}?>"></td>
											<td>District</td>
											<td><input type="text" name="p_district" id="p_district" class="form-control" value="<?php if(isset($row['p_district'])){echo $row['p_district'];}?>" tabindex="<?php echo $tab++; ?>"></td>
										</tr>
										<tr class="table-danger ">
											<td>PIN</td>
											<td><input type="text" name="p_pin" id="p_pin" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_pin'])){echo $row['p_pin'];}?>"></td>
											<td>State</td>
											<td><input type="text" name="p_state" id="p_state" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['p_state'])){echo $row['p_state'];}?>"></td>
											<td></td>
											<td></td>
										</tr>
									</table>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="bg-secondary text-white">
											<th colspan="6" class="h5" >Correspondence Address <a href="javascript:copy_adr()" class="btn btn-primary" >Click Here to Copy</a></th>
										</tr>
										<tr class="table-danger ">
											<td>Address Line 1</td>
											<td><input type="text" name="c_address1" id="c_address1" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_address1'])){echo $row['c_address1'];}?>"></td>
											<td>Address Line 2</td>
											<td><input type="text" name="c_address2" id="c_address2" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_address2'])){echo $row['c_address2'];}?>"></td>
											<td>Post</td>
											<td><input type="text" name="c_post" id="c_post" class="form-control"tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_post'])){echo $row['c_post'];}?>"></td>
										</tr>
										<tr class="">
											<td>Tehseel</td>
											<td><input type="text" name="c_tehseel" id="c_tehseel" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_tehseel'])){echo $row['c_tehseel'];}?>"></td>
											<td>Block</td>
											<td><input type="text" name="c_block" id="c_block" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_block'])){echo $row['c_block'];}?>"></td>
											<td>District</td>
											<td><input type="text" name="c_district" id="c_district" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_district'])){echo $row['c_district'];}?>"></td>
										</tr>
										<tr class="table-danger ">
											<td>PIN</td>
											<td><input type="text" name="c_pin" id="c_pin" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_pin'])){echo $row['c_pin'];}?>"></td>
											<td>State</td>
											<td><input type="text" name="c_state" id="c_state" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['c_state'])){echo $row['c_state'];}?>"></td>
											<td></td>
											<td></td>
										</tr>
									</table>
								</div>
								<div class="card tab"  >
									<h4>Bank Details</h4>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>A/C Number</td>
											<td><input type="text" name="ac_number" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['ac_number'])){echo $row['ac_number'];}?>"></td>
											<td>IFSC Code</td>
											<td><input type="text" name="ifsc_code" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['ifsc_code'])){echo $row['ifsc_code'];}?>"></td>
											<td>Attached Cancel Check/Passbook</td>
											<td><input type="file" name="attached_check" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['attached_check'])){echo $row['attached_check'];}?>">
											
												<?php
													if(!empty($row['attached_check']) && file_exists($row['attached_check'])) {
														?>
														<div class="">
															<img src="<?php echo $row['attached_check']; ?>" class="img-fluid img-thumbnail" style="height:50px;"
																id="society_photo_uploaded">
															<label><a href="<?php echo $row['attached_check']; ?>"
																	target="_blank">संलग्न देखें</a></label>

														</div>
														<?php
													}
												?>
											</td>
										</tr>
										
									</table>
									<h4>Nominee Details</h4>
									
									
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="">
											<td>Upload Scanned Image</td>
											<td>
												<input type="file" name="nomination_file" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php if(isset($row['nomination_file'])){echo $row['nomination_file'];}?>">
											</td>
											<td>
												<?php
													if(!empty($row['nomination_file']) && file_exists($row['nomination_file'])) {
														?>
														<div class="">
															<img src="<?php echo $row['nomination_file']; ?>" class="img-fluid img-thumbnail" style="height:50px;"
																id="society_photo_uploaded">
															<label><a href="<?php echo $row['nomination_file']; ?>"
																	target="_blank">संलग्न देखें</a></label>

														</div>
														<?php
													}
												?>
											</td>
										</tr>
										
										<tr class="table-danger ">
											<td>Name of Nominee</td>
											<td>Date of Birth Nominee</td>
											<td>Relation with Nominee</td>
											<td>Share Percentage of the Nominee</td>
											<td>Aadhar Number of the Nominee</td>
											<td></td>
											
											
										</tr>
										<?php 
										for($i=1;$i<=$_POST['add_rows_id_nomi'];$i++){
										?>
										<tr id="add_rows_length_nomi">
											<td><input type="text" id="nominee_name_<?php echo $i; ?>" name="nominee_name_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_name_'.$i]; ?>"></td>
											
											<td><input type="date" id="nominee_dob_<?php echo $i; ?>" name="nominee_dob_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_dob_'.$i]; ?>"></td>
											
											<td><input type="text" id="nominee_relation_<?php echo $i; ?>" name="nominee_relation_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_relation_'.$i]; ?>"></td>
											
											<td><input type="text" id="nominee_share_<?php echo $i; ?>" name="nominee_share_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_share_'.$i]; ?>"></td>
											
											<td><input type="text" id="nominee_aadhar_<?php echo $i; ?>" name="nominee_aadhar_<?php echo $i; ?>" class="form-control" tabindex="<?php echo $tab++; ?>" value="<?php echo $_POST['nominee_aadhar_'.$i]; ?>"></td>
											
											
											<td>
											<div class="col-md-1 d-flex justify-content- align-items-center">
												<button type="button" id="add_button_nomi" class="btn btn-info pull-right" onClick="add_rows_nomi()">Add</button>
											</div>
											</td>
										</tr>
										
										<?php  } ?>
										<tr>
											<th colspan="6" id="nominee"></th>
										</tr>
										<input type="hidden" name="add_rows_id_nomi" id="add_rows_id_nomi" value="<?php echo $_POST['add_rows_id_nomi']; ?>">
									</table>
								</div>
								<div class="card tab" >
									<h3>Qualifcation Details</h3>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">
											<td>Class</td>
											<td>College Name</td>
											<td>Board</td>
											<td>Roll No.</td>
											<td>Marks</td>
											<td>Percentage</td>
											<td>Grade</td>
											<td>Attach</td>
											<td></td>
											
										</tr>
										<?php 
										for($i=1;$i<=$_POST['add_rows_id'];$i++){
										?>
										<tr id="add_rows_length">
										
											<td> <?php echo $i; ?>.<select class="form-control" name="class_id_<?php echo $i; ?>" id="class_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
												<option value="">--- Select ---</option>
												<?php
												$query = "select * from dp_class";
												$run = execute_query($query);
												while($data = mysqli_fetch_array($run)){
													echo '<option value="'.$data['sno'].'" ';
													if(isset($_POST['edit'])){
														if($_POST['class_id_'.$i]==$data['sno']){
															echo ' selected="Selected"';
														}
													}
													echo '>'.trim($data['class_name']).'</option>';
												}
												?>
											</select></td>
											
											<td><input type="text" name="college_name_<?php echo $i; ?>" id="college_name_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['college_name_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="text" name="board_name_<?php echo $i; ?>" id="board_name_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['board_name_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											<td><input type="text" name="roll_number_<?php echo $i; ?>" id="roll_number_<?php echo $i; ?>" class="form-control" placeholder=""value="<?php echo $_POST['roll_number_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="text" name="obtained_marks_<?php echo $i; ?>" id="obtained_marks_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['obtained_marks_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="text" name="percentage_<?php echo $i; ?>" id="percentage_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['percentage_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											<td><input type="text" name="grade_<?php echo $i; ?>" id="grade_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['grade_'.$i]; ?>" tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="file" name="file_name_<?php echo $i; ?>" id="file_name_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['file_name_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
											
												<?php
													if(!empty($_POST['file_name_'.$i]) && file_exists($_POST['file_name_'.$i])) {
														?>
														<div class="">
															<img src="<?php echo $_POST['file_name_'.$i]; ?>" class="img-fluid img-thumbnail" style="height:50px;"
																id="society_photo_uploaded">
															<label><a href="<?php echo $_POST['file_name_'.$i]; ?>"
																	target="_blank">संलग्न देखें</a></label>

														</div>
														<?php
													}
												?>
											
											</td>
											<td>
											<div class="col-md-1 d-flex justify-content- align-items-center">
												<button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button>
											</div>
											</td>
											
										</tr>
										<?php  } ?>
										<tr>
											<th colspan="9" id="test"></th>
										</tr>
										<input type="hidden" name="add_rows_id" id="add_rows_id" value="<?php echo $_POST['add_rows_id']; ?>">
										
									</table>
								</div>
								<div class="card tab" >
									<h3>Other Attachemnts</h3>
									<table width="100%" class="table table-striped-success table-hover rounded">
										<tr class="table-danger ">										
											<td>Title</td>
											<td>Upload file</td>
											<td></td>
											<td></td>
										</tr>
										<?php 
										for($i=1;$i<=$_POST['add_rows_id1'];$i++){
										?>
										<tr id="add_rows_length1">
											<td><?php echo $i; ?>.<input type="text" name="title_<?php echo $i; ?>" id="title_<?php echo $i; ?>" class="form-control" placeholder=" " value="<?php echo $_POST['title_'.$i]; ?>"  tabindex="<?php echo $tab++; ?>"></td>
											
											<td><input type="file" accept=".jpg, .jpeg, .gif, .png, .bmp" class="form-control" id="attachment_<?php echo $i; ?>" name="attachment_<?php echo $i; ?>" value="<?php echo $_POST['attachment_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
											</td>
											<td>
												<?php
												
													if(!empty($_POST['attachment_'.$i]) && file_exists($_POST['attachment_'.$i])) {
														?>
														<div class="">
															<img src="<?php echo $_POST['attachment_'.$i]; ?>" class="img-fluid img-thumbnail" style="height:50px;"
																id="society_photo_uploaded">
															<label><a href="<?php echo $_POST['attachment_'.$i]; ?>"
																	target="_blank">संलग्न देखें</a></label>

														</div>
														<?php
													}
												?>
											
											</td>
											<td>
												<div class="col-md-1 d-flex justify-content- align-items-center">
													<button type="button" id="add_button1" class="btn btn-info pull-right" onClick="add_rows1()">Add</button>
												</div>
											</td>
										</tr>
										<?php  } ?>
										<tr>
											<th colspan="2" id="test1"></th>
										</tr>
										<input type="hidden" name="add_rows_id1" id="add_rows_id1" value="<?php echo $_POST['add_rows_id1']; ?>">
									</table>
								
									<h3>Other Information</h3>
									<div  class="row border rounded m-2 p-2 border-secondary">
										<div class="col-md-3">							
											<label>Physical handicap</label>
											<input type="checkbox" name="physical_handicap" id="physical_handicap" class="" <?php if($_POST['physical_handicap']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
										</div>
										<div class="col-md-3">							
											<label>Freedom fighter dependent</label>
											<input type="checkbox" name="freedom_fighter" id="freedom_fighter" class="" <?php if($_POST['freedom_fighter']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
										</div>
										<div class="col-md-2">							
											<label>Ex-Army man</label>
											<input type="checkbox" name="ex_army_man" id="ex_army_man" class="" <?php if($_POST['ex_army_man']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
										</div>
										<div class="col-md-2">							
											<label>Female/ladies</label>
											<input type="checkbox" name="ladies" id="ladies" class="" <?php if($_POST['ladies']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
										</div>
										<div class="col-md-2">							
											<label>Other</label>
											<input type="checkbox" name="other" id="other" class="" <?php if($_POST['other']=='1'){echo 'checked="checked"';}?> tabindex="<?php echo $tab++; ?>" value="1">
										</div>
									</div>
								</div>
								<div class="card tab">
									<h3>Appointment Detail</h3>
									<div class="border rounded m-2 p-2 border-secondary" id="">
										<div id="add_rows_length" class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label>Appointment Type</label>
													<select class="form-control" name="appo_id" id="appo_id" tabindex="<?php echo $tab++; ?>" onchange="onchangetype()" >
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_appointment_type where type=1 ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['appo_id'])){
															if($_POST['appo_id']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['appo_type']).'</option>';
													}
													?>
													</select>
												</div>
											</div>
											<div id="hide" style="display:<?php echo $_POST['appo_id']==5?'flex':'none'; ?>;">
												<div class="col-md-4" >
													<div class="form-group">
														<label >Department Name</label>
														<input type="text" name="deputation_dep_name" id="deputation_dep_name" class="form-control" value="<?php echo $_POST['deputation_dep_name']; ?>" tabindex="<?php echo $tab++; ?>">
													</div>
												</div>
												<div class="col-md-4"  >
													<div class="form-group">
														<label >Deputation From </label>
													   <input type="date" name="deputation_from" id="deputation_from" class="form-control" value="<?php echo $_POST['deputation_from']; ?>" tabindex="<?php echo $tab++; ?>">
													</div>
												</div>
												<div class="col-md-4" >
													<div class="form-group">
														<label >Deputation To</label>
													   <input type="date" name="deputation_to" id="deputation_to" class="form-control" value="<?php echo $_POST['deputation_to']; ?>" tabindex="<?php echo $tab++; ?>">
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Appointment No.</label>
													<input type="text" name="appointment_no" id="appointment_no" class="form-control" placeholder="" value="<?php echo $_POST['appointment_no']; ?>" tabindex="<?php echo $tab++; ?>">
												</div>
											</div>
											<div class="col-3">
												<div class="form-group">
													<label >Appointment Date</label>
													<input type="date" name="appointment_date" id="appointment_date" class="form-control" placeholder="" value="<?php echo $_POST['appointment_date']; ?>" tabindex="<?php echo $tab++; ?>">
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Appoint Division</label>
													<select class="form-control" name="appoint_division" id="appoint_division" tabindex="<?php echo $tab++; ?>">
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from uprnss_division order by division_name ASC';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['s_no'].'" ';
														if(isset($_POST['appoint_division'])){
															if($_POST['appoint_division']==$data['s_no']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['division_name'].'</option>';
													}
													?>
												</select>
												</div>          
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Post </label>
													<select class="form-control" name="appoint_post" id="appoint_post" tabindex="<?php echo $tab++; ?>">
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from dp_designation order by abs(sort_no)';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['appoint_post'])){
															if($_POST['appoint_post']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['designation'].'</option>';
													}
													?>
												</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>Pay Scale</label>
													<select name="pay_scale" id="pay_scale" class="form-control" onChange="fill_payband(this.value, 'pay_band_id')" tabindex="<?php echo $tab++; ?>">
														<option value="">--- Select ---</option>
														<option value="1" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 1 ? 'selected' : ''); ?>>5th Pay</option>
														<option value="2" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 2 ? 'selected' : ''); ?>>6th Pay</option>
														<option value="3" <?php echo (isset($_POST['pay_scale']) && $_POST['pay_scale'] == 3 ? 'selected' : ''); ?>>7th Pay</option>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Pay Band</label>
													<select class="form-control" name="pay_band_id" id="pay_band_id" onChange="fill_paylevel(this.value, 'pay_level_id')" tabindex="<?php echo $tab++; ?>" >
													<option value="">--- Select ---</option>
														<?php
														$query = "select * from master_pay_band";
														$run = mysqli_query($db,$query);
														while($data = mysqli_fetch_array($run)){
															echo '<option value="'.$data['sno'].'" ';
															if(isset($_POST['pay_band_id'])){
																if($_POST['pay_band_id']==$data['sno']){
																	echo ' selected="Selected"';
																}
															}
															echo '>'.trim($data['band_name']).'</option>';
														}
														?>	
													</select>
												</div>          
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>Pay Level</label>
													<select class="form-control" name="pay_level_id" id="pay_level_id" onChange="fill_basicpay(this.value, 'grade_pay_id')" tabindex="<?php echo $tab++; ?>" >
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_pay_level ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['pay_level_id'])){
															if($_POST['pay_level_id']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['level_name']).'</option>';
													}
													?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>Basic Pay</label>
													<select class="form-control" name="grade_pay_id" id="grade_pay_id" tabindex="<?php echo $tab++; ?>" >
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_pay_level_grade";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['grade_pay_id'])){
															if($_POST['grade_pay_id']==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['basic_pay']).'</option>';
													}
													?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Joinning Date</label>
													<input type="date" name="joinning_date" id="joinning_date" class="form-control" placeholder="" value="<?php echo $_POST['joinning_date']; ?>" tabindex="<?php echo $tab++; ?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="card tab">
									<h3>Employment Detail</h3>
									<?php
									
									for($i=1;$i<=$_POST['add_rows_id_work'];$i++){
										
									?>
									<?php echo $i; ?>.
									<div class="border rounded m-2 p-2 border-secondary" id="">
										<div id="add_rows_length" class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label>Promotion/ Type</label>
													
													<select class="form-control" name="pramotaion_type_<?php echo $i; ?>" id="pramotaion_type_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" >
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_appointment_type  where type=2 ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['pramotaion_type_'.$i])){
															if($_POST['pramotaion_type_'.$i]==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['appo_type']).'</option>';
													}
													?>
												</select>
												</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Order No.</label>
													<input type="text" name="order_no_<?php echo $i; ?>" id="order_no_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['order_no_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
												</div>
											</div>
											<div class="col-3">
												<div class="form-group">
													<label >Order Date</label>
													<input type="date" name="order_date_<?php echo $i; ?>" id="order_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['order_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Appoint Division</label>
													<select class="form-control" name="place_<?php echo $i; ?>" id="place_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from uprnss_division order by division_name ASC';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['s_no'].'" ';
														if(isset($_POST['place_'.$i])){
															if($_POST['place_'.$i]==$data['s_no']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['division_name'].'</option>';
													}
													?>
												</select>
												</div>          
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Post </label>
													<select class="form-control" name="post_<?php echo $i; ?>" id="post_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>">
													<option value="">--- Select ---</option>
													<?php
													$query = 'select * from dp_designation order by abs(sort_no)';
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['post_'.$i])){
															if($_POST['post_'.$i]==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.$data['designation'].'</option>';
													}
													?>
												</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>Pay Scale</label>
													<select name="pay_scale_<?php echo $i; ?>" id="pay_scale_<?php echo $i; ?>" class="form-control" onChange="fill_payband(this.value, 'pay_band_id_<?php echo $i; ?>')" tabindex="<?php echo $tab++; ?>">
														<option value="">--- Select ---</option>
														<option value="1" <?php echo (isset($_POST['pay_scale_'.$i]) && $_POST['pay_scale_'.$i] == 1 ? 'selected' : ''); ?>>5th Pay</option>
														<option value="2" <?php echo (isset($_POST['pay_scale_'.$i]) && $_POST['pay_scale_'.$i] == 2 ? 'selected' : ''); ?>>6th Pay</option>
														<option value="3" <?php echo (isset($_POST['pay_scale_'.$i]) && $_POST['pay_scale_'.$i] == 3 ? 'selected' : ''); ?>>7th Pay</option>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Pay Band</label>
													<select class="form-control" name="pay_band_id_<?php echo $i; ?>" id="pay_band_id_<?php echo $i; ?>" onChange="fill_paylevel(this.value, 'pay_level_id_<?php echo $i; ?>')" tabindex="<?php echo $tab++; ?>" >
														<option value="">--- Select ---</option>
														<?php
														$query = "select * from master_pay_band";
														$run = mysqli_query($db,$query);
														while($data = mysqli_fetch_array($run)){
															echo '<option value="'.$data['sno'].'" ';
															if(isset($_POST['pay_band_id_'.$i])){
																if($_POST['pay_band_id_'.$i]==$data['sno']){
																	echo ' selected="Selected"';
																}
															}
															echo '>'.trim($data['band_name']).'</option>';
														}
														?>
														</select>
												</div>          
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>Pay Level</label>
													<select class="form-control" name="pay_level_id_<?php echo $i; ?>" id="pay_level_id_<?php echo $i; ?>" onChange="fill_basicpay(this.value, 'grade_pay_id_<?php echo $i; ?>')" tabindex="<?php echo $tab++; ?>" >
													<option value="">--- Select ---</option>
													<?php
													$query = "select * from master_pay_level ";
													$run = mysqli_query($db,$query);
													while($data = mysqli_fetch_array($run)){
														echo '<option value="'.$data['sno'].'" ';
														if(isset($_POST['pay_level_id_'.$i])){
															if($_POST['pay_level_id_'.$i]==$data['sno']){
																echo ' selected="Selected"';
															}
														}
														echo '>'.trim($data['level_name']).'</option>';
													}
													?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label>Basic Pay</label>
													<select class="form-control" name="grade_pay_id_<?php echo $i; ?>" id="grade_pay_id_<?php echo $i; ?>" tabindex="<?php echo $tab++; ?>" >
														<option value="">--- Select ---</option>
														<?php
														$query = "select * from master_pay_level_grade";
														$run = mysqli_query($db,$query);
														while($data = mysqli_fetch_array($run)){
															echo '<option value="'.$data['sno'].'" ';
															if(isset($_POST['grade_pay_id_'.$i])){
																if($_POST['grade_pay_id_'.$i]==$data['sno']){
																	echo ' selected="Selected"';
																}
															}
															echo '>'.trim($data['basic_pay']).'</option>';
														}
														?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label >Joinning Date</label>
													<input type="date" name="joinning_date_<?php echo $i; ?>" id="joinning_date_<?php echo $i; ?>" class="form-control" placeholder="" value="<?php echo $_POST['joinning_date_'.$i]; ?>" tabindex="<?php echo $tab++; ?>">
												</div>
											</div>
											<div class="col-md-1 d-flex justify-content- align-items-center" >
												<button type="button" id="add_button_work" class="btn btn-info pull-right" onClick="add_rows_work()">Add</button>
											</div>
										</div>
									</div>
									<?php }?>
									<div id="test_work"></div>
									<input type="hidden" name="add_rows_id_work" id="add_rows_id_work" value="<?php echo $_POST['add_rows_id_work']; ?>">
								</div>
							</div>
							<div style="overflow:auto;"  class="mx-auto">
								<div style="float:left;">
									<button type="button" class="btn btn-primary pull-right" id="prevBtn" onclick="nextPrev(-1)">&laquo; Previous</button>
									<button type="button" class="btn btn-primary pull-right" id="nextBtn" onclick="nextPrev(1)">Next &raquo;</button>
									<button type="submit" name="submit" id="submit_btn" class="btn btn-success pull-right" onclick="return confirm('Are you sure?');">Submit</button>
									<input type="hidden" id="edit" name="edit" value="<?php echo $_POST['edit']; ?>">
								</div>
							</div>
						</div>
				</div>
			</div>
		</div>
	</form>
<script>
document.getElementById('page-select').addEventListener('change', function() {
    var selectedPage = this.value;

    // Use AJAX to fetch and replace the table data
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'your_data_fetching_script.php?page=' + selectedPage, true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('table-body').innerHTML = xhr.responseText;
        }
    };

    xhr.send();
});
</script>	
	<div class="row">
		<div class="col-12">
			<div class="card">
			
				<div class="card-body text-center">
					<div class="row">
						<div class="col-md-12">
							<h5>Employee Details</h5>
						</div>
					<?php echo $msg1; ?>
					</div>
					<div class="row">
						<div class="col-md-12">
							<table class="table table-striped table-hover table-bordered" id="general_stat_table">
								<thead style="position:sticky;top:0; z-index:2;">
								<tr>
									<th>S.No.</th>
									<th scope="col">Old Employee Code</th>
									<th scope="col">Full Name</th>
									<th scope="col">Division</th>
									<th scope="col">Employee Designation</th>
									<th scope="col">Employee Type</th>
									<th scope="col">Employee Group</th>
						<!---		<th scope="col">Employee Sub-Group</th> --->
									<th scope="col">Category</th>
									<th scope="col">Father Name</th>
									<th scope="col">Contact Number</th>
									<th scope="col">Aadhar Number</th>
									
									<th class="no-print text-center">View</th>
									<th class="no-print text-center">Edit</th>
									<th class="no-print text-center">Delete</th>
								</tr>
								</thead>
								<tbody id="table-body">
									<?php

									$items_per_page = 30;
									$page = isset($_GET['page']) ? $_GET['page'] : 1;
									$offset = ($page - 1) * $items_per_page;
									
									$sql = 'SELECT * FROM dp_personal_info LIMIT ' . $items_per_page . ' OFFSET ' . $offset;
									$result = execute_query($sql);
									$i = $offset + 1;
									while ($row = mysqli_fetch_assoc($result)) {
			
										$sql = 'select * from dp_designation where sno="'.$row['employee_designation_id'].'"';
										$des = execute_query($sql);
										if(mysqli_num_rows($des)!=0){
											$des = mysqli_fetch_assoc($des);
										}
										else{
											unset($des);
											$des['designation'] = '';
										}
										
										$sql = 'select * from dp_type where sno="'.$row['employee_type_id'].'"';
										$etype = execute_query($sql);
										if(mysqli_num_rows($etype)!=0){
											$etype = mysqli_fetch_assoc($etype);
											}
										else{
											unset($etype);
											$etype['type_name'] = '';
										}
										
										$sql = 'select * from dp_category where sno="'.$row['employee_category_id'].'"';
										$ecat = execute_query($sql);
										if(mysqli_num_rows($ecat)!=0){
											$ecat = mysqli_fetch_assoc($ecat);
											}
										else{
											unset($ecat);
											$ecat['category_name'] = '';
										}
										$sql = 'select * from uprnss_division where s_no="'.$row['division_id'].'"';
										$row_div = execute_query($sql);
										if(mysqli_num_rows($row_div)!=0){
											$row_div = mysqli_fetch_assoc($row_div);
											}
										else{
											unset($row_div);
											$row_div['division_name'] = '';
										}
			
										echo '<tr>
										<td>'.$i++.'</td>
										<td>'.$row['old_employee_code'].'</td>
										<td>'.$row['full_name'].'</td>
										<td>'.$row_div['division_name'].'</td>
										<td>'.$des['designation'].'</td>
										<td>'.$etype['type_name'].'</td>
										<td>'.$ecat['category_name'].'</td>
										<td>'.$row['caste'].'</td>
										<td>'.$row['father_name'].'</td>
										<td>'.$row['c_number'].'</td>
										<td>'.$row['aadhar_number'].'</td>
										<td class="no-print"><a href="view_digital_profile.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td>
										<td class="no-print"><a href="master_employees.php?edit='.$row['sno'].'" onClick="return confirm(\'Are you sure?\');"><span class="far fa-edit" aria-hidden="true" data-toggle="tooltip" title="Edit " ></span></a></td>
										<td class="no-print"><a href="master_employees.php?del='.$row['sno'].'" onClick="return confirm(\'Are you sure? \');" > <span class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></span> </a></td>
										</tr>'; 
									}
									?>
								</tbody>
							</table>
							<div class="row">
								<div class="col-md-1">
									<ul class="pagination">
										<?php
										// Calculate the number of pages
										$sql = 'SELECT COUNT(*) FROM dp_personal_info';
										$result = execute_query($sql);
										$total_records = mysqli_fetch_row($result)[0];
										$total_pages = ceil($total_records / $items_per_page);

										// Display the pagination links
										for ($i = 1; $i <= $total_pages; $i++) {
											echo '<li class="page-item ';
											if ($i == $page) {
												echo 'active';
											}
											echo '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>

<!-- ... Your HTML code for displaying the table ... -->




	
	
<?php

page_footer_start();

?>
	<script>
	
	var actionUrl = 'scripts/ajax.php';
	function fill_payband(val, element_id, selected=''){
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"payband", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.band_name+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}
	function fill_paylevel(val, element_id, selected=''){
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"paylevel", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.level_name+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}	
	function fill_basicpay(val, element_id, selected=''){
		console.log(val);
		$("#"+element_id).html('');
		var data = {"term":"b", "id":"basicpay", "val":val};

		$.ajax({
			type: "POST",
			url: actionUrl,
			data: data, // serializes the form's elements.
			success: function(ajaxdata){
				//console.log(ajaxdata);
				var txt = '<option value="">--Select--</option>';
				ajaxdata = JSON.parse(ajaxdata);
				$.each(ajaxdata, function(key, value){
					txt += '<option value="'+value.id+'" ';
					if(value.id==selected){
						txt += ' selected="selected" ';
					}
					txt += '>'+value.basic_pay+'</option>';
					
				});
				$("#"+element_id).html(txt);
			}
		});
	}	
	</script>
	
	<script type="text/javascript">
				function show_info() {
					$("#info_table").toggle();
					
				}
				function copy_adr(){
					 document.getElementById('c_address1').value = document.getElementById('p_address1').value;
					 document.getElementById('c_address2').value = document.getElementById('p_address2').value;
					 document.getElementById('c_post').value = document.getElementById('p_post').value;
					 document.getElementById('c_tehseel').value = document.getElementById('p_tehseel').value;
					 document.getElementById('c_block').value = document.getElementById('p_block').value;
					 document.getElementById('c_district').value = document.getElementById('p_district').value;
					 document.getElementById('c_pin').value = document.getElementById('p_pin').value;
					 document.getElementById('c_state').value = document.getElementById('p_state').value;
				}
		
			function add_rows(){
				var id = parseFloat($("#add_rows_id").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = id+'<tr id="add_rows_length"><td><select class="form-control" name="class_id_'+id+'" id="class_id_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from dp_class";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['class_name'].'</option>";'."\n";
				}
				?>
				txt += '</select></td><td><input type="text" name="college_name_'+id+'" id="college_name_'+id+'" class="form-control" placeholder="" value=""></td><td><input type="text" name="board_name_'+id+'" id="board_name_'+id+'" class="form-control" placeholder="" value=""></td><td><input type="text" name="roll_number_'+id+'" id="roll_number_'+id+'" class="form-control" placeholder="" value=""></td><td><input type="text" name="obtained_marks_'+id+'" id="obtained_marks_'+id+'" class="form-control" placeholder="" value=""></td><td><input type="text" name="percentage_'+id+'" id="percentage_'+id+'" class="form-control" placeholder="" value=""></td><td><input type="text" name="grade_'+id+'" id="grade_'+id+'" class="form-control" placeholder="" value=""></td><td><input type="file" name="file_name_'+id+'" id="file_name_'+id+'" class="form-control" placeholder="" value=""></td><td><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></td></tr>';
				$("#test").append(txt);
				$("#add_rows_id").val(id);
			}
			
			function add_rows1(){
				var id = parseFloat($("#add_rows_id1").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button1").remove();
				
				var txt = id+'<tr id="add_rows_length1"><td width="50%"><input type="text" name="title_'+id+'" id="title_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></td><td  width="50%"><input type="file" accept=".jpg, .jpeg, .gif, .png, .bmp" class="form-control" id="attachment_'+id+'" name="attachment_'+id+'" value="" tabindex=""></td><td><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button1" class="btn btn-info pull-right" onClick="add_rows1()">Add</button></div></td></tr>';
				$("#test1").append(txt);
				$("#add_rows_id1").val(id);
			}
			
			function add_rows_nomi(){
				var id = parseFloat($("#add_rows_id_nomi").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button_nomi").remove();
				
				var txt = id+'<tr id="add_rows_length_nomi"><td><input type="text" name="nominee_name_'+id+'" id="nominee_name_'+id+'" class="form-control" tabindex="" value=""></td><td><input type="date" id="nominee_dob_'+id+'" name="nominee_dob_'+id+'" class="form-control" tabindex="" value=""></td><td><input type="text" id="nominee_relation_'+id+'" name="nominee_relation_'+id+'" class="form-control" tabindex="" value=""></td><td><input type="text" id="nominee_share_'+id+'" name="nominee_share_'+id+'" class="form-control" tabindex="" value=""></td><td><input type="text" id="nominee_aadhar_'+id+'" name="nominee_aadhar_'+id+'" class="form-control" tabindex="" value=""></td><td><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button_nomi" class="btn btn-info pull-right" onClick="add_rows_nomi()">Add</button></div></td></tr>';
				$("#nominee").append(txt);
				$("#add_rows_id_nomi").val(id);
			}
			
			function add_rows_work(){
				var id = parseFloat($("#add_rows_id_work").val());
				if(!id){
					id=0;
				}
				/*for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}*/
				id = id+1;
				var date_1 = DateInput('transaction_date_'+id, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = id+'.<div class="border rounded m-2 p-2 border-secondary" id=""><div id="add_rows_length" class="row"><div class="col-md-3"><div class="form-group"><label>Appointment Type</label><select class="form-control" name="pramotaion_type_'+id+'" id="pramotaion_type_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = "select * from master_appointment_type where type=2";
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['appo_type'].'</option>";'."\n";
				}
				?>
				txt += '</select></div></div><div class="col-md-3"><div class="form-group"><label >Order No.</label><input type="text" name="order_no_'+id+'" id="order_no_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-3"><div class="form-group"><label >Order Date</label><input type="date" name="order_date_'+id+'" id="order_date_'+id+'" class="form-control" placeholder="" value="<" tabindex=""></div></div><div class="col-md-3 "><div class="form-group"><label>Appointment Division</label><select class="form-control" name="place_'+id+'" id="place_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from uprnss_division order by division_name ASC';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['s_no'].'\'>'.$data['division_name'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3 "><div class="form-group"><label>post</label><select class="form-control" name="post_'+id+'" id="post_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from dp_designation order by abs(sort_no)';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['designation'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3"><div class="form-group"><label>Pay Scale</label><select name="pay_scale_'+id+'" id="pay_scale_'+id+'" class="form-control" onChange="fill_payband(this.value, \'pay_band_id_'+id+'\')" ><option value="">--- Select ---</option><option value="1" >5th Pay</option><option value="2" >6th Pay</option><option value="3" >7th Pay</option></select></div></div><div class="col-md-3 "><div class="form-group"><label>Pay Band</label><select class="form-control" name="pay_band_id_'+id+'" id="pay_band_id_'+id+'" onChange="fill_paylevel(this.value, \'pay_level_id_'+id+'\')" ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from master_pay_band ';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['band_name'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3 "><div class="form-group"><label>Pay Level</label><select class="form-control" name="pay_level_id_'+id+'" id="pay_level_id_'+id+'" onChange="fill_basicpay(this.value, \'grade_pay_id_'+id+'\')" ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from master_pay_level ';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['level_name'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3 "><div class="form-group"><label>Basic Pay</label><select class="form-control" name="grade_pay_id_'+id+'" id="grade_pay_id_'+id+'"  ><option value="">--- Select ---</option>';
				<?php
				$query = 'select * from master_pay_level_grade ';
				$run = mysqli_query($db,$query);
				while($data = mysqli_fetch_array($run)){
					echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['basic_pay'].'</option>";'."\n";
				}
				?>
				txt += '</select>';
				txt += '</div></div><div class="col-md-3"><div class="form-group"><label >Joinning Date</label><input type="date" name="joinning_date_'+id+'" id="joinning_date_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button_work" class="btn btn-info pull-right" onClick="add_rows_work()">Add</button></div></div></div>';
				$("#test_work").append(txt);
				$("#add_rows_id_work").val(id);
			}
			
			
						
	</script>

	
	<script>
		var currentTab = 0; // Current tab is set to be the first tab (0)
		showTab(currentTab); // Display the current tab

		function showTab(n) {
		  // This function will display the specified tab of the form...
		  var x = document.getElementsByClassName("tab");
		  x[n].style.display = "block";
		  //... and fix the Previous/Next buttons:
		  if (n == 0) {
			document.getElementById("prevBtn").style.display = "none";
			document.getElementById("submit_btn").style.display = "none";
		  } else {
			document.getElementById("prevBtn").style.display = "inline";
		  }
		  if (n == (x.length - 1)) {
			// document.getElementById("nextBtn").innerHTML = "Submit";
			document.getElementById("nextBtn").style.display = "none";
			document.getElementById("submit_btn").style.display = "inline";
		  } else {
			document.getElementById("nextBtn").style.display = "inline";
			document.getElementById("submit_btn").style.display = "none";
		  }
		  //... and run a function that will display the correct step indicator:
		  fixStepIndicator(n)
		}

		function nextPrev(n) {
		  // This function will figure out which tab to display
		  var x = document.getElementsByClassName("tab");
		  // Exit the function if any field in the current tab is invalid:
		  if (n == 1 && !validateForm()) return false;
		  // Hide the current tab:
		  x[currentTab].style.display = "none";
		  // Increase or decrease the current tab by 1:
		  currentTab = currentTab + n;
		  // if you have reached the end of the form...
		  if (currentTab >= x.length) {
			// ... the form gets submitted:
			document.getElementById("employees_form").submit();
			return false;
		  }
		  // Otherwise, display the correct tab:
		  showTab(currentTab);
		}

		function validateForm() {
		  // This function deals with validation of the form fields
		  var x, y, i, valid = true;
		  x = document.getElementsByClassName("tab");
		  y = x[currentTab].getElementsByTagName("input");
		  // A loop that checks every input field in the current tab:
		  // for (i = 0; i < y.length; i++) {
			// // If a field is empty...
			// if (y[i].value == "") {
			  // // add an "invalid" class to the field:
			  // y[i].className += " invalid";
			  // // and set the current valid status to false
			  // valid = false;
			// }
		  // }
		  // If the valid status is true, mark the step as finished and valid:
		  if (valid) {
			document.getElementsByClassName("step")[currentTab].className += " finish";
		  }
		  return valid; // return the valid status
		}

		function fixStepIndicator(n) {
		  // This function removes the "active" class of all steps...
		  var i, x = document.getElementsByClassName("step");
		  for (i = 0; i < x.length; i++) {
			x[i].className = x[i].className.replace(" active", "");
		  }
		  //... and adds the "active" class on the current step:
		  x[n].className += " active";
		}

	</script>

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>


<?php		

page_footer_end();

?>