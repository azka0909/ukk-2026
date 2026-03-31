<?php
session_start();
if($_SESSION['status_login'] != true ){
    echo '<script>window.location= "login.php"</script>';
}

include 'db.php';

$pesan = '';
$tipe_pesan = '';

// Function untuk generate ID Pelapor
function generateIdPelapor($nis) {
    $random = rand(1000, 9999);
    return "PLP-" . $nis . "-" . $random;
}

// Handle tambah siswa
if(isset($_POST['tambah'])){
    $nis = (int)$_POST['nis'];
    $kelas = htmlspecialchars($_POST['kelas']);
    $id_pelapor = htmlspecialchars($_POST['id_pelapor']);
    
    if(empty($nis) || empty($kelas)){
        $pesan = 'NIS dan Kelas harus diisi!';
        $tipe_pesan = 'error';
    } else {
        // Auto-generate ID Pelapor jika kosong
        if(empty($id_pelapor)){
            $id_pelapor = generateIdPelapor($nis);
        }
        
        $stmt = $CON->prepare("INSERT INTO tb_siswa (nis, kelas, id_pelapor) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $nis, $kelas, $id_pelapor);
        
        if($stmt->execute()){
            $pesan = 'Siswa berhasil ditambahkan! ID Pelapor: ' . $id_pelapor;
            $tipe_pesan = 'success';
        } else {
            $pesan = 'Error: ' . $stmt->error;
            $tipe_pesan = 'error';
        }
        $stmt->close();
    }
}

// Handle edit siswa
if(isset($_POST['edit'])){
    $nis = (int)$_POST['nis'];
    $kelas = htmlspecialchars($_POST['kelas']);
    $id_pelapor = htmlspecialchars($_POST['id_pelapor']);
    $old_nis = (int)$_POST['old_nis'];
    
    $stmt = $CON->prepare("UPDATE tb_siswa SET kelas = ?, id_pelapor = ? WHERE nis = ?");
    $stmt->bind_param("ssi", $kelas, $id_pelapor, $old_nis);
    
    if($stmt->execute()){
        $pesan = 'Siswa berhasil diupdate!';
        $tipe_pesan = 'success';
    } else {
        $pesan = 'Error: ' . $stmt->error;
        $tipe_pesan = 'error';
    }
    $stmt->close();
}

// Handle hapus siswa
if(isset($_GET['delete'])){
    $nis = (int)$_GET['delete'];
    $stmt = $CON->prepare("DELETE FROM tb_siswa WHERE nis = ?");
    $stmt->bind_param("i", $nis);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_siswa.php');
}

// Get data untuk edit
$edit_data = null;
if(isset($_GET['edit'])){
    $nis = (int)$_GET['edit'];
    $stmt = $CON->prepare("SELECT * FROM tb_siswa WHERE nis = ?");
    $stmt->bind_param("i", $nis);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_object();
    $stmt->close();
}

// Get semua data siswa
$data_query = mysqli_query($CON, "SELECT s.nis, s.kelas, s.id_pelapor
                                  FROM tb_siswa s
                                  ORDER BY s.nis ASC");

// Auto-fill ID Pelapor untuk siswa yang belum punya
$fix_query = mysqli_query($CON, "SELECT nis FROM tb_siswa WHERE id_pelapor IS NULL OR id_pelapor = ''");
if($fix_query && mysqli_num_rows($fix_query) > 0){
    while($fix_data = mysqli_fetch_object($fix_query)){
        $new_id_pelapor = generateIdPelapor($fix_data->nis);
        $update_stmt = $CON->prepare("UPDATE tb_siswa SET id_pelapor = ? WHERE nis = ?");
        $update_stmt->bind_param("si", $new_id_pelapor, $fix_data->nis);
        $update_stmt->execute();
        $update_stmt->close();
    }
    // Re-query data setelah update
    $data_query = mysqli_query($CON, "SELECT s.nis, s.kelas, s.id_pelapor
                                      FROM tb_siswa s
                                      ORDER BY s.nis ASC");
}

if($data_query === false) {
    die('Query error: ' . mysqli_error($CON));
}
$total = mysqli_num_rows($data_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Siswa - Admin</title>
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
        
        h3 { 
            color: #2c3e50; 
            margin-bottom: 25px; 
            font-size: 24px; 
            font-weight: 600;
            border-bottom: 3px solid #3498db;
            padding-bottom: 12px;
            display: inline-block;
        }
        
        h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 5px solid;
        }
        .alert-success { 
            background-color: #d4edda; 
            color: #155724; 
            border-left-color: #28a745;
        }
        .alert-error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border-left-color: #dc3545;
        }
        
        /* Form Container */
        .form-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid #3498db;
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Quicksand', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .form-group textarea { 
            resize: vertical; 
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        
        .form-group button { 
            background-color: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group button:hover { 
            background-color: #229954;
            box-shadow: 0 4px 12px rgba(34, 153, 84, 0.3);
            transform: translateY(-2px);
        }
        
        /* Tabel */
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
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }
        
        /* Button dalam tabel */
        .btn-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-edit { 
            background-color: #f39c12; 
            color: #fff;
        }
        .btn-edit:hover { 
            background-color: #e67e22;
            box-shadow: 0 3px 8px rgba(230, 126, 34, 0.3);
            transform: translateY(-1px);
        }
        
        .btn-delete { 
            background-color: #e74c3c; 
            color: #fff;
        }
        .btn-delete:hover { 
            background-color: #c0392b;
            box-shadow: 0 3px 8px rgba(192, 57, 43, 0.3);
            transform: translateY(-1px);
        }
        
        .btn-back { 
            background-color: #95a5a6; 
            color: #fff;
            display: inline-block;
            margin-top: 15px;
        }
        .btn-back:hover { 
            background-color: #7f8c8d;
            box-shadow: 0 3px 8px rgba(127, 140, 141, 0.3);
            transform: translateY(-1px);
        }
        
        /* Badge */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-menunggu { 
            background-color: #e74c3c; 
            color: white;
        }
        .badge-proses { 
            background-color: #f39c12; 
            color: white;
        }
        .badge-selesai { 
            background-color: #27ae60; 
            color: white;
        }
        
        /* No Data */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
        }
        
        code {
            background-color: #f0f2f5;
            padding: 3px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #2c3e50;
        }
        
        @media (max-width: 1200px) {
            .table {
                font-size: 12px;
            }
            
            .table th, .table td {
                padding: 10px;
            }
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
            <h3>Kelola Siswa</h3>
            
            <?php if($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit -->
            <div class="form-container">
                <h4><?= isset($edit_data) ? 'Edit Siswa' : 'Tambah Siswa Baru' ?></h4>
                <form method="POST">
                    <div class="form-group">
                        <label>NIS:</label>
                        <input type="number" name="nis" value="<?= isset($edit_data) ? $edit_data->nis : '' ?>" required <?= isset($edit_data) ? 'readonly' : '' ?>>
                    </div>
                    
                    <div class="form-group">
                        <label>Kelas:</label>
                        <input type="text" name="kelas" value="<?= isset($edit_data) ? htmlspecialchars($edit_data->kelas) : '' ?>" placeholder="Contoh: X-TKJ-1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Pelapor (otomatis terisi jika kosong):</label>
                        <input type="text" name="id_pelapor" value="<?= isset($edit_data) ? htmlspecialchars($edit_data->id_pelapor) : '' ?>" placeholder="Otomatis: PLP-NIS-XXXX">
                    </div>
                    
                    <div class="form-group">
                        <?php if(isset($edit_data)): ?>
                            <input type="hidden" name="old_nis" value="<?= $edit_data->nis ?>">
                            <button type="submit" name="edit">Update Siswa</button>
                            <a href="admin_siswa.php" class="btn btn-back">Batal</a>
                        <?php else: ?>
                            <button type="submit" name="tambah">Tambah Siswa</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="box">
                <h4>👥 Daftar Siswa (Total: <?= $total ?> Siswa)</h4>
                <?php if($total > 0): ?>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="15%">NIS</th>
                                    <th width="18%">Kelas</th>
                                    <th width="40%">ID Pelapor</th>
                                    <th width="27%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while($d = mysqli_fetch_object($data_query)){
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($d->nis) ?></strong></td>
                                        <td><?= htmlspecialchars($d->kelas) ?></td>
                                        <td><code><?= htmlspecialchars($d->id_pelapor ?? '-') ?></code></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin_siswa.php?edit=<?= $d->nis ?>" class="btn btn-edit">✎ Edit</a>
                                                <a href="admin_siswa.php?delete=<?= $d->nis ?>" onclick="return confirm('Yakin hapus siswa ini?')" class="btn btn-delete">🗑 Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>📭 Belum ada siswa terdaftar.</p>
                    </div>
                <?php endif; ?>
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
