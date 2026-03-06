<?php
include("scripts/settings.php");
page_header_start();
page_header_end(); 
$msg='';
$tab=1;

if(isset($_GET['id'])){
	$sql = 'select invoice_format_1.sno as sno, user_name, entry_date from invoice_format_1 left join users on users.sno = user_id where invoice_format_1.sno='.$_GET['id'];
	$invoice = mysqli_fetch_assoc(execute_query($sql));
	//echo mysqli_error($db).'>>'.$sql;
}

?>
		<div class="row m-4">
            <div class="col-md-8">
                <div class="card border-primary">
                    <div class="card-header">
                        <h4 class="card-title text-center">Trading Sheet </h4></br>
						</br><h5>User ID: <span class="text-primary"><?php echo $_SESSION['username'].'</span> | Unit Name: <span class="text-primary">'.$_SESSION['unit_name'].'</span>'; ?></h5></br>
                    </div>
						<?php echo $msg; ?>
                    <div class="card-body ">
					
						<?php
						$i=1;
						$sql = 'select master_trading_child.sno as sno, group_id, group_name, child_name from master_trading_child left join master_trading_group on master_trading_group.sno = group_id order by group_name, child_name';
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
							$sql = 'select * from transaction_trading_account where invoice_id="'.$invoice['sno'].'" and child_id="'.$row['sno'].'"';
							$result_trans = execute_query($sql);
							$i=1;
							$row_trans = mysqli_fetch_assoc($result_trans);
							
							echo '<div class="row">
							<div class="col-md-4">'.chr($a++).'.&nbsp;&nbsp;&nbsp;'.$row['child_name'].'</div>
							<div class="col-md-3"><input type="text" class="form-control" readonly value="'.$row_trans['child_value'].'" placeholder="'.$row['child_name'].' Amount in Rs." name="child_'.$row['sno'].'" id="child_'.$row['sno'].'"></div>
							</div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
          
		  
		  
<?php
page_footer_start();
page_footer_end();
?>