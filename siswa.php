<?php
include 'db.php';

$pesan = '';
$tipe_pesan = '';

// Handle form submission
if(isset($_POST['submit'])){
    $nis = htmlspecialchars($_POST['nis']);
    $id_kategori = (int)$_POST['id_kategori'];
    $lokasi = htmlspecialchars($_POST['lokasi']);
    $ket = htmlspecialchars($_POST['ket']);
    
    // Validasi
    if(empty($nis) || empty($id_kategori) || empty($lokasi) || empty($ket)){
        $pesan = 'Semua field harus diisi!';
        $tipe_pesan = 'error';
    } else {
        // Validasi NIS ada di database dan ambil id_pelapor
        $check_nis = mysqli_query($CON, "SELECT id_pelapor FROM tb_siswa WHERE nis = '$nis'");
        if(mysqli_num_rows($check_nis) == 0){
            $pesan = 'NIS tidak ditemukan dalam sistem!';
            $tipe_pesan = 'error';
        } else {
            // Ambil id_pelapor dari siswa
            $siswa_data = mysqli_fetch_object($check_nis);
            $id_pelapor = $siswa_data->id_pelapor;
            
            // Validasi kategori ada di database
            $check_kategori = mysqli_query($CON, "SELECT id_kategori FROM tb_kategori WHERE id_kategori = $id_kategori");
            if(mysqli_num_rows($check_kategori) == 0){
                $pesan = 'Kategori tidak valid!';
                $tipe_pesan = 'error';
            } else {
                // Insert ke tb_input_aspirasi dengan id_pelapor
                $stmt = $CON->prepare("INSERT INTO tb_input_aspirasi (nis, id_kategori, lokasi, ket, id_pelapor) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sisss", $nis, $id_kategori, $lokasi, $ket, $id_pelapor);
                
                if($stmt->execute()){
                    $id_pelaporan = $stmt->insert_id;
                    $stmt->close();
                    
                    // Otomatis buat entry di tb_aspirasi dengan status "Menunggu" dan id_pelapor
                    $stmt_aspirasi = $CON->prepare("INSERT INTO tb_aspirasi (id_pelaporan, status, id_pelapor) VALUES (?, 'Menunggu', ?)");
                    $stmt_aspirasi->bind_param("is", $id_pelaporan, $id_pelapor);
                    
                    if($stmt_aspirasi->execute()){
                        $pesan = 'Aspirasi berhasil dikirim! Terima kasih.';
                        $tipe_pesan = 'success';
                        // Reset form
                        $_POST = array();
                    } else {
                        $pesan = 'Error: ' . $stmt_aspirasi->error;
                        $tipe_pesan = 'error';
                    }
                    $stmt_aspirasi->close();
                } else {
                    $pesan = 'Error: ' . $stmt->error;
                    $tipe_pesan = 'error';
                    $stmt->close();
                }
            }
        }
    }
}

// Query data NIS dari tb_siswa
$siswa_query = mysqli_query($CON, "SELECT nis, kelas FROM tb_siswa ORDER BY nis ASC");

// Query data Kategori dari tb_kategori
$kategori_query = mysqli_query($CON, "SELECT id_kategori, ket_kategori FROM tb_kategori ORDER BY ket_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kirim Aspirasi - Sistem Aspirasi Sekolah</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Quicksand', sans-serif; background: #f0f2f5; padding-top: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-box h2 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: bold; }
        .form-group input,
        .form-group select,
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Quicksand', sans-serif; box-sizing: border-box; }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { outline: none; border-color: #3498db; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
        .btn { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #2980b9; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #3498db; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .message { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistem Aspirasi Sekolah</h1>
    </div>
    
    <div class="container">
        <div class="form-box">
            <h2>Kirim Aspirasi</h2>
            
            <?php if(!empty($pesan)): ?>
                <div class="message <?= $tipe_pesan ?>">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nis">NIS (Nomor Induk Siswa)</label>
                    <select id="nis" name="nis" required>
                        <option value="">-- Pilih NIS Anda --</option>
                        <?php
                        while($s = mysqli_fetch_object($siswa_query)){
                            $selected = (isset($_POST['nis']) && $_POST['nis'] == $s->nis) ? 'selected' : '';
                            echo "<option value='{$s->nis}' {$selected}>{$s->nis} - Kelas {$s->kelas}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_kategori">Kategori Aspirasi</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        while($k = mysqli_fetch_object($kategori_query)){
                            $selected = (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $k->id_kategori) ? 'selected' : '';
                            echo "<option value='{$k->id_kategori}' {$selected}>{$k->ket_kategori}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="lokasi">Lokasi/Tempat</label>
                    <input type="text" id="lokasi" name="lokasi" placeholder="Contoh: Perpustakaan, Kantin, Kelas" value="<?= isset($_POST['lokasi']) ? $_POST['lokasi'] : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="ket">Aspirasi / Masukan</label>
                    <textarea id="ket" name="ket" required><?= isset($_POST['ket']) ? $_POST['ket'] : '' ?></textarea>
                </div>
                
                <button type="submit" name="submit" class="btn">Kirim Aspirasi</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">&larr; Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>
</body>
</html>
