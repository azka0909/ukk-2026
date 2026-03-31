<?php
include 'db.php';

$pesan = '';
$tipe_pesan = '';

// Handle form submission
if(isset($_POST['submit'])){
    $nis = (int)$_POST['nis'];
    $id_kategori = (int)$_POST['id_kategori'];
    $lokasi = htmlspecialchars($_POST['lokasi']);
    $ket = htmlspecialchars($_POST['ket']);
    
    // Validasi
    if(empty($nis) || empty($id_kategori) || empty($lokasi) || empty($ket)){
        $pesan = 'Semua field harus diisi!';
        $tipe_pesan = 'error';
    } else {
        // Validasi NIS ada di database dan ambil id_pelapor
        $check_nis = mysqli_query($CON, "SELECT id_pelapor FROM tb_siswa WHERE nis = " . (int)$nis);
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
                if($stmt === false) {
                    $pesan = 'Database Error: ' . $CON->error;
                    $tipe_pesan = 'error';
                } else {
                    $stmt->bind_param("iisss", $nis, $id_kategori, $lokasi, $ket, $id_pelapor);
                    
                    if($stmt->execute()){
                        $id_pelaporan = $stmt->insert_id;
                        $stmt->close();
                        
                        // Otomatis buat entry di tb_aspirasi dengan status "Menunggu"
                        $status = 'Menunggu';
                        $stmt_aspirasi = $CON->prepare("INSERT INTO tb_aspirasi (id_pelaporan, status) VALUES (?, ?)");
                        if($stmt_aspirasi === false) {
                            $pesan = 'Database Error di tb_aspirasi: ' . $CON->error;
                            $tipe_pesan = 'error';
                        } else {
                            $stmt_aspirasi->bind_param("is", $id_pelaporan, $status);
                            
                            if($stmt_aspirasi->execute()){
                                $pesan = 'Aspirasi berhasil dikirim! ID Pelapor: ' . htmlspecialchars($id_pelapor) . ' (Simpan untuk melacak status aspirasi Anda)';
                                $tipe_pesan = 'success';
                                // Reset form
                                $_POST = array();
                            } else {
                                $pesan = 'Error saat menyimpan status aspirasi: ' . $stmt_aspirasi->error;
                                $tipe_pesan = 'error';
                            }
                            $stmt_aspirasi->close();
                        }
                    } else {
                        $pesan = 'Error: ' . $stmt->error;
                        $tipe_pesan = 'error';
                        $stmt->close();
                    }
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
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Quicksand', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .form-box h2 { color: #2c3e50; text-align: center; margin-bottom: 10px; font-size: 28px; }
        .form-box p { text-align: center; color: #7f8c8d; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 600; font-size: 14px; }
        .form-group input,
        .form-group select,
        .form-group textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e0e0e0; 
            border-radius: 6px; 
            font-family: 'Quicksand', sans-serif; 
            box-sizing: border-box; 
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { 
            outline: none; 
            border-color: #3498db; 
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); 
        }
        .btn { 
            width: 100%; 
            padding: 14px; 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            cursor: pointer; 
            font-weight: bold; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        .btn:hover { 
            background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
            box-shadow: 0 6px 16px rgba(52, 152, 219, 0.3);
            transform: translateY(-2px);
        }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #3498db; text-decoration: none; font-weight: 600; font-size: 14px; }
        .back-link a:hover { text-decoration: underline; }
        .message { padding: 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid; font-size: 14px; }
        .message.success { 
            background: #d4edda; 
            color: #155724; 
            border-left-color: #28a745;
        }
        .message.error { 
            background: #f8d7da; 
            color: #721c24; 
            border-left-color: #dc3545;
        }
        .header { 
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 14px; }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sharing Aspirasi</h1>
        <p>Suarakan aspirasi dan masukan Anda demi sekolah menjadi sekolah yanglebih baik</p>
    </div>
    
    <div class="container">
        <div class="form-box">
            
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
                    <input type="text" id="lokasi" name="lokasi" placeholder="Contoh: Perpustakaan, Kantin, Kelas, Toiletl" value="<?= isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="ket">Deskripsi Aspirasi / Masukan</label>
                    <textarea id="ket" name="ket" placeholder="Jelaskan aspirasi atau masukan Anda secara detail..." required><?= isset($_POST['ket']) ? htmlspecialchars($_POST['ket']) : '' ?></textarea>
                </div>
                
                <button type="submit" name="submit" class="btn">✓ Kirim Aspirasi</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">&larr; Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>

    <footer>
        <small>Copyright &copy 2025 - Sistem Aspirasi Sekolah</small>
    </footer>
</body>
</html>
