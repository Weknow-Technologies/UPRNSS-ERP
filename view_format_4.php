<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	$sql = 'select invoice_format_4.sno as sno, user_name, entry_date from invoice_format_4 left join users on users.sno = user_id where invoice_format_4.sno='.$_GET['id'];
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">अन्य मदों में मुख्यालय द्वारा प्रेषित धनराशि का विवरण </h4></br>
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
						$sql = 'select * from transaction_format_4 where invoice_id="'.$invoice['sno'].'"';
						$result_trans = execute_query($sql);
						$i=1;
						while($row_trans = mysqli_fetch_assoc($result_trans)){
						?>
						<div class="row border" id="add_rows_length">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >माह</label><br>
                                    <input type="text" name="month_1" id="month_1" class="form-control" placeholder="" readonly value="<?php echo $row_trans['month']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label >मद </label><br>
									<input type="text" class="form-control" readonly value="<?php echo $row_trans['head_type']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label >प्रेषित धनराशि</label><br>
                                    <input type="text" name="amount_1" id="amount_1" class="form-control" placeholder="" readonly value="<?php echo $row_trans['amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>अन्य विवरण </label><br>
                                    <input type="text" name="other_1" id="other_1" class="form-control" placeholder="" readonly value="<?php echo $row_trans['other']; ?>" tabindex="<?php echo $tab++; ?>">
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