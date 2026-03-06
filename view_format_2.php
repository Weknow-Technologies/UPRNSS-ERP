<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	$sql = 'select invoice_format_2.sno as sno, user_name, entry_date from invoice_format_2 left join users on users.sno = user_id where invoice_format_2.sno='.$_GET['id'];
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">मुख्यालय को जी0एस0टी0 एवं जी0एस0टी0 टी0डी0एस0 के मद में प्रेषित धनराशि का विवरण</h4></br>
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
						$sql = 'select * from transaction_format_2 where invoice_id="'.$invoice['sno'].'"';
						$result_trans = execute_query($sql);
						$i=1;
						while($row_trans = mysqli_fetch_assoc($result_trans)){
						?>
						<div class="row border" id="add_rows_length">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >माह</label><br>
                                    <input type="text" readonly name="month" id="month" class="form-control" placeholder=" " value="<?php echo $row_trans['month']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >मद </label><br>
									<input type="text" class="form-control" readonly value="<?php echo $row_trans['head_type']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >प्रेषित धनराशि</label><br>
                                    <input type="text" readonly name="amount" id="amount" class="form-control" placeholder=" " value="<?php echo $row_trans['amount']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label>यू0 टी0आर0 नंबर </label><br>
                                    <input type="text" readonly name="utr_no" id="utr_no" class="form-control" placeholder=" " value="<?php echo $row_trans['utr_no']; ?>" tabindex="<?php echo $tab++; ?>">
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label >दिनांक</label><br>
									<input type="text" class="form-control" readonly value="<?php echo $row_trans['transaction_date']; ?>">   
								</div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label >अन्य विवरण </label><br>
                                    <input type="text" readonly name="other" id="other" class="form-control" placeholder=" " value="<?php echo $row_trans['other']; ?>" tabindex="<?php echo $tab++; ?>">
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