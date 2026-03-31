<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Aspirasi Sekolah</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .choice-container { text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 30px; }
        .btn { display: inline-block; width: 200px; padding: 15px; margin: 10px; text-decoration: none; color: white; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-siswa { background: #3498db; }
        .btn-siswa:hover { background: #2980b9; }
        .btn-admin { background: #2c3e50; }
        .btn-admin:hover { background: #1a252f; }
        .btn-lihat { background: #27ae60; }
        .btn-lihat:hover { background: #229954; }
    </style>
</head>
<body>
    <div class="choice-container">
        <h1>Selamat Datang di Portal Aspirasi</h1>
        <p>Silakan pilih akses anda:</p>
        <a href="input_aspirasi.php" class="btn btn-siswa">Siswa (Kirim Aspirasi)</a>
        <a href="aspirasi_publik.php" class="btn btn-lihat">👁 Lihat Aspirasi</a>
        <a href="login.php" class="btn btn-admin">Admin (Kelola Data)</a>
    </div>
</body>
</html>