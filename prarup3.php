<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['unit_name'])){
	
	$sql = 'insert into invoice_format_3 (user_id, entry_date, creation_time) values ("'.$_SESSION['usersno'].'", "'.date("Y-m-d").'", "'.date("Y-m-d H:i:s").'")';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['id']; $i++){
			$sql= ' insert into transaction_format_3 (invoice_id, month, project_name, credit_date, amount, advance_centage_deduction, incometax_dedcution, other_dedcution, total_dedcution) values ("'.$inv_id.'", "' .$_POST['month_'.$i].'", "'.$_POST['project_name_'.$i].'", "'.$_POST['credit_date_'.$i].'", "'.$_POST['amount_'.$i].'", "'.$_POST['advance_centage_deduction_'.$i].'", "'.$_POST['incometax_dedcution_'.$i].'", "'.$_POST['other_dedcution_'.$i].'","'.$_POST['total_dedcution_'.$i].'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';
				
				$_POST['unit_name_1'] = '';
				$_POST['month_1'] = '';
				$_POST['project_name_1'] = '';
				$_POST['credit_date_1'] = '';
				$_POST['amount_1'] = '';
				$_POST['advance_centage_deduction_1'] = '';
				$_POST['incometax_dedcution_1'] = '';
				$_POST['other_dedcution_1'] = '';
				$_POST['total_dedcution_1'] = '';
			}
		}
	}
}
else{
	
$_POST['unit_name_1'] = '';
$_POST['month_1'] = '';
$_POST['project_name_1'] = '';
$_POST['credit_date_1'] = '';
$_POST['amount_1'] = '';
$_POST['advance_centage_deduction_1'] = '';
$_POST['incometax_dedcution_1'] = '';
$_POST['other_dedcution_1'] = '';
$_POST['total_dedcution_1'] = '';


}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">मुख्याल द्वारा निर्माण कार्य हेतु प्रेषित धनराशि का विवरण </h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<div class="row">
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >प्रखण्ड का नाम</label><br>
                                    <input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
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
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >योजना का नाम </label><br>
                                    <input type="text" name="project_name_1" id="project_name_1" class="form-control" placeholder=" " value="<?php echo $_POST['project_name_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>बैंक में क्रेडिट होने का दिनांक</label><br>
									<script type="text/javascript" language="javascript" >
											document.writeln(DateInput('credit_date_1', 'date_from', true, 'YYYY-MM-DD', '<?php echo date("Y-m-d"); ?>', <?php echo $tab++; ?>));
									</script>	
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >प्रेषित धनराशि</label><br>
                                    <input type="text" name="amount_1" id="amount_1" class="form-control" placeholder=" " value="<?php echo $_POST['amount_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>बैंक द्वारा अग्रिम सेंटेज कटौती </label><br>
                                    <input type="text" name="advance_centage_deduction_1" id="advance_centage_deduction_1" class="form-control" placeholder=" " value="<?php echo $_POST['advance_centage_deduction_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>मुख्यालय आयकर द्वारा कटौती </label><br>
                                    <input type="text" name="incometax_dedcution_1" id="incometax_dedcution_1" class="form-control" placeholder=" " value="<?php echo $_POST['incometax_dedcution_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>मुख्यालय द्वारा अन्य कटौती </label><br>
                                    <input type="text" name="other_dedcution_1" id="other_dedcution_1" class="form-control" placeholder="" value="<?php echo $_POST['other_dedcution_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>कुल कटौती </label><br>
                                    <input type="text" name="total_dedcution_1" id="total_dedcution_1" class="form-control" placeholder=" " value="<?php echo $_POST['total_dedcution_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button>
							</div>
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
				var date_1 = DateInput('credit_date_'+i, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();	
				
				var txt = '<div class="row border" id="add_rows_length"><div class="col-md-4"> <div class="form-group"> <label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control" placeholder="" value="" tabindex=""><option value="Select">Select--	</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div> </div><div class="col-md-4"><div class="form-group"><label >योजना का नाम </label><br><input type="text" name="project_name_'+id+'" id="project_name_'+id+'" class="form-control" placeholder="" value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>बैंक में क्रेडिट होने का दिनांक</label>'+date_1+'</div></div><div class="col-md-4"><div class="form-group"><label >प्रेषित धनराशि</label><br><input type="text" name="amount_'+id+'" id="amount_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>बैंक द्वारा अग्रिम सेंटेज कटौती </label><br><input type="text" name="advance_centage_deduction_'+id+'" id="advance_centage_deduction_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>मुख्यालय आयकर द्वारा कटौती </label><br><input type="text" name="incometax_dedcution_'+id+'" id="incometax_dedcution_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>मुख्यालय द्वारा अन्य कटौती </label><br><input type="text" name="other_dedcution_'+id+'" id="other_dedcution_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-4"><div class="form-group"><label>कुल कटौती </label><br><input type="text" name="total_dedcution_'+id+'" id="total_dedcution_'+id+'" class="form-control" placeholder=" " value="" tabindex=""></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" class="btn btn-info pull-right" onClick="add_rows()">Add</button><input type="hidden" name="add_rows_id" id="add_rows_id" value="1"></div></div>';
				$("#test").append(txt);
				$("#id").val(id);
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