<?php
include("scripts/settings.php");
$msg = '';
$tab = 1;
page_header_start();
page_sidebar();
page_header_end();
?>
 <style>
    .liked {
    color: red;
    transition: color 0.3s ease;
}

     .card{
        width: 100%;
        padding:30px;
     }
        /* Grey background for the img-box container */
        #img-box {
            background: #f8f8f8; /* Light grey */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Outer shadow */
            margin-top: 30px;
        }

        /* Styling for individual image cards */
        #img-box .col-md-3 {
            position: relative;
            border-radius: 15px;
            background: #ffffff; /* White card background */
            padding: 5px;
           
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Card shadow */
        }

        /* Hover effect for cards */
        #img-box .col-md-3:hover {
            transform: translateY(-1px) scale(1.01); /* Lift on hover */
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2); /* Highlighted shadow */
        }

        /* Styling for images inside cards */
        #img-box .img-thumbnail {
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1); /* Add shadow to images */
            transition: transform 0.3s ease;
            width: 100%; /* Responsive image size */
            height: 150px; /* Consistent height */
            object-fit: cover; /* Maintain aspect ratio */
        }

        #img-box .img-thumbnail:hover {
            transform: scale(1.05); /* Slight zoom on image hover */
        }

        /* Detail section below images */
        #img-box .detail {
            margin-top: 10px;
            background: #eeeeee; /* Light grey for the detail box */
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Soft shadow */
            text-align: left;
        }

        #img-box .detail p {
            margin: 5px 0; /* Spacing between paragraphs */
            font-size: 14px;
            color: #333333; /* Dark grey text */
            line-height: 1.5;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            #img-box .col-md-3 {
                margin-bottom: 20px;
            }
        }

        .modal {
    position: fixed;
    z-index: 1050;
    padding-top: 60px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.9);
    display: none;
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
}

.modal .close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.modal .close:hover, .modal .close:focus {
    color: #bbb;
    text-decoration: none;
    cursor: pointer;
}
.icons a {
    color: #333;
    font-size: 1.2rem;
    margin: 0 5px;
    text-decoration: none;
}

.icons a:hover {
    color: #dc3545;
}
    </style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Album</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="mt-4">
    <?php
   
    $division_id = isset($_GET['division_id']) ? $_GET['division_id'] : '';

    if (!$division_id) {
        echo '<div class="alert alert-danger">Invalid division!</div>';
        exit;
    }
   // Fetch the division name
   $division_query = 'SELECT division_name FROM uprnss_division WHERE s_no = "' . mysqli_real_escape_string($db, $division_id) . '"';
   $division_result = mysqli_query($db, $division_query);
   $division_name = '';

   if (mysqli_num_rows($division_result) > 0) {
       $division_data = mysqli_fetch_assoc($division_result);
       $division_name = $division_data['division_name'];
   } else {
       echo '<div class="alert alert-danger">Division not found!</div>';
       exit;
   }
   // Fetch the project name
   $project_query = 'SELECT project_name_hindi FROM invoice_project_file join uprnss_project_temp on invoice_project_file.project_id=uprnss_project_temp.sno'; 
   $project_result = mysqli_query($db, $project_query);
   $project_name = '';

   if (mysqli_num_rows($project_result) > 0) {
       $project_data = mysqli_fetch_assoc($project_result);
       $project_name = $project_data['project_name_hindi'];
   } else {
       echo '<div class="alert alert-danger">Project not found!</div>';
       exit;
   }
   // Fetch the district name
   $district_query = 'SELECT district_name_hindi FROM invoice_project_file join uprnss_district on invoice_project_file.district_id=uprnss_district.sno'; 
   $district_result = mysqli_query($db, $district_query);
   $district_name = '';

   if (mysqli_num_rows($district_result) > 0) {
       $district_data = mysqli_fetch_assoc($district_result);
       $district_name = $district_data['district_name_hindi'];
   } else {
       echo '<div class="alert alert-danger">Project not found!</div>';
       exit;
   }
   $query = 'SELECT 
   invoice_project_file.*, 
   transaction_project_file.*, 
   uprnss_division.division_name, 
   uprnss_project_temp.project_name_hindi, 
   uprnss_district.district_name_hindi 
FROM 
   invoice_project_file 
LEFT JOIN 
   transaction_project_file 
ON 
   transaction_project_file.invoice_id = invoice_project_file.sno 
LEFT JOIN 
   uprnss_project_temp 
ON 
   invoice_project_file.project_id = uprnss_project_temp.sno 
LEFT JOIN 
   uprnss_division 
ON 
   uprnss_division.s_no = invoice_project_file.division_id 
LEFT JOIN 
   uprnss_district 
ON 
   invoice_project_file.district_id = uprnss_district.sno 
WHERE 
   invoice_project_file.division_id = "' . mysqli_real_escape_string($db, $division_id) . '" 
ORDER BY 
   transaction_project_file.file_type DESC, 
   invoice_project_file.sno DESC';


$result = mysqli_query($db, $query);

echo '<div class="card">
<h2 class="text-center bg-danger text-light">Files for Division ' . htmlspecialchars($division_name) . '</h2>
<div id="img-box" class="row text-center justify-content-center mt-4">';

if (mysqli_num_rows($result) > 0) {
    while ($file = mysqli_fetch_assoc($result)) {
        // Determine the file type (image or video)
        $fileExtension = pathinfo($file['upload_file'], PATHINFO_EXTENSION);
        $isVideo = in_array(strtolower($fileExtension), ['mp4', 'webm', 'ogg']); // Add more video extensions if needed
        
        // Check if like_status is 1 to apply the red color
        $heartColor = $file['like_status'] == 1 ? 'style="color: red;"' : '';
        
        echo '<div class="col-md-3 mb-4">';
        
        if ($isVideo) {
            // Display video
            echo '<video controls class="video-thumbnail img-thumbnail"
                    style="height: 150px; width: auto;" 
                    onclick="openModal(\'' . htmlspecialchars($file['upload_file']) . '\')">
                    <source src="' . htmlspecialchars($file['upload_file']) . '" type="video/' . htmlspecialchars($fileExtension) . '">
                  Your browser does not support the video tag.
                  </video>';
        } else {
            // Display image
            echo '<img src="' . htmlspecialchars($file['upload_file']) . '" alt="Image" 
                  class="img-thumbnail image-thumb" 
                  style="height: 150px; width: auto;" 
                  onclick="openModal(\'' . htmlspecialchars($file['upload_file']) . '\')">';
        }

        // File details and options
        echo '<div class="detail">
                <div class="d-flex justify-content-between">
                    <div>
                        <p><b>Remark: </b>' . htmlspecialchars($file['remark']) . '</p>
                        <p><b>Division: </b>' . htmlspecialchars($file['division_name']) . '</p>
                    </div>
                    
                    <div class="icons">
                        <a href="' . htmlspecialchars($file['upload_file']) . '" download 
                           title="Download" class="download-icon">
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="javascript:void(0);" title="Like" class="like-icon" 
                           data-id="' . htmlspecialchars($file['sno']) . '" 
                           onclick="toggleLike(this)">
                            <i class="fas fa-heart" ' . $heartColor . '></i>
                        </a>
                    </div>
                </div>
                <p><b>Project: </b>' . htmlspecialchars($file['project_name_hindi']) . '</p>
              </div>
              </div>';
    }
} else {
    echo '<p>No files found for this division.</p>';
}

echo '</div>
      </div>';

    ?>
    <div id="imageModal" class="modal" style="display: none;">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>
</div>
</body>
</html>

<?php
page_footer_start();
?>
<script>
function openModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imageSrc;
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}
</script>

<script>
function openModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imageSrc;
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}


</script>

<script>
    function toggleLike(element) {
    const fileId = element.getAttribute('data-id');
    const heartIcon = element.querySelector('.fas.fa-heart');

    fetch('update_like_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `file_id=${fileId}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Toggle the heart icon style
            heartIcon.classList.toggle('liked'); // Add 'liked' for styling
            if (data.new_like_status === 1) {
                heartIcon.style.color = 'red'; // Change icon color to red
            } else {
                heartIcon.style.color = ''; // Reset icon color
            }
        } else {
            alert('Failed to update like status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

</script>
<?php
page_footer_end();
?>