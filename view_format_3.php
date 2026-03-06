<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	$sql = 'select invoice_format_3.sno as sno, user_name, entry_date from invoice_format_3 left join users on users.sno = user_id where invoice_format_3.sno='.$_GET['id'];
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">मुख्याल द्वारा निर्माण कार्य हेतु प्रेषित धनराशि का विवरण </h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<div class="row">
							<div class="col-md-5">
                                <div class="form-group">
                                    <label >प्रखण्ड का नाम</label><br>
                                    <input type="text" name="unit_name" id="unit_name" class="form-control" placeholder=" " value="<?php echo $invoice['user_name']; ?>" readonly tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
						</div>
						<?php
						$sql = 'select * from transaction_format_3 where invoice_id="'.$invoice['sno'].'"';
						$result_trans = execute_query($sql);
						$i=1;
						while($row_trans = mysqli_fetch_assoc($result_trans)){
						?>
						<div class="row border" id="add_rows_length">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >माह</label><br>
                                    <input type="text" name="month" id="month" class="form-control" placeholder="" readonly value="<?php echo $row_trans['month']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >योजना का नाम </label><br>
                                    <input type="text" name="project_name" id="project_name" class="form-control" placeholder="" readonly value="<?php echo $row_trans['project_name']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>बैंक में क्रेडिट होने का दिनांक</label><br>
									<input type="text" class="form-control" readonly value="<?php echo $row_trans['credit_date']; ?>">   	
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >प्रेषित धनराशि</label><br>
                                    <input type="text" name="amount" id="amount" class="form-control" placeholder="" readonly value="<?php echo $row_trans['amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>बैंक द्वारा अग्रिम सेंटेज कटौती </label><br>
                                    <input type="text" name="advance_centage_deduction" id="advance_centage_deduction" class="form-control" placeholder="" readonly value="<?php echo $row_trans['advance_centage_deduction']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>मुख्यालय आयकर द्वारा कटौती </label><br>
                                    <input type="text" name="incometax_dedcution" id="incometax_dedcution" class="form-control" placeholder="" readonly value="<?php echo $row_trans['incometax_dedcution']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>मुख्यालय द्वारा अन्य कटौती </label><br>
                                    <input type="text" name="other_dedcution" id="other_dedcution" class="form-control" placeholder="" readonly value="<?php echo $row_trans['other_dedcution']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>कुल कटौती </label><br>
                                    <input type="text" name="total_dedcution" id="total_dedcution" class="form-control" placeholder="" readonly value="<?php echo $row_trans['total_dedcution']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                        </div>
						<?php } ?>
					</div>
                </div>
            </div>
        </div>
		
		
<?php
page_footer_start();
page_footer_end();
?>