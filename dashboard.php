<?php
     session_start();
     if($_SESSION['status_login'] != true ){
         echo '<script>window.location= "login.php"</script>';
     }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <style>
       
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table tr th, .table tr td {
            padding: 10px;
            border: 1px solid #020202;
            text-align: left;
        }
        .table tr th {
            background-color: #f9f9f9;
        }
        .btn {
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        .btn-edit { background-color: #f1c40f; color: #fff; }
        .btn-delete { background-color: #e74c3c; color: #fff; }
        .btn-add { background-color: #2980b9; color: #fff; margin-bottom: 10px; }
        
        /* Dropdown Menu Styles */
        header ul li { position: relative; }
        header ul li:hover > a { background-color: rgba(255, 255, 255, 0.1); }
        header ul li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #34495e;
            flex-direction: column;
            width: 200px;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        header ul li:hover ul { display: flex; }
        header ul li ul li { width: 100%; }
        header ul li ul li a {
            padding: 12px 15px;
            display: block;
            border: none;
        }
        header ul li ul li a:hover { background-color: #2c3e50; }
    </style>
</head>
<body>
      <!-- Header -->
    <header>
        <div class="container">
            <h1><a href="dashboard.php">Aspirasi</a></h1>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="aspirasi_report.php">Laporan Aspirasi</a>
                <li><a href="admin_kategori.php">Kategori</a></li>
                <li><a href="admin_siswa.php">Siswa</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

<!-- content -->
<div class="section">
    <div class="container">
        <h3>Dashboard Admin</h3>
        <div class="box">
            <h4>Selamat Datang <?php echo $_SESSION['a_global']->username?> di Sistem Aspirasi</h4>
        </div>
        
        <!-- Statistik Aspirasi -->
        <?php
        include 'db.php';
        
        $total_aspirasi_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi");
        $total_aspirasi = mysqli_fetch_object($total_aspirasi_query)->total;
        
        $total_menunggu_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Menunggu'");
        $total_menunggu = mysqli_fetch_object($total_menunggu_query)->total;
        
        $total_proses_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Proses'");
        $total_proses = mysqli_fetch_object($total_proses_query)->total;
        
        $total_selesai_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Selesai'");
        $total_selesai = mysqli_fetch_object($total_selesai_query)->total;
        ?>
        
        <h3 style="margin-top: 30px;">Statistik Sistem</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div style="padding: 20px; background: #3498db; color: white; border-radius: 5px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?= $total_aspirasi ?></div>
                <div style="font-size: 14px;">Total Aspirasi</div>
            </div>
            <div style="padding: 20px; background: #e74c3c; color: white; border-radius: 5px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?= $total_menunggu ?></div>
                <div style="font-size: 14px;">Menunggu</div>
            </div>
            <div style="padding: 20px; background: #f39c12; color: white; border-radius: 5px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?= $total_proses ?></div>
                <div style="font-size: 14px;">Proses</div>
            </div>
            <div style="padding: 20px; background: #27ae60; color: white; border-radius: 5px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?= $total_selesai ?></div>
                <div style="font-size: 14px;">Selesai</div>
            </div>
        </div>
        
        <!-- <h3>Laporan Aspirasi</h3>
        <div style="margin-top: 20px;">
            <a href="aspirasi_report.php" style="padding: 15px 30px; background: #9b59b6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                📊 Lihat Laporan Aspirasi Lengkap -->
            </a>
        </div>
    </div>
</div>

<!-- footer -->
<footer>
    <div class="container">
        <small>Copyright &copy 2025 - Sistem Aspirasi</small>
    </div>
</footer>
</body>
</html>