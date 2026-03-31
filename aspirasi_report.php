<?php
session_start();
if($_SESSION['status_login'] != true ){
    echo '<script>window.location= "login.php"</script>';
}

include 'db.php';

// Get filter dari GET
$filter_status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$filter_nis = isset($_GET['nis']) ? htmlspecialchars($_GET['nis']) : '';
$filter_tanggal = isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : '';
$filter_bulan = isset($_GET['bulan']) ? htmlspecialchars($_GET['bulan']) : '';
$filter_kategori = isset($_GET['kategori']) ? htmlspecialchars($_GET['kategori']) : '';

// Build WHERE clause
$where = "1=1";
if($filter_status) $where .= " AND a.status = '$filter_status'";
if($filter_nis) $where .= " AND ia.nis = '$filter_nis'";
if($filter_tanggal) $where .= " AND DATE(a.tanggal_dibuat) = '$filter_tanggal'";
if($filter_bulan) $where .= " AND MONTH(a.tanggal_dibuat) = '$filter_bulan'";
if($filter_kategori) $where .= " AND ia.id_kategori = '$filter_kategori'";

// Query dengan filter
$data_query = mysqli_query($CON, "SELECT a.*, ia.nis, ia.lokasi, ia.ket, ia.id_pelapor as id_pelapor_input, ia.tanggal_input, s.kelas, s.id_pelapor as id_plp_siswa
                                  FROM tb_aspirasi a 
                                  JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
                                  LEFT JOIN tb_siswa s ON ia.nis = s.nis
                                  WHERE $where
                                  ORDER BY a.tanggal_dibuat DESC");

if($data_query === false) {
    die('Query error: ' . mysqli_error($CON));
}
$total = mysqli_num_rows($data_query);

// Get statistik
$total_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi");
$total_all = mysqli_fetch_object($total_query)->total;

$selesai_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Selesai'");
$total_selesai = mysqli_fetch_object($selesai_query)->total;

$proses_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Proses'");
$total_proses = mysqli_fetch_object($proses_query)->total;

$menunggu_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Menunggu'");
$total_menunggu = mysqli_fetch_object($menunggu_query)->total;

// Get list siswa untuk filter
$siswa_query = mysqli_query($CON, "SELECT DISTINCT ia.nis, s.kelas FROM tb_input_aspirasi ia LEFT JOIN tb_siswa s ON ia.nis = s.nis ORDER BY ia.nis ASC");
$siswa_list = [];
while($s = mysqli_fetch_object($siswa_query)){
    $siswa_list[] = $s;
}

// Get list kategori untuk filter
$kategori_query = mysqli_query($CON, "SELECT id_kategori, ket_kategori FROM tb_kategori ORDER BY ket_kategori ASC");
$kategori_list = [];
while($k = mysqli_fetch_object($kategori_query)){
    $kategori_list[] = $k;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Aspirasi - Admin</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Quicksand', sans-serif; background-color: #f5f7fa; }
        
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
        
        .section { background-color: #f5f7fa; padding: 30px 0; }
        h3 { color: #2c3e50; font-size: 24px; font-weight: 600; margin: 0; }
        h4 { color: #2c3e50; margin-bottom: 15px; font-size: 16px; font-weight: 600; }
        
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-item {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: white;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .stat-item:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .stat-all { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .stat-selesai { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); }
        .stat-proses { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .stat-menunggu { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
        .stat-number { font-size: 28px; margin-bottom: 8px; }
        
        .filter-box {
            background: white;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid #3498db;
        }
        .filter-box strong { color: #2c3e50; font-size: 15px; display: block; margin-bottom: 15px; }
        .filter-box form { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; }
        .filter-box label { font-size: 12px; color: #666; display: block; margin-bottom: 5px; font-weight: 600; }
        .filter-box input, .filter-box select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px; font-family: 'Quicksand', sans-serif; }
        .filter-box input:focus, .filter-box select:focus { outline: none; border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); }
        
        .box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .table-wrapper {
            margin-top: 15px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .table thead tr {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        .table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            color: white;
            border-bottom: 2px solid #1a252f;
            white-space: nowrap;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            color: #2c3e50;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table td[data-wrap="yes"] {
            white-space: normal;
            word-break: break-word;
        }
        .table tbody tr { transition: all 0.2s ease; }
        .table tbody tr:hover { background-color: #f8f9fa; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .table tbody tr:nth-child(even) { background-color: #fafbfc; }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-menunggu { background-color: #e74c3c; color: white; }
        .badge-proses { background-color: #f39c12; color: white; }
        .badge-selesai { background-color: #27ae60; color: white; }
        
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 12px; display: inline-block; border: none; cursor: pointer; transition: all 0.3s ease; font-weight: 600; }
        .btn-back { background-color: #95a5a6; color: #fff; margin-top: 15px; }
        .btn-back:hover { background-color: #7f8c8d; box-shadow: 0 3px 8px rgba(127, 140, 141, 0.3); transform: translateY(-1px); }
        
        .button-group { display: flex; gap: 10px; margin-top: 20px; }
        .button-group button { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 13px; transition: all 0.3s ease; }
        .button-group button[type="submit"] { background-color: #3498db; color: white; }
        .button-group button[type="submit"]:hover { background-color: #2980b9; box-shadow: 0 3px 8px rgba(52, 152, 219, 0.3); transform: translateY(-1px); }
        .button-group a { padding: 10px 20px; background-color: #95a5a6; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
        .button-group a:hover { background-color: #7f8c8d; }
        
        .no-data { text-align: center; padding: 40px; color: #95a5a6; font-style: italic; }
        code { background-color: #f0f2f5; padding: 3px 6px; border-radius: 3px; font-family: 'Courier New', monospace; font-size: 12px; color: #2c3e50; }
        
        .btn-kelola { padding: 12px 25px; background: #9b59b6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; transition: all 0.3s ease; }
        .btn-kelola:hover { background: #8e44ad; box-shadow: 0 4px 12px rgba(155, 89, 182, 0.3); transform: translateY(-2px); }
        
        @media (max-width: 1200px) {
            .table { font-size: 12px; }
            .table th, .table td { padding: 10px; }
            .stats-box { grid-template-columns: repeat(2, 1fr); }
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
                <li><a href="aspirasi_report.php">Laporan Aspirasi</a></li>
                <li><a href="admin_kategori.php">Kategori</a></li>
                <li><a href="admin_siswa.php">Siswa</a></li>                              
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Content -->
    <div class="section">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h3>Laporan Aspirasi Siswa</h3>
                <a href="admin_aspirasi.php" class="btn-kelola">Kelola Aspirasi</a>
            </div>

            <!-- Statistik -->
            <div class="stats-box">
                <div class="stat-item stat-all">
                    <div class="stat-number"><?= $total_all ?></div>
                    <div>Total Aspirasi</div>
                </div>
                <div class="stat-item stat-selesai">
                    <div class="stat-number"><?= $total_selesai ?></div>
                    <div>Selesai</div>
                </div>
                <div class="stat-item stat-proses">
                    <div class="stat-number"><?= $total_proses ?></div>
                    <div>Proses</div>
                </div>
                <div class="stat-item stat-menunggu">
                    <div class="stat-number"><?= $total_menunggu ?></div>
                    <div> Menunggu</div>
                </div>
            </div>

            <!-- Filter -->
            <div class="filter-box">
                <form method="GET" action="aspirasi_report.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px;">
                    
                    <!-- Filter Status -->
                    <div>
                        <label style="font-size: 12px; color: #666;">Status</label>
                        <select name="status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" <?= $filter_status == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Proses" <?= $filter_status == 'Proses' ? 'selected' : '' ?>>Proses</option>
                            <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                    
                    <!-- Filter NIS -->
                    <div>
                        <label style="font-size: 12px; color: #666;">NIS Siswa</label>
                        <select name="nis" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <option value="">Semua NIS</option>
                            <?php foreach($siswa_list as $s): ?>
                                <option value="<?= $s->nis ?>" <?= $filter_nis == $s->nis ? 'selected' : '' ?>>
                                    <?= $s->nis ?> (<?= $s->kelas ?? '-' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filter Tanggal -->
                    <div>
                        <label style="font-size: 12px; color: #666;">Per Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $filter_tanggal ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                    </div>
                    
                    <!-- Filter Bulan -->
                    <div>
                        <label style="font-size: 12px; color: #666;">Per Bulan</label>
                        <select name="bulan" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <option value="">Semua Bulan</option>
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $filter_bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                    <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?> - <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Filter Kategori -->
                    <div>
                        <label style="font-size: 12px; color: #666;">Per ategori</label>
                        <select name="kategori" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                            <option value="">Semua Kategori</option>
                            <?php foreach($kategori_list as $k): ?>
                                <option value="<?= $k->id_kategori ?>" <?= $filter_kategori == $k->id_kategori ? 'selected' : '' ?>><?= $k->ket_kategori ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Button -->
                    <div style="display: flex; gap: 5px; align-items: flex-end;">
                        <button type="submit" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 3px; cursor: pointer; font-weight: bold;">Terapkan</button>
                        <a href="aspirasi_report.php" style="padding: 8px 15px; background: #95a5a6; color: white; text-decoration: none; border-radius: 3px; font-weight: bold; text-align: center;">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="box">
                <h4>    Daftar Aspirasi (Total: <?= $total ?> Data)</h4>
                <?php if($total > 0): ?>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="13%">ID PLP</th>
                                    <th width="8%">NIS</th>
                                    <th width="6%">Kls</th>
                                    <th width="10%">Status</th>
                                    <th width="12%">Lokasi</th>
                                    <th width="15%">Aspirasi</th>
                                    <th width="17%">Feedback</th>
                                    <th width="12%">Tgl Input</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while($d = mysqli_fetch_object($data_query)){
                                    $badge_class = 'badge-' . strtolower($d->status);
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><code><strong><?= htmlspecialchars($d->id_plp_siswa ?? '-') ?></strong></code></td>
                                        <td><strong><?= htmlspecialchars($d->nis) ?></strong></td>
                                        <td><?= htmlspecialchars($d->kelas ?? '-') ?></td>
                                        <td><span class="badge <?= $badge_class ?>"><?= $d->status ?></span></td>
                                        <td><?= htmlspecialchars($d->lokasi ?? '-') ?></td>
                                        <td data-wrap="yes"><?= htmlspecialchars($d->ket ?? '-') ?></td>
                                        <td data-wrap="yes"><?= htmlspecialchars($d->feedback ?? '-') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($d->tanggal_input)) ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>📭 Tidak ada data aspirasi dengan filter yang dipilih</p>
                    </div>
                <?php endif; ?>
            </div>



        </div>
    </div>

    <!-- footer -->
    <footer>
        <div class="container">
            <small>Copyright &copy 2025 - Sistem Aspirasi Sekolah</small>
        </div>
    </footer>
</body>
</html>
