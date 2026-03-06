<?php
include("scripts/settings.php");
 
$msg='';
$tab=1;
print_r($_POST);
page_header_start();
page_header_end();
page_sidebar();



?>
	
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center"></h4></br>
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
						
						$sql = 'select * from invoice_profit_loss WHERE user_id = "'.$_SESSION['usersno'].'"';
						//echo $sql;
						$result = execute_query($sql);
						while($row = mysqli_fetch_assoc($result)){
							echo '<tr>
							<td>'.$i++.'</td>
							<td>'.$row['entry_date'].'</td>
							<td><a href="view_format_profit_loss_sheet.php?id='.$row['sno'].'" target="_blank"><i class="fa fa-eye"></i></a></td></tr>';
						}
						?>
						</tbody>
						</table>
					</div>
                </div>
            </div>
        </div>
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