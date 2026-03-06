<?php
include("scripts/settings.php");
$msg = '';
$tab = 1;
page_header_start();
page_sidebar();
?>
<style>
.image-container {
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.3s;
}
.image-container:hover {
    transform: rotateY(10deg) rotateX(10deg);
}
.image-thumbnail {
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 10px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.image-thumbnail:hover {
    transform: scale(1.1);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    transition: transform 0.3s ease;
}
.close {
    position: absolute;
    top: 10px;
    right: 20px;
    color: #fff;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
}
</style>
<link rel="stylesheet" href="css/impulseslider.css" type="text/css" media="screen" />

<?php
page_header_end();

$sql = 'SELECT * FROM transaction_project_file WHERE like_status=1';
$result = execute_query($sql);

echo '<div class="card" style="padding:20px;">
<h2 class="text-center bg-danger text-light">Featured Photos</h2>
<div class="mt-3" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px;">';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<div class="image-container" style="perspective: 1000px;">
            <div class="image-card" style="transform: rotateY(0deg);">
                <img src="' . htmlspecialchars($row['upload_file']) . '" alt="Image" 
                     width="200" height="150" 
                     class="image-thumbnail" 
                     onclick="showPopup(\'' . htmlspecialchars($row['upload_file']) . '\')">
            </div>
          </div>';
}

echo '</div></div>';

// Add Modal for full-screen popup
echo '<div id="imageModal" class="modal" style="display:none;">
        <span class="close" onclick="closePopup()">&times;</span>
        <img class="modal-content" id="modalImage">
      </div>';
page_footer_start();
?>

<?php
page_footer_end();
?>
<script>
function showPopup(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageSrc;
    modal.style.display = 'flex';
}

function closePopup() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}
</script>