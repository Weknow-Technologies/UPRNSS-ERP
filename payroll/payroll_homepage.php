<?php	 
$msg='';
$tab=1;
include("scripts/settings.php");
page_header_start('Login');
page_header_end();
navigation($_SERVER['PHP_SELF']);

?>
<?php
if(isset($_POST['submit'])){
	
	$sql='UPDATE `salary_structure` SET head_value="0" WHERE head_id="20"';
	execute_query($sql);
		if(mysqli_error($db)){ 
			$msg .= '<p class="alert alert-danger">Error # 1 : '.mysqli_error($db).'>> '.$sql.'</p>';
		}
		if($msg==''){
			$msg .= '<script>alert("Cug Deduction is zero");</script>';

		
		}
	
}

?>
<style type="text/css">
#div1{
	height:80px;
	width:140px;
	color:black;
	border:1px solid black;
	margin:50px;
	background-color: #efe1da;
}
#innertext{
	margin-top:18px;
	text-align: center;
	font-size: 20px;
}
.main_container {
	width: 100vw;
	height: 80vh;
	background-color: whitesmoke;
	display: flex;
	align-items: flex-end;  
	justify-content: flex-start; 
	padding: 20px;
}

.action-container {
	display: flex;
	align-items: center;
	gap: 15px; 
}
.form-check-input {
	transform: scale(1.5); 
	width: 17px;
	height: 17px;
	cursor: pointer; 
}
.form-check-label {
	margin-left: 5px;
	font-size: 1.5rem; 
}

</style>
<div class="container">
	<div class="row">
		<div class="col-sm-2"></div>
		<!--<div class="col-sm-8" style="border:1px solid">
			<?php
				$sql = 'select * from navigation where  parent=1';
				$result = execute_query($sql);
				while($row = mysqli_fetch_array($result)){
				$caret = '';
				$drop_link = '';
				echo '<a href="'.$row['hyper_link'].'"><div id="div1" class="col-sm-3 col-md-4">
						<div id="innertext">'.$row['link_description'].'</div>
						</div></a>';
						
					}
			?>
		</div>-->
	</div>
</div>
<div class="main_container">
	<?php    
	if ($_SESSION['usersno']<="29"){
	?>
		<div class="action-container">
		<?php echo $msg;?>
		
			<!-- Checkbox -->
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="enableButton">
				<label class="form-check-label" for="enableButton">
					Cug Deduction zero
				</label>
			</div>
			<form name="test" action="" method="POST" enctype="multipart/form-data">
				<input type="submit" name="submit" value="Submit" id="submitButton" class="btn btn-primary" onclick="return confirm('Are you sure you want to submit?');" disabled>
			</form>
			<!-- Button -->
		</div>
	<?php    
	}
	
	?>
	
</div>

<!-- JavaScript to Enable/Disable Button -->
<script>
    document.getElementById("enableButton").addEventListener("change", function() {
        document.getElementById("submitButton").disabled = !this.checked;
    });
</script>


<?php
page_footer();
?>