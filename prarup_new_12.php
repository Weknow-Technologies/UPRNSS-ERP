<?php
include("scripts/settings.php");
page_header_start();
page_header_end();
page_sidebar();
$i=1;
$msg='';
?>
	<script>
		function prarup_new_12_add_rows(){
       
			var id = parseFloat($("#prarup_new_12_id").val());
			if(!id){
				id=0;
			}
			id = id+1;
			$("#prarup_new_12_add_button").remove();
			var txt='<div class="row border rounded m-2 p-2 border-secondary"><div class="col-md-3"><label>परियोजना का नाम</label><select class="form-control" name="pariyojnaname_'+id+'" id="pariyojnaname_'+id+'" ><option value="">--- Select--</option>';
			<?php
			$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
			$run = mysqli_query($db,$query);
			while($data = mysqli_fetch_array($run)){
				echo 'txt += "<option value=\''.$data['sno'].'\'>'.$data['project_name_hindi'].'</option>";'."\n";
			}
			?>
			txt += '</select></div><div class="col-md-3"><label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control"  value="" tabindex=""><option value="Select">Select--</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div><div class="col-md-3"><label>कन्टीजेन्सी की धनराशि</label><input type="text" class="form-control" name="contigencyrupee_'+id+'" id="contigencyrupee_'+id+'" value="<?php echo isset($_GET['editid']) ? $editid['contigencyrupee_'.$i] : ''; ?>" ></div><div class="col-md-3"><label>मद का नाम जिस हेतु कन्टीजेन्सी से व्यय किया गया</label><input type="text" class="form-control" name="contigencyvya_'+id+'" id="contigencyvya_'+id+'" value="<?php echo isset($_GET['editid']) ? $editid['contigencyvya_'.$i] : ''; ?>" ></div><div class="col-md-3"><label>कन्टीजेन्सी से माह में व्यय</label><input type="text" class="form-control" name="contengencymonvya_'+id+'" id="contengencymonvya_'+id+'" value="<?php echo isset($_GET['editid']) ? $editid['contengencymonvya_'.$i] : ''; ?>" ></div><div class="col-md-3"><label>कन्ट्रीजेन्सी से क्रमिक व्यय</label><input type="text" class="form-control" name="kramikvya_'+id+'" id="kramikvya_'+id+'" value="<?php echo isset($_GET['editid']) ? $editid['kramikvya_'.$i] : ''; ?>" ></div><div class="col-md-3"><label>अवशेष कन्टीजेन्सी</label><input type="text" class="form-control" name="restcontigency_'+id+'" id="restcontigency_'+id+'" value="<?php echo isset($_GET['editid']) ? $editid['restcontigency_'.$i] : ''; ?>" ></div><div class="col-md-3"><label>अभियुक्ति</label><input type="text" class="form-control" name="remark_'+id+'" id="remark_'+id+'" value="<?php echo isset($_GET['editid']) ? $editid['remark_'.$i] : ''; ?>" ></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="prarup_new_12_add_button" class="btn btn-info pull-right" onClick="prarup_new_12_add_rows()">Add</button></div></div>';
				$("#prarup_new_12_insert").append(txt);
				$("#prarup_new_12_id").val(id);
		}
	
</script>
<?php
// print_r($_POST);
if (isset($_POST['submit'])) {
    if ($_POST['editid'] == "") {
        if ($_POST['pariyojnaname_1'] != "") {
            $sql = 'insert into invoice_prarup_new_12 (entry_date, total_trans, division_id, created_by, creation_time) values("' . date("Y-m-d") . '", "' . $_POST['prarup_new_12_id'] . '","' . $_POST['division_id'] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '");';
            execute_query($sql);
            if (mysqli_error($db)) {
                $msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
            } else {

                $inv_id = mysqli_insert_id($db);

                for ($i = 1; $i <= $_POST['prarup_new_12_id']; $i++) {
                    if ($_POST['pariyojnaname_' . $i] != "") {
                        $sql = 'insert into trans_prarup_new_12 (`invoice_id`, `pariyojnaname`,`month`, `contigencyrupee`, `contigencyvya`, `contengencymonvya`, `kramikvya`, `restcontigency`, `remark`, created_by, creation_time) values ("' . $inv_id . '", "' . $_POST['pariyojnaname_' . $i] . '","' . $_POST['month_' . $i] . '", "' . $_POST['contigencyrupee_' . $i] . '", "' . $_POST['contigencyvya_' . $i] . '", "' . $_POST['contengencymonvya_' . $i] . '", "' . $_POST['kramikvya_' . $i] . '", "' . $_POST['restcontigency_' . $i] . '", "' . $_POST['remark_' . $i] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '");';
                        execute_query($sql);
                        if (mysqli_error($db)) {
                            $msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
                        }
                    }
                }
                if ($msg == "") {
                    $msg = "<p class='alert alert-success'>Data stored all</p>";
                    unset($_POST);
                    goto postblank;
                }
            }
        } else {
            echo '<script>alert("Please Fill Form properly!");</script>';
        }
    } else {
        // Edit mode
        $sql = 'update invoice_prarup_new_12 set 
				`total_trans`= "' . $_POST['prarup_new_12_id'] . '",
				`edited_by` = "' . $_SESSION['username'] . '", 
				`edition_time` = "' . date("Y-m-d H:i:s") . '"
				where sno="' . $_POST['editid'] . '"';
        execute_query($sql);

        if (mysqli_error($db)) {
            $msg .= '<p class="text text-danger">Error # 1.1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
        } else {
            // Delete existing trans_prarup_new_12 records for the edited invoice
            $sql = 'delete from trans_prarup_new_12 where invoice_id="' . $_POST['editid'] . '"';
            execute_query($sql);

            // Insert updated data into trans_prarup_new_12
            for ($i = 1; $i <= $_POST['prarup_new_12_id']; $i++) {
                if ($_POST['pariyojnaname_' . $i] != "") {
                    $sql = 'insert into trans_prarup_new_12 (`invoice_id`, `pariyojnaname`,`month`, `contigencyrupee`, `contigencyvya`, `contengencymonvya`, `kramikvya`, `restcontigency`, `remark`, created_by, creation_time) values ("' . $_POST['editid'] . '", "' . $_POST['pariyojnaname_' . $i] . '","' . $_POST['month_' . $i] . '", "' . $_POST['contigencyrupee_' . $i] . '", "' . $_POST['contigencyvya_' . $i] . '", "' . $_POST['contengencymonvya_' . $i] . '", "' . $_POST['kramikvya_' . $i] . '", "' . $_POST['restcontigency_' . $i] . '", "' . $_POST['remark_' . $i] . '", "' . $_SESSION['username'] . '", "' . date("Y-m-d H:i:s") . '");';
                    execute_query($sql);
                    if (mysqli_error($db)) {
                        $msg .= '<p class="text text-danger">Error # 1 : ' . mysqli_error($db) . '>> ' . $sql . '</p>';
                    } else {
                    }
                }
            }
            if ($msg == '') {
                $msg .= '<p class="alert alert-info text-dark">Data Update!</p>';
                unset($_POST);
                goto postblank;
            }
        }
    }
} else {
	postblank:
    $_POST['prarup_new_12_id'] = "1";
    $_POST['editid'] = "";

    for ($i = 1; $i <= $_POST['prarup_new_12_id']; $i++) {
        $_POST['pariyojnaname_' . $i] = "";
        $_POST['month_' . $i] = "";
        $_POST['contigencyrupee_' . $i] = "";
        $_POST['contigencyvya_' . $i] = "";
        $_POST['contengencymonvya_' . $i] = "";
        $_POST['kramikvya_' . $i] = "";
        $_POST['restcontigency_' . $i] = "";
        $_POST['remark_' . $i] = "";
    }
}

if (isset($_GET['editid'])) {

    $sql_invoice = 'select * from invoice_prarup_new_12 where sno="' . $_GET['editid'] . '"';
    $result_invoice = execute_query($sql_invoice);

    if (mysqli_num_rows($result_invoice) != 0) {

        $row_invoice = mysqli_fetch_assoc($result_invoice);

        $_POST['prarup_new_12_id'] = $row_invoice['total_trans'];
        $_POST['division_id'] = $row_invoice['division_id'];

        $sql_trans = 'select * from trans_prarup_new_12 where invoice_id="' . $_GET['editid'] . '"';
        $result_trans = execute_query($sql_trans);

        if (mysqli_num_rows($result_trans) != 0) {
            $i = 1;
            while ($row_trans = mysqli_fetch_assoc($result_trans)) {
                $_POST['pariyojnaname_' . $i] = $row_trans['pariyojnaname'];
                $_POST['month_' . $i] = $row_trans['month'];
                $_POST['contigencyrupee_' . $i] = $row_trans['contigencyrupee'];
                $_POST['contigencyvya_' . $i] = $row_trans['contigencyvya'];
                $_POST['contengencymonvya_' . $i] = $row_trans['contengencymonvya'];
                $_POST['kramikvya_' . $i] = $row_trans['kramikvya'];
                $_POST['restcontigency_' . $i] = $row_trans['restcontigency'];
                $_POST['remark_' . $i] = $row_trans['remark'];

                $i++;
            }
        } else {
            for ($i = 1; $i <= $_POST['prarup_new_12_id']; $i++) {
                $_POST['pariyojnaname_' . $i] = "";
                $_POST['month_' . $i] = "";
                $_POST['contigencyrupee_' . $i] = "";
                $_POST['contigencyvya_' . $i] = "";
                $_POST['contengencymonvya_' . $i] = "";
                $_POST['kramikvya_' . $i] = "";
                $_POST['restcontigency_' . $i] = "";
                $_POST['remark_' . $i] = "";
            }
        }

        $_POST['editid'] = $_GET['editid'];
    } else {
        // Handle the case where the invoice entry is not found
    }
}

if(isset($_GET['delid'])){
	
	$sql = 'delete from invoice_prarup_new_12 where sno="'.$_GET['delid'].'"';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		
	}
	else{
		$sql = 'delete from trans_prarup_new_12 where invoice_id="'.$_GET['delid'].'"';
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		else{
			$msg .= '<div class="alert alert-warning text-dark">Data Delete</div>';
		}
	}		
}
?>


	<form  autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्ड पर उपलब्ध कन्टीजेन्सी का विवरण </h4></br>
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
							for($i=1;$i<= $_POST['prarup_new_12_id'];$i++){
						?>
						<div class="row border rounded m-2 p-2 border-secondary">
							<div class="col-md-3">
								<label>परियोजना का नाम</label>
								<select class="form-control" name="pariyojnaname_<?php echo $i; ?>" id="pariyojnaname_<?php echo $i; ?>"  onChange="fill_district(this.value), fill_sub_department(this.value)">
									<option value="">--- Select ---</option>
									<?php
										$query = '(SELECT sno, project_name_hindi, Project_name FROM uprnss_project_temp WHERE division_id IN (' . implode(",", $_SESSION['divisions']) . '))';
										// echo $query;
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['pariyojnaname_'.$i])){
												if($_POST['pariyojnaname_'.$i]==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['project_name_hindi']).'</option>';
										}
									?>
								</select>
							</div>
							<div class="col-md-3">
							<label >माह</label>
								<select name="month_<?php echo $i; ?>" id="month_<?php echo $i; ?>" class="form-control"  value="<?php echo isset($_GET['editid']) ? $editrow['month_'.$i] : ''; ?>" >
								<option value="">--Select--</option>
									<option value="January"<?php echo ($_POST['month_'.$i]=="January" ? 'selected' : ''); ?> >January</option>
									<option value="February"<?php echo ($_POST['month_'.$i]=="February"?'selected':''); ?> >February</option>
									<option value="March"<?php echo ($_POST['month_'.$i]=="March"?'selected':''); ?> >March</option>
									<option value="April"<?php echo ($_POST['month_'.$i]=="April"?'selected':''); ?> >April</option>
									<option value="May"<?php echo ($_POST['month_'.$i]=="May"?'selected':''); ?> >May</option>
									<option value="June"<?php echo ($_POST['month_'.$i]=="June"?'selected':''); ?> >June</option>
									<option value="July"<?php echo ($_POST['month_'.$i]=="July"?'selected':''); ?> >July</option>
									<option value="August"<?php echo ($_POST['month_'.$i]=="August"?'selected':''); ?> >August</option>
									<option value="September"<?php echo ($_POST['month_'.$i]=="September"?'selected':''); ?> >September</option>
									<option value="October"<?php echo ($_POST['month_'.$i]=="October"?'selected':''); ?> >October</option>
									<option value="November"<?php echo ($_POST['month_'.$i]=="November"?'selected':''); ?> >November</option>
									<option value="December"<?php echo ($_POST['month_'.$i]=="December"?'selected':''); ?> >December</option>
								
								</select>
							</div>
							<div class="col-md-3">
								<label>कन्टीजेन्सी की धनराशि</label>
								<input type="text" class="form-control" name="contigencyrupee_<?php echo $i; ?>" id="contigencyrupee_<?php echo $i; ?>" value="<?php echo $_POST['contigencyrupee_' . $i] ?>">
							</div>
							<div class="col-md-3">
								<label>मद का नाम जिस हेतु कन्टीजेन्सी से व्यय किया गया</label>
								<input type="text" class="form-control" name="contigencyvya_<?php echo $i; ?>" id="contigencyvya_<?php echo $i; ?>" value="<?php echo $_POST['contigencyvya_' . $i]  ?>">
							</div>
							<div class="col-md-3">
								<label>कन्टीजेन्सी से माह में व्यय</label>
								<input type="text" class="form-control" name="contengencymonvya_<?php echo $i; ?>" id="contengencymonvya_<?php echo $i; ?>" value="<?php echo $_POST['contengencymonvya_' . $i]; ?>">
							</div>
							<div class="col-md-3">
								<label>कन्ट्रीजेन्सी से क्रमिक व्यय</label>
								<input type="text" class="form-control" name="kramikvya_<?php echo $i; ?>" id="kramikvya_<?php echo $i; ?>" value="<?php echo $_POST['kramikvya_' . $i]; ?>">
							</div>
							<div class="col-md-3">
								<label>अवशेष कन्टीजेन्सी</label>
								<input type="text" class="form-control" name="restcontigency_<?php echo $i; ?>" id="restcontigency_<?php echo $i; ?>" value="<?php echo $_POST['restcontigency_' . $i] ; ?>">
							</div>
							<div class="col-md-3">
								<label>अभियुक्ति</label>
								<input type="text" class="form-control" name="remark_<?php echo $i; ?>" id="remark_<?php echo $i; ?>" value="<?php echo $_POST['remark_' . $i]; ?>">
							</div>
							<?php 
								
									if($i==$_POST['prarup_new_12_id']){
										?>
											<div class="col-md-1 d-flex justify-content- align-items-center">
												<button type="button" id="prarup_new_12_add_button" class="btn btn-info pull-right" onClick="prarup_new_12_add_rows()">Add</button>
											</div>
										<?php
									}
								
							?>
							
						</div>
						<?php } ?>
						<input type="hidden" name="prarup_new_12_id" id="prarup_new_12_id" value="<?php echo $_POST['prarup_new_12_id'];?>">
						<div id="prarup_new_12_insert"></div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<div class="form-group">
								<input type="hidden" id="editid" name="editid" value="<?php echo $_POST['editid']; ?>">
								<?php 
									if (isset($_GET['editid']) && $_GET['editid'] != "") {
										echo'<button type="submit" name="submit" class="btn btn-success">Update</button>
										<a href="prarup_new_12.php" class="btn btn-info">Back</a>';
									}else{
										echo '<button type="submit" name="submit" class="btn btn-success">Submit</button>';
										$_GET['editid']="";
									}	
								?>
								<!-- <button type="submit" name="submit" class="btn btn-success">Submit</button>
								<input type="hidden" id="id" name="id" value="1"> -->
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</form>


	
		<div class="row">
            <div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title text-center">प्रखण्ड पर उपलब्ध कन्टीजेन्सी का विवरण</h4></br>
					</div>
					<div class="card-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Sno</th>
									<th>Division</th>
									<th>Entry Date</th>
									<th>Delete</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$sql = 'SELECT sno,division_id, entry_date, created_by  FROM `invoice_prarup_new_12` where invoice_prarup_new_12.created_by = "'.$_SESSION['username'].'"';
							$result_prarup_new_12 = execute_query($sql);
							$i=1;
							while($row_prarup_new_12 = mysqli_fetch_assoc($result_prarup_new_12)){
								$sql = 'select * from uprnss_division where s_no="'.$row_prarup_new_12['division_id'].'"';
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
								<td>'.$i++.'</td>
								<td>'.$row_div['division_name'].'</td>
								<td>'.$row_prarup_new_12['entry_date'].'</td>
								<td><a href="'.$_SERVER["PHP_SELF"].'?delid='.$row_prarup_new_12['sno'].'" onClick="return confirm(\'Are you sure ?\');"  ><i class="far fa-trash-alt"></i></a></td>
								';
								?>
								<td><a href="<?php echo $_SERVER["PHP_SELF"]; ?>?editid=<?php echo $row_prarup_new_12['sno']; ?>"  onClick="return confirm(\'Are you sure ?\');"><i class="far fa-edit"></i></a></td></tr>
								<?php

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