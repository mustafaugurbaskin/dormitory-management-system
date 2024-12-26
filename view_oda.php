<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$oda_numarasi = isset($_GET['oda_numarasi']) ? $_GET['oda_numarasi'] : null;
$yurt_kodu = isset($_GET['yurt_kodu']) ? $_GET['yurt_kodu'] : null;

if (!$oda_numarasi || !$yurt_kodu) {
    die("Hatalı parametreler. Lütfen geri dönün ve tekrar deneyin.");
}

$sql = "
    SELECT o.numarasi, o.kati, o.kapasitesi,
           (SELECT COUNT(*) FROM ogrenciler WHERE oda_numarasi = o.numarasi AND tekil_yurt_kodu = o.tekil_yurt_kodu) AS doluluk,
           y.yurtadi, y.tekil_yurt_kodu
    FROM odalar o
    JOIN yurtlar y ON o.tekil_yurt_kodu = y.tekil_yurt_kodu
    WHERE o.numarasi = ? AND o.tekil_yurt_kodu = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $oda_numarasi, $yurt_kodu);
$stmt->execute();
$result = $stmt->get_result();
$oda = $result->fetch_assoc();

if (!$oda) {
    die("Oda bulunamadı.");
}

$ogrenci_sql = "
    SELECT ogr.* 
    FROM ogrenciler ogr
    WHERE ogr.oda_numarasi = ? AND ogr.tekil_yurt_kodu = ?
";
$ogrenci_stmt = $conn->prepare($ogrenci_sql);
$ogrenci_stmt->bind_param("ss", $oda_numarasi, $yurt_kodu);
$ogrenci_stmt->execute();
$ogrenci_result = $ogrenci_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oda Detayları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Modern tasarım için CSS */
        body {
            background-color: #F8F9FA;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #1E90FF;
            color: white;
            font-weight: bold;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .card-body p {
            margin: 5px 0;
        }
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: #1E90FF;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #E9F5FF;
        }
        .btn-primary {
            background-color: #1E90FF;
            border: none;
            transition: transform 0.2s ease, background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0F6CD1;
            transform: translateY(-2px);
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            Oda Detayları: <?= htmlspecialchars($oda['numarasi']) ?>
        </div>
        <div class="card-body">
            <p><strong>Kat:</strong> <?= htmlspecialchars($oda['kati']) ?></p>
            <p><strong>Kapasite:</strong> <?= htmlspecialchars($oda['kapasitesi']) ?></p>
            <p><strong>Doluluk:</strong> <?= htmlspecialchars($oda['doluluk']) ?>/<?= htmlspecialchars($oda['kapasitesi']) ?></p>
            <p><strong>Yurt Adı:</strong> <?= htmlspecialchars($oda['yurtadi']) ?></p>
            <p><strong>Yurt Kodu:</strong> <?= htmlspecialchars($oda['tekil_yurt_kodu']) ?></p>
        </div>
    </div>
    <div class="mt-4">
        <h3 class="text-center">Bu Odadaki Öğrenciler</h3>
        <?php if ($ogrenci_result && $ogrenci_result->num_rows > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>T.C. Kimlik No</th>
                        <th>Ad</th>
                        <th>Soyad</th>
                        <th>Telefon</th>
                        <th>Adres</th>
                        <th>Fotoğraf</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ogrenci = $ogrenci_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($ogrenci['tc_kimlik_no']) ?></td>
                            <td><?= htmlspecialchars($ogrenci['adi']) ?></td>
                            <td><?= htmlspecialchars($ogrenci['soyadi']) ?></td>
                            <td><?= htmlspecialchars($ogrenci['telefon']) ?></td>
                            <td><?= htmlspecialchars($ogrenci['adres']) ?></td>
                            <td>
                                <?php
                                $foto_json = json_decode($ogrenci['fotograf'], true);
                                $foto_path = isset($foto_json['path']) ? $foto_json['path'] : '';
                                if ($foto_path && file_exists($foto_path)): ?>
                                    <img src="<?= htmlspecialchars($foto_path) ?>" alt="Fotoğraf" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    Fotoğraf yok
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">Bu odada öğrenci bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
    <a href="dashboard.php?tab=odalar" class="btn btn-primary mt-3">Geri Dön</a>
</div>
</body>
</html>

<?php $conn->close(); ?>
