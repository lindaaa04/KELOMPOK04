<?php
session_start();
include "koneksi.php";

// Ambil pesan dari session (post-redirect-get)
$pesan = "";
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']);
}

// Buat CSRF token sederhana jika belum ada
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// Daftar field skala yang akan disimpan (nilai 1..5)
$fields_int = array(
    'kemudahan',         // Kemudahan pelayanan
    'kejelasan',         // Kejelasan prosedur
    'kelengkapan',       // Kelengkapan persyaratan
    'keramahan',         // Keramahan petugas
    'waktu',             // Lama/waktu pelayanan
    'ketepatan',         // Ketepatan informasi/dokumen
    'informasi_online',  // Ketersediaan informasi online
    'keseluruhan',       // Kepuasan keseluruhan
    'rekomendasi'        // Kesediaan merekomendasikan layanan
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim'])) {
    // Validasi CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['pesan'] = 'Token keamanan tidak valid.';
        header('Location: survei.php');
        exit;
    }

    // Ambil dan sanitasi data responden
    $nim = isset($_POST['nim']) ? trim($_POST['nim']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $fakultas = isset($_POST['fakultas']) ? trim($_POST['fakultas']) : '';
    $tujuan = isset($_POST['tujuan']) ? trim($_POST['tujuan']) : '';

    // Validasi minimal: nim dan nama direkomendasikan
    if ($nim === '' || $nama === '') {
        $_SESSION['pesan'] = 'Silakan isi NIM dan Nama.';
        header('Location: survei.php');
        exit;
    }

    // Ambil dan validasi jawaban skala (1-5)
    $answers = array();
    $valid = true;
    foreach ($fields_int as $f) {
        $v = isset($_POST[$f]) ? (int) $_POST[$f] : 0;
        if ($v < 1 || $v > 5) {
            $valid = false;
        }
        $answers[$f] = $v;
    }

    $saran = isset($_POST['saran']) ? trim($_POST['saran']) : '';

    if (!$valid) {
        $_SESSION['pesan'] = 'Pastikan semua pertanyaan skala (1-5) telah diisi.';
        header('Location: survei.php');
        exit;
    }

    // Susun query insert — pastikan struktur tabel `survei` sesuai kolom di bawah.
    $columns = '(nim,nama,fakultas,tujuan,' . implode(',', $fields_int) . ',saran)';
    $placeholders = '(' . rtrim(str_repeat('?,', count(explode(',', $columns))), ',') . ')';

    // Karena mysqli tidak menerima named placeholders, buat placeholder sesuai jumlah parameter
    $placeholders = '(' . implode(',', array_fill(0, 4 + count($fields_int) + 1, '?')) . ')';

    $stmt = mysqli_prepare($koneksi, "INSERT INTO survei $columns VALUES $placeholders");

    if ($stmt) {
        // types: 4 strings (nim,nama,fakultas,tujuan), N ints (fields_int), 1 string (saran)
        $types = str_repeat('s', 4) . str_repeat('i', count($fields_int)) . 's';

        // Buat array param urut sesuai types
        $params = array_merge(
            array($nim, $nama, $fakultas, $tujuan),
            array_values($answers),
            array($saran)
        );

        // mysqli_stmt_bind_param membutuhkan variables by reference
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }

        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
        $exec = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($exec) {
            $_SESSION['pesan'] = 'Terima kasih! Survei Anda berhasil dikirim.';
        } else {
            $_SESSION['pesan'] = 'Terjadi kesalahan saat menyimpan survei. Silakan coba lagi.';
        }
    } else {
        $_SESSION['pesan'] = 'Gagal menyiapkan query database. Periksa struktur tabel survei.';
    }

    header('Location: survei.php');
    exit;
}
?>

<?php include "header.php"; ?>
<?php include "sidebar.php"; ?>

<div class="content" style="max-width:920px;margin:20px auto;padding:20px;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);font-family:Arial,Helvetica,sans-serif;">
    <h2 style="margin-top:0;">Survei Kepuasan Pelayanan: Surat Keterangan Aktif Mahasiswa</h2>
    <p style="color:#555;margin-top:4px;margin-bottom:16px;">Bantu kami meningkatkan layanan dengan mengisi survei singkat ini. Jawaban Anda anonim dan digunakan untuk evaluasi pelayanan.</p>

    <?php if ($pesan !== ""): ?>
        <div style="padding:10px;border-radius:6px;background:#e6ffed;color:#064e2b;margin-bottom:16px;">
            <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <fieldset style="border:1px solid #eee;padding:12px;border-radius:6px;margin-bottom:14px;">
            <legend style="font-weight:bold;">Data Responden (Opsional tapi direkomendasikan)</legend>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <label for="nim">NIM</label>
                    <input id="nim" name="nim" type="text" required style="width:100%;padding:8px;margin-top:6px;border:1px solid #ddd;border-radius:4px;" placeholder="e.g. 12345678">
                </div>
                <div style="flex:2;min-width:200px;">
                    <label for="nama">Nama</label>
                    <input id="nama" name="nama" type="text" required style="width:100%;padding:8px;margin-top:6px;border:1px solid #ddd;border-radius:4px;" placeholder="Nama lengkap">
                </div>
                <div style="flex:1;min-width:180px;">
                    <label for="fakultas">Fakultas</label>
                    <input id="fakultas" name="fakultas" type="text" style="width:100%;padding:8px;margin-top:6px;border:1px solid #ddd;border-radius:4px;" placeholder="Fakultas">
                </div>
                <div style="flex:1;min-width:180px;">
                    <label for="tujuan">Tujuan Permohonan</label>
                    <select id="tujuan" name="tujuan" style="width:100%;padding:8px;margin-top:6px;border:1px solid #ddd;border-radius:4px;">
                        <option value="">Pilih (opsional)</option>
                        <option value="akademik">Keperluan Akademik</option>
                        <option value="beasiswa">Beasiswa</option>
                        <option value="kerja">Lamaran Kerja</option>
                        <option value="perpajakan">Administrasi / Perpajakan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset style="border:1px solid #eee;padding:12px;border-radius:6px;margin-bottom:14px;">
            <legend style="font-weight:bold;">Penilaian Layanan (Skala 1 = Sangat Buruk sampai 5 = Sangat Baik)</legend>

            <?php
            // Fungsi bantu untuk render select 1..5
            function render_scale($id, $label) {
                echo '<label for="' . $id . '" style="display:block;margin-top:8px;">' . $label . '</label>';
                echo '<select id="' . $id . '" name="' . $id . '" required style="width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ddd;border-radius:4px;">';
                echo '<option value="">Pilih</option>';
                for ($i=1;$i<=5;$i++) echo '<option value="' . $i . '">' . $i . '</option>';
                echo '</select>';
            }

            render_scale('kemudahan','1. Kemudahan prosedur pendaftaran dan pengajuan');
            render_scale('kejelasan','2. Kejelasan persyaratan dan prosedur yang diinformasikan');
            render_scale('kelengkapan','3. Kelengkapan dokumen yang diminta (format, tanda tangan, stempel)');
            render_scale('keramahan','4. Keramahan dan sikap petugas saat melayani');
            render_scale('waktu','5. Lama waktu penyelesaian layanan');
            render_scale('ketepatan','6. Ketepatan dokumen/isi surat (sesuai permintaan)');
            render_scale('informasi_online','7. Ketersediaan dan kemudahan informasi online (jika ada)');
            render_scale('keseluruhan','8. Kepuasan keseluruhan terhadap layanan');
            render_scale('rekomendasi','9. Seberapa besar kemungkinan Anda merekomendasikan layanan ini kepada mahasiswa lain');
            ?>
        </fieldset>

        <label for="saran">Saran / Masukan (opsional)</label>
        <textarea id="saran" name="saran" rows="4" style="width:100%;padding:8px;margin:6px 0 16px;border-radius:4px;border:1px solid #ddd;" placeholder="Tuliskan saran atau pengalaman Anda di sini..."></textarea>

        <div style="display:flex;gap:10px;align-items:center;">
            <button type="submit" name="kirim" style="padding:10px 18px;background:#0056b3;color:#fff;border:none;border-radius:6px;cursor:pointer;">Kirim Survei</button>
            <small style="color:#777;">Dengan mengirim, Anda membantu perbaikan layanan.</small>
        </div>
    </form>
</div>

<?php include "footer.php"; ?>



