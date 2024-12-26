<?php
$error_message = ""; // Hata mesajını tutmak için değişken

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rekoryurdu";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
    if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

    $tekil_yurt_kodu = $_POST['tekil_yurt_kodu'];
    $oda_numarasi = $_POST['oda_numarasi'];
    $kati = $_POST['kati'];
    $kapasitesi = $_POST['kapasitesi'];

    $numarasi = $kati . $oda_numarasi;

    // Aynı yurt kodu, kat ve oda numarasına sahip oda kontrolü
    $check_query = $conn->prepare("SELECT * FROM odalar WHERE tekil_yurt_kodu = ? AND numarasi = ?");
    $check_query->bind_param("ss", $tekil_yurt_kodu, $numarasi);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Bu yurt, kat ve oda numarasına sahip bir oda zaten mevcut.";
    } else {
        $yurt_query = $conn->query("SELECT kapasite, kat FROM yurtlar WHERE tekil_yurt_kodu = '$tekil_yurt_kodu'");
        $yurt = $yurt_query->fetch_assoc();

        if (!$yurt) {
            $error_message = "Seçilen yurt bulunamadı.";
        } else {
            $odalar_query = $conn->query("SELECT SUM(kapasitesi) AS toplam_kapasite FROM odalar WHERE tekil_yurt_kodu = '$tekil_yurt_kodu'");
            $odalar = $odalar_query->fetch_assoc();
            $toplam_kapasite = isset($odalar['toplam_kapasite']) ? $odalar['toplam_kapasite'] : 0;

            if ($toplam_kapasite + $kapasitesi > $yurt['kapasite']) {
                $error_message = "Bu yurt için toplam kapasite aşılamaz!";
            } elseif ($kati > $yurt['kat']) {
                $error_message = "Seçilen kat, yurdun maksimum kat sayısını geçemez!";
            } elseif (!preg_match('/^\d{2}$/', $oda_numarasi)) {
                $error_message = "Oda numarası hatalı! İki basamaklı bir sayı olmalı (ör: 01, 10, 99).";
            } else {
                $sql = "INSERT INTO odalar (tekil_yurt_kodu, numarasi, kati, kapasitesi) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssii", $tekil_yurt_kodu, $numarasi, $kati, $kapasitesi);
                if ($stmt->execute()) {
                    // Yönlendirme sırasında success mesajını query string olarak ekle
                    header("Location: dashboard.php?tab=odalar&success=Oda başarıyla eklendi.");
                    exit;
                } else {
                    $error_message = "Hata: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    $check_query->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<meta charset="UTF-8">
<html lang="tr">
<head>
    <title>Oda Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Yeni Oda Ekle</h2>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="tekil_yurt_kodu" class="form-label">Yurt Seç</label>
            <select class="form-select" name="tekil_yurt_kodu" required>
                <option value="">Bir yurt seçin</option>
                <?php
                $conn = new mysqli("localhost", "root", "", "rekoryurdu");
                $conn->set_charset("utf8mb4");
                $result = $conn->query("SELECT tekil_yurt_kodu, yurtadi FROM yurtlar");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['tekil_yurt_kodu']}'>{$row['yurtadi']} ({$row['tekil_yurt_kodu']})</option>";
                }
                $conn->close();
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="kati" class="form-label">Oda Katı (Min: 0)</label>
            <input type="number" class="form-control" name="kati" placeholder="Örn: 1" min="0" required>
        </div>
        <div class="mb-3">
            <label for="oda_numarasi" class="form-label">Oda Numarası</label>
            <input type="text" class="form-control" name="oda_numarasi" placeholder="Örn: 01, 02" pattern="\d{2}" required>
        </div>
        <div class="mb-3">
            <label for="kapasitesi" class="form-label">Kapasite</label>
            <input type="number" class="form-control" name="kapasitesi" placeholder="Örn: 3" min="0" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ekle</button>
    </form>
</div>
</body>
</html>
