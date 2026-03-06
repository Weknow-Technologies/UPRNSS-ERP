<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';

if(isset($_POST['submit'])){
	if($_POST['edit_sno']==''){ 
		if(isset($_POST['monthlytarget_1']) && $_POST['monthlytarget_1']!=""){
			$sql='insert into invoice_prarup_14 (entry_date, total_trans, division_id, created_by, creation_time) values("'.date('d-m-Y').'", "'.$_POST['prarup_14_id'].'","'.$_POST['division_id'].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				
				$inv_id = mysqli_insert_id($db);
		
				for($i=1; $i<=$_POST['prarup_14_id']; $i++){
					$sql = 'insert into trans_prarup_14 (`invoice_id`, `monthlytarget`, `monthprogress`, `totalprogress`, `swekritdhanrashi`, `avmuktdhanrashi`, `vyadhanrashi`, created_by, creation_time) values ("'.$inv_id.'", "'.$_POST['monthlytarget_'.$i].'", "'.$_POST['monthprogress_'.$i].'", "'.$_POST['totalprogress_'.$i].'", "'.$_POST['swekritdhanrashi_'.$i].'", "'.$_POST['avmuktdhanrashi_'.$i].'", "'.$_POST['vyadhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
					execute_query($sql);
					if(mysqli_error($db)){ 
						$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
					}
				}
				if($msg==""){
					$msg="<p class='alert alert-success'>Data stored all</p>";
					unset($_POST);
					goto postblank;
				}
			}
		}
		else{
			echo '<script>alert("Please Fill Form properly!");</script>';
		}
	}
	else{
		$sql = 'update invoice_prarup_14 set 
			`total_trans`= "'.$_POST['prarup_14_id'].'",
			`edited_by` = "'.$_SESSION['username'].'", 
			`edition_time` = "'.date("d-m-Y H:i:s").'"
			where sno="'.$_POST['edit_sno'].'"';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1.1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$sql = 'delete from trans_prarup_14 where invoice_id="'.$_POST['edit_sno'].'"';
				// echo $sql;
				execute_query($sql);
				for($i=1; $i<=$_POST['prarup_14_id']; $i++){
					if(isset($_POST['monthlytarget_'.$i]) && $_POST['monthlytarget_'.$i]!=""){

						$sql = 'insert into trans_prarup_14 (`invoice_id`, `monthlytarget`, `monthprogress`, `totalprogress`, `swekritdhanrashi`, `avmuktdhanrashi`, `vyadhanrashi`, created_by, creation_time) values ("'.$_POST['edit_sno'].'", "'.$_POST['monthlytarget_'.$i].'", "'.$_POST['monthprogress_'.$i].'", "'.$_POST['totalprogress_'.$i].'", "'.$_POST['swekritdhanrashi_'.$i].'", "'.$_POST['avmuktdhanrashi_'.$i].'", "'.$_POST['vyadhanrashi_'.$i].'", "'.$_SESSION['username'].'", "'.date("d-m-Y H:i:s").'");';
						execute_query($sql);
						if(mysqli_error($db)){ 
							$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
						}
					}
				}				
				if($msg==''){
						$msg .= '<p class="alert alert-info text-dark">Data Update!</p>';
						unset($_POST);
						goto postblank;
				}	
			}
			
	}
}
else{
	postblank:
	$_POST['monthlytarget_1']="";
	$_POST['monthprogress_1']="";
	$_POST['totalprogress_1']="";
	$_POST['swekritdhanrashi_1']="";
	$_POST['avmuktdhanrashi_1']="";
	$_POST['vyadhanrashi_1']="" ;


	$_POST['edit_sno']="";
	$_POST['prarup_14_id']="1";
}



	if(isset($_GET['delid'])){
	
		$sql = 'delete from invoice_prarup_14 where sno="'.$_GET['delid'].'"';
		execute_query($sql);
		$sql = 'delete from trans_prarup_14 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}else{
			$msg .= '<div class="alert alert-warning">Data Delete</div>';
		}
		
	}	
	if(isset($_GET['edit_sno'])){
		
			$sql = 'select * from trans_prarup_14 where invoice_id="'.$_GET['edit_sno'].'"';
			// echo $sql.'<br>';
			$result_rcpt = execute_query($sql);
			if(mysqli_num_rows($result_rcpt)!=0){
				$_POST['prarup_14_id'] = mysqli_num_rows($result_rcpt);
				$i=1;
				while($row_rcpt = mysqli_fetch_assoc($result_rcpt)){
					// echo '<h1>Test '.$i.'</h1>'.$_POST['prarup_14_id'].'<br/>';
					$_POST['monthlytarget_'.$i]=$row_rcpt['monthlytarget'];
					$_POST['monthprogress_'.$i]=$row_rcpt['monthprogress'];
					$_POST['totalprogress_'.$i]=$row_rcpt['totalprogress'];
					$_POST['swekritdhanrashi_'.$i]=$row_rcpt['swekritdhanrashi'];
					$_POST['avmuktdhanrashi_'.$i]=$row_rcpt['avmuktdhanrashi'];
					$_POST['vyadhanrashi_'.$i]=$row_rcpt['vyadhanrashi'];
					
					$i++;
				}
			}
			else{
				$_POST['monthlytarget_1']="";
				$_POST['monthprogress_1']="";
				$_POST['totalprogress_1']="";
				$_POST['swekritdhanrashi_1']="";
				$_POST['avmuktdhanrashi_1']="";
				$_POST['vyadhanrashi_1']="" ;

			}
		$_POST['edit_sno']=$_GET['edit_sno'];
	}

?>
<?php //echo $_SERVER['PHP_SELF']; ?>
<?php //echo $_SESSION['unit_name']; ?>

	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">अपूर्ण परियोजनाओ का विवरण</h4></br>
					</div>
						<?php echo $msg; ?>
					<div class="card-body">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label >प्रखण्ड का नाम</label>
									<input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION["unit_name"]; ?>" readonly \>
									<input type="hidden" name="division_id" id="division_id" class="form-control" placeholder=" " value="<?php echo $_SESSION["divisions"][0];?>" readonly \>
								</div>
							</div>
						</div>
						<?php
						// echo '<h1>'.$_POST['prarup_14_id'].'</h1>';
						for($i=1;$i<= $_POST['prarup_14_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-3">
								<label>परियोजना का नाम</label>
								<select class="form-control" name="monthlytarget_<?php echo $i; ?>" id="monthlytarget_<?php echo $i; ?>">
									<option value="">--- Select ---</option>
									<?php
									$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
									$run = mysqli_query($db, $query);
									while ($data = mysqli_fetch_array($run)) {
										echo '<option value="' . $data['sno'] . '" ';
										if (isset($_POST['monthlytarget_' . $i])) {
											if ($_POST['monthlytarget_' . $i] == $data['sno']) {
												echo ' selected="Selected"';
											}
										}
										echo '>' . trim($data['project_name_hindi']) . '</option>';
									}
									?>
								</select>
							</div>
							<div class="col-md-3">
								<label>जी०ए०सी०टी० राशि प्राप्त न होने के कारण</label>
								<input type="text" class="form-control" name="monthprogress_<?php echo $i; ?>" id="monthprogress_<?php echo $i; ?>" value="<?php echo $_POST['monthprogress_' . $i]; ?>">
							</div>
							<div class="col-md-3">
								<label>टी०डी०एस० की राशि प्राप्त न होने के कारण</label>
								<input type="text" class="form-control" name="totalprogress_<?php echo $i; ?>" id="totalprogress_<?php echo $i; ?>" value="<?php echo $_POST['totalprogress_' . $i]; ?>">
							</div>
							<div class="col-md-3">
								<label>किश्त न मिलने के कारण</label>
								<input type="text" class="form-control" name="swekritdhanrashi_<?php echo $i; ?>" id="swekritdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['swekritdhanrashi_' . $i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अन्य कारण यदि कोई हो</label>
								<input type="text" class="form-control" name="avmuktdhanrashi_<?php echo $i; ?>" id="avmuktdhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['avmuktdhanrashi_' . $i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अभ्युक्ति</label>
								<input type="text" class="form-control" name="vyadhanrashi_<?php echo $i; ?>" id="vyadhanrashi_<?php echo $i; ?>" value="<?php echo $_POST['vyadhanrashi_' . $i]; ?>">
							</div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="prarup_14_add_button" class="btn btn-info pull-right" onClick="prarup_14_add_rows()">Add</button>
							</div>
                    	</div>
						<?php 
						}
						?>
					
							<input type="hidden" name="prarup_14_id" id="prarup_14_id" value="<?php echo $_POST['prarup_14_id']; ?>">
							<div id="prarup_14_insert"></div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<!--<button type="submit" name="submit" class="btn btn-success">Submit</button> -->
								<input type="hidden" id="edit_sno" name="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
								<?php 
									if (isset($_GET['edit_sno']) && $_GET['edit_sno'] != "") {
										echo'<button type="submit" name="submit" class="btn btn-success">Update</button>
										<a href="prarup_new_14.php" class="btn btn-info">Back</a>';
									}else{
										echo '<button type="submit" name="submit" class="btn btn-success">Submit</button>';
										$_GET['edit_sno']="";
									}	
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	<script>
	function prarup_14_add_rows(){
       
	   var id = parseFloat($("#prarup_14_id").val());
	   if(!id){
		   id=0;
	   }
	   id = id+1;
	   $("#prarup_14_add_button").remove();
	   var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>परियोजना का नाम </label>';
	   txt += '<select class="form-control" name="monthlytarget_'+id+'" id="monthlytarget_'+id+'" ><option value="">--- Select--</option>';
	   <?php
	   $query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
	   $run = mysqli_query($db,$query);
	   while($data = mysqli_fetch_array($run)){
		   echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
	   }
	   ?>
	   txt+='</select>';
	   txt+= '</div><div class="col-md-3"><label>जी०एस०टी० राशि प्राप्त न होने के कारण</label><input type="text" class="form-control" name="monthprogress_'+id+'" id="monthprogress_'+id+'" value="" ></div><div class="col-md-3"><label>टी०डी०एस० की राशि प्राप्त न होने के कारण</label><input type="text" class="form-control" name="totalprogress_'+id+'" id="totalprogress_'+id+'" value="" ></div><div class="col-md-3"><label>किश्त न मिलने के कारण</label><input type="text" class="form-control" name="swekritdhanrashi_'+id+'" id="swekritdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>अन्य कारण यदि कोई हो</label><input type="text" class="form-control" name="avmuktdhanrashi_'+id+'" id="avmuktdhanrashi_'+id+'" value="" ></div><div class="col-md-3"><label>अभ्युक्ति</label><input type="text" class="form-control" name="vyadhanrashi_'+id+'" id="vyadhanrashi_'+id+'" value="<" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_14_add_button" class="btn btn-info pull-right" onClick="prarup_14_add_rows()">Add</button></div></div>';
		   $("#prarup_14_insert").append(txt);
		   $("#prarup_14_id").val(id);
   }
	</script>
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">अपूर्ण परियोजनाओ का विवरण </h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>delete</th>
									<th>View & Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if($_SESSION['usertype']=='1'){
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_14` where invoice_prarup_14.created_by = "'.$_SESSION['username'].'"';
							}else{
								$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_14`';
							}
							
							$result_prarup_new_4 = execute_query($sql);
							$a=1;
							while($row_prarup_new_4 = mysqli_fetch_assoc($result_prarup_new_4)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_4['division_id'].'"';
								$row_div = mysqli_fetch_assoc(execute_query($sql));
								$row_div = execute_query($sql);
								if(mysqli_num_rows($row_div)!=0){
								$row_div = mysqli_fetch_assoc($row_div);
								}
								else{
									unset($row_div);
									$row_div['division_name'] = '';
								}
								
								echo '<tr>
								<td>'.$a++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_4['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_4['sno'].'" onClick="return confirm(\'Are you sure you? you want to Delete\');" ><i class="far fa-trash-alt"></i></a></td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?edit_sno='.$row_prarup_new_4['sno'].'" ><i class="far fa-edit"></i></a></td>
								</tr>';
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

    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
	<script src="js/light-bootstrap-dashboard.js?v=1.4.0"></script>
<script>

$('select[multiple]').multiselect();
</script>

    
<?php		
page_footer_end();
?>