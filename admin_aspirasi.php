<?php
session_start();
if($_SESSION['status_login'] != true ){
    echo '<script>window.location= "login.php"</script>';
}

include 'db.php';

$pesan = '';
$tipe_pesan = '';

// Handle tambah aspirasi
if(isset($_POST['tambah'])){
    $id_pelaporan = (int)$_POST['id_pelaporan'];
    $status = htmlspecialchars($_POST['status']);
    $feedback = htmlspecialchars($_POST['feedback']);
    
    if(empty($id_pelaporan) || empty($status)){
        $pesan = 'ID Pelaporan dan Status harus diisi!';
        $tipe_pesan = 'error';
    } else {
        // Ambil id_pelapor dari siswa berdasarkan id_pelaporan
        $stmt = $CON->prepare("SELECT ia.nis, s.id_pelapor FROM tb_input_aspirasi ia LEFT JOIN tb_siswa s ON ia.nis = s.nis WHERE ia.id_pelaporan = ?");
        $stmt->bind_param("i", $id_pelaporan);
        $stmt->execute();
        $pelaporan_data = $stmt->get_result()->fetch_object();
        $stmt->close();
        
        $id_pelapor = $pelaporan_data ? $pelaporan_data->id_pelapor : NULL;
        
        $stmt = $CON->prepare("INSERT INTO tb_aspirasi (id_pelaporan, status, id_pelapor, feedback) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_pelaporan, $status, $id_pelapor, $feedback);
        
        if($stmt->execute()){
            $pesan = 'Aspirasi berhasil ditambahkan!';
            $tipe_pesan = 'success';
        } else {
            $pesan = 'Error: ' . $stmt->error;
            $tipe_pesan = 'error';
        }
        $stmt->close();
    }
}

// Handle edit aspirasi
if(isset($_POST['edit'])){
    $id_aspirasi = (int)$_POST['id_aspirasi'];
    $status = htmlspecialchars($_POST['status']);
    $feedback = htmlspecialchars($_POST['feedback']);
    
    $stmt = $CON->prepare("UPDATE tb_aspirasi SET status = ?, feedback = ? WHERE id_aspirasi = ?");
    $stmt->bind_param("ssi", $status, $feedback, $id_aspirasi);
    
    if($stmt->execute()){
        $pesan = 'Aspirasi berhasil diupdate!';
        $tipe_pesan = 'success';
    } else {
        $pesan = 'Error: ' . $stmt->error;
        $tipe_pesan = 'error';
    }
    $stmt->close();
}

// Get data untuk edit dengan informasi lengkap
$edit_data_full = null;
if(isset($edit_data)){
    // Ambil data siswa terkait untuk auto-fill ID PLP
    $stmt = $CON->prepare("SELECT ia.nis, s.id_pelapor FROM tb_input_aspirasi ia LEFT JOIN tb_siswa s ON ia.nis = s.nis WHERE ia.id_pelaporan = ?");
    $stmt->bind_param("i", $edit_data->id_pelaporan);
    $stmt->execute();
    $edit_data_full = $stmt->get_result()->fetch_object();
    $stmt->close();
}

// Handle hapus
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $CON->prepare("DELETE FROM tb_aspirasi WHERE id_aspirasi = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_aspirasi.php');
}

// Get data untuk edit
$edit_data = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $stmt = $CON->prepare("SELECT * FROM tb_aspirasi WHERE id_aspirasi = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_object();
    $stmt->close();
}

// Get semua data aspirasi dengan JOIN ke input_aspirasi
$data_query = mysqli_query($CON, "SELECT a.*, ia.nis, ia.lokasi, ia.ket, ia.id_pelapor as id_pelapor_input, ia.tanggal_input, s.kelas, s.id_pelapor as id_plp_siswa
                                  FROM tb_aspirasi a 
                                  JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
                                  LEFT JOIN tb_siswa s ON ia.nis = s.nis
                                  ORDER BY a.tanggal_dibuat DESC");
if($data_query === false) {
    die('Query error: ' . mysqli_error($CON));
}
$total = mysqli_num_rows($data_query);

// Get input aspirasi untuk dropdown
$input_query = mysqli_query($CON, "SELECT DISTINCT ia.id_pelaporan, ia.nis, ia.lokasi, ia.ket, s.kelas 
                                   FROM tb_input_aspirasi ia 
                                   LEFT JOIN tb_siswa s ON ia.nis = s.nis
                                   ORDER BY ia.id_pelaporan DESC");
$input_list = [];
while($i = mysqli_fetch_object($input_query)){
    $input_list[] = $i;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Aspirasi - Admin</title>
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
        
        .form-row { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px;
            margin-bottom: 20px;
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
        
        .table td[data-wrap="yes"] {
            white-space: normal;
            word-break: break-word;
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
            .form-row {
                grid-template-columns: 1fr;
            }
            
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Content -->
    <div class="section">
        <div class="container">
            <h3>Kelola Aspirasi</h3>
            
            <?php if($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit -->
            <div class="form-container">
                <h4><?= isset($edit_data) ? 'Edit Aspirasi' : ' ' ?></h4>
                <form method="POST">
                    <?php if(!isset($edit_data)): ?>
                    <div class="form-group">
                        <label>Pilih Input Aspirasi:</label>
                        <select name="id_pelaporan" required>
                            <option value="">-- Pilih Aspirasi Siswa --</option>
                            <?php foreach($input_list as $i): ?>
                                <option value="<?= $i->id_pelaporan ?>">
                                 ID: <?= $i->id_pelaporan ?> | NIS: <?= $i->nis ?> (<?= $i->kelas ?>) - <?= substr($i->ket, 0, 35) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Status:</label>
                            <select name="status" required>
                                <option value="Menunggu" <?= (isset($edit_data) && $edit_data->status == 'Menunggu') ? 'selected' : '' ?>>Menunggu</option>
                                <option value="Proses" <?= (isset($edit_data) && $edit_data->status == 'Proses') ? 'selected' : '' ?>>Proses</option>
                                <option value="Selesai" <?= (isset($edit_data) && $edit_data->status == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>ID Pelapor (Auto dari Siswa):</label>
                            <input type="text" value="<?= isset($edit_data_full) && $edit_data_full->id_pelapor ? htmlspecialchars($edit_data_full->id_pelapor) : '-' ?>" placeholder="Otomatis dari siswa" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Feedback/Keterangan:</label>
                        <textarea name="feedback" placeholder="Berikan keterangan atau feedback untuk aspirasi ini..."><?= isset($edit_data) ? htmlspecialchars($edit_data->feedback) : '' ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <?php if(isset($edit_data)): ?>
                            <input type="hidden" name="id_aspirasi" value="<?= $edit_data->id_aspirasi ?>">
                            <button type="submit" name="edit">💾 Update Aspirasi</button>
                            <a href="admin_aspirasi.php" class="btn btn-back">❌ Batal</a>
                        <?php else: ?>
                            <button type="submit" name="tambah">➕ Ubah Aspirasi</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="box">
                <h4>📋 Daftar Aspirasi Siswa (Total: <?= $total ?> Data)</h4>
                <?php if($total > 0): ?>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="8%">NIS</th>
                                    <th width="6%">Kls</th>
                                    <th width="9%">Status</th>
                                    <th width="14%">Aspirasi</th>
                                    <th width="12%">ID PLP</th>
                                    <th width="11%">Lokasi</th>
                                    <th width="13%">Feedback</th>
                                    <th width="12%">Tgl Input</th>
                                    <th width="11%">Aksi</th>
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
                                        <td><strong><?= htmlspecialchars($d->nis) ?></strong></td>
                                        <td><?= htmlspecialchars($d->kelas ?? '-') ?></td>
                                        <td><span class="badge <?= $badge_class ?>"><?= $d->status ?></span></td>
                                        <td data-wrap="yes"><?= htmlspecialchars($d->ket) ?></td>
                                        <td><code><strong><?= htmlspecialchars($d->id_plp_siswa ?? '-') ?></strong></code></td>
                                        <td><?= htmlspecialchars($d->lokasi ?? '-') ?></td>
                                        <td data-wrap="yes"><?= htmlspecialchars($d->feedback ?? '-') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($d->tanggal_input)) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin_aspirasi.php?edit=<?= $d->id_aspirasi ?>" class="btn btn-edit">✎ Edit</a>
                                                <a href="admin_aspirasi.php?delete=<?= $d->id_aspirasi ?>" onclick="return confirm('Yakin hapus aspirasi ini?')" class="btn btn-delete">🗑 Hapus</a>
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
                        <p>📭 Belum ada data aspirasi. Silakan tambah dari halaman Input Aspirasi.</p>
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
