<?php	 
$msg='';
$tab=1;
include("scripts/settings.php");
page_header_start('Login');
page_header_end();
navigation($_SERVER['PHP_SELF']);
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
<?php
page_footer();
?>