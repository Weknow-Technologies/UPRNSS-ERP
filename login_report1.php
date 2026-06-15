<?php
include("scripts/settings.php");
include("scripts/billit_settings.php");

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$view_div = isset($_GET['view_div']) ? intval($_GET['view_div']) : 0;
$days = isset($_GET['days']) ? intval($_GET['days']) : 0;

// SQL query for division login summary - Filtering for USERS TYPE 1, 6, 9 ONLY
$sql = "SELECT 
    d.s_no,
    d.division_name,
    (SELECT COUNT(DISTINCT u.sno) FROM users u
     JOIN user_division ud ON u.sno = ud.user_id
     WHERE ud.division_id = d.s_no AND u.type IN (1, 6, 9)) as total_ids_count,
    (SELECT COUNT(DISTINCT s.sno) FROM session s
     JOIN users u ON s.user = u.userid
     JOIN user_division ud ON u.sno = ud.user_id
     WHERE ud.division_id = d.s_no AND s.s_start_date = '$date' AND u.type IN (1, 6, 9)) as today_count,
    (SELECT COUNT(DISTINCT s.sno) FROM session s
     JOIN users u ON s.user = u.userid
     JOIN user_division ud ON u.sno = ud.user_id
     WHERE ud.division_id = d.s_no AND s.s_start_date >= DATE_SUB('$date', INTERVAL 7 DAY) AND s.s_start_date <= '$date' AND u.type IN (1, 6, 9)) as week_count,
    (SELECT COUNT(DISTINCT s.sno) FROM session s
     JOIN users u ON s.user = u.userid
     JOIN user_division ud ON u.sno = ud.user_id
     WHERE ud.division_id = d.s_no AND s.s_start_date >= DATE_SUB('$date', INTERVAL 15 DAY) AND s.s_start_date <= '$date' AND u.type IN (1, 6, 9)) as fifteen_count
FROM uprnss_division d
ORDER BY d.division_name";

$result = $db->query($sql);


?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Division Login Report</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      background: #f5f5f5;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      margin-bottom: 10px;
    }

    .filter {
      background: #fff;
      padding: 10px 15px;
      border-radius: 6px;
      margin-bottom: 15px;
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }

    .filter input {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 13px;
    }

    .filter button {
      padding: 6px 16px;
      background: #c12404ff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .filter a {
      padding: 6px 12px;
      background: #eee;
      border-radius: 4px;
      text-decoration: none;
      color: #333;
    }

    .report-container {
      display: flex;
      gap: 20px;
      flex-direction: column;
    }

    .card {
      background: #fff;
      border-radius: 6px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0, 0, 0, .1);
      padding-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
    }

    th {
      background: #b72407ff;
      color: #fff;
      padding: 12px 10px;
      text-align: center;
      font-size: 13px;
      white-space: nowrap;
    }

    td {
      padding: 10px 10px;
      border-bottom: 1px solid #eee;
      text-align: center;
    }

    tr:hover td {
      background: #f0f7ff;
    }

    .summary {
      margin: 15px 0;
      font-size: 15px;
      text-align: center;
      color: #b72407ff;
      font-weight: bold;
    }

    .detail-header {
      background: #333;
      color: #fff;
      padding: 10px 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .detail-header a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      font-size: 16px;
    }


    @media print {
      .no-print {
        display: none;
      }

      body {
        background: #fff;
        padding: 0;
      }
    }
  </style>
  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
</head>

<body>

  <h2>Division Login Summary Report</h2>

  <div class="filter no-print">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <label>Current Date: <input type="date" name="date" value="<?= htmlspecialchars($date) ?>"></label>
      <button type="submit">Filter Summary</button>
      <a href="?">Reset</a>
      <button type="button" onclick="window.print()">Print</button>
      <button type="button" onclick="exportToExcel()" class="btn btn-success"
        style="padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 4px;">📊 Export
        Excel</button>
    </form>
  </div>

  <div class="report-container">
    <div class="card">
      <div class="summary">
        Summary for Date: <?= date('d-M-Y', strtotime($date)) ?>
      </div>
      <table id="loginTable">
        <thead>
          <tr>
            <th>#</th>
            <th style="text-align: left;">Division Name</th>
            <th>Total IDs</th>
            <th>Login (Today)</th>
            <th>Last 7 Days</th>
            <th>Last 15 Days</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $serial = 1;
          $total_ids = 0;
          $total_today = 0;
          $total_week = 0;
          $total_fifteen = 0;

          if ($result && $result->num_rows > 0):
            while ($r = $result->fetch_assoc()):
              $total_ids += $r['total_ids_count'];
              $total_today += $r['today_count'];
              $total_week += $r['week_count'];
              $total_fifteen += $r['fifteen_count'];
              $active_row = ($view_div == $r['s_no']) ? 'style="background:#fff9c4;"' : '';
              ?>
              <tr <?= $active_row ?>>
                <td><?= $serial++ ?></td>
                <td style="text-align: left;"><b><?= htmlspecialchars($r['division_name']) ?></b></td>
                <td><?= $r['total_ids_count'] ?></td>
                <td><?= $r['today_count'] ?></td>
                <td><?= $r['week_count'] ?></td>
                <td><?= $r['fifteen_count'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:20px;color:#aaa;">Koi record nahi mila</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <tfoot>
            <tr style="background: #f9f9f9; font-weight: bold;">
              <td colspan="2" style="text-align: right;">Total Logins:</td>
              <td style="color: blue;"><?= $total_ids ?></td>
              <td style="color: blue;"><?= $total_today ?></td>
              <td><?= $total_week ?></td>
              <td><?= $total_fifteen ?></td>
            </tr>
          </tfoot>
        <?php endif; ?>
      </table>
    </div>


    </div>

  <!-- DataTables and Excel Export Libraries -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

  <script>
    var loginTable;

    function exportToExcel() {
      // Trigger the export on the existing table instance
      $('.buttons-excel').click();
    }

    $(document).ready(function () {
      // Initialize main summary table
      loginTable = $('#loginTable').DataTable({
        paging: true,
        pageLength: 20,
        ordering: true,
        info: true,
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Export Excel',
            className: 'invisible-btn', // Hidden since we have a custom trigger button
            filename: 'Division_Login_Report_' + '<?= $date ?>',
            title: 'Division Login Summary Report - <?= $date ?>',
            messageTop: 'Division-wise login counts for Today, last 7 days, and last 15 days.'
          }
        ]
      });

      // Hide the default DataTables buttons wrapper but keep buttons accessible
      $('.dt-buttons').css('display', 'none');
    });
  </script>

</body>

</html>