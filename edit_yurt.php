<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM yurtlar WHERE id = $id");
$yurt = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $yurtadi = $_POST['yurtadi'];
    $adres = $_POST['adres'];
    $telefon = $_POST['telefon'];
    $kapasite = $_POST['kapasite'];
    $kat = $_POST['kat'];
    $yurt_tipi = $_POST['yurt_tipi'];

    $resim_json = $yurt['resim'];

    if (!empty($_FILES['resim']['name'])) {
        $upload_dir = "upload/";
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $uploaded_file = $_FILES['resim']['tmp_name'];
        $file_extension = pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_types)) {
            $random_name = uniqid() . '.' . $file_extension;
            $resim_path = $upload_dir . $random_name;

            if (!move_uploaded_file($uploaded_file, $resim_path)) {
                die("Resim yüklenirken bir hata oluştu.");
            }
            $resim_json = json_encode(['path' => $resim_path]);
        } else {
            die("Sadece JPG ve PNG dosyaları yüklenebilir.");
        }
    }

    $sql = "UPDATE yurtlar SET yurtadi = ?, adres = ?, telefon = ?, resim = ?, kapasite = ?, kat = ?, yurt_tipi = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssissi", $yurtadi, $adres, $telefon, $resim_json, $kapasite, $kat, $yurt_tipi, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?tab=yurtlar");
    } else {
        echo "Hata: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yurt Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 6px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }

        .file-input {
            position: relative;
        }

        .file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }

        .file-label {
            display: block;
            background: #e9ecef;
            border: 1px dashed #ced4da;
            padding: 10px 15px;
            text-align: center;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .file-label:hover {
            background-color: #dfe3e8;
        }

        img {
            display: block;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Yurt Düzenle</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="yurtadi" class="form-label">Yurt Adı</label>
            <input type="text" class="form-control" id="yurtadi" name="yurtadi" value="<?= htmlspecialchars($yurt['yurtadi']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="adres" class="form-label">Adres</label>
            <input type="text" class="form-control" id="adres" name="adres" value="<?= htmlspecialchars($yurt['adres']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="telefon" class="form-label">Telefon</label>
            <input type="text" class="form-control" id="telefon" name="telefon" value="<?= htmlspecialchars($yurt['telefon']) ?>" required>
        </div>

        <div class="mb-3 file-input">
            <label for="resim" class="form-label">Resim Güncelle</label>
            <label for="resim" class="file-label">Bir resim seçin (JPG veya PNG)</label>
            <input type="file" id="resim" name="resim" accept=".jpg,.jpeg,.png">
            <img src="<?= htmlspecialchars(json_decode($yurt['resim'], true)['path']) ?>" alt="Mevcut Resim" style="width: 100px;">
        </div>

        <div class="mb-3">
            <label for="kapasite" class="form-label">Kapasite</label>
            <input type="number" class="form-control" id="kapasite" name="kapasite" value="<?= htmlspecialchars($yurt['kapasite']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="kat" class="form-label">Maksimum Kat</label>
            <input type="number" class="form-control" id="kat" name="kat" value="<?= htmlspecialchars($yurt['kat']) ?>" min="1" required>
        </div>

        <div class="mb-3">
            <label for="yurt_tipi" class="form-label">Yurt Tipi</label>
            <select class="form-select" id="yurt_tipi" name="yurt_tipi" required>
                <option value="Kız" <?= $yurt['yurt_tipi'] === 'Kız' ? 'selected' : '' ?>>Kız</option>
                <option value="Erkek" <?= $yurt['yurt_tipi'] === 'Erkek' ? 'selected' : '' ?>>Erkek</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Güncelle</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
