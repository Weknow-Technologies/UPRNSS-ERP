<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
print_r($_POST);
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
                        <h4 class="card-title text-center">मुख्याल द्वारा निर्माण कार्य हेतु प्रेषित धनराशि का विवरण </h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<table class="table">
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Entry Date</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$i=1;
						if($_SESSION['usersno']==1){
						    $sql = 'select * from invoice_format_3';
    						$result = execute_query($sql);
    						while($row = mysqli_fetch_assoc($result)){
    						    $sql = 'select * from users where sno="'.$row['user_id'].'"';
    						    $unit_name = mysqli_fetch_assoc(execute_query($sql));
    							
    							echo '<tr>
    							<td>'.$i++.'</td>
    							<td>'.$unit_name['user_name'].'</td>
    							<td>'.$row['entry_date'].'</td>
    							<td><a href="view_format_3.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td></tr>';
    						}
						}
						else{
    						$sql = 'select * from invoice_format_3 WHERE user_id = "'.$_SESSION['usersno'].'"';
    						$result = execute_query($sql);
    						while($row = mysqli_fetch_assoc($result)){
    							echo '<tr>
    							<td>'.$i++.'</td>
    							<td>'.$row['entry_date'].'</td>
    							<td><a href="view_format_3.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td></tr>';
    						}
						}
						?>
						</tbody>
						</table>
					</div>
                </div>
            </div>
        </div>
    </form>
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