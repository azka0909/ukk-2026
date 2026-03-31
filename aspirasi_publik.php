<?php
include 'db.php';

// Query tanpa filter
$data_query = mysqli_query($CON, "SELECT a.id_aspirasi, a.status, ia.lokasi, ia.ket, k.ket_kategori, ia.tanggal_input, a.feedback
                                  FROM tb_aspirasi a 
                                  JOIN tb_input_aspirasi ia ON a.id_pelaporan = ia.id_pelaporan
                                  JOIN tb_kategori k ON ia.id_kategori = k.id_kategori
                                  ORDER BY a.tanggal_dibuat DESC");

if($data_query === false) {
    die('Query error: ' . mysqli_error($CON));
}
$total = mysqli_num_rows($data_query);

// Get statistik aspirasi
$total_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi");
$total_all = mysqli_fetch_object($total_query)->total;

$selesai_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Selesai'");
$total_selesai = mysqli_fetch_object($selesai_query)->total;

$proses_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Proses'");
$total_proses = mysqli_fetch_object($proses_query)->total;

$menunggu_query = mysqli_query($CON, "SELECT COUNT(*) as total FROM tb_aspirasi WHERE status='Menunggu'");
$total_menunggu = mysqli_fetch_object($menunggu_query)->total;


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Aspirasi Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Quicksand', sans-serif; background-color: #f5f7fa; }
        
        .container { max-width: 1000px; margin: 0 auto; padding: 0 15px; }
        
        header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        header h1 {
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover { text-decoration: underline; }
        
        .section { padding: 30px 0; }
        
        h2 { 
            color: #2c3e50; 
            font-size: 24px; 
            font-weight: 600; 
            margin-bottom: 20px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            display: inline-block;
        }
        
        h3 { 
            color: #2c3e50; 
            margin-bottom: 15px; 
            font-size: 16px; 
            font-weight: 600; 
        }
        
        /* Stats Box */
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
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
        
        .stat-item:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
        }
        
        .stat-all { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .stat-selesai { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); }
        .stat-proses { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .stat-menunggu { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
        
        .stat-number { font-size: 28px; margin-bottom: 8px; }
        
        /* Filter Box */
        .filter-box {
            background: white;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid #3498db;
        }
        
        .filter-box strong { color: #2c3e50; font-size: 15px; display: block; margin-bottom: 15px; }
        .filter-box form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        .filter-box label { font-size: 12px; color: #666; display: block; margin-bottom: 5px; font-weight: 600; }
        .filter-box select, .filter-box input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 13px; 
            font-family: 'Quicksand', sans-serif; 
        }
        .filter-box select:focus, .filter-box input:focus { 
            outline: none; 
            border-color: #3498db; 
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); 
        }
        
        .button-group { 
            display: flex; 
            gap: 10px; 
            align-items: flex-end; 
        }
        
        .button-group button, .button-group a { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 13px; 
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .button-group button[type="submit"] { 
            background-color: #3498db; 
            color: white; 
        }
        .button-group button[type="submit"]:hover { 
            background-color: #2980b9; 
            box-shadow: 0 3px 8px rgba(52, 152, 219, 0.3); 
            transform: translateY(-1px); 
        }
        
        .button-group a { 
            background-color: #95a5a6; 
            color: white; 
        }
        .button-group a:hover { 
            background-color: #7f8c8d; 
        }
        
        /* Content Box */
        .box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .aspirasi-list {
            list-style: none;
        }
        
        .aspirasi-item {
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
            border-left: 5px solid #3498db;
        }
        
        .aspirasi-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .aspirasi-item.status-selesai { border-left-color: #27ae60; }
        .aspirasi-item.status-proses { border-left-color: #f39c12; }
        .aspirasi-item.status-menunggu { border-left-color: #e74c3c; }
        
        .aspirasi-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .aspirasi-id {
            color: #3498db;
            font-weight: bold;
            font-size: 14px;
        }
        
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
        
        .aspirasi-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #666;
        }
        
        .meta-item {
            display: flex;
            gap: 5px;
        }
        
        .meta-label { font-weight: 600; color: #2c3e50; }
        
        .aspirasi-content {
            margin-bottom: 12px;
        }
        
        .aspirasi-content h4 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .aspirasi-content p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .kategori-badge {
            display: inline-block;
            background: #ecf0f1;
            color: #2c3e50;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .aspirasi-feedback {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-top: 12px;
            border-left: 3px solid #3498db;
        }
        
        .aspirasi-feedback strong {
            color: #2c3e50;
            font-size: 12px;
        }
        
        .aspirasi-feedback p {
            color: #555;
            font-size: 13px;
            margin: 5px 0 0 0;
        }
        
        .no-data { 
            text-align: center; 
            padding: 40px; 
            color: #95a5a6; 
            font-style: italic; 
        }
        
        footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .stats-box { grid-template-columns: repeat(2, 1fr); }
            .aspirasi-header { flex-direction: column; }
            .filter-box form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📋 Daftar Aspirasi Siswa</h1>
            <p>Lihat status aspirasi yang telah diajukan oleh siswa-siswa di sekolah</p>
        </div>
    </header>

    <div class="section">
        <div class="container">
            <a href="index.php" class="back-link">← Kembali ke Beranda</a>

            <!-- Statistik -->
            <div class="stats-box">
                <div class="stat-item stat-all">
                    <div class="stat-number"><?= $total_all ?></div>
                    <div>Total Aspirasi</div>
                </div>
                <div class="stat-item stat-selesai">
                    <div class="stat-number"><?= $total_selesai ?></div>
                    <div>✓ Selesai</div>
                </div>
                <div class="stat-item stat-proses">
                    <div class="stat-number"><?= $total_proses ?></div>
                    <div>⚙ Proses</div>
                </div>
                <div class="stat-item stat-menunggu">
                    <div class="stat-number"><?= $total_menunggu ?></div>
                    <div>⏳ Menunggu</div>
                </div>
            </div>

            <!-- Filter -->
            <div class="filter-box">
                <form method="GET" action="aspirasi_publik.php">
                    <div>

                <?php if($total > 0): ?>
                    <ul class="aspirasi-list">
                        <?php
                        $no = 1;
                        while($d = mysqli_fetch_object($data_query)){
                            $status_class = 'status-' . strtolower($d->status);
                        ?>
                            <li class="aspirasi-item <?= $status_class ?>">
                                <div class="aspirasi-header">
                                    <span class="aspirasi-id">Aspirasi #<?= $d->id_aspirasi ?></span>
                                    <span class="badge badge-<?= strtolower($d->status) ?>"><?= $d->status ?></span>
                                </div>
                                
                                <div class="aspirasi-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Kategori:</span>
                                        <span class="kategori-badge"><?= htmlspecialchars($d->ket_kategori) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Lokasi:</span>
                                        <span><?= htmlspecialchars($d->lokasi) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Tanggal:</span>
                                        <span><?= date('d/m/Y H:i', strtotime($d->tanggal_input)) ?></span>
                                    </div>
                                </div>
                                
                                <div class="aspirasi-content">
                                    <h4>Deskripsi Aspirasi:</h4>
                                    <p><?= htmlspecialchars($d->ket) ?></p>
                                </div>
                                
                                <?php if(!empty($d->feedback)): ?>
                                    <div class="aspirasi-feedback">
                                        <strong>💬 Feedback Dari Admin:</strong>
                                        <p><?= htmlspecialchars($d->feedback) ?></p>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                <?php else: ?>
                    <div class="no-data">
                        <p>📭 Tidak ada aspirasi dengan filter yang dipilih</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <small>Copyright &copy 2025 - Sistem Aspirasi Sekolah</small>
    </footer>
</body>
</html>
