<?php
include("scripts/settings.php");
$msg = '';
$tab = 1;
page_header_start();
page_sidebar();
?>

<link rel="stylesheet" href="css/impulseslider.css" type="text/css" media="screen" />

<?php


page_header_end();
?>
<!---<div class="social-bar">
    <a href="#" class="social-link">
        <img src="img/case_study.png" alt="" class="icon" style="height: 90px;width: 85px;margin-right: 0px;">
        <span class="text1">Case Study</span>
    </a>
    <a href="#" target="_blank" class="social-link" >
        <img src="img/best_practice3.png" alt="" class="icon" style="height: 90px;width: 85px;margin-right: 0px;">
        <span class="text1">Best Practices</span>
    </a>
    <a href="#" class="social-link">
        <img src="img/success.jpg" alt="" class="icon" style="height: 90px;width: 85px;margin-right: 0px;">
        <span class="text1">Success Story</span>
    </a>
    <a href="#" target="_blank" class="social-link">
        <img src="img/enquiry1.png" alt="" class="icon" style="height: 80px;width: 85px;margin-right: 0px;">
        <span class="text1">Enquiry</span>
    </a>
</div>--->

<div class="row">
        <div class="col-md-12">
            <?php
          
            echo '<div class="card"><div class="content">
            <div class="row"><div class="col-12"><h4 style="color: #FFFFFF; background: #ec4049; border-radius: 15px; padding: 10px 10px 6px 20px;">
                <img src="images/logo/11.png" alt="text" class="img-fluid stat-icon" style="height:50px; width:50px;"> Albums</h4></div></div>
            <div class="card-body" style="background: whitesmoke; border-radius: 15px; padding: 20px;">
                <div class="row text-center justify-content-center">';
            
            // Fetch divisions
            $query = 'SELECT * FROM uprnss_division WHERE s_no != 53 ORDER BY division_name ASC';
            $run = mysqli_query($db, $query);
            $dataArray = [];
            while ($data = mysqli_fetch_array($run)) {
                $dataArray[] = $data;
            }

            $i = 1;
            foreach ($dataArray as $k => $v) {
                $sql = 'SELECT * FROM `invoice_project_file`  
                        LEFT JOIN transaction_project_file 
                        ON transaction_project_file.invoice_id = invoice_project_file.sno 
                        WHERE division_id = "' . $v['s_no'] . '" 
                        ORDER BY invoice_project_file.sno DESC LIMIT 2';
                $result = mysqli_query($db, $sql);

                // Add a link to the album
                echo '<a  href="view_album.php?division_id=' . urlencode($v['s_no']) . '" id="album" class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="border" style="border-radius: 15px; border: 3px solid #007BFF; 
                        background: linear-gradient(135deg, #ec4049 0%, #FF6F61 100%); padding: 15px; 
                        box-shadow: 0 6px 10px rgba(0,0,0,0.15);">
                        <div style="position: relative; width: 100px; height: 100px; margin: 0 auto;">';

                // Display thumbnails
                if (mysqli_num_rows($result) > 0) {
                    $zIndex = 1;
                    while ($albums_ui = mysqli_fetch_assoc($result)) {
                        if ($zIndex == 1) {
                            echo '<img src="' . htmlspecialchars($albums_ui['upload_file']) . '" alt="' . $zIndex . '" height="70" 
                                style="position: absolute; top: 0; left: 0; transform: rotate(-10deg); z-index: ' . $zIndex . '; 
                                border-radius: 10%; border: 2px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">';
                        } else {
                            echo '<img src="' . htmlspecialchars($albums_ui['upload_file']) . '" alt="' . $zIndex . '" height="70" 
                                style="position: absolute; top: 15px; left: 10px; transform: rotate(5deg); z-index: ' . $zIndex . '; 
                                border-radius: 10%; border: 2px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">';
                        }
                        $zIndex++;
                    }
                } else {
                    echo '<img src="images/no_image_found.jpg" height="70" 
                        style="margin: 5px; border-radius: 10%; box-shadow: 0 4px 6px rgba(0,0,0,0.3);"><br>
                        <span style="color: #FFF;">No image found</span>';
                }

                echo '</div>
                      <div class="alert alert-primary text-center font-weight-bold mt-4 p-2" 
                           style="background: rgba(0, 0, 0, 0.5); color: #FFF; border: none;">
                        ' . $i . '.&nbsp;' . htmlspecialchars($v['division_name']) . '
                      </div>
                    </div>
                </a>';

                if ($i % 5 == 0) {
                    echo '</div><div class="row text-center justify-content-center">';
                }
                $i++;
            }

            echo '</div></div></div>';
            ?>
        </div>
    </div>

<?php
page_footer_start();
?>

<script src="js/jquery.impulse.slider.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart.js initialization
    const ctx = document.getElementById('myChart');
    const ctx1 = document.getElementById('myChart1');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['2021-22', '2022-23', '2023-24'],
            datasets: [
                {
                    label: 'लाभ में',
                    data: [5498, 5788, 6245],
                    borderWidth: 1
                },
                {
                    label: 'हानि में',
                    data: [2000, 1710, 1253],
                    borderWidth: 1
                },
                {
                    label: 'संचित लाभ में',
                    data: [4023, 4569, 5163],
                    borderWidth: 1
                },
                {
                    label: 'संचित हानि में',
                    data: [3475, 2929, 2335],
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['अप्रैल-जून', 'जुलाई', 'अगस्त', 'सितम्बर', 'अक्टूबर', 'नवम्बर', 'दिसम्बर', 'जनवरी', 'फरवरी', 'मार्च'],
            datasets: [
                {
                    label: 'कुल व्यापार (रू करोड़ में)',
                    data: [20, 45, 30, 25, 50, 60, 70, 85, 90, 100],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php
page_footer_end();
?>
