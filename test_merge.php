<?php
include("scripts/settings.php");

if(!isset($_GET['id'])){
    die("Select ID");
}

if($_GET['id']=='mnp'){
    merge_new_project();
}


function merge_new_project(){
	global $db;
    if(isset($_POST['merge_id_from'])){
        $sql = 'select * from invoice_new_project where sno="'.$_POST['merge_id_from'].'"';
        $data_to_replace = mysqli_fetch_assoc(mysqli_query($db, $sql));
        
        $sql = 'select * from invoice_new_project where sno="'.$_POST['merge_id_to'].'"';
        $new_data = mysqli_fetch_assoc(mysqli_query($db, $sql));
		
		$sql = 'update transaction_new_project set invoice_id="'.$new_data['sno'].'" where invoice_id="'.$data_to_replace['sno'].'"';
		mysqli_query($db, $sql);
		
		$sql = 'update uprnss_project_temp set new_project_id="'.$new_data['sno'].'" where new_project_id="'.$data_to_replace['sno'].'"';
		mysqli_query($db, $sql);
		
		$sql = 'update invoice_new_project set status="5" where sno="'.$data_to_replace['sno'].'"';
		mysqli_query($db, $sql);
		
		echo 'Done';
    }
?>
<form action="test_merge.php?id=mnp" method="POST" enctype="multipart/form-data">
    Merge ID From : <input type="text" name="merge_id_from"><br/>
    Merge ID To : <input type="text" name="merge_id_to"><br/>
    <input type="submit">
</form>

<?php
}

?>