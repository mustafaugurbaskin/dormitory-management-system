<?php
$error_message = "";
$success_message = ""; // Başarı mesajını tutmak için değişken

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rekoryurdu";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
    if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

    // Form verileri
    $yurtadi = $_POST['yurtadi'];
    $adres = $_POST['adres'];
    $telefon = $_POST['telefon'];
    $kapasite = $_POST['kapasite'];
    $kat = $_POST['kat'];
    $yurt_tipi = $_POST['yurt_tipi'];

    // Tekil Yurt Kodu oluşturma
    function generateTekilKod($conn) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $is_unique = false;

        do {
            $random_letters = $characters[mt_rand(0, 25)] . $characters[mt_rand(0, 25)];
            $random_numbers = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $tekil_kod = $random_letters . $random_numbers;

            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM yurtlar WHERE tekil_yurt_kodu = ?");
            $stmt->bind_param("s", $tekil_kod);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] == 0) {
                $is_unique = true;
            }
        } while (!$is_unique);

        return $tekil_kod;
    }

    $tekil_yurt_kodu = generateTekilKod($conn);

    // Resim yükleme işlemi
    $upload_dir = "upload/";
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $resim_path = "";

    if (!empty($_FILES['resim']['name'])) {
        $file_extension = pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($file_extension), $allowed_types)) {
            $random_name = uniqid() . '.' . $file_extension;
            $resim_path = $upload_dir . $random_name;

            if (!move_uploaded_file($_FILES['resim']['tmp_name'], $resim_path)) {
                $error_message = "Resim yüklenirken bir hata oluştu.";
            }
        } else {
            $error_message = "Sadece JPG ve PNG dosyaları yüklenebilir.";
        }
    }

    // Veritabanına ekleme
    if (empty($error_message)) {
        $resim_json = json_encode(['path' => $resim_path]);
        $sql = "INSERT INTO yurtlar (id, yurtadi, adres, telefon, resim, kapasite, kat, yurt_tipi, tekil_yurt_kodu) 
                VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiiss", $yurtadi, $adres, $telefon, $resim_json, $kapasite, $kat, $yurt_tipi, $tekil_yurt_kodu);

        if ($stmt->execute()) {
            $success_message = "Yurt başarıyla eklendi: {$yurtadi}";
        } else {
            $error_message = "Hata: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Yurt Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }

        .form-control, .form-select {
            border-radius: 6px;
            font-size: 14px;
            padding: 10px 12px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: bold;
            padding: 12px 15px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Yeni Yurt Ekle</h2>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <!-- Yurt Adı -->
        <div class="mb-3">
            <label for="yurtadi">Yurt Adı</label>
            <input type="text" class="form-control" id="yurtadi" name="yurtadi" placeholder="Örnek: Serdivan Kız Yurdu" required>
        </div>

        <!-- Adres -->
        <div class="mb-3">
            <label for="adres">Adres</label>
            <input type="text" class="form-control" id="adres" name="adres" placeholder="Örnek: Sakarya, Serdivan" required>
        </div>

        <!-- Telefon -->
        <div class="mb-3">
            <label for="telefon">Telefon</label>
            <input type="text" class="form-control" id="telefon" name="telefon" placeholder="Örnek: 0264 123 45 67" required>
        </div>

        <!-- Resim Yükleme -->
        <div class="mb-3">
            <label for="resim">Resim</label>
            <input type="file" class="form-control" id="resim" name="resim" accept=".jpg,.jpeg,.png" required>
        </div>

        <!-- Kapasite -->
        <div class="mb-3">
            <label for="kapasite">Kapasite</label>
            <input type="number" class="form-control" id="kapasite" name="kapasite" min="1" required>
        </div>

        <!-- Maksimum Kat -->
        <div class="mb-3">
            <label for="kat">Maksimum Kat</label>
            <input type="number" class="form-control" id="kat" name="kat" min="1" required>
        </div>

        <!-- Yurt Tipi -->
        <div class="mb-3">
            <label for="yurt_tipi">Yurt Tipi</label>
            <select class="form-select" id="yurt_tipi" name="yurt_tipi" required>
                <option value="Kız">Kız</option>
                <option value="Erkek">Erkek</option>
            </select>
        </div>

        <!-- Gönder Butonu -->
        <button type="submit" class="btn btn-primary w-100">Ekle</button>
    </form>
    <div class="mt-4">
    <a href="dashboard.php"><button class="btn btn-primary w-100">Geri Dön</button></a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
