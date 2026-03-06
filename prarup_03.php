<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['unit_name'])){
	
	$sql = 'insert into invoice_format_1 (user_id, entry_date, creation_time) values ("'.$_SESSION['usersno'].'", "'.date("Y-m-d").'", "'.date("Y-m-d H:i:s").'")';
	execute_query($sql);
	if(mysqli_error($db)){ 
		$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
	}
	else{
		$msg .= '<p class="text text-success">Data Saved</p>';
		$inv_id = mysqli_insert_id($db);
		
		for($i=1; $i<=$_POST['id']; $i++){
			$sql= ' insert into transaction_format_1 (invoice_id, month, head_type, amount, utr_no, transaction_date, other) values ("'.$inv_id.'", "' .$_POST['month_'.$i].'", "'.$_POST['head_type_'.$i].'", "'.$_POST['amount_'.$i].'", "'.$_POST['utr_no_'.$i].'", "'.$_POST['transaction_date_'.$i].'", "'.$_POST['other_'.$i].'")';
			execute_query($sql);
			if(mysqli_error($db)){ 
				$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
			}
			else{
				$msg .= '<p class="text text-success">Data Saved</p>';
				$_POST['unit_name_1'] = '';
				$_POST['month_1'] = '';
				$_POST['head_type_1'] = '';
				$_POST['amount_1'] = '';
				$_POST['utr_no_1'] = '';
				$_POST['transaction_date_1'] = '';
				$_POST['other_1'] = '';
			}
		}
	}
}
else{
		
	$_POST['unit_name_1'] = '';
	$_POST['month_1'] = '';
	$_POST['head_type_1'] = '';
	$_POST['amount_1'] = '';
	$_POST['utr_no_1'] = '';
	$_POST['transaction_date_1'] = '';
	$_POST['other_1'] = '';


}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">निर्माण कार्य हेतु मुख्यालय से प्राप्त धनराशि का विवरण वर्ष 2023-24</h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<div class="row">
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >प्रखण्ड का नाम</label>
                                    <input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $_SESSION['unit_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
						</div>
						<div class="p-3">
						<div class="row border" id="add_rows_length">
                            <div class="col-md-3 pt-2 ">
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
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >योजना का नाम</label><br>
                                    <select name="head_type_1" id="head_type_1" class="form-control"  value="<?php echo $_POST['head_type_1']; ?>" tabindex="<?php echo $tab++; ?>">
										<option value="Select">Select--	</option>
										<option value="सेंटेज">सेंटेज</option>
									</select>
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >योजना की लागत</label><br>
                                    <select name="head_type_1" id="head_type_1" class="form-control"  value="<?php echo $_POST['head_type_1']; ?>" tabindex="<?php echo $tab++; ?>">
										<option value="Select">Select--	</option>
										<option value="सेंटेज">सेंटेज</option>
									</select>
                                </div>
                            </div>
                            <div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >बैंक में क्रेडिट होने का दिनांक</label><br>
                                    <input type="text" name="amount_1" id="amount_1" class="form-control" placeholder="" value="<?php echo $_POST['amount_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label>यू०टी०आर० नम्बर</label><br>
                                    <input type="text" name="utr_no_1" id="utr_no_1" class="form-control" placeholder="" value="<?php echo $_POST['utr_no_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >प्राप्त धनराशि</label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >मुख्यालय द्वारा अग्रिम सेंटेज कटौती </label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >मुख्यालय द्वारा विदहेल्ड आयकर</label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >जी०एस०टी० टी०डी०एस० </label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >लेबर सेस</label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >कुल धनराशि (7+8+9+10+11)</label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
                                <div class="form-group">
                                    <label >अवशेष धनराशि (4-12)</label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3 pt-2">
								<div class="form-group">
									<label >परियोजना की स्थिटि</label><br>
										<input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value="<?php echo $_POST['other_1']; ?>" tabindex="<?php echo $tab++; ?>"> 
								</div>
							</div>
							
							<div class="col-md-1 d-flex justify-content- align-items-center">
								<button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button>
							</div>
                        </div>
						<div id="test"></div>
						<div class="row">
							<div class="col-md-3 pt-2">
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
				
				var date_1 = DateInput('transaction_date_'+i, 'date_from', true, 'YYYY-MM-DD', '2022-12-01', 1);
				$("#add_button").remove();
				
				var txt = '<br><div class="p-1"><div class="row border" id="add_rows_length"><div class="col-md-3 pt-2"><div class="form-group"><label >माह</label><select name="month_'+id+'" id="month_'+id+'" class="form-control"  value=""><option value="">-------	</option><option value="January" >January</option><option value="February" >February</option><option value="March" >March</option><option value="April" >April</option><option value="May" >May</option><option value="June" >June</option><option value="July" >July</option><option value="August" >August</option><option value="September" >September</option><option value="October" >October</option><option value="November" >November</option><option value="December" >December</option></select></div></div><div class="col-md-3 pt-2"> <div class="form-group"><label >योजना का नाम</label><select name="head_type_'+id+'" id="head_type_'+id+'" class="form-control"  value="" tabindex="">	<option value="Select">Select--	</option><option value="सेंटेज">सेंटेज</option></select> </div></div><div class="col-md-3 pt-2"> <div class="form-group"><label >योजना की लागत</label><select name="head_type_'+id+'" id="head_type_'+id+'" class="form-control"  value="" tabindex="">	<option value="Select">Select--	</option><option value="सेंटेज">सेंटेज</option></select> </div> </div> <div class="col-md-3 pt-2"><div class="form-group"><label >बैंक में क्रेडिट होने का दिनांक</label><br><input type="text" name="amount_1" id="amount_1" class="form-control" placeholder="" value=""> </div></div><div class="col-md-3 pt-2"><div class="form-group"><label>पयू०टी०आर० नम्बर</label><br><input type="text" name="utr_no_1" id="utr_no_1" class="form-control" placeholder="" value=" "> </div> </div><div class="col-md-3 pt-2"> <div class="form-group"> <label >प्राप्त धनराशि</label><br> <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""></div> </div> <div class="col-md-3 pt-2"> <div class="form-group"> <label >मुख्यालय द्वारा अग्रिम सेंटेज कटौती</label><br> <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""> </div> </div><div class="col-md-3 pt-2"> <div class="form-group"> <label >मुख्यालय द्वारा विदहेल्ड आयकर </label><br> <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""></div> </div><div class="col-md-3 pt-2"> <div class="form-group"> <label >जी०एस०टी० टी०डी०एस० </label><br> <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""> </div></div><div class="col-md-3 pt-2">  <div class="form-group"><label >लेबर सेस</label><br> <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""></div></div><div class="col-md-3 pt-2"><div class="form-group"> <label >कुल धनराशि (7+8+9+10+11)</label><br><input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""></div> </div><div class="col-md-3 pt-2"> <div class="form-group"> <label >अवशेष धनराशि (4-12) </label><br><input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""></div> </div><div class="col-md-3 pt-2"><div class="form-group"><label >परियोजना की स्थिटि</label><br><input type="text" name="other_1" id="other_1" class="form-control" placeholder="" value=""> </div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button type="button" id="add_button" class="btn btn-info pull-right" onClick="add_rows()">Add</button></div> </div><div id="test"></div><div class="row"><div class="col-md-3 pt-2"><div class="form-group"><input type="hidden" id="id" name="id" value="1"></div></div></div></div>';
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