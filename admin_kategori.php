<?php
session_start();
if($_SESSION['status_login'] != true ){
    echo '<script>window.location= "login.php"</script>';
}

include 'db.php';

$pesan = '';
$tipe_pesan = '';

// Handle tambah kategori
if(isset($_POST['tambah'])){
    $ket_kategori = htmlspecialchars($_POST['ket_kategori']);
    
    if(empty($ket_kategori)){
        $pesan = 'Keterangan kategori harus diisi!';
        $tipe_pesan = 'error';
    } else {
        $stmt = $CON->prepare("INSERT INTO tb_kategori (ket_kategori) VALUES (?)");
        $stmt->bind_param("s", $ket_kategori);
        
        if($stmt->execute()){
            $pesan = 'Kategori berhasil ditambahkan!';
            $tipe_pesan = 'success';
        } else {
            $pesan = 'Error: ' . $stmt->error;
            $tipe_pesan = 'error';
        }
        $stmt->close();
    }
}

// Handle edit kategori
if(isset($_POST['edit'])){
    $id_kategori = (int)$_POST['id_kategori'];
    $ket_kategori = htmlspecialchars($_POST['ket_kategori']);
    
    if(empty($ket_kategori)){
        $pesan = 'Keterangan kategori harus diisi!';
        $tipe_pesan = 'error';
    } else {
        $stmt = $CON->prepare("UPDATE tb_kategori SET ket_kategori = ? WHERE id_kategori = ?");
        $stmt->bind_param("si", $ket_kategori, $id_kategori);
        
        if($stmt->execute()){
            $pesan = 'Kategori berhasil diupdate!';
            $tipe_pesan = 'success';
        } else {
            $pesan = 'Error: ' . $stmt->error;
            $tipe_pesan = 'error';
        }
        $stmt->close();
    }
}

// Handle hapus kategori
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $CON->prepare("DELETE FROM tb_kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_kategori.php');
}

// Get data untuk edit
$edit_data = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $stmt = $CON->prepare("SELECT * FROM tb_kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_object();
    $stmt->close();
}

// Get semua data kategori
$data_query = mysqli_query($CON, "SELECT * FROM tb_kategori ORDER BY id_kategori ASC");
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
    <title>Kelola Kategori - Admin</title>
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
        
        /* Form */
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
        
        /* Button Group */
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-start;
        }
        
        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .btn-edit { 
            background-color: #f39c12; 
            color: #fff;
        }
        .btn-edit:hover { 
            background-color: #e67e22;
            box-shadow: 0 4px 10px rgba(230, 126, 34, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-delete { 
            background-color: #e74c3c; 
            color: #fff;
        }
        .btn-delete:hover { 
            background-color: #c0392b;
            box-shadow: 0 4px 10px rgba(192, 57, 43, 0.4);
            transform: translateY(-2px);
        }
        
        /* No Data */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
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
                <li><a href="aspirasi_report.php">Laporan Aspirasi</a>
                <li><a href="admin_kategori.php">Kategori</a></li>
                <li><a href="admin_siswa.php">Siswa</a></li>                                   
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Content -->
    <div class="section">
        <div class="container">
            <h3>Kelola Kategori</h3>
            
            <?php if($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit -->
            <div class="form-container">
                <h4><?= isset($edit_data) ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h4>
                <form method="POST">
                    <div class="form-group">
                        <label>Keterangan Kategori:</label>
                        <input type="text" name="ket_kategori" value="<?= isset($edit_data) ? htmlspecialchars($edit_data->ket_kategori) : '' ?>" placeholder="Masukkan nama kategori..." required>
                    </div>
                    
                    <div class="form-actions">
                        <?php if(isset($edit_data)): ?>
                            <input type="hidden" name="id_kategori" value="<?= $edit_data->id_kategori ?>">
                            <button type="submit" name="edit">💾 Update Kategori</button>
                            <a href="admin_kategori.php" class="btn btn-back">❌ Batal</a>
                        <?php else: ?>
                            <button type="submit" name="tambah">➕ Tambah Kategori</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="box">
                <h4>📑 Daftar Kategori (Total: <?= $total ?> Kategori)</h4>
                <?php if($total > 0): ?>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="8%">No</th>
                                    <th width="75%">Keterangan</th>
                                    <th width="17%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while($d = mysqli_fetch_object($data_query)){
                                ?>
                                    <tr>
                                        <td><strong><?= $no++ ?></strong></td>
                                        <td><?= htmlspecialchars($d->ket_kategori) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin_kategori.php?edit=<?= $d->id_kategori ?>" class="btn btn-edit">✎ Edit</a>
                                                <a href="admin_kategori.php?delete=<?= $d->id_kategori ?>" onclick="return confirm('Yakin hapus kategori ini?')" class="btn btn-delete">🗑 Hapus</a>
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
                        <p>📭 Belum ada kategori. Silakan tambah kategori baru.</p>
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
