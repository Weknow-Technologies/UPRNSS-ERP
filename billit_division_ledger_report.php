<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");
include("scripts/alerts.php");

$msg = '';

page_header_start();
page_header_end();
page_sidebar();
?>

<style>
    @media print {

        /* Hide everything except the table */
        body * {
            visibility: hidden;
        }

        .table-responsive,
        .table-responsive * {
            visibility: visible;
        }

        .table-responsive {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            overflow: visible !important;
            border: none !important;
        }

        @page {
            size: A4 portrait;
            margin: 0.4cm;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .table th,
        .table td {
            font-size: 14px !important;
            line-height: 1.2 !important;
        }

        .table th {
            font-size: 14px !important;
            font-weight: bold !important;
            /* Force background colors to print */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Force header colors to print */
        .thead-light th {
            background-color: #c0392b !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Force striped row colors */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #fff5f5 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Grand total row */
        .bg-light {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .h5 {
            font-size: 12px !important;
        }

        strong {
            font-weight: bold !important;
        }
    }

    /* Screen styles */
    .table th,
    .table td {
        font-size: 14px;
        padding: 4px 6px;
    }

    .table thead th {
        vertical-align: middle;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                <h5 class="mb-0"><i class="fa fa-bar-chart mr-2"></i> Division-wise Ledger Count Report</h5>
                <div>
                    <button class="btn btn-sm btn-danger mr-1" onclick="exportToPDF()">
                        <i class="fa fa-file-pdf-o mr-1"></i> Export to PDF
                    </button>
                    <button class="btn btn-sm btn-light" onclick="window.print()">
                        <i class="fa fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">S.No.</th>
                                <th class="text-center">Division Name</th>
                                <th class="text-center">Total Ledgers</th>
                                <th class="text-center">Project Ledgers</th>
                                <th class="text-center">Other Ledgers</th>
                                <th class="text-center">Groups Created</th>
                                <th class="text-center">Opening Feeded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_proj = "SELECT sno FROM billit_pl_heads WHERE description LIKE '%Project Construction Work Balance%' AND (parent = '0' OR parent = '' OR parent IS NULL)";
                            $res_proj = execute_query($sql_proj);
                            $proj_sno = 11; // default fallback
                            if ($row_proj = mysqli_fetch_assoc($res_proj)) {
                                $proj_sno = (int) $row_proj['sno'];
                            }

                            if (!function_exists('get_descendant_heads')) {
                                function get_descendant_heads($parent_id)
                                {
                                    $ids = [(int) $parent_id];
                                    $sql = "SELECT sno FROM billit_pl_heads WHERE parent = '" . $parent_id . "'";
                                    $res = execute_query($sql);
                                    while ($row = mysqli_fetch_assoc($res)) {
                                        $ids = array_merge($ids, get_descendant_heads($row['sno']));
                                    }
                                    return $ids;
                                }
                            }
                            $project_heads = get_descendant_heads($proj_sno);
                            $project_heads_in = implode(",", $project_heads);
                            $sql = "SELECT d.s_no, d.division_name, 
                                           (SELECT COUNT(*) FROM billit_customer c WHERE c.unit_id = d.s_no) as ledger_count,
                                           (SELECT COUNT(*) FROM billit_pl_heads h WHERE h.parent != '0' AND h.parent != '' AND h.parent IS NOT NULL AND h.unit_id = d.s_no) as groups_created,
                                           (SELECT COUNT(*) FROM billit_customer c WHERE c.unit_id = d.s_no AND c.opening_balance IS NOT NULL AND c.opening_balance != '' AND c.opening_balance != '0' AND c.opening_balance != '0.00') as opening_feeded,
                                           (SELECT COUNT(*) FROM billit_customer c WHERE c.unit_id = d.s_no AND c.parent IN ($project_heads_in)) as project_ledgers
                                    FROM uprnss_division d 
                                    ORDER BY ledger_count DESC, d.division_name ASC";
                            $res = execute_query($sql);

                            if (mysqli_num_rows($res) > 0) {
                                $i = 1;
                                $total_ledgers = 0;
                                $total_project_ledgers = 0;
                                $total_other_ledgers = 0;
                                $total_groups_created = 0;
                                $total_opening_feeded = 0;
                                while ($row = mysqli_fetch_assoc($res)) {
                                    $count = (int) $row['ledger_count'];
                                    $groups_created = (int) $row['groups_created'];
                                    $opening_feeded = (int) $row['opening_feeded'];
                                    $project_ledgers = (int) $row['project_ledgers'];
                                    $other_ledgers = $count - $project_ledgers;

                                    $total_ledgers += $count;
                                    $total_project_ledgers += $project_ledgers;
                                    $total_other_ledgers += $other_ledgers;
                                    $total_groups_created += $groups_created;
                                    $total_opening_feeded += $opening_feeded;

                                    echo "<tr>";
                                    echo "<td class='text-center'>{$i}</td>";
                                    echo "<td class='text-left' style='text-align: left !important;'><strong>{$row['division_name']}</strong></td>";
                                    echo "<td class='text-center'><strong>{$count}</strong></td>";
                                    echo "<td class='text-center'><strong>{$project_ledgers}</strong></td>";
                                    echo "<td class='text-center'><strong>{$other_ledgers}</strong></td>";
                                    echo "<td class='text-center'><strong>{$groups_created}</strong></td>";
                                    echo "<td class='text-center'><strong>{$opening_feeded}</strong></td>";
                                    echo "</tr>";
                                    $i++;
                                }
                                echo "<tr class='bg-light'>";
                                echo "<td colspan='2' class='text-right'><strong>GRAND TOTAL</strong></td>";
                                echo "<td class='text-center h5'><strong>{$total_ledgers}</strong></td>";
                                echo "<td class='text-center h5'><strong>{$total_project_ledgers}</strong></td>";
                                echo "<td class='text-center h5'><strong>{$total_other_ledgers}</strong></td>";
                                echo "<td class='text-center h5'><strong>{$total_groups_created}</strong></td>";
                                echo "<td class='text-center h5'><strong>{$total_opening_feeded}</strong></td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='7' class='text-center text-muted py-4'>No divisions found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
page_footer_start();
?>
<script>
    function loadScript(src, callback) {
        if (document.querySelector('script[src="' + src + '"]')) {
            callback(); return;
        }
        var s = document.createElement('script');
        s.src = src;
        s.onload = callback;
        document.head.appendChild(s);
    }

    function exportToPDF() {
        var btn = document.querySelectorAll('.btn');
        btn.forEach(function (b) { b.disabled = true; b.innerText = 'Generating...'; });

        loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', function () {
            loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', function () {

                var table = document.querySelector('.table-responsive');
                var origStyle = table.getAttribute('style') || '';
                table.style.overflow = 'visible';
                table.style.width = 'auto';
                table.style.maxWidth = 'none';

                html2canvas(table, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    scrollX: 0,
                    scrollY: -window.scrollY,
                    windowWidth: table.scrollWidth,
                    windowHeight: table.scrollHeight
                }).then(function (canvas) {
                    table.setAttribute('style', origStyle);
                    var imgData = canvas.toDataURL('image/jpeg', 0.95);
                    var { jsPDF } = window.jspdf;
                    var pageW = 210;
                    var pageH = 297;
                    var margin = 8;
                    var usableW = pageW - margin * 2;
                    var usableH = pageH - margin * 2;

                    // Canvas dimensions in mm at 96dpi
                    var canvasW = canvas.width / 2;   // divide by scale
                    var canvasH = canvas.height / 2;

                    // Convert px to mm (1px = 0.264583mm)
                    var imgW_mm = canvasW * 0.264583;
                    var imgH_mm = canvasH * 0.264583;

                    // Scale to fit usable width
                    var ratio = usableW / imgW_mm;
                    var finalW = usableW;
                    var finalH = imgH_mm * ratio;

                    // If still too tall for 1 page, scale down further
                    if (finalH > usableH) {
                        ratio = usableH / imgH_mm;
                        finalH = usableH;
                        finalW = imgW_mm * ratio;
                    }

                    // Center horizontally
                    var xOffset = margin + (usableW - finalW) / 2;

                    var doc = new jsPDF({
                        orientation: finalH > finalW ? 'portrait' : 'landscape',
                        unit: 'mm',
                        format: 'a4'
                    });

                    // Title
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('Division-wise Ledger Count Report', margin, margin - 2);

                    doc.addImage(imgData, 'JPEG', xOffset, margin, finalW, finalH);
                    doc.save('Division_wise_Ledger_Count.pdf');

                    btn.forEach(function (b) {
                        b.disabled = false;
                        b.innerHTML = b.classList.contains('btn-danger')
                            ? '<i class="fa fa-file-pdf-o mr-1"></i> Export to PDF'
                            : '<i class="fa fa-print mr-1"></i> Print';
                    });
                });
            });
        });
    }
</script>
<?php
page_footer_end();
?>