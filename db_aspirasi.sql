SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_aspirasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id_admin` int(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', 202);

-- --------------------------------------------------------

--
-- Table structure for table `tb_aspirasi`
--

CREATE TABLE `tb_aspirasi` (
  `id_aspirasi` int(11) NOT NULL COMMENT 'Primary Key untuk setiap aspirasi',
  `id_pelaporan` int(11) NOT NULL COMMENT 'Foreign Key ke tb_input_aspirasi',
  `status` enum('Menunggu','Proses','Selesai') NOT NULL DEFAULT 'Menunggu' COMMENT 'Status aspirasi',
  `id_pelapor` varchar(50) DEFAULT NULL COMMENT 'ID Pelapor (generated)',
  `feedback` varchar(500) DEFAULT '' COMMENT 'Feedback dari admin',
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_aspirasi`
--

INSERT INTO `tb_aspirasi` (`id_aspirasi`, `id_pelaporan`, `status`, `id_pelapor`, `feedback`, `tanggal_dibuat`) VALUES
(1, 2, 'Selesai', '', 'Sudah ditambah 2 tong sampah', '2026-03-20 02:31:34'),
(2, 3, 'Proses', '', 'Sedang perbaikan', '2026-03-20 02:31:34'),
(3, 4, 'Menunggu', '', 'Akan dibahas di rapat', '2026-03-20 02:31:34'),
(4, 5, 'Selesai', '', 'Sudah perbaiki jadwal', '2026-03-20 02:31:34'),
(5, 6, 'Selesai', '', 'Pompa air sudah diganti', '2026-03-20 02:31:34'),
(6, 7, 'Proses', '', 'Menunggu sparepart', '2026-03-20 02:31:34'),
(7, 8, 'Menunggu', '', 'Perbaikan minggu depan', '2026-03-20 02:31:34'),
(8, 9, 'Selesai', '', 'Sudah dibersihkan', '2026-03-20 02:31:34'),
(10, 23, 'Menunggu', 'PLP-1004-4107', '', '2026-03-20 02:32:22'),
(11, 24, 'Menunggu', 'PLP-1007-5257', '', '2026-03-20 02:32:32'),
(12, 25, 'Menunggu', 'PLP-10010-3571', '', '2026-03-20 02:35:53');

-- --------------------------------------------------------

--
-- Table structure for table `tb_input_aspirasi`
--

CREATE TABLE `tb_input_aspirasi` (
  `id_pelaporan` int(5) NOT NULL,
  `nis` varchar(10) NOT NULL,
  `id_kategori` int(5) NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `ket` text NOT NULL,
  `id_pelapor` varchar(50) DEFAULT NULL,
  `tanggal_input` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_input_aspirasi`
--

INSERT INTO `tb_input_aspirasi` (`id_pelaporan`, `nis`, `id_kategori`, `lokasi`, `ket`, `tanggal_input`) VALUES
(2, 1003, 1, 'Perpustakaan', 'Buku referensi kurang', '2026-03-05 08:30:00'),
(3, 1004, 4, 'Parkiran', 'CCTV mati', '2026-03-06 14:15:00'),
(4, 1005, 3, 'Kelas', 'Jadwal terlalu padat', '2026-03-07 09:45:00'),
(5, 1006, 2, 'Toilet', 'Air sering mati', '2026-03-08 11:20:00'),
(6, 1007, 1, 'Aula', 'Sound system rusak', '2026-03-09 10:00:00'),
(7, 1008, 5, 'Lapangan', 'Tiang basket miring', '2026-03-09 15:30:00'),
(8, 1009, 2, 'Koridor', 'Lantai licin', '2026-03-10 10:15:00'),
(9, 1010, 1, 'UKS', 'Obat P3K habis', '2026-03-10 13:45:00'),
(12, 1004, 3, 'kelas', 'kerenn', '2026-03-11 10:27:29'),
(23, 1004, 3, 'Kantin', 'sadgsg', '2026-03-20 09:32:22'),
(24, 1007, 3, 'Kantin', 'sdasdasd', '2026-03-20 09:32:32'),
(25, 10010, 3, 'Kantin', 'belajar di kantin\r\n', '2026-03-20 09:35:53');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `id_kategori` int(5) NOT NULL AUTO_INCREMENT,
  `ket_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`id_kategori`, `ket_kategori`) VALUES
(0, 'Lain Lain'),
(1, 'Fasilitas'),
(2, 'Kebersihan'),
(3, 'Kurikulum'),
(4, 'Keamanan');

-- --------------------------------------------------------

--
-- Table structure for table `tb_siswa`
--

CREATE TABLE `tb_siswa` (
  `nis` int(10) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `id_pelapor` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_siswa`
--

INSERT INTO `tb_siswa` (`nis`, `kelas`, `id_pelapor`) VALUES
(1003, 'X-TKJ-1', 'PLP-1003-0301'),
(1004, 'XI-RPL-1', 'PLP-1004-5095'),
(1005, 'XI-RPL-2', 'PLP-1005-8759'),
(1006, 'XI-TKJ-2', 'PLP-1006-8282'),
(1007, 'X-RPL-1', 'PLP-1007-7418'),
(1008, 'X-RPL-2', 'PLP-1008-4406'),
(1009, 'X-TKJ-1', 'PLP-1009-6587'),
(1010, 'X-TKJ-2', 'PLP-1010-2845'),
(10010, 'X-TKJ-2', 'PLP-10010-7500');

--
-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nis` int(10) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `isi` text NOT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Indexes for dumped tables
--

--
-- Indexes for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `tb_aspirasi`
--
ALTER TABLE `tb_aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD KEY `idx_id_pelaporan` (`id_pelaporan`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `tb_input_aspirasi`
--
ALTER TABLE `tb_input_aspirasi`
  ADD PRIMARY KEY (`id_pelaporan`),
  ADD KEY `nis` (`nis`,`id_kategori`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  ADD PRIMARY KEY (`nis`),
  ADD UNIQUE KEY `id_pelapor` (`id_pelapor`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_aspirasi`
--
ALTER TABLE `tb_aspirasi`
  MODIFY `id_aspirasi` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key untuk setiap aspirasi', AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tb_input_aspirasi`
--
ALTER TABLE `tb_input_aspirasi`
  MODIFY `id_pelaporan` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  MODIFY `id_kategori` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_input_aspirasi`
--
ALTER TABLE `tb_input_aspirasi`
  ADD CONSTRAINT `tb_input_aspirasi_ibfk_1` FOREIGN KEY (`nis`) REFERENCES `tb_siswa` (`nis`),
  ADD CONSTRAINT `tb_input_aspirasi_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori` (`id_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
