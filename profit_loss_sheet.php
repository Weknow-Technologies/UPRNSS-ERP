<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;

page_header_start();
page_header_end();
page_sidebar();

if(isset($_POST['submit'])){
	
	$sql = 'insert into invoice_profit_loss (entry_date, user_id) values ("'.date("Y-m-d H:i:s").'", "'.$_SESSION['usersno'].'")';
	execute_query($sql);
	$invoice_id = mysqli_insert_id($db);
	
	$sql = 'select master_profit_loss_child.sno as sno, group_id, group_name, child_name from master_profit_loss_child left join master_profit_loss_group on master_profit_loss_group.sno = group_id order by group_name, child_name';
	$result = execute_query($sql);
	while($row = mysqli_fetch_assoc($result)){
		$sql = 'insert into transaction_profit_loss (invoice_id, entry_date, user_id, child_id, child_value) values("'.$invoice_id.'", "'.date("Y-m-d").'", "'.$_SESSION['usersno'].'", "'.$row['sno'].'", "'.$_POST['child_'.$row['sno']].'")';
	
		execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="text text-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
	}
	if($msg==''){
		$msg .= '<p class="text text-success">Data Saved</p>';
	}
}
else{
		
	$_POST['child_name'] = '';
	
	


}

?>


   <form id="sale_form" name="sale_form" class="" autocomplete="off" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="">

	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body">
						<?php
						$i=1;
						$sql = 'select master_profit_loss_child.sno as sno, group_id, group_name, child_name from master_profit_loss_child left join master_profit_loss_group on master_profit_loss_group .sno = group_id order by group_name, child_name';
						$result = execute_query($sql);
						$group_name = '';
						$a=65;
						while($row = mysqli_fetch_assoc($result)){
							if($group_name!=$row['group_id']){
								echo '
								<hr/>
								<h4>'.$i.'.'.$row['group_name'].'</h4>';
								$group_name = $row['group_id'];
								$i++;
								$a=65;
							}
							echo '<div class="row">
							<div class="col-md-4">'.chr($a++).'.&nbsp;&nbsp;&nbsp;'.$row['child_name'].'</div>
							<div class="col-md-4"><input type="text" class="form-control" placeholder="'.$row['child_name'].' Amount in Rs." name="child_'.$row['sno'].'" id="child_'.$row['sno'].'"></div>
							</div>';
						}
						?>
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