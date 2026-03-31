<?php
session_start();
if($_SESSION['status_login'] != true ){
    echo '<script>window.location= "login.php"</script>';
}

include 'db.php';

$pesan = '';
$tipe_pesan = '';

// Handle tambah input aspirasi
if(isset($_POST['tambah'])){
    $nis = (int)$_POST['nis'];
    $id_kategori = (int)$_POST['id_kategori'];
    $lokasi = htmlspecialchars($_POST['lokasi']);
    $ket = htmlspecialchars($_POST['ket']);
    
    if(empty($nis) || empty($id_kategori) || empty($lokasi) || empty($ket)){
        $pesan = 'Semua field harus diisi!';
        $tipe_pesan = 'error';
    } else {
        $stmt = $CON->prepare("INSERT INTO tb_input_aspirasi (nis, id_kategori, lokasi, ket) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $nis, $id_kategori, $lokasi, $ket);
        
        if($stmt->execute()){
            $pesan = 'Input aspirasi berhasil ditambahkan!';
            $tipe_pesan = 'success';
        } else {
            $pesan = 'Error: ' . $stmt->error;
            $tipe_pesan = 'error';
        }
        $stmt->close();
    }
}

// Handle edit input aspirasi
if(isset($_POST['edit'])){
    $id_pelaporan = (int)$_POST['id_pelaporan'];
    $nis = (int)$_POST['nis'];
    $id_kategori = (int)$_POST['id_kategori'];
    $lokasi = htmlspecialchars($_POST['lokasi']);
    $ket = htmlspecialchars($_POST['ket']);
    
    $stmt = $CON->prepare("UPDATE tb_input_aspirasi SET nis = ?, id_kategori = ?, lokasi = ?, ket = ? WHERE id_pelaporan = ?");
    $stmt->bind_param("iissi", $nis, $id_kategori, $lokasi, $ket, $id_pelaporan);
    
    if($stmt->execute()){
        $pesan = 'Input aspirasi berhasil diupdate!';
        $tipe_pesan = 'success';
    } else {
        $pesan = 'Error: ' . $stmt->error;
        $tipe_pesan = 'error';
    }
    $stmt->close();
}

// Handle hapus
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $CON->prepare("DELETE FROM tb_input_aspirasi WHERE id_pelaporan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_input_aspirasi.php');
}

// Get data untuk edit
$edit_data = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $stmt = $CON->prepare("SELECT * FROM tb_input_aspirasi WHERE id_pelaporan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_object();
    $stmt->close();
}

// Get semua data dengan JOIN ke siswa dan kategori
$data_query = mysqli_query($CON, "SELECT ia.*, s.kelas, k.ket_kategori FROM tb_input_aspirasi ia 
                                  LEFT JOIN tb_siswa s ON ia.nis = s.nis
                                  LEFT JOIN tb_kategori k ON ia.id_kategori = k.id_kategori
                                  ORDER BY ia.tanggal_input DESC");
if($data_query === false) {
    die('Query error: ' . mysqli_error($CON));
}
$total = mysqli_num_rows($data_query);

// Get kategori untuk dropdown
$kategori_query = mysqli_query($CON, "SELECT * FROM tb_kategori ORDER BY ket_kategori ASC");
$kategori_list = [];
while($k = mysqli_fetch_object($kategori_query)){
    $kategori_list[] = $k;
}

// Get siswa untuk dropdown
$siswa_query = mysqli_query($CON, "SELECT DISTINCT nis, kelas FROM tb_siswa ORDER BY nis ASC");
$siswa_list = [];
while($s = mysqli_fetch_object($siswa_query)){
    $siswa_list[] = $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Input Aspirasi - Admin</title>
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
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table tr th, .table tr td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 13px;
        }
        .table tr th {
            background-color: #2c3e50;
            color: white;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin-right: 3px;
        }
        .btn-edit { background-color: #f39c12; color: #fff; }
        .btn-delete { background-color: #e74c3c; color: #fff; }
        .btn-back { background-color: #95a5a6; color: #fff; }
        .btn-edit:hover { background-color: #e67e22; }
        .btn-delete:hover { background-color: #c0392b; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: 'Quicksand', sans-serif;
            box-sizing: border-box;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group button { 
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
        }
        .form-group button:hover { background-color: #229954; }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1><a href="dashboard.php">Aspirasi</a></h1>
            <ul>
                
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="aspirasi_report.php">📊 Laporan Aspirasi</a></li>
                <li><a href="admin_kategori.php">📂 Kategori</a></li>
                <li><a href="admin_siswa.php">👥 Siswa</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Content -->
    <div class="section">
        <div class="container">
            <h3>Kelola Input Aspirasi</h3>
            
            <?php if($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit -->
            <div class="form-container">
                <h4><?= isset($edit_data) ? 'Edit Input Aspirasi' : 'Tambah Input Aspirasi Baru' ?></h4>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>NIS Siswa:</label>
                            <select name="nis" required>
                                <option value="">Pilih Siswa</option>
                                <?php foreach($siswa_list as $s): ?>
                                    <option value="<?= $s->nis ?>" <?= (isset($edit_data) && $edit_data->nis == $s->nis) ? 'selected' : '' ?>>
                                        <?= $s->nis ?> - <?= $s->kelas ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Kategori:</label>
                            <select name="id_kategori" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($kategori_list as $k): ?>
                                    <option value="<?= $k->id_kategori ?>" <?= (isset($edit_data) && $edit_data->id_kategori == $k->id_kategori) ? 'selected' : '' ?>>
                                        <?= $k->ket_kategori ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi:</label>
                        <input type="text" name="lokasi" value="<?= isset($edit_data) ? htmlspecialchars($edit_data->lokasi) : '' ?>" placeholder="Contoh: Perpustakaan, Kantin" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan/Aspirasi:</label>
                        <textarea name="ket" required><?= isset($edit_data) ? htmlspecialchars($edit_data->ket) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <?php if(isset($edit_data)): ?>
                            <input type="hidden" name="id_pelaporan" value="<?= $edit_data->id_pelaporan ?>">
                            <button type="submit" name="edit">Update Input Aspirasi</button>
                            <a href="admin_input_aspirasi.php" class="btn btn-back">Batal</a>
                        <?php else: ?>
                            <button type="submit" name="tambah">Tambah Input Aspirasi</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="box">
                <h4>Daftar Input Aspirasi (<?= $total ?> data)</h4>
                <?php if($total > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="6%">No</th>
                                    <th width="8%">ID Lapor</th>
                                    <th width="8%">NIS</th>
                                    <th width="8%">Kelas</th>
                                    <th width="10%">Kategori</th>
                                    <th width="12%">ID Pelapor</th>
                                    <th width="12%">Lokasi</th>
                                    <th width="14%">Keterangan</th>
                                    <th width="12%">Tanggal</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while($d = mysqli_fetch_object($data_query)){
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $d->id_pelaporan ?></td>
                                        <td><?= htmlspecialchars($d->nis) ?></td>
                                        <td><?= htmlspecialchars($d->kelas ?? '-') ?></td>
                                        <td><?= htmlspecialchars($d->ket_kategori ?? '-') ?></td>
                                        <td><?= htmlspecialchars($d->id_pelapor ?? '-') ?></td>
                                        <td><?= htmlspecialchars($d->lokasi) ?></td>
                                        <td><?= substr(htmlspecialchars($d->ket), 0, 20) ?>...</td>
                                        <td><?= date('d/m/Y H:i', strtotime($d->tanggal_input)) ?></td>
                                        <td>
                                            <a href="admin_input_aspirasi.php?edit=<?= $d->id_pelaporan ?>" class="btn btn-edit">Edit</a>
                                            <a href="admin_input_aspirasi.php?delete=<?= $d->id_pelaporan ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-delete">Hapus</a>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Belum ada data input aspirasi</p>
                <?php endif; ?>
            </div>

            <a href="dashboard.php" class="btn btn-back">Kembali ke Dashboard</a>
        </div>
    </div>

    <!-- footer -->
    <footer>
        <div class="container">
            <small>Copyright &copy 2025 - E-Petugas</small>
        </div>
    </footer>
</body>
</html>
