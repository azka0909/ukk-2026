<?php
session_start();
if($_SESSION['status_login'] != true ){
    echo '<script>window.location= "login.php"</script>';
}

include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $CON->prepare("SELECT * FROM aspirasi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data_query = $stmt->get_result();
$data = $data_query->fetch_object();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Aspirasi</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <style>
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
            list-style: none;
            padding: 0;
            margin: 0;
        }
        header ul li:hover ul { display: flex; }
        header ul li ul li { width: 100%; }
        header ul li ul li a {
            padding: 12px 15px;
            display: block;
            border: none;
        }
        header ul li ul li a:hover { background-color: #2c3e50; }
        
        .detail-container {
            max-width: 700px;
            margin: 30px auto;
        }
        .detail-box {
            background: white;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .detail-box h3 {
            margin-top: 0;
            color: #2c3e50;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        .detail-row {
            margin-bottom: 20px;
        }
        .detail-row label {
            display: block;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .detail-row p {
            margin: 0;
            background: #f9f9f9;
            padding: 10px;
            border-left: 3px solid #3498db;
            line-height: 1.6;
        }
        .action-buttons {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
        }
        .btn {
            padding: 8px 15px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-back { background-color: #95a5a6; color: #fff; }
        .btn-delete { background-color: #e74c3c; color: #fff; }
        .btn-back:hover { background-color: #7f8c8d; }
        .btn-delete:hover { background-color: #c0392b; }
        .not-found {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1><a href="dashboard.php">Aspirasi</a></h1>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li>
                    <a href="#" style="cursor: default;">⚙ Pengelolaan Data</a>
                    <ul>
                        <li><a href="admin_kategori.php">📂 Kategori</a></li>
                        <li><a href="admin_siswa.php">👥 Siswa</a></li>
                    </ul>
                </li>
                <li><a href="aspirasi_report.php">📊 Laporan Aspirasi</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Content -->
    <div class="section">
        <div class="container">
            <div class="detail-container">
                <?php if($data): ?>
                    <div class="detail-box">
                        <h3>Detail Aspirasi</h3>
                        
                        <div class="detail-row">
                            <label>Nama Siswa :</label>
                            <p><?= htmlspecialchars($data->nama) ?></p>
                        </div>
                        
                        <div class="detail-row">
                            <label>NIS :</label>
                            <p><?= htmlspecialchars($data->nis) ?></p>
                        </div>
                        
                        <div class="detail-row">
                            <label>Kelas :</label>
                            <p><?= htmlspecialchars($data->kelas) ?></p>
                        </div>
                        
                        <div class="detail-row">
                            <label>Aspirasi / Saran :</label>
                            <p><?= nl2br(htmlspecialchars($data->isi)) ?></p>
                        </div>
                        
                        <div class="detail-row">
                            <label>Tanggal Diinput :</label>
                            <p><?= isset($data->tanggal_dibuat) ? date('d/m/Y H:i:s', strtotime($data->tanggal_dibuat)) : '-' ?></p>
                        </div>

                        <div class="action-buttons">
                            <a href="input_aspirasi.php" class="btn btn-back">Kembali</a>
                            <a href="input_aspirasi.php?delete=<?= $data->id ?>" onclick="return confirm('Hapus aspirasi ini?')" class="btn btn-delete">Hapus</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="not-found">
                        <h3>Data tidak ditemukan</h3>
                        <p>Aspirasi dengan ID tersebut tidak ada dalam sistem.</p>
                        <a href="input_aspirasi.php" class="btn btn-back">Kembali ke Daftar</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <small>Copyright &copy 2025 - E-Petugas</small>
        </div>
    </footer>
</body>
</html>