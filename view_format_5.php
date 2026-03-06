<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	$sql = 'select invoice_format_5.sno as sno, user_name, entry_date from invoice_format_5 left join users on users.sno = user_id where invoice_format_5.sno='.$_GET['id'];
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">अग्रिम सेंटेज का विवरण </h4></br>
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
						$sql = 'select * from transaction_format_5 where invoice_id="'.$invoice['sno'].'"';
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
                                	<label>प्रारम्भिक अवशेष (01.04.22)</label><br>
                                 	<input type="text" name="primary_residue" id="primary_residue" class="form-control" placeholder="" readonly value="<?php echo $row_trans['primary_residue']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >वर्ष मे मुख्यलय द्वारा अग्रिम सेंटेज के मद में कटौती की धनराशि </label><br>
                                    <input type="text" name="amount_deducted_on_account_of_centage" id="amount_deducted_on_account_of_centage" class="form-control" placeholder="" readonly value="<?php echo $row_trans['amount_deducted_on_account_of_centage']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>योग (प्रारम्भिक अवशेष +कटौती की धनराशि  )  </label><br>
                                    <input type="text" name="total" id="total" class="form-control" placeholder="" readonly value="<?php echo $row_trans['total']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>अग्रिम सेंटेज की समायोजित धनराशि </label><br>
                                    <input type="text" name="adjusted_amount_of_advance_centage" id="adjusted_amount_of_advance_centage" class="form-control" placeholder="" readonly value="<?php echo $row_trans['adjusted_amount_of_advance_centage']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>अग्रिम सेंटेज की अवशेष धनराशि </label>
                                    <input type="text" name="balance_amount_of_advance_centage" id="balance_amount_of_advance_centage" class="form-control" placeholder="" readonly value="<?php echo $row_trans['balance_amount_of_advance_centage']; ?>" tabindex="<?php echo $tab++; ?>">
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