<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar(); 


if(isset($_POST['unit_name'])){
	
	$sql = 'insert into invoice_format_5 ( user_id,`department_id`, `district_id`, `project_name`, entry_date, creation_time) values ("'.$_SESSION['usersno'].'","'.$_POST['department'].'", "'.$_POST['district'].'", "'.$_POST['project_name'].'", "'.date("Y-m-d").'", "'.date("Y-m-d H:i:s").'")';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['id']; $i++){
			$sql= ' insert into transaction_format_5 (invoice_id, month, project_name, primary_residue, amount_deducted_on_account_of_centage, total, adjusted_amount_of_advance_centage, balance_amount_of_advance_centage) values ("'.$inv_id.'", "' .$_POST['month_'.$i].'", "'.$_POST['project_name_'.$i].'", "'.$_POST['primary_residue_'.$i].'", "'.$_POST['amount_deducted_on_account_of_centage_'.$i].'", "'.$_POST['total_'.$i].'", "'.$_POST['adjusted_amount_of_advance_centage_'.$i].'", "'.$_POST['balance_amount_of_advance_centage_'.$i].'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';

				$_POST['unit_name'] = '';
				$_POST['month_1'] = '';
				$_POST['project_name_1'] = '';
				$_POST['primary_residue_1'] = '';
				$_POST['amount_deducted_on_account_of_centage_1'] = '';
				$_POST['total_1'] = '';
				$_POST['adjusted_amount_of_advance_centage_1'] = '';
				$_POST['balance_amount_of_advance_centage_1'] = '';


			}
		}
	}
}
else{
	
$_POST['unit_name'] = '';
$_POST['month_1'] = '';
$_POST['project_name_1'] = '';
$_POST['primary_residue_1'] = '';
$_POST['amount_deducted_on_account_of_centage_1'] = '';
$_POST['total_1'] = '';
$_POST['adjusted_amount_of_advance_centage_1'] = '';
$_POST['balance_amount_of_advance_centage_1'] = '';



}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">अग्रिम सेंटेज का विवरण </h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<div class="row">
							<!--- <div class="col-md-3">
                                <div class="form-group">
                                    <label >प्रखण्ड का नाम</label><br>
                                    <input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div> ---->
						
							<input type="hidden" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
							<div class="col-md-3 ">
								<div class="form-group">
									<label >विभाग</label><br>
									<select class="form-control" name="department" id="department" tabindex="<?php echo $tab++; ?>" onChange="fill_district(this.value)">
										<option value="">--- Select ---</option>
										<?php
										$query = "select * from uprnss_department_name";
										$query .= ' where sno in (SELECT department_id FROM `uprnss_project_temp` where division_id in ('.implode(",", $_SESSION['divisions']).') group by department_id)';
										//echo $query;
										$run = mysqli_query($db,$query);
										while($data = mysqli_fetch_array($run)){
											echo '<option value="'.$data['sno'].'" ';
											if(isset($_POST['department'])){
												if($_POST['department']==$data['sno']){
													echo ' selected="Selected"';
												}
											}
											echo '>'.trim($data['department_name_hindi']).'</option>';
										}
										?>
									</select>
								</div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >आच्छादित जनपद</label>
                                    <select class="form-control" name="district" id="district" tabindex="<?php echo $tab++; ?>" onChange="fill_project(this.value)">
									</select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label>परियोजना का नाम</label><br>
                                    <select class="form-control" name="project_name" id="project_name" tabindex="<?php echo $tab++; ?>">
									</select>
                                </div>
                            </div>
						</div>
						<div class="row border" id="add_rows_length">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >माह</label>
									<select name="month_1" id="month_1" class="form-control"  value="<?php echo $_POST['month_1']; ?>" tabindex="<?php echo $tab++; ?>">
										<option value="Select">Select--	</option>
										<option value="January" >January</option>
										<option value="February" >February</option>
										<option value="March" >March</option>
										<option value="April" >April</option>
										<option value="May" >May</option>
										<option value="June" >June</option>
										<option value="July" >July</option>
										<option value="August" >August</option>
										<option value="September" >September</option>
										<option value="October" >October</option>
										<option value="November" >November</option>
										<option value="December" >December</option>
									</select>
                                </div>
                            </div>
							<!--- <div class="col-md-4">
                                <div class="form-group">
                                    <label >योजना का नाम </label><br>
                                    <input type="text" name="project_name_1" id="project_name_1" class="form-control" placeholder=" " value="<?php echo $_POST['project_name_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div> -->
							<div class="col-md-4">
                                <div class="form-group">
                                	<label>प्रारम्भिक अवशेष (01.04.22)</label><br>
                                 	<input type="text" name="primary_residue_1" id="primary_residue_1" class="form-control" placeholder=" " value="<?php echo $_POST['primary_residue_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >वर्ष मे मुख्यलय द्वारा अग्रिम सेंटेज के मद में कटौती की धनराशि </label><br>
                                    <input type="text" name="amount_deducted_on_account_of_centage_1" id="amount_deducted_on_account_of_centage_1" class="form-control" placeholder=" " value="<?php echo $_POST['amount_deducted_on_account_of_centage_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>योग (प्रारम्भिक अवशेष +कटौती की धनराशि  )  </label><br>
                                    <input type="text" name="total_1" id="total_1" class="form-control" placeholder=" " value="<?php echo $_POST['total_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>अग्रिम सेंटेज की समायोजित धनराशि </label><br>
                                    <input type="text" name="adjusted_amount_of_advance_centage_1" id="adjusted_amount_of_advance_centage_1" class="form-control" placeholder=" " value="<?php echo $_POST['adjusted_amount_of_advance_centage_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>अग्रिम सेंटेज की अवशेष धनराशि </label>
                                    <input type="text" name="balance_amount_of_advance_centage_1" id="balance_amount_of_advance_centage_1" class="form-control" placeholder=" " value="<?php echo $_POST['balance_amount_of_advance_centage_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
						<!---<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button>
							</div>-->
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-success">Submit</button>
									<input type="hidden" id="id" name="id" value="1">
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </form>
	
	
	
	
	<script>			
			function add_rows(){
				var id = parseFloat($("#id").val());
				if(!id){
					id=0;
				}
				for(var i=1; i<=id; i++){
					if($("#month_"+i).val()=='' || $("#amount_"+i).val()==''){
						alert("पंक्ति संख्या "+i+" खाली है");
						$("#month_"+i).focus();
						return;
					}
				}
				id = id+1;
				
				$("#add_button").remove();
				
				var txt = '<div class="row border" id="add_rows_length"><div class="col-md-4"> <div class="form-group"> <label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control" placeholder="" value="" tabindex=""><option value="Select">Select--	</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div> </div><div class="col-md-4"><div class="form-group"><label>योजना का नाम </label><br><input type="text" name="project_name_'+id+'" id="project_name_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>प्रारम्भिक अवशेष (01.04.22)</label><br><input type="text" name="primary_residue_'+id+'" id="primary_residue_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label >वर्ष मे मुख्यलय द्वारा अग्रिम सेंटेज के मद में कटौती की धनराशि </label><br><input type="text" name="amount_deducted_on_account_of_centage_'+id+'" id="amount_deducted_on_account_of_centage_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>योग (प्रारम्भिक अवशेष +कटौती की धनराशि  )  </label><br><input type="text" name="total_'+id+'" id="total_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>अग्रिम सेंटेज की समायोजित धनराशि </label><br><input type="text" name="adjusted_amount_of_advance_centage_'+id+'" id="adjusted_amount_of_advance_centage_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>अग्रिम सेंटेज की अवशेष धनराशि </label><input type="text" name="balance_amount_of_advance_centage_'+id+'" id="balance_amount_of_advance_centage_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div></div>';
				$("#test").append(txt);
				$("#id").val(id);
			}	
			
			
		var actionUrl = 'scripts/ajax.php';

		function fill_district(val){
			var data = {"term":"b", "id":"dist", "val":val};
			$.ajax({
				type: "POST",
				url: actionUrl,
				data: data, // serializes the form's elements.
				success: function(data){
					var txt = '<option value="">--Select--</option>';
					data = JSON.parse(data);
					$.each(data, function(key, value){
						txt += '<option value="'+value.id+'">'+value.district_name+'</option>';
						
					});
					$("#district").html(txt);
				}
			});
		}
			
		function fill_project(val){
			var data = {"term":"b", "id":"proj", "val":val, "dept":$("#department").val()};
			$.ajax({
				type: "POST",
				url: actionUrl,
				data: data, // serializes the form's elements.
				success: function(data){
					var txt = '<option value="">--Select--</option>';
					data = JSON.parse(data);
					$.each(data, function(key, value){
						txt += '<option value="'+value.id+'">'+value.project_name_hindi+'</option>';
						
					});
					$("#project_name").html(txt);
				}
			});
		}
	
	</script>
	
	
	
	
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